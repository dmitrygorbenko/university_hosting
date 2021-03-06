/***************************************************************************
 *   Copyright (C) 2005 by Dmitriy Gorbenko                                *
 *   nial@ukr.net                                                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/

#ifdef HAVE_CONFIG_H
#include <config.h>
#endif

#include <ctype.h>
#include <cstring>
#include <cstdio>
#include <cstdlib>
#include <csignal>

#include <sys/stat.h>
#include <sys/types.h>
#include <sys/socket.h>
#include "sys/wait.h"
#include <netinet/in.h>
#include <arpa/inet.h>
#include <unistd.h>

#include "define.h"
//#include "ns.h"
//#include "dirs.h"
//#include "dialog.h"
#include "config.h"
#include "message.h"
#include "params.h"
#include "tools.h"
#include "pilgrim.h"

using namespace std;

extern struct params_def startup_params;
extern struct mess_state_def m_state;
extern struct config_data_def config_data;

unsigned int child_count = 0;

void Signal_TERM(int sig)
{
	if (sig == SIGINT)
		fprintf(stdout, "Cought SIGINT\n");
	else if (sig == SIGHUP)
		fprintf(stdout, "Cought SIGHUP\n");
	else if (sig == SIGTERM)
		fprintf(stdout, "Cought SIGTERM\n");
	else if (sig == SIGSEGV)
		fprintf(stdout, "Cought SIGSEGV\n");

	config_shutdown();
	message_shutdown();

	exit(1);
};

void Signal_PIPE(int sig)
{
/*
	if (sig == SIGPIPE) {
		broken_pipe = TRUE;
	}
*/
	return;
};

void Signal_CHILD(int sig)
{
	if (sig == SIGCHLD) {
		wait(NULL);
		child_count--;
	}
	return;
};

void Set_Signal_Handler(void)
{
	signal(SIGINT,   Signal_TERM);
	signal(SIGHUP,   Signal_TERM);
	signal(SIGTERM,  Signal_TERM);
	signal(SIGSEGV,  Signal_TERM);
	signal(SIGPIPE,  Signal_PIPE);
	signal(SIGCHLD,  Signal_CHILD);
};

unsigned int revoke_root()
{
	uid_t real;
	uid_t effective;

	real = getuid();
	effective = geteuid();

	if (real != 0 && effective != 0 ) {
		return TRUE;
	}

	// Revoking root...

	if (setreuid(1, 1) == -1) {
		fprintf(stdout, "Can't drop root privileges to uid/gid: 1/1\n");
		return FALSE;
	}

	return TRUE;
};

int main(int argc, char *argv[])
{
	unsigned int result = FALSE;
	int listenfd = 0;
	int clientfd = 0;
	int rr = 0;
	int x = 1;

	struct sockaddr_in listaddr;
	struct sockaddr_in cliaddr;
	socklen_t clilen;

	Set_Signal_Handler();
	m_state.log_init == FALSE;

	if (revoke_root() != TRUE) {
		fprintf(stdout, "Exiting after revoke_root\n");
		exit(0);
	}


	reset_params();
	if (parse_params(argc, argv) != TRUE) {
		fprintf(stdout, "Exiting after parse_params\n");
		exit(0);
	}
	if (startup_params.view_help == TRUE)
		exit(0);

//	UNCOMMENT NEXT LINES THEN YOU WILL COMPILE COMPLETE PROGRAM
/*
	if (startup_params.alternative_log == TRUE)
		result = message_init(startup_params.log_file);
	else
		result = message_init(LOG_FILE);
	if (result == FALSE) {
		fprintf(stdout, "Exiting\n");
		exit(0);
	}
*/

	if (startup_params.alternative_config == TRUE)
		result = config_init(startup_params.config_file);
	else
		result = config_init(CONFIG_FILE);

	if (result == FALSE) {
		message(1, "Exiting after config_init\n");
		exit(0);
	}

	result = read_config();
	if (result == FALSE) {
		message(1, "Exiting after read_config\n");
		exit(0);
	}


	listenfd = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);

	if (listenfd < 0) {
		message(1, "error on socket\n");
		exit(0);
	}

	rr = setsockopt (listenfd, SOL_SOCKET, SO_REUSEADDR, &x, sizeof (x));

	if (rr == -1) {
		message(1, "listenfd reuseaddr failed\n");
		exit(0);
	}

	memset((void *) &listaddr, '\0', (size_t) sizeof(listaddr));

	listaddr.sin_family = AF_INET;
	result = get_s_addr(config_data.listen_ip, &(listaddr.sin_addr));
	if (result == FALSE) {
		message(1, "Exiting after get_s_addr\n");
		fprintf(stderr, "Failed: %d\n", listaddr.sin_addr.s_addr);
		exit(0);
	}

	fprintf(stderr, "After: %d\n", listaddr.sin_addr.s_addr);

	listaddr.sin_port = htons(config_data.listen_port);

	if (bind(listenfd, (struct sockaddr *) &listaddr,  sizeof(listaddr)) < 0) {
		message(1, "error on bind\n");
		perror("error:");
		exit(0);
	}

	if (listen(listenfd, 256) < 0) {
		message(1, "error on listen\n");
		exit(0);
	}

	for (;;) {
		memset((void *) &cliaddr, '\0', sizeof(cliaddr));

		clilen = (socklen_t) sizeof(cliaddr);

		clientfd = accept(listenfd, (struct sockaddr *) &cliaddr, &clilen);
/*
		if (clientfd != 0 && child_count <= CHILD_MAX_COUNT) {
			fork_res = fork();

			if (fork_res == 0){
				begin_dialog(listenfd, clientfd, cliaddr);
				exit(EXIT_SUCCESS);
			}
			if (fork_res != 0) {
				child_count++;
				close(clientfd);
			}
		}
		else {
			if (clientfd != 0)
				close(clientfd);
		}
*/		close(clientfd);
	}


	config_shutdown();
	message_shutdown();

	exit(0);
	/* silence compiler warnings */
	return EXIT_SUCCESS;
	(void) argc;
	(void) argv;
};

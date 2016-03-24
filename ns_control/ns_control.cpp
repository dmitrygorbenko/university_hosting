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

#include <cstring>
#include <cstdio>
#include <cstdlib>
#include <csignal>

#include <sys/socket.h>
#include <arpa/inet.h>
#include "sys/wait.h"
#include <unistd.h>

#include "define.h"
#include "dialog.h"

using namespace std;

struct { uid_t real; uid_t effective; } uid_startup;
extern unsigned int broken_pipe;

unsigned int child_count = 0;

void Signal_TERM(int sig)
{
	if (sig == SIGINT)
		printf("Cought SIGINT\n");
	else if (sig == SIGHUP)
		printf("Cought SIGHUP\n");
	else if (sig == SIGTERM)
		printf("Cought SIGTERM\n");
	else if (sig == SIGSEGV)
		printf("Cought SIGSEGV\n");

	exit(1);
};

void Signal_PIPE(int sig)
{
	if (sig == SIGPIPE) {
		broken_pipe = TRUE;
	}

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

	broken_pipe = FALSE;
};


unsigned int become_root()
{
	uid_t real;
	uid_t effective;

	real = getuid();
	effective = geteuid();

	uid_startup.real = real;
	uid_startup.effective = effective;

	if (real == 0) {
		return TRUE;
	}

	if (effective != 0) {
		printf("File haven't '+s' flag or owner is not 'root'\n");
		return FALSE;
	}

	// Becoming root...

	if (setreuid(effective, effective) == -1) {
		printf("Can't become root\n");
		return FALSE;
	}

	return TRUE;
};

void become_simple()
{
	setreuid(uid_startup.real, uid_startup.effective);
};

int main(int argc, char *argv[])
{
	int listenfd = 0;
	int clientfd = 0;
	int rr = 0;
	int x = 1;
	int fork_res;

	struct sockaddr_in listaddr;
	struct sockaddr_in cliaddr;
	socklen_t clilen;

	struct timeval     *tv_rcv;
	struct timeval     *tv_snd;

	Set_Signal_Handler();

	if (ROOT_COMPILE) {
		if (become_root() != TRUE) {
			printf("Can't become root\n");
			exit(0);
		}
	}

	listenfd = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);

	if (listenfd < 0) {
		printf("error on socket\n");
		exit(0);
	}

	rr = setsockopt (listenfd, SOL_SOCKET, SO_REUSEADDR, &x, sizeof (x));

	if (rr == -1) {
		printf ("listenfd reuseaddr failed\n");
	}

	memset((void *) &listaddr, '\0', (size_t) sizeof(listaddr));

	listaddr.sin_family = AF_INET;
	listaddr.sin_addr.s_addr = htonl(0xAC10D4C8);
	listaddr.sin_port = htons(2697);

	if (bind(listenfd, (struct sockaddr *) &listaddr,  sizeof(listaddr)) < 0) {
		printf("error on bind\n");
		exit(0);
	}

	if (listen(listenfd, 256) < 0) {
		printf("error on listen\n");
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
*/				begin_dialog(clientfd, cliaddr);
/*				exit(EXIT_SUCCESS);
			}
			if (fork_res != 0) {
				child_count++;
				close(clientfd);
			}
		}
		else {
			if (clientfd != 0)
*/				close(clientfd);
//		}
	}

	exit(0);
	/* silence compiler warnings */
	return EXIT_SUCCESS;
	(void) argc;
	(void) argv;
};


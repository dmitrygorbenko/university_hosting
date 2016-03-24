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
#include <netinet/in.h>
#include <arpa/inet.h>
#include <unistd.h>

#include "define.h"
#include "dialog.h"
#include "str.h"
#include "ns.h"

using namespace std;

extern char * return_message;

int ns_connect(char *ns_addr, unsigned short int ns_port)
{
	int nsfd = 0;
	int rr = 0;
	int x = 1;

	struct sockaddr_in nsaddr;

	nsfd = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);

	if (nsfd < 0) {
		printf("error on socket\n");
		return 0;
	}

	rr = setsockopt (nsfd, SOL_SOCKET, SO_REUSEADDR, &x, sizeof (x));

	if (rr == -1) {
		printf ("nsfd reuseaddr failed\n");
	}

	memset((void *) &nsaddr, '\0', (size_t) sizeof(nsaddr));

	nsaddr.sin_family = AF_INET;

	if (inet_aton(ns_addr, &(nsaddr.sin_addr)) == 0) {
		printf("error on inet_aton\n");
		return 0;
	}

	nsaddr.sin_port = htons(ns_port);

	if (connect(nsfd, (struct sockaddr *) &nsaddr,  sizeof(nsaddr)) < 0) {
		printf("error on connect\n");
		return 0;
	}

	return nsfd;
}

unsigned int ns_control(char *ns_addr, unsigned short int ns_port, char * cmd)
{
	unsigned int result = FALSE;
	char * buf = NULL;
	char * tmp = NULL;
	char * res_mess = NULL;
	int fd = 0;

	fprintf(stderr, ">>>>>>>>>>>> TRACE:  ns_control BEGIN\n");

	fprintf(stderr, "CONNECTING TO NS...\n");
	fd = ns_connect(ns_addr, ns_port);

	if (fd == 0) {
		fprintf(stderr, "CAN'T CONNECT TO NS...\n");
		fprintf(stderr, ">>>>>>>>>>>> TRACE:  ns_control END\n");
		return FALSE;
	}

	// read "+OK"
	fprintf(stderr, "READ FROM NS \"+OK\"\n");
	buf = recv_line(fd);
	if (!buf) {
		fprintf(stderr, "CLOSE SOCKET TO NS...\n");
		close(fd);
		fprintf(stderr, ">>>>>>>>>>>> TRACE:  ns_control END\n");
		return FALSE;
	}

	if (strcmp(buf, "+OK") != 0) {
		fprintf(stderr, "CAN'T FIND INITIAL \"+OK\"\n");
		safe_free(&buf);
		fprintf(stderr, "CLOSE SOCKET TO NS...\n");
		close(fd);
		fprintf(stderr, ">>>>>>>>>>>> TRACE:  ns_control END\n");
		return FALSE;
	}
	safe_free(&buf);

	fprintf(stderr, "TRY TO LOGIN...\n");
	tmp = merge_strings(2, "LOGIN ", NS_ID);
	// Send to him our cmd...
	if (!send_line(fd, tmp)) {
		fprintf(stderr, "CLOSE SOCKET TO NS...\n");
		close(fd);
		fprintf(stderr, ">>>>>>>>>>>> TRACE:  ns_control END\n");
		return FALSE;
	}
	safe_free(&tmp);

	buf = recv_line(fd);
	if (!buf) {
		fprintf(stderr, "CLOSE SOCKET TO NS...\n");
		close(fd);
		fprintf(stderr, ">>>>>>>>>>>> TRACE:  ns_control END\n");
		return FALSE;
	}

	if (strcmp(buf, "+OK") != 0) {
		fprintf(stderr, "LOGIN FAILED\n");
		safe_free(&buf);
		fprintf(stderr, "CLOSE SOCKET TO NS...\n");
		close(fd);
		fprintf(stderr, ">>>>>>>>>>>> TRACE:  ns_control END\n");
		return FALSE;
	}
	safe_free(&buf);

	fprintf(stderr, "LOGIN OK\n");

	fprintf(stderr, "SEND COMMAND TO NS: %s\n", cmd);

	// Send to him our cmd...
	if (!send_line(fd, cmd)) {
		fprintf(stderr, "CLOSE SOCKET TO NS...\n");
		close(fd);
		fprintf(stderr, ">>>>>>>>>>>> TRACE:  ns_control END\n");
		return FALSE;
	}

	// Reading from socket our answer...
	// It maybe mutlistringed
	for(;;) {
		if (buf)
			safe_free(&buf);

		buf = recv_line(fd);
		if (!buf) {
			fprintf(stderr, "ON TRY TO READ FROM NS: nothing...\n", buf);
			result = FALSE;
			break;
		}

		fprintf(stderr, "READ FROM NS: %s\n", buf);

		tmp = get_first_word(buf);

		if(!tmp){
			result = FALSE;
			break;
		}

		safe_free(&tmp);

		if (strcmp(buf, "-NO") == 0) {
			fprintf(stderr, "RECEIVED FROM NS: -NO\n");
			result = FALSE;
			break;
		}

		if (strcmp(buf, "+OK") == 0) {
			fprintf(stderr, "RECEIVED FROM NS: +OK\n");
			result = TRUE;
			break;
		}

		fprintf(stderr, "RECEIVED FROM NS: %s\n", buf);
		if (res_mess)
			res_mess = sts(&res_mess, "\n");
		res_mess = sts(&res_mess, buf);
	}

	if (res_mess)
		return_message = res_mess;

	if (buf)
		safe_free(&buf);

	fprintf(stderr, "CLOSE SOCKET TO NS...\n");
	close(fd);

	fprintf(stderr, ">>>>>>>>>>>> TRACE:  ns_control END\n");
	return result;
}

unsigned int ns_manage(char * cmd)
{
	unsigned int result;
	result = ns_control("172.16.212.200", 2697, cmd);
	return result;
}

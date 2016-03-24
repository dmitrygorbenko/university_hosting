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

#include <arpa/inet.h>
#include <unistd.h>
#include <errno.h>

#include "define.h"
#include "login.h"
#include "str.h"
#include "dns.h"
#include "dialog.h"

using namespace std;

unsigned int broken_pipe;

char * return_message;

void begin_dialog(int fd, struct sockaddr_in client)
{
	char * buf = NULL;
	char * act = NULL;

	fprintf(stderr, ">>>>>>>>>>>> TRACE:  begin_dialog BEGIN\n");
	return_message = NULL;

	if (login_client(fd, client) != TRUE) {
		fprintf(stderr, "LOGIN FAILED\n");
		fprintf(stderr, "CLOSE SOCKET TO CLIENT...\n");
		close(fd);
		fprintf(stderr, ">>>>>>>>>>>> TRACE:  begin_dialog END\n");
		return;
	}

	fprintf(stderr, "CLIENT LOGIN\n");

	for (;;) {
		buf = recv_line(fd);

		if (!buf) {
			fprintf(stderr, "ON TRY TO READ FROM REMOTE: nothing...\n", buf);
			fprintf(stderr, "CLOSE SOCKET TO REMOTE...\n");
			fprintf(stderr, ">>>>>>>>>>>> TRACE:  begin_dialog END\n");
			close(fd);
			return;
		}

		fprintf(stderr, "READ FROM REMOTE: %s\n", buf);
		act = get_first_word(buf);

		// If he doens't send commands - exit
		if (!act) {
			reply(fd, "-NO");
			break;
		}

		if (strcmp(act, "DNS") == 0) {
			if (dns_manage(buf) != TRUE) {
				reply(fd, "-NO");
			}
			else {
				reply(fd, "+OK");
			}
		}
		else if (strcmp(act, "LOGOUT") == 0) {
			reply(fd, "+OK");
			break;
		}
		else {
			reply(fd, "-NO");
		}

		if (buf) safe_free(&buf);
		if (act) safe_free(&act);
	}

	fprintf(stderr, "CLOSE SOCKET TO REMOTE...\n");
	close(fd);

	if (buf) safe_free(&buf);
	if (act) safe_free(&act);

	fprintf(stderr, ">>>>>>>>>>>> TRACE:  begin_dialog END\n");

	return;
}

unsigned int reply(int fd, char * msg)
{
	if (return_message) {
		fprintf(stderr, "SEND TO REMOTE (MESSAGE): %s\n", return_message);
		send_line(fd, return_message);
		safe_free(&return_message);
	}

	fprintf(stderr, "SEND TO REMOTE: %s\n", msg);
	send_line(fd, msg);

	return TRUE;
};

int send_line(int fd, char *src)
{
	char *p = src;
	ssize_t res;
	size_t i = 0;
	size_t n = strlen(src);

	while (i < n) {

		res = write(fd, p, 1);

		if (broken_pipe == TRUE) {
			broken_pipe = FALSE;
			return (-1);
		}

		if (res <= 0) {
			if (errno != EINTR) {
				return (-1);
			}
		} else {
			p++; i++;
		}
	}

	write(fd, "\n", 1);

	return (i);
}

char * recv_line(int fd)
{
	char buf;
	char * str = NULL;
	ssize_t res;

	do {
		res = read(fd, &buf, 1);

		if (res == 0) {
			// end of stream
			break;
		}
		else if (res == -1) {
			// error caught
			if (errno == EINTR)
				continue;
			return NULL;
		}

		if (buf == '\n')
			break;
		str = add_char_to_string(str, buf);
	} while (1);

	return str;
}

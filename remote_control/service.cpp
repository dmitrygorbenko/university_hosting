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

#include <unistd.h>
#include "sys/wait.h"
#include "sys/socket.h"

#include "define.h"
#include "str.h"
#include "service.h"

using namespace std;

unsigned int service_manage(char *cmd, int server_fd, int current_fd)
{
	unsigned int result = FALSE;
	unsigned int i = 0;
	char * action = NULL;
	char * service = NULL;
	int fork_res;

	action = get_word_by_number(cmd, 2);
	service = get_word_by_number(cmd, 3);

	if (!action || !service) {
		fprintf(stderr, "FAILED ON GET ACTION OR SERVICE\n");
		result = FALSE;
		goto service_end;
	}

	for (i = 0; i < strlen(action); i++)
		action[i] = tolower(action[i]);

	for (i = 0; i < strlen(service); i++)
		service[i] = tolower(service[i]);

	fork_res = vfork();
	if (fork_res == 0){
		// After fork here child thread

		// closing listen server socket
		close(server_fd);
		shutdown(server_fd, SHUT_RDWR);

		// closing current connection
		close(current_fd);
		shutdown(current_fd, SHUT_RDWR);

		char *args[4] = {HOSTINGRC_PATH, action, service, NULL};
		execve(HOSTINGRC_PATH, args, NULL);
		_exit(EXIT_SUCCESS);
	}
	if (fork_res != 0) {
		wait(NULL);
	}

	result = TRUE;

service_end:

	if (action) safe_free(&action);
	if (service) safe_free(&service);

	return result;
}


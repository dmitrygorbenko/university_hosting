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

#include <unistd.h>
#include <dirent.h>
#include <errno.h>
#include "sys/wait.h"

#include "define.h"
#include "str.h"
#include "dirs.h"
#include "mail.h"

using namespace std;

unsigned int mail_recurse_chown(char * dir_s)
{
	char * f_name = NULL;
	struct dirent * dir_entry = NULL;
	DIR * dir_fd = NULL;
	struct stat * stat_rec = NULL;

	if (!ROOT_COMPILE)
		return TRUE;

	if (!dir_s)
		return FALSE;

	// try to catch stupid man
	if (strstr(dir_s, "../") != NULL){
		return FALSE;
	}
	if (strstr(dir_s, "/..") != NULL){
		return FALSE;
	}

	dir_fd = opendir(dir_s);
	if (!dir_fd) {
		perror("Opendir:");
		return FALSE;
	}

	for(;;) {
		dir_entry = readdir(dir_fd);

		if (!dir_entry)
			break;

		if (strcmp(dir_entry->d_name, ".") == 0 || strcmp(dir_entry->d_name, "..") == 0) {
			continue;
		}

		f_name = merge_strings(3, dir_s, "/",  dir_entry->d_name);

		stat_rec = (struct stat *) malloc(sizeof(struct stat));

		if (stat(f_name, stat_rec) == -1) {
			continue;
		}

		if (S_ISDIR(stat_rec->st_mode)) {
			mail_recurse_chown(f_name);
		}

		if (S_ISREG(stat_rec->st_mode)) {
			if (chown(f_name, COURIER_UID, HOSTING_GID) == -1) {
				perror("Chown:");
			}
		}

		free(stat_rec);
		safe_free(&f_name);
	}

	closedir(dir_fd);

	if (chown(dir_s, COURIER_UID, HOSTING_GID) == -1) {
		perror("Chown:");
	}

	return TRUE;
}

unsigned int mail_manage(char *cmd)
{
	unsigned int result = FALSE;
	char * act = NULL;
	char * client = NULL;
	char * new_client = NULL;
	char * domain = NULL;
	char * new_domain = NULL;
	char * login = NULL;
	char * new_login = NULL;
	char * dir = NULL;
	char * new_dir = NULL;

	int fork_res;

	act = get_word_by_number(cmd, 2);

	if (!act)
		return FALSE;

	if (strcmp(act, "CREATE") == 0) {

		client = get_word_by_number(cmd, 3);
		domain = get_word_by_number(cmd, 4);
		login = get_word_by_number(cmd, 5);

		if (!client || !domain || !login) {
			fprintf(stderr, "FAILED ON GET CLIENT, DOMAIN OR LOGIN\n");
			result = FALSE;
			goto mail_end;
		}

		dir = merge_strings(7, CLIENT_DIR, "/", client, "/", domain, "/mail/", login);

		fork_res = vfork();

		if (fork_res == 0){
			// After fork here child thread
			char *args[3] = {MAILDIRMAKE_PATH, dir, NULL};
			execve(MAILDIRMAKE_PATH, args, NULL);
			_exit(EXIT_SUCCESS);
		}
		if (fork_res != 0) {
			wait(NULL);
		}

		if (mail_recurse_chown(dir) != TRUE) {
			fprintf(stderr, "FAILED ON RECURSE_CHOWN\n");
			result = FALSE;
			goto mail_end;
		}

		result = TRUE;
	}

	else if (strcmp(act, "DELETE") == 0) {

		client = get_word_by_number(cmd, 3);
		domain = get_word_by_number(cmd, 4);
		login = get_word_by_number(cmd, 5);

		if (!client || !domain || !login) {
			fprintf(stderr, "FAILED ON GET CLIENT, DOMAIN OR LOGIN\n");
			result = FALSE;
			goto mail_end;
		}

		dir = merge_strings(7, CLIENT_DIR, "/", client, "/", domain, "/mail/", login);

		if (remove_directory(dir) != TRUE) {
			fprintf(stderr, "FAILED on remove directory\n");
			result = FALSE;
			goto mail_end;
		}

		result = TRUE;
	}

	else if (strcmp(act, "UPDATE") == 0) {

		result = TRUE;

		client = get_word_by_number(cmd, 3);
		domain = get_word_by_number(cmd, 4);
		login = get_word_by_number(cmd, 5);
		new_client = get_word_by_number(cmd, 6);
		new_domain = get_word_by_number(cmd, 7);
		new_login = get_word_by_number(cmd, 8);

		if (!client || !domain || !login || !new_client ||  !new_login || !new_domain) {
			fprintf(stderr, "FAILED ON GET PARAMETERS\n");
			result = FALSE;
			goto mail_end;
		}

		dir = merge_strings(7, CLIENT_DIR, "/", client,  "/", domain, "/mail/", login);
		new_dir = merge_strings(7, CLIENT_DIR, "/", new_client,  "/", new_domain, "/mail/", new_login);

		if (rename(dir, new_dir) == -1) {
			fprintf(stderr, "FAILED ON RENAME: %s again %s\n", dir, new_dir);
			result = FALSE;
			goto mail_end;
		}
	}

mail_end:

	if (act) safe_free(&act);
	if (dir) safe_free(&dir);
	if (new_dir) safe_free(&new_dir);
	if (client) safe_free(&client);
	if (new_client) safe_free(&new_client);
	if (domain) safe_free(&domain);
	if (new_domain) safe_free(&domain);
	if (login) safe_free(&login);
	if (new_login) safe_free(&new_login);

	return result;
}


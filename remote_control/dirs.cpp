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

#include "define.h"
#include "str.h"
#include "dirs.h"

using namespace std;

unsigned int remove_directory(char * dir_s)
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
		// if opendir failed... maybe file already deleted ?
		// Even if opendir will fail while dir
		// exists, we'll have an error
		// So, what is better: caught everytime an error
		// or signal about unexisten file ?
		// At my oppinion better to signal
		return FALSE;
	}

	for(;;) {
		dir_entry = readdir(dir_fd);

		if (!dir_entry)
			break;

		if (strcmp(dir_entry->d_name, ".") == 0 || strcmp(dir_entry->d_name, "..") == 0) {
			continue;
		}

		f_name = merge_strings(3, dir_s, "/", dir_entry->d_name);

		stat_rec = (struct stat *) malloc(sizeof(struct stat));

		if (stat(f_name, stat_rec) == -1) {
			continue;
		}

		if (S_ISDIR(stat_rec->st_mode)) {
			remove_directory(f_name);
		}

		if (S_ISREG(stat_rec->st_mode)) {
			if (unlink(f_name) == -1) {
				perror("Remove file:");
			}
		}

		free(stat_rec);
		safe_free(&f_name);
	}

	closedir(dir_fd);

	if (rmdir(dir_s) == -1) {
		perror("Remove dir:");
	}

	return TRUE;
}


unsigned int zone_manage(char *cmd)
{
	unsigned int result = FALSE;
	char * act = NULL;
	char * client = NULL;
	char * zone = NULL;
	char * new_zone = NULL;
	char * dir = NULL;
	char * new_dir = NULL;

	mode_t mode;

	act = get_word_by_number(cmd, 2);

	if (!act)
		return FALSE;

	if (strcmp(act, "CREATE") == 0) {

		client = get_word_by_number(cmd, 3);

		if (!client) {
			result = FALSE;
			goto zone_end;
		}

		zone = get_word_by_number(cmd, 4);

		if (!zone) {
			result = FALSE;
			goto zone_end;
		}

		dir = merge_strings(5, CLIENT_DIR, "/", client, "/", zone);

		// try to catch stupid man
		if (strstr(dir, "../") != NULL){
			result = FALSE;
			goto zone_end;
		}
		if (strstr(dir, "/..") != NULL){
			result = FALSE;
			goto zone_end;
		}

		mode = 0770;

		if (mkdir(dir, mode) == -1) {
			if (errno != EEXIST) {
				perror("mkdir Main:");
				result = FALSE;
				goto zone_end;
			}
		}

		if (ROOT_COMPILE) {
			if (chown(dir, APACHE_UID, HOSTING_GID) == -1) {
				perror("Chown:");
				result = FALSE;
				goto zone_end;
			}
		}

		mode = 0700;

		// Setting mail owner
		new_dir = merge_strings(2, dir, "/mail");

		if (mkdir(new_dir, mode) == -1) {
			if (errno != EEXIST) {
				perror("mkdir Mail:");
				result = FALSE;
				goto zone_end;
			}
		}

		if (ROOT_COMPILE) {
			if (chown(new_dir, COURIER_UID, HOSTING_GID) == -1) {
				perror("Chown:");
				result = FALSE;
				goto zone_end;
			}
		}

		safe_free(&new_dir);

		// Setting www owner
		mode = 0770;
		new_dir = merge_strings(2, dir, "/www");

		if (mkdir(new_dir, mode) == -1) {
			if (errno != EEXIST) {
				perror("mkdir www:");
				result = FALSE;
				goto zone_end;
			}
		}

		if (ROOT_COMPILE) {
			if (chown(new_dir, PROFTPD_UID, HOSTING_GID) == -1) {
				perror("Chown:");
				result = FALSE;
				goto zone_end;
			}
		}

		safe_free(&new_dir);

		// Setting backup owner
		mode = 0700;
		new_dir = merge_strings(2, dir, "/backup");

		if (mkdir(new_dir, mode) == -1) {
			if (errno != EEXIST) {
				perror("mkdir backup:");
				result = FALSE;
				goto zone_end;
			}
		}

		if (ROOT_COMPILE) {
			if (chown(new_dir, APACHE_UID, HOSTING_GID) == -1) {
				perror("Chown:");
				result = FALSE;
				goto zone_end;
			}
		}

		safe_free(&new_dir);

		// Setting stat owner
		mode = 0700;
		new_dir = merge_strings(2, dir, "/stat");
		if (mkdir(new_dir, mode) == -1) {
			if (errno != EEXIST) {
				perror("mkdir stat:");
				result = FALSE;
				goto zone_end;
			}
		}

		if (ROOT_COMPILE) {
			if (chown(new_dir, APACHE_UID, HOSTING_GID) == -1) {
				perror("Chown:");
				result = FALSE;
				goto zone_end;
			}
		}

		safe_free(&new_dir);

		// Setting logs owner
		new_dir = merge_strings(2, dir, "/logs");

		if (mkdir(new_dir, mode) == -1) {
			if (errno != EEXIST) {
				perror("mkdir logs:");
				result = FALSE;
				goto zone_end;
			}
		}

		if (ROOT_COMPILE) {
			if (chown(new_dir, APACHE_UID, HOSTING_GID) == -1) {
				perror("Chown:");
				result = FALSE;
				goto zone_end;
			}
		}

		result = TRUE;
	}

	else if (strcmp(act, "DELETE") == 0) {

		client = get_word_by_number(cmd, 3);

		if (!client) {
			result = FALSE;
			goto zone_end;
		}

		zone = get_word_by_number(cmd, 4);

		if (!zone) {
			result = FALSE;
			goto zone_end;
		}

		dir = merge_strings(5, CLIENT_DIR, "/", client, "/", zone);

		// try to catch stupid man
		if (strstr(dir, "../") != NULL){
			result = FALSE;
			goto zone_end;
		}
		if (strstr(dir, "/..") != NULL){
			result = FALSE;
			goto zone_end;
		}

		if (remove_directory(dir) != TRUE) {
			result = FALSE;
			goto zone_end;
		}

		result = TRUE;
	}

	else if (strcmp(act, "UPDATE") == 0) {

		client = get_word_by_number(cmd, 3);
		zone = get_word_by_number(cmd, 4);
		new_zone = get_word_by_number(cmd, 5);

		if (!client || !zone || !new_zone) {
			result = FALSE;
			goto zone_end;
		}

		dir = merge_strings(5, CLIENT_DIR, "/", client, "/", zone);
		new_dir = merge_strings(5, CLIENT_DIR, "/", client, "/", new_zone);

		// try to catch stupid man
		if (strstr(dir, "../") != NULL){
			result = FALSE;
			goto zone_end;
		}
		if (strstr(dir, "/..") != NULL){
			result = FALSE;
			goto zone_end;
		}
		if (strstr(new_dir, "../") != NULL){
			result = FALSE;
			goto zone_end;
		}
		if (strstr(new_dir, "/..") != NULL){
			result = FALSE;
			goto zone_end;
		}

		if (rename(dir, new_dir) == -1) {
			result = FALSE;
			goto zone_end;
		}

		result = TRUE;
	}

	else {
		result = FALSE;
	}

zone_end:

	if (act) safe_free(&act);
	if (dir) safe_free(&dir);
	if (new_dir) safe_free(&new_dir);
	if (client) safe_free(&client);
	if (zone) safe_free(&zone);
	if (new_zone) safe_free(&new_zone);

	return result;
}


unsigned int client_manage(char *cmd)
{
	unsigned int result = FALSE;
	char * act = NULL;
	char * login = NULL;
	char * new_login = NULL;
	char * dir = NULL;
	char * new_dir = NULL;

	mode_t mode;

	act = get_word_by_number(cmd, 2);

	if (!act)
		return FALSE;

	if (strcmp(act, "CREATE") == 0) {

		login = get_word_by_number(cmd, 3);

		if (!login) {
			result = FALSE;
			goto clients_end;
		}

		dir = merge_strings(3, CLIENT_DIR, "/", login);

		// try to catch stupid man
		if (strstr(dir, "../") != NULL){
			result = FALSE;
			goto clients_end;
		}
		if (strstr(dir, "/..") != NULL){
			result = FALSE;
			goto clients_end;
		}

		mode = 0770;

		if (mkdir(dir, mode) == -1) {
			if (errno != EEXIST) {
				fprintf(stderr, "DIRECTORY: %s\n", dir);
				perror("mkdir Main:");
				result = FALSE;
				goto clients_end;
			}
		}

		if (ROOT_COMPILE) {
			if (chown(dir, APACHE_UID, HOSTING_GID) == -1) {
				perror("Chown:");
				result = FALSE;
				goto clients_end;
			}
		}

		result = TRUE;
	}

	else if (strcmp(act, "DELETE") == 0) {

		login = get_word_by_number(cmd, 3);

		if (!login) {
			result = FALSE;
			goto clients_end;
		}

		dir = merge_strings(3, CLIENT_DIR, "/", login);

		// try to catch stupid man
		if (strstr(dir, "../") != NULL){
			result = FALSE;
			goto clients_end;
		}
		if (strstr(dir, "/..") != NULL){
			result = FALSE;
			goto clients_end;
		}

		if (remove_directory(dir) != TRUE) {
			result = FALSE;
			goto clients_end;
		}

		result = TRUE;
	}

	else if (strcmp(act, "UPDATE") == 0) {

		login = get_word_by_number(cmd, 3);
		new_login = get_word_by_number(cmd, 4);

		if (!login || !new_login) {
			result = FALSE;
			goto clients_end;
		}

		dir = merge_strings(3, CLIENT_DIR, "/", login);
		new_dir = merge_strings(3, CLIENT_DIR, "/", new_login);

		// try to catch stupid man
		if (strstr(dir, "../") != NULL){
			result = FALSE;
			goto clients_end;
		}
		if (strstr(dir, "/..") != NULL){
			result = FALSE;
			goto clients_end;
		}
		if (strstr(new_dir, "../") != NULL){
			result = FALSE;
			goto clients_end;
		}
		if (strstr(new_dir, "/..") != NULL){
			result = FALSE;
			goto clients_end;
		}

		if (rename(dir, new_dir) == -1) {
			result = FALSE;
			goto clients_end;
		}

		result = TRUE;
	}

	else {
		result = FALSE;
	}

clients_end:

	if (act) safe_free(&act);
	if (dir) safe_free(&dir);
	if (new_dir) safe_free(&new_dir);
	if (login) safe_free(&login);
	if (new_login) safe_free(&new_login);

	return result;
}


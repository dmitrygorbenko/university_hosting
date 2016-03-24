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

#include <unistd.h>
#include "sys/wait.h"
#include <dirent.h>
#include <sys/stat.h>
#include "dirs.h"

#include "define.h"
#include "str.h"
#include "stat.h"

using namespace std;

extern char * return_message;

unsigned int stat_manage(char *cmd)
{
	unsigned int result = TRUE;
	int fork_res;
	char * zone = NULL;
	char * log_name = NULL;
	char * stat_dir = NULL;

	zone = get_word_by_number(cmd, 2);

	if (!zone) {
		fprintf(stderr, "FAILED ON GET ZONE\n");
		result = FALSE;
		goto stat_end;
	}

	log_name = merge_strings(4, CLIENT_DIR, "/", zone, "/logs/apache_access");
	stat_dir = merge_strings(5, CLIENT_DIR, "/", zone, "/", STAT_DIR);

	fork_res = vfork();
	if (fork_res == 0){
		// After fork here child thread
		char *args[7] = {WEBALIZER_PATH, "-n", zone, "-o", stat_dir, log_name, NULL};
		execve(WEBALIZER_PATH, args, NULL);
		_exit(EXIT_SUCCESS);
	}
	if (fork_res != 0) {
		wait(NULL);
	}

	if (stat_recurse_chown(stat_dir) == FALSE) {
		result = FALSE;
	}

stat_end:

	safe_free(&stat_dir);
	safe_free(&log_name);
	safe_free(&zone);

	return result;
};

unsigned int stat_recurse_chown(char * dir_s)
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

		f_name = strdup(dir_s);
		f_name = sts(&f_name, "/");
		f_name = sts(&f_name, dir_entry->d_name);

		stat_rec = (struct stat *) malloc(sizeof(struct stat));

		if (stat(f_name, stat_rec) == -1) {
			continue;
		}

		if (S_ISDIR(stat_rec->st_mode)) {
			stat_recurse_chown(f_name);
		}

		if (S_ISREG(stat_rec->st_mode)) {
			if (chown(f_name, APACHE_UID, HOSTING_GID) == -1) {
				perror("Chown:");
			}
		}

		free(stat_rec);
		safe_free(&f_name);
	}

	closedir(dir_fd);

	if (chown(dir_s, APACHE_UID, HOSTING_GID) == -1) {
		perror("Chown:");
	}

	return TRUE;
}

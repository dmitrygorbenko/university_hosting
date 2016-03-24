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
#include "backup.h"

using namespace std;

extern char * return_message;

unsigned int backup_manage(char *cmd)
{
	unsigned int result = FALSE;
	unsigned int file_find = FALSE;
	int fork_res;
	char * zone = NULL;
	char * dir = NULL;
	char * client_dir = NULL;
	char * client_www_dir = NULL;
	char * tar_file_name = NULL;
	struct dirent * dir_entry = NULL;
	DIR * dir_fd = NULL;

	zone = get_word_by_number(cmd, 2);

	if (!zone)
		return FALSE;

	client_dir = merge_strings(4, CLIENT_DIR, "/", zone, "/");
	client_www_dir = merge_strings(4, CLIENT_DIR, "/", zone, "/www");
	dir = merge_strings(4, CLIENT_DIR, "/", zone, "/backup");

	if (strstr(dir, "../") != NULL){
		return FALSE;
	}
	if (strstr(dir, "/..") != NULL){
		return FALSE;
	}

	dir_fd = opendir(dir);
	if (!dir_fd) {
		perror("Opendir:");
		safe_free(&dir);
		return FALSE;
	}

	for(;;) {
		dir_entry = readdir(dir_fd);

		if (!dir_entry)
			break;

		if (strcmp(dir_entry->d_name, ".") == 0 || strcmp(dir_entry->d_name, "..") == 0) {
			continue;
		}

		file_find = TRUE;
		break;
	}

	if (file_find == FALSE) {
		closedir(dir_fd);
		return FALSE;
	}

	tar_file_name = merge_strings(3, dir, "/", dir_entry->d_name);

	closedir(dir_fd);

	fork_res = vfork();

	if (fork_res == 0){
		char *args[6] = {TAR_PATH, "-zxC", client_dir, "-f", tar_file_name, NULL};
		execve(TAR_PATH, args, NULL);
		_exit(EXIT_SUCCESS);
	}
	if (fork_res != 0) {
		wait(NULL);
	}

	result = TRUE;
	if (remove_htaccess(client_www_dir) != TRUE) {
		result = FALSE;
	}

	if (dir) safe_free(&dir);
	if (zone) safe_free(&zone);
	if (client_dir) safe_free(&client_dir);
	if (client_www_dir) safe_free(&client_www_dir);
	if (tar_file_name) safe_free(&tar_file_name);

	return result;
}

unsigned int remove_htaccess(char * dir_s)
{
	char * f_name = NULL;
	struct dirent * dir_entry = NULL;
	DIR * dir_fd = NULL;
	struct stat * stat_rec = NULL;

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
			remove_htaccess(f_name);
		}

		if (S_ISREG(stat_rec->st_mode)) {
			if (strcmp(dir_entry->d_name, ".htaccess") == 0) {
				if (unlink(f_name) == -1) {
					perror("Remove file:");
				}
			}
		}

		free(stat_rec);
		safe_free(&f_name);
	}

	closedir(dir_fd);

	return TRUE;
}

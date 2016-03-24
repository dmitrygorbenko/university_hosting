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
#include "sys/types.h"
#include "sys/stat.h"

#include "define.h"
#include "str.h"
#include "tools.h"
#include "protect.h"

using namespace std;

extern char * return_message;
/*
=========Templates:===============

---------Creating:----------------
*** for creating user:
	PRO CREATE USER client domain user-name password [group-name]
*** for creating group:
	PRO CREATE GROUP client domain group-name
*** for creating pretocted area:
	PRO CREATE AREA client domain area-name object-type object-path method-type method-name

---------Updating:----------------
*** for updating user:
	PRO UPDATE USER client domain user-name new-user-name new-password [new-group-name]
*** for updating group:
	PRO UPDATE GROUP client domain group-name new-group-name
*** for updating pretocted area:
	PRO UPDATE AREA client domain object-type object-path new-area-name new-object-type new-object-path new-method-type new-method-name

---------Removing:----------------
*** for removing user:
	PRO REMOVE USER client domain user-name
*** for removing group:
	PRO REMOVE GROUP client domain group-name
*** for removing pretocted area:
	PRO REMOVE AREA client domain object-type object-path

*/

unsigned int protect_manage(char *cmd)
{
	unsigned int result = FALSE;
	char * act = NULL;

	act = get_word_by_number(cmd, 2);

	if (!act)
		return FALSE;

	if (strcmp(act, "CREATE") == 0) {
		if (protect_create_manage(cmd) == FALSE) {
			result = FALSE;
			goto protect_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "DELETE") == 0) {
		if (protect_remove_manage(cmd) == FALSE) {
			result = FALSE;
			goto protect_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "UPDATE") == 0) {
		if (protect_update_manage(cmd) == FALSE) {
			result = FALSE;
			goto protect_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "INFO") == 0) {
		result = FALSE;
	}

protect_end:

	if (act) safe_free(&act);

	return result;
}

unsigned int protect_create_manage(char *cmd)
{
	unsigned int result = FALSE;
	char * act = NULL;

	act = get_word_by_number(cmd, 3);

	if (!act)
		return FALSE;

	if (strcmp(act, "USER") == 0) {
		if (protect_create_user(cmd) == FALSE) {
			result = FALSE;
			goto protect_create_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "GROUP") == 0) {
		if (protect_create_group(cmd) == FALSE) {
			result = FALSE;
			goto protect_create_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "AREA") == 0) {
		if (protect_create_area(cmd) == FALSE) {
			result = FALSE;
			goto protect_create_end;
		}
		result = TRUE;
	}

protect_create_end:

	if (act) safe_free(&act);

	return result;
}

unsigned int protect_update_manage(char *cmd)
{
	unsigned int result = FALSE;
	char * act = NULL;

	act = get_word_by_number(cmd, 3);

	if (!act)
		return FALSE;

	if (strcmp(act, "USER") == 0) {
		if (protect_update_user(cmd) == FALSE) {
			result = FALSE;
			goto protect_update_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "GROUP") == 0) {
		if (protect_update_group(cmd) == FALSE) {
			result = FALSE;
			goto protect_update_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "AREA") == 0) {
		if (protect_update_area(cmd) == FALSE) {
			result = FALSE;
			goto protect_update_end;
		}
		result = TRUE;
	}

protect_update_end:

	if (act) safe_free(&act);

	return result;
}

unsigned int protect_remove_manage(char *cmd)
{
	unsigned int result = FALSE;
	char * act = NULL;

	act = get_word_by_number(cmd, 3);

	if (!act)
		return FALSE;

	if (strcmp(act, "USER") == 0) {
		if (protect_remove_user(cmd) == FALSE) {
			result = FALSE;
			goto protect_remove_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "GROUP") == 0) {
		if (protect_remove_group(cmd) == FALSE) {
			result = FALSE;
			goto protect_remove_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "AREA") == 0) {
		if (protect_remove_area(cmd) == FALSE) {
			result = FALSE;
			goto protect_remove_end;
		}
		result = TRUE;
	}

protect_remove_end:

	if (act) safe_free(&act);

	return result;
}

unsigned int protect_create_user(char * cmd)
{
	unsigned int result = FALSE;
	unsigned int i;
	struct stat * stat_rec = NULL;
	int fork_res;
	char * unique = NULL;

	char * domain = NULL;
	char * user_name = NULL;
	char * password = NULL;
	char * group_name = NULL;
	char * flags = NULL;

	char * buff = NULL;
	char * tmp = NULL;

	char * pass_file_name = NULL;
	char * tmp_file_name = NULL;
	char * group_file_name = NULL;

	FILE * file = NULL;
	FILE * tmpf = NULL;

	domain = get_word_by_number(cmd, 4);
	if (!domain) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET DOMAIN\n");
		goto protect_create_user_end;
	}

	user_name = get_word_by_number(cmd, 5);
	if (!user_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET USER_NAME\n");
		goto protect_create_user_end;
	}

	password = get_word_by_number(cmd, 6);
	if (!password) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET PASSWORD\n");
		goto protect_create_user_end;
	}

	group_name = get_word_by_number(cmd, 7);

	unique = create_unique_id();

	pass_file_name = merge_strings(4, CLIENT_DIR, "/", domain, "/passwords");
	tmp_file_name = merge_strings(5, CLIENT_DIR, "/", domain, "/", unique);

	stat_rec = (struct stat *) malloc(sizeof(struct stat));
	if (stat(pass_file_name, stat_rec) == -1) {
		flags = strdup("-cb");
	}
	else {
		if (S_ISREG(stat_rec->st_mode)) {
			flags = strdup("-b");
		}
		else {
			result = FALSE;
			fprintf(stderr, "THIS IS NOT FILE !!!\n");
			goto protect_create_user_end;
		}
	}

	free(stat_rec);
	stat_rec = NULL;

	// Launch program...
	fork_res = vfork();

	if (fork_res == 0){
		// After fork here child thread
		char *args[6] = {APACHEHTPASSWD_PATH, flags, pass_file_name, user_name, password, NULL};
		execve(APACHEHTPASSWD_PATH, args, NULL);
		_exit(EXIT_SUCCESS);
	}

	if (fork_res != 0) {
		wait(NULL);
	}

	if (ROOT_COMPILE) {
		if (chown(pass_file_name, APACHE_UID, HOSTING_GID) == -1) {
			perror("Chown:");
			result = FALSE;
			goto protect_create_user_end;
		}
	}

	result = TRUE;

	if (group_name) {
		// Group exists - add user to group
		result = FALSE;

		group_file_name = merge_strings(4, CLIENT_DIR, "/", domain, "/groups");

		// Adding group...
		file = fopen(group_file_name, "r+");
		if (!file) {
			fprintf(stderr, "FAILED ON FOPEN (%s)\n", group_file_name);
			result = FALSE;
			goto protect_create_user_end;
		}

		tmpf = fopen(tmp_file_name, "w+");
		if (!tmpf) {
			fclose(file);
			printf("Failed to open tmpf\n");
			result = FALSE;
			goto protect_create_user_end;
		}

		i = 0;
		while(!feof(file)) {
			buff = read_string(file);
			if (!buff)
				break;

			// group name
			tmp = get_word_by_number(buff, 1);

			if (!tmp) {
				if (i) fprintf(tmpf, "\n");
				fprintf(tmpf, "%s", buff);
				i++;
				if (buff) safe_free(&buff);
				continue;
			}

			tmp[strlen(tmp) - 1] = '\0';

			if (strcmp(group_name, tmp) == 0) {
				if (i) fprintf(tmpf, "\n");
				fprintf(tmpf, "%s %s", buff, user_name);
				i++;
				result = TRUE;
			}
			else {
				if (i) fprintf(tmpf, "\n");
				fprintf(tmpf, "%s", buff);
				i++;
			}

			if (buff) safe_free(&buff);
			if (tmp) safe_free(&tmp);
		}

		fclose(file);
		fclose(tmpf);

		if (unlink(group_file_name) == -1) {
			perror("Remove file:");
		}

		if (rename(tmp_file_name, group_file_name) == -1) {
			perror("Rename file:");
			result = FALSE;
			goto protect_create_user_end;
		}

		if (ROOT_COMPILE) {
			if (chown(group_file_name, APACHE_UID, HOSTING_GID) == -1) {
				perror("Chown:");
				result = FALSE;
				goto protect_create_user_end;
			}
		}
	}

protect_create_user_end:

	if (stat_rec)
		free(stat_rec);

	if (user_name) safe_free(&user_name);
	if (group_name) safe_free(&group_name);
	if (domain) safe_free(&domain);
	if (password) safe_free(&password);
	if (pass_file_name) safe_free(&pass_file_name);
	if (group_file_name) safe_free(&group_file_name);
	if (tmp_file_name) safe_free(&tmp_file_name);
	if (flags) safe_free(&flags);
	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);
	if (unique) safe_free(&unique);

	return result;
}

unsigned int protect_create_group(char * cmd)
{
	unsigned int result = FALSE;
	unsigned int file_exist = FALSE;

	char * domain = NULL;
	char * group_name = NULL;
	char * group_file_name = NULL;

	FILE * file = NULL;

	domain = get_word_by_number(cmd, 4);
	if (!domain) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET DOMAIN\n");
		goto protect_create_group_end;
	}

	group_name = get_word_by_number(cmd, 5);
	if (!group_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET GROUP_NAME\n");
		goto protect_create_group_end;
	}

	group_file_name = merge_strings(4, CLIENT_DIR, "/", domain, "/groups");

	// Check if file exist
	file = fopen(group_file_name, "r");
	if (file) {
		file_exist = TRUE;
		fclose(file);
	}

	// Adding group...
	file = fopen(group_file_name, "a");

	if (!file) {
		fprintf(stderr, "FAILED ON FOPEN (%s)\n", group_file_name);
		result = FALSE;
		goto protect_create_group_end;
	}

	if (file_exist == TRUE)
		fprintf(file, "\n%s:", group_name);
	else
		fprintf(file, "%s:", group_name);

	fclose(file);

	if (ROOT_COMPILE) {
		if (chown(group_file_name, APACHE_UID, HOSTING_GID) == -1) {
			perror("Chown:");
			result = FALSE;
			goto protect_create_group_end;
		}
	}

	result = TRUE;
protect_create_group_end:

	if (domain) safe_free(&domain);
	if (group_name) safe_free(&group_name);
	if (group_file_name) safe_free(&group_file_name);

	return result;
}

unsigned int protect_create_area(char * cmd)
{
	unsigned int result = FALSE;
	unsigned int len2 = 0;
	unsigned int i = 0;

	char * domain = NULL;
	char * area_name = NULL;
	char * object_type = NULL;
	char * object_path = NULL;
	char * object_file_path = NULL;
	char * method_type = NULL;
	char * method_name = NULL;

	char * htaccess_file_name = NULL;

	char * tmp = NULL;
	char * a1 = NULL;

	FILE * file = NULL;

	domain = get_word_by_number(cmd, 4);
	if (!domain) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET DOMAIN\n");
		goto protect_create_area_end;
	}

	area_name = get_word_by_number(cmd, 5);
	if (!area_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET AREA_NAME\n");
		goto protect_create_area_end;
	}

	object_type = get_word_by_number(cmd, 6);
	if (!object_type) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET OBJECT_TYPE\n");
		goto protect_create_area_end;
	}

	if (strcmp(object_type, "file") != 0 &&
		strcmp(object_type, "dir") != 0) {
		result = FALSE;
		fprintf(stderr, "FAILED ON CHECK OBJECT_TYPE\n");
		goto protect_create_area_end;
	}

	object_path = get_word_by_number(cmd, 7);
	if (!object_path) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET OBJECT_TYPE\n");
		goto protect_create_area_end;
	}

	method_type = get_word_by_number(cmd, 8);
	if (!method_type) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET METHOD_TYPE\n");
		goto protect_create_area_end;
	}

	if (strcmp(method_type, "user") != 0 &&
		strcmp(method_type, "group") != 0) {
		result = FALSE;
		fprintf(stderr, "FAILED ON CHECK METHOD_TYPE\n");
		goto protect_create_area_end;
	}

	method_name = get_word_by_number(cmd, 9);
	if (!method_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET METHOD_NAME\n");
		goto protect_create_area_end;
	}

	for (i = 0; i < strlen(area_name); i++) {
		if (area_name[i] == '+') {
			if (i != strlen(area_name) -1) {
				if (area_name[i+1] == '+') {
					i++;
					continue;
				}
			}
			area_name[i] = ' ';
		}
	}

	for (i = 0; i < strlen(area_name) - 1; i++) {
		if (area_name[i] == '+' && area_name[i+1] == '+') {
			tmp = strndup(area_name, i);
			tmp = sts(&tmp, area_name + sizeof(char) * (i+1));
			safe_free(&area_name);
			area_name = tmp;
		}
	}

	if (strcmp(object_type, "file") == 0) {

		// We need to ride filename
		// object_path is something like this:
		// 	/forum/old/new/index.html
		// so, we have to get "/forum/old/new"

		if (object_path[0] != '/' ) {
			result = FALSE;
			fprintf(stderr, "OBJECT_PATH DOESN'T HAVE LEADING /\n");
			goto protect_create_area_end;
		}

		a1 = strrchr(object_path, '/');

		if (!a1 || a1 == object_path) {
			tmp = strdup("/");
			a1 = object_path;
		}
		else {
			len2 = strlen(object_path) - strlen(a1);

			if (len2 < 1 ) {
				result = FALSE;
				fprintf(stderr, "FAILED ON CHECK A1\n");
				goto protect_create_area_end;
			}

			tmp = strndup(object_path, len2);

			if (!tmp) {
				result = FALSE;
				fprintf(stderr, "FAILED ON GET PURE PATH\n");
				goto protect_create_area_end;
			}
		}

		a1++;
		object_file_path = strdup(a1);

		if (!object_file_path) {
			result = FALSE;
			fprintf(stderr, "FAILED ON GET PURE FILE PATH\n");
			goto protect_create_area_end;
		}

		htaccess_file_name = merge_strings(7, CLIENT_DIR, "/", domain, "/www", tmp, "/", ".htaccess");

	}
	else if (strcmp(object_type, "dir") == 0) {

		htaccess_file_name = merge_strings(7, CLIENT_DIR, "/", domain, "/www", object_path, "/", ".htaccess");
	}

	// Adding access...
	file = fopen(htaccess_file_name, "a");

	if (!file) {
		fprintf(stderr, "FAILED ON FOPEN (%s)\n", htaccess_file_name);
		result = FALSE;
		goto protect_create_area_end;
	}

	if (strcmp(object_type, "file") == 0) {

		fprintf(file, "\n\
<Files %s>\n\
\tAuthType Basic\n\
\tAuthName \"%s\"",
object_file_path, area_name);

		if (strcmp(method_type, "user") == 0) {
			fprintf(file, "\n\
\tAuthUserFile %s/%s/passwords\n\
\tRequire user %s\n",
CLIENT_DIR, domain, method_name);
		}
		else if (strcmp(method_type, "group") == 0) {
			fprintf(file, "\n\
\tAuthUserFile %s/%s/passwords\n\
\tAuthGroupFile %s/%s/groups\n\
\tRequire group %s\n",
CLIENT_DIR, domain, CLIENT_DIR, domain, method_name);
		}

		fprintf(file, "</Files>\n");
	}

	else if (strcmp(object_type, "dir") == 0) {

		fprintf(file, "\n\
AuthType Basic\n\
AuthName \"%s\"",
area_name);

		if (strcmp(method_type, "user") == 0) {
			fprintf(file, "\n\
AuthUserFile %s/%s/passwords\n\
Require user %s\n",
CLIENT_DIR, domain, method_name);
		}
		else if (strcmp(method_type, "group") == 0) {
			fprintf(file, "\n\
AuthUserFile %s/%s/passwords\n\
AuthGroupFile %s/%s/groups\n\
Require group %s\n",
CLIENT_DIR, domain, CLIENT_DIR, domain, method_name);
		}
	}

	fclose(file);

	if (ROOT_COMPILE) {
		if (chown(htaccess_file_name, PROFTPD_UID, HOSTING_GID) == -1) {
			perror("Chown:");
			result = FALSE;
			goto protect_create_area_end;
		}
	}

	result = TRUE;
protect_create_area_end:

	if (domain) safe_free(&domain);
	if (area_name) safe_free(&area_name);
	if (htaccess_file_name) safe_free(&htaccess_file_name);
	if (object_type) safe_free(&object_type);
	if (object_path) safe_free(&object_path);
	if (object_file_path) safe_free(&object_file_path);
	if (method_type) safe_free(&method_type);
	if (method_name) safe_free(&method_name);
	if (tmp) safe_free(&tmp);

	return result;
}

unsigned int protect_update_user(char * cmd)
{
	unsigned int result = FALSE;

	char * domain = NULL;
	char * user_name = NULL;
	char * new_user_name = NULL;
	char * new_password = NULL;
	char * group_name = NULL;

	char * action_string = NULL;

	domain = get_word_by_number(cmd, 4);
	if (!domain) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET DOMAIN\n");
		goto protect_update_user_end;
	}

	user_name = get_word_by_number(cmd, 5);
	if (!user_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET USER_NAME\n");
		goto protect_update_user_end;
	}

	new_user_name = get_word_by_number(cmd, 6);
	if (!new_user_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET NEW_USER_NAME\n");
		goto protect_update_user_end;
	}

	new_password = get_word_by_number(cmd, 7);
	if (!new_password) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET NEW_PASSWORD\n");
		goto protect_update_user_end;
	}

	group_name = get_word_by_number(cmd, 8);

	action_string = merge_strings(4, "PRO DELETE USER ", domain, " ", user_name);
	fprintf(stderr, "ACTION1: %s\n", action_string);
	result = protect_remove_user(action_string);

	if (result == FALSE)
		goto protect_update_user_end;

	safe_free(&action_string);

	action_string = merge_strings(6, "PRO CREATE USER ", domain, " ", new_user_name, " ", new_password);
	if (group_name) {
		action_string = sts(&action_string, " ");
		action_string = sts(&action_string, group_name);
	}
	fprintf(stderr, "ACTION2: %s\n", action_string);
	result = protect_create_user(action_string);

protect_update_user_end:

	if (domain) safe_free(&domain);
	if (user_name) safe_free(&user_name);
	if (new_user_name) safe_free(&new_user_name);
	if (new_password) safe_free(&new_password);
	if (group_name) safe_free(&group_name);
	if (action_string) safe_free(&action_string);

	return result;
}

unsigned int protect_update_group(char * cmd)
{
	unsigned int result = FALSE;

	char * domain = NULL;
	char * group_name = NULL;
	char * new_group_name = NULL;

	char * action_string = NULL;

	domain = get_word_by_number(cmd, 4);
	if (!domain) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET DOMAIN\n");
		goto protect_update_group_end;
	}

	group_name = get_word_by_number(cmd, 5);
	if (!group_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET GROUP_NAME\n");
		goto protect_update_group_end;
	}

	new_group_name = get_word_by_number(cmd, 6);
	if (!new_group_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET NEW_GROUP_NAME\n");
		goto protect_update_group_end;
	}

	action_string = merge_strings(4, "PRO DELETE GROUP ", domain, " ", group_name);
	result = protect_remove_group(action_string);

	if (result == FALSE)
		goto protect_update_group_end;

	safe_free(&action_string);

	action_string = merge_strings(4, "PRO CREATE GROUP ", domain, " ", new_group_name);
	result = protect_create_group(action_string);

protect_update_group_end:

	if (domain) safe_free(&domain);
	if (group_name) safe_free(&group_name);
	if (new_group_name) safe_free(&new_group_name);
	if (action_string) safe_free(&action_string);

	return result;
}

unsigned int protect_update_area(char * cmd)
{
//PRO UPDATE AREA domain object-type object-path area-name new-area-name new-object-type new-object-path new-method-type new-method-name
	unsigned int result = FALSE;

	char * domain = NULL;
	char * object_type = NULL;
	char * object_path = NULL;
	char * new_area_name = NULL;
	char * new_object_type = NULL;
	char * new_object_path = NULL;
	char * new_method_type = NULL;
	char * new_method_name = NULL;

	char * action_string = NULL;

	domain = get_word_by_number(cmd, 4);
	if (!domain) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET DOMAIN\n");
		goto protect_update_area_end;
	}

	object_type = get_word_by_number(cmd, 5);
	if (!object_type) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET OBJECT_TYPE\n");
		goto protect_update_area_end;
	}

	if (strcmp(object_type, "file") != 0 &&
		strcmp(object_type, "dir") != 0) {
		result = FALSE;
		fprintf(stderr, "FAILED ON CHECK OBJECT_TYPE\n");
		goto protect_update_area_end;
	}

	object_path = get_word_by_number(cmd, 6);
	if (!object_path) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET OBJECT_TYPE\n");
		goto protect_update_area_end;
	}

	new_area_name = get_word_by_number(cmd, 7);
	if (!new_area_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET NEW_AREA_NAME\n");
		goto protect_update_area_end;
	}

	new_object_type = get_word_by_number(cmd, 8);
	if (!new_object_type) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET NEW_OBJECT_TYPE\n");
		goto protect_update_area_end;
	}

	if (strcmp(new_object_type, "file") != 0 &&
		strcmp(new_object_type, "dir") != 0) {
		result = FALSE;
		fprintf(stderr, "FAILED ON CHECK NEW_OBJECT_TYPE\n");
		goto protect_update_area_end;
	}

	new_object_path = get_word_by_number(cmd, 9);
	if (!new_object_path) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET NEW_OBJECT_TYPE\n");
		goto protect_update_area_end;
	}

	new_method_type = get_word_by_number(cmd, 10);
	if (!new_method_type) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET NEW_METHOD_TYPE\n");
		goto protect_update_area_end;
	}

	if (strcmp(new_method_type, "user") != 0 &&
		strcmp(new_method_type, "group") != 0) {
		result = FALSE;
		fprintf(stderr, "FAILED ON CHECK NEW_METHOD_TYPE\n");
		goto protect_update_area_end;
	}

	new_method_name = get_word_by_number(cmd, 11);
	if (!new_method_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET NEW_METHOD_NAME\n");
		goto protect_update_area_end;
	}


	action_string = merge_strings(6, "PRO DELETE AREA ", domain, " ", object_type, " ", object_path);
	fprintf(stderr, "ACTION1: %s\n", action_string);
	result = protect_remove_area(action_string);

	if (result == FALSE)
		goto protect_update_area_end;

	safe_free(&action_string);

	action_string = merge_strings(12, "PRO CREATE AREA ", domain, " ", new_area_name, " ", new_object_type, " ", new_object_path, " ", new_method_type, " ", new_method_name);
	fprintf(stderr, "ACTION2: %s\n", action_string);
	result = protect_create_area(action_string);

protect_update_area_end:

	if (domain) safe_free(&domain);
	if (object_type) safe_free(&object_type);
	if (object_path) safe_free(&object_path);
	if (new_area_name) safe_free(&new_area_name);
	if (new_object_type) safe_free(&new_object_type);
	if (new_object_path) safe_free(&new_object_path);
	if (new_method_type) safe_free(&new_method_type);
	if (new_method_name) safe_free(&new_method_name);

	if (action_string) safe_free(&action_string);

	return result;
}

unsigned int protect_remove_user(char * cmd)
{
	unsigned int result = FALSE;
	unsigned int i, k;
	unsigned int count;
	char * unique = NULL;

	char * domain = NULL;
	char * user_name = NULL;

	char * pass_file_name = NULL;
	char * group_file_name = NULL;
	char * tmp_file_name = NULL;

	char * buff = NULL;
	char * tmp = NULL;
	char * new_line = NULL;

	FILE * file = NULL;
	FILE * tmpf = NULL;

	domain = get_word_by_number(cmd, 4);
	if (!domain) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET DOMAIN\n");
		goto protect_remove_user_end;
	}

	user_name = get_word_by_number(cmd, 5);
	if (!user_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET USER_NAME\n");
		goto protect_remove_user_end;
	}

	unique = create_unique_id();

	pass_file_name = merge_strings(4, CLIENT_DIR, "/", domain, "/passwords");
	group_file_name = merge_strings(4, CLIENT_DIR, "/", domain, "/groups");
	tmp_file_name = merge_strings(5, CLIENT_DIR, "/", domain, "/", unique);

	// Removing old user...

	file = fopen(pass_file_name, "r");

	if (!file) {
		fprintf(stderr, "FAILED ON FOPEN (%s)\n", pass_file_name);
		result = FALSE;
		goto protect_remove_user_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(file);
		printf("Failed to open tmpf\n");
		result = FALSE;
		goto protect_remove_user_end;
	}

	while(!feof(file)) {
		buff = read_string(file);
		if (!buff)
			break;

		if (strstr(buff, user_name) != buff) {
			fprintf(tmpf, "%s\n", buff);
		}


		if (buff) safe_free(&buff);
	}

	fclose(file);
	fclose(tmpf);

	if (unlink(pass_file_name) == -1) {
		perror("Remove file:");
	}

	if (rename(tmp_file_name, pass_file_name) == -1) {
		perror("Rename file:");
		result = FALSE;
		goto protect_remove_user_end;
	}

	if (ROOT_COMPILE) {
		if (chown(pass_file_name, APACHE_UID, HOSTING_GID) == -1) {
			perror("Chown:");
			result = FALSE;
			goto protect_remove_user_end;
		}
	}

	// Next, we will look up user in group file

	safe_free(&unique);
	safe_free(&tmp_file_name);

	unique = create_unique_id();
	tmp_file_name = merge_strings(5, CLIENT_DIR, "/", domain, "/", unique);

	file = fopen(group_file_name, "r");

	if (!file) {
		result = TRUE;
		goto protect_remove_user_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(file);
		printf("Failed to open tmpf\n");
		result = FALSE;
		goto protect_remove_user_end;
	}

	i = 0;
	while(!feof(file)) {
		buff = read_string(file);
		if (!buff)
			break;

		count = how_much_words(buff);

		if (count < 2) {
			if (i) fprintf(tmpf, "\n");
			fprintf(tmpf, "%s", buff);
			safe_free(&buff);
			i++;
			continue;
		}

		for (k = 1; k <= count; k++) {
			tmp = get_word_by_number(buff, k);

			if (!tmp) {
				if (i) fprintf(tmpf, "\n");
				fprintf(tmpf, "%s", buff);
				safe_free(&buff);
				i++;
				continue;
			}

			if (strcmp(tmp, user_name) != 0) {
				if (new_line)
					new_line = sts(&new_line, " ");

				new_line = sts(&new_line, tmp);
			}
		}


		if (i) fprintf(tmpf, "\n");
		fprintf(tmpf, "%s", new_line);
		i++;

		if (buff) safe_free(&buff);
		if (tmp) safe_free(&tmp);
		if (new_line) safe_free(&new_line);
	}

	fclose(file);
	fclose(tmpf);

	if (unlink(group_file_name) == -1) {
		perror("Remove file:");
	}

	if (rename(tmp_file_name, group_file_name) == -1) {
		perror("Rename file:");
		result = FALSE;
		goto protect_remove_user_end;
	}

	if (ROOT_COMPILE) {
		if (chown(group_file_name, APACHE_UID, HOSTING_GID) == -1) {
			perror("Chown:");
			result = FALSE;
			goto protect_remove_user_end;
		}
	}

	result = TRUE;

protect_remove_user_end:

	if (domain) safe_free(&domain);
	if (user_name) safe_free(&user_name);
	if (unique) safe_free(&unique);
	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);
	if (new_line) safe_free(&new_line);

	if (group_file_name) safe_free(&group_file_name);
	if (pass_file_name) safe_free(&pass_file_name);
	if (tmp_file_name) safe_free(&tmp_file_name);

	return result;
}

unsigned int protect_remove_group(char * cmd)
{
	unsigned int result = FALSE;
	unsigned int i = FALSE;
	char * unique = NULL;

	char * domain = NULL;
	char * group_name = NULL;

	char * group_file_name = NULL;
	char * tmp_file_name = NULL;

	char * buff = NULL;
	char * tmp = NULL;

	FILE * file = NULL;
	FILE * tmpf = NULL;

	domain = get_word_by_number(cmd, 4);
	if (!domain) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET DOMAIN\n");
		goto protect_remove_group_end;
	}

	group_name = get_word_by_number(cmd, 5);
	if (!group_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET GROUP_NAME\n");
		goto protect_remove_group_end;
	}

	unique = create_unique_id();

	group_file_name = merge_strings(4, CLIENT_DIR, "/", domain, "/groups");
	tmp_file_name = merge_strings(5, CLIENT_DIR, "/", domain, "/", unique);

	// Removing old group...
	file = fopen(group_file_name, "r");

	if (!file) {
		fprintf(stderr, "FAILED ON FOPEN (%s)\n", group_file_name);
		result = FALSE;
		goto protect_remove_group_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(file);
		printf("Failed to open tmpf\n");
		result = FALSE;
		goto protect_remove_group_end;
	}

	i = 0;
	while(!feof(file)) {
		buff = read_string(file);
		if (!buff)
			break;

		tmp = get_word_by_number(buff, 1);

		if (!tmp) {
			if (i) fprintf(tmpf, "\n");
			fprintf(tmpf, "%s", buff);
			if (buff) safe_free(&buff);
			i++;
			continue;
		}

		tmp[strlen(tmp) - 1] = '\0';

		if (strcmp(tmp, group_name) != 0) {
			if (i) fprintf(tmpf, "\n");
			fprintf(tmpf, "%s", buff);
			i++;
		}

		if (buff) safe_free(&buff);
		if (tmp) safe_free(&tmp);
	}

	fclose(file);
	fclose(tmpf);

	if (unlink(group_file_name) == -1) {
		perror("Remove file:");
	}

	if (rename(tmp_file_name, group_file_name) == -1) {
		perror("Rename file:");
		result = FALSE;
		goto protect_remove_group_end;
	}

	if (ROOT_COMPILE) {
		if (chown(group_file_name, APACHE_UID, HOSTING_GID) == -1) {
			perror("Chown:");
			result = FALSE;
			goto protect_remove_group_end;
		}
	}

	result = TRUE;
protect_remove_group_end:

	if (domain) safe_free(&domain);
	if (group_name) safe_free(&group_name);
	if (unique) safe_free(&unique);
	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);

	if (group_file_name) safe_free(&group_file_name);
	if (tmp_file_name) safe_free(&tmp_file_name);

	return result;
}

unsigned int protect_remove_area(char * cmd)
{
	unsigned int result = FALSE;
	unsigned int len2 = 0;
	unsigned int in_section = FALSE;
	unsigned int area_delete = FALSE;
	unsigned int comp = FALSE;
	char * unique = NULL;

	char * domain = NULL;
	char * object_type = NULL;
	char * object_path = NULL;
	char * object_file_path = NULL;

	char * htaccess_file_name = NULL;
	char * tmp_file_name = NULL;

	char * buff = NULL;
	char * tmp = NULL;
	char * tmp2 = NULL;
	char * a1 = NULL;

	FILE * file = NULL;
	FILE * tmpf = NULL;

	domain = get_word_by_number(cmd, 4);
	if (!domain) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET DOMAIN\n");
		goto protect_remove_area_end;
	}

	object_type = get_word_by_number(cmd, 5);
	if (!object_type) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET OBJECT_TYPE\n");
		goto protect_remove_area_end;
	}

	if (strcmp(object_type, "file") != 0 &&
		strcmp(object_type, "dir") != 0) {
		result = FALSE;
		fprintf(stderr, "FAILED ON CHECK OBJECT_TYPE\n");
		goto protect_remove_area_end;
	}

	object_path = get_word_by_number(cmd, 6);
	if (!object_path) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET OBJECT_TYPE\n");
		goto protect_remove_area_end;
	}

	unique = create_unique_id();

	if (strcmp(object_type, "file") == 0) {

		// We need to ride filename
		// object_path is something like this:
		// 	/forum/old/new/index.html
		// so, we have to get "/forum/old/new"

		if (object_path[0] != '/' ) {
			result = FALSE;
			fprintf(stderr, "OBJECT_PATH DOESN'T HAVE LEADING /\n");
			goto protect_remove_area_end;
		}

		a1 = strrchr(object_path, '/');

		if (!a1 || a1 == object_path) {
			tmp = strdup("/");
			a1 = object_path;
		}
		else {
			len2 = strlen(object_path) - strlen(a1);

			if (len2 < 1 ) {
				result = FALSE;
				fprintf(stderr, "FAILED ON CHECK A1\n");
				goto protect_remove_area_end;
			}

			tmp = strndup(object_path, len2);

			if (!tmp) {
				result = FALSE;
				fprintf(stderr, "FAILED ON GET PURE PATH\n");
				goto protect_remove_area_end;
			}
		}

		a1++;
		object_file_path = strdup(a1);

		if (!object_file_path) {
			result = FALSE;
			fprintf(stderr, "FAILED ON GET PURE FILE PATH\n");
			goto protect_remove_area_end;
		}

		htaccess_file_name = merge_strings(7, CLIENT_DIR, "/", domain, "/www", tmp, "/", ".htaccess");
		tmp_file_name = merge_strings(7, CLIENT_DIR, "/", domain, "/www", tmp, "/", unique);
	}
	else if (strcmp(object_type, "dir") == 0) {

		htaccess_file_name = merge_strings(7, CLIENT_DIR, "/", domain, "/www", object_path, "/", ".htaccess");
		tmp_file_name = merge_strings(7, CLIENT_DIR, "/", domain, "/www", object_path, "/", unique);
	}

	object_file_path = add_char_to_string(object_file_path, '>');

	file = fopen(htaccess_file_name, "r");

	if (!file) {
		// Maybe user allready deleted himself
		// this file ? - anyway, result is TRUE
		result = TRUE;
		goto protect_remove_area_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(file);
		printf("Failed to open tmpf (%s)\n", tmp_file_name);
		result = FALSE;
		goto protect_remove_area_end;
	}

	if (strcmp(object_type, "file") == 0) {
		in_section = FALSE;
		while (!feof(file)) {
			buff = read_string(file);
			if (!buff)
				break;

			tmp = get_word_by_number(buff, 1);

			if (!tmp) {
				fprintf(tmpf, "%s\n", buff);
				if (buff) safe_free(&buff);
				continue;
			}

			// Searching <Files> begining...
			if (in_section == FALSE) {
				comp = 0;
				tmp2 = get_word_by_number(buff, 2);

				if (tmp2) {
					tmp2 = cut_quotes(tmp2);

					if (strcmp(tmp, "<Files") == 0)
						comp++;

					if (strcmp(tmp2, object_file_path) == 0)
						comp++;

					// Check if we come into Files section
					if (comp == 2) {
						in_section = TRUE;
						area_delete = TRUE;
					}
				}

				// We still outside of section ?
				if (in_section == FALSE) {
					// Print \n if section begin
					if (strcmp(tmp, "<Files") == 0)
						fprintf(tmpf, "\n");
					fprintf(tmpf, "%s\n", buff);
				}

				// Print \n if section end
				if (strcmp(tmp, "</Files>") == 0)
					fprintf(tmpf, "\n");
			}

			// Searching end of the section...
			else if (in_section == TRUE) {
				if (strcmp(tmp, "</Files>") == 0)
					in_section = FALSE;
			}

			if (buff) safe_free(&buff);
			if (tmp) safe_free(&tmp);
			if (tmp2) safe_free(&tmp2);
		}
	}
	else if (strcmp(object_type, "dir") == 0) {
		in_section = FALSE;
		while (!feof(file)) {
			buff = read_string(file);
			if (!buff)
				break;

			tmp = get_word_by_number(buff, 1);

			if (!tmp) {
				fprintf(tmpf, "%s\n", buff);
				if (buff) safe_free(&buff);
				continue;
			}

			// Searching <Files> begining...
			if (in_section == FALSE) {
				if (strcmp(tmp, "<Files") == 0) {
					in_section = TRUE;
				}
				else {
					area_delete = TRUE;
				}

				// Are we inside of section ?
				if (in_section == TRUE) {
					fprintf(tmpf, "%s\n", buff);
				}
			}

			// Searching end of the section...
			else if (in_section == TRUE) {

				fprintf(tmpf, "%s\n", buff);

				if (strcmp(tmp, "</Files>") == 0) {
					in_section = FALSE;
					fprintf(tmpf, "\n");
				}
			}

			if (buff) safe_free(&buff);
			if (tmp) safe_free(&tmp);
		}
	}

	fclose(file);
	fclose(tmpf);

	if (unlink(htaccess_file_name) == -1) {
		perror("Remove file:");
	}

	if (rename(tmp_file_name, htaccess_file_name) == -1) {
		perror("Rename file:");
		result = FALSE;
		goto protect_remove_area_end;
	}

	if (ROOT_COMPILE) {
		if (chown(htaccess_file_name, PROFTPD_UID, HOSTING_GID) == -1) {
			perror("Chown:");
			result = FALSE;
			goto protect_remove_area_end;
		}
	}

	result = area_delete;
protect_remove_area_end:

	if (domain) safe_free(&domain);
	if (object_type) safe_free(&object_type);
	if (object_path) safe_free(&object_path);
	if (object_file_path) safe_free(&object_file_path);
	if (htaccess_file_name) safe_free(&htaccess_file_name);
	if (tmp_file_name) safe_free(&tmp_file_name);
	if (unique) safe_free(&unique);
	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);
	if (tmp2) safe_free(&tmp2);

	return result;
}

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

#include <arpa/inet.h>
#include <unistd.h>
#include "errno.h"

#include "define.h"
#include "dialog.h"
#include "str.h"
#include "link.h"
#include "tools.h"
#include "pwd.h"

using namespace std;

extern char * return_message;

char * get_free_uid()
{
	unsigned int c = 0, len = 0, f = 0;
	char * result = NULL;
	char * buff = NULL;
	char * tmp = NULL;

	Spisok <unsigned int> P;

	FILE * fp = NULL;

	fp = fopen(PASSWD_PATH, "r");

	if (!fp) {
		fprintf(stderr, "Failed to open passwd file for reading\n");
		return NULL;
	}

	while(!feof(fp)) {

		buff = read_string(fp);

		if (!buff)
			break;

		c = 0;
		len = 0;
		tmp = buff;

		while (buff) {
			if (buff[0] == ':')
				c++;

			buff++;

			if (c == 2)
				break;
		}

		while (buff[len] && buff[len] != ':')
			len++;

		result = strndup(buff, len);

		if (atoi(result) > PWD_UID_MIN)
			P.Add(atoi(result));

		safe_free(&tmp);
		safe_free(&result);
	}

	fclose(fp);

	len = PWD_UID_MIN + 1;

	while(1) {
		f = 0;

		for (c = 0; c < P.Count; c++)
			if (len == P[c]->a) {
				f = 1;
				break;
			}

		if (!f)
			break;

		len++;
	}

	while(P.Count)
		P.Delete(P.Count);

	result = int_to_string(len);

	return result;
}

char * get_free_gid()
{
	unsigned int c = 0, len = 0, f = 0;
	char * result = NULL;
	char * buff = NULL;
	char * tmp = NULL;

	Spisok <unsigned int> P;

	FILE * fp = NULL;

	fp = fopen(GROUP_PATH, "r");

	if (!fp) {
		fprintf(stderr, "Failed to open group file for reading\n");
		return NULL;
	}

	while(!feof(fp)) {

		buff = read_string(fp);

		if (!buff)
			break;

		c = 0;
		len = 0;
		tmp = buff;

		while (buff) {
			if (buff[0] == ':')
				c++;

			buff++;

			if (c == 2)
				break;
		}

		while (buff[len] && buff[len] != ':')
			len++;

		result = strndup(buff, len);

		if (atoi(result) > PWD_GID_MIN)
			P.Add(atoi(result));

		safe_free(&tmp);
		safe_free(&result);
	}

	fclose(fp);

	len = PWD_GID_MIN + 1;

	while(1) {
		f = 0;

		for (c = 0; c < P.Count; c++)
			if (len == P[c]->a) {
				f = 1;
				break;
			}

		if (!f)
			break;

		len++;
	}

	while(P.Count)
		P.Delete(P.Count);

	result = int_to_string(len);

	return result;
}

unsigned int pwd_manage(char *cmd)
{
	unsigned int result = FALSE;
	char * act = NULL;

	act = get_word_by_number(cmd, 2);

	if (!act)
		return FALSE;

	if (strcmp(act, "CREATE") == 0) {
		if (pwd_create_manage(cmd) == FALSE) {
			result = FALSE;
			goto pwd_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "DELETE") == 0) {
		if (pwd_remove_manage(cmd) == FALSE) {
			result = FALSE;
			goto pwd_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "UPDATE") == 0) {
		if (pwd_update_manage(cmd) == FALSE) {
			result = FALSE;
			goto pwd_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "INFO") == 0) {
		if (pwd_info_manage(cmd) == FALSE) {
			result = FALSE;
			goto pwd_end;
		}
		result = TRUE;
	}

pwd_end:

	if (act) safe_free(&act);

	return result;
}

unsigned int pwd_create_manage(char *cmd)
{
	unsigned int result = FALSE;

	char * login = NULL;
	char * uid = NULL;
	char * gid = NULL;
	char * tmp = NULL;

	char * passwd_string = NULL;
	char * group_string = NULL;
	char * shadow_string = NULL;

	FILE * fp = NULL;

	login = get_word_by_number(cmd, 3);

	if (!login) {
		fprintf(stderr,"FAILED ON GET PARAMS\n");
		return FALSE;
	}

	uid = get_free_uid();
	gid = get_free_gid();

	if (!uid || !gid) {
		fprintf(stderr, "Failed to get uid or gid\n");
		result = FALSE;
		goto pwd_create_end;
	}

	passwd_string = merge_strings(6, login, ":x:", uid, ":", gid ,":,,,:/dummy:/bin/false");
	group_string = merge_strings(4, login, "::", gid ,":");
	shadow_string = merge_strings(2, login, ":*:9797:0:::::");

	fprintf(stderr, "Add to passwd: %s \n", passwd_string);
	fprintf(stderr, "Add to group: %s \n", group_string);
	fprintf(stderr, "Add to shadow: %s \n", shadow_string);

	fp = fopen(PASSWD_PATH, "a");
	if (!fp) {
		fprintf(stderr, "Failed to open passwd file for writing\n");
		result = FALSE;
		goto pwd_create_end;
	}
	fprintf(fp, "%s\n", passwd_string);
	fclose(fp);

	fp = fopen(GROUP_PATH, "a");
	if (!fp) {
		fprintf(stderr, "Failed to open group file for writing\n");
		result = FALSE;
		goto pwd_create_end;
	}
	fprintf(fp, "%s\n", group_string);
	fclose(fp);

	fp = fopen(SHADOW_PATH, "a");
	if (!fp) {
		fprintf(stderr, "Failed to open shadow file for writing\n");
		result = FALSE;
		goto pwd_create_end;
	}
	fprintf(fp, "%s\n", shadow_string);
	fclose(fp);

	return_message = merge_strings(3, uid, " ", gid);
	tmp  = return_message;
	return_message = base64_encode(tmp, strlen(tmp));
	safe_free(&tmp);

	result = TRUE;

pwd_create_end:

	if (login) safe_free(&login);
	if (uid) safe_free(&uid);
	if (gid) safe_free(&gid);
	if (passwd_string) safe_free(&passwd_string);
	if (group_string) safe_free(&group_string);
	if (shadow_string) safe_free(&shadow_string);

	return result;
}

unsigned int pwd_update_manage(char *cmd)
{
	unsigned int result = FALSE;
	unsigned int done = FALSE;

	char * login = NULL;

	char * new_login = NULL;
	char * new_uid = NULL;
	char * new_gid = NULL;

	char * passwd_string = NULL;
	char * group_string = NULL;
	char * shadow_string = NULL;

	char * unique = NULL;
	char * buff = NULL;
	char * tmp = NULL;
	char * temp_file_name = NULL;

	FILE * file = NULL;
	FILE * tmpf = NULL;

	login = get_word_by_number(cmd, 3);
	new_login = get_word_by_number(cmd, 4);
	new_uid = get_word_by_number(cmd, 5);
	new_gid = get_word_by_number(cmd, 6);

	if (!login || !new_login || !new_uid || !new_gid) {
		fprintf(stderr,"FAILED ON GET PARAMS\n");
		result = FALSE;
		goto pwd_update_end;
	}

	passwd_string = merge_strings(6, new_login, ":x:", new_uid, ":", new_gid ,":,,,:/dummy:/bin/false");
	group_string = merge_strings(4, new_login, "::", new_gid ,":");
	shadow_string = merge_strings(2, new_login, ":*:9797:0:::::");

	fprintf(stderr, "Update passwd: %s \n", passwd_string);
	fprintf(stderr, "Update group: %s \n", group_string);
	fprintf(stderr, "Update shadow: %s \n", shadow_string);

	unique = create_unique_id();
	temp_file_name = merge_strings(3, SYS_ETC_PATH, "/", unique);

	// PASSWD

	tmpf = fopen(temp_file_name, "w");
	if (!tmpf) {
		fprintf(stderr, "Failed to open temp file (for passwd) for writing\n");
		result = FALSE;
		goto pwd_update_end;
	}

	file = fopen(PASSWD_PATH, "r");
	if (!file) {
		fprintf(stderr, "Failed to open passwd file for reading\n");
		result = FALSE;
		goto pwd_update_end;
	}

	tmp = merge_strings(2, login, ":x:");
	done = FALSE;

	while(!feof(file)) {
		buff = read_string(file);

		if (!buff)
			break;

		if (strstr(buff, tmp) != buff)
			fprintf(tmpf, "%s\n", buff);
		else {
			fprintf(tmpf, "%s\n", passwd_string);
			done = TRUE;
		}

		if (buff) safe_free(&buff);
	}

	if (tmp) safe_free(&tmp);

	if (file) { fclose(file); file = NULL; }
	if (tmpf) { fclose(tmpf); tmpf = NULL; }

	if (rename(temp_file_name, PASSWD_PATH) == -1) {
		perror("Rename passwd file:");
		result = FALSE;
		goto pwd_update_end;
	}

	if (done == FALSE) {
		fprintf(stderr, "Not found old (passwd) record\n");
		result = FALSE;
		goto pwd_update_end;
	}

	// GROUP

	tmpf = fopen(temp_file_name, "w");
	if (!tmpf) {
		fprintf(stderr, "Failed to open temp file (for group) for writing\n");
		result = FALSE;
		goto pwd_update_end;
	}

	file = fopen(GROUP_PATH, "r");
	if (!file) {
		fprintf(stderr, "Failed to open group file for reading\n");
		result = FALSE;
		goto pwd_update_end;
	}

	tmp = merge_strings(2, login, "::");
	done = FALSE;

	while(!feof(file)) {
		buff = read_string(file);

		if (!buff)
			break;

		if (strstr(buff, tmp) != buff)
			fprintf(tmpf, "%s\n", buff);
		else {
			fprintf(tmpf, "%s\n", group_string);
			done = TRUE;
		}

		if (buff) safe_free(&buff);
	}

	if (tmp) safe_free(&tmp);

	if (file) { fclose(file); file = NULL; }
	if (tmpf) { fclose(tmpf); tmpf = NULL; }

	if (rename(temp_file_name, GROUP_PATH) == -1) {
		perror("Rename group file:");
		result = FALSE;
		goto pwd_update_end;
	}

	if (done == FALSE) {
		fprintf(stderr, "Not found old (group) record\n");
		result = FALSE;
		goto pwd_update_end;
	}

	// SHADOW

	tmpf = fopen(temp_file_name, "w");
	if (!tmpf) {
		fprintf(stderr, "Failed to open temp file (for shadow) for writing\n");
		result = FALSE;
		goto pwd_update_end;
	}

	file = fopen(SHADOW_PATH, "r");
	if (!file) {
		fprintf(stderr, "Failed to open shadow file for reading\n");
		result = FALSE;
		goto pwd_update_end;
	}

	tmp = merge_strings(2, login, ":*:9797:0:::::");
	done = FALSE;

	while(!feof(file)) {
		buff = read_string(file);

		if (!buff)
			break;

		if (strstr(buff, tmp) != buff)
			fprintf(tmpf, "%s\n", buff);
		else {
			fprintf(tmpf, "%s\n", shadow_string);
			done = TRUE;
		}

		if (buff) safe_free(&buff);
	}

	if (tmp) safe_free(&tmp);

	if (file) { fclose(file); file = NULL; }
	if (tmpf) { fclose(tmpf); tmpf = NULL; }

	if (rename(temp_file_name, SHADOW_PATH) == -1) {
		perror("Rename shadow file:");
		result = FALSE;
		goto pwd_update_end;
	}

	if (done == FALSE) {
		fprintf(stderr, "Not found old (shadow) record\n");
		result = FALSE;
		goto pwd_update_end;
	}

	result = TRUE;

pwd_update_end:

	if (login) safe_free(&login);
	if (new_login) safe_free(&new_login);
	if (new_uid) safe_free(&new_uid);
	if (new_gid) safe_free(&new_gid);
	if (passwd_string) safe_free(&passwd_string);
	if (group_string) safe_free(&group_string);
	if (shadow_string) safe_free(&shadow_string);

	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);
	if (unique) safe_free(&unique);
	if (temp_file_name) safe_free(&temp_file_name);

	return result;
}

unsigned int pwd_remove_manage(char *cmd)
{
	unsigned int result = FALSE;
	unsigned int done = FALSE;

	char * login = NULL;

	char * unique = NULL;
	char * buff = NULL;
	char * tmp = NULL;
	char * temp_file_name = NULL;

	FILE * file = NULL;
	FILE * tmpf = NULL;

	login = get_word_by_number(cmd, 3);

	if (!login) {
		fprintf(stderr,"FAILED ON GET LOGIN\n");
		result = FALSE;
		goto pwd_remove_end;
	}

	unique = create_unique_id();
	temp_file_name = merge_strings(3, SYS_ETC_PATH, "/", unique);

	// PASSWD

	tmpf = fopen(temp_file_name, "w");
	if (!tmpf) {
		fprintf(stderr, "Failed to open temp file (for passwd) for writing\n");
		result = FALSE;
		goto pwd_remove_end;
	}

	file = fopen(PASSWD_PATH, "r");
	if (!file) {
		fprintf(stderr, "Failed to open passwd file for reading\n");
		result = FALSE;
		goto pwd_remove_end;
	}

	tmp = merge_strings(2, login, ":x:");
	done = FALSE;

	while(!feof(file)) {
		buff = read_string(file);

		if (!buff)
			break;

		if (strstr(buff, tmp) != buff)
			fprintf(tmpf, "%s\n", buff);
		else
			done = TRUE;


		if (buff) safe_free(&buff);
	}

	if (tmp) safe_free(&tmp);

	if (file) { fclose(file); file = NULL; }
	if (tmpf) { fclose(tmpf); tmpf = NULL; }

	if (rename(temp_file_name, PASSWD_PATH) == -1) {
		perror("Rename passwd file:");
		result = FALSE;
		goto pwd_remove_end;
	}

	if (done == FALSE) {
		fprintf(stderr, "Not found old (passwd) record\n");
		result = FALSE;
		goto pwd_remove_end;
	}

	// GROUP

	tmpf = fopen(temp_file_name, "w");
	if (!tmpf) {
		fprintf(stderr, "Failed to open temp file (for group) for writing\n");
		result = FALSE;
		goto pwd_remove_end;
	}

	file = fopen(GROUP_PATH, "r");
	if (!file) {
		fprintf(stderr, "Failed to open group file for reading\n");
		result = FALSE;
		goto pwd_remove_end;
	}

	tmp = merge_strings(2, login, "::");
	done = FALSE;

	while(!feof(file)) {
		buff = read_string(file);

		if (!buff)
			break;

		if (strstr(buff, tmp) != buff)
			fprintf(tmpf, "%s\n", buff);
		else
			done = TRUE;

		if (buff) safe_free(&buff);
	}

	if (tmp) safe_free(&tmp);

	if (file) { fclose(file); file = NULL; }
	if (tmpf) { fclose(tmpf); tmpf = NULL; }

	if (rename(temp_file_name, GROUP_PATH) == -1) {
		perror("Rename group file:");
		result = FALSE;
		goto pwd_remove_end;
	}

	if (done == FALSE) {
		fprintf(stderr, "Not found old (group) record\n");
		result = FALSE;
		goto pwd_remove_end;
	}

	// SHADOW

	tmpf = fopen(temp_file_name, "w");
	if (!tmpf) {
		fprintf(stderr, "Failed to open temp file (for shadow) for writing\n");
		result = FALSE;
		goto pwd_remove_end;
	}

	file = fopen(SHADOW_PATH, "r");
	if (!file) {
		fprintf(stderr, "Failed to open shadow file for reading\n");
		result = FALSE;
		goto pwd_remove_end;
	}

	tmp = merge_strings(2, login, ":*:9797:0:::::");
	done = FALSE;

	while(!feof(file)) {
		buff = read_string(file);

		if (!buff)
			break;

		if (strstr(buff, tmp) != buff)
			fprintf(tmpf, "%s\n", buff);
		else
			done = TRUE;

		if (buff) safe_free(&buff);
	}

	if (tmp) safe_free(&tmp);

	if (file) { fclose(file); file = NULL; }
	if (tmpf) { fclose(tmpf); tmpf = NULL; }

	if (rename(temp_file_name, SHADOW_PATH) == -1) {
		perror("Rename shadow file:");
		result = FALSE;
		goto pwd_remove_end;
	}

	if (done == FALSE) {
		fprintf(stderr, "Not found old (shadow) record\n");
		result = FALSE;
		goto pwd_remove_end;
	}

	result = TRUE;

pwd_remove_end:

	if (login) safe_free(&login);

	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);
	if (unique) safe_free(&unique);
	if (temp_file_name) safe_free(&temp_file_name);

	return result;
}

unsigned int pwd_info_manage(char *cmd)
{
	unsigned int result = FALSE;
	unsigned int len = 0;

	char * buff = NULL;
	char * copy = NULL;
	char * tmp = NULL;
	char * str = NULL;

	FILE * file = NULL;

	file = fopen(PASSWD_PATH, "r");
	if (!file) {
		fprintf(stderr, "Failed to open passwd file for reading\n");
		result = FALSE;
		goto pwd_info_end;
	}

	while(!feof(file)) {
		buff = read_string(file);

		if (!buff)
			break;

		copy = buff;

		// get login
		len = 0;
		while (buff[len] && buff[len] != ':')
			len++;
		str = strndup(buff, len);
		buff += sizeof(char) * (len+1);

		// skip ":x:"
		len = 0;
		while (buff[len] && buff[len] != ':')
			len++;
		buff += sizeof(char) * (len+1);

		// get uid
		len = 0;
		while (buff[len] && buff[len] != ':')
			len++;
		tmp = strndup(buff, len);
		buff += sizeof(char) * (len+1);
		str = sts(&str, " ");
		str = sts(&str, tmp);
		if (tmp) safe_free(&tmp);

		// get gid
		len = 0;
		while (buff[len] && buff[len] != ':')
			len++;
		tmp = strndup(buff, len);
		buff += sizeof(char) * (len+1);
		str = sts(&str, " ");
		str = sts(&str, tmp);
		if (tmp) safe_free(&tmp);

		if (return_message)
			return_message = sts(&return_message, "\n");

		return_message = sts(&return_message, str);

		if (copy) safe_free(&copy);
		buff = NULL;
		if (str) safe_free(&str);
	}

	if (tmp) safe_free(&tmp);
	if (copy) safe_free(&copy);

	if (file) { fclose(file); file = NULL; }

	result = TRUE;

	tmp  = return_message;
	return_message = base64_encode(tmp, strlen(tmp));
	safe_free(&tmp);

pwd_info_end:

	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);

	return result;
}

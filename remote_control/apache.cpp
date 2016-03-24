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

#include "define.h"
#include "str.h"
#include "apache.h"
#include "tools.h"

using namespace std;

extern char * return_message;

/*

				Command Syntax

		------------------------------------------------

Create new vhost entry:
APACHE CREATE [zone_name] [cgi_perl_enable] [ssi_enable]

Update vhost:
APACHE UPDATE [old_zone_name] [new_zone_name] [new_cgi_perl_enable] [new_ssi_enable]

Remove vhost:
APACHE DELETE [zone_name]

Create new webdir entry:
	system_mode:
		APACHE CREATE_WEBDIR system [web_dir_name] [root_dir]
	client_mode:
		APACHE CREATE_WEBDIR client [zone_name] [web_dir_name] [cgi_perl_enable] [ssi_enable] [root_dir]

Update webdir:
	system_mode:
		APACHE UPDATE_WEBDIR system [old_web_dir_name] and then look for `Create new webdir entry`
	client_mode:
		APACHE UPDATE_WEBDIR client [old_zone_name] [old_web_dir_name]  and then look for `Create new webdir entry`

Remove webdir:
	system_mode:
		APACHE DELETE_WEBDIR system [web_dir_name]
	client mode:
		APACHE DELETE_WEBDIR client [zone_name] [web_dir_name]

--------------------------------

Note: 'web_dir_name' just subdirectory inside zone's www-dir

*/

unsigned int apache_manage(char *cmd)
{
	unsigned int result = FALSE;
	int fork_res;
	char * act = NULL;

	act = get_word_by_number(cmd, 2);

	if (!act)
		return FALSE;

	if (strcmp(act, "CREATE") == 0) {
		if (create_vhost(cmd) == FALSE) {
			result = FALSE;
			goto apache_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "UPDATE") == 0) {
		if (update_vhost(cmd) == FALSE) {
			result = FALSE;
			goto apache_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "DELETE") == 0) {
		if (remove_vhost(cmd) == FALSE) {
			result = FALSE;
			goto apache_end;
		}
		result = TRUE;
	}

	if (strcmp(act, "CREATE_WEBDIR") == 0) {
		if (create_webdir(cmd) == FALSE) {
			result = FALSE;
			goto apache_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "UPDATE_WEBDIR") == 0) {
		if (update_webdir(cmd) == FALSE) {
			result = FALSE;
			goto apache_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "DELETE_WEBDIR") == 0) {
		if (remove_webdir(cmd) == FALSE) {
			result = FALSE;
			goto apache_end;
		}
		result = TRUE;
	}

	else if (strcmp(act, "INFO") == 0) {
		result = FALSE;
	}

apache_end:

	if (result == TRUE && strcmp(act, "INFO") != 0) {
		// We should reload bind...
		fork_res = vfork();

		if (fork_res == 0){
			// After fork here child thread
			char *args[3] = {APACHECTL_PATH, APACHECTL_COMMAND, NULL};
			execve(APACHECTL_PATH, args, NULL);
			_exit(EXIT_SUCCESS);
		}
		if (fork_res != 0) {
			wait(NULL);
		}
	}

	if (act) safe_free(&act);

	return result;
}

unsigned int create_vhost(char * cmd)
{
	unsigned int result = FALSE;
	char * vh_name = NULL;
	char * zone = NULL;
	char * cgi_perl = NULL;
	char * ssi = NULL;
	char * services = NULL;

	FILE * vf = NULL;

	zone = get_word_by_number(cmd, 3);
	if (!zone) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET NEW ZONE NAME\n");
		goto new_vhost_end;
	}

	cgi_perl = get_word_by_number(cmd, 4);
	if (!cgi_perl) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET CGI_PERL\n");
		goto new_vhost_end;
	}

	ssi = get_word_by_number(cmd, 5);
	if (!ssi) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET SSI\n");
		goto new_vhost_end;
	}

	if (strcmp(cgi_perl, "1") == 0) {
		services = sts(&services, "\t\tOptions +ExecCGI\n\t\tAddHandler cgi-script .cgi .pl\n");
	}
	else {
		services = sts(&services, "\t\tOptions -ExecCGI\n");
	}

	if (strcmp(ssi, "1") == 0) {
		services = sts(&services, "\t\tOptions +Includes\n\t\tAddType text/html .shtml\n\t\tAddOutputFilter INCLUDES .shtml\n");
	}
	else {
		services = sts(&services, "\t\tOptions -Includes\n");
	}

	vh_name = merge_strings(4, APACHE_VHOST_DIR, "/", zone, ".conf");

	vf = fopen(vh_name, "a");

	if (!vf) {
		fprintf(stderr, "FAILED ON FOPEN (%s)\n", vh_name);
		result = FALSE;
		goto new_vhost_end;
	}

	fprintf(vf,
"<VirtualHost %s:%s>\n\
\tServerName %s\n\
\tServerAdmin webmaster@%s\n\
\tDocumentRoot %s/%s/www\n\
\n\
\tphp_admin_value open_basedir %s/%s:%s/www/error:%s/www/hosting/cp/client/errors\n\
\tphp_admin_value doc_root %s/%s/www\n\
\tphp_admin_value safe_mode_include_dir %s/%s/www\n\
\tphp_admin_value safe_mode_exec_dir %s/%s/www\n\
\n\
\tErrorLog %s/%s/logs/apache_error\n\
\tCustomLog %s/%s/logs/apache_access common\n\
\n\
\t<Directory %s/%s/www>\n\
\t\tAllowOverride All\n\
\t\tOptions Indexes\n\
%s\
\t</Directory>\n\
\n\
\tAccessFileName %s/%s/www/.htaccess\n\
</VirtualHost>\n\n",
HOSTING_IP, HOSTING_PORT,
zone,
zone,
CLIENT_DIR, zone,
CLIENT_DIR, zone, HOSTING_ROOT, HOSTING_ROOT,
CLIENT_DIR, zone,
CLIENT_DIR, zone,
CLIENT_DIR, zone,
CLIENT_DIR, zone,
CLIENT_DIR, zone,
CLIENT_DIR, zone,
services,
CLIENT_DIR, zone);

	fclose(vf);

	result = TRUE;
new_vhost_end:

	if (vh_name) safe_free(&vh_name);
	if (zone) safe_free(&zone);
	if (cgi_perl) safe_free(&cgi_perl);
	if (ssi) safe_free(&ssi);
	if (services) safe_free(&services);

	return result;
}

unsigned int update_vhost(char * cmd)
{
	unsigned int result = FALSE;
	unsigned int in_vhost = FALSE;
	unsigned int update_done = FALSE;
	char * old_zone = NULL;
	char * new_zone = NULL;
	char * old_vh_name = NULL;
	char * new_vh_name = NULL;
	char * cgi_perl = NULL;
	char * ssi = NULL;
	char * services = NULL;

	char * tmp_file_name = NULL;
	char * unique = NULL;
	char * buff = NULL;
	char * tmp = NULL;

	FILE * vf = NULL;
	FILE * tmpf = NULL;

	old_zone = get_word_by_number(cmd, 3);
	if (!old_zone) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET OLD ZONE NAME\n");
		goto update_vhost_end;
	}

	new_zone = get_word_by_number(cmd, 4);
	if (!new_zone) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET NEW ZONE NAME\n");
		goto update_vhost_end;
	}

	cgi_perl = get_word_by_number(cmd, 5);
	if (!cgi_perl) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET CGI_PERL\n");
		goto update_vhost_end;
	}

	ssi = get_word_by_number(cmd, 6);
	if (!ssi) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET SSI\n");
		goto update_vhost_end;
	}

	if (strcmp(cgi_perl, "1") == 0) {
		services = sts(&services, "\t\tOptions +ExecCGI\n\t\tAddHandler cgi-script .cgi .pl\n");
	}
	else {
		services = sts(&services, "\t\tOptions -ExecCGI\n");
	}

	if (strcmp(ssi, "1") == 0) {
		services = sts(&services, "\t\tOptions +Includes\n\t\tAddType text/html .shtml\n\t\tAddOutputFilter INCLUDES .shtml\n");
	}
	else {
		services = sts(&services, "\t\tOptions -Includes\n");
	}


	unique = create_unique_id();

	tmp_file_name = merge_strings(3, APACHE_VHOST_DIR, "/", unique);
	old_vh_name = merge_strings(4, APACHE_VHOST_DIR, "/",  old_zone, ".conf");
	new_vh_name = merge_strings(4, APACHE_VHOST_DIR, "/", new_zone, ".conf");

	vf = fopen(old_vh_name, "r");
	if (!vf) {
		fprintf(stderr, "FAILED ON FOPEN (%s)\n", old_vh_name);
		result = FALSE;
		goto update_vhost_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(vf);
		printf("Failed to open tmpf\n");
		result = FALSE;
		goto update_vhost_end;
	}

	in_vhost = FALSE;
	update_done = FALSE;
	while (!feof(vf)) {

		buff = read_string(vf);
		if (!buff)
			break;

		tmp = get_word_by_number(buff, 1);
		if (!tmp) {
			if(buff) safe_free(&buff);
			continue;
		}

		if (update_done == FALSE) {
			if (in_vhost == FALSE) {
				if (strcmp(tmp, "<VirtualHost") == 0) {
					in_vhost = TRUE;
				}
			}
			else {
				if (strcmp(tmp, "</VirtualHost>") == 0) {
					in_vhost = FALSE;
					update_done = TRUE;
					fprintf(tmpf,
"<VirtualHost %s:%s>\n\
\tServerName %s\n\
\tServerAdmin webmaster@%s\n\
\tDocumentRoot %s/%s/www\n\
\n\
\tphp_admin_value open_basedir %s/%s:%s/www/error:%s/www/hosting/cp/client/errors\n\
\tphp_admin_value doc_root %s/%s/www\n\
\tphp_admin_value safe_mode_include_dir %s/%s/www\n\
\tphp_admin_value safe_mode_exec_dir %s/%s/www\n\
\n\
\tErrorLog %s/%s/logs/apache_error\n\
\tCustomLog %s/%s/logs/apache_access common\n\
\n\
\t<Directory %s/%s/www>\n\
\t\tAllowOverride All\n\
\t\tOptions Indexes\n\
%s\
\t</Directory>\n\
\n\
\tAccessFileName %s/%s/www/.htaccess\n\
</VirtualHost>\n\n",
HOSTING_IP, HOSTING_PORT,
new_zone,
new_zone,
CLIENT_DIR, new_zone,
CLIENT_DIR, new_zone, HOSTING_ROOT, HOSTING_ROOT,
CLIENT_DIR, new_zone,
CLIENT_DIR, new_zone,
CLIENT_DIR, new_zone,
CLIENT_DIR, new_zone,
CLIENT_DIR, new_zone,
CLIENT_DIR, new_zone,
services,
CLIENT_DIR, new_zone);
				}
			}
		}
		else {
			fprintf(tmpf, "%s\n", buff);
			if (strcmp(buff, "</VirtualHost>") == 0)
				fprintf(tmpf, "\n");
		}

		if(buff) safe_free(&buff);
		if(tmp) safe_free(&tmp);
	}

	fclose(vf);
	fclose(tmpf);

	if (strcmp(old_zone, new_zone) != 0) {
		if (unlink(old_vh_name) == -1) {
			perror("Remove file:");
		}
	}

	if (rename(tmp_file_name, new_vh_name) == -1) {
		perror("Rename file:");
		result = FALSE;
		goto update_vhost_end;
	}

	if (update_done == TRUE)
		result = TRUE;
	else
		fprintf(stderr, "Zone has not been updated\n");

update_vhost_end:

	if (old_vh_name) safe_free(&old_vh_name);
	if (new_vh_name) safe_free(&new_vh_name);
	if (old_zone) safe_free(&old_zone);
	if (new_zone) safe_free(&new_zone);
	if (cgi_perl) safe_free(&cgi_perl);
	if (ssi) safe_free(&ssi);
	if (services) safe_free(&services);

	if (tmp_file_name) safe_free(&tmp_file_name);
	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);
	if (unique) safe_free(&unique);

	return result;
}

unsigned int remove_vhost(char * cmd)
{
	unsigned int result = FALSE;
	char * vh_name = NULL;
	char * zone = NULL;

	zone = get_word_by_number(cmd, 3);

	if (!zone) {
		result = FALSE;
		goto remove_vhost_end;
	}

	vh_name = merge_strings(4, APACHE_VHOST_DIR, "/",  zone, ".conf");

	if (unlink(vh_name) == -1) {
		perror("Remove file:");
		result = FALSE;
		goto remove_vhost_end;
	}

	result = TRUE;
remove_vhost_end:

	if (vh_name) safe_free(&vh_name);
	if (zone) safe_free(&zone);

	return result;
}

unsigned int create_webdir(char * cmd)
{
	unsigned int result = FALSE;
	unsigned int index = 0;
	char * vh_name = NULL;
	char * system_mode = NULL;
	char * zone = NULL;
	char * webdir = NULL;
	char * cgi_perl = NULL;
	char * ssi = NULL;
	char * root_dir = NULL;
	char * services = NULL;

	FILE * vf = NULL;

	index = 3;

	system_mode = get_word_by_number(cmd, index++);
	if (!system_mode) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET SYSTEM_MODE\n");
		goto create_webdir_end;
	}

	if (strcmp(system_mode, "system") != 0 && strcmp(system_mode, "client") != 0) {
		result = FALSE;
		fprintf(stderr, "WRONG SYSTEM_MODE\n");
		goto create_webdir_end;
	}

	if (strcmp(system_mode, "client") == 0) {
		zone = get_word_by_number(cmd, index++);
		if (!zone) {
			result = FALSE;
			fprintf(stderr, "FAILED ON GET ZONE NAME\n");
			goto create_webdir_end;
		}
	}

	webdir = get_word_by_number(cmd, index++);
	if (!webdir) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET WEBDIR\n");
		goto create_webdir_end;
	}

	if (strcmp(system_mode, "client") == 0) {
		cgi_perl = get_word_by_number(cmd, index++);
		if (!cgi_perl) {
			result = FALSE;
			fprintf(stderr, "FAILED ON GET CGI_PERL\n");
			goto create_webdir_end;
		}

		ssi = get_word_by_number(cmd, index++);
		if (!ssi) {
			result = FALSE;
			fprintf(stderr, "FAILED ON GET SSI\n");
			goto create_webdir_end;
		}
	}

	root_dir = get_word_by_number(cmd, index++);
	if (!root_dir) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET ROOT_DIR\n");
		goto create_webdir_end;
	}

	if (strcmp(system_mode, "client") == 0) {

		if (strcmp(cgi_perl, "1") == 0) {
			services = sts(&services, "\t\tOptions +ExecCGI\n\t\tAddHandler cgi-script .cgi .pl\n");
		}
		else {
			services = sts(&services, "\t\tOptions -ExecCGI\n");
		}

		if (strcmp(ssi, "1") == 0) {
			services = sts(&services, "\t\tOptions +Includes\n\t\tAddType text/html .shtml\n\t\tAddOutputFilter INCLUDES .shtml\n");
		}
		else {
			services = sts(&services, "\t\tOptions -Includes\n");
		}

		vh_name = merge_strings(4, APACHE_VHOST_DIR, "/", zone, ".conf");

		vf = fopen(vh_name, "a");

		if (!vf) {
			fprintf(stderr, "FAILED ON FOPEN (%s)\n", vh_name);
			result = FALSE;
			goto create_webdir_end;
		}

		fprintf(vf,
"<VirtualHost %s:%s>\n\
\tServerName %s.%s\n\
\tServerAdmin webmaster@%s.%s\n\
\tDocumentRoot %s/%s/www/%s\n\
\n\
\tphp_admin_value open_basedir %s/%s:%s/www/error:%s/www/hosting/cp/client/errors\n\
\tphp_admin_value doc_root %s/%s/www/%s\n\
\tphp_admin_value safe_mode_include_dir %s/%s/www/%s\n\
\tphp_admin_value safe_mode_exec_dir %s/%s/www/%s\n\
\n\
\tErrorLog %s/%s/logs/apache_error\n\
\tCustomLog %s/%s/logs/apache_access common\n\
\n\
\t<Directory %s/%s/www/%s>\n\
\t\tAllowOverride All\n\
\t\tOptions Indexes\n\
%s\
\t</Directory>\n\
\n\
\tAccessFileName %s/%s/www/.htaccess\n\
</VirtualHost>\n\n",
HOSTING_IP, HOSTING_PORT,
webdir, zone,
webdir, zone,
CLIENT_DIR, zone, root_dir,
CLIENT_DIR, zone, HOSTING_ROOT, HOSTING_ROOT,
CLIENT_DIR, zone, root_dir,
CLIENT_DIR, zone, root_dir,
CLIENT_DIR, zone, root_dir,
CLIENT_DIR, zone,
CLIENT_DIR, zone,
CLIENT_DIR, zone, root_dir,
services,
CLIENT_DIR, zone);

		fclose(vf);

		result = TRUE;
	}
	else {
		vh_name = merge_strings(4, APACHE_VHOST_DIR, "/",  webdir, ".conf");

		vf = fopen(vh_name, "w+");
		if (!vf) {
			fprintf(stderr, "FAILED ON FOPEN (%s)\n", vh_name);
			result = FALSE;
			goto create_webdir_end;
		}

		fprintf(vf,
"<VirtualHost %s:%s>\n\
\tServerName %s\n\
\tServerAdmin webmaster@%s\n\
\tDocumentRoot %s\n\
\n\
\tphp_admin_value safe_mode off\n\
\n\
</VirtualHost>\n\n",
HOSTING_IP, HOSTING_PORT,
webdir,
webdir,
root_dir);

		fclose(vf);

		result = TRUE;
	}

create_webdir_end:

	if (vh_name) safe_free(&vh_name);
	if (system_mode) safe_free(&system_mode);
	if (zone) safe_free(&zone);
	if (webdir) safe_free(&webdir);
	if (cgi_perl) safe_free(&cgi_perl);
	if (ssi) safe_free(&ssi);
	if (root_dir) safe_free(&root_dir);
	if (services) safe_free(&services);

	return result;
}

unsigned int update_webdir(char * cmd)
{
	unsigned int index = 0;
	unsigned int result = FALSE;
	char * old_vh_name = NULL;
	char * new_vh_name = NULL;
	char * old_system_mode = NULL;
	char * new_system_mode = NULL;
	char * old_zone = NULL;
	char * new_zone = NULL;
	char * old_webdir = NULL;
	char * old_webdir_domain = NULL;
	char * new_webdir = NULL;
	char * cgi_perl = NULL;
	char * ssi = NULL;
	char * root_dir = NULL;

	char * action_string = NULL;

	index = 3;

	old_system_mode = get_word_by_number(cmd, index++);
	if (!old_system_mode) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET OLD SYSTEM_MODE\n");
		goto update_webdir_end;
	}

	if (strcmp(old_system_mode, "system") != 0 && strcmp(old_system_mode, "client") != 0) {
		result = FALSE;
		fprintf(stderr, "WRONG OLD SYSTEM_MODE\n");
		goto update_webdir_end;
	}

	if (strcmp(old_system_mode, "client") == 0) {
		old_zone = get_word_by_number(cmd, index++);
		if (!old_zone) {
			result = FALSE;
			fprintf(stderr, "FAILED ON GET OLD ZONE NAME\n");
			goto update_webdir_end;
		}
	}

	old_webdir = get_word_by_number(cmd, index++);
	if (!old_webdir) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET OLD WEBDIR\n");
		goto update_webdir_end;
	}

	new_system_mode = get_word_by_number(cmd, index++);
	if (!new_system_mode) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET NEW SYSTEM_MODE\n");
		goto update_webdir_end;
	}

	if (strcmp(new_system_mode, "system") != 0 && strcmp(new_system_mode, "client") != 0) {
		result = FALSE;
		fprintf(stderr, "WRONG NEW SYSTEM_MODE\n");
		goto update_webdir_end;
	}

	if (strcmp(new_system_mode, "client") == 0) {
		new_zone = get_word_by_number(cmd, index++);
		if (!new_zone) {
			result = FALSE;
			fprintf(stderr, "FAILED ON GET NEW ZONE NAME\n");
			goto update_webdir_end;
		}
	}

	new_webdir = get_word_by_number(cmd, index++);
	if (!new_webdir) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET NEW WEBDIR\n");
		goto update_webdir_end;
	}

	if (strcmp(new_system_mode, "client") == 0) {
		cgi_perl = get_word_by_number(cmd, index++);
		if (!cgi_perl) {
			result = FALSE;
			fprintf(stderr, "FAILED ON GET CGI_PERL\n");
			goto update_webdir_end;
		}

		ssi = get_word_by_number(cmd, index++);
		if (!ssi) {
			result = FALSE;
			fprintf(stderr, "FAILED ON GET SSI\n");
			goto update_webdir_end;
		}
	}

	root_dir = get_word_by_number(cmd, index++);
	if (!root_dir) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET ROOT_DIR\n");
		goto update_webdir_end;
	}

	if (strcmp(old_system_mode, "client") == 0) {
		action_string = merge_strings(4, "APACHE DELETE_WEBDIR client ", old_zone, " ", old_webdir);
		fprintf(stderr, "ACTION1: %s\n", action_string);
		result = remove_webdir(action_string);

		if (result == FALSE)
			goto update_webdir_end;

		safe_free(&action_string);
	}
	else {
		action_string = merge_strings(2, "APACHE DELETE_WEBDIR system ", old_webdir);
		fprintf(stderr, "ACTION1: %s\n", action_string);
		result = remove_webdir(action_string);

		if (result == FALSE)
			goto update_webdir_end;

		safe_free(&action_string);
	}

	if (strcmp(new_system_mode, "client") == 0) {
		action_string = merge_strings(10, "APACHE CREATE_WEBDIR client ", new_zone, " ", new_webdir, " ", cgi_perl, " ", ssi, " ", root_dir);
		fprintf(stderr, "ACTION2: %s\n", action_string);
		result = create_webdir(action_string);

		if (result == FALSE)
			goto update_webdir_end;

		safe_free(&action_string);
	}
	else {
		action_string = merge_strings(4, "APACHE CREATE_WEBDIR system ", new_webdir, " ", root_dir);
		fprintf(stderr, "ACTION2: %s\n", action_string);
		result = create_webdir(action_string);

		if (result == FALSE)
			goto update_webdir_end;

		safe_free(&action_string);
	}

update_webdir_end:

	if (old_vh_name) safe_free(&old_vh_name);
	if (new_vh_name) safe_free(&new_vh_name);
	if (new_system_mode) safe_free(&new_system_mode);
	if (old_system_mode) safe_free(&old_system_mode);
	if (old_zone) safe_free(&old_zone);
	if (new_zone) safe_free(&new_zone);
	if (old_webdir) safe_free(&old_webdir);
	if (old_webdir_domain) safe_free(&old_webdir_domain);
	if (new_webdir) safe_free(&new_webdir);
	if (cgi_perl) safe_free(&cgi_perl);
	if (ssi) safe_free(&ssi);
	if (root_dir) safe_free(&root_dir);

	if (action_string) safe_free(&action_string);

	return result;
}

unsigned int remove_webdir(char * cmd)
{
	unsigned int index = 0;
	unsigned int result = FALSE;
	unsigned int in_vhost = FALSE;
	unsigned int update_done = FALSE;
	unsigned int zone_error = FALSE;
	char * vh_name = NULL;
	char * system_mode = NULL;
	char * zone = NULL;
	char * webdir = NULL;
	char * webdir_domain = NULL;


	char * tmp_file_name = NULL;
	char * unique = NULL;
	char * buff = NULL;
	char * buff2 = NULL;
	char * tmp = NULL;
	char * tmp2 = NULL;
	char * tmp3 = NULL;

	FILE * vf = NULL;
	FILE * tmpf = NULL;

	index = 3;

	system_mode = get_word_by_number(cmd, index++);
	if (!system_mode) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET SYSTEM_MODE\n");
		goto remove_webdir_end;
	}

	if (strcmp(system_mode, "system") != 0 && strcmp(system_mode, "client") != 0) {
		result = FALSE;
		fprintf(stderr, "WRONG SYSTEM_MODE\n");
		goto remove_webdir_end;
	}

	if (strcmp(system_mode, "client") == 0) {
		zone = get_word_by_number(cmd, index++);
		if (!zone) {
			result = FALSE;
			fprintf(stderr, "FAILED ON GET ZONE NAME\n");
			goto remove_webdir_end;
		}
	}

	webdir = get_word_by_number(cmd, index++);
	if (!webdir) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET WEBDIR\n");
		goto remove_webdir_end;
	}

	if (strcmp(system_mode, "client") == 0) {
		unique = create_unique_id();

		webdir_domain = merge_strings(3, webdir, ".", zone);
		vh_name = merge_strings(4, APACHE_VHOST_DIR, "/",  zone, ".conf");

		tmp_file_name = merge_strings(3, APACHE_VHOST_DIR, "/", unique);

		vf = fopen(vh_name, "r");
		if (!vf) {
			fprintf(stderr, "FAILED ON FOPEN (%s)\n", vh_name);
			result = FALSE;
			goto remove_webdir_end;
		}

		tmpf = fopen(tmp_file_name, "w+");
		if (!tmpf) {
			fclose(vf);
			printf("Failed to open tmpf\n");
			result = FALSE;
			goto remove_webdir_end;
		}

		in_vhost = FALSE;
		update_done = FALSE;
		zone_error = FALSE;
		while (!feof(vf)) {

			buff = read_string(vf);
			if (!buff)
				break;

			tmp = get_word_by_number(buff, 1);
			if (!tmp) {
				if(buff) safe_free(&buff);
				continue;
			}

			if (update_done == FALSE) {
				if (in_vhost == FALSE) {
					if (strcmp(tmp, "<VirtualHost") == 0) {

						buff2 = read_string(vf);
						if (!buff2) {
							return_message = strdup("I have read the next string after VirtualHost directive, but it false");
							zone_error = TRUE;
							break;
						}

						if (how_much_words(buff2) != 2) {
							return_message = strdup("String did not contain two words");
							zone_error = TRUE;
							break;
						}

						tmp2 = get_word_by_number(buff2, 1);
						if (strcmp(tmp2, "ServerName") != 0) {
							return_message = strdup("Expected word is not 'ServerName'");
							zone_error = TRUE;
							break;
						}

						tmp3 = get_word_by_number(buff2, 2);
						if (!tmp2) {
							return_message = strdup("I can't get second word");
							zone_error = TRUE;
							break;
						}

						if (strcmp(tmp3, webdir_domain) == 0) {
							in_vhost = TRUE;
						}
						else {
							fprintf(tmpf, "%s\n", buff);
							fprintf(tmpf, "%s\n", buff2);
						}
					}
					else {
						fprintf(tmpf, "%s\n", buff);
						if (strcmp(buff, "</VirtualHost>") == 0)
							fprintf(tmpf, "\n");
					}
				}
				else {
					if (strcmp(tmp, "</VirtualHost>") == 0) {
						in_vhost = FALSE;
						update_done = TRUE;
					}
				}
			}
			else {
				fprintf(tmpf, "%s\n", buff);
				if (strcmp(buff, "</VirtualHost>") == 0)
					fprintf(tmpf, "\n");
			}

			if (buff) safe_free(&buff);
			if (buff2) safe_free(&buff2);
			if (tmp) safe_free(&tmp);
			if (tmp2) safe_free(&tmp2);
			if (tmp3) safe_free(&tmp3);
		}

		fclose(vf);
		fclose(tmpf);

		if (zone_error == FALSE) {
			if (rename(tmp_file_name, vh_name) == -1) {
				perror("Rename file:");
				result = FALSE;
				goto remove_webdir_end;
			}
		}
		else {
			if (unlink(tmp_file_name) == -1) {
				perror("Remove file:");
			}
		}

		if (update_done == TRUE)
			result = TRUE;
		else
			fprintf(stderr, "Zone has not been updated\n");
	}
	else {

		vh_name = merge_strings(4, APACHE_VHOST_DIR, "/",  webdir, ".conf");

		if (unlink(vh_name) == -1) {
			perror("Remove file:");
			result = FALSE;
			goto remove_webdir_end;
		}

		result = TRUE;
	}

remove_webdir_end:

	if (system_mode) safe_free(&system_mode);
	if (zone) safe_free(&zone);
	if (webdir) safe_free(&webdir);
	if (webdir_domain) safe_free(&webdir_domain);
	if (vh_name) safe_free(&vh_name);

	if (tmp_file_name) safe_free(&tmp_file_name);
	if (buff) safe_free(&buff);
	if (buff2) safe_free(&buff2);
	if (tmp) safe_free(&tmp);
	if (tmp2) safe_free(&tmp2);
	if (tmp3) safe_free(&tmp3);
	if (unique) safe_free(&unique);

	return result;
}

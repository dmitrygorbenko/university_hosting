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

#include <ctype.h>
#include <cstring>
#include <cstdio>
#include <cstdlib>

#include "define.h"
#include "message.h"
#include "str.h"
#include "config.h"

using namespace std;

struct config_state_def config_state;
struct config_data_def config_data;

unsigned int config_init(char * file_name)
{
	config_state.cfg_exists = TRUE;
	config_state.cfg_read_startup = TRUE;
	config_state.cfg_fd = NULL;
	config_state.cfg_file_name = safe_strdup(file_name);

	config_data.ns_count = 0;

	config_state.cfg_fd = fopen(config_state.cfg_file_name, "r");

	if (config_state.cfg_fd == NULL) {
		message(3, "Config_Init: Can't open Config file.\nConfig file mame: ",
			config_state.cfg_file_name,
			"\nPlease, create this file.\n");
		config_state.cfg_exists = FALSE;
		config_state.cfg_read_startup = FALSE;
	}

	if(config_state.cfg_fd != NULL)
		fclose(config_state.cfg_fd);

	if(!config_state.cfg_read_startup)
		return FALSE;

	return TRUE;
};

void config_shutdown()
{
	if (config_state.cfg_file_name)
		safe_free(&config_state.cfg_file_name);

	if (config_data.listen_ip)
		safe_free(&config_data.listen_ip);

};

void config_close()
{
	if (config_state.cfg_open == TRUE)
		fclose(config_state.cfg_fd);
};

unsigned int read_config()
{
	FILE * file = NULL;
	char * filename = NULL;
	char * str = NULL;
	char * buf = NULL;
	char * tmp = NULL;
	unsigned char result = 0;

	if (!config_state.cfg_read_startup)
		return FALSE;

	result = TRUE;

	filename = config_state.cfg_file_name;

	file = fopen(filename,"r");
	config_state.cfg_open = TRUE;

	if (file == NULL) {
		message(2, "Read_Config: Can't open Config file.\nConfig file mame: ",
			config_state.cfg_file_name);
		config_state.cfg_exists = FALSE;
		config_state.cfg_read_startup = FALSE;
		return FALSE;
	}

	while (!feof(file)) {

		str = read_string(file);

		if (!str)
			break;

		if ((strstr(str, "ns_count") != (char) NULL))
			goto ns_count;
		if ((strstr(str, "listen_ip") != (char) NULL))
			goto listen_ip;
		if ((strstr(str, "listen_port") != (char) NULL))
			goto listen_port;

		goto freedom;

ns_count:
		tmp = get_param(str);
		config_data.ns_count = atoi(tmp);
		if (tmp)
			safe_free(&tmp);
		if (str)
			safe_free(&str);
		goto freedom;

listen_ip:
		config_data.listen_ip = get_param(str);
		if (str)
			safe_free(&str);
		goto freedom;

listen_port:
		tmp = get_param(str);
		config_data.listen_port = atoi(tmp);
		if (tmp)
			safe_free(&tmp);
		if (str)
			safe_free(&str);
		goto freedom;

freedom:
		if(str)
			safe_free(&str);
	}

	config_state.cfg_open = FALSE;
	if(file != NULL)
		fclose(file);

	if (result == FALSE)
		return FALSE;

	return TRUE;
};

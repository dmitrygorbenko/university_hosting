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

#include <unistd.h>
#include <sys/types.h>
#include <sys/times.h>
#include <time.h>
#include <getopt.h>

#include "define.h"
#include "str.h"
#include "tools.h"
#include "params.h"

using namespace std;

struct params_def startup_params;

void print_help()
{
	fprintf(stdout, "Usage: %s [OPTIONS]\n"\
		"OPTIONS:\n"\
		"-l, --log_file			Set log file\n"
		"-c, --config_file		Set config file\n"
		"\n"
	    , PROGRAM);
};

void reset_params()
{
	startup_params.alternative_config = FALSE;
	startup_params.alternative_log = FALSE;
	startup_params.view_help = FALSE;

	startup_params.config_file = NULL;
	startup_params.log_file = NULL;
};

unsigned int parse_params(int counter, char **values)
{
	int c;
	int digit_optind = 0;

	static struct option long_options[] = {
		{ "help", 0, 0, 'h' },
		{ "log_file", 1, 0, 'l' },
		{ "config_file", 1, 0, 'c' },
		{ 0 , 0 , 0 , 0}
	};

	while (1) {
		int this_option_optind = optind ? optind : 1;
		int option_index = 0;

		c = getopt_long (counter, values,"hc:l:", long_options, &option_index);

		if (c == -1)
			break;

		switch (c) {
		case 0:
			fprintf(stdout, "\nParameter %s", long_options[option_index].name);
			if(optarg)
				fprintf(stdout,"\n  with argument %s",optarg);
			fprintf(stdout,"\n");
			break;

		case 'h':
			print_help();
			startup_params.view_help = TRUE;
		break;

		case 'c':
			startup_params.alternative_config = TRUE;
			startup_params.config_file = safe_strdup(optarg);
		break;

		case 'l':
			startup_params.alternative_log = TRUE;
			startup_params.log_file = safe_strdup(optarg);
		break;

		case ':': // missing parameter
			fprintf(stdout, "Missing parameter. Try ` --help' for more options.\n\n");
			return FALSE;
		break;

		case '?': // unknown option
			fprintf(stdout, "Unknown option. Try ` --help' for more options.\n\n");
			return FALSE;
		break;
		}
	}

	return TRUE;
};


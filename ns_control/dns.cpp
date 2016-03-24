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
#include "dns.h"
#include "zones.h"
#include "zone_info.h"
#include "domains.h"

using namespace std;

unsigned int dns_manage(char *cmd)
{
	unsigned int result = FALSE;
	int fork_res;
	char * act = NULL;
	char * r_type = NULL;

	act = get_word_by_number(cmd, 2);

	if (!act)
		return FALSE;

	if (strcmp(act, "CREATE") == 0) {
		r_type = get_word_by_number(cmd, 3);

		if (!r_type) {
			result = FALSE;
			goto dns_end;
		}

		if (strcmp(r_type, "ZONE_INFO") == 0) {
			if (create_new_zone_info(cmd) == FALSE) {
				result = FALSE;
				goto dns_end;
			}
			result = TRUE;
		}

		else if (strcmp(r_type, "ZONE") == 0) {
			if (create_new_zone(cmd) == FALSE) {
				result = FALSE;
				goto dns_end;
			}
			result = TRUE;
		}

		else if (strcmp(r_type, "DOMAIN") == 0) {
			if (create_new_domain(cmd) == FALSE) {
				result = FALSE;
				goto dns_end;
			}
			result = TRUE;
		}
	}

	else if (strcmp(act, "DELETE") == 0) {
		r_type = get_word_by_number(cmd, 3);

		if (!r_type) {
			result = FALSE;
			goto dns_end;
		}

		if (strcmp(r_type, "ZONE_INFO") == 0) {
			if (remove_zone_info(cmd) == FALSE) {
				result = FALSE;
				goto dns_end;
			}
			result = TRUE;
		}

		else if (strcmp(r_type, "ZONE") == 0) {
			if (remove_zone(cmd) == FALSE) {
				result = FALSE;
				goto dns_end;
			}
			result = TRUE;
		}

		else if (strcmp(r_type, "DOMAIN") == 0) {
			if (remove_domain(cmd) == FALSE) {
				result = FALSE;
				goto dns_end;
			}
			result = TRUE;
		}
	}

	else if (strcmp(act, "UPDATE") == 0) {
		r_type = get_word_by_number(cmd, 3);

		if (!r_type) {
			result = FALSE;
			goto dns_end;
		}

		if (strcmp(r_type, "ZONE_INFO") == 0) {
			if (update_zone_info(cmd) == FALSE) {
				result = FALSE;
				goto dns_end;
			}
			result = TRUE;
		}

		else if (strcmp(r_type, "ZONE_MANUAL") == 0) {
			if (update_zone_manual(cmd) == FALSE) {
				result = FALSE;
				goto dns_end;
			}
			result = TRUE;
		}

		else if (strcmp(r_type, "ZONE") == 0) {
			if (update_zone(cmd) == FALSE) {
				result = FALSE;
				goto dns_end;
			}
			result = TRUE;
		}

		else if (strcmp(r_type, "DOMAIN") == 0) {
			if (update_domain(cmd) == FALSE) {
				result = FALSE;
				goto dns_end;
			}
			result = TRUE;
		}
	}

	else if (strcmp(act, "INFO") == 0) {
		r_type = get_word_by_number(cmd, 3);

		if (!r_type) {
			result = FALSE;
			goto dns_end;
		}

		if (strcmp(r_type, "ZONE_INFO") == 0) {
			if (info_zone_info(cmd) == FALSE) {
				result = FALSE;
				goto dns_end;
			}
			result = TRUE;
		}

		else if (strcmp(r_type, "ZONE") == 0) {
			if (info_zone(cmd) == FALSE) {
				result = FALSE;
				goto dns_end;
			}
			result = TRUE;
		}

		else if (strcmp(r_type, "DOMAIN") == 0) {
			// nothing to do here
			result = FALSE;
		}
	}

dns_end:

	if (result == TRUE && strcmp(act, "INFO") != 0 && ROOT_COMPILE) {
		//Make sleep to create next time unique ID
		sleep(1);

		// We should reload bind...
		fork_res = vfork();

		if (fork_res == 0){
			// After fork here child thread
			char *args[3] = {RNDC_PATH, RNDC_COMMAND, NULL};
			execve(RNDC_PATH, args, NULL);
			_exit(EXIT_SUCCESS);
		}
		if (fork_res != 0) {
			wait(NULL);
		}
	}

	if (act) safe_free(&act);
	if (r_type) safe_free(&r_type);

	return result;
}


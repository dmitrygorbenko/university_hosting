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

#include <unistd.h>

#include "define.h"
#include "str.h"
#include "tools.h"
#include "serial.h"
#include "zones.h"

using namespace std;

extern char * return_message;

unsigned int create_new_zone(char * cmd)
{
	struct def_dns_serial * serial = NULL;
	unsigned int result = FALSE;

	char * zone_name = NULL;
	char * zone_file_name = NULL;

	FILE * zf = NULL;

	zone_name = get_word_by_number(cmd, 4);

	if (!zone_name) {
		result = FALSE;
		goto new_zone_end;
	}

	zone_file_name = merge_strings(2, zone_name, ".zone");

	serial = serial_create();

	// Fill up zone config
	zf = fopen(DNS_ZONE_FILE, "a");

	if (!zf) {
		result = FALSE;
		goto new_zone_end;
	}

	fprintf(zf,
"\n\
zone \"%s\" IN {\n\
	type master;\n\
	file \"%s\";\n\
	allow-update { none; };\n\
};\n",
zone_name,
zone_file_name);

	fclose(zf);
	zf = NULL;
	safe_free(&zone_file_name);

	// Fill up zone file
	zone_file_name = merge_strings(3, DNS_DB_DIR, zone_name, ".zone");

	zf = fopen(zone_file_name, "w+");

	if (!zf) {
		result = FALSE;
		goto new_zone_end;
	}

	fprintf(zf,
"$ORIGIN .\n\
$TTL 86400	; 1 day\n\
%s			IN SOA  %s %s (\n\
				%s ; serial\n\
				10800      ; refresh (3 hours)\n\
				900        ; retry (15 minutes)\n\
				604800     ; expire (1 week)\n\
				3600       ; negative caching (1 hour)\n\
				)\n\
		IN	NS	%s\n\
		IN	NS	%s\n\
		IN	MX	10	%s\n\
		IN	MX	20	%s\n\
		IN	A	%s\n\
$ORIGIN %s.\n",
zone_name,
DNS_SOA_NS_SERVER,
DNS_SOA_EMAILSERVER,
serial->serial_num,
DNS_NS1_SERVER,
DNS_NS2_SERVER,
DNS_MX1_SERVER,
DNS_MX2_SERVER,
DNS_HOSTING_IP,
zone_name);

	fclose(zf);
	zf = NULL;

	result = TRUE;
new_zone_end:

	if (zone_name) safe_free(&zone_name);
	if (zone_file_name) safe_free(&zone_file_name);
	serial_clean(serial);

	return result;
}

unsigned int update_zone(char * cmd)
{
	struct def_dns_serial * serial = NULL;
	unsigned int result = FALSE;
	unsigned int find = 0;
	unsigned int in_zone = FALSE;
	unsigned int zone_list_update = FALSE;
	unsigned int comp = 0;

	char * zone_name = NULL;
	char * new_zone_name = NULL;

	char * unique = NULL;
	char * zone_list_file_name = NULL;
	char * zone_file_name = NULL;
	char * new_zone_file_name = NULL;
	char * short_new_zone_file_name = NULL;
	char * tmp_file_name = NULL;

	char * buff = NULL;
	char * tmp = NULL;
	char * tmp2 = NULL;

	FILE * zf = NULL;
	FILE * tmpf = NULL;

	zone_name = get_word_by_number(cmd, 4);
	if (!zone_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET ZONE_NAME\n");
		goto update_zone_end;
	}

	new_zone_name = get_word_by_number(cmd, 5);
	if (!new_zone_name) {
		result = FALSE;
		fprintf(stderr, "FAILED ON GET NEW_ZONE_NAME\n");
		goto update_zone_end;
	}

	unique = create_unique_id();
	serial = serial_create();

	zone_list_file_name = strdup(DNS_ZONE_FILE);
	zone_file_name = merge_strings(3, DNS_DB_DIR, zone_name, ".zone");
	new_zone_file_name = merge_strings(3, DNS_DB_DIR, new_zone_name, ".zone");
	short_new_zone_file_name = merge_strings(2, new_zone_name, ".zone");
	tmp_file_name = merge_strings(2, DNS_DB_DIR, unique);

	// UPDATE ZONE FILE LIST

	zf = fopen(zone_list_file_name, "r");
	if (!zf) {
		fprintf(stderr, "Failed to open zf\n");
		result = FALSE;
		goto update_zone_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(zf);
		fprintf(stderr, "Failed to open tmpf\n");
		result = FALSE;
		goto update_zone_end;
	}

	in_zone = FALSE;
	zone_list_update = FALSE;
	while (!feof(zf)) {
		buff = read_string(zf);
		if (!buff)
			break;

		tmp = get_word_by_number(buff, 1);

		if (!tmp) {
			fprintf(tmpf, "%s\n", buff);
			if (buff) safe_free(&buff);
			continue;
		}

		// Searching zone mode...
		if (in_zone == FALSE) {
			comp = 0;
			tmp2 = get_word_by_number(buff, 2);

			if (tmp2) {
				tmp2 = cut_quotes(tmp2);

				if (strcmp(tmp, "zone") == 0)
					comp++;

				if (strcmp(tmp2, zone_name) == 0)
					comp++;

				// Check: does we find our zone ?
				if (comp == 2) {
					in_zone = TRUE;
					zone_list_update = TRUE;
				}
			}

			// We still outside of zone ?
			if (in_zone == FALSE) {
				fprintf(tmpf, "%s\n", buff);
			}

			// We have find end olf old zone

			if (strcmp(tmp, "};") == 0) {
				fprintf(tmpf, "\n");
			}
		}

		// Searching end of the zone...
		else if (in_zone == TRUE) {
			// So, fill out new zone
			if (strcmp(tmp, "};") == 0) {
				in_zone = FALSE;
				fprintf(tmpf,
"zone \"%s\" IN {\n\
	type master;\n\
	file \"%s\";\n\
	allow-update { none; };\n\
};\n\n",
new_zone_name,
short_new_zone_file_name);
			}
		}

		if (buff) safe_free(&buff);
		if (tmp) safe_free(&tmp);
		if (tmp2) safe_free(&tmp2);
	}

	fclose(zf);
	fclose(tmpf);

	// END OF PARSE LIST OF ZONES

	if (rename(tmp_file_name, zone_list_file_name) == -1) {
		perror("Rename file:");
		result = FALSE;
		goto update_zone_end;
	}

	if (zone_list_update == FALSE) {
		fprintf(stderr, "Zone list file was not updated !\n");
		result = FALSE;
		goto update_zone_end;
	}

	// NOW, UPDATE ZONE FILE

	zf = fopen(zone_file_name, "r");
	if (!zf) {
		fprintf(stderr, "Failed to open zf\n");
		result = FALSE;
		goto update_zone_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(zf);
		fprintf(stderr, "Failed to open tmpf\n");
		result = FALSE;
		goto update_zone_end;
	}

	while(!feof(zf)) {
		buff = read_string(zf);

		if (!buff)
			break;

		tmp = get_first_word(buff);

		if (!tmp) {
			safe_free(&buff);
			continue;
		}

		if  (strcmp(tmp, "$ORIGIN") == 0 && how_much_words(buff) == 2) {
			find++;
		}

		if (find > 2)
			fprintf(tmpf, "%s\n", buff);

		if (find == 2) {
			fprintf(tmpf,
"$ORIGIN .\n\
$TTL 86400	; 1 day\n\
%s			IN SOA  %s %s (\n\
				%s ; serial\n\
				10800      ; refresh (3 hours)\n\
				900        ; retry (15 minutes)\n\
				604800     ; expire (1 week)\n\
				3600       ; negative caching (1 hour)\n\
				)\n\
		IN	NS	%s\n\
		IN	NS	%s\n\
		IN	MX	10	%s\n\
		IN	MX	20	%s\n\
		IN	A	%s\n\
$ORIGIN %s.\n",
new_zone_name,
DNS_SOA_NS_SERVER,
DNS_SOA_EMAILSERVER,
serial->serial_num,
DNS_NS1_SERVER,
DNS_NS2_SERVER,
DNS_MX1_SERVER,
DNS_MX2_SERVER,
DNS_HOSTING_IP,
new_zone_name);
			find++;
		}

		if (buff) safe_free(&buff);
		if (tmp) safe_free(&tmp);
	}

	fclose(zf);
	fclose(tmpf);

	if (unlink(zone_file_name) == -1) {
		perror("Remove file:");
	}

	if (rename(tmp_file_name, new_zone_file_name) == -1) {
		perror("Rename file:");
		result = FALSE;
		goto update_zone_end;
	}

	result = TRUE;

update_zone_end:

	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);
	if (unique) safe_free(&unique);
	if (zone_name) safe_free(&zone_name);
	if (new_zone_name) safe_free(&new_zone_name);
	if (zone_list_file_name) safe_free(&zone_list_file_name);
	if (zone_file_name) safe_free(&zone_file_name);
	if (new_zone_file_name) safe_free(&new_zone_file_name);
	if (short_new_zone_file_name) safe_free(&short_new_zone_file_name);
	if (tmp_file_name) safe_free(&tmp_file_name);
	serial_clean(serial);

	return result;
}

unsigned int remove_zone(char * cmd)
{
	unsigned int result = FALSE;
	unsigned int zone_delete = FALSE;
	unsigned int in_zone = FALSE;
	unsigned int comp = 0;
	char * unique = NULL;
	char * zone_name = NULL;
	char * zone_file_name = NULL;
	char * tmp_file_name = NULL;

	char * buff = NULL;
	char * tmp = NULL;
	char * tmp2 = NULL;

	FILE * zf = NULL;
	FILE * tmpf = NULL;

	zone_name = get_word_by_number(cmd, 4);

	if (!zone_name) {
		result = FALSE;
		goto remove_zone_end;
	}

	// BEGIN TO PARSE LIST OF ZONES
	unique = create_unique_id();

	zone_file_name = strdup(DNS_ZONE_FILE);

	tmp_file_name = merge_strings(2, DNS_DB_DIR, unique);

	zf = fopen(zone_file_name, "r");
	if (!zf) {
		fprintf(stderr, "Failed to open zf\n");
		result = FALSE;
		goto remove_zone_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(zf);
		fprintf(stderr, "Failed to open tmpf\n");
		result = FALSE;
		goto remove_zone_end;
	}

	in_zone = FALSE;
	while (!feof(zf)) {
		buff = read_string(zf);
		if (!buff)
			break;

		tmp = get_word_by_number(buff, 1);

		if (!tmp) {
			fprintf(tmpf, "%s\n", buff);
			if (buff) safe_free(&buff);
			continue;
		}

  // Searching zone mode...
		if (in_zone == FALSE) {
			comp = 0;
			tmp2 = get_word_by_number(buff, 2);

			if (tmp2) {
				tmp2 = cut_quotes(tmp2);

				if (strcmp(tmp, "zone") == 0)
					comp++;

				if (strcmp(tmp2, zone_name) == 0)
					comp++;

    // Check: does we find our zone ?
				if (comp == 2) {
					in_zone = TRUE;
					zone_delete = TRUE;
				}
			}

			// We still outside of zone ?
			if (in_zone == FALSE) {
				fprintf(tmpf, "%s\n", buff);
			}

			// Print \n if zone end
			if (strcmp(tmp, "};") == 0)
				fprintf(tmpf, "\n");
		}

		// Searching end of the zone...
		else if (in_zone == TRUE) {
			if (strcmp(tmp, "};") == 0)
				in_zone = FALSE;
		}

		if (buff) safe_free(&buff);
		if (tmp) safe_free(&tmp);
		if (tmp2) safe_free(&tmp2);
	}

	fclose(zf);
	fclose(tmpf);

	// END OF PARSE LIST OF ZONES

	if (unlink(zone_file_name) == -1) {
		perror("Remove file:");
	}

	if (rename(tmp_file_name, zone_file_name) == -1) {
		perror("Rename file:");
		result = FALSE;
		goto remove_zone_end;
	}

	zone_file_name = merge_strings(3, DNS_DB_DIR, zone_name, ".zone");

	if (unlink(zone_file_name) == -1) {
		perror("Remove file:");
	}

	result = zone_delete;
remove_zone_end:

	if (unique) safe_free(&unique);
	if (zone_name) safe_free(&zone_name);
	if (zone_file_name) safe_free(&zone_file_name);
	if (tmp_file_name) safe_free(&tmp_file_name);

	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);
	if (tmp2) safe_free(&tmp2);

	return result;
}


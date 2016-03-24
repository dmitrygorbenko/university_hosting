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
#include "zone_info.h"

using namespace std;

extern char * return_message;

// Can Info:
// MX - tested - OK
// NS - not supported
// A - not supported
unsigned int info_zone_info(char * cmd)
{
	unsigned int result = FALSE;

	char * str_result = NULL;
	char * zone_name = NULL;
	char * type = NULL;

	char * zone_file_name = NULL;

	char * buff = NULL;
	char * tmp = NULL;
	char * tmp2 = NULL;
	char * tmp3 = NULL;
	char * tmp4 = NULL;
	char * tmp5 = NULL;

	FILE * zf = NULL;

	zone_name = get_word_by_number(cmd, 4);
	type = get_word_by_number(cmd, 5);

	if (!zone_name || !type) {
		result = FALSE;
		goto info_zone_info_end;
	}

	if (strcmp(type, "MX") != 0) {
		result = FALSE;
		goto info_zone_info_end;
	}

	zone_file_name = merge_strings(3, DNS_DB_DIR, zone_name, ".zone");

	zf = fopen(zone_file_name, "r");

	if (!zf) {
		result = FALSE;
		goto info_zone_info_end;
	}

	while (!feof(zf)) {
		buff = read_string(zf);
		if (!buff)
			break;

		// word "IN"
		tmp = get_word_by_number(buff, 1);
		// type
		tmp2 = get_word_by_number(buff, 2);

		if (!tmp || !tmp2) {
			if (buff) safe_free(&buff);
			if (tmp) safe_free(&tmp);
			if (tmp2) safe_free(&tmp2);
			continue;
		}

		if (strcmp(tmp, "IN") == 0) {
			// Ups, find... Check for type
			if (strcmp(tmp2, type) == 0) {
				tmp3 = get_word_by_number(buff, 3);
				tmp4 = get_word_by_number(buff, 4);

				if (!tmp3 || !tmp4) {
					if (buff) safe_free(&buff);
					if (tmp) safe_free(&tmp);
					if (tmp2) safe_free(&tmp2);
					if (tmp3) safe_free(&tmp3);
					if (tmp4) safe_free(&tmp4);
					continue;
				}

				if (str_result) {
					tmp5 = merge_strings(5, str_result, "\n", tmp3, " ", tmp4);
					safe_free(&str_result);
					str_result = tmp5;

				}
				else
					str_result = merge_strings(3, tmp3, " ", tmp4);
			}
		}

		if (buff) safe_free(&buff);
		if (tmp) safe_free(&tmp);
		if (tmp2) safe_free(&tmp2);
		if (tmp3) safe_free(&tmp3);
		if (tmp4) safe_free(&tmp4);
	}

	fclose(zf);

	result = TRUE;
info_zone_info_end:

	if (zone_name) safe_free(&zone_name);
	if (zone_file_name) safe_free(&zone_file_name);
	if (type) safe_free(&type);

	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);
	if (tmp2) safe_free(&tmp2);
	if (tmp3) safe_free(&tmp3);
	if (tmp4) safe_free(&tmp4);

	if (result == TRUE) {
		return_message = NULL;
		return_message = base64_encode(str_result, strlen(str_result));
		if (!return_message)
			return FALSE;

		safe_free(&str_result);
	}
	return result;
}

// Can Info:
// Report content of zone at plain text
unsigned int info_zone(char * cmd)
{
	unsigned int result = FALSE;

	char * str_result = NULL;
	char * zone_name = NULL;
	char * zone_file_name = NULL;

	char * buff = NULL;
	char * tmp = NULL;

	FILE * zf = NULL;

	zone_name = get_word_by_number(cmd, 4);

	if (!zone_name) {
		result = FALSE;
		goto info_zone_end;
	}

	zone_file_name = merge_strings(3, DNS_DB_DIR, zone_name, ".zone");

	zf = fopen(zone_file_name, "r");

	if (!zf) {
		result = FALSE;
		goto info_zone_end;
	}

	while (!feof(zf)) {
		buff = read_string(zf);
		if (!buff)
			break;

		if (!str_result)
			str_result = strdup(buff);
		else {
			tmp = merge_strings(3, str_result, "\n", buff);
			safe_free(&str_result);
			str_result = tmp;
		}

		if (buff) safe_free(&buff);
	}

	fclose(zf);

	result = TRUE;
info_zone_end:

	if (zone_name) safe_free(&zone_name);
	if (zone_file_name) safe_free(&zone_file_name);

	if (buff) safe_free(&buff);

	if (result == TRUE) {
		return_message = NULL;
		return_message = base64_encode(str_result, strlen(str_result));
		if (!return_message)
			return FALSE;

		safe_free(&str_result);
	}

	return result;
}

// Can Create:
// MX - tested - OK
// NS - tested - OK
unsigned int create_new_zone_info(char * cmd)
{
	unsigned int find_origin = 0;
	unsigned int result = FALSE;

	char * unique = NULL;
	char * zone_name = NULL;

	char * type = NULL;
	char * prior = NULL;
	char * record = NULL;

	char * buff = NULL;
	char * tmp = NULL;

	char * zone_file_name = NULL;
	char * tmp_file_name = NULL;

	FILE * zf = NULL;
	FILE * tmpf = NULL;

	zone_name = get_word_by_number(cmd, 4);
	type = get_word_by_number(cmd, 5);

	if (!zone_name || !type) {
		result = FALSE;
		goto new_zone_info_end;
	}

	if (strcmp(type, "MX") != 0 &&
		strcmp(type, "NS") != 0) {
		result = FALSE;
		goto new_zone_info_end;
	}

	if (strcmp(type, "MX") == 0) {
		prior = get_word_by_number(cmd, 6);
		record = get_word_by_number(cmd, 7);

		if (!prior) {
			result = FALSE;
			goto new_zone_info_end;
		}
	}
	else
		record = get_word_by_number(cmd, 6);

	if (!record) {
		result = FALSE;
		goto new_zone_info_end;
	}

	if (update_zone_serial(zone_name) == FALSE) {
		result = FALSE;
		goto new_zone_info_end;
	}

	unique = create_unique_id();

	zone_file_name = merge_strings(3, DNS_DB_DIR, zone_name, ".zone");
	tmp_file_name = merge_strings(2, DNS_DB_DIR, unique);

	zf = fopen(zone_file_name, "r");

	if (!zf) {
		result = FALSE;
		fprintf(stderr, "Failed to open zf\n");
		goto new_zone_info_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(zf);
		fprintf(stderr, "Failed to open tmpf\n");
		result = FALSE;
		goto new_zone_info_end;
	}

	while (!feof(zf)) {
		buff = read_string(zf);
		if (!buff)
			break;

		// word "IN"
		tmp = get_word_by_number(buff, 1);

		if (!tmp) {
			fprintf(tmpf, "%s\n", buff);
			if (buff) safe_free(&buff);
			if (tmp) safe_free(&tmp);
			continue;
		}

		if (strcmp(tmp, "$ORIGIN") == 0) {
			find_origin++;
		}

		if (find_origin == 2) {
			if (strcmp(type, "MX") == 0)
				fprintf(tmpf, "\t\tIN\t%s\t%s\t%s\n", type, prior, record);
			else
				fprintf(tmpf, "\t\tIN\t%s\t%s\n", type, record);
			find_origin = 0;
		}

		fprintf(tmpf, "%s\n", buff);

		if (buff) safe_free(&buff);
		if (tmp) safe_free(&tmp);
	}

	fclose(zf);
	fclose(tmpf);

	if (unlink(zone_file_name) == -1) {
		perror("Remove file:");
	}

	if (rename(tmp_file_name, zone_file_name) == -1) {
		perror("Rename file:");
		result = FALSE;
		goto new_zone_info_end;
	}

	result = TRUE;
new_zone_info_end:

	if (zone_name) safe_free(&zone_name);
	if (zone_file_name) safe_free(&zone_file_name);
	if (tmp_file_name) safe_free(&tmp_file_name);
	if (type) safe_free(&type);
	if (prior) safe_free(&prior);
	if (record) safe_free(&record);
	if (unique) safe_free(&unique);
	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);

	return result;
}

// Can Update:
// MX - tested - OK
// NS - not supported
// A - not supported
unsigned int update_zone_info(char * cmd)
{
	unsigned int result = FALSE;
	unsigned int find_record = FALSE;
	unsigned int index = 0;

	char * unique = NULL;
	char * zone_name = NULL;

	char * type = NULL;
	char * prior = NULL;

	char * new_type = NULL;
	char * new_prior = NULL;
	char * new_record = NULL;

	char * zone_file_name = NULL;
	char * tmp_file_name = NULL;

	char * buff = NULL;
	char * tmp = NULL;
	char * tmp2 = NULL;
	char * tmp3 = NULL;
	char * tmp4 = NULL;

	FILE * zf = NULL;
	FILE * tmpf = NULL;

//quake.hosting.ai                IN SOA  ns.deep.lan. root.deep.lan. (
//                                2005080600 ; serial
//                                10800      ; refresh (3 hours)
//                                900        ; retry (15 minutes)
//                                604800     ; expire (1 week)
//                                86400 )     ; minimum (1 day)
//                IN      NS      ns.deep.lan.
//                IN      MX      10      mx.deep.lan.
//                IN      A       172.16.212.5

//DNS UPDATE ZONE {zone-name} {type} [prior] {new_type} [new_prior] {new_record}
//  1   2     3         4       5       6       7         7 or 8       8 or 9
	// Initiallly index is set to 4 - zone name
	index = 4;

	zone_name = get_word_by_number(cmd, index++);

	// fetch old records
	type = get_word_by_number(cmd, index++);

	fprintf(stderr, "Called: %s\n", cmd);

	if (!zone_name || !type) {
		result = FALSE;
		fprintf(stderr, "Failed on check zone_name, type\n");
		goto update_zone_info_end;
	}

	if (strcmp(type, "MX") != 0) {
		result = FALSE;
		fprintf(stderr, "Failed on type check\n");
		goto update_zone_info_end;
	}

	if (strcmp(type, "MX") == 0) {
		prior = get_word_by_number(cmd, index++);
		if (!prior) {
			result = FALSE;
			fprintf(stderr, "Failed to get prior\n");
			goto update_zone_info_end;
		}
	}

	// fetch new records
	new_type = get_word_by_number(cmd, index++);

	if (!new_type) {
		result = FALSE;
		fprintf(stderr, "Failed on check and type\n");
		goto update_zone_info_end;
	}

	if (strcmp(new_type, "MX") != 0) {
		result = FALSE;
		fprintf(stderr, "Failed on new_type\n");
		goto update_zone_info_end;
	}

	if (strcmp(new_type, "MX") == 0) {
		new_prior = get_word_by_number(cmd, index++);
		if (!new_prior) {
			result = FALSE;
			fprintf(stderr, "Failed on get new_prior\n");
			goto update_zone_info_end;
		}
	}

	new_record = get_word_by_number(cmd, index++);
	if (!new_record) {
		result = FALSE;
		fprintf(stderr, "Failed on get new_record\n");
		goto update_zone_info_end;
	}

	// Ok, now - update serial number
	if (update_zone_serial(zone_name) == FALSE) {
		result = FALSE;
		fprintf(stderr, "Failed on update serial\n");
		goto update_zone_info_end;
	}

	unique = create_unique_id();

	zone_file_name = merge_strings(3, DNS_DB_DIR, zone_name, ".zone");
	tmp_file_name = merge_strings(2, DNS_DB_DIR, unique);

	zf = fopen(zone_file_name, "r");

	if (!zf) {
		result = FALSE;
		fprintf(stderr, "Failed on open zf\n");
		goto update_zone_info_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(zf);
		fprintf(stderr, "Failed to open tmpf\n");
		result = FALSE;
		goto update_zone_info_end;
	}

	find_record = FALSE;
	while (!feof(zf)) {
		buff = read_string(zf);
		if (!buff)
			break;

		// word "IN"
		tmp = get_word_by_number(buff, 1);
		// type
		tmp2 = get_word_by_number(buff, 2);

		if (!tmp || !tmp2) {
			fprintf(tmpf, "%s\n", buff);
			if (buff) safe_free(&buff);
			if (tmp) safe_free(&tmp);
			if (tmp2) safe_free(&tmp2);
			continue;
		}

		if (strcmp(tmp, "IN") == 0) {
			// Ups, find... Check for type
			if (strcmp(tmp2, type) == 0) {
				tmp3 = get_word_by_number(buff, 3);
				tmp4 = get_word_by_number(buff, 4);

				if (!tmp3 || !tmp4) {
					result = FALSE;
					fprintf(stderr, "Failed to get tmp3 and tmp4\n");
					goto update_zone_info_end;
				}

				if (strcmp(tmp3, prior) == 0) {
					find_record = TRUE;
				}
			}
		}

		if (find_record == TRUE) {
			safe_free(&buff);

			if (strcmp(new_type, "MX") == 0)
				buff = merge_strings(7, "\t\tIN\t", new_type, "\t", new_prior, "\t", new_record, "");
			else
				buff = merge_strings(5, "\t\tIN\t", new_type, "\t", new_record, "");

			fprintf(stderr, "UPDATE: %s\n", buff);

			fprintf(tmpf, "%s\n", buff);
			find_record = FALSE;
			result = TRUE;
		}
		else {
			fprintf(tmpf, "%s\n", buff);
		}

		if (buff) safe_free(&buff);
		if (tmp) safe_free(&tmp);
		if (tmp2) safe_free(&tmp2);
		if (tmp3) safe_free(&tmp3);
		if (tmp4) safe_free(&tmp4);
	}

	fclose(zf);
	fclose(tmpf);

	if (unlink(zone_file_name) == -1) {
		perror("Remove file:");
	}

	if (rename(tmp_file_name, zone_file_name) == -1) {
		perror("Rename file:");
		result = FALSE;
		goto update_zone_info_end;
	}

update_zone_info_end:

	if (unique) safe_free(&unique);
	if (zone_name) safe_free(&zone_name);
	if (zone_file_name) safe_free(&zone_file_name);
	if (tmp_file_name) safe_free(&tmp_file_name);

	if (type) safe_free(&type);
	if (prior) safe_free(&prior);

	if (new_type) safe_free(&new_type);
	if (new_prior) safe_free(&new_prior);
	if (new_record) safe_free(&new_record);

	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);
	if (tmp2) safe_free(&tmp2);
	if (tmp3) safe_free(&tmp3);
	if (tmp4) safe_free(&tmp4);

	return result;
}

// Can Update:
// Update whole zone - just rewrite zone
unsigned int update_zone_manual(char * cmd)
{
	unsigned int result = FALSE;

	char * zone_name = NULL;
	char * zone_content = NULL;

	char * zone_file_name = NULL;
	char * tmp_file_name = NULL;

	char * unique = NULL;
	char * tmp = NULL;

	FILE * zf = NULL;
	FILE * tmpf = NULL;

	zone_name = get_word_by_number(cmd, 4);
	zone_content = get_word_by_number(cmd, 5);

	if (!zone_name || !zone_content) {
		result = FALSE;
		fprintf(stderr, "Failed on check zone_name, zone_content\n");
		goto update_zone_manual_end;
	}

	if (base64_decode(zone_content, &tmp) == -1) {
		result = FALSE;
		fprintf(stderr, "Failed on base64_decode\n");
		goto update_zone_manual_end;
	}

	safe_free(&zone_content);
	zone_content = tmp;

	unique = create_unique_id();

	zone_file_name = merge_strings(3, DNS_DB_DIR, zone_name, ".zone");
	tmp_file_name = merge_strings(2, DNS_DB_DIR, unique);

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(zf);
		fprintf(stderr, "Failed to open tmpf\n");
		result = FALSE;
		goto update_zone_manual_end;
	}

	fprintf(tmpf, "%s\n", zone_content);

	fclose(tmpf);

	if (unlink(zone_file_name) == -1) {
		perror("Remove file:");
	}

	if (rename(tmp_file_name, zone_file_name) == -1) {
		perror("Rename file:");
		result = FALSE;
		goto update_zone_manual_end;
	}

	result = TRUE;
update_zone_manual_end:

	if (unique) safe_free(&unique);
	if (zone_name) safe_free(&zone_name);
	if (zone_content) safe_free(&zone_content);
	if (zone_file_name) safe_free(&zone_file_name);
	if (tmp_file_name) safe_free(&tmp_file_name);

	return result;
}

// Can remove:
// MX - tested - OK
// NS - tested - OK
unsigned int remove_zone_info(char * cmd)
{
	unsigned int result = FALSE;
	unsigned int echo_str = FALSE;
	unsigned int record_delete = FALSE;

	char * unique = NULL;
	char * zone_name = NULL;

	char * type = NULL;
	char * prior = NULL;
	char * record = NULL;

	char * zone_file_name = NULL;
	char * tmp_file_name = NULL;

	char * buff = NULL;
	char * tmp = NULL;
	char * tmp2 = NULL;
	char * tmp3 = NULL;

	FILE * zf = NULL;
	FILE * tmpf = NULL;

	zone_name = get_word_by_number(cmd, 4);
	type = get_word_by_number(cmd, 5);

	fprintf(stderr, "CALLED: %s\n", cmd);

	if (!zone_name || !type) {
		fprintf(stderr, "FAILED ON zone and type check\n");
		result = FALSE;
		goto remove_zone_info_end;
	}

	if (strcmp(type, "MX") != 0 &&
		strcmp(type, "NS") != 0) {
		fprintf(stderr, "FAILED ON type check\n");
		result = FALSE;
		goto remove_zone_info_end;
	}

	if (strcmp(type, "MX") == 0) {
		prior = get_word_by_number(cmd, 6);
		if (!prior) {
			fprintf(stderr, "FAILED ON get prior\n");
			result = FALSE;
			goto remove_zone_info_end;
		}
	}

	if (strcmp(type, "NS") == 0) {
		record = get_word_by_number(cmd, 6);
		if (!record) {
			fprintf(stderr, "FAILED ON get record\n");
			result = FALSE;
			goto remove_zone_info_end;
		}
	}

	if (update_zone_serial(zone_name) == FALSE) {
		result = FALSE;
		fprintf(stderr, "FAILED ON update serial\n");
		goto remove_zone_info_end;
	}

	unique = create_unique_id();

	zone_file_name = merge_strings(3, DNS_DB_DIR, zone_name, ".zone");
	tmp_file_name = merge_strings(2, DNS_DB_DIR, unique);

	zf = fopen(zone_file_name, "r");

	if (!zf) {
		result = FALSE;
		fprintf(stderr, "FAILED ON open zf\n");
		goto remove_zone_info_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(zf);
		fprintf(stderr, "Failed to open tmpf\n");
		result = FALSE;
		goto remove_zone_info_end;
	}

	while (!feof(zf)) {
		echo_str = TRUE;
		buff = read_string(zf);
		if (!buff)
			break;

		// word "IN"
		tmp = get_word_by_number(buff, 1);
		// type
		tmp2 = get_word_by_number(buff, 2);

		if (!tmp || !tmp2) {
			fprintf(tmpf, "%s\n", buff);
			if (buff) safe_free(&buff);
			if (tmp) safe_free(&tmp);
			if (tmp2) safe_free(&tmp2);
			continue;
		}

		if (strcmp(tmp, "IN") == 0) {
			// Ups, find... Check for type
			if (strcmp(tmp2, type) == 0) {
				// If type is NS - is't need to check record
				if (strcmp(type, "NS") == 0) {
					tmp3 = get_word_by_number(buff, 3);

					if (!tmp3) {
						result = FALSE;
						goto remove_zone_info_end;
					}

					if (strcmp(tmp3, record) == 0) {
						echo_str = FALSE;
					}
				}
				// Type is MX - check next...
				else {
					// If type is MX, it's need to check prior
					if (strcmp(type, "MX") == 0) {
						// prior
						tmp3 = get_word_by_number(buff, 3);

						if (!tmp3) {
							result = FALSE;
							goto remove_zone_info_end;
						}

						if (strcmp(tmp3, prior) == 0) {
							echo_str = FALSE;
						}
					}
				}
			}
		}


		if (echo_str == TRUE)
			fprintf(tmpf, "%s\n", buff);
		else {
			fprintf(stderr, "RECORD DELETED\n");
			record_delete = TRUE;
		}

		if (buff) safe_free(&buff);
		if (tmp) safe_free(&tmp);
		if (tmp2) safe_free(&tmp2);
		if (tmp3) safe_free(&tmp3);
	}

	fclose(zf);
	fclose(tmpf);

	if (unlink(zone_file_name) == -1) {
		perror("Remove file:");
	}

	if (rename(tmp_file_name, zone_file_name) == -1) {
		perror("Rename file:");
		result = FALSE;
		goto remove_zone_info_end;
	}

	result = record_delete;
remove_zone_info_end:

	if (unique) safe_free(&unique);
	if (zone_name) safe_free(&zone_name);
	if (zone_file_name) safe_free(&zone_file_name);
	if (tmp_file_name) safe_free(&tmp_file_name);
	if (type) safe_free(&type);
	if (prior) safe_free(&prior);
	if (record) safe_free(&record);

	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);
	if (tmp2) safe_free(&tmp2);
	if (tmp3) safe_free(&tmp3);

	return result;
}

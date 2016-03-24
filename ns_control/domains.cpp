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
#include "domains.h"

using namespace std;

// Can Create:
// MX - tested - OK
// NS - tested - OK
// A - tested - OK
// CNAME - tested - OK
unsigned int create_new_domain(char * cmd)
{
	unsigned int result = FALSE;

	char * zone_name = NULL;
	char * domain_name = NULL;
	char * type = NULL;
	char * prior = NULL;
	char * record = NULL;
	char * new_record = NULL;

	char * zone_file_name = NULL;
	FILE * zf = NULL;

	zone_name = get_word_by_number(cmd, 4);
	domain_name = get_word_by_number(cmd, 5);
	type = get_word_by_number(cmd, 6);

	if (!zone_name || !domain_name || !type) {
		result = FALSE;
		goto new_domain_end;
	}

	if (strcmp(type, "A") != 0 &&
		strcmp(type, "CNAME") != 0 &&
		strcmp(type, "MX") != 0 &&
		strcmp(type, "NS") != 0) {
		result = FALSE;
		goto new_domain_end;
	}

	if (strcmp(type, "MX") == 0) {
		prior = get_word_by_number(cmd, 7);
		record = get_word_by_number(cmd, 8);

		if (!prior) {
			result = FALSE;
			goto new_domain_end;
		}
	}
	else
		record = get_word_by_number(cmd, 7);

	if (!record) {
		result = FALSE;
		goto new_domain_end;
	}

	if (strcmp(type, "MX") == 0)
		new_record = merge_strings(8, domain_name, "\t\tIN\t", type, "\t", prior, "\t", record, "\n");
	else
		new_record = merge_strings(6, domain_name, "\t\tIN\t", type, "\t", record, "\n");

	if (update_zone_serial(zone_name) == FALSE) {
		result = FALSE;
		goto new_domain_end;
	}

	zone_file_name = merge_strings(3, DNS_DB_DIR, zone_name, ".zone");

	zf = fopen(zone_file_name, "a");

	if (!zf) {
		result = FALSE;
		goto new_domain_end;
	}

	fprintf(zf, new_record);

	fclose(zf);

	result = TRUE;
new_domain_end:

	if (zone_name) safe_free(&zone_name);
	if (zone_file_name) safe_free(&zone_file_name);
	if (domain_name) safe_free(&domain_name);
	if (type) safe_free(&type);
	if (prior) safe_free(&prior);
	if (record) safe_free(&record);
	if (new_record) safe_free(&new_record);

	return result;
}

// Can Remove:
// MX - tested - OK
// NS - tested - OK
// A - tested - OK
// CNAME - tested - OK
unsigned int remove_domain(char * cmd)
{
	unsigned int result = FALSE;
	unsigned int echo_str = FALSE;
	unsigned int record_delete = FALSE;

	char * unique = NULL;
	char * zone_name = NULL;
	char * domain_name = NULL;
	char * type = NULL;
	char * prior = NULL;

	char * zone_file_name = NULL;
	char * tmp_file_name = NULL;

	char * buff = NULL;
	char * tmp = NULL;
	char * tmp2 = NULL;
	char * tmp3 = NULL;
	char * tmp4 = NULL;

	FILE * zf = NULL;
	FILE * tmpf = NULL;

	zone_name = get_word_by_number(cmd, 4);
	domain_name = get_word_by_number(cmd, 5);
	type = get_word_by_number(cmd, 6);

	if (!zone_name || !domain_name || !type) {
		result = FALSE;
		goto remove_domain_end;
	}

	if (strcmp(type, "A") != 0 &&
		strcmp(type, "CNAME") != 0 &&
		strcmp(type, "MX") != 0 &&
		strcmp(type, "NS") != 0) {
		result = FALSE;
		goto remove_domain_end;
	}

	if (strcmp(type, "MX") == 0) {
		prior = get_word_by_number(cmd, 7);
		if (!prior) {
			result = FALSE;
			goto remove_domain_end;
		}
	}

	if (update_zone_serial(zone_name) == FALSE) {
		result = FALSE;
		goto remove_domain_end;
	}

	unique = create_unique_id();

	zone_file_name = merge_strings(3, DNS_DB_DIR, zone_name, ".zone");
	tmp_file_name = merge_strings(2, DNS_DB_DIR, unique);

	zf = fopen(zone_file_name, "r");

	if (!zf) {
		result = FALSE;
		goto remove_domain_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(zf);
		fprintf(stderr, "Failed to open tmpf\n");
		result = FALSE;
		goto remove_domain_end;
	}

	while (!feof(zf)) {
		echo_str = TRUE;
		buff = read_string(zf);
		if (!buff)
			break;

		// domain name
		tmp = get_word_by_number(buff, 1);
		// word "IN"
		tmp2 = get_word_by_number(buff, 2);
		// type
		tmp3 = get_word_by_number(buff, 3);

		if (!tmp || !tmp2 || !tmp3) {
			fprintf(tmpf, "%s\n", buff);
			if (buff) safe_free(&buff);
			if (tmp) safe_free(&tmp);
			if (tmp2) safe_free(&tmp2);
			if (tmp3) safe_free(&tmp3);
			continue;
		}

		if (strcmp(tmp, domain_name) == 0) {
			// Ups, find... Check for type
			if (strcmp(tmp3, type) == 0) {
				// If type is NS or CNAME - all done
				if (strcmp(type, "NS") == 0 ||
					strcmp(type, "CNAME") == 0) {
						echo_str = FALSE;
				}
				// Type is A or MX - check next...
				else {
					// If type is MX, it's need to check prior
					if (strcmp(type, "MX") == 0) {
						// prior
						tmp4 = get_word_by_number(buff, 4);

						if (!tmp4) {
							result = FALSE;
							goto remove_domain_end;
						}

						if (strcmp(tmp4, prior) == 0) {
							echo_str = FALSE;
						}
					}
					// Type is A. Not need check
					else {
						echo_str = FALSE;
					}
				}
			}
		}


		if (echo_str == TRUE)
			fprintf(tmpf, "%s\n", buff);
		else {
			record_delete = TRUE;
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
		goto remove_domain_end;
	}

	result = record_delete;
remove_domain_end:

	if (unique) safe_free(&unique);
	if (zone_name) safe_free(&zone_name);
	if (zone_file_name) safe_free(&zone_file_name);
	if (tmp_file_name) safe_free(&tmp_file_name);
	if (domain_name) safe_free(&domain_name);
	if (type) safe_free(&type);
	if (prior) safe_free(&prior);

	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);
	if (tmp2) safe_free(&tmp2);
	if (tmp3) safe_free(&tmp3);
	if (tmp4) safe_free(&tmp4);

	return result;
}

// Can Update:
// MX - tested - OK
// NS - tested - OK
// A - tested - OK
// CNAME - tested - OK
unsigned int update_domain(char * cmd)
{
	unsigned int result = FALSE;
	unsigned int find_record = FALSE;
	unsigned int index = 0;

	char * unique = NULL;
	char * zone_name = NULL;

	char * domain_name = NULL;
	char * type = NULL;
	char * prior = NULL;

	char * new_domain_name = NULL;
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

//DNS UPDATE DOMAIN {zone-name} {domain-name} {type} [prior] {new_domain-name} {new_type} [new_prior] {new_record}
//  1   2     3         4              5        6       7          7 or 8        8 or 9     9 or 10   9 or 10 or 11
	// Initiallly index is set to 4 - zone name
	index = 4;

	zone_name = get_word_by_number(cmd, index++);

	// fetch old records
	domain_name = get_word_by_number(cmd, index++);
	type = get_word_by_number(cmd, index++);

	fprintf(stderr, "Called: %s\n", cmd);

	if (!zone_name || !domain_name || !type) {
		result = FALSE;
		fprintf(stderr, "Failed on check zone_name, domain, type\n");
		goto update_domain_end;
	}

	if (strcmp(type, "A") != 0 &&
		strcmp(type, "CNAME") != 0 &&
		strcmp(type, "MX") != 0 &&
		strcmp(type, "NS") != 0) {
		result = FALSE;
		fprintf(stderr, "Failed on type check\n");
		goto update_domain_end;
	}

	if (strcmp(type, "MX") == 0) {
		prior = get_word_by_number(cmd, index++);
		if (!prior) {
			result = FALSE;
			fprintf(stderr, "Failed to get prior\n");
			goto update_domain_end;
		}
	}

	// fetch new records
	new_domain_name = get_word_by_number(cmd, index++);
	new_type = get_word_by_number(cmd, index++);

	if (!new_domain_name || !new_type) {
		result = FALSE;
		fprintf(stderr, "Failed on check domain and type\n");
		goto update_domain_end;
	}

	if (strcmp(new_type, "A") != 0 &&
		strcmp(new_type, "CNAME") != 0 &&
		strcmp(new_type, "MX") != 0 &&
		strcmp(new_type, "NS") != 0) {
		result = FALSE;
		fprintf(stderr, "Failed on new_type\n");
		goto update_domain_end;
	}

	if (strcmp(new_type, "MX") == 0) {
		new_prior = get_word_by_number(cmd, index++);
		if (!new_prior) {
			result = FALSE;
			fprintf(stderr, "Failed on get new_prior\n");
			goto update_domain_end;
		}
	}

	new_record = get_word_by_number(cmd, index++);
	if (!new_record) {
		result = FALSE;
		fprintf(stderr, "Failed on get new_record\n");
		goto update_domain_end;
	}

	// Ok, now - update serial number
	if (update_zone_serial(zone_name) == FALSE) {
		result = FALSE;
		fprintf(stderr, "Failed on update serial\n");
		goto update_domain_end;
	}

	unique = create_unique_id();

	zone_file_name = merge_strings(3, DNS_DB_DIR, zone_name, ".zone");
	tmp_file_name = merge_strings(2, DNS_DB_DIR, unique);

	zf = fopen(zone_file_name, "r");

	if (!zf) {
		result = FALSE;
		fprintf(stderr, "Failed on open zf\n");
		goto update_domain_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		fclose(zf);
		printf("Failed to open tmpf\n");
		result = FALSE;
		goto update_domain_end;
	}

	find_record = FALSE;
	while (!feof(zf)) {
		buff = read_string(zf);
		if (!buff)
			break;

		// domain name
		tmp = get_word_by_number(buff, 1);
		// word "IN"
		tmp2 = get_word_by_number(buff, 2);
		// type
		tmp3 = get_word_by_number(buff, 3);

		if (!tmp || !tmp2 || !tmp3) {
			fprintf(tmpf, "%s\n", buff);
			if (buff) safe_free(&buff);
			if (tmp) safe_free(&tmp);
			if (tmp2) safe_free(&tmp2);
			if (tmp3) safe_free(&tmp3);
			continue;
		}

		if (strcmp(tmp, domain_name) == 0) {
			// Ups, find... Check for type
			if (strcmp(tmp3, type) == 0) {
				// If type is NS or CNAME - all done
				if (strcmp(type, "NS") == 0 ||
					strcmp(type, "CNAME") == 0) {
						find_record = TRUE;
				}
				// Type is A or MX - check next...
				else {
					// If type is MX, it's need to check prior
					if (strcmp(type, "MX") == 0) {
						// prior
						tmp4 = get_word_by_number(buff, 4);

						if (!tmp4) {
							result = FALSE;
							fprintf(stderr, "Failed to get tmp4\n");
							goto update_domain_end;
						}

						if (strcmp(tmp4, prior) == 0) {
							find_record = TRUE;
						}
					}
					// Type is A. Not need check
					else {
						find_record = TRUE;
					}
				}
			}
		}

		if (find_record == TRUE) {
			safe_free(&buff);

			if (strcmp(new_type, "MX") == 0)
				buff = merge_strings(8, new_domain_name, "\t\tIN\t", new_type, "\t", new_prior, "\t", new_record, "");
			else
				buff = merge_strings(6, new_domain_name, "\t\tIN\t", new_type, "\t", new_record, "");

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
		goto update_domain_end;
	}

update_domain_end:

	if (unique) safe_free(&unique);
	if (zone_name) safe_free(&zone_name);
	if (zone_file_name) safe_free(&zone_file_name);
	if (tmp_file_name) safe_free(&tmp_file_name);

	if (domain_name) safe_free(&domain_name);
	if (type) safe_free(&type);
	if (prior) safe_free(&prior);

	if (new_domain_name) safe_free(&new_domain_name);
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


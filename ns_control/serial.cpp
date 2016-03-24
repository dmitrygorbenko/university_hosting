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
#include <time.h>

#include "define.h"
#include "str.h"
#include "tools.h"
#include "serial.h"

using namespace std;

struct def_dns_serial * serial_create()
{
	time_t	t;
	struct tm * loc_time = NULL;
	char * tmp = NULL;
	struct def_dns_serial *res = NULL;

	res = (struct def_dns_serial *)malloc(sizeof(struct def_dns_serial));
	t = time(NULL);
	loc_time = localtime(&t);

	res->d_year = loc_time->tm_year + 1900;
	res->d_month = loc_time->tm_mon + 1;
	res->d_day = loc_time->tm_mday;

	res->year = int_to_string(res->d_year);

	tmp = int_to_string(res->d_month);

	if (res->d_month < 10) {
		res->month = strdup("0");
		res->month = sts(&(res->month), tmp);
	}
	else {
		res->month = int_to_string(res->d_month);
	}

	tmp = int_to_string(res->d_day);

	if (res->d_day < 10) {
		res->day = strdup("0");
		res->day = sts(&(res->day), tmp);
	}
	else {
		res->day = int_to_string(res->d_day);
	}

	safe_free(&tmp);
//	free(loc_time);

	res->num = strdup("00");

	res->serial_num = merge_strings(4, res->year, res->month, res->day, res->num);

	return res;
}

struct def_dns_serial *serial_increase(char *str)
{
	struct def_dns_serial *serial = NULL;
	unsigned int len;
	unsigned int number;
	unsigned int day;
	unsigned int month;
	unsigned int year;

	char * tmp1_1 = NULL, *tmp1_2 = NULL, *tmp1_3 = NULL, *tmp1_4 = NULL;
	char * tmp2_1 = NULL, *tmp2_2 = NULL, *tmp2_3 = NULL, *tmp2_4 = NULL;
	char * tmp = NULL;
	char *s1 = NULL, *s2 = NULL;

	if (!str)
		return NULL;

	len = strlen(str);

	serial = serial_create();
	serial_clean(serial);

	if (len == 10) {
		number = ((int)str[len-1] - 48) + ((int)str[len-2] - 48) * 10;
		day = ((int)str[len-3] - 48) + ((int)str[len-4] - 48) * 10;
		month = ((int)str[len-5] - 48) + ((int)str[len-6] - 48) * 10;
		year = ((int)str[len-7] - 48) + ((int)str[len-8] - 48) * 10 + ((int)str[len-9] - 48) * 100 + ((int)str[len-10] - 48) * 1000;

		if (year < serial->d_year)
			year = serial->d_year;
		if (year > 2015)
			year = 2015;

		if (month < 1)
			month = 1;
		if (month > 12)
			month = 12;

		if (day < 0)
			day = 0;

		if (month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12) {
			if (day > 31)
				day = 31;
		}
		if (month == 4 || month == 6 || month == 9 || month == 11) {
			if (day > 30)
				day = 30;
		}
		if (month == 2) {
			if (year % 4 == 0) {
				if (day > 29)
					day = 29;
			}
			else {
				if (day > 28)
					day = 28;
			}
		}

		if (number < 0)
			number = 0;
		if (number > 99)
			number = 99;

		if (number < 99)
			number++;

		// this date we read from file
		if (number < 10) {
			tmp = int_to_string(number);
			tmp1_1 = merge_strings(2, "0", tmp);
			safe_free(&tmp);
		}
		else {
			tmp1_1 = int_to_string(number);
		}

		if (day < 10) {
			tmp = int_to_string(day);
			tmp1_2 = merge_strings(2, "0", tmp);
			safe_free(&tmp);
		}
		else {
			tmp1_2 = int_to_string(day);
		}

		if (month < 10) {
			tmp = int_to_string(month);
			tmp1_3 = merge_strings(2, "0", tmp);
			safe_free(&tmp);
		}
		else {
			tmp1_3 = int_to_string(month);
		}

		tmp1_4 = int_to_string(year);

		s1 = merge_strings(3, tmp1_4, tmp1_3, tmp1_2);
		safe_free(&tmp1_2); safe_free(&tmp1_3); safe_free(&tmp1_4);

		// and this is a current date
		tmp2_1 = strdup("00");

		if (serial->d_day < 10) {
			tmp = int_to_string(serial->d_day);
			tmp2_2 = merge_strings(2, "0", tmp);
			safe_free(&tmp);
		}
		else {
			tmp2_2 = int_to_string(serial->d_day);
		}

		if (serial->d_month < 10) {
			tmp = int_to_string(serial->d_month);
			tmp2_3 = merge_strings(2, "0", tmp);
			safe_free(&tmp);
		}
		else {
			tmp2_3 = int_to_string(serial->d_month);
		}

		tmp2_4 = int_to_string(serial->d_year);

		s2 = merge_strings(3, tmp2_4, tmp2_3, tmp2_2);
		safe_free(&tmp2_2); safe_free(&tmp2_3); safe_free(&tmp2_4);

		if (strcmp(s2, s1) > 0) {
			// New date
			serial->serial_num = merge_strings(2, s2, tmp2_1);
			safe_free(&tmp2_1);
		}
		else {
			// Old date
			serial->serial_num = merge_strings(2, s1, tmp1_1);
			safe_free(&tmp1_1);
		}

	}
	else {
		tmp2_1 = strdup("00");

		if (serial->d_day < 10) {
			tmp = int_to_string(serial->d_day);
			tmp2_2 = merge_strings(2, "0", tmp);
			safe_free(&tmp);
		}
		else {
			tmp2_2 = int_to_string(serial->d_day);
		}

		if (serial->d_month < 10) {
			tmp = int_to_string(serial->d_month);
			tmp2_3 = merge_strings(2, "0", tmp);
			safe_free(&tmp);
		}
		else {
			tmp2_3 = int_to_string(serial->d_month);
		}

		tmp2_4 = int_to_string(serial->d_year);

		serial->serial_num = merge_strings(4, tmp2_4, tmp2_3, tmp2_2, tmp2_1);
		safe_free(&tmp2_1); safe_free(&tmp2_2); safe_free(&tmp2_3); safe_free(&tmp2_4);
	}

	if (tmp1_1) safe_free(&tmp1_1);
	if (tmp1_2) safe_free(&tmp1_2);
	if (tmp1_3) safe_free(&tmp1_3);
	if (tmp1_4) safe_free(&tmp1_4);

	if (tmp2_1) safe_free(&tmp2_1);
	if (tmp2_2) safe_free(&tmp2_2);
	if (tmp2_3) safe_free(&tmp2_3);
	if (tmp2_4) safe_free(&tmp2_4);

	if (s1) safe_free(&s1);
	if (s2) safe_free(&s2);

	if (tmp) safe_free(&tmp);

	return serial;
}

void serial_clean(struct def_dns_serial * serial)
{
	if (!serial)
		return;

	if (serial->serial_num) safe_free(&(serial->serial_num));
	if (serial->year) safe_free(&(serial->year));
	if (serial->month) safe_free(&(serial->month));
	if (serial->day) safe_free(&(serial->day));
	if (serial->num) safe_free(&(serial->num));
}

unsigned int update_zone_serial(char * zone_name)
{
	struct def_dns_serial *serial = NULL;
	unsigned int result = FALSE;
	char * unique = NULL;
	char * zone_file_name = NULL;
	char * tmp_file_name = NULL;

	char * buff = NULL;
	char * tmp = NULL;
	char * tmp2 = NULL;
	char * tmp3 = NULL;

	FILE * zf = NULL;
	FILE * tmpf = NULL;

	// BEGIN TO PARSE ZONE FILE
	unique = create_unique_id();

	zone_file_name = merge_strings(3, DNS_DB_DIR, zone_name, ".zone");
	tmp_file_name = merge_strings(2, DNS_DB_DIR, unique);

	zf = fopen(zone_file_name, "r");
	if (!zf) {
		printf("Serial: Failed to open zf\n");
		result = FALSE;
		goto update_serial_end;
	}

	tmpf = fopen(tmp_file_name, "w+");
	if (!tmpf) {
		printf("Serial: Failed to open tmpf\n");
		result = FALSE;
		goto update_serial_end;
	}

	while (!feof(zf)) {
//%s			IN SOA  %s %s (
//				%s     ; serial
//				10800      ; refresh (3 hours)
//				900        ; retry (15 minutes)
//				604800     ; expire (1 week)
//				3600       ; negative caching (1 hour)
//				)
		buff = read_string(zf);
		if (!buff)
			break;

		tmp = get_word_by_number(buff, 1);
		tmp2 = get_word_by_number(buff, 2);
		tmp3 = get_word_by_number(buff, 3);

		if (tmp && tmp2 && tmp3) {

			if (strcmp(tmp2, "IN") == 0 &&
				strcmp(tmp3, "SOA") == 0) {

				fprintf(tmpf, "%s\n", buff);

				safe_free(&buff);
				buff = read_string(zf);
				if (!buff)
					break;
//				%s     ; serial

				safe_free(&tmp);
				tmp = get_word_by_number(buff, 1);
				serial = serial_increase(tmp);
				safe_free(&buff);
				safe_free(&tmp);

				buff = merge_strings(3, "\t\t\t\t", serial->serial_num, " ; serial");
			}
		}

		fprintf(tmpf, "%s\n", buff);

		if (buff) safe_free(&buff);
		if (tmp) safe_free(&tmp);
		if (tmp2) safe_free(&tmp2);
		if (tmp3) safe_free(&tmp3);
	}

	fclose(zf);
	fclose(tmpf);

	// END OF PARSE ZONE FILE

	if (unlink(zone_file_name) == -1) {
		perror("Serial: Remove file:");
	}

	if (rename(tmp_file_name, zone_file_name) == -1) {
		perror("Serial: Rename file:");
		result = FALSE;
		goto update_serial_end;
	}

	result = TRUE;
update_serial_end:

	if (unique) safe_free(&unique);
	if (zone_file_name) safe_free(&zone_file_name);
	if (tmp_file_name) safe_free(&tmp_file_name);

	if (buff) safe_free(&buff);
	if (tmp) safe_free(&tmp);
	if (tmp2) safe_free(&tmp2);
	if (tmp3) safe_free(&tmp3);

	serial_clean(serial);

	return result;

}

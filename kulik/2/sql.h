/***************************************************************************
 *   Copyright (C) 2003, 2004, 2005                                              *
 *   by Dmitriy Gorbenko. "XAI" University, Kharkov, Ukraine               *
 *   e-mail: nial@ukr.net                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 ***************************************************************************/

struct __p_answer {

	// system variables
	unsigned int result_code;
	char * source_query;
	char * errmsg;
	unsigned int rows_count;
	unsigned int row_number;

	// zone_cfg
	unsigned int id_table;
	unsigned int active;
	unsigned int max_lease_time;
	unsigned int initial;
	unsigned int grace;
	unsigned int pending;
	char * price;
	char * admin_email;
	
	// ns and mx 
	unsigned int id_own;

	// ns_cfg
	unsigned int id_ns;
	char * ns_title;
	char * ns_domain_name;
	char * ns_ip;
	unsigned int ns_master;

	// mx_cfg
	unsigned int id_mx;
	char * mx_title;
	char * mx_domain_name;
	char * mx_ip;
	unsigned int mx_prior;

	// main page
	unsigned int in_query_to_pay;
	unsigned int in_registered;
	unsigned int in_refused;
	unsigned int in_released;
	unsigned int in_to_create;

	// main domain information
	unsigned int id_domain;
	char * full_name;
	char * d_name;
	char * z_name;
	unsigned int d_type;

	// person information
	unsigned int id_person;
	char * firstname;
	char * lastname;
	char * company;
	char * country;
	char * region;
	unsigned int postal;
	char * city;
	char * address1;
	char * address2;
	char * phone;
	char * fax;
	char * email;
	unsigned int egrpou;
	char * userhdl;
	char * cash;
	unsigned int domain_registered;

	// technical information
	char * ns1;
	char * ns2;
	char * mx;
	char * query_from_host;
	char * confirm_from_host;
	
	// dates information
	char * query_post_date;
	char * confirm_date;
	char * payed;
	unsigned int lease_time;
	char * end_time;
	char * released;
	char * refused;
	char * holded;
	char * frozen;
	
	// descr information
	char * add_info;
	char * why_released;
	char * why_refused;
	char * notes;
	
	// domain status information
	char * status;
	enum {
		enum_wait_for_pay,
		enum_queued,
		enum_checked,
		enum_suspended,
		enum_refused,
		enum_recalled,
		enum_ok,
		enum_hold,
		enum_frozen,
		enum_released
	} domain_status;
	
	// and to make a list, this pointer
	struct __p_answer * next;
};

void sql_close_connect();
void init_pans(struct __p_answer * p);
void clear_pans(struct __p_answer * p);
char * sql_get_value(unsigned int row, unsigned int column);
char * sql_get_field_name(unsigned int column);
unsigned int sql_socket_init();
unsigned int sql_make_query(char * query);
unsigned int sql_get_rows_count();
unsigned int sql_get_cols_count();
unsigned int sql_get_length(unsigned int row, unsigned int column);
unsigned int sql_get_is_null(unsigned int row, unsigned int column);
struct __p_answer * parse_result(char * type, unsigned int id = 0);
struct __p_answer * sql_execute(char * q_type, unsigned int id_addict = 0);

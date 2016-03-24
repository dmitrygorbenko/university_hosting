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

#ifdef HAVE_CONFIG_H
	#include <config.h>
#endif

#ifdef PGSQL_USE
	#include "pgsql.h"       /* The local header */
#endif

#ifdef MYSQL_USE
	#include "mysql.h"       /* The local header */
#endif

#include "cstdio"
#include "cstdlib"
#include "cstring"
#include "unistd.h"

#include "define.h"
#include "param.h"
#include "common.h"
#include "str.h"
#include "sql.h"

using namespace std;

#ifdef PGSQL_USE
	extern struct __pgsql_connection pg_conn;
#endif

#ifdef MYSQL_USE
#endif

unsigned int sql_socket_init() 
{
#ifdef PGSQL_USE
	return pgsql_connect();
#endif
	
#ifdef MYSQL_USE
	return RES_FALSE;
#endif
};

unsigned int sql_make_query(char * query)
{
	if (!query)
		return SQL_RESULT_ERROR;

#ifdef PGSQL_USE
	return pgsql_make_query(query);
#endif

#ifdef MYSQL_USE
	return SQL_RESULT_ERROR;
#endif

};

void sql_close_connect()
{
#ifdef PGSQL_USE
	pgsql_close_connect();
#endif

#ifdef MYSQL_USE

#endif
};

char * sql_get_value(unsigned int row, unsigned int column)
{
	char * result_value = NULL;

#ifdef PGSQL_USE
	result_value = pgsql_get_value(row, column);
#endif

#ifdef MYSQL_USE
	result_value = mysql_get_value(row, column);
#endif

	return result_value;
}

unsigned int sql_get_rows_count()
{
#ifdef PGSQL_USE
	return pgsql_get_rows_count();
#endif

#ifdef MYSQL_USE
	return mysql_get_rows_count();
#endif
};

unsigned int sql_get_cols_count()
{
#ifdef PGSQL_USE
	return pgsql_get_cols_count();
#endif

#ifdef MYSQL_USE
	return mysql_get_cols_count();
#endif
};

char * sql_get_field_name(unsigned int column)
{
#ifdef PGSQL_USE
	return pgsql_get_field_name(column);
#endif

#ifdef MYSQL_USE
	return mysql_get_field_name(column);
#endif
};

unsigned int sql_get_length(unsigned int row, unsigned int column)
{
#ifdef PGSQL_USE
	return pgsql_get_length(row, column);
#endif

#ifdef MYSQL_USE
	return mysql_get_length(row, column);
#endif
};

unsigned int sql_get_is_null(unsigned int row, unsigned int column)
{
#ifdef PGSQL_USE
	return pgsql_get_is_null(row, column);
#endif

#ifdef MYSQL_USE
	return mysql_get_is_null(row, column);
#endif
};

void init_pans(struct __p_answer * p)
{
	p->active = 0;
	p->add_info = NULL;
	p->address1 = NULL;
	p->address2 = NULL;
	p->admin_email = NULL;
	p->cash = NULL;
	p->city = NULL;
	p->company = NULL;
	p->confirm_date = NULL;
	p->confirm_from_host = NULL;
	p->country = NULL;
	p->d_name = NULL;
	p->d_type = 0;
	p->domain_registered = 0;
	p->domain_status = p->enum_ok;
	p->egrpou = 0;
	p->email = NULL;
	p->end_time = NULL;
	p->errmsg = NULL;
	p->fax = NULL;
	p->firstname = NULL;
	p->frozen = NULL;
	p->full_name = NULL;
	p->grace = 0;
	p->holded = NULL;
	p->id_domain = 0;
	p->id_mx = 0;
	p->id_ns = 0;
	p->id_own = 0;
	p->id_person = 0;
	p->id_table = 0;
	p->initial = 0;
	p->lastname = NULL;
	p->lease_time = 0;
	p->max_lease_time = 0;
	p->mx = NULL;
	p->mx_domain_name = NULL;
	p->mx_ip = NULL;
	p->mx_prior = 0;
	p->mx_title = NULL;
	p->next = NULL;
	p->notes = NULL;
	p->ns1 = NULL;
	p->ns2 = NULL;
	p->ns_domain_name = NULL;
	p->ns_ip = NULL;
	p->ns_master = 0;
	p->ns_title = NULL;
	p->payed = NULL;
	p->pending = 0;
	p->phone = NULL;
	p->postal = 0;
	p->price = NULL;
	p->query_from_host = NULL;
	p->query_post_date = NULL;
	p->refused = NULL;
	p->region = NULL;
	p->released = NULL;
	p->result_code = SQL_RESULT_OK;
	p->row_number = 0;
	p->rows_count = 0;
	p->source_query = NULL;
	p->status = NULL;
	p->userhdl = NULL;
	p->why_refused = NULL;
	p->why_released = NULL;
	p->z_name = NULL;
};

void clear_pans(struct __p_answer * p)
{
	init_pans(p);
}

struct __p_answer * parse_result(char * q_type, unsigned int id_domain)
{
	struct __p_answer * pans = NULL;
	char * query = NULL;
	unsigned int result = RES_FALSE;
	unsigned int i = 0;
	
	if (!q_type)
		return NULL;

	pans = new struct __p_answer;
	init_pans(pans);

	if (strcmp("count_for_main_page_wait_for_pay", q_type) == 0) {
		query = strdup("SELECT COUNT(id_domain_data) FROM wait_for_pay");
	}

	if (strcmp("count_for_main_page_registered", q_type) == 0) {
		query = strdup("SELECT COUNT(id_domain_data) FROM registered");
	}

	if (strcmp("count_for_main_page_to_create", q_type) == 0) {
		query = strdup("SELECT COUNT(id_domain_data) FROM to_create");
	}

	if (strcmp("count_for_main_page_refused", q_type) == 0) {
		query = strdup("SELECT COUNT(id_domain_data) FROM refused");
	}
	
	if (strcmp("view_query_body_individ", q_type) == 0) {
		char * id = NULL;

		id = get_value("ID");
		if (id_domain != 0) {
			safe_free(&id);
			id = int_to_string(id_domain);
		}

		query = strdup("SELECT dd.id_table, dd.d_name, dd.d_zone, "
				"st.id_table, st.wait_for_pay, st.queued, st.checked, st.suspended, st.refused, st.recalled, st.ok, st.hold, st.frozen, st.released, "
				"dat.query_post_date, dat.confirm_date, dat.payed, dat.lease_time, dat.end_time, dat.released, dat.refused, dat.holded, dat.frozen, "
				"des.add_info, des.why_released, des.why_refused, des.notes, "
				"tch.ns1, tch.ns2, tch.mx, tch.query_from_host, tch.confirm_from_host "
				"FROM domain_data dd, domain_status_table st, dates_table dat, descr_table des, technical_table tch "
				"WHERE dd.id_table = ");

		query = sts (&query, id);
		query = sts (&query, " AND st.id_domain_data = ");
		query = sts (&query, id);
		query = sts (&query, " AND dat.id_domain_data = ");
		query = sts (&query, id);
		query = sts (&query, " AND des.id_domain_data = ");
		query = sts (&query, id);
		query = sts (&query, " AND tch.id_domain_data = ");
		query = sts (&query, id);
	
		pans->id_domain = atoi(id);
	}
	
	if (strcmp("view_query_body_individ_person", q_type) == 0) {
		char * id = NULL;

		id = get_value("ID");

		query = strdup("SELECT * "
				"FROM person_table WHERE id_table IN (SELECT id_person_data FROM person_owns_table WHERE id_domain_data = ");
		query = sts (&query, id);
		query = sts (&query, " )");
	
		pans->id_domain = atoi(id);
	}

	if (strcmp("how_many_person_registered", q_type) == 0) {
		char * id = NULL;

		id = get_value("ID");

		query = strdup("SELECT COUNT(id_domain_data) "
				"FROM person_owns_table WHERE id_person_data IN (SELECT id_person_data FROM person_owns_table WHERE id_domain_data = ");
		query = sts (&query, id);
		query = sts (&query, " )");
	
		pans->id_domain = atoi(id);
	}

	if (strcmp("how_many_person_registered_2", q_type) == 0) {
		char * id = NULL;

		id = get_value("ID");

		query = strdup("SELECT COUNT(id_domain_data) FROM person_owns_table WHERE id_person_data = ");
		query = sts (&query, id);
	
		pans->id_domain = atoi(id);
	}

	if (strcmp("view_queries", q_type) == 0) {
		char * sort_by = NULL;
		
		query = strdup("SELECT id_table, d_name, d_zone FROM domain_data WHERE id_table IN (SELECT id_domain_data FROM wait_for_pay)");	
		
		sort_by = get_value("SORT");
		if (sort_by) {
			query = sts(&query, " order by ");
			query = sts(&query, sort_by);
			safe_free(&sort_by);
		}
	}

	if (strcmp("view_refused", q_type) == 0) {
		char * sort_by = NULL;
		
		query = strdup("SELECT dd.id_table, dd.d_name, dd.d_zone, "
				"dat.refused "
				"FROM domain_data dd, dates_table dat "
				"WHERE dd.id_table IN (SELECT id_domain_data FROM refused) AND dat.id_domain_data = dd.id_table");
		
		sort_by = get_value("SORT");
		if (sort_by) {
			query = sts(&query, " order by ");
			query = sts(&query, sort_by);
			safe_free(&sort_by);
		}
	}

	if (strcmp("view_registered", q_type) == 0) {
		char * sort_by = NULL;
		
		query = strdup("SELECT dd.id_table, dd.d_name, dd.d_zone, "
				"dat.payed, dat.end_time "
				"FROM domain_data dd, dates_table dat "
				"WHERE dd.id_table IN (SELECT id_domain_data FROM registered) AND dat.id_domain_data = dd.id_table");
		
		sort_by = get_value("SORT");
		if (sort_by) {
			query = sts(&query, " order by ");
			query = sts(&query, sort_by);
			safe_free(&sort_by);
		}
	}

	if (strcmp("view_to_create", q_type) == 0) {
		char * sort_by = NULL;
		
		query = strdup("SELECT dd.id_table, dd.d_name, dd.d_zone, "
				"dat.payed, dat.lease_time "
				"FROM domain_data dd, dates_table dat "
				"WHERE dd.id_table IN (SELECT id_domain_data FROM to_create) AND dat.id_domain_data = dd.id_table");
		
		sort_by = get_value("SORT");
		if (sort_by) {
			query = sts(&query, " order by ");
			query = sts(&query, sort_by);
			safe_free(&sort_by);
		}
	}

	if (strcmp("view_clients", q_type) == 0) {
		char * sort_by = NULL;
		
		query = strdup("SELECT per.id_table, firstname, lastname, email, userhdl, cash, (SELECT COUNT(id_domain_data) FROM person_owns_table WHERE id_person_data = per.id_table) AS domain_count FROM person_table per ");
		
		sort_by = get_value("SORT");
		if (sort_by) {
			query = sts(&query, " order by ");
			query = sts(&query, sort_by);
			safe_free(&sort_by);
		}
	}

	if (strcmp("view_client_individ", q_type) == 0) {
		char * id = NULL;

		id = get_value("ID");

		query = strdup("SELECT * FROM person_table WHERE id_table = ");
		query = sts (&query, id);
	
		pans->id_person = atoi(id);
	}

	if (strcmp("view_client_individ", q_type) == 0) {
		char * id = NULL;

		id = get_value("ID");

		query = strdup("SELECT * FROM person_table WHERE id_table = ");
		query = sts (&query, id);
	
		pans->id_person = atoi(id);
	}

	if (strcmp("domain_list", q_type) == 0) {
		char * id = NULL;

		id = get_value("ID");

		query = strdup("SELECT id_table, full_name FROM domain_data WHERE id_table IN (SELECT id_domain_data from person_owns_table WHERE id_person_data = ");
		query = sts (&query, id);
		query = sts (&query, " )");
	
		pans->id_person = atoi(id);
	}

	if (strcmp("search_domain", q_type) == 0) {
		char * id_domain = NULL;
		char * full_name = NULL;
		char * d_name = NULL;
		char * d_zone = NULL;
		char * id_person = NULL;
		char * firstname = NULL;
		char * lastname = NULL;
		char * company = NULL;
		char * country = NULL;
		char * region = NULL;
		char * postal = NULL;
		char * city = NULL;
		char * address1 = NULL;
		char * address2 = NULL;
		char * phone = NULL;
		char * fax = NULL;
		char * nic = NULL;
		char * email = NULL;
		char * domain_status = NULL;

		unsigned int where = 0;
		unsigned int where_person = 0;
		unsigned int person_search = FALSE;

		id_domain = get_value("ID_DOMAIN");
		full_name = get_value("FULL_NAME");
		d_name = get_value("D_NAME");
		d_zone = get_value("D_ZONE");
		id_person = get_value("ID_PERSON");
		firstname = get_value("FIRSTNAME");
		lastname = get_value("LASTNAME");
		company = get_value("COMPANY");
		country = get_value("COUNTRY");
		region = get_value("REGION");
		postal = get_value("POSTAL");
		city = get_value("CITY");
		address1 = get_value("ADDRESS1");
		address2 = get_value("ADDRESS2");
		phone = get_value("PHONE");
		fax = get_value("FAX");
		nic = get_value("NIC");
		email = get_value("EMAIL");
		domain_status = get_value("DOMAIN_STATUS");

		if (id_domain) if (strlen(id_domain) == 0)
			safe_free(&id_domain);
		if (full_name) if (strlen(full_name) == 0)
			safe_free(&full_name);
		if (d_name) if (strlen(d_name) == 0)
			safe_free(&d_name);
		if (d_zone) if (strlen(d_zone) == 0)
			safe_free(&d_zone);
		if (id_person)if (strlen(id_person) == 0)
			safe_free(&id_person);
		if (firstname) if (strlen(firstname) == 0)
			safe_free(&firstname);
		if (lastname) if (strlen(lastname) == 0)
			safe_free(&lastname);
		if (company) if (strlen(company) == 0)
			safe_free(&company);
		if (country) if (strlen(country) == 0)
			safe_free(&country);
		if (region) if (strlen(region) == 0)
			safe_free(&region);
		if (postal) if (strlen(postal) == 0)
			safe_free(&postal);
		if (city) if (strlen(city) == 0)
			safe_free(&city);
		if (address1) if (strlen(address1) == 0)
			safe_free(&address1);
		if (address2) if (strlen(address2) == 0)
			safe_free(&address2);
		if (phone) if (strlen(phone) == 0)
			safe_free(&phone);
		if (fax) if (strlen(fax) == 0)
			safe_free(&fax);
		if (nic) if (strlen(nic) == 0)
			safe_free(&nic);
		if (email) if (strlen(email) == 0)
			safe_free(&email);

		if (id_person || 
			firstname || 
			lastname || 
			company || 
			country ||
			region || 
			postal || 
			city || 
			address1 || 
			address2 || 
			phone || 
			fax || 
			nic || 
			email)
			person_search = TRUE;
		
		query = strdup("SELECT dd.id_table, dd.d_name, dd.d_zone, "
		"st.wait_for_pay, st.queued, st.checked, st.suspended, st.refused, st.recalled, st.ok, st.hold, st.frozen, st.released, "
		"dat.query_post_date, dat.confirm_date, dat.payed, dat.lease_time, dat.end_time, dat.released, dat.refused, dat.holded, dat.frozen "
		" FROM domain_data dd, domain_status_table st, dates_table dat WHERE ");
			
		if (id_domain) {
			query = sts(&query, " dd.id_table = ");
			query = sts(&query, id_domain);
			where++;
		}

		if (full_name) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " dd.full_name = '");
			query = sts(&query, full_name);
			query = sts(&query, "'");
			where++;
		}

		if (d_name) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " dd.d_name = '");
			query = sts(&query, d_name);
			query = sts(&query, "'");
			where++;
		}

		if (d_zone) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " dd.d_zone = '");
			query = sts(&query, d_zone);
			query = sts(&query, "'");
			where++;
		}
				
		if (where)
			query = sts(&query, " AND ");
		
		query = sts(&query, " dd.id_table IN (SELECT dst.id_domain_data FROM domain_status_table dst ");
		
		if (strcmp(domain_status, "all") != 0)
			query = sts(&query, " WHERE dst.");

		if (strcmp(domain_status, "ok") == 0)
				query = sts(&query, "ok = 1 ");
		
		if (strcmp(domain_status, "wait_for_pay") == 0)
				query = sts(&query, "wait_for_pay = 1 ");

		if (strcmp(domain_status, "queued") == 0)
				query = sts(&query, "queued = 1 ");

		if (strcmp(domain_status, "checked") == 0)
				query = sts(&query, "checked = 1 ");

		if (strcmp(domain_status, "suspended") == 0)
				query = sts(&query, "suspended = 1 ");

		if (strcmp(domain_status, "refused") == 0)
				query = sts(&query, "refused = 1 ");

		if (strcmp(domain_status, "recalled") == 0)
				query = sts(&query, "recalled = 1 ");

		if (strcmp(domain_status, "hold") == 0)
				query = sts(&query, "hold = 1 ");

		if (strcmp(domain_status, "frozen") == 0)
				query = sts(&query, "frozen = 1 ");

		if (strcmp(domain_status, "released") == 0)
				query = sts(&query, "released = 1 ");

		query = sts(&query, " ) ");
		
		if (person_search == TRUE) {
			query = sts(&query, " AND ");
			
			query = sts(&query, " dd.id_table IN (SELECT own.id_domain_data FROM person_owns_table own WHERE own.id_person_data IN (SELECT per.id_table FROM person_table per WHERE ");
			
			if (id_person) {
				query = sts(&query, " per.id_table = ");
				query = sts(&query, id_person);
				where_person++;
			}

			if (firstname) {
				if (where_person)
					query = sts(&query, " AND ");
				query = sts(&query, " per.firstname = '");
				query = sts(&query, firstname);
				query = sts(&query, "'");
				where_person++;
			}

			if (lastname) {
				if (where_person)
					query = sts(&query, " AND ");
				query = sts(&query, " per.lastname = '");
				query = sts(&query, lastname);
				query = sts(&query, "'");
				where_person++;
			}

			if (company) {
				if (where_person)
					query = sts(&query, " AND ");
				query = sts(&query, " per.company = '");
				query = sts(&query, company);
				query = sts(&query, "'");
				where_person++;
			}

			if (country) {
				if (where_person)
					query = sts(&query, " AND ");
				query = sts(&query, " per.country = '");
				query = sts(&query, country);
				query = sts(&query, "'");
				where_person++;
			}

			if (region) {
				if (where_person)
					query = sts(&query, " AND ");
				query = sts(&query, " per.region = '");
				query = sts(&query, region);
				query = sts(&query, "'");
				where_person++;
			}

			if (postal) {
				if (where_person)
					query = sts(&query, " AND ");
				query = sts(&query, " per.postal = ");
				query = sts(&query, postal);
				where_person++;
			}

			if (city) {
				if (where_person)
					query = sts(&query, " AND ");
				query = sts(&query, " per.city = '");
				query = sts(&query, city);
				query = sts(&query, "'");
				where_person++;
			}

			if (address1) {
				if (where_person)
					query = sts(&query, " AND ");
				query = sts(&query, " per.address1 = '");
				query = sts(&query, address1);
				query = sts(&query, "'");
				where_person++;
			}

			if (address2) {
				if (where_person)
					query = sts(&query, " AND ");
				query = sts(&query, " per.address2 = '");
				query = sts(&query, address2);
				query = sts(&query, "'");
				where_person++;
			}

			if (phone) {
				if (where_person)
					query = sts(&query, " AND ");
				query = sts(&query, " per.phone = '");
				query = sts(&query, phone);
				query = sts(&query, "'");
				where_person++;
			}

			if (fax) {
				if (where_person)
					query = sts(&query, " AND ");
				query = sts(&query, " per.fax = '");
				query = sts(&query, fax);
				query = sts(&query, "'");
				where_person++;
			}

			if (nic) {
				if (where_person)
					query = sts(&query, " AND ");
				query = sts(&query, " per.userhdl = '");
				query = sts(&query, nic);
				query = sts(&query, "'");
				where_person++;
			}

			if (email) {
				if (where_person)
					query = sts(&query, " AND ");
				query = sts(&query, " per.email = '");
				query = sts(&query, email);
				query = sts(&query, "'");
				where_person++;
			}
			
			query = sts(&query, " ))");
		}
		
		query = sts(&query, " AND st.id_table = dd.id_table ");
		query = sts(&query, " AND dat.id_table = dd.id_table ");
	}

	if (strcmp("search_client", q_type) == 0) {
		char * full_name = NULL;
		char * id_person = NULL;
		char * firstname = NULL;
		char * lastname = NULL;
		char * company = NULL;
		char * country = NULL;
		char * region = NULL;
		char * postal = NULL;
		char * city = NULL;
		char * address1 = NULL;
		char * address2 = NULL;
		char * phone = NULL;
		char * fax = NULL;
		char * nic = NULL;
		char * email = NULL;

		unsigned int where = 0;
		unsigned int where_domain = 0;
		unsigned int domain_search = FALSE;
		unsigned int person_search = FALSE;

		full_name = get_value("FULL_NAME");
		id_person = get_value("ID_PERSON");
		firstname = get_value("FIRSTNAME");
		lastname = get_value("LASTNAME");
		company = get_value("COMPANY");
		country = get_value("COUNTRY");
		region = get_value("REGION");
		postal = get_value("POSTAL");
		city = get_value("CITY");
		address1 = get_value("ADDRESS1");
		address2 = get_value("ADDRESS2");
		phone = get_value("PHONE");
		fax = get_value("FAX");
		nic = get_value("NIC");
		email = get_value("EMAIL");

		if (full_name) if (strlen(full_name) == 0)
			safe_free(&full_name);
		if (id_person)if (strlen(id_person) == 0)
			safe_free(&id_person);
		if (firstname) if (strlen(firstname) == 0)
			safe_free(&firstname);
		if (lastname) if (strlen(lastname) == 0)
			safe_free(&lastname);
		if (company) if (strlen(company) == 0)
			safe_free(&company);
		if (country) if (strlen(country) == 0)
			safe_free(&country);
		if (region) if (strlen(region) == 0)
			safe_free(&region);
		if (postal) if (strlen(postal) == 0)
			safe_free(&postal);
		if (city) if (strlen(city) == 0)
			safe_free(&city);
		if (address1) if (strlen(address1) == 0)
			safe_free(&address1);
		if (address2) if (strlen(address2) == 0)
			safe_free(&address2);
		if (phone) if (strlen(phone) == 0)
			safe_free(&phone);
		if (fax) if (strlen(fax) == 0)
			safe_free(&fax);
		if (nic) if (strlen(nic) == 0)
			safe_free(&nic);
		if (email) if (strlen(email) == 0)
			safe_free(&email);

		if (id_person || 
			firstname || 
			lastname || 
			company || 
			country ||
			region || 
			postal || 
			city || 
			address1 || 
			address2 || 
			phone || 
			fax || 
			nic || 
			email)
			person_search = TRUE;
			
		if (full_name)
			domain_search = TRUE;
		
		query = strdup("SELECT per.id_table, per.firstname, per.lastname, per.email, per.userhdl, per.cash, "
		"(SELECT COUNT(id_domain_data) FROM person_owns_table WHERE id_person_data = per.id_table) AS domain_count "
		" FROM person_table per ");

		if (person_search == TRUE || domain_search == TRUE)
			query = sts(&query, " WHERE ");
		
		if (id_person) {
			query = sts(&query, " per.id_table = ");
			query = sts(&query, id_person);
			where++;
		}

		if (firstname) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " per.firstname = '");
			query = sts(&query, firstname);
			query = sts(&query, "'");
			where++;
		}

		if (lastname) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " per.lastname = '");
			query = sts(&query, lastname);
			query = sts(&query, "'");
			where++;
		}

		if (company) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " per.company = '");
			query = sts(&query, company);
			query = sts(&query, "'");
			where++;
		}

		if (country) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " per.country = '");
			query = sts(&query, country);
			query = sts(&query, "'");
			where++;
		}

		if (region) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " per.region = '");
			query = sts(&query, region);
			query = sts(&query, "'");
			where++;
		}

		if (postal) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " per.postal = ");
			query = sts(&query, postal);
			where++;
		}

		if (city) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " per.city = '");
			query = sts(&query, city);
			query = sts(&query, "'");
			where++;
		}

		if (address1) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " per.address1 = '");
			query = sts(&query, address1);
			query = sts(&query, "'");
			where++;
		}

		if (address2) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " per.address2 = '");
			query = sts(&query, address2);
			query = sts(&query, "'");
			where++;
		}

		if (phone) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " per.phone = '");
			query = sts(&query, phone);
			query = sts(&query, "'");
			where++;
		}

		if (fax) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " per.fax = '");
			query = sts(&query, fax);
			query = sts(&query, "'");
			where++;
		}

		if (nic) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " per.userhdl = '");
			query = sts(&query, nic);
			query = sts(&query, "'");
			where++;
		}

		if (email) {
			if (where)
				query = sts(&query, " AND ");
			query = sts(&query, " per.email = '");
			query = sts(&query, email);
			query = sts(&query, "'");
			where++;
		}

		if (domain_search == TRUE) {
		
			if (where)
				query = sts(&query, " AND ");

			query = sts(&query, " per.id_table IN (SELECT own.id_person_data FROM person_owns_table own WHERE own.id_domain_data IN ( SELECT id_table FROM domain_data WHERE ");

			if (full_name) {
				if (where_domain)
					query = sts(&query, " AND ");
				query = sts(&query, " full_name = '");
				query = sts(&query, full_name);
				query = sts(&query, "'");
				where_domain++;
			}
		
		query = sts(&query, " )) ");
		
		}
	}

	if (strcmp("cfg_zone_list", q_type) == 0) {
		query = strdup("SELECT id_table, d_zone, active FROM zone_cfg");
	}

	if (strcmp("get_main_zone_cfg", q_type) == 0) {
		char * id = NULL;

		id = get_value("ID");

		query = strdup("SELECT * FROM zone_cfg WHERE id_table = ");
		query = sts (&query, id);
		query = sts (&query, "");
		
		pans->id_table = atoi(id);
	}

	if (strcmp("get_ns_zone_cfg", q_type) == 0) {
		char * id = NULL;

		id = get_value("ID");

		query = strdup("SELECT nsl.*, own.id_table as own_id, own.ns_master FROM ns_list_cfg nsl, zone_own_ns_cfg own WHERE own.id_ns_data = nsl.id_table AND own.id_zone_data = ");
		query = sts (&query, id);
		
		pans->id_table = atoi(id);
	}

	if (strcmp("get_mx_zone_cfg", q_type) == 0) {
		char * id = NULL;

		id = get_value("ID");

		query = strdup("SELECT mxl.*, own.id_table as own_id, own.mx_prior FROM mx_list_cfg mxl, zone_own_mx_cfg own WHERE own.id_mx_data = mxl.id_table AND own.id_zone_data = ");
		query = sts (&query, id);
		
		pans->id_table = atoi(id);
	}

	if (strcmp("get_all_ns", q_type) == 0) {
		query = strdup("SELECT nsl.* FROM ns_list_cfg nsl");
	}
	
	if (strcmp("get_all_mx", q_type) == 0) {
		query = strdup("SELECT mxl.* FROM mx_list_cfg mxl");
	}

//***********************************************************//
//***********************************************************//
//***********************************************************//
//***********************************************************//
//***********************************************************//
//***********************************************************//

	if (!query) {
		pans->result_code = SQL_RESULT_ERROR;
		pans->source_query = NULL;
		pans->errmsg = strdup("Не найден обработчик запроса");
		return pans;
	}

	pans->source_query = strdup(query);
	result = sql_make_query(query);
	pans->result_code = result;
	safe_free(&query);
	
	if (result == SQL_RESULT_ERROR) {
#ifdef PGSQL_USE
		pans->errmsg = pg_conn.errmsg;
#endif
#ifdef MYSQL_USE

#endif
		return pans;
	}

//***********************************************************//
//***********************************************************//
//***********************************************************//
//***********************************************************//
//***********************************************************//
//***********************************************************//
	
	if (strcmp("count_for_main_page_wait_for_pay", q_type) == 0) {
		if (pans->result_code == SQL_RESULT_EMPTY)
			return pans;
		
		pans->in_query_to_pay = atoi(sql_get_value(0, 0));
	}

	if (strcmp("count_for_main_page_registered", q_type) == 0) {
		if (pans->result_code == SQL_RESULT_EMPTY)
			return pans;
		
		pans->in_registered = atoi(sql_get_value(0, 0));
	}

	if (strcmp("count_for_main_page_to_create", q_type) == 0) {
		if (pans->result_code == SQL_RESULT_EMPTY)
			return pans;
		
		pans->in_to_create = atoi(sql_get_value(0, 0));
	}

	if (strcmp("count_for_main_page_refused", q_type) == 0) {
		if (pans->result_code == SQL_RESULT_EMPTY)
			return pans;
		
		pans->in_refused = atoi(sql_get_value(0, 0));
	}

	if (strcmp("view_query_body_individ", q_type) == 0) {
		char * tmp = NULL;
		
		if (pans->result_code == SQL_RESULT_EMPTY)
			return pans;
		
		pans->d_name = sql_get_value(0, 1);
		pans->z_name = sql_get_value(0, 2);

		tmp = sql_get_value(0, 4);
		if (strcmp(tmp, "1")==0) { 
			pans->status = strdup("Ожидает оплаты"); 
			pans->domain_status = pans->enum_wait_for_pay; } safe_free(&tmp);

		tmp = sql_get_value(0, 5);
		if (strcmp(tmp, "1")==0) {
			pans->status = strdup("На регистрации");
			pans->domain_status = pans->enum_queued; } safe_free(&tmp);

		tmp = sql_get_value(0, 6);
		if (strcmp(tmp, "1")==0) {
			pans->status = strdup("Проходит проверку"); 
			pans->domain_status = pans->enum_checked; } safe_free(&tmp);

		tmp = sql_get_value(0, 7);
		if (strcmp(tmp, "1")==0) {
			pans->status = strdup("Обработку отложено"); 
			pans->domain_status = pans->enum_suspended; } safe_free(&tmp);

		tmp = sql_get_value(0, 8);
		if (strcmp(tmp, "1")==0) {
			pans->status = strdup("Отклонен"); 
			pans->domain_status = pans->enum_refused; } safe_free(&tmp);

		tmp = sql_get_value(0, 9);
		if (strcmp(tmp, "1")==0) {
			pans->status = strdup("Заявка отозвана"); 
			pans->domain_status = pans->enum_recalled; } safe_free(&tmp);
		
		tmp = sql_get_value(0, 10);
		if (strcmp(tmp, "1")==0) {
			pans->status = strdup("Активный"); 
			pans->domain_status = pans->enum_ok; } safe_free(&tmp);
		
		tmp = sql_get_value(0, 11);
		if (strcmp(tmp, "1")==0) {
			pans->status = strdup("Приостановлен"); 
			pans->domain_status = pans->enum_hold; } safe_free(&tmp);
		
		tmp = sql_get_value(0, 12);
		if (strcmp(tmp, "1")==0) {
			pans->status = strdup("Заморожен"); 
			pans->domain_status = pans->enum_frozen; } safe_free(&tmp);
		
		tmp = sql_get_value(0, 13);
		if (strcmp(tmp, "1")==0) {
			pans->status = strdup("Свободен"); 
			pans->domain_status = pans->enum_released; } safe_free(&tmp);
		
		pans->query_post_date = sql_get_value(0, 14);
		pans->confirm_date = sql_get_value(0, 15);
		pans->payed = sql_get_value(0, 16);
		pans->lease_time = atoi(sql_get_value(0, 17));
		pans->end_time = sql_get_value(0, 18);
		pans->released = sql_get_value(0, 19);
		pans->refused = sql_get_value(0, 20);
		pans->holded = sql_get_value(0, 21);
		pans->frozen = sql_get_value(0, 22);
		
		pans->add_info = sql_get_value(0, 23);
		pans->why_released = sql_get_value(0, 24);
		pans->why_refused = sql_get_value(0, 25);
		pans->notes = sql_get_value(0, 26);
		
		pans->ns1 = sql_get_value(0, 27);
		pans->ns2 = sql_get_value(0, 28);
		pans->mx = sql_get_value(0, 29);
		pans->query_from_host = sql_get_value(0, 30);
		pans->confirm_from_host = sql_get_value(0, 31);
	}
	
	if (strcmp("view_query_body_individ_person", q_type) == 0) {
		if (pans->result_code == SQL_RESULT_EMPTY)
			return pans;
		
		pans->id_person = atoi(sql_get_value(0, 0));
		pans->firstname = sql_get_value(0, 1);
		pans->lastname = sql_get_value(0, 2);
		pans->company = sql_get_value(0, 3);
		pans->country = sql_get_value(0, 4);
		pans->region = sql_get_value(0, 5);
		pans->postal = atoi(sql_get_value(0, 6));
		pans->city = sql_get_value(0, 7);
		pans->address1 = sql_get_value(0, 8);
		pans->address2 = sql_get_value(0, 9);
		pans->phone = sql_get_value(0, 10);
		pans->fax = sql_get_value(0, 11);
		pans->email = sql_get_value(0, 12);
		pans->userhdl = sql_get_value(0, 14);
		pans->cash = fetch_money(sql_get_value(0, 15));
	}

	if (strcmp("how_many_person_registered", q_type) == 0) {
		if (pans->result_code == SQL_RESULT_EMPTY)
			return pans;
		
		pans->domain_registered = atoi(sql_get_value(0, 0));
	}

	if (strcmp("how_many_person_registered_2", q_type) == 0) {
		if (pans->result_code == SQL_RESULT_EMPTY)
			return pans;
		
		pans->domain_registered = atoi(sql_get_value(0, 0));
	}
	
	if (strcmp("view_queries", q_type) == 0) {
		struct __p_answer * next_pans = NULL;
		struct __p_answer * pans_copy = NULL;
		
		pans_copy = pans;
		
		if (pans_copy->result_code == SQL_RESULT_EMPTY)
			return pans_copy;
		
		for (i=0; i < sql_get_rows_count(); i++) {
			pans_copy->rows_count = sql_get_rows_count();
			pans_copy->row_number = i;
			pans_copy->id_domain = atoi(sql_get_value(i, 0));
			pans_copy->d_name = sql_get_value(i, 1);
			pans_copy->z_name = sql_get_value(i, 2);

			if (i < (sql_get_rows_count()-1)) {
				next_pans = new struct __p_answer;
				init_pans(next_pans);
				pans_copy->next = next_pans;
				pans_copy = next_pans;
			}
		}
	}

	if (strcmp("view_refused", q_type) == 0) {
		struct __p_answer * next_pans = NULL;
		struct __p_answer * pans_copy = NULL;
		
		pans_copy = pans;
		
		if (pans_copy->result_code == SQL_RESULT_EMPTY)
			return pans_copy;
		
		for (i=0; i < sql_get_rows_count(); i++) {
			pans_copy->rows_count = sql_get_rows_count();
			pans_copy->row_number = i;
			pans_copy->id_domain = atoi(sql_get_value(i, 0));
			pans_copy->d_name = sql_get_value(i, 1);
			pans_copy->z_name = sql_get_value(i, 2);
			pans_copy->refused = sql_get_value(i, 3);

			if (i < (sql_get_rows_count()-1)) {
				next_pans = new struct __p_answer;
				init_pans(next_pans);
				pans_copy->next = next_pans;
				pans_copy = next_pans;
			}
		}
	}

	if (strcmp("view_registered", q_type) == 0) {
		struct __p_answer * next_pans = NULL;
		struct __p_answer * pans_copy = NULL;
		
		pans_copy = pans;
		
		if (pans_copy->result_code == SQL_RESULT_EMPTY)
			return pans_copy;
		
		for (i=0; i < sql_get_rows_count(); i++) {
			pans_copy->rows_count = sql_get_rows_count();
			pans_copy->row_number = i;
			pans_copy->id_domain = atoi(sql_get_value(i, 0));
			pans_copy->d_name = sql_get_value(i, 1);
			pans_copy->z_name = sql_get_value(i, 2);
			pans_copy->payed = sql_get_value(i, 3);
			pans_copy->end_time = sql_get_value(i, 4);

			if (i < (sql_get_rows_count()-1)) {
				next_pans = new struct __p_answer;
				init_pans(next_pans);
				pans_copy->next = next_pans;
				pans_copy = next_pans;
			}
		}
	}

	if (strcmp("view_to_create", q_type) == 0) {
		struct __p_answer * next_pans = NULL;
		struct __p_answer * pans_copy = NULL;
		
		pans_copy = pans;
		
		if (pans_copy->result_code == SQL_RESULT_EMPTY)
			return pans_copy;
		
		for (i=0; i < sql_get_rows_count(); i++) {
			pans_copy->rows_count = sql_get_rows_count();
			pans_copy->row_number = i;
			pans_copy->id_domain = atoi(sql_get_value(i, 0));
			pans_copy->d_name = sql_get_value(i, 1);
			pans_copy->z_name = sql_get_value(i, 2);
			pans_copy->payed = sql_get_value(i, 3);
			pans_copy->lease_time = atoi(sql_get_value(i, 4));

			if (i < (sql_get_rows_count()-1)) {
				next_pans = new struct __p_answer;
				init_pans(next_pans);
				pans_copy->next = next_pans;
				pans_copy = next_pans;
			}
		}
	}

	if (strcmp("view_clients", q_type) == 0) {
		struct __p_answer * next_pans = NULL;
		struct __p_answer * pans_copy = NULL;
		
		pans_copy = pans;
		
		if (pans_copy->result_code == SQL_RESULT_EMPTY)
			return pans_copy;
		
		for (i=0; i < sql_get_rows_count(); i++) {
			pans_copy->rows_count = sql_get_rows_count();
			pans_copy->row_number = i;
			pans_copy->id_person = atoi(sql_get_value(i, 0));
			pans_copy->firstname = sql_get_value(i, 1);
			pans_copy->lastname = sql_get_value(i, 2);
			pans_copy->email = sql_get_value(i, 3);
			pans_copy->userhdl = sql_get_value(i, 4);
			pans_copy->cash = fetch_money(sql_get_value(i, 5));
			pans_copy->domain_registered = atoi(sql_get_value(i, 6));

			if (i < (sql_get_rows_count()-1)) {
				next_pans = new struct __p_answer;
				init_pans(next_pans);
				pans_copy->next = next_pans;
				pans_copy = next_pans;
			}
		}
	}
	
	if (strcmp("view_client_individ", q_type) == 0) {
		if (pans->result_code == SQL_RESULT_EMPTY)
			return pans;
		
		pans->id_person = atoi(sql_get_value(0, 0));
		pans->firstname = sql_get_value(0, 1);
		pans->lastname = sql_get_value(0, 2);
		pans->company = sql_get_value(0, 3);
		pans->country = sql_get_value(0, 4);
		pans->region = sql_get_value(0, 5);
		pans->postal = atoi(sql_get_value(0, 6));
		pans->city = sql_get_value(0, 7);
		pans->address1 = sql_get_value(0, 8);
		pans->address2 = sql_get_value(0, 9);
		pans->phone = sql_get_value(0, 10);
		pans->fax = sql_get_value(0, 11);
		pans->email = sql_get_value(0, 12);
		pans->userhdl = sql_get_value(0, 14);
		pans->cash = fetch_money(sql_get_value(0, 15));
	}

	if (strcmp("domain_list", q_type) == 0) {
		struct __p_answer * next_pans = NULL;
		struct __p_answer * pans_copy = NULL;
		
		pans_copy = pans;
		
		if (pans_copy->result_code == SQL_RESULT_EMPTY)
			return pans_copy;
		
		for (i=0; i < sql_get_rows_count(); i++) {
			pans_copy->rows_count = sql_get_rows_count();
			pans_copy->row_number = i;
			pans_copy->id_domain = atoi(sql_get_value(i, 0));
			pans_copy->full_name = sql_get_value(i, 1);

			if (i < (sql_get_rows_count()-1)) {
				next_pans = new struct __p_answer;
				init_pans(next_pans);
				pans_copy->next = next_pans;
				pans_copy = next_pans;
			}
		}

	}

	if (strcmp("search_domain", q_type) == 0) {
		char * tmp = NULL;
		struct __p_answer * next_pans = NULL;
		struct __p_answer * pans_copy = NULL;
		
		pans_copy = pans;
		
		if (pans_copy->result_code == SQL_RESULT_EMPTY)
			return pans_copy;
		
		for (i=0; i < sql_get_rows_count(); i++) {
			pans_copy->rows_count = sql_get_rows_count();
			pans_copy->row_number = i;
			pans_copy->id_domain = atoi(sql_get_value(i, 0));
			pans_copy->d_name = sql_get_value(i, 1);
			pans_copy->z_name = sql_get_value(i, 2);

			tmp = sql_get_value(i, 3);
			if (strcmp(tmp, "1")==0) { 
				pans_copy->status = strdup("Ожидает оплаты"); 
				pans_copy->domain_status = pans_copy->enum_wait_for_pay; } safe_free(&tmp);
	
			tmp = sql_get_value(i, 4);
			if (strcmp(tmp, "1")==0) {
				pans_copy->status = strdup("На регистрации");
				pans_copy->domain_status = pans_copy->enum_queued; } safe_free(&tmp);
	
			tmp = sql_get_value(i, 5);
			if (strcmp(tmp, "1")==0) {
				pans_copy->status = strdup("Проходит проверку"); 
				pans_copy->domain_status = pans_copy->enum_checked; } safe_free(&tmp);
	
			tmp = sql_get_value(i, 6);
			if (strcmp(tmp, "1")==0) {
				pans_copy->status = strdup("Обработку отложено"); 
				pans_copy->domain_status = pans_copy->enum_suspended; } safe_free(&tmp);
	
			tmp = sql_get_value(i, 7);
			if (strcmp(tmp, "1")==0) {
				pans_copy->status = strdup("Отклонен"); 
				pans_copy->domain_status = pans_copy->enum_refused; } safe_free(&tmp);
	
			tmp = sql_get_value(i, 8);
			if (strcmp(tmp, "1")==0) {
				pans_copy->status = strdup("Заявка отозвана"); 
				pans_copy->domain_status = pans_copy->enum_recalled; } safe_free(&tmp);
			
			tmp = sql_get_value(i, 9);
			if (strcmp(tmp, "1")==0) {
				pans_copy->status = strdup("Активный"); 
				pans_copy->domain_status = pans_copy->enum_ok; } safe_free(&tmp);
			
			tmp = sql_get_value(i, 10);
			if (strcmp(tmp, "1")==0) {
				pans_copy->status = strdup("Приостановлен"); 
				pans_copy->domain_status = pans_copy->enum_hold; } safe_free(&tmp);
			
			tmp = sql_get_value(i, 11);
			if (strcmp(tmp, "1")==0) {
				pans_copy->status = strdup("Заморожен"); 
				pans_copy->domain_status = pans_copy->enum_frozen; } safe_free(&tmp);
			
			tmp = sql_get_value(i, 12);
			if (strcmp(tmp, "1")==0) {
				pans_copy->status = strdup("Свободен"); 
				pans_copy->domain_status = pans_copy->enum_released; } safe_free(&tmp);

			pans_copy->query_post_date = sql_get_value(i, 13);
			pans_copy->confirm_date = sql_get_value(i, 14);
			pans_copy->payed = sql_get_value(i, 15);
			pans_copy->lease_time = atoi(sql_get_value(i, 16));
			pans_copy->end_time = sql_get_value(i, 17);
			pans_copy->released = sql_get_value(i, 18);
			pans_copy->refused = sql_get_value(i, 19);
			pans_copy->holded = sql_get_value(i, 20);
			pans_copy->frozen = sql_get_value(i, 21);

			if (i < (sql_get_rows_count()-1)) {
				next_pans = new struct __p_answer;
				init_pans(next_pans);
				pans_copy->next = next_pans;
				pans_copy = next_pans;
			}
		}
	}
	
	if (strcmp("search_client", q_type) == 0) {
		struct __p_answer * next_pans = NULL;
		struct __p_answer * pans_copy = NULL;
		
		pans_copy = pans;
		
		if (pans_copy->result_code == SQL_RESULT_EMPTY)
			return pans_copy;
		
		for (i=0; i < sql_get_rows_count(); i++) {
			pans_copy->rows_count = sql_get_rows_count();
			pans_copy->row_number = i;
			pans_copy->id_person = atoi(sql_get_value(i, 0));
			pans_copy->firstname = sql_get_value(i, 1);
			pans_copy->lastname = sql_get_value(i, 2);
			pans_copy->email = sql_get_value(i, 3);
			pans_copy->userhdl = sql_get_value(i, 4);
			pans_copy->cash = fetch_money(sql_get_value(i, 5));
			pans_copy->domain_registered = atoi(sql_get_value(i, 6));

			if (i < (sql_get_rows_count()-1)) {
				next_pans = new struct __p_answer;
				init_pans(next_pans);
				pans_copy->next = next_pans;
				pans_copy = next_pans;
			}
		}
	}

	if (strcmp("cfg_zone_list", q_type) == 0) {
		struct __p_answer * next_pans = NULL;
		struct __p_answer * pans_copy = NULL;
		
		pans_copy = pans;
		
		if (pans_copy->result_code == SQL_RESULT_EMPTY)
			return pans_copy;
		
		for (i=0; i < sql_get_rows_count(); i++) {
			pans_copy->rows_count = sql_get_rows_count();
			pans_copy->row_number = i;
			pans_copy->id_table = atoi(sql_get_value(i, 0));
			pans_copy->z_name = sql_get_value(i, 1);
			pans_copy->active = atoi(sql_get_value(i, 2));

			if (i < (sql_get_rows_count()-1)) {
				next_pans = new struct __p_answer;
				init_pans(next_pans);
				pans_copy->next = next_pans;
				pans_copy = next_pans;
			}
		}
	}

	if (strcmp("get_main_zone_cfg", q_type) == 0) {
		if (pans->result_code == SQL_RESULT_EMPTY)
			return pans;
		
		pans->z_name = sql_get_value(0, 1);
		pans->max_lease_time = atoi(sql_get_value(0, 2));
		pans->initial = atoi(sql_get_value(0, 3));
		pans->grace = atoi(sql_get_value(0, 4));
		pans->pending = atoi(sql_get_value(0, 5));
		pans->price = fetch_money(sql_get_value(0, 6));
		pans->admin_email = sql_get_value(0, 7);
		pans->active = atoi(sql_get_value(0, 8));
	}

	if (strcmp("get_ns_zone_cfg", q_type) == 0) {
		struct __p_answer * next_pans = NULL;
		struct __p_answer * pans_copy = NULL;

		pans_copy = pans;
		
		if (pans_copy->result_code == SQL_RESULT_EMPTY)
			return pans_copy;
		
		for (i=0; i < sql_get_rows_count(); i++) {
			pans_copy->rows_count = sql_get_rows_count();
			pans_copy->row_number = i;
			pans_copy->id_ns = atoi(sql_get_value(i, 0));
			pans_copy->ns_title = sql_get_value(i, 1);
			pans_copy->ns_domain_name = sql_get_value(i, 2);
			pans_copy->ns_ip = sql_get_value(i, 3);
			pans_copy->id_own = atoi(sql_get_value(i, 4));
			pans_copy->ns_master = atoi(sql_get_value(i, 5));

			if (i < (sql_get_rows_count()-1)) {
				next_pans = new struct __p_answer;
				init_pans(next_pans);
				pans_copy->next = next_pans;
				pans_copy = next_pans;
			}
		}
	}

	if (strcmp("get_mx_zone_cfg", q_type) == 0) {
		struct __p_answer * next_pans = NULL;
		struct __p_answer * pans_copy = NULL;
		
		pans_copy = pans;
		
		if (pans_copy->result_code == SQL_RESULT_EMPTY)
			return pans_copy;
		
		for (i=0; i < sql_get_rows_count(); i++) {
			pans_copy->rows_count = sql_get_rows_count();
			pans_copy->row_number = i;
			pans_copy->id_mx = atoi(sql_get_value(i, 0));
			pans_copy->mx_title = sql_get_value(i, 1);
			pans_copy->mx_domain_name = sql_get_value(i, 2);
			pans_copy->mx_ip = sql_get_value(i, 3);
			pans_copy->id_own = atoi(sql_get_value(i, 4));
			pans_copy->mx_prior = atoi(sql_get_value(i, 5));

			if (i < (sql_get_rows_count()-1)) {
				next_pans = new struct __p_answer;
				init_pans(next_pans);
				pans_copy->next = next_pans;
				pans_copy = next_pans;
			}
		}
	}

	if (strcmp("get_all_ns", q_type) == 0) {
		struct __p_answer * next_pans = NULL;
		struct __p_answer * pans_copy = NULL;

		pans_copy = pans;
		
		if (pans_copy->result_code == SQL_RESULT_EMPTY)
			return pans_copy;
		
		for (i=0; i < sql_get_rows_count(); i++) {
			pans_copy->rows_count = sql_get_rows_count();
			pans_copy->row_number = i;
			pans_copy->id_ns = atoi(sql_get_value(i, 0));
			pans_copy->ns_title = sql_get_value(i, 1);
			pans_copy->ns_domain_name = sql_get_value(i, 2);
			pans_copy->ns_ip = sql_get_value(i, 3);

			if (i < (sql_get_rows_count()-1)) {
				next_pans = new struct __p_answer;
				init_pans(next_pans);
				pans_copy->next = next_pans;
				pans_copy = next_pans;
			}
		}
	}
	
	if (strcmp("get_all_mx", q_type) == 0) {
		struct __p_answer * next_pans = NULL;
		struct __p_answer * pans_copy = NULL;
		
		pans_copy = pans;
		
		if (pans_copy->result_code == SQL_RESULT_EMPTY)
			return pans_copy;
		
		for (i=0; i < sql_get_rows_count(); i++) {
			pans_copy->rows_count = sql_get_rows_count();
			pans_copy->row_number = i;
			pans_copy->id_mx = atoi(sql_get_value(i, 0));
			pans_copy->mx_title = sql_get_value(i, 1);
			pans_copy->mx_domain_name = sql_get_value(i, 2);
			pans_copy->mx_ip = sql_get_value(i, 3);

			if (i < (sql_get_rows_count()-1)) {
				next_pans = new struct __p_answer;
				init_pans(next_pans);
				pans_copy->next = next_pans;
				pans_copy = next_pans;
			}
		}
	}

	return pans;
};


struct __p_answer * sql_execute(char * q_type, unsigned int id_addict)
{
	struct __p_answer * pans = NULL;
	char * query = NULL;
	unsigned int result = RES_FALSE;
	unsigned int i = 0;
	
	if (!q_type)
		return NULL;

	pans = new struct __p_answer;
	init_pans(pans);

	if (strcmp("cfg_zone_update_main", q_type) == 0) {
		char * id = NULL;
		char * max_lease_time = NULL;
		char * initial = NULL;
		char * grace = NULL;
		char * pending = NULL;
		char * price = NULL;
		char * admin_email = NULL;
		char * active = NULL;
	
		id = get_value("ID");
		max_lease_time = get_value("MAX_LEASE_TIME");
		initial = get_value("INITIAL");
		grace = get_value("GRACE");
		pending = get_value("PENDING");
		price = get_value("PRICE");
		admin_email = get_value("ADMIN_EMAIL");
		active = get_value("ACTIVE");

		if (!active)
			active = strdup("false");
	
		if (strlen(initial) == 0) {
			safe_free(&initial);
			initial = strdup("0");
		}
		if (strlen(grace) == 0) {
			safe_free(&grace);
			grace = strdup("0");
		}
		if (strlen(pending) == 0) {
			safe_free(&pending);
			pending = strdup("0");
		}
		if (strlen(price) == 0) {
			safe_free(&price);
			price = strdup("0");
		}

		query = strdup("UPDATE zone_cfg SET max_lease_time=");
		query = sts(&query, max_lease_time);
		query = sts(&query, ", initial=");
		query = sts(&query, initial);
		query = sts(&query, ", grace=");
		query = sts(&query, grace);
		query = sts(&query, ", pending=");
		query = sts(&query, pending);
		query = sts(&query, ", price=");
		query = sts(&query, price);
		query = sts(&query, ", admin_email='");
		query = sts(&query, admin_email);
		query = sts(&query, "', active=");
		query = sts(&query, (char *)((strcmp(active, "on"))==0?"1":"0"));
		query = sts(&query, " WHERE id_table=");
		query = sts(&query, id);
	}
	
	if (strcmp("cfg_zone_clear_ns", q_type) == 0) {
		char * id = NULL;
	
		id = get_value("ID");

		query = strdup("DELETE FROM zone_own_ns_cfg WHERE id_zone_data=");
		query = sts(&query, id);
	}

	if (strcmp("cfg_zone_update_ns", q_type) == 0) {
		char * id = NULL;
		char * ns_count = NULL;
		char * ns_id = NULL;
		char * ns_master = NULL;
		char * temp = NULL;
		char * temp2 = NULL;
		unsigned int i = 0;
	
		id = get_value("ID");
		ns_count = get_value("NS_COUNT");

		for (i=0; i < atoi(ns_count); i++) {
			temp = strdup("NS_REC_");
			temp2 = int_to_string(i);
			temp = sts(&temp, temp2);
			if (temp2)
				safe_free(&temp2);
			temp = sts(&temp, "_ROLE");
			ns_master = get_value(temp);
			safe_free(&temp);

			temp = strdup("NS_REC_");
			temp2 = int_to_string(i);
			temp = sts(&temp, temp2);
			if (temp2)
				safe_free(&temp2);
			temp = sts(&temp, "_SERVER");
			ns_id = get_value(temp);
			safe_free(&temp);
		
			if (strcmp(ns_id, "none") == 0) {
				safe_free(&ns_id);
				safe_free(&ns_master);
				continue;
			}
			
			query = strdup("INSERT INTO zone_own_ns_cfg (id_zone_data, id_ns_data, ns_master) VALUES (");
			query = sts(&query, id);
			query = sts(&query, ", ");
			query = sts(&query, ns_id);
			query = sts(&query, ", ");
			query = sts(&query, ns_master);
			query = sts(&query, " )");

			safe_free(&ns_id);
			safe_free(&ns_master);

			if (pans->source_query)
				safe_free(&pans->source_query);
			pans->source_query = strdup(query);
			result = sql_make_query(query);
			pans->result_code = result;
			safe_free(&query);
			if (result == SQL_RESULT_ERROR) {
#ifdef PGSQL_USE
				pans->errmsg = pg_conn.errmsg;
#endif
#ifdef MYSQL_USE

#endif
				return pans;
			}
		}

		return pans;
	}

	if (strcmp("cfg_zone_clear_mx", q_type) == 0) {
		char * id = NULL;
	
		id = get_value("ID");

		query = strdup("DELETE FROM zone_own_mx_cfg WHERE id_zone_data=");
		query = sts(&query, id);
	}

	if (strcmp("cfg_zone_update_mx", q_type) == 0) {
		char * id = NULL;
		char * mx_count = NULL;
		char * mx_id = NULL;
		char * mx_prior = NULL;
		char * temp = NULL;
		char * temp2 = NULL;
		unsigned int i = 0;
		
		id = get_value("ID");
		mx_count = get_value("MX_COUNT");

		for (i=0; i < atoi(mx_count); i++) {

			temp = strdup("MX_REC_");
			temp2 = int_to_string(i);
			temp = sts(&temp, temp2);
			if (temp2)
				safe_free(&temp2);
			temp = sts(&temp, "_PRIOR");
			mx_prior = get_value(temp);
			safe_free(&temp);

			temp = strdup("MX_REC_");
			temp2 = int_to_string(i);
			temp = sts(&temp, temp2);
			if (temp2)
				safe_free(&temp2);
			temp = sts(&temp, "_SERVER");
			mx_id = get_value(temp);
			safe_free(&temp);

			if (strcmp(mx_id, "none") == 0) {
				safe_free(&mx_id);
				safe_free(&mx_prior);
				continue;
			}

			query = strdup("INSERT INTO zone_own_mx_cfg (id_zone_data, id_mx_data, mx_prior) VALUES (");
			query = sts(&query, id);
			query = sts(&query, ", ");
			query = sts(&query, mx_id);
			query = sts(&query, ", ");
			query = sts(&query, mx_prior);
			query = sts(&query, " )");
			
			safe_free(&mx_id);
			safe_free(&mx_prior);
			
			if (pans->source_query)
				safe_free(&pans->source_query);
			
			pans->source_query = strdup(query);
			result = sql_make_query(query);
			pans->result_code = result;
			safe_free(&query);
			if (result == SQL_RESULT_ERROR) {
#ifdef PGSQL_USE
				pans->errmsg = pg_conn.errmsg;
#endif
#ifdef MYSQL_USE

#endif
				return pans;
			}
		}

		return pans;
	}

	if (strcmp("cfg_ns_mx_delete_ns", q_type) == 0) {
		struct __p_answer * psql = NULL;
		char * id = NULL;
		
		psql = new struct __p_answer;
		init_pans(psql);
	
		if (id_addict == 0)
			return psql;

		query = strdup("DELETE FROM zone_own_ns_cfg WHERE id_ns_data = ");
		id = int_to_string(id_addict);
		query = sts(&query, id);
		safe_free(&id);
			
		if (psql->source_query)
			safe_free(&psql->source_query);
		psql->source_query = strdup(query);
		result = sql_make_query(query);
		psql->result_code = result;
		safe_free(&query);
		if (result == SQL_RESULT_ERROR) {
#ifdef PGSQL_USE
			psql->errmsg = pg_conn.errmsg;
#endif
#ifdef MYSQL_USE

#endif
			return pans;
		}

		query = strdup("DELETE FROM ns_list_cfg WHERE id_table = ");
		id = int_to_string(id_addict);
		query = sts(&query, id);
		safe_free(&id);
		
		if (psql->source_query)
			safe_free(&psql->source_query);
		psql->source_query = strdup(query);
		result = sql_make_query(query);
		psql->result_code = result;
		safe_free(&query);
		if (result == SQL_RESULT_ERROR) {
#ifdef PGSQL_USE
			psql->errmsg = pg_conn.errmsg;
#endif
#ifdef MYSQL_USE

#endif
			return pans;
		}
		
		return pans;
	}

	if (strcmp("cfg_ns_mx_delete_mx", q_type) == 0) {
		struct __p_answer * psql = NULL;
		char * id = NULL;
		
		psql = new struct __p_answer;
		init_pans(psql);
	
		if (id_addict == 0)
			return psql;

		query = strdup("DELETE FROM zone_own_mx_cfg WHERE id_mx_data = ");
		id = int_to_string(id_addict);
		query = sts(&query, id);
		safe_free(&id);
			
		if (psql->source_query)
			safe_free(&psql->source_query);
		psql->source_query = strdup(query);
		result = sql_make_query(query);
		psql->result_code = result;
		safe_free(&query);
		if (result == SQL_RESULT_ERROR) {
#ifdef PGSQL_USE
			psql->errmsg = pg_conn.errmsg;
#endif
#ifdef MYSQL_USE

#endif
			return pans;
		}

		query = strdup("DELETE FROM mx_list_cfg WHERE id_table = ");
		id = int_to_string(id_addict);
		query = sts(&query, id);
		safe_free(&id);
		
		if (psql->source_query)
			safe_free(&psql->source_query);
		psql->source_query = strdup(query);
		result = sql_make_query(query);
		psql->result_code = result;
		safe_free(&query);
		if (result == SQL_RESULT_ERROR) {
#ifdef PGSQL_USE
			psql->errmsg = pg_conn.errmsg;
#endif
#ifdef MYSQL_USE

#endif
			return pans;
		}
		
		return pans;
	}

	if (strcmp("cfg_ns_mx_update_ns", q_type) == 0) {
		struct __p_answer * psql = NULL;
		char * count = NULL;
		char * id = NULL;
		char * domain = NULL;
		char * title = NULL;
		char * ip = NULL;
		char * temp = NULL;
		char * temp2 = NULL;
		unsigned int i = 0;
	
		count = get_value("NS_COUNT");

		for (i=0; i < atoi(count); i++) {
			temp = strdup("NS_ID_");
			temp2 = int_to_string(i);
			temp = sts(&temp, temp2);
			if (temp2)
				safe_free(&temp2);
			id = get_value(temp);
			safe_free(&temp);
	
			temp = strdup("NS_TITLE_");
			temp2 = int_to_string(i);
			temp = sts(&temp, temp2);
			if (temp2)
				safe_free(&temp2);
			title = get_value(temp);
			safe_free(&temp);
	
			temp = strdup("NS_DOMAIN_");
			temp2 = int_to_string(i);
			temp = sts(&temp, temp2);
			if (temp2)
				safe_free(&temp2);
			domain = get_value(temp);
			safe_free(&temp);
			
			temp = strdup("NS_IP_");
			temp2 = int_to_string(i);
			temp = sts(&temp, temp2);
			if (temp2)
				safe_free(&temp2);
			ip = get_value(temp);
			safe_free(&temp);
			
			if (strcmp(id, "0") != 0 && strlen(title) == 0 && strlen(domain) == 0 && strlen(ip) == 0) {
				psql = sql_execute("cfg_ns_mx_delete_ns", atoi(id));
				if (psql->result_code == SQL_RESULT_ERROR) {
					return psql;
				}
			}

			if (strlen(title) == 0 && strlen(domain) == 0 && strlen(ip) == 0) {
				safe_free(&id);
				safe_free(&title);
				safe_free(&domain);
				safe_free(&ip);
				continue;
			}
			
			if (strcmp(id, "0") == 0) {
				query = strdup("INSERT INTO ns_list_cfg (ns_title, ns_domain_name, ns_ip) VALUES ('");
				query = sts(&query, title);
				query = sts(&query, "', '");
				query = sts(&query, domain);
				query = sts(&query, "', '");
				query = sts(&query, ip);
				query = sts(&query, "')");
			}
			else {
				query = strdup("UPDATE ns_list_cfg SET ns_title = '");
				query = sts(&query, title);
				query = sts(&query, "', ns_domain_name = '");
				query = sts(&query, domain);
				query = sts(&query, "', ns_ip = '");
				query = sts(&query, ip);
				query = sts(&query, "' WHERE id_table = ");
				query = sts(&query, id);
			}

			safe_free(&id);
			safe_free(&title);
			safe_free(&domain);
			safe_free(&ip);

			if (pans->source_query)
				safe_free(&pans->source_query);
			pans->source_query = strdup(query);
			result = sql_make_query(query);
			pans->result_code = result;
			safe_free(&query);
			if (result == SQL_RESULT_ERROR) {
#ifdef PGSQL_USE
				pans->errmsg = pg_conn.errmsg;
#endif
#ifdef MYSQL_USE

#endif
				return pans;
			}
		}

		return pans;
	}

	if (strcmp("cfg_ns_mx_update_mx", q_type) == 0) {
		struct __p_answer * psql = NULL;
		char * count = NULL;
		char * id = NULL;
		char * domain = NULL;
		char * title = NULL;
		char * ip = NULL;
		char * temp = NULL;
		char * temp2 = NULL;
		unsigned int i = 0;
	
		count = get_value("MX_COUNT");

		for (i=0; i < atoi(count); i++) {
			temp = strdup("MX_ID_");
			temp2 = int_to_string(i);
			temp = sts(&temp, temp2);
			if (temp2)
				safe_free(&temp2);
			id = get_value(temp);
			safe_free(&temp);
	
			temp = strdup("MX_TITLE_");
			temp2 = int_to_string(i);
			temp = sts(&temp, temp2);
			if (temp2)
				safe_free(&temp2);
			title = get_value(temp);
			safe_free(&temp);
	
			temp = strdup("MX_DOMAIN_");
			temp2 = int_to_string(i);
			temp = sts(&temp, temp2);
			if (temp2)
				safe_free(&temp2);
			domain = get_value(temp);
			safe_free(&temp);
			
			temp = strdup("MX_IP_");
			temp2 = int_to_string(i);
			temp = sts(&temp, temp2);
			if (temp2)
				safe_free(&temp2);
			ip = get_value(temp);
			safe_free(&temp);

			if (strcmp(id, "0") != 0 && strlen(title) == 0 && strlen(domain) == 0 && strlen(ip) == 0) {
				psql = sql_execute("cfg_ns_mx_delete_mx", atoi(id));
				if (psql->result_code == SQL_RESULT_ERROR) {
					return psql;
				}
			}
		
			if (strlen(title) == 0 && strlen(domain) == 0 && strlen(ip) == 0) {
				safe_free(&id);
				safe_free(&title);
				safe_free(&domain);
				safe_free(&ip);
				continue;
			}

			if (strcmp(id, "0") == 0) {
				query = strdup("INSERT INTO mx_list_cfg (mx_title, mx_domain_name, mx_ip) VALUES ('");
				query = sts(&query, title);
				query = sts(&query, "', '");
				query = sts(&query, domain);
				query = sts(&query, "', '");
				query = sts(&query, ip);
				query = sts(&query, "')");
			}
			else {
				query = strdup("UPDATE mx_list_cfg SET mx_title = '");
				query = sts(&query, title);
				query = sts(&query, "', mx_domain_name = '");
				query = sts(&query, domain);
				query = sts(&query, "', mx_ip = '");
				query = sts(&query, ip);
				query = sts(&query, "' WHERE id_table = ");
				query = sts(&query, id);
			}

			safe_free(&id);
			safe_free(&title);
			safe_free(&domain);
			safe_free(&ip);

			if (pans->source_query)
				safe_free(&pans->source_query);
			pans->source_query = strdup(query);
			result = sql_make_query(query);
			pans->result_code = result;
			safe_free(&query);
			if (result == SQL_RESULT_ERROR) {
#ifdef PGSQL_USE
				pans->errmsg = pg_conn.errmsg;
#endif
#ifdef MYSQL_USE

#endif
				return pans;
			}
		}

		return pans;
	}
	
//***********************************************************//
//***********************************************************//
//***********************************************************//
//***********************************************************//
//***********************************************************//
//***********************************************************//

	if (!query) {
		pans->result_code = SQL_RESULT_ERROR;
		pans->source_query = NULL;
		pans->errmsg = strdup("Не найден обработчик запроса");
		return pans;
	}

	pans->source_query = strdup(query);
	result = sql_make_query(query);
	pans->result_code = result;
	safe_free(&query);
	
	if (result == SQL_RESULT_ERROR) {
#ifdef PGSQL_USE
		pans->errmsg = pg_conn.errmsg;
#endif
#ifdef MYSQL_USE

#endif
	}
	
	return pans;
}

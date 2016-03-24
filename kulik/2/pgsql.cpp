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

#include "pgsql.h"       /* The local header */

#include "cstdio"
#include "cstring"
#include "unistd.h"

#include "define.h"
#include "str.h"

using namespace std;

/* Structure and anchor for caching connections. */

struct __pgsql_connection pg_conn;

unsigned int pgsql_socket_init() 
{
	return pgsql_connect();
};

unsigned int pgsql_connect()
{
	char *server_copy = NULL;
	unsigned int to_sleep = 1;
	unsigned int wait_for_established = TRUE;
	unsigned int established = FALSE;
	unsigned int idle_time = 0;
	
	PGconn *pg_handle = NULL;
	PGresult *pg_result = NULL;

	pg_conn.pghost = strdup("localhost");
	pg_conn.pgport = strdup("5432");
	pg_conn.dbName = strdup("domain");
	pg_conn.login = strdup("postgres");
	pg_conn.pgoptions = NULL;
	pg_conn.pgtty = NULL;
	pg_conn.pwd = NULL;
	pg_conn.errmsg = NULL;

	pg_handle = PQsetdbLogin(
		pg_conn.pghost,
		pg_conn.pgport,
		pg_conn.pgoptions,
		pg_conn.pgtty,
		pg_conn.dbName,
		pg_conn.login,
		pg_conn.pwd);

	
	idle_time = 0;
	while (wait_for_established) {

		if(PQstatus(pg_handle) == CONNECTION_BAD) {
			wait_for_established = FALSE;
			established = FALSE;
			goto pgsql_con_end;
		}
	
		if(PQstatus(pg_handle) == CONNECTION_STARTED) {
			wait_for_established = TRUE;
		}

		if(PQstatus(pg_handle) == CONNECTION_AWAITING_RESPONSE) {
			wait_for_established = TRUE;
		}

		if(PQstatus(pg_handle) == CONNECTION_AUTH_OK) {
			wait_for_established = TRUE;
		}

		if(PQstatus(pg_handle) == CONNECTION_SETENV) {
			wait_for_established = TRUE;
		}
	
		if (PQstatus(pg_handle) == CONNECTION_MADE) {
			wait_for_established = FALSE;
			established = TRUE;
		}

		if (PQstatus(pg_handle) == CONNECTION_OK) {
			wait_for_established = FALSE;
			established = TRUE;
			goto pgsql_con_end;
		}
				
		idle_time++;
		
		if (idle_time > PGSQL_WAIT_TO_CONNECT) {
			wait_for_established = FALSE;
			established = FALSE;
			break;
		}
		
		sleep(to_sleep);
	}
pgsql_con_end:
	
	pg_conn.established = established;
	pg_conn.pg_handle = pg_handle;
	pg_conn.pg_result = NULL;
	
	return established;
};

void pgsql_close_connect()
{
	if (pg_conn.pg_handle)
		PQfinish(pg_conn.pg_handle);

	if (pg_conn.pg_result != NULL) 
		PQclear(pg_conn.pg_result);
};

unsigned int pgsql_make_query(char * query)
{
	unsigned int i = 0;

	if (!query)
		return SQL_RESULT_ERROR;

	if (pg_conn.established == FALSE)
		return SQL_RESULT_ERROR;

	/* Set variables to default */
	pg_conn.query_result = SQL_RESULT_UNDEFINED;
	pg_conn.num_fields = 0;
	pg_conn.num_columns = 0;
	
	/* Clear previously used variables */		
	if (pg_conn.errmsg)
		safe_free(&(pg_conn.errmsg));
	if (pg_conn.resultptr)
		safe_free(&(pg_conn.resultptr));
	if (pg_conn.pg_result != NULL) 
		PQclear(pg_conn.pg_result);

	/* Make an Query */
	
	pg_conn.pg_result = PQexec(pg_conn.pg_handle, query);
	
	switch(PQresultStatus(pg_conn.pg_result)) {
		case PGRES_TUPLES_OK:    
			pg_conn.query_result = SQL_RESULT_OK;
			break;
		
		case PGRES_EMPTY_QUERY:
		case PGRES_COMMAND_OK:
			pg_conn.query_result = SQL_RESULT_EMPTY;
			break;

		default:
			pg_conn.errmsg = strdup(PQresultErrorMessage(pg_conn.pg_result));
			pg_conn.query_result = SQL_RESULT_ERROR;
			break;
	}

	if (pg_conn.query_result == SQL_RESULT_EMPTY) {
		return pg_conn.query_result;
	}

	if (pg_conn.query_result == SQL_RESULT_ERROR) {
		if (pg_conn.pg_result != NULL) 
			PQclear(pg_conn.pg_result);
		return pg_conn.query_result;
	}

	pg_conn.num_fields = PQnfields(pg_conn.pg_result);
	pg_conn.num_columns = PQntuples(pg_conn.pg_result);

	if (pg_conn.num_columns == 0)
		pg_conn.query_result = SQL_RESULT_EMPTY;

	return pg_conn.query_result;
};

char * pgsql_get_value(unsigned int row, unsigned int column)
{
	return clear_white_spaces_abae(PQgetvalue(pg_conn.pg_result, row, column));
};

unsigned int pgsql_get_rows_count()
{
	return pg_conn.num_columns;
};

char * pgsql_get_field_name(unsigned int column)
{
	return clear_white_spaces_abae(PQfname(pg_conn.pg_result, column));
};

unsigned int pgsql_get_cols_count()
{
	return pg_conn.num_fields;
};

unsigned int pgsql_get_length(unsigned int row, unsigned int column)
{
	return PQgetlength(pg_conn.pg_result, row, column);
};

unsigned int pgsql_get_is_null(unsigned int row, unsigned int column)
{
	unsigned int result;
	result = PQgetisnull(pg_conn.pg_result, row, column);
	if (result == 1)
		return RES_TRUE;
	
	return RES_FALSE;
};

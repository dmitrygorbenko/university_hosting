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
 
#include "libpq-fe.h"
 
struct __pgsql_connection {

	unsigned int established;
	unsigned int num_fields;
	unsigned int num_columns;
	unsigned int query_result;
	
	PGconn *pg_handle;
	PGresult *pg_result;
	char *errmsg;
	char *resultptr;
		
	char * pghost;
	char * pgport;
	char * pgoptions;
	char * pgtty;
	char * dbName;
	char * login;
	char * pwd;
};

unsigned int pgsql_socket_init();
unsigned int pgsql_connect();
void pgsql_close_connect();
unsigned int pgsql_make_query(char * query);
char * pgsql_get_value(unsigned int row, unsigned int column);
unsigned int pgsql_get_rows_count();
unsigned int pgsql_get_cols_count();
char * pgsql_get_field_name(unsigned int column);
unsigned int pgsql_get_length(unsigned int row, unsigned int column);
unsigned int pgsql_get_is_null(unsigned int row, unsigned int column);

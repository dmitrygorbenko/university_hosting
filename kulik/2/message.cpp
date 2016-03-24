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
#include <stdarg.h>

#include "define.h"
#include "message.h"
#include "str.h"

using namespace std;

struct mess_state_def m_state;

void message(unsigned int count, ...)
{
	va_list ap;

	if (m_state.log_init == TRUE) {
		m_state.log_open = TRUE;
		m_state.log_fd = fopen (m_state.log_file_name, "a");

		if (m_state.log_fd == NULL) {
			fprintf (stdout, "Message_Output: Can't open/create log file\n");
			return;
		}

		va_start(ap, count);

		for(;count > 0; count--)
			fprintf (m_state.log_fd, "%s", va_arg(ap, char *));

		va_end (ap);

		fclose (m_state.log_fd);
		m_state.log_open = TRUE;
	}
	else {
		va_start(ap, count);

		for(;count > 0; count--)
			fprintf (stdout, "%s", va_arg(ap, char *));

		va_end (ap);
	}
}

unsigned int message_init(char * log_file_name)
{
	m_state.log_fd = NULL;
	m_state.log_file_name = NULL;
	m_state.log_init = FALSE;

	if (!log_file_name) {
		fprintf (stdout, "Message_Init: Bad log file name\n");
		return FALSE;
	}

	m_state.log_file_name = safe_strdup(log_file_name);

	if (!m_state.log_file_name) {
		fprintf (stdout, "Message_Init: Failed copy log file name\n");
		return FALSE;
	}

	m_state.log_fd = fopen(m_state.log_file_name, "a");

	if (m_state.log_fd == NULL) {
		fprintf (stdout, "Message_Init: Can't open/create log file\n");
		return FALSE;
	}

	fclose (m_state.log_fd);

	m_state.log_open = FALSE;
	m_state.log_init = TRUE;

	return TRUE;
};

void message_close()
{
	if (m_state.log_open == TRUE)
		fclose(m_state.log_fd);
};

void message_shutdown()
{
	if (m_state.log_file_name)
		safe_free(&m_state.log_file_name);
}

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

unsigned int protect_manage(char *cmd);
unsigned int protect_create_manage(char *cmd);
unsigned int protect_update_manage(char *cmd);
unsigned int protect_remove_manage(char *cmd);

unsigned int protect_create_user(char * cmd);
unsigned int protect_create_group(char * cmd);
unsigned int protect_create_area(char * cmd);
unsigned int protect_update_user(char * cmd);
unsigned int protect_update_group(char * cmd);
unsigned int protect_update_area(char * cmd);
unsigned int protect_remove_user(char * cmd);
unsigned int protect_remove_group(char * cmd);
unsigned int protect_remove_area(char * cmd);

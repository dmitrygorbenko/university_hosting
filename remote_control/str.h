/***************************************************************************
 *   Copyright (C) 2003, 2004, 2005                                        *
 *   by Dmitriy Gorbenko. "XAI" University, Kharkov, Ukraine               *
 *   e-mail: nial@ukr.net                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 ***************************************************************************/

/*
 *  Define size for block of bytes for reading at `read_string' function
 */

#define MAX_READ_COUNT 1000
#define STRING_SPRINTF_BUFFER_SIZE (8192 * 4)

void safe_free(char ** str);
void plus_term (char ** from, char * term);
char smart_get_byte(char * str, unsigned int pos);
char * read_string (FILE *file);
char * read_string_2 (FILE *file);
char * read_string_3 (FILE *file);
char * string_sprintf(char *format, ...);
char * cut_quotes(char * str);
char * get_first_word(char *str);
char * del_first_word(char *str);
char * get_before_slash(char * word);
char * get_after_slash(char * word);
char * add_char_to_string(char * str, char byte);
char * safe_strdup(char * str);
char * get_and_del_first_word(char **str);
char * get_word_by_number(char *str, unsigned int k);
char * add_string_to_string(char ** str, char* add);
char * sts(char ** str, char* add);
char * int_to_string(int i);
char * get_param(char * str);
char * trim(char * str);
char * convert_time_format(char * old);
char * merge_strings(unsigned int count, ...);
unsigned int string_vformat(char *buffer, int buflen, char *format, va_list ap);
unsigned long convert_time(char * t);
unsigned int convert_month(char * m);
unsigned int convert_day(char * m);
unsigned int how_much_words(char * str);
unsigned int special_how_much_words(char * str, unsigned int flag);
unsigned int get_word_position(char * str, char * example);

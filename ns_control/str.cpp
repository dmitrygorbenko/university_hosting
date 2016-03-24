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

#ifdef HAVE_CONFIG_H
#include <config.h>
#endif

#include <cstdio>
#include <cstdlib>
#include <cstring>

#include <stdarg.h>
#include "ctype.h"

#include "define.h"
#include "str.h"

using namespace std;

char * read_string_3 (FILE * file)
{
	unsigned int i = 0;
	char * str = NULL;
	char buf[MAX_READ_COUNT+1];

	do {
		if (i == 0)
			str = (char*)calloc(MAX_READ_COUNT+1,sizeof(char));
		else
			str = (char *)realloc(str,strlen(str) + MAX_READ_COUNT + i);

		memset(buf,0,MAX_READ_COUNT+1);
		fgets(buf,MAX_READ_COUNT+1,file);

		strcat(str,buf);

		if (buf[strlen(buf)-1] == '\n') {
			str[strlen(str)-1] = '\0';
			break;
		}

		i++;
	} while (!feof(file));

	return str;
};

char * read_string_2(FILE * file)
{
	char * str = NULL;

	do {
		if (str)
			safe_free(&str);
		str = read_string_3(file);
	}
	while ( (str[0] == '#' || (strstr(str, "#") != (char) NULL)) && !feof(file) );

	if (!str)
		return NULL;

	return str;
};

char * read_string(FILE * file)
{
	char * str = NULL;
	char * buf = NULL;
	unsigned int result = TRUE;

	do {
		buf = NULL;
		str = read_string_2(file);
		buf = get_first_word(str);

		if (buf == NULL) {
			safe_free(&str);
			result = FALSE;
		}
		else {
			safe_free(&buf);
			result = TRUE;
		}
	}
	while (result == FALSE && !feof(file));

	return str;
};

char * cut_quotes(char * str)
{
	unsigned int i = 0;
	unsigned int len = 0;
	unsigned int len1 = 0;
	unsigned int len2 = 0;
	unsigned int find_quote = 0;
	char * buf = NULL;
	char * temp = NULL;
	char * result = NULL;

	if (!str)
		return NULL;

	len = strlen(str);

	for (i=0; i<len && !find_quote; i++)
		if (str[i] == '\"')
			find_quote = 1;

	if (find_quote == 0)
		return str;

	if (i >= (len-1))
		return NULL;

	buf = strchr(str,'\"');
	buf++;
	buf = strdup(buf);

	len1 = strlen(buf);

	temp = strrchr(buf,'\"');
	if (temp == NULL)
		return NULL;
	len2 = strlen(temp);

	result = (char *)strndup(buf,len1-len2);

	if (buf)
		safe_free(&buf);

	return result;
};

char * get_first_word(char *str)
{
	unsigned int i = 0;
	unsigned int len = 0;
	char * result = NULL;
	char * copy = NULL, * back_copy = NULL;

	/* is string correct ? */
	if(!str)
		return NULL;

	/* we should work with copy */
	copy = strdup(str);
	if (!copy)
		return NULL;

	back_copy = copy;
	len = strlen(copy); /* if we don't want receive SIGSEGV */

	/* skip white spaces */
	while ((*copy == ' ' || *copy == '\t') && i < len)
		{ copy++; i++; }

	/* where are only white spaces -> return */
	if (i >= len) { // >= is really need
		copy = back_copy;
		safe_free(&copy);
		return NULL;
	}

	len = strlen(copy);  /* if we don't want receive SIGSEGV */
	i = 0;
	while ((copy[i] != ' ' && copy[i] != '\t') && i < len)
		i++;

	result = (char *)strndup(copy,i);
	if (!result) {
		copy = back_copy;
		safe_free(&copy);
		return NULL;
	}

	/* restore copy for free memory */
	copy = back_copy;
	safe_free(&copy);

	return result;
};

char * del_first_word(char *str)
{
	unsigned int i = 0, len = 0;
	char * buf = NULL;
	char * copy = NULL;


	if(!str)
		return str;

	/* save str - we need it for free(str) bellow */
	copy = str;

	len = strlen(str);

	/* skipping white spaces at begining */
	while ((*str == ' ' || *str == '\t') && i < len)
		{ str++; i++; }

	if(i == len) /* where are only spaces in str - return NULL */
	/* and keep `str` is same */
		return NULL;

	/* one step to word */
	/* `( )word' */
	str++; i++;
	/* ` (w)ord' */

	/* move to the end of word */
	while (*str != ' ' && *str != '\t' && i < len)
		{ str++; i++; }

	/* check, if we have only white spaces at end*/
	while ((*str == ' ' || *str == '\t') && i < len)
		{ str++; i++; }

	/*
	 * (i == len) means where are nothing after word - return NULL
	 * but we do not need to do something, because buf already == NULL,
	 * and next if(...) == FALSE, because i==len .
	 */

	/* It's all right */
	if (strlen(str) != 0 && i < len)
		buf = (char *)strdup(str);

	/* recover str for free */
	str = copy;
	safe_free(&str);

	if (!buf)
		return NULL;

	return buf;
};

unsigned long convert_time(char * t)
{
	unsigned long result = 0;
	unsigned int len = 0;
	unsigned int i = 0;
	unsigned int count_dotes = 0;
	char *buf = NULL;
	char *tmp = NULL;

	if(!t)
		return 0;

	len = strlen(t);

	for (i=0; i<len; i++)
		if (t[i] == ':')
			count_dotes++;

	if (count_dotes != 2)
		return 0;

	tmp = (char *)strrchr(t,':');
	tmp++;
	result += atoi(tmp);

	len = strlen(t) - strlen(tmp) - 1;
	buf = (char *)strndup(t,len);
	if (!buf)
		return 0;
	tmp = (char *)strrchr(buf,':');
	tmp++;
	result += atoi(tmp) * 60;
	len = len - strlen(tmp) - 1;
	safe_free(&buf);

	buf = (char *)strndup(t,len);
	if (!buf)
		return 0;
	result += atoi(buf) * 3600;
	safe_free(&buf);

	return result;
};

char * convert_time_format(char * old)
{
// from: Wed Oct 20 11:02:31 2004
// to:   2004-11-06 20:22:06
	unsigned int i = 0;
	char *result = NULL;
	char *temp = NULL;
	char *ntemp = NULL;

	if(!old)
		return NULL;

	temp = get_word_by_number (old, 5);
	ntemp =  trim(temp);
	result = sts (&result, ntemp);
	result = sts (&result, "-");

	temp = get_word_by_number (old, 2);
	i = convert_month(temp);
	result = sts (&result, int_to_string(i));
	result = sts (&result, "-");

	temp = get_word_by_number (old, 3);
	result = sts (&result, temp);
	result = sts (&result, " ");

	temp = get_word_by_number (old, 4);
	result = sts (&result, temp);

	return result;
};

void plus_term (char ** from, char * term)
{
// from:     2004-11-06 20:22:06  term: year
// result:   2005-11-06 20:22:06
	unsigned int i = 0;
	char *temp = NULL;
	char year[5] = {'\0'};

	if(!term)
		return;
	if(!(*from))
		return;

	if (strcmp(term, "year") == 0) {
		for (i=0; i<4; i++)
			year[i] = smart_get_byte(*from, i);

		i = atoi(year);
		i++;
		temp = int_to_string(i);

		for (i=0; i<4; i++)
			(*from)[i] = temp[i];
	}
};


void safe_free(char ** str)
{
	free(*str);
	*str = NULL;
};

char * get_before_slash(char * word)
{
	char * result = NULL;
	unsigned int find_slash = 0;
	unsigned int len = 0, i = 0;

	if (!word)
		return NULL;

	len = strlen(word);

	for (i = 0; i < len && !find_slash; i++)
		if (word[i] == '/')
			find_slash = 1;

	if (find_slash == 0) {
		result = (char *)safe_strdup(word);
		return result;
	}

	if (i == 1)
		return NULL;

	result = (char *)strndup(word,i-1);

	return result;
};

char * get_after_slash(char * word)
{
	char * result = NULL;
	unsigned int find_slash = 0;
	unsigned int len = 0, i = 0;

	if (!word)
		return NULL;

	len = strlen(word);

	for (i = 0; i< len && !find_slash; i++)
		if (word[i] == '/')
			find_slash = 1;

	if (find_slash == 0)
		return NULL;

	if (i == len)
		return NULL;

	result = strchr(word,'/');
	result++;
	result = strdup(result); /* allocate new memory */

	return result;
};

char smart_get_byte(char * str, unsigned int pos)
{
	unsigned int len = 0;
	char byte;

	if(!str)
		return 0;

	len = strlen(str);
	if (pos >= len)
		return 0;

	byte = str[pos];

	return byte;
};

char * add_char_to_string(char * str, char byte)
{
	char * result = NULL;
	unsigned int len = 0;

	if (!str)
		len = 0;
	else
		len = strlen(str);

	result = (char*) calloc(len+2, sizeof(char));

	if (!result)
		return NULL;

	if (len != 0)
		strcat(result,str);

	result[len] = byte;
	result[len+1] = '\0';

	if (str)
		safe_free(&str);

	return result;
};

char * safe_strdup(char * str)
{
	char * result = NULL;
	unsigned int len = 0;

	if (!str)
		len = 0;
	else
		len = strlen(str);

	result = (char*) calloc(len+1, sizeof(char));

	if (!result)
		return NULL;

	if (len != 0)
		strcat(result,str);

	result[len] = '\0';

	return result;
};

unsigned int how_much_words(char * str)
{
	unsigned int count = 0;
	char * copy = NULL;

	if (!str)
		return 0;

	copy = get_first_word(str);
	if (copy == NULL)
		return 0;

	safe_free(&copy);
	copy = strdup(str);

	while (copy) {
		copy = (char *)del_first_word(copy);
		count++;
	}

	return count;
}

unsigned int special_how_much_words(char * str, unsigned int flag)
/*  Флаг (если истина) означает учитывать слова начинающиеся с симвоал '~' */
{
	unsigned int count = 0;
	char * copy = NULL;
	char * temp = NULL;

	if (!str)
		return 0;

	copy = strdup(str);

	while (copy) {
		temp = get_first_word(copy);
		copy = (char *)del_first_word(copy);

		/* не счетает слова содержащие символ '~' в начале слова */
		if (flag == FALSE) {
			if (temp[0] != '~' && strcmp(temp, "*") != 0 && strcmp(temp, "~") != 0)
				count++;
		}
		/* счетает слова содержащие символ '~' в начале слова */
		else {
			if (strcmp(temp, "*") != 0 && strcmp(temp, "~") != 0)
				count++;
		}

		if (temp)
			safe_free(&temp);
	}

	return count;
}

unsigned int get_word_position(char * str, char * example)
{
	unsigned int count = 0;
	unsigned int position = 0;
	char * copy = NULL;
	char * temp = NULL;

	if (!str)
		return 0;

	if (!example)
		return 0;

	copy = strdup(str);

	while(copy) {
		temp = get_first_word(copy);
		if (strcmp(temp, example) == 0)
			position = count;
		copy = (char *)del_first_word(copy);
		count++;
		if (temp)
			safe_free(&temp);
	}

	return position;
}

char * get_and_del_first_word(char **str)
{
	char * result = NULL;

	if (!str)
		return NULL;

	if (!(*str))
		return NULL;

	result = get_first_word(*str);
	*str = del_first_word(*str);

	return result;
}

char * get_word_by_number(char *str, unsigned int k)
{
	unsigned int i = 0;
	unsigned int count = 0;
	char * result = NULL;
	char * copy = NULL;

	if (!str)
		return NULL;

	count = how_much_words(str);

	if (count < k)
		return NULL;

	copy = strdup(str);

	for (i=0; i < k-1; i++)
		copy = del_first_word (copy);

	result = get_first_word (copy);

	return result;
}

unsigned int convert_month(char * m)
{
	unsigned int result;

	if(strcmp(m,"Jan") == 0) result = 1;
	if(strcmp(m,"Feb") == 0) result = 2;
	if(strcmp(m,"Mar") == 0) result = 3;
	if(strcmp(m,"Arp") == 0) result = 4;
	if(strcmp(m,"May") == 0) result = 5;
	if(strcmp(m,"Jun") == 0) result = 6;
	if(strcmp(m,"Jul") == 0) result = 7;
	if(strcmp(m,"Aug") == 0) result = 8;
	if(strcmp(m,"Sep") == 0) result = 9;
	if(strcmp(m,"Oct") == 0) result = 10;
	if(strcmp(m,"Nov") == 0) result = 11;
	if(strcmp(m,"Dec") == 0) result = 12;

	return result;
};

unsigned int convert_day(char * m)
{
	unsigned int result;

	if(strcmp(m,"Mon") == 0) result = 1;
	if(strcmp(m,"Tue") == 0) result = 2;
	if(strcmp(m,"Wed") == 0) result = 3;
	if(strcmp(m,"Thu") == 0) result = 4;
	if(strcmp(m,"Fri") == 0) result = 5;
	if(strcmp(m,"Sat") == 0) result = 6;
	if(strcmp(m,"Sun") == 0) result = 7;

	return result;
};


char * add_string_to_string(char ** str, char* add)
{
	char * result = NULL;
	unsigned int len = 0;
	unsigned int len_add = 0;
	unsigned int i = 0;

	if (!str)
		len = 0;
	else {
		if (!(*str))
			len = 0;
		else
			len = strlen((*str));
	}

	if (!add)
		len_add = 0;
	else
		len_add = strlen(add);

	result = (char*) calloc(len + len_add + 1, sizeof(char));

	if (!result)
		return NULL;

	if (len != 0) {
		strcat(result, (*str));

		while (i < len_add)
			result[len + i++] = add[i];
	}
	else {
		if (len_add != 0)
			strcat(result, add);
	}

	result[len + len_add] = '\0';

	if (str)
		if ((*str))
			safe_free(str);

	return result;
};

char * sts(char ** str, char* add)
{
	char * result = NULL;
	unsigned int len = 0;
	unsigned int len_add = 0;
	unsigned int i = 0;

	if (!str)
		len = 0;
	else {
		if (!(*str))
			len = 0;
		else
			len = strlen((*str));
	}

	if (!add)
		len_add = 0;
	else
		len_add = strlen(add);

	result = (char*) calloc(len + len_add + 1, sizeof(char));

	if (!result)
		return NULL;

	if (len != 0) {
		strcat(result, (*str));

		while (i < len_add)
			result[len + i++] = add[i];
	}
	else {
		if (len_add != 0)
			strcat(result, add);
	}

	result[len + len_add] = '\0';

	if (str)
		if ((*str))
			safe_free(str);

	return result;
};

char * int_to_string(int i)
{
	char * result = NULL;
	char tmp = 0;
	unsigned int len = 0;
	unsigned int k = 0;
	unsigned int sign = FALSE;

	if (i < 0) {
		result = add_char_to_string(result, '-');
		i = -i;
		sign = TRUE;
	}

	if (i == 0) {
		result = add_char_to_string(result, '0');
		return result;
	}

	while (i) {
		result = add_char_to_string(result, (i % 10) + 48);
		i = i / 10;
	}

	if (sign == TRUE)
		result++;

	len = strlen(result);

	for(k=0; k<(len/2); k++) {
		tmp = result[k];
		result[k] = result[len-k-1];
		result[len-k-1] = tmp;
	}

	if (sign == TRUE)
		result--;

	return result;
};

char * get_param(char * str)
{
	char * buf = NULL;
	char * temp = NULL;

	if (!str)
		return NULL;

	buf = strstr(str,"=");
	buf = strdup(++buf);

	if (how_much_words(buf) == 0) {
		if (buf)
			safe_free(&buf);
		return NULL;
	}

	temp = cut_quotes(buf);

	if (temp) {
		safe_free(&buf);
		return temp;
	}

	temp = trim(buf);

	return temp;
};

char * trim(char * str)
{
	unsigned int i = 0;
	unsigned int k = 0;
	unsigned int len = 0;
	char * result = NULL;
	char * copy = NULL, * back_copy = NULL;

	/* is string correct ? */
	if(!str)
		return NULL;

	/* we should work with copy */
	copy = strdup(str);
	if (!copy)
		return NULL;

	back_copy = copy;
	len = strlen(copy); /* if we don't want receive SIGSEGV */

	/* skip white spaces at the beginning*/
	while ((*copy == ' ' || *copy == '\t') && i < len)
		{ copy++; i++; }

	/* where are only white spaces -> return */
	if (i >= len) { // >= is really need
		copy = back_copy;
		safe_free(&copy);
		return NULL;
	}

	i = strlen(copy) - 1;

	/* skip white spaces at the end*/
	while ((copy[i] == ' ' || copy[i] == '\t') && i > 0)
		{ i--; k++; }

	result = (char *)strndup(copy, strlen(copy) - k);

	/* restore copy for free memory */
	copy = back_copy;
	safe_free(&copy);

	return result;
};

// From Exim
unsigned int string_vformat(char *buffer, int buflen, char *format, va_list ap)
{
	enum { L_NORMAL, L_SHORT, L_LONG, L_LONGLONG, L_LONGDOUBLE };

	unsigned int yield = TRUE;
	int width, precision;
	char *fp = format;             /* Deliberately not unsigned */
	char *p = buffer;
	char *last = buffer + buflen - 1;

	/* Scan the format and handle the insertions */

	while (*fp != 0) {
		int length = L_NORMAL;
		int *nptr;
		int slen;
		char *null = "NULL";         /* ) These variables */
		char *item_start, *s;        /* ) are deliberately */
		char newformat[16];          /* ) not unsigned */

		/* Non-% characters just get copied verbatim */

		if (*fp != '%') {
			if (p >= last) {
				yield = FALSE;
				break;
			}
			*p++ = (char)*fp++;
			continue;
		}

		/* Deal with % characters. Pick off the width and precision, for checking
		strings, skipping over the flag and modifier characters. */

		item_start = fp;
		width = precision = -1;

		if (strchr("-+ #0", *(++fp)) != NULL) {
			if (*fp == '#')
				null = "";
			fp++;
		}

		if (isdigit((char)*fp)) {
			width = *fp++ - '0';
			while (isdigit((char)*fp))
				width = width * 10 + *fp++ - '0';
		}
		else if (*fp == '*') {
			width = va_arg(ap, int);
			fp++;
		}

		if (*fp == '.') {
			if (*(++fp) == '*') {
				precision = va_arg(ap, int);
				fp++;
			}
			else {
				precision = 0;
				while (isdigit((char)*fp))
					precision = precision*10 + *fp++ - '0';
			}
		}

		/* Skip over 'h', 'L', 'l', and 'll', remembering the item length */

		if (*fp == 'h') {
			fp++;
			length = L_SHORT;
		}
		else if (*fp == 'L') {
			fp++;
			length = L_LONGDOUBLE;
		}
		else if (*fp == 'l') {
			if (fp[1] == 'l') {
				fp += 2;
				length = L_LONGLONG;
			}
			else {
				fp++;
				length = L_LONG;
			}
		}

		/* Handle each specific format type. */

		switch (*fp++) {
			case 'n':
				nptr = va_arg(ap, int *);
				*nptr = p - buffer;
				break;

			case 'd':
			case 'o':
			case 'u':
			case 'x':
			case 'X':
				if (p >= last - ((length > L_LONG)? 24 : 12)) {
					yield = FALSE;
					goto END_FORMAT;
				}
				strncpy(newformat, item_start, fp - item_start);
				newformat[fp - item_start] = 0;

				/* Short int is promoted to int when passing through ..., so we must use
				int for va_arg(). */

				switch(length) {
					case L_SHORT:
					case L_NORMAL:   sprintf( p, newformat, va_arg(ap, int)); break;
					case L_LONG:     sprintf( p, newformat, va_arg(ap, long int)); break;
					case L_LONGLONG: sprintf( p, newformat, va_arg(ap, long long int)); break;
				}
				while (*p)
					p++;
				break;

			case 'p':
				if (p >= last - 24) {
					yield = FALSE;
					goto END_FORMAT;
				}
				strncpy(newformat, item_start, fp - item_start);
				newformat[fp - item_start] = 0;
				sprintf( p, newformat, va_arg(ap, void *));
				while (*p)
					p++;
				break;

			/* %f format is inherently insecure if the numbers that it may be
			handed are unknown (e.g. 1e300). However, in Exim, %f is used for
			printing load averages, and these are actually stored as integers
			(load average * 1000) so the size of the numbers is constrained.
			It is also used for formatting sending rates, where the simplicity
			of the format prevents overflow. */

			case 'f':
			case 'e':
			case 'E':
			case 'g':
			case 'G':
				if (precision < 0)
					precision = 6;
				if (p >= last - precision - 8) {
					yield = FALSE;
					goto END_FORMAT;
				}
				strncpy(newformat, item_start, fp - item_start);
				newformat[fp-item_start] = 0;
				if (length == L_LONGDOUBLE)
					sprintf( p, newformat, va_arg(ap, long double));
				else
					sprintf( p, newformat, va_arg(ap, double));
				while (*p)
					p++;
				break;

			/* String types */

			case '%':
				if (p >= last) {
					yield = FALSE;
					goto END_FORMAT;
				}
				*p++ = '%';
				break;

			case 'c':
				if (p >= last) {
					yield = FALSE;
					goto END_FORMAT;
				}
				*p++ = va_arg(ap, int);
				break;

			case 's':
			case 'S':                   /* Forces *lower* case */
				s = va_arg(ap, char *);

//				INSERT_STRING:              /* Come to from %D above */
				if (s == NULL)
					s = null;
				slen = strlen(s);

				/* If the width is specified, check that there is a precision
				set; if not, set it to the width to prevent overruns of long
				strings. */

				if (width >= 0) {
					if (precision < 0)
						precision = width;
				}

				/* If a width is not specified and the precision is specified, set
				the width to the precision, or the string length if shorted. */

				else if (precision >= 0) {
					width = (precision < slen)? precision : slen;
				}

				/* If neither are specified, set them both to the string length. */


				else
					width = precision = slen;

				/* Check string space, and add the string to the buffer if ok. If
				not OK, add part of the string (debugging uses this to show as
				much as possible). */

				if (p >= last - width) {
					yield = FALSE;
					width = precision = last - p - 1;
				}
				sprintf( p, "%*.*s", width, precision, s);
				if (fp[-1] == 'S')
					while (*p) {
						*p = tolower(*p);
						p++;
					}
				else
					while (*p)
						p++;
				if (!yield)
					goto END_FORMAT;
				break;

			/* Some things are never used in Exim; also catches junk. */

			default:
				strncpy(newformat, item_start, fp - item_start);
				newformat[fp-item_start] = 0;
				break;
		}
	}

	/* Ensure string is complete; return TRUE if got to the end of the format */

	END_FORMAT:

	*p = 0;
	return yield;
}

// From Exim
char * string_sprintf(char *format, ...)
{
	va_list ap;
	char buffer[STRING_SPRINTF_BUFFER_SIZE];
	va_start(ap, format);

	if (!string_vformat(buffer, sizeof(buffer), format, ap))
  		return NULL;

	va_end(ap);

	return strdup(buffer);
}

char * merge_strings(unsigned int count, ...)
{
	va_list ap;
	char *result = NULL;

	va_start(ap, count);

	for(;count > 0; count--)
		result = sts(&result, va_arg(ap, char *));

	va_end(ap);

	return result;
}

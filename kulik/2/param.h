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

void clear_Param_list();

/*
 * List of Param. It determines program execution
 */

class Param_Nod
{
public:
	// user variables
	char * param;
	char * value;
	// class variables
	Param_Nod* next;
	Param_Nod(){next = NULL;};
	~Param_Nod(){};
};

class Param {
public:
	Param_Nod* start,*current;
	unsigned long Count;
	Param() { start = current = NULL; Count=0; };
	~Param(){};

	unsigned int	Add(Param_Nod *);
	Param_Nod*	Get(unsigned int );
	void		Delete(unsigned int );
	Param_Nod*	operator[](unsigned int );
};

void SwapChar(char * pOriginal, char cBad, char cGood);
void URLDecode(char *pEncoded);
void PrintOut (char * VarVal);
void Parse_pairs();
char * get_value(char * param);
unsigned int IntFromHex(char *pChars);

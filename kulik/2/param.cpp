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

#include <cstring>
#include <cstdio>
#include <cstdlib>
#include <ctype.h>

#include "define.h"
#include "param.h"
#include "str.h"

using namespace std;

Param P;

char * InputBuffer;		// local storage for input

void clear_param_list()
{
	while(P.Count)
		P.Delete(P.Count);
};

Param_Nod *Param::operator[](unsigned int num)
{
	unsigned int i;
	Param_Nod *Pn;
	Pn = start;
	for (i=0; i<num; i++) {
		if(Pn->next) Pn = Pn->next;
		else return Pn;
	}
	return Pn;
};

unsigned int Param::Add(Param_Nod * new_Pn)
{
	Param_Nod * Pn;
  	if (!(Pn = new Param_Nod))
		return RES_FALSE;

	Pn = new_Pn;

	if(!start)
		start = current = Pn;
	else
		current = current->next = Pn;
        Count++;
	return 0;
};

Param_Nod* Param::Get(unsigned int num)
{
	unsigned int i;
	Param_Nod *Pn;
	Pn = start;
	for (i=0; i<num; i++) {
		if(Pn->next) Pn = Pn->next;
		else return Pn;
	}
	return Pn;
};

void Param::Delete(unsigned int num)
{
	unsigned int i;

	Param_Nod *t,*p;

	if (Count == 0)
		return;

	if( num == 0 || Count == 1) {
		p = start;
		start = start->next;
	}
	else {
		t = start;
		for (i=0; i<(num-1); i++)
			if (t->next->next) t = t->next;
		p = t->next;
		if (!t->next->next) current = t;
		t->next = t->next->next;
	}

	delete p->param;
	delete p->value;
	delete p;

	if(Count)Count--;
};


void SwapChar(char * pOriginal, char cBad, char cGood)
{
	unsigned int i = 0;

	while (pOriginal[i]) {
		if (pOriginal[i] == cBad) pOriginal[i] = cGood;
		i++;
	}
};


void URLDecode(char *pEncoded)
{
	char *pDecoded;

	// First, change those pesky plusses to spaces
	SwapChar (pEncoded, '+', ' ');

	// Now, loop through looking for escapes
	pDecoded = pEncoded;
	while (*pEncoded) {
		if (*pEncoded=='%') {
			// A percent sign followed by two hex digits means
			// that the digits represent an escaped character.
			// We must decode it.

			pEncoded++;
			if (isxdigit(pEncoded[0]) && isxdigit(pEncoded[1])) {
				*pDecoded++ = (char) IntFromHex(pEncoded);
				pEncoded += 2;
			}
		} else {
			*pDecoded ++ = *pEncoded++;
		}
	}

	*pDecoded = '\0';
};

unsigned int IntFromHex(char *pChars)
{
	int Hi;		// holds high byte
	int Lo;		// holds low byte
	int Result;	// holds result

	// Get the value of the first byte to Hi

	Hi = pChars[0];
	if ('0' <= Hi && Hi <= '9') {
		Hi -= '0';
	} else
	if ('a' <= Hi && Hi <= 'f') {
		Hi -= ('a'-10);
	} else
	if ('A' <= Hi && Hi <= 'F') {
		Hi -= ('A'-10);
	}

	// Get the value of the second byte to Lo

	Lo = pChars[1];
	if ('0' <= Lo && Lo <= '9') {
		Lo -= '0';
	} else
	if ('a' <= Lo && Lo <= 'f') {
		Lo -= ('a'-10);
	} else
	if ('A' <= Lo && Lo <= 'F') {
		Lo -= ('A'-10);
	}

	Result = Lo + (16 * Hi);

	return (Result);
};

void PrintOut (char * VarVal)
{
	char * pEquals;		// pointer to equals sign
	int  i;
	Param_Nod * Pn;

	pEquals = strchr(VarVal, '=');	// find the equals sign
	if (pEquals != NULL) {
		*pEquals++ = '\0';	// terminate the Var name
		URLDecode(VarVal);	// decode the Var name

		// Convert the Var name to upper case
		i = 0;
		while (VarVal[i]) {
			VarVal[i] = toupper(VarVal[i]);
			i++;
		}

		// decode the Value associated with this Var name
		URLDecode(pEquals);

		// print out the var=val pair

		Pn = new Param_Nod;

		Pn->param = VarVal;
		Pn->value = pEquals;

		P.Add(Pn);
	}

};

void Parse_pairs()
{
	char * pToken;

	InputBuffer = getenv("QUERY_STRING");

	pToken = strtok(InputBuffer,"&");
	while (pToken != NULL) {		// While any tokens left in string
		PrintOut (pToken);		// Do something with var=val pair
		pToken = strtok(NULL,"&");	// Find the next token
	}

};

char * get_value(char * param)
{
	char * result = NULL;
	char * pToken = NULL;
	char * env = NULL;
	unsigned int i = 0;
	
	if (!param)
		return NULL;
	
	for (i=0; i< P.Count; i++)
		if (strcmp(P[i]->param, param) == 0) {
			result = strdup(P[i]->value);
			break;
		}
/*
	if (result == NULL) {
		env = strdup("_POST[\"");
		env = sts(&env, param);
		env = sts(&env, "\"]");
		pToken = getenv(env);
		safe_free(&env);
		if (pToken)
			PrintOut(pToken);
		
		for (i=0; i< P.Count; i++)
			if (strcmp(P[i]->param, param) == 0) {
				result = strdup(P[i]->value);
				break;
			}
	}
*/
	return result;
};

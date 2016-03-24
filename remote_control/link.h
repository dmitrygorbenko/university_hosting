#ifndef __LINK__
#define __LINK__

template <class T> class Nod
{
public:
T a;
Nod* next;
Nod(){next = NULL;};
~Nod(){};
};

template <class T> class Spisok
{
public:
Nod<T>* start,*current;
unsigned long Count;

Spisok() { start = current = NULL; Count=0; };
int	Add(T );
T 	Get(int );
void	Delete(int );
int	Insert(int ,T );
Nod<T> *operator[](int );
~Spisok(){};
};

template <class T> Nod<T> *Spisok<T>::operator[](int c)
{
	Nod<T> *t;
	t = start;
	for (int i=0;i<c;i++)
	{
		if(t->next) t = t->next;
		else return t;
	}
	return t;
};

template <class T> int Spisok<T>::Add(T c)
{
	Nod<T> * p;
  if (!(p = new Nod<T>)) return 1;
	p->a = c;
	if(!start)
		start = current = p;
	else
	current = current->next = p;
        Count++;
	return 0;
};

template <class T> T Spisok<T>::Get(int c)
{
	Nod<T> *t;
	t = start;
	for (int i=0;i<c;i++)
	{
		if(t->next) t = t->next;
		else return t->a;
	}
	return t->a;
};

template <class T> void Spisok<T>::Delete(int z)
{
	Nod<T> *t,*p;

	if (Count == 0)
		return;

	if( z == 0 || Count == 1) {
		p = start;
		start = start->next;
	}
	else {
		t = start;
		for (int i=0;i<(z-1);i++)
			if (t->next->next) t = t->next;
		p = t->next;
		if (!t->next->next) current = t;
		t->next = t->next->next;
	}
	delete p;

	if(Count)Count--;
};

template <class T> int Spisok<T>::Insert(int z, T c)
{
	Nod<T> *t;
	Nod<T> *p;
  if (!(p = new Nod<T>)) return 1;
	p->a = c;

if(z == 0)
	{
		p->next = start;
		start = p;
	}
else
	{
		t = start;
		for (int i=0;i<(z-1);i++)
			if(t->next) t = t->next;

		if(!t->next)
			current = p;

		else
			p->next = t->next;

		t->next = p;
	}
Count++;
return 0;
};

#endif


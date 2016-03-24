CC=gcc
CFLAGS=-g -O -Wall -D_REENTRANT
LIBPTHREAD= /usr/lib/libpthread.a

PROGS=ex1 ex2 ex3 ex4 ex5 proxy

all: $(PROGS)

.c:
	$(CC) $(CFLAGS) -o $* $*.c $(LIBPTHREAD)

$(PROGS):

clean:
	rm -f $(PROGS)

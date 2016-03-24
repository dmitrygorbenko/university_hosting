#include <stdio.h>
#include <stdlib.h>
#include <arpa/inet.h>
#include <netdb.h>
#include <string.h>
#include <signal.h>
#include <getopt.h>

#ifndef	SCANDNS_H
    #include "include/scandns.h"
#endif

#ifndef	MESSAGE
    #include "message.cpp"
#endif

int main(int argc, char **argv)
{
    struct hostent *host;
    static struct in_addr addr;
    int i;

    Set_Signal_Handler();
    PROGRAM = argv[0];

    Reset_Options();
    Parse_Param(argc,argv);

    if (ShowVersion || ShowHelp)
	exit(0);
    
    if (Silent && !LogToFile)
    {
        fprintf(stdout,"You don't need any result ? Please, read help\n");
	exit(0);
    }

    if (((argc - ParamCount) - 1) < 2)
    {
        fprintf(stdout,"Without any IP address ? Please, read help\n");
	exit(0);
    }

    Init_Message();
    
    if(CheckIP(argv[argc-2]))
    {
        fprintf(stdout,"Bad start IP address %s\n",argv[argc-2]);
        CloseLogFile();	
	exit(0);
    }

    if(CheckIP(argv[argc-1]))
    {
        fprintf(stdout,"Bad end IP address %s\n",argv[argc-1]);	
	CloseLogFile();
	exit(0);
    }

    SetIP(IPStart,argv[argc-2]);
    SetIP(IPEnd,argv[argc-1]);    

    Message("\nRunning scan DNS database for IP range:\nfrom ");
    Message(argv[argc-2]); Message(" to "); Message(argv[argc-1]);
    Message("\n\n");

// ===============================      MAIN   LOOP  =====================

    for(IPCurrent[0]=IPStart[0] ; IPCurrent[0]<=IPEnd[0] ; IPCurrent[0]++)
    for(IPCurrent[1]=IPStart[1] ; IPCurrent[1]<=IPEnd[1] ; IPCurrent[1]++)    
    for(IPCurrent[2]=IPStart[2] ; IPCurrent[2]<=IPEnd[2] ; IPCurrent[2]++)    
    for(IPCurrent[3]=IPStart[3] ; IPCurrent[3]<=IPEnd[3] ; IPCurrent[3]++)
    {
	address = inet_ip(IPCurrent);

        addr.s_addr = inet_addr(address);
        host = gethostbyaddr((char *)&addr, sizeof(struct in_addr), AF_INET);

        if (host == NULL)
	{
    	    if (h_errno == TRY_AGAIN)
	    {
	        if(AdvancedReport)
		{
		    Message("At "); Message(address);
		    Message(" DNS server error - try again\n");
		}

    		if(!Ignore)
        	{
		    Message("At "); Message(address);
		    Message(" DNS server error - try again\n");

		    CloseLogFile();
	    	    exit(1);
		}
            }

	    if (h_errno == NO_RECOVERY)
    	    {
	        if(AdvancedReport)
		{
		    Message("At "); Message(address);
		    Message(" DNS server error - no recovery\n");
		}

    		if(!Ignore)
        	{
		    Message("At "); Message(address);
		    Message(" DNS server error - no recovery\n");

		    CloseLogFile();
	    	    exit(1);
		}
    	    }

	    if (h_errno == NO_ADDRESS)
    	    {
	        if(AdvancedReport)
		{
		    Message(address);
		    Message(" didn't have IP address\n");
		}

    		if(!Ignore)
        	{
		    Message(address);
		    Message(" didn't have IP address\n");

		    CloseLogFile();
	    	    exit(1);
		}

    	    }

	    if (h_errno == HOST_NOT_FOUND)
    	    {
	        if(AdvancedReport)
		{
		    Message("Host "); Message(address);
		    Message(" not found\n");
		}
    	    }
	ResolveErrorCount++;
	}
        
	if (host != NULL)
        {
	    Message(address); Message("  ");
	    Message((char *) host->h_name); Message("\n");
	    ResolveCount++;
        }
    }

    Message("\nScan completed with:\n");
    Message(longtostr(ResolveCount)); Message(" hosts resolved\n");
    Message(longtostr(ResolveErrorCount)); Message(" errors\n");

    CloseLogFile();
    return(0);
};


void Reset_Options()
{
    ShowVersion = 0;
    ShowHelp = 0;
    AdvancedReport = 0;
    LogToFile = 0;
    AppendLog = 0;
    Ignore = 0;
    Silent = 0;    
    ResolveCount = 0;
    ResolveErrorCount = 0;
};

void PrintHelp()
{
fprintf(stdout,"Usage: %s [OPTIONS] IP_start IP_end\n"\
	    "OPTIONS:\n"\
	    "-a, --advanced		Advanced report\n"\
	    "-A, --append		Don't erase last report file\n"\
	    "-i, --ignore		Ignore DNS errors\n"\
	    "-h, --help		Show this help\n"\
	    "-l, --log <file name>	Report to file\n"\
	    "-s, --silent		Don't output to stdout. Use only with ` -l 'option\n"\
	    "-V, --version		Show version\n"\
	    ,PROGRAM);
};

void Parse_Param(int counter, char **values)
{
    int c;
    int digit_optind = 0;

    static struct option long_options[] = 
    {
        { "advanced", 0, 0, 'a' },	
        { "append", 0, 0, 'A' },	
        { "ignore", 0, 0, 'i' },	
	{ "help", 0, 0, 'h' },
        { "log", 1, 0, 'l' },
	{ "silent", 0, 0, 's' },	
        { "version", 0, 0, 'V' },
        { 0 , 0 , 0 , 0}
    };

    while (1) 
    {	
	int this_option_optind = optind ? optind : 1;
	int option_index = 0;

        c = getopt_long (counter, values,"aAihl:sV",long_options, &option_index);

	if(c == -1)
	    break;
        
	switch (c) 
	{
	case 0:	fprintf(stdout,"\nParameter %s", long_options[option_index].name);
	    if(optarg)
	    fprintf(stdout,"\n  with argument %s",optarg);
	    fprintf(stdout,"\n");
	break;

	case 'a':
	    AdvancedReport = 1;
	break;

	case 'A':
	    AppendLog = 1;
	break;

	case 'i':
	    Ignore = 1;
	break;

	case 'h':
	    PrintHelp();
	    ShowHelp = 1;
	break;

	case 'l':
	    LogToFile = 1;
	    Logfilename = strdup(optarg);
	break;

	case 's':
	    Silent = 1;
	break;

	case 'V':   
	    fprintf(stdout,"Version 0.1\n");       
	    ShowVersion = 1;
	break;

	case ':': // missing parameter
	    fprintf(stdout, "Missing parameter. Try ` --help' for more options.\n\n");
	    exit(0);
	break;

	case '?': // unknown option
	    fprintf(stdout, "Unknown option. Try ` --help' for more options.\n\n");
	    exit(0);
	break;
	}
    }
    ParamCount =optind - 1;
};

void Set_Signal_Handler(void)
{
   signal(SIGINT,   Signal_TERM);
   signal(SIGHUP,   Signal_TERM);
   signal(SIGTERM,  Signal_TERM);
};

void Signal_TERM(int sig)
{
    if (sig == SIGINT)
	Message("Caught CTRL+C signal... shutdown\n");

    Message("Close at "); Message(address); Message("\n");

    CloseLogFile();
    exit(1);	
};

char * inttostr(unsigned int z)
{
    int i;
    int n = 0;
    unsigned int last = z;
    char * dest;

    while(last) { last = last / 10; n++; }

    if( z == 0) 
    {  
	dest = new char[2];
	dest[0] = '0';
	dest[1] = 0;
    }
    else
    {
        dest = new char[n+1];

        for(i=0,last = z;i<n;i++,last/=10)
	    dest[n-i-1] = (char)(last % 10 + 48);
        dest[n] = 0;
    }
return dest;
};

char * longtostr(unsigned long z)
{
    int i;
    int n = 0;
    unsigned long last = z;
    char * dest;

    while(last) { last = last / 10; n++; }

    if( z == 0) 
    {  
	dest = new char[2];
	dest[0] = '0';
	dest[1] = 0;
    }
    else
    {
        dest = new char[n+1];

        for(i=0,last = z;i<n;i++,last/=10)
	    dest[n-i-1] = (char)(last % 10 + 48);
        dest[n] = 0;
    }
return dest;
};


char * inet_ip(unsigned int z[4])
{
    char * res = new char
    [16];
    char * tmp;
    int i,k,len;
    int pos = 0;
    
    for(i=0;i<4;i++)
    {
        tmp = inttostr(z[i]);
        len = strlen(tmp);
	for(k=0;k<len;k++)
	    res[pos++] = tmp[k];
	if(i != 3) res[pos++] = '.';
    }
    res[pos] = 0;
return res;
};


int CheckIP(char * ip)
{// check about "xxx.xxx.xxx.xxx"
    int i,k,pos,oldpos;
    int count,len;
    char byte[3];

    //	first, check about three '.'
    len = strlen(ip);    
    for(i=0,count=0;i<len;i++)
	if(ip[i] == '.') count++;

    if(count != 3) 
        return 1;
    //	check for 0 <= xxx <= 255
    pos = 0;
    for(i=0;i<4;i++)
    {
	oldpos = pos;
	for(k=0;k<3;k++) byte[k] = 0;	k = 0;
	while(ip[pos] != '.' && pos < len)   byte[k++] = ip[pos++];
	pos++;
	if (0 > atoi(byte) && atoi(byte) > 255)
	    return 1;
	
	if ((pos - oldpos) == 1)
	    return 1;
    }
    // check for '0'-'9' and '.'
    for(i=0;i<len;i++)
	if (!((int)ip[i] >= (int)'0' && (int)ip[i] <= (int)'9') && !(ip[i] == '.')) 
    	    return 1;

return 0;
};

void SetIP(unsigned int ip[4],char * address)
{
    int i,k,pos;
    int len;
    char byte[3];

    len = strlen(address);    

    pos = 0;
    for(i=0;i<4;i++)
    {
	for(k=0;k<3;k++) byte[k] = 0;	k = 0;
	while(address[pos] != '.' && pos < len)   byte[k++] = address[pos++];
	pos++;
	ip[i] = atoi(byte);
    }
};


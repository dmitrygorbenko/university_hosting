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

#define ROOT_COMPILE 0

#undef FALSE
#undef TRUE

#define TRUE 1
#define FALSE 0

// NEXT LINES SHOULD BE LOADED FROM CONFIG FILES

#define ID "KULLYKULLY"

#define CHILD_MAX_COUNT 100

#define ALLOW_FROM "172.16.212.200"

#define DNS_SOA_NS_SERVER "ns.hosting.ai."
#define DNS_SOA_EMAILSERVER "hostmaster.hosting.ai."

#define DNS_NS1_SERVER "ns.hosting.ai."
#define DNS_NS2_SERVER "ns2.hosting.ai."

#define DNS_MX1_SERVER "mx.hosting.ai."
#define DNS_MX2_SERVER "mx2.hosting.ai."

#define DNS_HOSTING_IP "172.16.212.200"

#define RNDC_PATH "/hosting/system/bind/sbin/rndc"
#define RNDC_COMMAND "reload"

//#define DNS_ZONE_FILE "/hosting/system/bind/etc/zones"
//#define DNS_DB_DIR "/hosting/system/bind/zones/"
#define DNS_ZONE_FILE "/home/bazil/hosting2/dns/zones"
#define DNS_DB_DIR "/home/bazil/hosting2/dns/"

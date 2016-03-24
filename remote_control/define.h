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

#define HOSTNAME_LENGHT 1024

//// NEXT LINES SHOULD BE LOADED FROM CONFIG FILE

#define CLIENT_DIR "/home/bazil/hosting2/clients"
#define APACHE_VHOST_DIR "/home/bazil/hosting2/clients"
#define HOSTING_ROOT "/hosting"
//#define CLIENT_DIR "/hosting/clients"
//#define APACHE_VHOST_DIR "/hosting/system/apache/conf/vhosts"

#define ID "GABBAGABBAKEY"
#define NS_ID "KULLYKULLY"

#define CHILD_MAX_COUNT 100

#define ALLOW_FROM "172.16.212.200"

#define EXIM_UID 20
#define COURIER_UID 20
#define PROFTPD_UID 22
#define APACHE_UID 21

#define HOSTING_GID 200

#define HOSTING_IP "172.16.212.200"
#define HOSTING_PORT "80"

#define PWD_UID_MIN 1000
#define PWD_GID_MIN 200

//#define PASSWD_PATH "/etc/passwd"
//#define SHADOW_PATH "/etc/shadow"
//#define GROUP_PATH "/etc/group"
#define SYS_ETC_PATH "/home/bazil/hosting2/etc"
#define PASSWD_PATH "/home/bazil/hosting2/etc/passwd"
#define SHADOW_PATH "/home/bazil/hosting2/etc/shadow"
#define GROUP_PATH "/home/bazil/hosting2/etc/group"

#define TAR_PATH "/usr/bin/tar"

#define MAILDIRMAKE_PATH "/hosting/system/courier/mail/bin/maildirmake"

#define APACHECTL_PATH "/etc/rc.d/rc.httpd"
#define APACHECTL_COMMAND "restart"
#define APACHEHTPASSWD_PATH "/hosting/system/apache/bin/htpasswd"

#define WEBALIZER_PATH "/hosting/system/webalizer/bin/webalizer"

#define HOSTINGRC_PATH "/hosting/rc.d/rc.hosting"

#define STAT_DIR "stat"


create DATABASE hosting2;

use hosting2;

DROP TABLE area_protect_table;
DROP TABLE area_users_table;
DROP TABLE area_groups_table;
DROP TABLE mysql_table;
DROP TABLE pgsql_table;
DROP TABLE ftp_table;
DROP TABLE mail_folder_table;
DROP TABLE mail_book_table;
DROP TABLE mail_pref_table;
DROP TABLE mail_table;
DROP TABLE mail_maillist_table;
DROP TABLE mail_autoreply_table;
DROP TABLE mail_alias_table;
DROP TABLE mail_forward_table;
DROP TABLE webdir_table;
DROP TABLE redirect_table;
DROP TABLE subdomain_table;
DROP TABLE zone_service_table;
DROP TABLE candidate_client_table;
DROP TABLE zone_table;
DROP TABLE client_table;
DROP TABLE admin_table;
DROP TABLE our_zone_subdomain_table;
DROP TABLE our_zone_table;

CREATE TABLE our_zone_table (
id_table SERIAL PRIMARY KEY,
name varchar (255) NOT NULL);

CREATE TABLE our_zone_subdomain_table (
id_table SERIAL PRIMARY KEY,
id_our_zone_table integer REFERENCES our_zone_table (id_table) ON DELETE CASCADE,
f_name varchar (255) NOT NULL,
name varchar (255) NOT NULL,
type VARCHAR(20) NOT NULL
	CHECK (type IN ('NS', 'MX', 'A', 'CNAME')),
prior integer DEFAULT 10,
record varchar(255) NOT NULL);

CREATE TABLE admin_table (
id_table SERIAL PRIMARY KEY,
login varchar(255) NOT NULL,
passwd varchar (255) NOT NULL );

CREATE TABLE client_table (
id_table SERIAL PRIMARY KEY,
active BOOLEAN DEFAULT '0',
login varchar(255) NOT NULL,
passwd varchar (255) NOT NULL,
email varchar (255) NOT NULL,
firstname varchar(255) NOT NULL,
lastname varchar(255),
company varchar (255),
country varchar (255),
region varchar (255),
postal varchar (255),
city varchar (255),
address varchar (255),
phone varchar (20),
fax varchar (20),
add_info text,
create_time timestamp );

CREATE TABLE zone_table (
id_table SERIAL PRIMARY KEY,
id_client_table integer REFERENCES client_table (id_table) ON DELETE CASCADE,
name varchar (255) NOT NULL,
zone_type VARCHAR(20) NOT NULL
	CHECK (zone_type IN ('our', 'parked', 'register')),
id_our_zone_table integer DEFAULT 0,
service_type varchar (255),
uid integer DEFAULT 65534,
gid integer DEFAULT 65534 );

CREATE TABLE candidate_client_table (
id_table SERIAL PRIMARY KEY,
fdname varchar (255) NOT NULL,
zone_type VARCHAR(20) NOT NULL
	CHECK (zone_type IN ('register')),
login varchar(255) NOT NULL,
passwd varchar (255) NOT NULL,
email varchar (255) NOT NULL,
firstname varchar(255) NOT NULL,
lastname varchar(255),
company varchar (255),
country varchar (255),
region varchar (255),
postal varchar (255),
city varchar (255),
address varchar (255),
phone varchar (20),
fax varchar (20),
service_type varchar (255),
add_info text,
query_time timestamp );

CREATE TABLE zone_service_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
disk_space FLOAT DEFAULT '10485760',
subdomain_max_count integer DEFAULT '5',
email_max_count integer DEFAULT '5',
email_alias_max_count integer DEFAULT '50',
email_reply_max_count integer DEFAULT '20',
ftp_max_count integer DEFAULT '5',
mysql_max_count integer DEFAULT '5',
pgsql_max_count integer DEFAULT '5',
cp BOOLEAN DEFAULT '1',
ftp_access BOOLEAN DEFAULT '1',
popimap_access BOOLEAN DEFAULT '1',
smtp_access BOOLEAN DEFAULT '1',
php BOOLEAN DEFAULT '1',
cgi_perl BOOLEAN DEFAULT '0',
ssi BOOLEAN DEFAULT '0',
mysql BOOLEAN DEFAULT '1',
pgsql BOOLEAN DEFAULT '0',
stat BOOLEAN DEFAULT '0',
error_pages BOOLEAN DEFAULT '1',
secure_dir BOOLEAN DEFAULT '0',
support BOOLEAN DEFAULT '1',
backup BOOLEAN DEFAULT '0');

CREATE TABLE subdomain_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
f_name varchar (255) NOT NULL,
name varchar (255) NOT NULL,
type VARCHAR(20) NOT NULL
	CHECK (type IN ('NS', 'MX', 'A', 'CNAME')),
prior integer DEFAULT 10,
record varchar(255) NOT NULL);

CREATE TABLE redirect_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
dummy integer DEFAULT 0,
domain varchar (255) NOT NULL,
pointer varchar(255),
frameset BOOLEAN DEFAULT '0');

CREATE TABLE webdir_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
dummy integer DEFAULT 0,
domain_lite varchar (255) NOT NULL,
domain varchar (255) NOT NULL,
rootdir varchar(255));

CREATE TABLE mail_forward_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
dummy integer DEFAULT 0,
email varchar(255) NOT NULL,
forward_address varchar(255) NOT NULL,
forward_do BOOLEAN DEFAULT '0');

CREATE TABLE mail_alias_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
dummy integer DEFAULT 0,
email varchar(255) NOT NULL,
alias varchar(255) NOT NULL);

CREATE TABLE mail_autoreply_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
dummy integer DEFAULT 0,
email varchar(255) NOT NULL,
reply text,
reply_do BOOLEAN DEFAULT '0');

CREATE TABLE mailing_list_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
dummy integer DEFAULT 0,
title varchar(255) NOT NULL,
list_member text NOT NULL );

CREATE TABLE mail_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
dummy integer DEFAULT 0,
domain varchar(255) NOT NULL,
login varchar(255) NOT NULL,
passwd varchar(255) NOT NULL,
name varchar(255) DEFAULT '',
uid integer DEFAULT 65534,
gid integer DEFAULT 65534,
home varchar(255) NOT NULL,
maildir varchar(255) NOT NULL);

CREATE TABLE mail_pref_table (
id_table SERIAL PRIMARY KEY,
id_mail_table integer REFERENCES mail_table (id_table) ON DELETE CASCADE,
reply_email varchar(255) NOT NULL,
my_sign text,
timezone varchar(5) DEFAULT '+0200',
rpp integer DEFAULT 10,
refresh_time integer DEFAULT 10,
change_html_tags integer DEFAULT 1,
save_html_links integer DEFAULT 0,
clean_trash integer DEFAULT 0,
add_sign integer DEFAULT 0,
save_to_sent integer DEFAULT 0,
save_to_trash integer DEFAULT 1,
save_only_seen integer DEFAULT 1 );

CREATE TABLE mail_book_table (
id_table SERIAL PRIMARY KEY,
id_mail_table integer REFERENCES mail_table (id_table) ON DELETE CASCADE,
name varchar(255) NOT NULL,
email varchar(255) NOT NULL,
address varchar(255),
city varchar(255),
region varchar(255),
work_place varchar(255) );

CREATE TABLE mail_folder_table (
id_table SERIAL PRIMARY KEY,
id_mail_table integer REFERENCES mail_table (id_table) ON DELETE CASCADE,
name varchar(255) NOT NULL,
title varchar(255) NOT NULL );

CREATE TABLE ftp_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
dummy integer DEFAULT 0,
login varchar(255) NOT NULL,
passwd varchar(255) NOT NULL,
rootdir varchar(255) NOT NULL,
uid integer DEFAULT 65534,
gid integer DEFAULT 65534 );

CREATE TABLE mysql_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
dummy integer DEFAULT 0,
db_name varchar(255) NOT NULL,
size_quota INT8 NOT NULL);

CREATE TABLE pgsql_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
db_name varchar(255) NOT NULL,
size_quota INT8 NOT NULL);

CREATE TABLE area_groups_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
name varchar(255) NOT NULL);

CREATE TABLE area_users_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
id_area_groups_table integer,
name varchar(255) NOT NULL,
passwd varchar(255) NOT NULL);

CREATE TABLE area_protect_table (
id_table SERIAL PRIMARY KEY,
id_zone_table integer REFERENCES zone_table (id_table) ON DELETE CASCADE,
id_area_user_table integer REFERENCES area_users_table (id_table) ON DELETE CASCADE,
id_area_group_table integer REFERENCES area_groups_table (id_table) ON DELETE CASCADE,
title varchar(255) NOT NULL,
method_type VARCHAR(5) NOT NULL
	CHECK (method_type IN ('user', 'group')),
item_type VARCHAR(4) NOT NULL
	CHECK (item_type IN ('file', 'dir')),
item varchar(1024) NOT NULL);

INSERT INTO our_zone_table(name) VALUES ('hosting.ai');
INSERT INTO our_zone_table(name) VALUES ('deep.lan');
INSERT INTO our_zone_table(name) VALUES ('link.lan');

insert into admin_table(login, passwd) values('bazil', '$1$YUAYtr6u$OZov2uZ2/fkgUrPw0xFtc.');
insert into admin_table(login, passwd) values('bo', '$1$dzK1VP/o$yYilbeS8mkRmohewJT40w/');
insert into admin_table(login, passwd) values('kulik', '$1$OSgQ2.Xn$EMiEXG7jM3r5ARyku5VQx0');

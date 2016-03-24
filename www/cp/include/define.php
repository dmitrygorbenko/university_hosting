<?php

//**********************************************
//			GLOBAL
//**********************************************

$disable_mcrypt = FALSE;
$key = "OR WHAT YOU WANT";
$pref_ciphers = array ("rijndael-256", "tripledes", "blowfish", "des");

$trans = array(">" => "&#062;", "<" => "&#060;", "\"" => "&#034;", "'" => "&#039;", "\$" => "&#036;", "\\" => "&#092;");
$login_trans = array("." => "_");

$MySQL_Login_Max = 16;

$warn_color = array (
	"info" => "#000000",
	"minor" => "#000055",
	"medium" => "#555599",
	"major" => "#AA0000"
);

//**********************************************
//	FTP, EMAIL, DNS... just SYSTEM
//**********************************************

$HOSTING_IP = "172.16.212.200";

$MAIL_UID = "20";
$APACHE_UID = "21";
$FTP_UID = "22";
$HOSTING_GID = "200";

//$Clients_dir = "/hosting/clients";
$Clients_dir = "/home/bazil/hosting2/clients";
$Alone_dir = "Alone";

$remote_server_login = "GABBAGABBAKEY";
$allow_nxdomain_parked = FALSE;

$Hosting_Service_Type = Array(
	"free" => Array(
		"disk_space" => "10485760",
		"subdomain_max_count" => "5",
		"email_max_count" => "5",
		"email_alias_max_count" => "50",
		"email_reply_max_count" => "20",
		"ftp_max_count" => "5",
		"mysql_max_count" => "1",
		"pgsql_max_count" => "0",
		"cp" => "1",
		"ftp_access" => "1",
		"popimap_access" => "1",
		"smtp_access" => "1",
		"php" => "1",
		"cgi_perl" => "0",
		"ssi" => "0",
		"mysql" => "1",
		"pgsql" => "0",
		"stat" => "0",
		"error_pages" => "1",
		"secure_dir" => "0",
		"support" => "1",
		"backup" => "0"
	),
	"full" => Array(
		"disk_space" => "26214400",
		"subdomain_max_count" => "0",
		"email_max_count" => "5",
		"email_alias_max_count" => "0",
		"email_reply_max_count" => "0",
		"ftp_max_count" => "0",
		"mysql_max_count" => "5",
		"pgsql_max_count" => "5",
		"cp" => "1",
		"ftp_access" => "1",
		"popimap_access" => "1",
		"smtp_access" => "1",
		"php" => "1",
		"cgi_perl" => "1",
		"ssi" => "1",
		"mysql" => "1",
		"pgsql" => "1",
		"stat" => "1",
		"error_pages" => "1",
		"secure_dir" => "1",
		"support" => "1",
		"backup" => "1"
	)
);

?>
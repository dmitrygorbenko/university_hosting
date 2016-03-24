<?php
	require_once ("header.php");

function print_menu()
{
	$menu = Array (
		Array ("Server", "Server Setup", "server_status.php", "general", "body", Array (
			Array("Server:status", "Server Status", "server_status.php", "body"),
			Array("Server:root_password", "Change Root Password", "server_root_passwd.php", "body"),
			)
		),
		Array ("Service", "Service Manage", "service_manage.php", "general", "body", Array (
			Array("Service:restart", "Services Restart", "service_restart.php", "body")
/*
			Array("Service:apache", "Apache setup", "service_apache.php", "body"),
			Array("Service:php", "PHP setup", "service_php.php", "body"),
			Array("Service:bind", "BIND setup", "service_bind.php", "body"),
			Array("Service:ftp", "FTP configuration", "service_ftp.php", "body"),
			Array("Service:exim", "Exim configuration", "service_exim.php", "body"),
			Array("Service:postgresql", "PostgreSQL setup", "service_pgsql.php", "body"),
			Array("Service:mysql", "MySQL setup", "service_mysql.php", "body"),
			Array("Service:imap", "IMAP configuration", "service_imap.php", "body"),
			Array("Service:pop3", "POP3 configuration", "service_pop3.php", "body"),
			Array("Service:auth", "AUTH configuration", "service_auth.php", "body"),
			Array("Service:remote", "Remote setup", "service_remote.php", "body")
*/
			)
		),
		Array ("Hosting", "Hosting Status", "overview.php", "general", "body", Array (
			Array("Hosting:overview", "Hosting Status", "overview.php", "body"),
			Array("Hosting:services", "Hosting Services", "services.php", "body")
			)
		),
		Array ("Clients", "Hosting Clients", "client.php", "support", "body", Array (
			Array("Clients:View", "Show Hosting Clients", "client.php", "body"),
			Array("Clients:View", "Show Hosting Candidates", "candidate.php", "body"),
			Array("Clients:Create", "Create Hosting Client", "client_add.php", "body")
			)
		),
		Array ("Domain and Zones", "Manage Domains and Zones", "zone.php", "domain", "body", Array (
			Array("Domain:HostingZones", "Show Hosting Zones", "zone.php", "body"),
			Array("Domain:OurZones", "Show Our Zones", "our_zone.php", "body"),
			Array("WebRedirect:View", "Show Web Redirectors", "redirect.php", "body"),
			Array("WebDir:View", "Show Web Dirs", "webdir.php", "body"),
			Array("Domain:CreateHostingZones", "Create Hosting Zones", "zone_add.php", "body"),
			Array("Domain:OurCreateZone", "Create Our Zone", "our_zone_add.php", "body"),
			Array("WebRedirect:Create", "Create Web Redirector", "redirect_add.php", "body"),
			Array("WebDir:Create", "Create Web Dir", "webdir_add.php", "body"),
			Array("Domain:ManageHostingZonesDomains", "Manage Hosting Zone's Domains", "zone_manage.php", "body"),
			Array("Domain:ManageOurZonesDomains", "Manage Our Zone's Domains", "our_zone_manage.php", "body")
			)
		),
		Array ("Email", "E-mail", "mail.php", "new_mail", "body", Array (
			Array("Email:View", "Show E-mails", "mail.php", "body"),
			Array("Email:ViewForward", "Show E-mail Forwarders", "mail_forwarder.php", "body"),
			Array("Email:ViewAutoAnswer", "Show E-mail Auto Answer", "mail_autoreply.php", "body"),
			Array("Email:ViewAlias", "Show E-mail Aliases", "mail_alias.php", "body"),
			Array("Email:ViewLists", "Show Mailing Lists", "maillist.php", "body"),
			Array("Email:Create", "Create E-mail", "mail_add.php", "body"),
			Array("Email:CreateForward", "Create E-mail Forward", "mail_forwarder_add.php", "body"),
			Array("Email:CreateAutoAnswer", "Create E-mail Auto Answer", "mail_autoreply_add.php", "body"),
			Array("Email:CreateAlias", "Create E-mail Alias", "mail_alias_add.php", "body"),
			Array("Email:CreateList", "Create Mailing List", "maillist_add.php", "body")
			)
		),
		Array ("FTP", "FTP", "ftp.php", "ftp", "body", Array (
			Array("FTP:View", "Show FTP Accounts", "ftp.php", "body"),
			Array("FTP:Create", "Create FTP Account", "ftp_add.php", "body")
			)
		),
		Array ("Database", "MySQL & PostgreSQL", "database.php", "support", "body", Array (
			Array("Database:MySQLView", "Show MySQL Databases", "mysql_db.php", "body"),
			Array("Database:PgSQLView", "Show PostgreSQL Databases", "pgsql_db.php", "body"),
			Array("Database:MySQLCreate", "Create MySQL Database", "mysql_db_add.php", "body"),
			Array("Database:PgSQLCreate", "Create PostgreSQL Database", "pgsql_db_add.php", "body")
			)
		),
		Array ("FTP", "TODO", "todo.php", "ftp", "body"),
		Array ("LOGOUT", "Logout", "logout.php", "logout", "_top")
	);

echo "
	<table width=\"236px\" border=\"0\" align=\"left\" cellpadding=\"0\" cellspacing=\"0\" bgcolor=\"#F5F5F5\">";

	for ($i = 0; $i < count($menu); $i++) {
		echo "
			<!-- ".$menu[$i][0]." -->
			<tr class=\"menu\" height=\"36px\"  width=\"236px\">
				<td class=\"menu\" height=\"36px\" width=\"36px\" align=\"left\" background=\"../img/new/item_major.jpg\">
					<a href=\"".$menu[$i][2]."\" target=\"".$menu[$i][4]."\">
						<img class=\"menu_img\" height=\"36px\" width=\"36px\" src=\"../img/menu/".$menu[$i][3]."_a.gif\" name=\"".$menu[$i][3]."\" id=\"".$menu[$i][3]."\">
					</a>
				</td>
				<td class=\"menu\" height=\"36px\" width=\"200px\" background=\"../img/new/item_major.jpg\">
					<a class=\"menu\" href=\"".$menu[$i][2]."\" target=\"".$menu[$i][4]."\">
						".$menu[$i][1]."
					</a>
				</td>
			</tr>
			";

		if (isset($menu[$i][5]))
			for ($k = 0; $k < count($menu[$i][5]); $k++)
				echo "
				<!-- ".$menu[$i][5][$k][0]." -->
				<tr width=\"236px\">
					<td colspan=\"2\" width=\"236px\" background=\"../img/menu/open_background.jpg\">
						<a class=\"submenu\" href=\"".$menu[$i][5][$k][2]."\" target=\"".$menu[$i][5][$k][3]."\">
							".$menu[$i][5][$k][1]."
						</a>
					</td>
				</tr>
				";
	}
	echo "</table>";
};

	html_header("menu");

	print_menu();

	html_end();

?>

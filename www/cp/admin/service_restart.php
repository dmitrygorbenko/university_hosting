<?php
	require_once ("header.php");

	function check_proc($services) {

		exec("ps ax", $res);

		for ($i = 0; $i < count($res); $i++)
			$fres .= $res[$i]." ";

		for ($i = 0; $i < count($services); $i++) {
			if (strstr($fres, $services[$i]["proc"]))
				$services[$i]["on-line"] = TRUE;
			else
				$services[$i]["on-line"] = FALSE;
		}

		return $services;
	}

	function body_page() {

	$services = array();

	$service["name"] = "Apache";
	$service["proc"] = "/hosting/system/apache/bin/httpd";
	$service["service"] = "apache";
	array_push($services, $service);

	$service["name"] = "BIND";
	$service["proc"] = "/hosting/system/bind/sbin/named";
	$service["service"] = "bind";
	array_push($services, $service);

	$service["name"] = "MySQL";
	$service["proc"] = "/usr/local/mysql/bin/mysqld";
	$service["service"] = "mysql";
	array_push($services, $service);

	$service["name"] = "PostgreSQL";
	$service["proc"] = "/usr/local/pgsql/bin/postmaster";
	$service["service"] = "pgsql";
	array_push($services, $service);

	$service["name"] = "Exim";
	$service["proc"] = "/hosting/system/exim/bin/exim";
	$service["service"] = "exim";
	array_push($services, $service);

	$service["name"] = "Auth Daemon";
	$service["proc"] = "/hosting/system/courier/auth/libexec/courier-authlib/authdaemond";
	$service["service"] = "auth";
	array_push($services, $service);

	$service["name"] = "IMAP Daemon";
	$service["proc"] = "/hosting/system/courier/mail/bin/imapd";
	$service["service"] = "imap";
	array_push($services, $service);

	$service["name"] = "POP3 Daemon";
	$service["proc"] = "/hosting/system/courier/mail/libexec/courier/courierpop3d";
	$service["service"] = "pop";
	array_push($services, $service);

	$service["name"] = "ProFTPD";
	$service["proc"] = "proftpd";
	$service["service"] = "proftpd";
	array_push($services, $service);

	$service["name"] = "Remote Control";
	$service["proc"] = "/home/bazil/hosting/remote_control/src/remote_control";
	$service["service"] = "remote_control";
	array_push($services, $service);

	$service["name"] = "NS Control";
	$service["proc"] = "/home/bazil/hosting/ns_control/src/ns_control";
	$service["service"] = "ns_control";
	array_push($services, $service);

	$services = check_proc($services);

	$k = 0;
	for ($i = 0; $i < count($services); $i++)
		if ($services[$i]["on-line"])
			$k++;

	if ($k == count($services))
		$service["on-line"] = TRUE;
	else
		$service["on-line"] = FALSE;

	$service["name"] = "Whole VHS System";
	$service["service"] = "all";
	array_push($services, $service);

		echo "
			<form action=\"\" method=\"POST\" name=\"service_manage\">
			<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\" width=\"100%\">
			<tr>
				<td colspan=\"2\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Restart Service
						</td>
						<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
							<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>

			<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\" width=\"60%\">
			<tr>
				<td class=\"content\" colspan=\"3\">
					Do NOT play with this ability !!!
				</td>
			</tr>
			<tr align=\"left\">
				<td class=\"content2\" width=\"38%\">
					Service:
				</td>
				<td class=\"content2\" width=\"23%\">
					Status
				</td>
				<td class=\"content2\" width=\"38%\">
					Action
				</td>
			</tr>
		";

	for ($i = 0; $i < count($services)-1; $i++)
		echo "
			<tr align=\"left\">
				<td class=\"content_3\">
					".$services[$i]["name"].":
				</td>
				<td class=\"content_3_center\">
					".($services[$i]["on-line"]?"<font color=\"#00aa00\">Up</font>":"<font color=\"#aa0000\">Down</font>")."
				</td>
				<td class=\"content_3_right\">
					".($services[$i]["on-line"]?"Start":"<a href=\"service_action.php?act=start&service=".$services[$i]["service"]."\">Start</a>")."
					&nbsp;&nbsp;&nbsp;&nbsp;
					".($services[$i]["on-line"]?"<a href=\"service_action.php?act=stop&service=".$services[$i]["service"]."\">Stop</a>":"Stop")."
					&nbsp;&nbsp;&nbsp;&nbsp;
					".($services[$i]["on-line"]?"<a href=\"service_action.php?act=restart&service=".$services[$i]["service"]."\">Restart</a>":"Restart")."
				</td>
			</tr>
		";

		echo "
			<tr align=\"left\">
				<td class=\"content_3\">
					".$services[$i]["name"].":
				</td>
				<td class=\"content_3_center\">
					".($services[$i]["on-line"]?"<font color=\"#00aa00\">Up</font>":"<font color=\"#aa0000\">Broken</font>")."
				</td>
				<td class=\"content_3_right\">
					Start
					&nbsp;&nbsp;&nbsp;&nbsp;
					<a href=\"service_action.php?act=stop&service=".$services[$i]["service"]."\">Stop</a>
					&nbsp;&nbsp;&nbsp;&nbsp;
					<a href=\"service_action.php?act=restart&service=".$services[$i]["service"]."\">Restart</a>
				</td>
			</tr>
			</table>
		";
	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

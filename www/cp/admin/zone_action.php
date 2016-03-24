<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage();

	$act = safe_get("act", "GET", 255);

	if ($act == "add_zone") {

		$client = safe_get("client", "POST", 255);
		$zone_name = safe_get("zone_name", "POST", 255);
		$zone_type = safe_get("zone_type", "POST", 15);
		$service_type = safe_get("service_type", "POST", 15);

		$disk_space = safe_get("disk_space", "POST", 20);
		$subdomain_max_count = safe_get("subdomain_max_count", "POST", 10);
		$email_max_count = safe_get("email_max_count", "POST", 10);
		$email_alias_max_count = safe_get("email_alias_max_count", "POST", 10);
		$email_reply_max_count = safe_get("email_reply_max_count", "POST", 10);
		$ftp_max_count = safe_get("ftp_max_count", "POST", 10);
		$mysql_max_count = safe_get("mysql_max_count", "POST", 10);
		$pgsql_max_count = safe_get("pgsql_max_count", "POST", 10);
		$cp = safe_get("cp", "POST", 5);
		$ftp_access = safe_get("ftp_access", "POST", 5);
		$popimap_access = safe_get("popimap_access", "POST", 5);
		$smtp_access = safe_get("smtp_access", "POST", 5);
		$php = safe_get("php", "POST", 5);
		$cgi_perl = safe_get("cgi_perl", "POST", 5);
		$ssi = safe_get("ssi", "POST", 5);
		$mysql = safe_get("mysql", "POST", 5);
		$pgsql = safe_get("pgsql", "POST", 5);
		$stat = safe_get("stat", "POST", 5);
		$error_pages = safe_get("error_pages", "POST", 5);
		$secure_dir = safe_get("secure_dir", "POST", 5);
		$support = safe_get("support", "POST", 5);
		$backup = safe_get("backup", "POST", 5);

		$go_back =  "<html>
		<head>
		<title>Hosting.ai: Admin Panel</title>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">
		</head>
		<body bgcolor=\"EEEEEE\">
		<form name=\"zone\" method=\"POST\" action=\"zone_add.php\">
		<input type=\"hidden\" name=\"client\" value=\"".$client."\">
		<input type=\"hidden\" name=\"zone_name\" value=\"".$zone_name."\">
		<input type=\"hidden\" name=\"zone_type\" value=\"".$zone_type."\">
		<input type=\"hidden\" name=\"service_type\" value=\"".$service_type."\">
		<input type=\"hidden\" name=\"disk_space\" value=\"".$disk_space."\">
		<input type=\"hidden\" name=\"subdomain_max_count\" value=\"".$subdomain_max_count."\">
		<input type=\"hidden\" name=\"email_max_count\" value=\"".$email_max_count."\">
		<input type=\"hidden\" name=\"email_alias_max_count\" value=\"".$email_alias_max_count."\">
		<input type=\"hidden\" name=\"email_reply_max_count\" value=\"".$email_reply_max_count."\">
		<input type=\"hidden\" name=\"ftp_max_count\" value=\"".$ftp_max_count."\">
		<input type=\"hidden\" name=\"mysql_max_count\" value=\"".$mysql_max_count."\">
		<input type=\"hidden\" name=\"pgsql_max_count\" value=\"".$pgsql_max_count."\">
		<input type=\"hidden\" name=\"cp\" value=\"".$cp."\">
		<input type=\"hidden\" name=\"ftp_access\" value=\"".$ftp_access."\">
		<input type=\"hidden\" name=\"popimap_access\" value=\"".$popimap_access."\">
		<input type=\"hidden\" name=\"smtp_access\" value=\"".$smtp_access."\">
		<input type=\"hidden\" name=\"php\" value=\"".$php."\">
		<input type=\"hidden\" name=\"cgi_perl\" value=\"".$cgi_perl."\">
		<input type=\"hidden\" name=\"ssi\" value=\"".$ssi."\">
		<input type=\"hidden\" name=\"mysql\" value=\"".$mysql."\">
		<input type=\"hidden\" name=\"pgsql\" value=\"".$pgsql."\">
		<input type=\"hidden\" name=\"stat\" value=\"".$stat."\">
		<input type=\"hidden\" name=\"error_pages\" value=\"".$error_pages."\">
		<input type=\"hidden\" name=\"secure_dir\" value=\"".$secure_dir."\">
		<input type=\"hidden\" name=\"support\" value=\"".$support."\">
		<input type=\"hidden\" name=\"support\" value=\"".$support."\">
		<input type=\"hidden\" name=\"backup\" value=\"".$backup."\">
		<script language=\"javascript\">
			document.zone.submit();
		</script>
		<noscript>
			You don't have JavaScript to be enabled. Please, push this button:<br>
			<input type=\"submit\">
		</noscript>
		</form>";

		if (	$client != "" &&
			$zone_name != "") {

			if ($HM->ZC->add_zone($client, $zone_name, $zone_type, $service_type, $disk_space, $subdomain_max_count, $email_max_count, $email_alias_max_count, $email_reply_max_count, $ftp_max_count, $mysql_max_count, $pgsql_max_count, $cp, $ftp_access, $popimap_access, $smtp_access, $php, $cgi_perl, $ssi, $mysql, $pgsql, $stat, $error_pages, $secure_dir, $support, $backup) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't create zone: ".$_SESSION["adminpanel_error"];
				echo $go_back;
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			echo $go_back;
			exit;
		}

		Header("Location: zone.php\n\n");
		exit;
	}

	elseif ($act == "change_zone") {

		$id = safe_get("id", "POST", 255);

		$zone_data["zone_name"] = safe_get("zone_name", "POST", 255);
		$zone_data["zone_type"] = safe_get("zone_type", "POST", 15);
		$zone_data["uid"] = safe_get("uid", "POST", 255);
		$zone_data["gid"] = safe_get("gid", "POST", 255);
		$zone_data["service_type"] = safe_get("service_type", "POST", 15);
		$zone_data["disk_space"] = safe_get("disk_space", "POST", 20);
		$zone_data["subdomain_max_count"] = safe_get("subdomain_max_count", "POST", 10);
		$zone_data["email_max_count"] = safe_get("email_max_count", "POST", 10);
		$zone_data["email_alias_max_count"] = safe_get("email_alias_max_count", "POST", 10);
		$zone_data["email_reply_max_count"] = safe_get("email_reply_max_count", "POST", 10);
		$zone_data["ftp_max_count"] = safe_get("ftp_max_count", "POST", 10);
		$zone_data["mysql_max_count"] = safe_get("mysql_max_count", "POST", 10);
		$zone_data["pgsql_max_count"] = safe_get("pgsql_max_count", "POST", 10);
		$zone_data["cp"] = safe_get("cp", "POST", 5);
		$zone_data["ftp_access"] = safe_get("ftp_access", "POST", 5);
		$zone_data["popimap_access"] = safe_get("popimap_access", "POST", 5);
		$zone_data["smtp_access"] = safe_get("smtp_access", "POST", 5);
		$zone_data["php"] = safe_get("php", "POST", 5);
		$zone_data["cgi_perl"] = safe_get("cgi_perl", "POST", 5);
		$zone_data["ssi"] = safe_get("ssi", "POST", 5);
		$zone_data["mysql"] = safe_get("mysql", "POST", 5);
		$zone_data["pgsql"] = safe_get("pgsql", "POST", 5);
		$zone_data["stat"] = safe_get("stat", "POST", 5);
		$zone_data["error_pages"] = safe_get("error_pages", "POST", 5);
		$zone_data["secure_dir"] = safe_get("secure_dir", "POST", 5);
		$zone_data["support"] = safe_get("support", "POST", 5);
		$zone_data["backup"] = safe_get("backup", "POST", 5);

		if (	$id != "" &&
			$zone_data["zone_name"] != "" &&
			$zone_data["zone_type"] != "" &&
			$zone_data["uid"] != "" &&
			$zone_data["gid"] != "" &&
			$zone_data["service_type"] != "" &&
			$zone_data["disk_space"] != "" &&
			$zone_data["subdomain_max_count"] != "" &&
			$zone_data["email_max_count"] != "" &&
			$zone_data["email_alias_max_count"] != "" &&
			$zone_data["email_reply_max_count"] != "" &&
			$zone_data["ftp_max_count"] != "" &&
			$zone_data["mysql_max_count"] != "" &&
			$zone_data["pgsql_max_count"] != "") {

			if ($HM->ZC->change_zone($id, $zone_data) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't change zone: ".$_SESSION["adminpanel_error"];
				Header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
				exit;
			}

		}
		else {
			$_SESSION["adminpanel_error"] = "Not enought data";
			Header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
			exit;
		}

		Header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
		exit;

	}

	elseif ($act == "remove") {

		$ids = safe_get("ids", "POST");

		if ($ids == "") {
			$_SESSION["adminpanel_error"] = "Error - did not specified id's parameter";
			Header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
			exit;
		}

		$id_separate = explode(":", $ids);

		for($i=0; $i<count($id_separate); $i++) {
			$id = trim($id_separate[$i]);
			if ($id != "") {
				if ($HM->ZC->remove_zone($id) != TRUE) {
					$_SESSION["adminpanel_error"] = "Can't remove zone: ".$_SESSION["adminpanel_error"];
					Header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
					exit;
				}
			}
		}

		Header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
		exit;
	}

	$_SESSION["adminpanel_error"] = "Unknown action";
	Header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
	exit;
?>

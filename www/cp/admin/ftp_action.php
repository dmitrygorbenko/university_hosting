<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage();

	$act = safe_get("act", "GET", 255);

	if ($act == "add_ftp") {

		$login = safe_get("login", "POST", 255);
		$id_zone = safe_get("id_zone", "POST", 255);
		$password = safe_get("password", "POST", 255);
		$password2 = safe_get("password2", "POST", 255);
		$rootdir = safe_get("rootdir", "POST", 255);
		$uid = safe_get("uid", "POST", 255);
		$gid = safe_get("gid", "POST", 255);

		$go_back =  "<html>
		<head>
		<title>Hosting.ai: Admin Panel</title>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">
		</head>
		<body bgcolor=\"EEEEEE\">
		<form name=\"ftp\" method=\"POST\" action=\"ftp_add.php\">
		<input type=\"hidden\" name=\"login\" value=\"".$login."\">
		<input type=\"hidden\" name=\"id_zone\" value=\"".$id_zone."\">
		<input type=\"hidden\" name=\"rootdir\" value=\"".$rootdir."\">
		<input type=\"hidden\" name=\"uid\" value=\"".$uid."\">
		<input type=\"hidden\" name=\"gid\" value=\"".$gid."\">
		<script language=\"javascript\">
			document.ftp.submit();
		</script>
		<noscript>
			You don't have JavaScript to be enabled. Please, push this button:<br>
			<input type=\"submit\">
		</noscript>
		</form>";

		if ($password !== $password2) {
			$_SESSION["adminpanel_error"] = "Passwords are not the same";
			echo $go_back;
			exit;
		}

		if (	$login != "" &&
			$id_zone != "" &&
			$password != "") {

			$passwd = crypt($password);

			if ($HM->FC->add_ftp_account($login, $id_zone, $passwd, $rootdir, $uid, $gid) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't create ftp user: ".$_SESSION["adminpanel_error"];
				echo $go_back;
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			echo $go_back;
			exit;
		}

		Header("Location: ftp.php\n\n");
		exit;
	}

	elseif ($act == "change_ftp") {

		$id = safe_get("id", "POST", 255);
		$login = safe_get("login", "POST", 255);
		$id_zone = safe_get("id_zone", "POST", 255);
		$password = safe_get("password", "POST", 255);
		$password2 = safe_get("password2", "POST", 255);
		$rootdir = safe_get("rootdir", "POST", 255);
		$uid = safe_get("uid", "POST", 255);
		$gid = safe_get("gid", "POST", 255);

		if ($password !== $password2) {
			$_SESSION["adminpanel_error"] = "Passwords are not the same";
			Header("Location: ftp_change.php?id=".$id."\n\n");
			exit;
		}

		if ($id != "" &&
			$login != "" &&
			$id_zone != "") {

			if ($password != "") {
				$passwd = crypt($password);
			}
			else
				$passwd = "";

			if ($HM->FC->change_ftp_account($id, $login, $id_zone, $passwd, $rootdir, $uid, $gid) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't change ftp: ".$_SESSION["adminpanel_error"];
				Header("Location: ftp_change.php?id=".$id."\n\n");
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			Header("Location: ftp_change.php?id=".$id."\n\n");
			exit;
		}

		Header("Location: ftp_change.php?id=".$id."\n\n");
		exit;
	}

	elseif ($act == "remove") {

		$ids = safe_get("ids", "POST");

		if ($ids == "") {
			echo "Error - did not specified id's parameter";
			exit;
		}

		$id_separate = explode(":", $ids);

		for($i=0; $i<count($id_separate); $i++) {
			$id = trim($id_separate[$i]);
			if ($id != "") {
				if ($HM->FC->remove_ftp_account($id) != TRUE) {
					$_SESSION["adminpanel_error"] = "Can't remove ftp user: ".$_SESSION["adminpanel_error"];
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

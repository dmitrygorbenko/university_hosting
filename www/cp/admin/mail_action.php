<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage();

	$act = safe_get("act", "GET", 255);

	if ($act == "add_mail") {

		$login = safe_get("login", "POST", 255);
		$password = safe_get("password", "POST", 255);
		$password2 = safe_get("password2", "POST", 255);
		$name = safe_get("name", "POST", 255);
		$forward_do = safe_get("forward_do", "POST", 5);
		$forward = safe_get("forward", "POST", 255);
		$reply_do = safe_get("reply_do", "POST", 5);
		$reply_text = pure_get("reply_text", "POST");

		$go_back =  "<html>
		<head>
		<title>Hosting.ai: Admin Panel</title>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">
		</head>
		<body bgcolor=\"EEEEEE\">
		<form name=\"mail\" method=\"POST\" action=\"mail_add.php\">
		<input type=\"hidden\" name=\"login\" value=\"".$login."\">
		<input type=\"hidden\" name=\"name\" value=\"".$name."\">
		<input type=\"hidden\" name=\"forward\" value=\"".$forward."\">
		<input type=\"hidden\" name=\"reply_text\" value=\"".base64_encode($reply_text)."\">
		<input type=\"hidden\" name=\"reply_do\" value=\"".$reply_do."\">
		<script language=\"javascript\">
			document.mail.submit();
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
			$password != "" &&
			$name != "") {

			$passwd = crypt($password);

			if ($HM->MC->add_mail_account($login, $passwd, $name, $forward_do, $forward, $reply_do, $reply_text) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't create email: ".$_SESSION["adminpanel_error"];
				echo $go_back;
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			echo $go_back;
			exit;
		}

		Header("Location: mail.php\n\n");
		exit;
	}

	elseif ($act == "change_email") {

		$id = safe_get("id", "POST", 255);
		$login = safe_get("login", "POST", 255);
		$password = safe_get("password", "POST", 255);
		$password2 = safe_get("password2", "POST", 255);
		$maildir = safe_get("maildir", "POST", 255);
		$home = safe_get("home", "POST", 255);
		$name = safe_get("name", "POST", 255);
		$uid = safe_get("uid", "POST", 255);
		$gid = safe_get("gid", "POST", 255);
		$forward_do = safe_get("forward_do", "POST", 5);
		$forward = safe_get("forward", "POST", 255);
		$reply_do = safe_get("reply_do", "POST", 5);
		$reply_text = pure_get("reply_text", "POST");

		if ($password !== $password2) {
			$_SESSION["adminpanel_error"] = "Passwords are not the same";
			Header("Location: mail_change.php?id=".$id."\n\n");
			exit;
		}

		if ($id != "" &&
			$login != "" &&
			$maildir != "" &&
			$home != "" &&
			$name != "" &&
			$uid != "" &&
			$gid != "") {

			if ($password != "") {
				$passwd = crypt($password);
			}
			else
				$passwd = "";

			if ($HM->MC->change_mail_account($id, $login, $passwd, $maildir, $home, $name, $uid, $gid, $forward_do, $forward, $reply_do, $reply_text) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't change email: ".$_SESSION["adminpanel_error"];
				Header("Location: mail_change.php?id=".$id."\n\n");
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			Header("Location: mail_change.php?id=".$id."\n\n");
			exit;
		}

		Header("Location: mail_change.php?id=".$id."\n\n");
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
				if ($HM->MC->remove_mail_account($id) != TRUE) {
					$_SESSION["adminpanel_error"] = "Can't remove account: ".$_SESSION["adminpanel_error"];
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

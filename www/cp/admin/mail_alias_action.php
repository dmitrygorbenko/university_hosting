<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage();

	$act = safe_get("act", "GET", 255);

	if ($act == "add_mail_alias") {

		$email = safe_get("email", "POST", 255);
		$alias = safe_get("alias", "POST", 65535);

		$go_back =  "<html>
		<head>
		<title>Hosting.ai: Admin Panel</title>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">
		</head>
		<body bgcolor=\"EEEEEE\">
		<form name=\"email_alias\" method=\"POST\" action=\"mail_alias_add.php\">
		<input type=\"hidden\" name=\"email\" value=\"".$email."\">
		<input type=\"hidden\" name=\"alias\" value=\"".$alias."\">
		<script language=\"javascript\">
			document.email_alias.submit();
		</script>
		<noscript>
			You don't have JavaScript to be enabled. Please, push this button:<br>
			<input type=\"submit\">
		</noscript>
		</form>";

		if (	$email != "" &&
			$alias != "") {

			if ($HM->MC->add_mail_alias($email, $alias) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't create email alias: ".$_SESSION["adminpanel_error"];
				echo $go_back;
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			echo $go_back;
			exit;
		}

		Header("Location: mail_alias.php\n\n");
		exit;
	}

	elseif ($act == "change_email_alias") {

		$id = safe_get("id", "POST", 255);
		$email = safe_get("email", "POST", 255);
		$alias = safe_get("alias", "POST", 65535);

		if ($id != "" &&
			$email != "" &&
			$alias != "") {

			if ($HM->MC->change_mail_alias($id, $email, $alias) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't change email alias: ".$_SESSION["adminpanel_error"];
				Header("Location: mail_alias_change.php?id=".$id."\n\n");
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			Header("Location: mail_alias_change.php?id=".$id."\n\n");
			exit;
		}

		Header("Location: mail_alias_change.php?id=".$id."\n\n");
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
				if ($HM->MC->remove_mail_alias($id) != TRUE) {
					$_SESSION["adminpanel_error"] = "Can't remove mail alias: ".$_SESSION["adminpanel_error"];
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

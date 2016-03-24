<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage();

	$act = safe_get("act", "GET", 255);

	if ($act == "add_redirector") {

		$domain = safe_get("domain", "POST", 255);
		$pointer = safe_get("pointer", "POST", 255);
		$title = safe_get("title", "POST", 255);
		$frameset = safe_get("frameset", "POST", 255);

		$go_back =  "<html>
		<head>
		<title>Hosting.ai: Admin Panel</title>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">
		</head>
		<body bgcolor=\"EEEEEE\">
		<form name=\"redirect\" method=\"POST\" action=\"redirect_add.php\">
		<input type=\"hidden\" name=\"domain\" value=\"".$domain."\">
		<input type=\"hidden\" name=\"pointer\" value=\"".$pointer."\">
		<input type=\"hidden\" name=\"frameset\" value=\"".$frameset."\">
		<script language=\"javascript\">
			document.redirect.submit();
		</script>
		<noscript>
			You don't have JavaScript to be enabled. Please, push this button:<br>
			<input type=\"submit\">
		</noscript>
		</form>";

		if ($domain != "" &&
			$pointer != "" &&
			$title != "") {

			if ($HM->WC->add_redirector($domain, $pointer, $title, $frameset) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't create redirector: ".$_SESSION["adminpanel_error"];
				echo $go_back;
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			echo $go_back;
			exit;
		}

		Header("Location: redirect.php\n\n");
		exit;
	}

	elseif ($act == "change_redirector") {

		$id = safe_get("id", "POST", 255);
		$domain = safe_get("domain", "POST", 255);
		$pointer = safe_get("pointer", "POST", 255);
		$title = safe_get("title", "POST", 255);
		$frameset = safe_get("frameset", "POST", 255);

		if ($id != "" &&
			$domain != "" &&
			$pointer != "" &&
			$title != "") {

			if ($HM->WC->change_redirector($id, $domain, $pointer, $title, $frameset) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't change redirector: ".$_SESSION["adminpanel_error"];
				Header("Location: redirect_change.php?id=".$id."\n\n");
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			Header("Location: redirect_change.php?id=".$id."\n\n");
			exit;
		}

		Header("Location: redirect_change.php?id=".$id."\n\n");
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
				if ($HM->WC->remove_redirector($id) != TRUE) {
					$_SESSION["adminpanel_error"] = "Can't remove redirector: ".$_SESSION["adminpanel_error"];
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

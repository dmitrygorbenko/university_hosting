<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage();

	$act = safe_get("act", "GET", 255);

	if ($act == "add_webdir") {

		$domain = safe_get("domain", "POST", 255);
		$id_zone = safe_get("id_zone", "POST", 255);
		$rootdir = safe_get("rootdir", "POST", 255);

		$go_back =  "<html>
		<head>
		<title>Hosting.ai: Admin Panel</title>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">
		</head>
		<body bgcolor=\"EEEEEE\">
		<form name=\"webdir\" method=\"POST\" action=\"webdir_add.php\">
		<input type=\"hidden\" name=\"domain\" value=\"".$domain."\">
		<input type=\"hidden\" name=\"id_zone\" value=\"".$id_zone."\">
		<input type=\"hidden\" name=\"rootdir\" value=\"".$rootdir."\">
		<script language=\"javascript\">
			document.webdir.submit();
		</script>
		<noscript>
			You don't have JavaScript to be enabled. Please, push this button:<br>
			<input type=\"submit\">
		</noscript>
		</form>";

		if ($domain != "" &&
			$id_zone != "" &&
			$rootdir != "") {

			if ($HM->WC->add_webdir($domain, $id_zone, $rootdir) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't create webdir: ".$_SESSION["adminpanel_error"];
				echo $go_back;
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			echo $go_back;
			exit;
		}

		Header("Location: webdir.php\n\n");
		exit;
	}

	elseif ($act == "change_webdir") {

		$id = safe_get("id", "POST", 255);
		$domain = safe_get("domain", "POST", 255);
		$id_zone = safe_get("id_zone", "POST", 255);
		$rootdir = safe_get("rootdir", "POST", 255);

		if ($id != "" &&
			$domain != "" &&
			$id_zone != "" &&
			$rootdir != "") {

			if ($HM->WC->change_webdir($id, $domain, $id_zone, $rootdir) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't change webdir: ".$_SESSION["adminpanel_error"];
				Header("Location: webdir_change.php?id=".$id."\n\n");
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			Header("Location: webdir_change.php?id=".$id."\n\n");
			exit;
		}

		Header("Location: webdir_change.php?id=".$id."\n\n");
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
				if ($HM->WC->remove_webdir($id) != TRUE) {
					$_SESSION["adminpanel_error"] = "Can't remove webdir: ".$_SESSION["adminpanel_error"];
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

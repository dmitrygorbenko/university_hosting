<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage;

	$act = safe_get("act", "GET", 255);

	if ($act == "add_our_zone") {

		$zone_name = safe_get("zone_name", "POST", 255);

		$go_back =  "<html>
		<head>
		<title>Hosting.ai: Admin Panel</title>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">
		</head>
		<body bgcolor=\"EEEEEE\">
		<form name=\"our_zone\" method=\"POST\" action=\"our_zone_add.php\">
		<input type=\"hidden\" name=\"zone_name\" value=\"".$zone_name."\">
		<script language=\"javascript\">
			document.our_zone.submit();
		</script>
		<noscript>
			You don't have JavaScript to be enabled. Please, push this button:<br>
			<input type=\"submit\">
		</noscript>
		</form>";

		if ($zone_name != "") {

			if ($HM->ZC->add_our_zone($zone_name) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't create our zone: ".$_SESSION["adminpanel_error"];
				echo $go_back;
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			echo $go_back;
			exit;
		}

		Header("Location: our_zone.php\n\n");
		exit;
	}

	elseif ($act == "change_our_zone") {

		$id = safe_get("id", "POST", 255);
		$zone_name = safe_get("zone_name", "POST", 255);

		if ($id != "" &&
			$zone_name != "") {

			if ($HM->ZC->change_our_zone($id, $zone_name) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't change our zone: ".$_SESSION["adminpanel_error"];
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
				if ($HM->ZC->remove_our_zone($id) != TRUE) {
					$_SESSION["adminpanel_error"] = "Can't remove our zone: ".$_SESSION["adminpanel_error"];
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

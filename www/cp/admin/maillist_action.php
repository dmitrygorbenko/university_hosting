<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage();

	$act = safe_get("act", "GET", 255);

	if ($act == "add_maillist") {

		$zone_id = safe_get("zone_id", "POST", 255);
		$title = safe_get("title", "POST", 255);
		$member_list = pure_get("member_list", "POST");

		$go_back =  "<html>
		<head>
		<title>Hosting.ai: Admin Panel</title>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">
		</head>
		<body bgcolor=\"EEEEEE\">
		<form name=\"emaillist\" method=\"POST\" action=\"maillist_add.php\">
		<input type=\"hidden\" name=\"zone_id\" value=\"".$zone_id."\">
		<input type=\"hidden\" name=\"title\" value=\"".$title."\">
		<input type=\"hidden\" name=\"member_list\" value=\"".base64_encode($member_list)."\">
		<script language=\"javascript\">
			document.emaillist.submit();
		</script>
		<noscript>
			You don't have JavaScript to be enabled. Please, push this button:<br>
			<input type=\"submit\">
		</noscript>
		</form>";

		if (	$zone_id != "" &&
			$title != "" &&
			$member_list != "") {

			if ($HM->MC->add_maillist($zone_id, $title, $member_list) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't create mailing list: ".$_SESSION["adminpanel_error"];
				echo $go_back;
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			echo $go_back;
			exit;
		}

		Header("Location: maillist.php\n\n");
		exit;
	}

	elseif ($act == "change_maillist") {

		$id = safe_get("id", "POST", 255);
		$zone_id = safe_get("zone_id", "POST", 255);
		$title = safe_get("title", "POST", 255);
		$member_list = pure_get("member_list", "POST");

		if ($id != "" &&
			$zone_id != "" &&
			$title != "" &&
			$member_list != "") {

			if ($HM->MC->change_maillist($id, $zone_id, $title, $member_list) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't change mailing list: ".$_SESSION["adminpanel_error"];
				Header("Location: maillist_change.php?id=".$id."\n\n");
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			Header("Location: maillist_change.php?id=".$id."\n\n");
			exit;
		}

		Header("Location: maillist_change.php?id=".$id."\n\n");
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
				if ($HM->MC->remove_maillist($id) != TRUE) {
					$_SESSION["adminpanel_error"] = "Can't remove mailing list: ".$_SESSION["adminpanel_error"];
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

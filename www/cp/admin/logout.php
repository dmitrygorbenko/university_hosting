<?php
	require_once ("header.php");

	if ($_SESSION["hosting_apps"] == 1) {
		session_destroy();
	}
	else {
		$_SESSION["hosting_apps"] -= 1;
		unset($_SESSION["adminpanel_login"]);
		unset($_SESSION["adminpanel_ip"]);
		unset($_SESSION["adminpanel_error"]);
	}

	header("Location: index.php\n\n");
	die();
?>
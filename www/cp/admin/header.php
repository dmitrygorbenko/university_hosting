<?php
	error_reporting (E_ALL ^E_NOTICE ^E_WARNING);
//	error_reporting (E_ALL);

	$INCLUDE_PATH="../include/";
	require_once ($INCLUDE_PATH."define.php");
	require_once ($INCLUDE_PATH."tools.php");
	require_once ("common.php");

	$SID = get_SID();

	if ($SID != "") {
		session_id($SID);
	}
	else {
		header( "Location: index.php\n\n" );
		exit;
	}

	session_start();

	if (!isset($_SESSION["adminpanel_login"])) {
		header( "Location: index.php\n\n" );
		exit;
	}

	if ($_SESSION["adminpanel_ip"] != $_SERVER["REMOTE_ADDR"]) {
		header( "Location: index.php\n\n" );
		exit;
	}

Header("Content-type: text/html");

Header("Expires: Wed, 11 Nov 1998 11:11:11 GMT\n".
"Cache-Control: no-cache\n".
"Cache-Control: must-revalidate\n".
"Pragma: no-cache");

$nocache = "<META HTTP-EQUIV=\"Cache-Control\" CONTENT=\"no-cache\">
<META HTTP-EQUIV=\"Expires\" CONTENT=\"-1\">
<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">";

?>
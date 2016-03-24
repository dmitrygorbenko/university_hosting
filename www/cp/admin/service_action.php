<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage();

	$act = safe_get("act", "GET", 255);

	if ($act == "start" || $act == "stop" || $act == "restart") {

		$service = safe_get("service", "GET", 255);

		if ($service != "") {

			$title = "";
			$duration = "0";

			if ($service == "remote_control") {
				$title = "Remote Control System";
				$duration = "5";
			}
			if ($service == "apache") {
				$title = "Apache daemon";
				$duration = "20";
			}
			if ($service == "all") {
				$title = "Whole VHS System";
				$duration = "60";
			}

			$body_content = "
<html>
<head>
<title>Hosting.ai: Admin Panel</title>
<META HTTP-EQUIV=\"Cache-Control\" CONTENT=\"no-cache\">
<META HTTP-EQUIV=\"Expires\" CONTENT=\"-1\">
<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">
<META HTTP-EQUIV=\"Refresh\" CONTENT=\"".$duration."; URL=service_restart.php\">
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">
<link href=\"../include/style.pph\" rel=\"stylesheet\" type=\"text/css\">
<script type=\"text/javascript\" src=\"../include/java_script.php\"></script>
</head>
<body bgcolor=\"EEEEEE\">
".$title." goes down... Please, wait ".$duration." seconds till your browser will go on
</body>
</html>";
			if ($service == "remote_control" || $service == "apache" || $service == "all") {
				echo $body_content;
				exit;
			}

			$result = $HM->HC->connect();
			if ($result["error"] == TRUE) {
				$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
				Header("Location: service_restart.php\n\n");
				exit;
			}

			$result = $HM->HC->service_control(strtoupper($act), strtoupper($service));

			// if we shutdown remote control, we will not know what happen
			// so, skip the result check
			if ($service != "remote_control" && $service != "apache" && $service != "all") {
				if ($result["error"] == TRUE) {
					$_SESSION["adminpanel_error"] = "Remote control: ".$result["mess"];
					$HM->HC->disconnect();
					Header("Location: service_restart.php\n\n");
					exit;
				}

				$HM->HC->disconnect();
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Can't find service variable ! - Browser error";
			Header("Location: service_restart.php\n\n");
			exit;
		}

		Header("Location: service_restart.php\n\n");
		exit;
	}

	$_SESSION["adminpanel_error"] = "Unknown action";
	Header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
	exit;
?>

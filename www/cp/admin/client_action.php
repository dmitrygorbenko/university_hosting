<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage();

	$act = safe_get("act", "GET", 255);

	if ($act == "add_client") {

		$login = safe_get("login", "POST", 255);
		$password = safe_get("password", "POST", 255);
		$password2 = safe_get("password2", "POST", 255);
		$email = safe_get("email", "POST", 255);
		$firstname = safe_get("firstname", "POST", 255);
		$lastname = safe_get("lastname", "POST", 255);
		$company = safe_get("company", "POST", 255);
		$region = safe_get("region", "POST", 255);
		$postal = safe_get("postal", "POST", 255);
		$city = safe_get("city", "POST", 255);
		$address = safe_get("address", "POST", 255);
		$phone = safe_get("phone", "POST", 255);
		$fax = safe_get("fax", "POST", 255);
		$active = (safe_get("active", "POST", 5) == "on"?TRUE:FALSE);

		$go_back =  "<html>
		<head>
		<title>Hosting.ai: Admin Panel</title>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">
		</head>
		<body bgcolor=\"EEEEEE\">
		<form name=\"client\" method=\"POST\" action=\"client_add.php\">
		<input type=\"hidden\" name=\"login\" value=\"".$login."\">
		<input type=\"hidden\" name=\"email\" value=\"".$email."\">
		<input type=\"hidden\" name=\"firstname\" value=\"".$firstname."\">
		<input type=\"hidden\" name=\"lastname\" value=\"".$lastname."\">
		<input type=\"hidden\" name=\"company\" value=\"".$company."\">
		<input type=\"hidden\" name=\"region\" value=\"".$region."\">
		<input type=\"hidden\" name=\"poastal\" value=\"".$postal."\">
		<input type=\"hidden\" name=\"city\" value=\"".$city."\">
		<input type=\"hidden\" name=\"address\" value=\"".$address."\">
		<input type=\"hidden\" name=\"phone\" value=\"".$phone."\">
		<input type=\"hidden\" name=\"fax\" value=\"".$fax."\">
		<script language=\"javascript\">
			document.client.submit();
		</script>
		<noscript>
			You don't have JavaScript to be enabled. Please, push this button:<br>
			<input type=\"submit\">
		</noscript>
		</form>";

		if ($password !== $password2) {
			$_SESSION["adminpanel_error"] = "Password do not match";
			echo $go_back;
			exit;
		}

		if (	$login != "" &&
			$password != "" &&
			$firstname != "") {

			$passwd = crypt($password);

			if ($HM->CC->add_client_account($active, $login, $passwd, $email, $firstname, $lastname, $company, $region, $postal, $city, $address, $phone, $fax, "") != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't create client: ".$_SESSION["adminpanel_error"];
				echo $go_back;
				exit;
			}
		}
		else {
			$_SESSION["adminpanel_error"] = "Not enough data";
			echo $go_back;
			exit;
		}

		Header("Location: client.php\n\n");
		exit;
	}

	elseif ($act == "change_account") {

		$id = safe_get("id", "POST", 255);
		$login = safe_get("login", "POST", 255);
		$password = safe_get("password", "POST", 255);
		$password2 = safe_get("password2", "POST", 255);
		$email = safe_get("email", "POST", 255);
		$firstname = safe_get("firstname", "POST", 255);
		$lastname = safe_get("lastname", "POST", 255);
		$company = safe_get("company", "POST", 255);
		$region = safe_get("region", "POST", 255);
		$postal = safe_get("postal", "POST", 255);
		$city = safe_get("city", "POST", 255);
		$address = safe_get("address", "POST", 255);
		$phone = safe_get("phone", "POST", 255);
		$fax = safe_get("fax", "POST", 255);
		$add_info = safe_get("add_info", "POST", 255);
		$active = (safe_get("active", "POST", 5) == "on"?TRUE:FALSE);

		if ($password !== $password2) {
			$_SESSION["adminpanel_error"] = "Password do not match";
			Header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
			exit;
		}

		if (	$id != "" &&
			$login != "" &&
			$firstname != "") {

			if ($password != "") {
				$passwd = crypt($password);
			}
			else
				$passwd = "";

			if ($HM->CC->change_client_account($id, $active, $login, $passwd, $email, $firstname, $lastname, $company, $region, $postal, $city, $address, $phone, $fax, $add_info) != TRUE) {
				$_SESSION["adminpanel_error"] = "Can't change client: ".$_SESSION["adminpanel_error"];
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
				if ($HM->CC->remove_client_account($id) != TRUE) {
					$_SESSION["adminpanel_error"] = "Can't remove client: ".$_SESSION["adminpanel_error"];
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

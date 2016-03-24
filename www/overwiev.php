<?php
	$INCLUDE_PATH="cp/include/";
	include_once($INCLUDE_PATH."define.php");
	include_once($INCLUDE_PATH."tools.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage;

	$service_type = safe_get("type", "POST", 255);
	$domain_type = safe_get("id", "POST", 255);
	$fdname = safe_get("fdname", "POST", 255);

	if ($service_type == "" || $domain_type == "" && $fdname == "")
		header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");

	if ($service_type != "free" && $service_type != "full")
		header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
	if ($domain_type != "our" && $domain_type != "transfer" && $domain_type != "buy")
		header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
?>

<html>
<head>
<title>Хостинг в ХАИ</title>
<meta http-equiv="Content-Type" content="text/html; charset=koi8-r">
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body class="body">

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="70px">
<tr width="100%" height="70px">
	<td width="113px" height="70px" background="cp/img/new/banner_left.jpg" NOWRAP>
	</td>

	<td width="412px" height="70px" NOWRAP>
		<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
		<tr height="44px">
			<td width="412px" height="44px" colspan="9" background="cp/img/new/banner_top.jpg" NOWRAP>
			</td>
		</tr>
		<tr height="17px">

			<td width="59px" height="17px" NOWRAP>
			<a href="index.php">
				<img width="59px" height="17px" src="cp/img/new/banner_main.jpg" alt="Главная">
			</a>
			</td>

			<td width="10px" height="17px" background="cp/img/new/banner_dummy.jpg" NOWRAP>
			</td>

			<td width="61px" height="17px" NOWRAP>
			<a href="tariff.php">
				<img width="61px" height="17px" src="cp/img/new/banner_tariff.jpg" alt="Тарифы">
			</a>
			</td>

			<td width="10px" height="17px" background="cp/img/new/banner_dummy.jpg" NOWRAP>
			</td>

			<td width="134px" height="17px" NOWRAP>
			<a href="cp/client/">
				<img width="134px" height="17px" src="cp/img/new/banner_panel.jpg" alt="Контрольная Панель">
			</a>
			</td>

			<td width="10px" height="17px" background="cp/img/new/banner_dummy.jpg" NOWRAP>
			</td>

			<td width="53px" height="17px" NOWRAP>
			<a href="forum/">
				<img width="53px" height="17px" src="cp/img/new/banner_forum.jpg" alt="Форум">
			</a>
			</td>

			<td width="10px" height="17px" background="cp/img/new/banner_dummy.jpg" NOWRAP>
			</td>

			<td width="65px" height="17px" NOWRAP>
			<a href="rules.php">
				<img width="65px" height="17px" src="cp/img/new/banner_rules.jpg" alt="Правила">
			</a>
			</td>
		</tr>
		<tr height="9px" width="412px">
			<td width="412px" height="9px" colspan="9" background="cp/img/new/banner_bottom.jpg" NOWRAP>
			</td>
		</tr>
		</table>
	</td>

	<td width="100%" height="70px" background="cp/img/new/banner_center.jpg" NOWRAP>
	</td>

	<td width="101px" height="70px" background="cp/img/new/banner_right.jpg" NOWRAP>
	</td>
</tr>
</table>

<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr width="100%" height="10px">
<td NOWRAP></td>
</tr>
<tr width="100%">

	<td width="30px" NOWRAP>
	</td>

	<td width="150px" NOWRAP valign="top">
		<table border="0" cellspacing="0" cellpadding="0">
		<tr><td>
			<a href="index.php" class="menu_main">
				<strong>Главная</strong>
			</a>
		</td></tr>

		<tr><td height="10px" NOWRAP>
		</td></tr>

		<tr><td>
			<a href="tariff.php" class="menu_main">
				<strong>Тарифы</strong>
			</a>
		</td></tr>

		<tr><td>
			<a href="free.php" class="menu">
				Бесплатный
			</a>
		</td></tr>

		<tr><td>
			<a href="full.php" class="menu">
				Полный
			</a>
		</td></tr>

		<tr><td>
			<a href="compare.php" class="menu">
				Сравнение
			</a>
		</td></tr>

		<tr><td height="10px" NOWRAP>
		</td></tr>

		<tr><td>
			<a href="cp/client/" class="menu_main">
				<strong>Контрольная панель</strong>
			</a>
		</td></tr>

		<tr><td>
			<a href="cp/client/webmail/" class="menu">
				Web-почта
			</a>
		</td></tr>

		<tr><td>
			<a href="cp/client/ftpmanager/" class="menu">
				FTP-менеджер
			</a>
		</td></tr>

		<tr><td>
			<a href="cp/client/pma/" class="menu">
				Управление MySQL
			</a>
		</td></tr>

		<tr><td>
			<a href="cp/client/pga/" class="menu">
				Управление PgSQL
			</a>
		</td></tr>

		<tr><td height="10px" NOWRAP>
		</td></tr>

		<tr><td>
			<a href="forum/" class="menu_main">
				<strong>Форум - Хостинг</strong>
			</a>
		</td></tr>

		<tr><td height="10px" NOWRAP>
		</td></tr>

		<tr><td>
			<a href="rules.php" class="menu_main">
				<strong>Правила</strong>
			</a>
		</td></tr>

		</table>

	</td>

	<td width="100%" valign="top">

		<table border="0" cellspacing="0" cellpadding="0" width="100%">

		<tr height="20px" width="100%">
			<td width="20px" height="20px" background="cp/img/new/border_nw.jpg" NOWRAP>
			</td>
			<td width="100%" height="20px" background="cp/img/new/border_n.jpg">
			</td>
			<td width="20px" height="20px" background="cp/img/new/border_ne.jpg" NOWRAP>
			</td>
		</tr>

		<tr width="100%">
			<td width="20px" background="cp/img/new/border_w.jpg" NOWRAP>
			</td>
			<td bgcolor="#FFFFFF">
<?php
	// Еще раз проверим новый домен на наличие
	$exist = FALSE;
	$tmp_data = $HM->ZC->fetch_zone_by_name($fdname);
	if ($tmp_data != FALSE) {
		$exist = TRUE;
	}
	else {
		if ($domain_type != "transfer") {
		$result = gethostbyname($fdname);
		if ($result != $fdname)
			$exist = TRUE;
		}
	}
	if ($exist == TRUE) {
		// Такой домен уже есть
		echo "Такой домен уже существует";
		exit;
	}

	if ($domain_type != "our") {
		// Еще раз проверим, может наш клиент хочет создать доменную зону наподобие нашей
		$result = $HM->ZC->fetch_all_our_zones();
		if ($result == FALSE) {
			echo "Can't fetch all our zones: ".$_SESSION["adminpanel_error"];
			exit;
		}

		for ($i = 0; $i < $HM->ZC->SQL->get_num_rows($result); $i++) {
			$data = $HM->ZC->SQL->get_object($result);

			$a = substr($fdname, strlen($fdname) - strlen($data->name), strlen($fdname));

			if ($a == $data->name) {
				echo "Такие доменные имена не катят !<br>Нельзя выбрать домен в одной из наших доменных зон";
				exit;
			}
		}
	}

	// какой тип домена у нас ?
	if ($domain_type == "our") {
		$active = "1";
	}
	else {
		$active = "0";
	}
?>
<?php
	$login = safe_get("login", "POST", 255);
	$password = safe_get("password", "POST", 255);
	$password2 = safe_get("password2", "POST", 255);
	$email = safe_get("email", "POST", 255);
	$firstname = safe_get("firstname", "POST", 255);
	$lastname = safe_get("lastname", "POST", 255);
	$address = safe_get("address", "POST", 255);
	$phone = safe_get("phone", "POST", 255);
	$add_info = safe_get("add_info", "POST", 1024);


	// Проверяем, есть ли такой логин ?
	// Если есть - тревога !
	$tmp_data = $HM->CC->fetch_client_account_by_login($login);
	if ($tmp_data != FALSE) {
		echo "<center>
			<form action=\"private_info.php\" method=\"POST\">
				Такой логин уже существует
				<p>
				<input type=\"hidden\" name=\"type\" value=\"".$service_type."\">
				<input type=\"hidden\" name=\"id\" value=\"".$domain_type."\">
				<input type=\"hidden\" name=\"domain\" value=\"".$fdname."\">
				<input type=\"hidden\" name=\"fisrtname\" value=\"".$firstname."\">
				<input type=\"hidden\" name=\"lastname\" value=\"".$lastname."\">
				<input type=\"hidden\" name=\"email\" value=\"".$email."\">
				<input type=\"hidden\" name=\"address\" value=\"".$address."\">
				<input type=\"hidden\" name=\"phone\" value=\"".$phone."\">
				<input type=\"hidden\" name=\"add_info\" value=\"".$add_info."\">
				<input type=\"submit\" class=\"simple_button\" value=\" Назад \">
			</center>
			</form>
			</body>
			</html>";
		exit;
	}

	if ($password !== $password2) {
		header("Location: private_info.php?type=".$service_type."&id=".$domain_type."&login=".$login."&fdname=".$fdname."&firstname=".$firstname."&lastname=".$lastname."&email=".$email."&address=".$address."&phone=".$phone."&add_info=".$add_info."&err=Пароли не совпадают\n\n");
		exit;
	}

	if (	$login != "" &&
		$password != "" &&
		$firstname != "") {

		$company = "";
		$region = "";
		$postal = "";
		$city = "";
		$fax = "";
		$passwd = crypt($password);

		if ($HM->CC->add_client_account($service_type, $domain_type, $fdname, $active, $login, $passwd, $email, $firstname, $lastname, $company, $region, $postal, $city, $address, $phone, $fax, $add_info, "client") != TRUE) {
			echo "Не могу создать домен: ".$_SESSION["adminpanel_error"];
			exit;
		}

	}
	else {
		echo "Введите данные !";
	}

	echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">";

	if ($domain_type == "buy") {
		echo "
		<tr>
			<td class=\"content\" width=\"100%\">
				Шаг последний: <strong>Создание домена</strong>
			</td>
		</tr>
		<tr>
			<td class=\"content_3\">
			<ul>
				Мы попытаемся создать заказанный Вами домен.
				<p>
				Это может занять от нескольких дней до одной недели.
				В любом случае, Вы будете оповещены о результате.
			</ul>
			</td>
		</tr>
		";
	}

	else {
		echo "
		<tr>
			<td class=\"content\" width=\"100%\">
				<strong>Ресурс создан !</strong>
			</td>
		</tr>
		<tr>
			<td class=\"content_3\">
			<strong>Дата:</strong>&nbsp;".date("l dS of F Y h:i:s")."
			<ul>
				Ваш ресурс создан и готов к использованию.";

		if ($domain_type == "transfer")
			echo "	<br>Однако, до тех пор, пока Вы не переведете к нам домен,
				Ваш ресурс не будет доступен.";

		echo		"
				<p>
				<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"600\">
				<tr>
					<td width=\"100%\" class=\"content\" colspan=\"3\">
						Данные ресурса
					</td>
				</tr>
				<tr>
					<td width=\"33%\" valign=\"top\">
						<strong>Адрес:</strong>
						<br>
						http://".$fdname."
					</td>
					<td width=\"33%\" valign=\"top\">
						<strong>Тип:</strong>
						<br>
						".($service_type=="free"?"Бесплатный":"Платный")."
					</td>
					<td width=\"34%\" valign=\"top\">
						&nbsp;
					</td>
				</tr>
				</table>

				<br>
				<br>

				<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"600\">
				<tr>
				<tr>
					<td width=\"100%\" class=\"content\" colspan=\"3\">
						Доступ:
					</td>
				</tr>
				<tr>
					<td width=\"33%\" valign=\"top\">
						<strong>Контрольная панель:</strong>
						<br>
						Логин: <strong>".$login."</strong><br>
						Пароль: <strong>".$password."</strong><br>
					</td>
					<td width=\"33%\" valign=\"top\">
						<strong>СУБД MySQL:</strong>
						<br>
						Логин: <strong>".$login."</strong><br>
						Пароль: <strong>".$login."</strong><br>
					</td>
					<td width=\"34%\" valign=\"top\">
						<strong>СУБД PGSQL:</strong>
						<br>";
			if($service_type == "full") {
				echo "
						Логин: <strong>".$login."</strong><br>
						Пароль: <strong>".$login."</strong><br>";
			}
			else {
				echo "не доступен";
			}
			echo
			"
					</td>
				</tr>
				</table>

				<p>
				<a href=\"cp/client/\" target=\"_blank\">Контрольная панель</a>
			</ul>
			</td>
		</tr>
		";
	}
?>
</table>
			</td>
			<td width="20px" background="cp/img/new/border_e.jpg" NOWRAP>
			</td>
		</tr>

		<tr height="20px" width="100%">
			<td width="20px" height="20px" background="cp/img/new/border_sw.jpg" NOWRAP>
			</td>
			<td width="100%" height="20px" background="cp/img/new/border_s.jpg">
			</td>
			<td width="20px" height="20px" background="cp/img/new/border_se.jpg" NOWRAP>
			</td>
		</tr>

		</table>
	</td>
</tr>
</table>
</body>
</html>

<?php
	include ("footer.php");
?>

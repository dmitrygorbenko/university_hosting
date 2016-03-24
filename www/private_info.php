<?php
	$INCLUDE_PATH="cp/include/";
	include_once($INCLUDE_PATH."define.php");
	include_once($INCLUDE_PATH."tools.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage;

	$type = safe_get("type", "POST", 255);
	$id = safe_get("id", "POST", 255);
	$domain = safe_get("domain", "POST", 255);
	$zone = safe_get("zone", "POST", 255);

	if ($type != "free" && $type != "full")
		header("Location: order.php\n\n");
	if ($id != "our" && $id != "transfer" && $id != "buy")
		header("Location: order.php\n\n");
?>

<html>
<head>
<title>Хостинг в ХАИ</title>
<meta http-equiv="Content-Type" content="text/html; charset=koi8-r">
<link href="style.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="script.js"></script>
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
	if ($type == "" || $id == "" || $domain == "") {
		echo "Не заполненны необходимые поля !";
		exit;
	}

	if ($id == "our") {
		$fdname = $domain.".".$zone;
	}
	else {
		$fdname = $domain;
	}


	// Есть ли уже такой домен ?
	$exist = FALSE;
	$tmp_data = $HM->ZC->fetch_zone_by_name($fdname);
	if ($tmp_data != FALSE) {
		$exist = TRUE;
	}
	else {
		if ($id != "transfer") {
		$result = gethostbyname($fdname);
		if ($result != $fdname)
			$exist = TRUE;
		}
	}

	if ($exist == TRUE) {
		// Такой домен уже есть
		echo "<center>
			Такой домен уже существует
			<p>
			<a href=\"sel_dom.php?type=".$type."&id=".$id."&domain=".$domain."\">Назад</a>			</form>
			</center>
		</body>
		</html>";
		exit;
	}

	if ($id != "our") {
		// Проверим, может наш клиент хочет создать доменную зону наподобие нашей
		$result = $HM->ZC->fetch_all_our_zones();
		if ($result == FALSE) {
			echo "Can't fetch all our zones: ".$_SESSION["adminpanel_error"];
			exit;
		}

		for ($i = 0; $i < $HM->ZC->SQL->get_num_rows($result); $i++) {
			$data = $HM->ZC->SQL->get_object($result);

			$a = substr($fdname, strlen($fdname) - strlen($data->name), strlen($fdname));

			if ($a == $data->name) {
				echo "<center>
					Такие доменные имена не катят !<br>Нельзя выбрать домен в одной из наших доменных зон
					<p>
					<a href=\"sel_dom.php?type=".$type."&id=".$id."&domain=".$domain."\">Назад</a>
					</center>
				</body>
				</html>";
				exit;
			}
		}
	}

?>

<table border="0" cellspacing="0" cellpadding="0" width="100%">

<tr>
	<td class="content" width="100%">
		Шаг третий: <strong>Персональная информация</strong>
	</td>
</tr>
<tr>
	<td class="content_3">
<?php

	$login = safe_get("login", "POST", 255);
	$email = safe_get("email", "POST", 255);
	$firstname = safe_get("firstname", "POST", 255);
	$lastname = safe_get("lastname", "POST", 255);
	$address = safe_get("address", "POST", 255);
	$phone = safe_get("phone", "POST", 255);
	$add_info = safe_get("add_info", "POST", 1024);

echo "
	<a href=\"sel_dom.php?type=".$type."&id=".$id."&domain=".$domain."\">Назад</a>
	<p>
	<ul>
	Вы выбрали доменное имя:&nbsp;<strong>".$fdname."</strong>
	<p>
	Далее, Вам следут ввести информацию о себе. Поля помеченные красной звездой являются обязательными.";

	if ($err != "") {
		echo "<p><font color=\"#AA0000\">".$err."</font><p>";
	}

echo "
	<form action=\"overwiev.php\" method=\"POST\" name=\"client_manage\">
	<input type=\"hidden\" name=\"type\" value=\"".$type."\">
	<input type=\"hidden\" name=\"id\" value=\"".$id."\">
	<input type=\"hidden\" name=\"fdname\" value=\"".$fdname."\">
	<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\">

	<tr>
		<td class=\"content2\" colspan=\"2\">
			<strong>Доступ к Контрольной Панели:</strong>
		</td>
	</tr>

	<tr>
		<td class=\"content2\">
			<font color=\"#FF0000\">*</font>Логин:
		</td>
		<td>
			<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"login\" id=\"login\" value=\"".$login."\">
		</td>
	</tr>
	<tr>
		<td class=\"content2\">
			<font color=\"#FF0000\">*</font>Пароль:
		</td>
		<td>
			<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"password\" name=\"password\" id=\"password\">
		</td>
	</tr>
	<tr>
		<td class=\"content2\">
			<font color=\"#FF0000\">*</font>Еще раз:
		</td>
		<td>
			<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"password\" name=\"password2\" id=\"password2\">
		</td>
	</tr>
	<tr height=\"15\">
		<td colspan=\"2\">
		</td>
	</tr>
	<tr>
		<td class=\"content2\" colspan=\"2\">
			<strong>Личное:</strong>
		</td>
	</tr>
	<tr>
		<td class=\"content2\">
			<font color=\"#FF0000\">*</font>Имя:
		</td>
		<td>
			<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"firstname\" id=\"firstname\" value=\"".$firstname."\">
		</td>
	</tr>
	<tr>
		<td class=\"content2\">
			Фамилия:
		</td>
		<td>
			<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"lastname\" value=\"".$lastname."\">
		</td>
	</tr>
	<tr>
		<td class=\"content2\">
			E-mail:
		</td>
		<td>
			<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"email\" value=\"".$email."\">
		</td>
	</tr>
	<tr>
		<td class=\"content2\">
			Адрес:
		</td>
		<td>
			<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"address\" value=\"".$address."\">
		</td>
	</tr>
	<tr>
		<td class=\"content2\">
			Телефон:
		</td>
		<td>
			<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"phone\" value=\"".$phone."\">
		</td>
	</tr>

	<tr>
		<td class=\"content2\">
			&nbsp;
		</td>
		<td class=\"content2\">
			<p><p>
			Если Вы не указали номер телефона и E-mail, то в следующем
			поле оставьте информацию, чтобы мы могли с Вами связаться.
			Например: ник в форуме <a href=\"http://dexanet.kharkov.ua/phpBB2/\" target=\"_blank\">DexaNet</a>
		</td>
	</tr>
	<tr>
		<td class=\"content2\">
			Дополнительная информация:
		</td>
		<td class=\"content2\">
			<textarea cols=\"40\" name=\"add_info\">".$add_info."</textarea>
		</td>
	</tr>
	<tr height=\"40\">
		<td>
			&nbsp;
		</td>
		<td>
			&nbsp;<input class=\"simple_button\" type=\"button\" onClick=\"submit_private()\" value=\" Далее \">
		</td>
	</tr>
	</table>
	</form>
	</ul>
<script language=\"javascript\">
	document.client_manage.domain.focus();
</script>";

?>
	</td>
</tr>
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

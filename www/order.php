<?php
	$INCLUDE_PATH="cp/include/";
	include_once($INCLUDE_PATH."define.php");
	include_once($INCLUDE_PATH."tools.php");

	$type = safe_get("type", "GET", 255);

	if ($type == "")
		header("Location: index.php\n\n");
	if ($type != "free" && $type != "full")
		header("Location: index.php\n\n");

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
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
	<td class="content" width="100%">
		Шаг первый: <strong>Способ регистрации домена</strong>
	</td>
</tr>
<tr>
	<td width="60%"  class="content_3">
		<br>
		Доменное имя это адрес который
		надо будет набрать в обозревателе Интернета для доступа в Вашему
		ресурсу.
		<br><br>
		Вам следует выбрать, каким способом Вы зарегистрируете доменное имя:
		<table>
		<tr>
			<td>
			<ol>
				<li>
				<a href="sel_dom.php?type=<?php echo $type; ?>&id=our">Выбрать доменное имя в одной из следующих доменных зон:</a>
				<ul>
					<li>
					hosting.ai
					</li>
					<li>
					web.ai
					</li>
					<li>
					narod.ai
					</li>

				</ul>
				</li>

				<br>

				<li>
				<a href="sel_dom.php?type=<?php echo $type; ?>&id=transfer">Перенести Ваш домен к нам:</a>
				<ul>
					Для этого Вам прийдется обратиться в администратору Вашей доменной зоны.
				</ul>
				</li>

				<br>

				<li>
				<a href="sel_dom.php?type=<?php echo $type; ?>&id=buy">Заказать домен:</a>
				<ul>
					Эта услуга платная, но она дает Вам возможность выбора любого доменного имени.
				</ul>
				</li>
			<ol>
			</td>
		</tr>
		<tr>
			<td>
			</td>
		</tr>
		<tr>
			<td>
			</td>
		</tr>
		<tr>
			<td>
			</td>
		</tr>
		</table>
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

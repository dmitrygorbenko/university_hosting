<?php
	$INCLUDE_PATH="cp/include/";
	include_once($INCLUDE_PATH."define.php");
	include_once($INCLUDE_PATH."tools.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage;

	$type = safe_get("type", "GET", 255);
	$id = safe_get("id", "GET", 255);

	if ($type == "")
		$type = safe_get("type", "POST", 255);
	if ($id == "")
		$id = safe_get("id", "POST", 255);

	$domain = safe_get("domain", "POST", 255);
	if ($domain == "")
		$domain = safe_get("domain", "GET", 255);


	if ($type == "" || $id == "") {
		echo "1";
		exit;
		header("Location: order.php\n\n");
	}

	if ($type != "free" && $type != "full"){
		echo "2";
		exit;

		header("Location: order.php\n\n");
	}
	if ($id != "our" && $id != "transfer" && $id != "buy"){
		echo "3";
		exit;
		header("Location: order.php\n\n");
	}
?>

<html>
<head>
<title>������� � ���</title>
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
				<img width="59px" height="17px" src="cp/img/new/banner_main.jpg" alt="�������">
			</a>
			</td>

			<td width="10px" height="17px" background="cp/img/new/banner_dummy.jpg" NOWRAP>
			</td>

			<td width="61px" height="17px" NOWRAP>
			<a href="tariff.php">
				<img width="61px" height="17px" src="cp/img/new/banner_tariff.jpg" alt="������">
			</a>
			</td>

			<td width="10px" height="17px" background="cp/img/new/banner_dummy.jpg" NOWRAP>
			</td>

			<td width="134px" height="17px" NOWRAP>
			<a href="cp/client/">
				<img width="134px" height="17px" src="cp/img/new/banner_panel.jpg" alt="����������� ������">
			</a>
			</td>

			<td width="10px" height="17px" background="cp/img/new/banner_dummy.jpg" NOWRAP>
			</td>

			<td width="53px" height="17px" NOWRAP>
			<a href="forum/">
				<img width="53px" height="17px" src="cp/img/new/banner_forum.jpg" alt="�����">
			</a>
			</td>

			<td width="10px" height="17px" background="cp/img/new/banner_dummy.jpg" NOWRAP>
			</td>

			<td width="65px" height="17px" NOWRAP>
			<a href="rules.php">
				<img width="65px" height="17px" src="cp/img/new/banner_rules.jpg" alt="�������">
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
				<strong>�������</strong>
			</a>
		</td></tr>

		<tr><td height="10px" NOWRAP>
		</td></tr>

		<tr><td>
			<a href="tariff.php" class="menu_main">
				<strong>������</strong>
			</a>
		</td></tr>

		<tr><td>
			<a href="free.php" class="menu">
				����������
			</a>
		</td></tr>

		<tr><td>
			<a href="full.php" class="menu">
				������
			</a>
		</td></tr>

		<tr><td>
			<a href="compare.php" class="menu">
				���������
			</a>
		</td></tr>

		<tr><td height="10px" NOWRAP>
		</td></tr>

		<tr><td>
			<a href="cp/client/" class="menu_main">
				<strong>����������� ������</strong>
			</a>
		</td></tr>

		<tr><td>
			<a href="cp/client/webmail/" class="menu">
				Web-�����
			</a>
		</td></tr>

		<tr><td>
			<a href="cp/client/ftpmanager/" class="menu">
				FTP-��������
			</a>
		</td></tr>

		<tr><td>
			<a href="cp/client/pma/" class="menu">
				���������� MySQL
			</a>
		</td></tr>

		<tr><td>
			<a href="cp/client/pga/" class="menu">
				���������� PgSQL
			</a>
		</td></tr>

		<tr><td height="10px" NOWRAP>
		</td></tr>

		<tr><td>
			<a href="forum/" class="menu_main">
				<strong>����� - �������</strong>
			</a>
		</td></tr>

		<tr><td height="10px" NOWRAP>
		</td></tr>

		<tr><td>
			<a href="rules.php" class="menu_main">
				<strong>�������</strong>
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

<?php

if ($id == "our") {

	$result = $HM->ZC->fetch_all_our_zones();
	if ($result == FALSE) {
		echo "�� ���� ������� �������� ����: ".$SQL->get_error();
		$SQL->disconnect();
		exit;
	}

	echo "
	<tr>
		<td class=\"content\" width=\"100%\">
			��� ������: <strong>����� ��������� �����</strong>
		</td>
	</tr>
	<tr>
		<td width=\"60%\"  class=\"content_3\">
			<a href=\"order.php?type=".$type."\">�����</a>
			<form action=\"private_info.php\" method=\"POST\">
			<input type=\"hidden\" name=\"type\" value=\"".$type."\">
			<input type=\"hidden\" name=\"id\" value=\"our\">
			<br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			��� ������ ��������� ����� ������ ����� �������� ��������:
			�������� �� ��������� �������� ���.
			<br>
			��� ����� ������� �������� ���, �������� �������� ���� � ������� \" ����� \".
			<br>
			<br>
			�������� �����:
			<input class=\"simple_input\" size=\"20\" maxlength=\"255\" type=\"text\" name=\"domain\" value=\"".$domain."\">
			� �������� ����:
			<select name=\"zone\" class=\"simple_select\">";

	for ($i = 0; $i < $HM->ZC->SQL->get_num_rows($result); $i++) {
		$data = $HM->ZC->SQL->get_object($result);
		echo  "<option value=\"".$data->name."\">".$data->name."</option>\n";
	}

	echo "
			</select>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input class=\"simple_button\" type=\"submit\" value=\" ����� \">
			<br><br>

		</td>
	</tr>
	";
}

elseif ($id == "transfer") {

	echo "
	<tr>
		<td class=\"content\" width=\"100%\">
			��� ������: <strong>������� ��������� �����</strong>
		</td>
	</tr>
	<tr>
		<td width=\"60%\"  class=\"content_3\">
			<a href=\"order.php?type=".$type."\">�����</a>
			<form action=\"private_info.php\" method=\"POST\">
			<input type=\"hidden\" name=\"type\" value=\"".$type."\">
			<input type=\"hidden\" name=\"id\" value=\"transfer\">
			<br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			��� �������� ������ ������ �� ���� NS �������, ��� ���������� ���������
			� ��������������� ����� �������� ����. �������������� ����� ����������� ��������� ����������:
			<ul>
				<li>
				��������� NS ������:&nbsp;&nbsp; ns.hosting.ai (IP: 172.16.212.200)
				</li>
				<li>
				��������� NS ������:&nbsp;&nbsp; ns2.hosting.ai (IP: 172.16.212.89)
				</li>
			</ul>
			<br>
			�����, ������� �������� ��� ������� �� ���������� � ��� � ������� \" ����� \".
			<br>
			<br>
			�������� ���:
			<input class=\"simple_input\" size=\"20\" maxlength=\"255\" type=\"text\" name=\"domain\" value=\"".$domain."\">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input class=\"simple_button\" type=\"submit\" value=\" ����� \">
			<br><br>

		</td>
	</tr>
	";
}

elseif ($id == "buy") {

	echo "
	<tr>
		<td class=\"content\" width=\"100%\">
			��� ������: <strong>����� ��������� �����</strong>
		</td>
	</tr>
	<tr>
		<td width=\"60%\"  class=\"content_3\">
			<a href=\"order.php?type=".$type."\">�����</a>
			<form action=\"private_info.php\" method=\"POST\">
			<input type=\"hidden\" name=\"type\" value=\"".$type."\">
			<input type=\"hidden\" name=\"id\" value=\"buy\">
			<br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			� ��� ������, ���� �� ��� ������� �������� ���, �� ��
			������ ��� ��� ������� � ���������, �� ����� ����������
			���� ������: �� ���������� ��������� � ��������������� ���� ������,
			������� �� �������, � ������� � ��� ���� �������� ���.
			��� ������ �������, �� ��������� - 20 ���.
			<br>
			<br>
			�� �� ����������� �������� ������ ��������� ����� � ������� ����� ��������� ����� ��������. ������ �� � ������� �� �������� ������ ����� ������ ������.
			<br>
			<br>
			������� �������� ��� � ������� \" ����� \".
			<br>
			<br>
			�������� ���:
			<input class=\"simple_input\" size=\"20\" maxlength=\"255\" type=\"text\" name=\"domain\" value=\"".$domain."\">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input class=\"simple_button\" type=\"submit\" value=\" ����� \">
			<br><br>

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

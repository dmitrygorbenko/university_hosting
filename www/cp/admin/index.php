<?php
	include ( "../include/define.php" );
	include ( "../include/tools.php" );

	$SID = get_SID();

	if ($SID != "") {
		session_start();

		if (isset($_SESSION["adminpanel_login"])) {
			header( "Location: frame.php\n\n" );
			exit;
		}
	}

	$error = safe_get("error", "GET", 10);
?>

<html>
<head>
<title>Hosting.ai: Admin Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=koi8-r">
<link href="../include/style.php" rel="stylesheet" type="text/css">
</head>
<body bgcolor="EEEEEE">

<?php
	if ($error == "0")
		echo "<FONT color=\"".$warn_color["minor"]."\">Info: Empty fields</FONT><br>";
	if ($error == "1")
		echo "<FONT color=\"".$warn_color["major"]."\">Error: Login Failed</FONT><br>";
?>

<form action="login.php" method="POST" name="login_form">
<center>
<table border="0" width="220px" height="100%">
	<tr height="100%">
	<td width="100%" height="100%" valign="center" align="center">

		<span class="title">Панель Администратора</span>

		<table width="100%" border="0">

		<tr height="44px">
			<td width="15%" align="left" >
				<b style="color:555555;">Login:</b>
			</td>
			<td width="65%" align="left" >
				<input class="simple_input" type="text" name="login" maxlength="255" size="20" >
			</td>
		</tr>

		<tr height="22px">
			<td align="left" >
				<b style="color:555555;">Password:</b>
			</td>
			<td align="left" >
				<input class="simple_input" type="password" name="password" maxlength="255" size="20" >
			</td>
		</tr>

		<tr height="44px">
			<td align="center" >
				&nbsp;
			</td>
			<td align="left" >
				&nbsp;<input type="submit" class="simple_button" value="   Login   ">
			</td>
		</tr>
	</td>
	</tr>
</table>
</center>
</form>

<script language="javascript">
        document.login_form.login.focus();
</script>

</body>
</html>

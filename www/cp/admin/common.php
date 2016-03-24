<?php

function html_header($type) {

	global $nocache;

echo "
<html>
<head>
<title>Hosting.ai: Admin Panel</title>
".$nocache."
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=koi8-r\">
<link href=\"../include/style.php\" rel=\"stylesheet\" type=\"text/css\">
<script type=\"text/javascript\" src=\"../include/java_script.php\"></script>
</head>
<body class=\"".$type."\">
";

}


function html_end() {

echo "
</body>
</html>
";

}

function entire_html() {

	html_header("body");

	include("top.php");

echo "

<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">
<tr width=\"100%\">
<td width=\"5px\">
</td>
<td>";

	if ($_SESSION["adminpanel_error"] != "")
		echo $_SESSION["adminpanel_error"]."<br>";

	body_page();

echo "
</td>
</tr>
</table>";

include("bottom.php");

	html_end();
}

?>

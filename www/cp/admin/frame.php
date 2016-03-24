<?php
	require_once ("header.php");

	echo "
<HTML><HEAD><TITLE>VHS: Admin Control Panel</TITLE></HEAD>
<FRAMESET ROWS=\"30px,*\" BORDER=\"0\" color=\"#AA0000\">
<FRAME NAME=\"menu\" SRC=\"top_strip.php\">
	<FRAMESET COLS=\"236px,*\" BORDER=\"0\" color=\"#AA0000\">
	<FRAME NAME=\"menu\" SRC=\"menu.php\">
	<FRAME NAME=\"body\" SRC=\"server_status.php\">
	</FRAMESET>
</FRAMESET>
</HTML>
";
?>

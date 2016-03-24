<?php
//	error_reporting (E_ALL ^E_NOTICE ^E_WARNING);
	error_reporting (E_ALL);

	$INCLUDE_PATH="../cp/include/";
	require_once ($INCLUDE_PATH."define.php");
	require_once ($INCLUDE_PATH."tools.php");
	include_once ($INCLUDE_PATH."sql_core.php");

	$SQL = new PGSQL;

	$requested_domain = $_SERVER["HTTP_HOST"];
	$url_to_go = "hosting.ai";
	$title = "VHS: Redirection Service";
	$frame = false;

	$SQL->connect();

	$result = $SQL->exec_query("SELECT * FROM redirect_table WHERE domain='".$requested_domain."'");
	if ($result == FALSE) {
		echo "Can't get redirect information: ".$SQL->get_error();
		$SQL->disconnect();
		exit;
	}
	$data = $SQL->get_object($result);

	if ($data != "") {
		$url_to_go = $data->pointer;
		$title = $data->title;
		$frame = $data->frameset=="t"?true:false;
	}

	$SQL->disconnect();

	if ($frame) {
		echo "<HTML><HEAD><TITLE>".$title."</TITLE></HEAD>
<FRAMESET ROWS=\"100%\" BORDER=\"0\" color=\"#000000\">
<FRAME NAME=\"".create_unique_id()."\" SRC=\"http://".$url_to_go."\">
</FRAMESET>
</HTML>";
	}
	else {
		Header("Location: http://".$url_to_go."\n\n");
	}

?>
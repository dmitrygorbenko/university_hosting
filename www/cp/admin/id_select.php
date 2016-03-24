<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		$HM = new Hosting_Manage();

		$result = $HM->HC->connect();
		if ($result["error"] == TRUE) {
			echo "Can't connect to Remote Server: ".$result["mess"];
			return;
		}

		$result = $HM->HC->fetch_ids();
		if ($result["error"] == TRUE) {
			echo "Can't fetch ids: ".$_SESSION["adminpanel_error"];
			$HM->HC->disconnect();
			return;
		}

		$ids = array();

		$str = explode("\n", $result["result"]);
		for ($i = 0; $i < count($str); $i++) {
			$id = explode(" ", $str[$i]);

			$tmp["login"] = $id[0];
			$tmp["uid"] = $id[1];
			$tmp["gid"] = $id[2];

			array_push($ids, $tmp);
		}

		$HM->HC->disconnect();

		echo "
		<form name=\"id_form\">

		<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
		<tr valign=\"center\" height=\"62\" width=\"100%\">
			<td colspan=\"5\" width=\"100%\">
				<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
				<tr valign=\"center\" height=\"62\" width=\"100%\">
					<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
						<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
					</td>
					<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
						Select ID
					</td>
					<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
						<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr valign=\"top\">
			<td width=\"6%\" class=\"content\">&#035;</td>
			<td width=\"54%\" class=\"content\">Login</td>
			<td width=\"15%\" class=\"content\">Uid</td>
			<td width=\"15%\" class=\"content\">Gid</td>
			<td width=\"10%\" class=\"content\">Select</td>
		</tr>
		";

		for ($i = 0; $i < count($ids); $i++) {
			echo "
				<tr>
					<td class=\"content2\">".($i+1)."</td>
					<td class=\"content2\">".$ids[$i]["login"]."</td>
					<td class=\"content2\">".$ids[$i]["uid"]."</td>
					<td class=\"content2\">".$ids[$i]["gid"]."</td>
					<td class=\"content2\"><a href=\"javascript:CopyID('".$ids[$i]["uid"]."', '".$ids[$i]["gid"]."')\">Select</a></td>
				</tr>
			";
		}

		echo "
		</table>
		</form>
		";
	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

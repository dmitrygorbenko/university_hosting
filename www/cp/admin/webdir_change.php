<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		$HM = new Hosting_Manage;

		$id = safe_get("id", "GET", 255);
		if ($id == "") {
			echo "Can't find id !";
			return;
		}

		$data = $HM->WC->fetch_webdir($id);
		if ($data == FALSE) {
			echo "Can't fetch webdir: ".$_SESSION["adminpanel_error"];
			return;
		}

		$zones = $HM->ZC->fetch_all_zones();
		if ($zones == FALSE) {
			echo "Can't fetch all zones: ".$_SESSION["adminpanel_error"];
			return;
		}

		echo "
			<form action=\"webdir_action.php?act=change_webdir\" method=\"POST\" name=\"webdir_manage\">
			<input name=\"id\" value=\"".$data->id_table."\" size=\"60\" maxlength=\"255\" type=\"hidden\">
			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"center\" height=\"62\" width=\"100%\">
				<td colspan=\"5\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Web Dir: <strong>".$data->domain."</strong>
						</td>
						<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
							<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class=\"content\" colspan=\"2\">
					If Domain will owns by some Hosting Zone then RootDir will be prefixed by Hosting Zone's www dir.<br>
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Domain:
				</td>
				<td NOWRAP>
					<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"domain\" value=\"".$data->domain_lite."\">
					&nbsp;<strong>.</strong>&nbsp;
					<select name=\"id_zone\" class=\"simple_select\">
						<option value=\"none\">----------------</option>
			";

		for ($i = 0; $i < $HM->ZC->SQL->get_num_rows($zones); $i++) {
			$zone_data = $HM->ZC->SQL->get_object($zones);

			echo "<option ".($zone_data->id_table==$data->id_zone_table?"selected":"")." value=\"".$zone_data->id_table."\">".$zone_data->name."</option>\n";
		}

		echo "
					</select>
				</td class=\"content2\">
			</tr>
			<tr>
				<td class=\"content2\">
					Root Dir:
				</td>
				<td>
					<input class=\"simple_input\" size=\"80\" maxlength=\"255\" type=\"text\" name=\"rootdir\" value=\"".$data->rootdir."\">
				</td class=\"content2\">
			</tr>
			<tr height=\"40px\">
				<td>
					&nbsp;
				</td>
				<td>
					<input class=\"simple_button\" type=\"submit\" value=\"Apply changes\">
				</td>
			</tr>
			";
		echo "</table>
			</form>";
	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

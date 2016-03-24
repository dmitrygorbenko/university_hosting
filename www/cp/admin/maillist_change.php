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

		$data = $HM->MC->fetch_maillist($id);
		if ($data == FALSE) {
			echo "Can't fetch mailing list: ".$_SESSION["adminpanel_error"];
			return;
		}

		if ($data->id_zone_table != "") {
			$zone = $HM->ZC->fetch_zone($data->id_zone_table);
			if ($zone == FALSE) {
				echo "Can't fetch zone: ".$_SESSION["adminpanel_error"];
				return;
			}
			$append = "of <a href=\"zone_change.php?id=".$zone->id_table."\" class=\"title\">".$zone->name."</a> Hosting Zone";
		}
		else {
			$zone = FALSE;
			$append = "";
		}

		$zones = $HM->ZC->fetch_all_zones();
		if ($zones == FALSE) {
			echo "Can't fetch all zones: ".$_SESSION["adminpanel_error"];
			return;
		}

		echo "
			<form action=\"maillist_action.php?act=change_maillist\" method=\"POST\" name=\"maillist_manage\">
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
							Mailing Lists: <strong>".$data->title."</strong> ".$append."
						</td>
						<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
							<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Zone:
				</td>
				<td>
					&nbsp;<select name=\"zone_id\" class=\"simple_select\">
					<option ".($data->id_zone_table==""?"selected":"")." value=\"none\">Alone (non zone)</option>\n
					";


		for ($i = 0; $i < $HM->ZC->SQL->get_num_rows($zones); $i++) {
			$zone_data = $HM->ZC->SQL->get_object($zones);

			echo "<option ".($zone_data->id_table==$data->id_zone_table?"selected":"")." value=\"".$zone_data->id_table."\">".$zone_data->name."</option>\n";
		}

		echo "
					</select>
				</td>
			</tr>
			<tr height=\"10\">
				<td colspan=\"2\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td width=\"10%\" class=\"content2\" NOWRAP>
					List Title:
				</td>
				<td width=\"90%\">
					<input name=\"title\" value=\"".$data->title."\" class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					List Members:
				</td>
				<td>
					<textarea cols=\"80\" rows=\"4\" name=\"member_list\" wrap=\"physical\" class=\"textinput\">".$data->list_member."</textarea>
				</td>
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

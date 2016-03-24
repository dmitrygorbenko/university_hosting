<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		$zone_id = safe_get("zone_id", "POST", 255);
		$title = safe_get("title", "POST", 255);
		$member_list = pure_get("member_list", "POST");

		$HM = new Hosting_Manage;

		$zones = $HM->ZC->fetch_all_zones();
		if ($zones == FALSE) {
			echo "Can't fetch all zones: ".$_SESSION["adminpanel_error"];
			return;
		}

		echo "
			<form action=\"maillist_action.php?act=add_maillist\" method=\"POST\" name=\"maillist_manage\">
			<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\">
			<tr>
				<td colspan=\"2\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Create Mailing List
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
					Fields marked <font color=\"#FF0000\">*</font> are required. Memebers separated by commas.
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Zone:
				</td>
				<td>
					&nbsp;<select name=\"zone_id\" class=\"simple_select\">
					<option selected value=\"none\">Alone (non zone)</option>\n
					";


		for ($i = 0; $i < $HM->ZC->SQL->get_num_rows($zones); $i++) {
			$data = $HM->ZC->SQL->get_object($zones);

			echo "<option ".($zone_id==$data->id_table?"selected":"")." value=\"".$data->id_table."\">".$data->name."</option>\n";
		}

		echo "
					</select>
				</td>
			</tr>
			<tr height=\"10\">
				<td colspan=\"2\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					<font color=\"#FF0000\">*</font>List Title:
				</td>
				<td>
					<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"title\" value=\"".$title."\">
				</td class=\"content2\">
			</tr>
			<tr>
				<td class=\"content2\">
					<font color=\"#FF0000\">*</font>List Members:
				</td>
				<td>
					<textarea cols=\"80\" rows=\"4\" name=\"member_list\" wrap=\"physical\" class=\"textinput\">".base64_decode($member_list)."</textarea>
				</td>
			</tr>
			<tr height=\"40px\">
				<td colspan=\"2\">
					<input class=\"simple_button\" type=\"submit\" value=\"Create Mailing List\">
				</td>
			</tr>
			</table>
			</form>
		<script language=\"javascript\">
			document.maillist_manage.title.focus();
		</script>

		";

	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

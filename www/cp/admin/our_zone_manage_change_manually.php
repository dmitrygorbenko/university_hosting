<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		global $HOSTING_IP;

		$id_zone = safe_get("id_zone", "GET", 255);

		if ($id_zone == "") {
			echo "Can't find id_zone !";
			return;
		}

		$HM = new Hosting_Manage;

		$zone = $HM->ZC->fetch_our_zone($id_zone);
		if ($zone == FALSE) {
			echo "Can't fetch our zone: ".$_SESSION["adminpanel_error"];
			return;
		}

		$zone_plain_text = $HM->ZC->fetch_our_zone_plain_text($id_zone);

		if ($zone_plain_text == FALSE) {
			echo "Can't fetch our zone plain text: ".$_SESSION["adminpanel_error"];
			return;
		}

		echo "
		<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"center\" height=\"62\" width=\"100%\">
				<td colspan=\"6\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Manage Hosting Zone <a class=\"title\" href=\"our_zone_change.php?id=".$id_zone."\">".$zone->name."</a> of client <a class=\"title\" href=\"client_change.php?id=".$client->id_table."\">".$client->login."</a>
						</td>
						<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
							<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr valign=\"top\">
				<td colspan=\"6\" width=\"100%\">
					<form action=\"our_zone_manage_action.php\" method=POST name=\"create_record\">
					<input type=\"hidden\" name=\"manual\" value=\"on\">
					<input type=\"hidden\" name=\"id_zone\" value=\"".$id_zone."\">
					<input type=\"button\" class=\"simple_button\" onClick=\"document.location='our_zone_manage_change.php?id_zone=".$id_zone."'\" value=\"Script Mode\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td colspan=\"6\" width=\"100%\">
					<textarea name=\"zone_content\" cols=\"100\" rows=\"25\" class=\"textinput_plain\">".$zone_plain_text."</textarea>
				</td>
			</tr>
			<tr valign=\"top\">
				<td colspan=\"6\" width=\"100%\">
					<input type=\"submit\" class=\"simple_button\" value=\"Apply Changes\">
					</form>
				</td>
			</tr>
		</table>
		<br>
		<br>
		";
	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

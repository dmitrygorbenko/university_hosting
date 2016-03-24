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

		$our_zone = $HM->ZC->fetch_our_zone($id);
		if ($our_zone == FALSE) {
			echo "Can't fetch our zone: ".$_SESSION["adminpanel_error"];
			return;
		}

		$hosting_zones = $HM->ZC->fetch_our_zone_hosting_zones($id);
		if ($hosting_zones == FALSE) {
			echo "Can't fetch our zone's hosting zones: ".$_SESSION["adminpanel_error"];
			return;
		}
		$hosting_zones_empty = TRUE;

		echo "
		<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">
		<tr>
		<td width=\"100%\" colspan=\"2\">
			<table width=\"100%\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"center\" height=\"62\" width=\"100%\">
				<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
					<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
				</td>
				<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
					Our Zone <strong>".$our_zone->name."</strong>
				</td>
				<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
					<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
				</td>
			</tr>
			</table>
		</td>
		</tr>

		<tr>
		<td width=\"50%\" valign=\"top\" align=\"left\">

			<form action=\"our_zone_action.php?act=change_our_zone\" method=\"POST\" name=\"zone_manage\">
			<input name=\"id\" value=\"".$id."\" size=\"40\" maxlength=\"255\" type=\"hidden\">
			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"top\">
				<td class=\"content2\">
					Name:
				</td>
				<td>
					<input name=\"zone_name\" value=\"".$our_zone->name."\" class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\"><br>
				</td>
			</tr>
			<tr height=\"40\">
				<td>
					&nbsp;
				</td>
				<td>
					<input class=\"simple_button\" type=\"submit\" value=\"Apply changes\">
				</td>
			</tr>
			</table>
			</form>

			<form name=\"subdomain\" method=\"POST\" action=\"our_zone_manage_change.php?id_zone=".$id."\">
				<input type=\"submit\" value=\"Manage Our Zone\" class=\"simple_button\">
			</form>

		</td>
		<td width=\"50%\" valign=\"top\" align=\"left\">

			<form name=\"subdomain_remove_form\" method=\"POST\" action=\"subdomain_action.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
  			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"2%\" class=\"content\">&nbsp;</td>
				<td width=\"47%\" class=\"content\">Zone</td>
				<td width=\"46%\" class=\"content\">Serive Type</td>
			</tr>
			<span id=\"zones_count\" title=\"".$HM->ZC->SQL->get_num_rows($hosting_zones)."\"></span>

			";

		for ($i = 0; $i < $HM->CC->SQL->get_num_rows($hosting_zones); $i++) {
			$data = $HM->CC->SQL->get_object($hosting_zones);

			echo "<tr>
				<span id=\"zone_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"zone_checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"zone_change.php?id=".$data->id_table."\">".$data->name."</a></td>
				<td class=\"content_3\">".$data->service_type."</td>
			</tr>";
			$hosting_zones_empty = FALSE;
		}

		if ($hosting_zones_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"4\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"4\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
							<a href=\"javascript:checkall_zones(1)\">Select all</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href=\"javascript:checkall_zones(0)\">Clear all</a>
						</td>
						<td align=\"right\" NOWRAP>
							<input onClick=\"remove_zones()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
							</form>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"zone\" method=\"POST\" action=\"zone_add.php\">
							<input type=\"hidden\" name=\"zone_name\" value=\".".$our_zone->name."\">
							<input type=\"submit\" value=\"Create Zone\" class=\"simple_button\">
							</form>
						</td>
					</tr>
					</table>
				</td>
			</tr>";

		echo "
			</table>


		</td>
		</tr>
		</table>";
	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

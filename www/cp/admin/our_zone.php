<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		$HM = new Hosting_Manage;

		$our_zones = $HM->ZC->fetch_all_our_zones();
		if ($our_zones == FALSE) {
			echo "Can't fetch all our zones: ".$_SESSION["adminpanel_error"];
			return;
		}
		$our_zones_empty = TRUE;

		echo "
			<form name=\"our_zone_remove_form\" method=\"POST\" action=\"our_zone_action.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"center\" height=\"62\" width=\"100%\">
				<td colspan=\"4\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Our Zones
						</td>
						<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
							<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"2%\" class=\"content\">&nbsp;</td>
				<td width=\"84%\" class=\"content\">Our Zone Name</td>
				<td width=\"10%\" class=\"content\">Hosting Zones</td>
			</tr>
			<span id=\"our_zones_count\" title=\"".$HM->ZC->SQL->get_num_rows($our_zones)."\"></span>";

		for ($i = 0; $i < $HM->ZC->SQL->get_num_rows($our_zones); $i++) {
			$data = $HM->ZC->SQL->get_object($our_zones);
			echo "<tr>
				<span id=\"our_zone_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"our_zone_checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"our_zone_change.php?id=".$data->id_table."\">".$data->name."</a></td>
				<td class=\"content_3\">".$data->count."</td>
			</tr>";
			$our_zones_empty = FALSE;
		}

		if ($our_zones_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"4\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"4\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
							<a href=\"javascript:checkall_our_zones(1)\">Select all</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href=\"javascript:checkall_our_zones(0)\">Clear all</a>
						</td>
						<td align=\"right\" NOWRAP>
							<input onClick=\"remove_our_zones()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
							</form>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"our_zone\" method=\"POST\" action=\"our_zone_add.php\">
							<input type=\"submit\" value=\"Create Our Zone\" class=\"simple_button\">
							</form>
						</td>
					</tr>
					</table>
				</td>
			</tr>";

		echo "</table>";

	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

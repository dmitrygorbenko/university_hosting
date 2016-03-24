<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		$HM = new Hosting_Manage;

		$id_zone = safe_get("id_zone", "GET", 255);
		$append = "";

		if ($id_zone == "" || $id_zone == "none") {
			$redirectors = $HM->WC->fetch_all_redirectors();
			if ($redirectors == FALSE) {
				echo "Can't fetch all redirectors: ".$_SESSION["adminpanel_error"];
				return;
			}
			$append = "from Hosting Spool";
		}
		elseif ($id_zone == "alone") {
			$redirectors = $HM->WC->fetch_alone_redirectors();
			if ($redirectors == FALSE) {
				echo "Can't fetch alone redirectors: ".$_SESSION["adminpanel_error"];
				return;
			}
			$append = "from non-Client Spool";
		}
		else {
			$redirectors = $HM->WC->fetch_all_redirectors_of_zone($id_zone);
			if ($redirectors == FALSE) {
				echo "Can't fetch all zone's redirectors: ".$_SESSION["adminpanel_error"];
				return;
			}

			$zone_data = $HM->ZC->fetch_zone($id_zone);
			if ($zone_data == FALSE) {
				echo "Can't fetch zone: ".$_SESSION["adminpanel_error"];
				return FALSE;
			}

			$append = "from <a href=\"zone_change.php?id=".$id_zone."\" class=\"title\">".$zone_data->name."</a> zone";
		}

		$redirectors_empty = TRUE;
		echo "
			<form name=\"redirect_remove_form\" method=\"POST\" action=\"redirect_action.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"center\" height=\"62\" width=\"100%\">
				<td colspan=\"6\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Select Web Redirector ".$append."
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
				<td width=\"30%\" class=\"content\">Domain</td>
				<td width=\"30%\" class=\"content\">Redirect To</td>
				<td width=\"19%\" class=\"content\">Redirect Title</td>
				<td width=\"5%\" class=\"content\">Frameset Method</td>
			</tr>
			<span id=\"redirectors_count\" title=\"".$HM->WC->SQL->get_num_rows($redirectors)."\"></span>
			";

		for ($i = 0; $i < $HM->WC->SQL->get_num_rows($redirectors); $i++) {
			$data = $HM->WC->SQL->get_object($redirectors);

			echo "<tr>
				<span id=\"redirector_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"redirector_checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"redirect_change.php?id=".$data->id_table."\">".$data->domain."</a></td>
				<td class=\"content_3\">".$data->pointer."</td>
				<td class=\"content_3\">".substr($data->title, 0, 40).(strlen($data->title)>40?"...":"")."</td>
				<td class=\"content_3\">".($data->frameset=="t"?"<font color=\"#007700\">Yes</font>":"<font color=\"#AA0000\">No</font>")."</td>
			</tr>";
			$redirectors_empty = FALSE;
		}

		if ($redirectors_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"6\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"6\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
							<a href=\"javascript:checkall_redirectors(1)\">Select all</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href=\"javascript:checkall_redirectors(0)\">Clear all</a>
						</td>
						<td align=\"right\" NOWRAP>
							<input onClick=\"remove_redirectors()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
							</form>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"redirect\" method=\"POST\" action=\"redirect_add.php\">
							<input type=\"submit\" value=\"Create Web Redirector\" class=\"simple_button\">
							</form>
						</td>
					</tr>
					</table>
				</td>
			</tr>";

		echo "</table>
			</form>";

	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

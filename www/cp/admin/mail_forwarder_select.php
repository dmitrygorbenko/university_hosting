<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		$HM = new Hosting_Manage;

		$id_zone = safe_get("id_zone", "GET", 255);
		$append = "";

		if ($id_zone == "" || $id_zone == "none") {
			$forwarders = $HM->MC->fetch_all_mail_forwarders();
			if ($forwarders == FALSE) {
				echo "Can't fetch all mail forwarders: ".$_SESSION["adminpanel_error"];
				return;
			}
			$append = "from Hosting Spool";
		}
		elseif ($id_zone == "alone") {
			$forwarders = $HM->MC->fetch_alone_mail_forwarders();
			if ($forwarders == FALSE) {
				echo "Can't fetch alone mail forwarders: ".$_SESSION["adminpanel_error"];
				return;
			}
			$append = "from non-Client Spool";
		}
		else {
			$forwarders = $HM->MC->fetch_all_mail_forwarders_of_zone($id_zone);
			if ($forwarders == FALSE) {
				echo "Can't fetch all zone's mail forwarders: ".$_SESSION["adminpanel_error"];
				return;
			}

			$zone_data = $HM->ZC->fetch_zone($id_zone);
			if ($zone_data == FALSE) {
				echo "Can't fetch zone: ".$_SESSION["adminpanel_error"];
				return FALSE;
			}

			$append = "from <a href=\"zone_change.php?id=".$id_zone."\" class=\"title\">".$zone_data->name."</a> zone";
		}

		$forwarders_empty = TRUE;
		echo "
			<form name=\"email_forwarder_remove_form\" method=\"POST\" action=\"mail_forwarder_action.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"center\" height=\"62\" width=\"100%\">
				<td colspan=\"5\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Select E-Mail Forwarder ".$append."
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
				<td width=\"42%\" class=\"content\">Forward From E-mail</td>
				<td width=\"42%\" class=\"content\">Forward To</td>
				<td width=\"10%\" class=\"content\">Enabled</td>
			</tr>
			<span id=\"email_forwarder_count\" title=\"".$HM->MC->SQL->get_num_rows($forwarders)."\"></span>
			";

		for ($i = 0; $i < $HM->MC->SQL->get_num_rows($forwarders); $i++) {
			$data = $HM->MC->SQL->get_object($forwarders);

			echo "<tr>
				<span id=\"email_forwarder_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"email_forwarder_checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"mail_forwarder_change.php?id=".$data->id_table."\">".$data->email."</a></td>
				<td class=\"content_3\">".$data->forward_address."</td>
				<td class=\"content_3\">".($data->forward_do=="t"?"<font color=\"#007700\">Yes</font>":"<font color=\"#AA0000\">No</font>")."</td>
			</tr>";
			$forwarders_empty = FALSE;
		}

		if ($forwarders_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"5\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"5\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
							<a href=\"javascript:checkall_email_forwarders(1)\">Select all</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href=\"javascript:checkall_email_forwarders(0)\">Clear all</a>
						</td>
						<td align=\"right\" NOWRAP>
							<input onClick=\"remove_email_forwarders()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
							</form>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"mail_forwarder\" method=\"POST\" action=\"mail_forwarder_add.php\">
							<input type=\"submit\" value=\"Create E-Mail Forwarder\" class=\"simple_button\">
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

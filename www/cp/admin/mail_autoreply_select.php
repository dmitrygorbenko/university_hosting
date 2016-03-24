<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		$HM = new Hosting_Manage;

		$id_zone = safe_get("id_zone", "GET", 255);
		$append = "";

		if ($id_zone == "" || $id_zone == "none") {
			$autoreplies = $HM->MC->fetch_all_mail_autoreplies();
			if ($autoreplies == FALSE) {
				echo "Can't fetch all mail autoreplies: ".$_SESSION["adminpanel_error"];
				return;
			}
			$append = "from Hosting Spool";
		}
		elseif ($id_zone == "alone") {
			$autoreplies = $HM->MC->fetch_alone_mail_autoreplies();
			if ($autoreplies == FALSE) {
				echo "Can't fetch alone mail autoreplies: ".$_SESSION["adminpanel_error"];
				return;
			}
			$append = "from non-Client Spool";
		}
		else {
			$autoreplies = $HM->MC->fetch_all_mail_autoreplies_of_zone($id_zone);
			if ($autoreplies == FALSE) {
				echo "Can't fetch all zone's mail autoreplies: ".$_SESSION["adminpanel_error"];
				return;
			}

			$zone_data = $HM->ZC->fetch_zone($id_zone);
			if ($zone_data == FALSE) {
				echo "Can't fetch zone: ".$_SESSION["adminpanel_error"];
				return FALSE;
			}

			$append = "from <a href=\"zone_change.php?id=".$id_zone."\" class=\"title\">".$zone_data->name."</a> zone";
		}

		$autoreplies_empty = TRUE;
		echo "
			<form name=\"email_autoreply_remove_form\" method=\"POST\" action=\"mail_autoreply_action.php?act=remove\">
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
							Select E-Mail Auto Answer ".$append."
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
				<td width=\"42%\" class=\"content\">E-mail</td>
				<td width=\"42%\" class=\"content\">Auto Answer Text</td>
				<td width=\"10%\" class=\"content\">Enabled</td>
			</tr>
			<span id=\"email_autoreply_count\" title=\"".$HM->MC->SQL->get_num_rows($autoreplies)."\"></span>
			";

		for ($i = 0; $i < $HM->MC->SQL->get_num_rows($autoreplies); $i++) {
			$data = $HM->MC->SQL->get_object($autoreplies);

			echo "<tr>
				<span id=\"email_autoreply_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"email_autoreply_checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"mail_autoreply_change.php?id=".$data->id_table."\">".$data->email."</a></td>
				<td class=\"content_3\">".substr($data->reply, 0, 80).(strlen($data->reply)>80?"&nbsp;...":"")."</td>
				<td class=\"content_3\">".($data->reply_do=="t"?"<font color=\"#007700\">Yes</font>":"<font color=\"#AA0000\">No</font>")."</td>
			</tr>";
			$autoreplies_empty = FALSE;
		}

		if ($autoreplies_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"5\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"5\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
							<a href=\"javascript:checkall_email_autoreplies(1)\">Select all</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href=\"javascript:checkall_email_autoreplies(0)\">Clear all</a>
						</td>
						<td align=\"right\" NOWRAP>
							<input onClick=\"remove_email_autoreplies()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
							</form>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"mail_autoreply\" method=\"POST\" action=\"mail_autoreply_add.php\">
							<input type=\"submit\" value=\"Create E-Mail Auto Answer\" class=\"simple_button\">
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

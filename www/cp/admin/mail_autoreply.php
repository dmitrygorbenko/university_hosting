<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		$HM = new Hosting_Manage;

		$id_client = safe_get("id_client", "GET", 255);
		if ($id_client == "" || $id_client == "none") {
			$zones = $HM->ZC->fetch_all_zones_for_mail_autoreplies();
			if ($zones == FALSE) {
				echo "Can't fetch all zones: ".$_SESSION["adminpanel_error"];
				return;
			}
		}
		else {
			$zones = $HM->ZC->fetch_all_zones_of_client_for_mail_autoreplies($id_client);
			if ($zones == FALSE) {
				echo "Can't fetch all zones of client: ".$_SESSION["adminpanel_error"];
				return;
			}
		}

		$autoreply_count = $HM->MC->get_email_autoreplies_count();
		if ($autoreply_count == FALSE) {
			echo "Can't get email autoreply count: ".$_SESSION["adminpanel_error"];
			return;
		}

		$alone_autoreply_count = $HM->MC->get_alone_email_autoreplies_count();
		if ($alone_autoreply_count == FALSE) {
			echo "Can't get alone email autoreply count: ".$_SESSION["adminpanel_error"];
			return;
		}

		$clients = $HM->CC->fetch_all_client_account_light();
		if ($clients == FALSE) {
			echo "Can't fetch all clients: ".$_SESSION["adminpanel_error"];
			return;
		}

		$zones_empty = TRUE;
		echo "
			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"center\" height=\"62\" width=\"100%\">
				<td colspan=\"4\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							E-Mail Auto Answer Manage: Select Hosting Zone
						</td>
						<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
							<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr valign=\"top\">
				<td width=\"100%\" colspan=\"4\">
					Client:
					&nbsp;&nbsp;&nbsp;&nbsp;
					<select id=\"client_select\" name=\"id_client\" class=\"simple_select\" onChange=\"update_page('mail_autoreply.php', 'client_select', 'id_client')\">
						<option value=\"none\">----------------</option>
			";

		for ($i = 0; $i < $HM->CC->SQL->get_num_rows($clients); $i++) {
			$data = $HM->CC->SQL->get_object($clients);

			echo "<option ".($data->id_table==$id_client?"selected":"")." value=\"".$data->id_table."\">".$data->login."</option>\n";
		}

		echo "
					</select>
					&nbsp;&nbsp;&nbsp;&nbsp;
					<a href=\"mail_autoreply_select.php?id_zone=none\" title=\"View All E-Mail Auto Answers\">View All E-Mail Auto Answers (".$autoreply_count->autoreplies_count.")</a>
					&nbsp;&nbsp;&nbsp;&nbsp;
					<a href=\"mail_autoreply_select.php?id_zone=alone\" title=\"View non-Client E-Mail Auto Answers\">non-Client E-Mail Auto Answers (".$alone_autoreply_count->autoreplies_count.")</a>
				</td>
			</tr>
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"47%\" class=\"content\">Zone</td>
				<td width=\"53%\" class=\"content\">Client</td>
				<td width=\"13%\" class=\"content\">Auto Answers Count</td>
			</tr>
			";

		for ($i = 0; $i < $HM->ZC->SQL->get_num_rows($zones); $i++) {
			$data = $HM->ZC->SQL->get_object($zones);
			echo "<tr>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\"><a href=\"mail_autoreply_select.php?id_zone=".$data->id_table."\">".$data->name."</a></td>
				<td class=\"content_3\"><a href=\"client_change.php?id=".$data->id_client_table."\">".$data->client."</a></td>
				<td class=\"content_3\">".$data->autoreply_count."</td>
			</tr>";
			$zones_empty = FALSE;
		}

		if ($zones_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"4\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"4\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
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

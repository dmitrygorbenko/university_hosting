<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		$HM = new Hosting_Manage;

		$id_client = safe_get("id_client", "GET", 255);
		if ($id_client == "" || $id_client == "none") {
			$zones = $HM->ZC->fetch_all_zones();
			if ($zones == FALSE) {
				echo "Can't fetch all zones: ".$_SESSION["adminpanel_error"];
				return;
			}
		}
		else {
			$zones = $HM->ZC->fetch_all_zones_of_client($id_client);
			if ($zones == FALSE) {
				echo "Can't fetch all zones: ".$_SESSION["adminpanel_error"];
				return;
			}
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
				<td colspan=\"5\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Manage Hosting Zones: Select Hosting Zone
						</td>
						<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
							<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr valign=\"top\">
				<td width=\"100%\" colspan=\"5\">
					Client:
					&nbsp;&nbsp;&nbsp;&nbsp;
					<select id=\"client_select\" name=\"id_client\" class=\"simple_select\" onChange=\"update_page('zone.php', 'client_select', 'id_client')\">
						<option value=\"none\">----------------</option>
			";

		for ($i = 0; $i < $HM->CC->SQL->get_num_rows($clients); $i++) {
			$data = $HM->CC->SQL->get_object($clients);

			echo "<option ".($data->id_table==$id_client?"selected":"")." value=\"".$data->id_table."\">".$data->login."</option>\n";
		}

		echo "
					</select>
				</td>
			</tr>
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"24%\" class=\"content\">Zone</td>
				<td width=\"23%\" class=\"content\">Client</td>
				<td width=\"23%\" class=\"content\">Domain Type</td>
				<td width=\"23%\" class=\"content\">Serive Type</td>
			</tr>
			";

		for ($i = 0; $i < $HM->ZC->SQL->get_num_rows($zones); $i++) {
			$data = $HM->ZC->SQL->get_object($zones);
			echo "<tr>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\"><a href=\"zone_manage_change.php?id_zone=".$data->id_table."\">".$data->name."</a></td>
				<td class=\"content_3\"><a href=\"client_change.php?id=".$data->id_client_table."\">".$data->client."</a></td>
				<td class=\"content_3\">".$data->zone_type."</td>
				<td class=\"content_3\">".$data->service_type."</td>
			</tr>";
			$zones_empty = FALSE;
		}

		if ($zones_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"5\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"5\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"zone\" method=\"POST\" action=\"zone_add.php\">
							<input type=\"submit\" value=\"Create Zone\" class=\"simple_button\">
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

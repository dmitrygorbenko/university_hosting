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

		$data = $HM->FC->fetch_mysql_account($id);
		if ($data == FALSE) {
			echo "Can't fetch mysql: ".$_SESSION["adminpanel_error"];
			return;
		}

		$zones = $HM->ZC->fetch_all_zones();
		if ($zones == FALSE) {
			echo "Can't fetch all zones: ".$_SESSION["adminpanel_error"];
			return;
		}

		echo "
			<form action=\"mysql_action.php?act=change_mysql\" method=\"POST\" name=\"mysql_manage\">
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
							FTP User: <strong>".$data->login."</strong>
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
					Leave password filed empty to save one.<br>
					Symbol '~' in Root Dir means zone's www dir (only if login will owns by some Hosting Zone of client).<br>
					If login will not owns by some Hosting Zone, only when you should fill ID fileds.
				</td>
			</tr>
			<tr valign=\"top\">
				<td width=\"10%\" class=\"content2\" NOWRAP>
					Login:
				</td>
				<td width=\"90%\">
					<input name=\"login\" value=\"".$data->login."\" class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Hosting Zone:
				</td>
				<td>
					<select name=\"id_zone\" class=\"simple_select\">
						<option value=\"none\">----------------</option>
			";

		for ($i = 0; $i < $HM->ZC->SQL->get_num_rows($zones); $i++) {
			$zone = $HM->ZC->SQL->get_object($zones);

			echo "<option ".($zone->id_table==$data->id_zone_table?"selected":"")." value=\"".$zone->id_table."\">".$zone->name."</option>\n";
		}

		echo "
					</select>
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Password:
				</td>
				<td>
					<input name=\"password\" value=\"\" class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"password\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Again:
				</td>
				<td>
					<input name=\"password2\" value=\"\" class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"password\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Root Dir:
				</td>
				<td>
					<input name=\"rootdir\" value=\"".$data->rootdir."\" class=\"simple_input\" size=\"80\" maxlength=\"255\" type=\"text\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					ID:
				</td>
				<td NOWRAP>
					<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
					<tr>
						<td align=\"right\">
							&nbsp;&nbsp;
							UID:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"uid\" value=\"".$data->uid."\">
						</td>
						<td align=\"right\">
							&nbsp;&nbsp;
							GID:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"gid\" value=\"".$data->gid."\">
						</td>
						<td align=\"right\">
							&nbsp;&nbsp;
							<input type=\"button\" class=\"simple_button\" value=\"Select ID\" onClick=\"javascript:OpenID()\">
						</td>
					</tr>
					</table>
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

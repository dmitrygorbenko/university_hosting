<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		$HM = new Hosting_Manage;

		$login = safe_get("login", "POST", 255);
		$id_zone = safe_get("id_zone", "POST", 255);
		$rootdir = safe_get("rootdir", "POST", 255);
		$uid = safe_get("uid", "POST", 255);
		$gid = safe_get("gid", "POST", 255);

		$zones = $HM->ZC->fetch_all_zones();
		if ($zones == FALSE) {
			echo "Can't fetch all zones: ".$_SESSION["adminpanel_error"];
			return;
		}

		echo "
			<form action=\"ftp_action.php?act=add_ftp\" method=\"POST\" name=\"ftp_manage\">
			<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\">
			<tr>
				<td colspan=\"2\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Create FTP User
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
					All fileds are required.<br>
					Symbol '~' in Root Dir means zone's www dir (only if Login will owns by some Hosting Zone of client).<br>
					If Login will not owns by some Hosting Zone, only when you should fill ID fileds.
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Login:
				</td>
				<td>
					<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"login\" value=\"".$login."\">
				</td class=\"content2\">
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
			$data = $HM->ZC->SQL->get_object($zones);

			echo "<option ".($data->id_table==$id_zone?"selected":"")." value=\"".$data->id_table."\">".$data->name."</option>\n";
		}

		echo "
					</select>
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Password:
				</td>
				<td>
					<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"password\" name=\"password\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Again:
				</td>
				<td>
					<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"password\" name=\"password2\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Root Dir:
				</td>
				<td>
					<input class=\"simple_input\" size=\"80\" maxlength=\"255\" type=\"text\" name=\"rootdir\" value=\"".($rootdir?$rootdir:"~")."\">
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
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"uid\" value=\"".$uid."\">
						</td>
						<td align=\"right\">
							&nbsp;&nbsp;
							GID:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"gid\" value=\"".$gid."\">
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
				<td colspan=\"2\">
					<input class=\"simple_button\" type=\"submit\" value=\"Create FTP User\">
				</td>
			</tr>
			</table>
			</form>
		<script language=\"javascript\">
			document.ftp_manage.login.focus();
		</script>

		";

	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

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

		$data = $HM->MC->fetch_mail_alias($id);
		if ($data == FALSE) {
			echo "Can't fetch email alias: ".$_SESSION["adminpanel_error"];
			return;
		}

		if ($data->id_zone_table != "") {
			$zone = $HM->ZC->fetch_zone($data->id_zone_table);
			if ($zone == FALSE) {
				echo "Can't fetch zone: ".$_SESSION["adminpanel_error"];
				return;
			}
			$append = "of <a href=\"zone_change.php?id=".$zone->id_table."\" class=\"title\">".$zone->name."</a> Hosting Zone";
		}
		else {
			$zone = FALSE;
			$append = "";
		}

		echo "
			<form action=\"mail_alias_action.php?act=change_email_alias\" method=\"POST\" name=\"mail_alias_manage\">
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
							E-mail Alias: <strong>".$data->email."</strong> ".$append."
						</td>
						<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
							<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr valign=\"top\">
				<td width=\"10%\" class=\"content2\" NOWRAP>
					E-mail:
				</td>
				<td width=\"90%\">
					<input name=\"email\" value=\"".$data->email."\" class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Alias List:
				</td>
				<td>
					<input name=\"alias\" value=\"".$data->alias."\" class=\"simple_input\" size=\"80\" maxlength=\"255\" type=\"text\">
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

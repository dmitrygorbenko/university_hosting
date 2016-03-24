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

		$data = $HM->MC->fetch_mail_account($id);
		if ($data == FALSE) {
			echo "Can't fetch email: ".$_SESSION["adminpanel_error"];
			return;
		}

		$forward_data = $HM->MC->fetch_forward_of_mail($data->login);
		if ($forward_data == FALSE) {
			echo "Can't fetch forward info: ".$_SESSION["adminpanel_error"];
			return;
		}

		$reply_data = $HM->MC->fetch_reply_of_mail($data->login);
		if ($reply_data == FALSE) {
			echo "Can't fetch reply info: ".$_SESSION["adminpanel_error"];
			return;
		}

		echo "
			<form action=\"mail_action.php?act=change_email\" method=\"POST\" name=\"mail_manage\">
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
							E-mail account: <strong>".$data->login."</strong>
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
					Leave password filed empty to save one
				</td>
			</tr>
			<tr valign=\"top\">
				<td width=\"10%\" class=\"content2\" NOWRAP>
					E-mail:
				</td>
				<td width=\"90%\">
					<input name=\"login\" value=\"".$data->login."\" class=\"simple_input\" size=\"60\" maxlength=\"255\" type=\"text\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Password:
				</td>
				<td>
					<input name=\"password\" value=\"\" class=\"simple_input\" size=\"60\" maxlength=\"255\" type=\"password\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Again:
				</td>
				<td>
					<input name=\"password2\" value=\"\" class=\"simple_input\" size=\"60\" maxlength=\"255\" type=\"password\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Name:
				</td>
				<td>
					<input name=\"name\" value=\"".$data->name."\" class=\"simple_input\" size=\"60\" maxlength=\"255\" type=\"text\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Maildir:
				</td>
				<td>
					<input name=\"maildir\" value=\"".$data->maildir."\" class=\"simple_input\" size=\"80\" maxlength=\"255\" type=\"text\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Home:
				</td>
				<td>
					<input name=\"home\" value=\"".$data->home."\" class=\"simple_input\" size=\"80\" maxlength=\"255\" type=\"text\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Forwarder Enabled:
				</td>
				<td>
					<input type=\"checkbox\" name=\"forward_do\" ".($forward_data->forward_do=="t"?" checked ":"").">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Forward To:
				</td>
				<td>
					<input name=\"forward\" value=\"".$forward_data->forward_address."\" class=\"simple_input\" size=\"60\" maxlength=\"255\" type=\"text\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Auto Answer Enabled:
				</td>
				<td>
					<input type=\"checkbox\" name=\"reply_do\" ".($reply_data->reply_do=="t"?" checked ":"").">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Auto Answer Text:
				</td>
				<td>
					<textarea name=\"reply_text\" wrap=\"physical\" class=\"textinput\" cols=\"60\" rows=\"4\">".$reply_data->reply."</textarea>
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

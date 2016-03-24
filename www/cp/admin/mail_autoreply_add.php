<?php
	require_once ("header.php");

	function body_page() {

		$email = safe_get("email", "POST", 255);
		$reply_do = safe_get("reply_do", "POST", 10);
		$reply_text = pure_get("reply_text", "POST");

		echo "
			<form action=\"mail_autoreply_action.php?act=add_mail_autoreply\" method=\"POST\" name=\"mail_autoreply_manage\">
			<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\">
			<tr>
				<td colspan=\"2\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Create E-Mail Auto Answer
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
					All fields are required
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					E-Mail:
				</td>
				<td>
					<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"email\" value=\"".$email."\">
				</td class=\"content2\">
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Auto Answer Enabled:
				</td>
				<td>
					<input type=\"checkbox\" name=\"reply_do\" ".($reply_do=="on"?" checked ":"").">
				</td>
			</tr>
			<tr valign=\"top\">
				<td class=\"content2\">
					Auto Answer Text:
				</td>
				<td>
					<textarea name=\"reply_text\" wrap=\"physical\" class=\"textinput\" cols=\"60\" rows=\"4\">".$reply_text."</textarea>
				</td>
			</tr>
			<tr height=\"40px\">
				<td colspan=\"2\">
					<input class=\"simple_button\" type=\"submit\" value=\"Create E-Mail Auto Answer\">
				</td>
			</tr>
			</table>
			</form>
		<script language=\"javascript\">
			document.mail_autoreply_manage.email.focus();
		</script>

		";

	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

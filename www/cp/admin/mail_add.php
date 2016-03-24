<?php
	require_once ("header.php");

	function body_page() {

		$login = safe_get("login", "POST", 255);
		$name = safe_get("name", "POST", 255);
		$forward = safe_get("forward", "POST", 255);
		$forward_do = safe_get("forward_do", "POST", 5);
		$reply_text = pure_get("reply_text", "POST");
		$reply_do = safe_get("reply_do", "POST", 5);

		echo "
			<form action=\"mail_action.php?act=add_mail\" method=\"POST\" name=\"mail_manage\">
			<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\">
			<tr>
				<td colspan=\"2\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Create E-Mail
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
					Fields marked <font color=\"#FF0000\">*</font> are required
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					<font color=\"#FF0000\">*</font>Login:
				</td>
				<td>
					<input class=\"simple_input\" size=\"60\" maxlength=\"255\" type=\"text\" name=\"login\" value=\"".$login."\">
				</td class=\"content2\">
			</tr>
			<tr>
				<td class=\"content2\">
					<font color=\"#FF0000\">*</font>Password:
				</td>
				<td>
					<input class=\"simple_input\" size=\"60\" maxlength=\"255\" type=\"password\" name=\"password\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					<font color=\"#FF0000\">*</font>Again:
				</td>
				<td>
					<input class=\"simple_input\" size=\"60\" maxlength=\"255\" type=\"password\" name=\"password2\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					<font color=\"#FF0000\">*</font>Name:
				</td>
				<td>
					<input class=\"simple_input\" size=\"60\" maxlength=\"255\" type=\"text\" name=\"name\" value=\"".$name."\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Forwarder Enabled:
				</td>
				<td>
					<input type=\"checkbox\" name=\"forward_do\" ".($forward_do==""?"":($forward_do=="on"?"checked":"")).">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Forward To:
				</td>
				<td>
					<input class=\"simple_input\" size=\"60\" maxlength=\"255\" type=\"text\" name=\"forward\" value=\"".$forward."\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Auto Reply Enabled:
				</td>
				<td>
					<input type=\"checkbox\" name=\"reply_do\" ".($reply_do==""?"":($reply_do=="on"?"checked":"")).">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Auto Reply Text:
				</td>
				<td>
					<textarea cols=\"40\" rows=\"4\" name=\"reply_text\" wrap=\"physical\" class=\"textinput\">".base64_decode($reply_text)."</textarea>
				</td>
			</tr>
			<tr height=\"40px\">
				<td colspan=\"2\">
					<input class=\"simple_button\" type=\"submit\" value=\"Create E-Mail\">
				</td>
			</tr>
			</table>
			</form>
		<script language=\"javascript\">
			document.mail_manage.login.focus();
		</script>

		";

	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

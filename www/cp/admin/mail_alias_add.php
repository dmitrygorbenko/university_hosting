<?php
	require_once ("header.php");

	function body_page() {

		$email = safe_get("email", "POST", 255);
		$alias = safe_get("alias", "POST", 65535);

		echo "
			<form action=\"mail_alias_action.php?act=add_mail_alias\" method=\"POST\" name=\"mail_alias_manage\">
			<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\">
			<tr>
				<td colspan=\"2\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Create E-Mail Alias
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
			<tr>
				<td class=\"content2\">
					Alias List:
				</td>
				<td>
					<input class=\"simple_input\" size=\"80\" maxlength=\"255\" type=\"text\" name=\"alias\" value=\"".$alias."\">
				</td>
			</tr>
			<tr height=\"40px\">
				<td colspan=\"2\">
					<input class=\"simple_button\" type=\"submit\" value=\"Create E-Mail Alias\">
				</td>
			</tr>
			</table>
			</form>
		<script language=\"javascript\">
			document.mail_alias_manage.email.focus();
		</script>

		";

	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

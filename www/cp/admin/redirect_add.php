<?php
	require_once ("header.php");

	function body_page() {

		$domain = safe_get("domain", "POST", 255);
		$pointer = safe_get("pointer", "POST", 255);
		$title = safe_get("title", "POST", 255);
		$frameset = safe_get("frameset", "POST", 255);

		echo "
			<form action=\"redirect_action.php?act=add_redirector\" method=\"POST\" name=\"redirect_manage\">
			<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\">
			<tr>
				<td colspan=\"2\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Create Web Redirector
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
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Domain:
				</td>
				<td>
					<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"domain\" value=\"".$domain."\">
				</td class=\"content2\">
			</tr>
			<tr>
				<td class=\"content2\">
					Redirect To:
				</td>
				<td>
					<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"pointer\" value=\"".$pointer."\">
				</td class=\"content2\">
			</tr>
			<tr>
				<td class=\"content2\">
					Redirect Title:
				</td>
				<td>
					<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"title\" value=\"".$title."\">
				</td class=\"content2\">
			</tr>
			<tr>
				<td class=\"content2\">
					Frameset Method:
				</td>
				<td>
					<input type=\"checkbox\" name=\"frameset\" ".($frameset==""?"":($frameset=="on"?"checked":"")).">
				</td>
			</tr>
			<tr height=\"40px\">
				<td colspan=\"2\">
					<input class=\"simple_button\" type=\"submit\" value=\"Create Web Redirector\">
				</td>
			</tr>
			</table>
			</form>
		<script language=\"javascript\">
			document.redirect_manage.domain.focus();
		</script>

		";

	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

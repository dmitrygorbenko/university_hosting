<?php
	require_once ("header.php");

	function body_page() {

		$zone_name = safe_get("zone_name", "POST", 255);

		echo "
			<form action=\"our_zone_action.php?act=add_our_zone\" method=\"POST\" name=\"zone_manage\">
			<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\">
			<tr>
				<td colspan=\"2\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Create Our Zone
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
					<font color=\"#FF0000\">*</font>Zone Name:
				</td>
				<td>
					<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"zone_name\" value=\"".$zone_name."\">
				</td>
			</tr>
			<tr height=\"40\">
				<td colspan=\"2\">
					<input class=\"simple_button\" type=\"submit\" value=\"Create Our Zone\">
				</td>
			</tr>
			</table>
			</form>
		<script language=\"javascript\">
			document.zone_manage.name.focus();
		</script>

		";
	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

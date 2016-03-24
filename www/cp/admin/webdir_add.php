<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		$HM = new Hosting_Manage;

		$domain = safe_get("domain", "POST", 255);
		$id_zone = safe_get("id_zone", "POST", 255);
		$rootdir = safe_get("rootdir", "POST", 255);

		$zones = $HM->ZC->fetch_all_zones();
		if ($zones == FALSE) {
			echo "Can't fetch all zones: ".$_SESSION["adminpanel_error"];
			return;
		}

		echo "
			<form action=\"webdir_action.php?act=add_webdir\" method=\"POST\" name=\"webdir_manage\">
			<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\">
			<tr>
				<td colspan=\"2\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Create Web Dir
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
					If Domain will owns by some Hosting Zone then RootDir will be prefixed by Hosting Zone's www dir.<br>
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Domain:
				</td>
				<td NOWRAP>
					<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"domain\" value=\"".$domain."\">
					&nbsp;<strong>.</strong>&nbsp;
					<select name=\"id_zone\" class=\"simple_select\">
						<option value=\"none\">----------------</option>
			";

		for ($i = 0; $i < $HM->ZC->SQL->get_num_rows($zones); $i++) {
			$data = $HM->ZC->SQL->get_object($zones);

			echo "<option ".($data->id_table==$id_zone?"selected":"")." value=\"".$data->id_table."\">".$data->name."</option>\n";
		}

		echo "
					</select>
				</td class=\"content2\">
			</tr>
			<tr>
				<td class=\"content2\">
					Root Dir:
				</td>
				<td>
					<input class=\"simple_input\" size=\"80\" maxlength=\"255\" type=\"text\" name=\"rootdir\" value=\"".($rootdir?$rootdir:"")."\">
				</td class=\"content2\">
			</tr>
			<tr height=\"40px\">
				<td colspan=\"2\">
					<input class=\"simple_button\" type=\"submit\" value=\"Create Web Dir\">
				</td>
			</tr>
			</table>
			</form>
		<script language=\"javascript\">
			document.webdir_manage.domain.focus();
		</script>

		";

	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

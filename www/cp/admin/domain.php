<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

function body_page() {
	$HM = new Hosting_Manage;

	$act = safe_get("act", "GET", 255);

	if ($act != "")
		$view_all = FALSE;
	else
		$view_all = TRUE;

	if ($act != "") {

		$zone_list = $HM->ZC->fetch_all_zones();
		if ($zone_list == FALSE) {
			echo "Error while retrive zone information: ".$_SESSION["adminpanel_error"];
			return;
		}

		if ($HM->ZC->SQL->get_num_rows($zone_list) == 0) {
			echo "Create at least one domain zone: ".$_SESSION["adminpanel_error"];
			return;
		}

		if ($act == "create_domain") {
			echo "
				<form action=\"domain.php?act=add_domain\" method=\"POST\" name=\"domain_manage\">
				<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\">
				<tr>
					<td colspan=\"2\" width=\"100%\">
						<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
						<tr valign=\"center\" height=\"62\" width=\"100%\">
							<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
								<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
							</td>
							<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
								Create new domain
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
						<font color=\"#FF0000\">*</font>Zone:
					</td>
					<td>
						<select name=\"zone\">";

	for ($i = 0; $i < $HM->ZC->SQL->get_num_rows($zone_list); $i++) {
		$z_data = $HM->ZC->SQL->get_object($zone_list);
		echo "<option value=\"".$z_data->name."\">".$z_data->name."</option>";
	}

			echo "
						</select>

					</td class=\"content2\">
				</tr>
				<tr>
					<td class=\"content2\">
						<font color=\"#FF0000\">*</font>Domain:
					</td>
					<td>
						<input class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\" name=\"domain\" value=\"\"><br>
					</td>
				</tr>
				<tr>
					<td colspan=\"2\">
						<input class=\"simple_button\" type=\"submit\" value=\"Add new domain\">
					</td>
				</tr>
				</table>
				</form>
			<script language=\"javascript\">
				document.domain_manage.domain.focus();
			</script>

			";
		}

		elseif ($act == "add_domain") {

			$zone = safe_get("zone", "POST", 255);
			$domain = safe_get("domain", "POST", 255);

			if (	$zone != "" &&
				$domain != "") {

				if ($HM->DC->add_domain($zone, $domain) != TRUE) {
					echo "Can't create domain: ".$_SESSION["adminpanel_error"];
					return;
				}

				$view_all = TRUE;
			}
			else {
				echo "Enter data !";
			}
		}

		elseif ($act == "change_domain") {

			$id = safe_get("id", "POST", 255);
			$zone = safe_get("zone", "POST", 255);
			$domain = safe_get("domain", "POST", 255);

			if ($id != "" &&
				$zone != "" &&
				$domain != "") {

				if ($HM->DC->change_domain($id, $zone, $domain) != TRUE) {
					echo "Can't change domain: ".$_SESSION["adminpanel_error"];
					return;
				}

				$view_all = TRUE;
			}
			else {
				echo "Enter data !";
			}
		}

		elseif ($act == "remove") {

			$ids = safe_get("ids", "POST");

			if ($ids == "") {
				echo "Error - did not specified id's parameter";
				return;
			}

			$id_separate = explode(":", $ids);

			for($i=0; $i<count($id_separate); $i++) {
				$id = trim($id_separate[$i]);
				if ($id != "") {
					if ($HM->DC->remove_domain($id) != TRUE) {
						echo "Can't remove domain: ".$_SESSION["adminpanel_error"];
						return;
					}
				}
			}

			$view_all = TRUE;
		}

		elseif ($act == "remove_sub") {

			$ids = safe_get("ids", "POST");

			if ($ids == "") {
				echo "Error - did not specified id's parameter";
				return;
			}

			$id_separate = explode(":", $ids);

			for($i=0; $i<count($id_separate); $i++) {
				$id = trim($id_separate[$i]);
				if ($id != "") {
					if ($HM->DC->remove_subdomain($id) != TRUE) {
						echo "Can't remove subdomain: ".$_SESSION["adminpanel_error"];
						return;
					}
				}
			}

			$view_all = TRUE;
		}

		elseif ($act == "view") {

			$id = safe_get("id", "GET", 255);
			if ($id == "") {
				echo "Can't find id !";
				return;
			}

			$data = $HM->DC->fetch_domain($id);
			if ($data == FALSE) {
				echo "Can't fetch domain: ".$_SESSION["adminpanel_error"];
				return;
			}

			$zone_list = $HM->ZC->fetch_all_zones();
			if ($zone_list == FALSE) {
				echo "Error while retrive zone information: ".$_SESSION["adminpanel_error"];
				return;
			}

			echo "
				<form action=\"domain.php?act=change_domain\" method=\"POST\" name=\"domain_manage\">
				<input name=\"id\" value=\"".$data->id_table."\" size=\"40\" maxlength=\"255\" type=\"hidden\">
				<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
				<tr valign=\"center\" height=\"62\" width=\"100%\">
					<td colspan=\"5\" width=\"100%\">
						<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
						<tr valign=\"center\" height=\"62\" width=\"100%\">
							<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
								<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
							</td>
							<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
								Domain: <strong>".$data->f_name."</strong>
							</td>
							<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
								<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
							</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr valign=\"top\">
					<td width=\"20%\" class=\"content\">
						Zone:
					</td>
					<td width=\"80%\" class=\"content_2\">
						<select name=\"zone\">";

	for ($i = 0; $i < $HM->ZC->SQL->get_num_rows($zone_list); $i++) {
		$z_data = $HM->ZC->SQL->get_object($zone_list);

		echo "<option ".($z_data->name==$data->zone?"selected":"")." value=\"".$z_data->name."\">".$z_data->name."</option>";
	}

			echo "
						</select>
					</td>
				</tr>
				<tr valign=\"top\">
					<td width=\"20%\" class=\"content\">
						Domain:
					</td>
					<td width=\"80%\" class=\"content_2\">
						<input name=\"domain\" value=\"".$data->name."\" class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\"><br>
					</td>
				</tr>
				<tr>
					<td>
						&nbsp;
					</td>
					<td>
						<input class=\"simple_button\" type=\"submit\" value=\"Apply changes\">
					</td>
				</tr>
				";
			echo "</table>";
		}
	}

	if ($view_all == TRUE) {

		$result = $HM->DC->fetch_all_domains();
		if ($result == FALSE) {
			echo "Can't fetch all domains: ".$_SESSION["adminpanel_error"];
			return;
		}

		$subresult = $HM->DC->fetch_all_subdomains();
		if ($subresult == FALSE) {
			echo "Can't fetch all subdomains: ".$_SESSION["adminpanel_error"];
			return;
		}

		$empty = TRUE;
		echo "
			<form name=\"remove_form\" method=\"POST\" action=\"domain.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"center\" height=\"62\" width=\"100%\">
				<td colspan=\"5\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Domains
						</td>
						<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
							<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"2%\" class=\"content\">&nbsp;</td>
				<td width=\"42%\" class=\"content\">Domain</td>
				<td width=\"42%\" class=\"content\">Zone</td>
			</tr>
			<span id=\"elem_count\" title=\"".$HM->DC->SQL->get_num_rows($result)."\"></span>";

		for ($i = 0; $i < $HM->DC->SQL->get_num_rows($result); $i++) {
			$data = $HM->DC->SQL->get_object($result);
			echo "<tr>
				<span id=\"elem_account_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"domain.php?act=view&id=".$data->id_table."\">".$data->name."</a></td>
				<td class=\"content_3\">".$data->zone."</td>
			</tr>";
			$empty = FALSE;
		}

		if ($empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"4\">В таблице нет данных</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"3\" align=\"left\">
					<a href=\"javascript:checkall_elem(1)\">Выделить все</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href=\"javascript:checkall_elem(0)\">Очистить все</a>
				</td>
				<td colspan=\"2\" align=\"right\">
					<input onClick=\"remove_domains()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
				</td>
			</tr>";

		echo "</table>
			</form>";


		echo "
			<form name=\"remove_sub_form\" method=\"POST\" action=\"domain.php?act=remove_sub\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"center\" height=\"62\" width=\"100%\">
				<td colspan=\"5\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Subdomains
						</td>
						<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
							<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class=\"content\" colspan=\"5\">
					<strong>Warning:</strong> Removing subdomains may cause exceeding subdomain limit by clients
				</td>
			</tr>
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"2%\" class=\"content\">&nbsp;</td>
				<td width=\"28%\" class=\"content\">Subdomain</td>
				<td width=\"28%\" class=\"content\">Client</td>
				<td width=\"28%\" class=\"content\">Folder</td>
			</tr>
			<span id=\"sub_elem_count\" title=\"".$HM->DC->SQL->get_num_rows($subresult)."\"></span>";

		for ($i = 0; $i < $HM->DC->SQL->get_num_rows($subresult); $i++) {
			$data = $HM->DC->SQL->get_object($subresult);
			echo "<tr>
				<span id=\"sub_elem_account_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"sub_checkbox_".$i."\"></td>
				<td class=\"content_3\">".$data->name."</td>
				<td class=\"content_3\">".$data->domain."</td>
				<td class=\"content_3\">".$data->homedir."</td>
			</tr>";
			$empty = FALSE;
		}

		if ($empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"4\">В таблице нет данных</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"3\" align=\"left\">
					<a href=\"javascript:checkall_elem(1)\">Выделить все</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href=\"javascript:checkall_elem(0)\">Очистить все</a>
				</td>
				<td colspan=\"2\" align=\"right\">
					<input onClick=\"remove_subdomains()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
				</td>
			</tr>";

		echo "</table>
			</form>";

	}
}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

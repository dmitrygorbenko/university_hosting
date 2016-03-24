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

		if ($act == "activate") {
			$id = safe_get("id", "GET", 255);
			if ($id == "") {
				echo "Can't find id !: ".$_SESSION["adminpanel_error"];
				return;
			}

			$data = $HM->CC->candidate_activate_account($id);
			if ($data == FALSE) {
				echo "Can't activate account: ".$_SESSION["adminpanel_error"];
				return;
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

					if ($HM->CC->candidate_remove_client_account($id) != TRUE) {
						echo "Can't remove account: ".$_SESSION["adminpanel_error"];
						return;
					}

				}
			}

			$view_all = TRUE;
		}

		elseif ($act == "view") {

			$id = safe_get("id", "GET", 255);
			if ($id == "") {
				echo "Can't find id !: ".$_SESSION["adminpanel_error"];
				return;
			}

			$data = $HM->CC->candidate_fetch_client_account($id);
			if ($data == FALSE) {
				echo "Can't fetch client account: ".$_SESSION["adminpanel_error"];
				return;
			}

			echo "
				<form action=\"candidate.php?act=remove\" method=\"POST\" name=\"client_manage\">
				<input name=\"ids\" value=\"".$data->id_table."\" size=\"40\" maxlength=\"255\" type=\"hidden\">
				<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\">
				<tr>
					<td colspan=\"2\" width=\"100%\">
						<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
						<tr valign=\"center\" height=\"62\" width=\"100%\">
							<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
								<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
							</td>
							<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
								Candidate: <strong>".$data->fdname."</strong>
							</td>
							<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
								<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
							</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class=\"content2\">
						Domain:
					</td>
					<td class=\"content_3\">
						".$data->fdname."&nbsp;
					</td class=\"content2\">
				</tr>
				<tr>
					<td class=\"content2\">
						Domain type:
					</td>
					<td class=\"content_3\">
						".$data->domain_type."&nbsp;
					</td class=\"content2\">
				</tr>
				<tr>
					<td class=\"content2\">
						Service type:
					</td>
					<td class=\"content_3\">
						".$data->service_type."&nbsp;
					</td class=\"content2\">
				</tr>
				<tr>
					<td class=\"content2\">
						Login:
					</td>
					<td class=\"content_3\">
						".$data->login."&nbsp;
					</td class=\"content2\">
				</tr>
				<tr height=\"10\">
					<td colspan=\"2\">
					</td>
				</tr>
				<tr>
					<td class=\"content2\">
						First name:
					</td>
					<td class=\"content_3\">
						".$data->firstname."&nbsp;
					</td>
				</tr>
				<tr>
					<td class=\"content2\">
						Last name:
					</td>
					<td class=\"content_3\">
						".$data->lastname."&nbsp;
					</td>
				</tr>
				<tr>
					<td class=\"content2\">
						E-mail:
					</td>
					<td class=\"content_3\">
						".$data->email."&nbsp;
					</td>
				</tr>
				<tr>
					<td class=\"content2\">
						City:
					</td>
					<td class=\"content_3\">
						".$data->city."&nbsp;
					</td>
				</tr>
				<tr>
					<td class=\"content2\">
						Region:
					</td>
					<td class=\"content_3\">
						".$data->region."&nbsp;
					</td>
				</tr>
				<tr>
					<td class=\"content2\">
						Postal:
					</td>
					<td class=\"content_3\">
						".$data->postal."&nbsp;
					</td>
				</tr>
				<tr>
					<td class=\"content2\">
						Company:
					</td>
					<td class=\"content_3\">
						".$data->company."&nbsp;
					</td>
				</tr>
				<tr>
					<td class=\"content2\">
						Address:
					</td>
					<td class=\"content_3\">
						".$data->address."&nbsp;
					</td>
				</tr>
				<tr>
					<td class=\"content2\">
						Phone:
					</td>
					<td class=\"content_3\">
						".$data->phone."&nbsp;
					</td>
				</tr>
				<tr>
					<td class=\"content2\">
						Fax:
					</td>
					<td class=\"content_3\">
						".$data->fax."&nbsp;
					</td>
				</tr>
				<tr>
					<td class=\"content2\">
						Add info:
					</td>
					<td class=\"content_3\">
						<textarea cols=\"40\">".$data->add_info."</textarea>
					</td>
				</tr>
				<tr height=\"10\">
					<td colspan=\"2\">
					</td>
				</tr>
				<tr height=\"40\">
					<td>
						&nbsp;
					</td>
					<td>
						<input class=\"simple_button\" type=\"button\" onClick=\"document.location='candidate.php?act=activate&id=".$data->id_table."'\" value=\"Activate\">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<input class=\"simple_button\" type=\"submit\" value=\"Remove\">
					</td>
				</tr>
				</table>";
		}
	}

	if ($view_all == TRUE) {

		$result = $HM->CC->candidate_fetch_all_client_account();
		if ($result == FALSE) {
			echo "Can't fetch all client accounts: ".$_SESSION["adminpanel_error"];
			return;
		}

		$empty = TRUE;
		echo "
			<form name=\"remove_form\" method=\"POST\" action=\"candidate.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"center\" height=\"62\" width=\"100%\">
				<td colspan=\"7\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Hosting Candidates
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
				<td width=\"40%\" class=\"content\">Domain</td>
				<td width=\"20%\" class=\"content\">Date</td>
				<td width=\"14%\" class=\"content\">Client Name</td>
				<td width=\"10%\" class=\"content\">Service Type</td>
				<td width=\"20%\" class=\"content\">Activate</td>
			</tr>
			<span id=\"elem_count\" title=\"".$HM->CC->SQL->get_num_rows($result)."\"></span>";

		for ($i = 0; $i < $HM->CC->SQL->get_num_rows($result); $i++) {
			$data = $HM->CC->SQL->get_object($result);

			echo "<tr>
				<span id=\"elem_account_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"candidate.php?act=view&id=".$data->id_table."\">".$data->fdname."</a></td>
				<td class=\"content_3\">".$data->query_time."</td>
				<td class=\"content_3\" NOWRAP>".$data->firstname." ".$data->lastname."</td>
				<td class=\"content_3\">".$data->service_type."</td>
				<td class=\"content_3\"><a href=\"candidate.php?act=activate&id=".$data->id_table."\">Activate</a></td>
			</tr>";
			$empty = FALSE;
		}

		if ($empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"7\">В таблице нет данных</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"5\" align=\"left\">
					<a href=\"javascript:checkall_elem(1)\">Выделить все</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href=\"javascript:checkall_elem(0)\">Очистить все</a>
				</td>
				<td colspan=\"2\" align=\"right\">
					<input onClick=\"remove_client_accounts()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
				</td>
			</tr>";

		echo "</table>";

		if (!$empty)
		echo "Description:<br>
			<strong>buy</strong> - He(she) wants us to create his(her) domain<br>";
	}

}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

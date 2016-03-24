<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		global $HOSTING_IP;

		$id_zone = safe_get("id_zone", "GET", 255);

		if ($id_zone == "") {
			echo "Can't find id_zone !";
			return;
		}

		$HM = new Hosting_Manage;

		$zone = $HM->ZC->fetch_zone($id_zone);
		if ($zone == FALSE) {
			echo "Can't fetch zone: ".$_SESSION["adminpanel_error"];
			return;
		}

		$client = $HM->CC->fetch_client_account($zone->id_client_table);
		if ($client == FALSE) {
			echo "Can't fetch client info: ".$_SESSION["adminpanel_error"];
			return;
		}

		$subdomains = $HM->SDC->fetch_all_subdomains_of_zone($id_zone);
		if ($subdomains == FALSE) {
			echo "Can't fetch all subdomains of zone: ".$_SESSION["adminpanel_error"];
			return;
		}

		$zone_info = $HM->SDC->fetch_zone_info($id_zone);
		if ($zone_info == FALSE) {
			echo "Can't fetch zone info: ".$_SESSION["adminpanel_error"];
			return;
		}

		echo "
		<script language=javascript>

			function check_form(form_id) {

				form = document.getElementById(form_id);
				new_rr = form.new_rr.value;

				if (new_rr == \"\") {
					alert('Заполните все поля');
					return false;
				}

				if (form_id == \"create_d_mx\") {
					new_pri = form.new_pri.value;

					all_right = \"true\";

					if (new_pri < 1 && new_pri != \"\") {
						alert('Нельзя установить для записи MX приоритет меньше единицы');
						all_right = \"false\";
					}

					if (new_pri == \"\") {
						alert('Не определено поле \"Приоритет\"');
						all_right = \"false\";
					}

					if (all_right == \"true\") {
						form.submit();
					}

				}
				else if (form_id == \"new_record\") {
					new_d_name = form.new_d_name.value;
					new_rt = form.new_rt.options[form.new_rt.selectedIndex].value;
					new_pri = form.new_pri.value;

					all_right = \"true\";

					if (new_d_name == \"\" || new_rt == \"\") {
						alert('Заполните все поля');
						all_right = \"false\";
					}

					if (new_d_name == \"".$zone->name."\") {
						alert('Для определения домена воспользуйтесь первой записью');
						all_right = \"false\";
					}

					if (new_rt == \"MX\") {
						if (new_pri < 1 && new_pri != \"\") {
							alert('Нельзя установить для записи MX приоритет меньше единицы');
							all_right = \"false\";
						}
						if (new_pri == \"\") {
							alert('Не определено поле \"Приоритет\"');
							all_right = \"false\";
						}

					}

					if (all_right == \"true\") {
						form.submit();
					}

				}
				else {
					new_d_name = form.new_d_name.value;
					new_rt = form.new_rt.options[form.new_rt.selectedIndex].value;
					new_pri = form.new_pri.value;

					all_right = \"true\";

					if (new_d_name == \"\" || new_rt == \"\") {
						alert('Заполните все поля');
							all_right = \"false\";
						}

					if (new_d_name == \"".$zone->name."\") {
						alert('Для определения домена воспользуйтесь первой записью');
							all_right = \"false\";
						}

					if (new_rt == \"MX\") {
						if (new_pri < 1 && new_pri != \"\") {
							alert('Нельзя установить для записи MX приоритет меньше единицы');
							all_right = \"false\";
						}

						if (new_pri == \"\") {
							alert('Не определено поле \"Приоритет\"');
							all_right = \"false\";
						}
					}

					if (all_right == \"true\") {
						form.submit();
					}
				}
			}

			function check_mx_form(form_id) {

				form = document.getElementById(\"modify_d_mx_\" + form_id);
				new_rr = form.new_rr.value;
				new_pri = form.new_pri.value;

				all_right = \"true\";

				if (new_pri < 1 && new_pri != \"\") {
					alert('Нельзя установить для записи MX приоритет меньше единицы');
					all_right = \"false\";
				}

				if (new_pri == \"\") {
					alert('Не определено поле \"Приоритет\"');
					all_right = \"false\";
				}

				if (all_right == \"true\") {
					form.submit();
				}
			}

			function check_rt(form_id) {

				form = document.getElementById(form_id);
				new_rt = form.new_rt.options[form.new_rt.selectedIndex].value;

				if (new_rt == \"MX\") {
					form.new_pri.style.display = \"\";
				}
				else {
					form.new_pri.style.display = \"none\";
				}
			}

		</script>
		<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"center\" height=\"62\" width=\"100%\">
				<td colspan=\"6\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Manage Hosting Zone <a class=\"title\" href=\"zone_change.php?id=".$id_zone."\">".$zone->name."</a> of client <a class=\"title\" href=\"client_change.php?id=".$client->id_table."\">".$client->login."</a>
						</td>
						<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
							<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr valign=\"top\">
				<td colspan=\"6\">
					<input type=\"button\" class=\"simple_button\" onClick=\"document.location='zone_manage_change_manually.php?id_zone=".$id_zone."'\"\" value=\"Manual Mode\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td colspan=\"6\" width=\"100%\" class=\"content\">MX Records</td>
			</tr>
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"22%\" class=\"content\">Domain</td>
				<td width=\"13%\" class=\"content\">Record Type</td>
				<td width=\"13%\" class=\"content\">Priority</td>
				<td width=\"25%\" class=\"content\">Resource Record</td>
				<td width=\"23%\" class=\"content\">Actions</td>
			</tr>
			";

		$index = 0;

		for ($i = 0; $i < count($zone_info); $i++, $index++)
			echo	"
			<tr valign=\"top\">
				<form action=\"zone_manage_action.php\" method=\"POST\" id=\"modify_d_mx_".$i."\" name=\"modify_d_mx_".$i."\">
				<input type=\"hidden\" name=\"id_zone\" value=\"".$id_zone."\">
				<input type=\"hidden\" name=\"action\" value=\"modify_d_mx\">
				<input type=\"hidden\" name=\"old_pri\" value=\"".$zone_info[$i]["prior"]."\">

				<td class=\"dns\">
					&nbsp;
				</td>
				<td class=\"dns\">
					".$zone->name."
				</td>
				<td class=\"dns\">
					MX
				</td>
				<td class=\"dns\">
					<input class=\"simple_input\" type=\"text\" name=\"new_pri\" size=\"4\" value=\"".$zone_info[$i]["prior"]."\">
				</td>
				<td class=\"dns\">
					<input class=\"simple_input\" type=\"text\" name=\"new_rr\" value=\"".$zone_info[$i]["name"]."\">
				</td>
				<td class=\"dns\">
					<input class=\"simple_button\" type=\"button\" name=\"update\" value=\"Update\" onClick=\"check_mx_form(".$i.")\">
					".($index>0?"<input class=\"simple_button\" type=\"button\" name=\"delete\" value=\"Remove\" onClick=\"(document.getElementById('modify_d_mx_".$i."')).action.value = 'delete_d_mx';check_mx_form(".$i.")\">":"")."
				</td>
				</form>
			</tr>
			";

		$index = 1;

			echo "
			<tr valign=\"top\">
				<form action=\"zone_manage_action.php\" method=\"POST\" id=\"create_d_mx\" name=\"create_d_mx\">
				<input type=\"hidden\" name=\"id_zone\" value=\"".$id_zone."\">
				<input type=\"hidden\" name=\"action\" value=\"create_d_mx\">

				<td class=\"dns\">
					&nbsp;
				</td>
				<td class=\"dns\">
					".$zone->name."
				</td>
				<td class=\"dns\">
					MX
				</td>
				<td class=\"dns\">
					<input class=\"simple_input\" type=\"text\" name=\"new_pri\" size=\"4\" value=\"\">
				</td>
				<td class=\"dns\">
					<input class=\"simple_input\" type=\"text\" name=\"new_rr\" value=\"\">
				</td>
				<td class=\"dns\">
					<input class=\"simple_button\" type=\"button\" name=\"update\" value=\"Create\" onClick=\"check_form('create_d_mx')\">
				</td>
				</form>
			</tr>
			<tr valign=\"top\" height=\"20\">
				<td colspan=\"6\" width=\"100%\">
					&nbsp;
				</td>
			</tr>
		</table>

		<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"top\">
				<td colspan=\"6\" width=\"100%\" class=\"content\">Domain Records of Domain Zone <strong>".$zone->name."</strong></td>
			</tr>
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"22%\" class=\"content\">Domain</td>
				<td width=\"13%\" class=\"content\">Record Type</td>
				<td width=\"13%\" class=\"content\">Proirity</td>
				<td width=\"25%\" class=\"content\">Resource Record</td>
				<td width=\"23%\" class=\"content\">Actions</td>
			</tr>";


		for ($i = 0; $i < $HM->SDC->SQL->get_num_rows($subdomains); $i++) {
			$data = $HM->SDC->SQL->get_object($subdomains);

			echo "
				<tr valign=\"top\">
					<form action=\"zone_manage_action.php\" method=POST id=\"modify_record_".$i."\" name=\"modify_record_".$i."\">
					<input type=\"hidden\" name=\"id_zone\" value=\"".$id_zone."\">
					<input type=\"hidden\" name=\"action\" value=\"modify_record\">
					<input type=\"hidden\" name=\"id_domain\" value=\"".$data->id_table."\">
					<input type=\"hidden\" name=\"old_d_name\" value=\"".$data->name."\">
					<input type=\"hidden\" name=\"old_rt\" value=\"".$data->type."\">
					<input type=\"hidden\" name=\"old_pri\" value=\"".$data->prior."\">

					<td class=\"dns\">
						".$index++."
					</td>
					<td class=\"dns\">
						<input class=\"simple_input\" type=\"text\" size=\"17\" maxlength=\"255\" name=\"new_d_name\" value=\"".$data->name."\">
					</td>
					<td class=\"dns\">
						<select class=\"simple_select\" name=\"new_rt\" onChange=\"check_rt('modify_record_".$i."')\">
							<option value=\"A\" ".($data->type=="A"?"selected":"").">A</option>
							<option value=\"CNAME\" ".($data->type=="CNAME"?"selected":"").">CNAME</option>
							<option value=\"MX\" ".($data->type=="MX"?"selected":"").">MX</option>
							<option value=\"NS\" ".($data->type=="NS"?"selected":"").">NS</option>
						</select>
					</td>
					<td class=\"dns\">
						<input class=\"simple_input\" type=\"text\" name=\"new_pri\" size=\"4\" value=\"".$data->prior."\">
					</td>
					<td class=\"dns\">
						<input class=\"simple_input\" type=\"text\" name=\"new_rr\" value=\"".$data->record."\">
					</td>
					<td class=\"dns\">
						<input class=\"simple_button\" type=\"button\" name=\"update\" value=\"Update\" onClick=\"(document.getElementById('modify_record_".$i."')).action.value = 'modify_record';check_form('modify_record_".$i."')\">
						<input class=\"simple_button\" type=\"button\" name=\"delete\" value=\"Remove\" onClick=\"(document.getElementById('modify_record_".$i."')).action.value = 'delete_record';check_form('modify_record_".$i."')\">
					</td>
					</form>
					<script language=javascript>
						check_rt('modify_record_".$i."');
					</script>
				</tr>
			";
		}

		echo "
			<tr valign=\"top\">
				<form action=\"zone_manage_action.php\" method=POST id=\"create_record\" name=\"create_record\">
				<input type=\"hidden\" name=\"id_zone\" value=\"".$id_zone."\">
				<input type=\"hidden\" name=\"action\" value=\"create_record\">

				<td class=\"dns\">
					&nbsp;
				</td>
				<td class=\"dns\">
					<input class=\"simple_input\" type=\"text\" size=\"17\" maxlength=\"255\" name=\"new_d_name\" value=\"\">
				</td>
				<td class=\"dns\">
					<select class=\"simple_select\" name=\"new_rt\" onChange=\"check_rt('create_record')\">
						<option value=\"A\" selected>A</option>
						<option value=\"CNAME\">CNAME</option>
						<option value=\"MX\">MX</option>
						<option value=\"NS\">NS</option>
					</select>
				</td>
				<td class=\"dns\">
					<input class=\"simple_input\" type=\"text\" name=\"new_pri\" size=\"4\" value=\"10\">
				</td>
				<td class=\"dns\">
					<input class=\"simple_input\" type=\"text\" name=\"new_rr\" value=\"".$HOSTING_IP."\" >
				</td>
				<td class=\"dns\">
					<input class=\"simple_button\" type=\"button\" name=\"add\" value=\"Create\" onClick=\"return check_form('create_record')\">
				</td>
				</form>
				<script language=javascript>
					check_rt('create_record');
				</script>
			</tr>
		</table>
		<br>
		<br>
		";
	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

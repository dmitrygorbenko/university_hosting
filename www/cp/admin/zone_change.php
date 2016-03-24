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

		$zone = $HM->ZC->fetch_zone($id);
		if ($zone == FALSE) {
			echo "Can't fetch zone: ".$_SESSION["adminpanel_error"];
			return;
		}

		$zone_quota = $HM->ZC->fetch_zone_quota($zone->id_table);
		if ($zone_quota == FALSE) {
			echo "Can't fetch quota info: ".$_SESSION["adminpanel_error"];
			return;
		}

		$client = $HM->CC->fetch_client_account($zone->id_client_table);
		if ($client == FALSE) {
			echo "Can't fetch client info: ".$_SESSION["adminpanel_error"];
			return;
		}

		$subdomains = $HM->SDC->fetch_all_subdomains_of_zone($id);
		if ($subdomains == FALSE) {
			echo "Can't fetch zone's subdomains: ".$_SESSION["adminpanel_error"];
			return;
		}
		$subdomains_empty = TRUE;

		$mails = $HM->MC->fetch_all_zones_mail($id);
		if ($mails == FALSE) {
			echo "Can't fetch zone's mails: ".$_SESSION["adminpanel_error"];
			return;
		}
		$mails_empty = TRUE;

		$ftps = $HM->FC->fetch_all_zones_ftp($id);
		if ($ftps == FALSE) {
			echo "Can't fetch zone's ftps: ".$_SESSION["adminpanel_error"];
			return;
		}
		$ftps_empty = TRUE;

		$mysqls = $HM->MyC->fetch_all_zones_mysqldb($id);
		if ($mysqls == FALSE) {
			echo "Can't fetch zone's mysqls db: ".$_SESSION["adminpanel_error"];
			return;
		}
		$mysqls_empty = TRUE;

		$pgsqls = $HM->PgC->fetch_all_zones_pgsqldb($id);
		if ($pgsqls == FALSE) {
			echo "Can't fetch zone's pgsqls db: ".$_SESSION["adminpanel_error"];
			return;
		}
		$pgsqls_empty = TRUE;

		$areas = $HM->AC->fetch_all_zones_areas($id);
		if ($areas == FALSE) {
			echo "Can't fetch zone's areas: ".$_SESSION["adminpanel_error"];
			return;
		}
		$areas_empty = TRUE;

		$area_users = $HM->AC->fetch_all_zones_area_users($id);
		if ($area_users == FALSE) {
			echo "Can't fetch zone's area users: ".$_SESSION["adminpanel_error"];
			return;
		}
		$area_users_empty = TRUE;

		$area_groups = $HM->AC->fetch_all_zones_area_groups($id);
		if ($area_groups == FALSE) {
			echo "Can't fetch zone's area groups: ".$_SESSION["adminpanel_error"];
			return;
		}
		$area_groups_empty = TRUE;

		$service = $HM->ZC->fetch_zone_service($id);
		if ($service == FALSE) {
			echo "Can't fetch zone's service: ".$_SESSION["adminpanel_error"];
			return;
		}

		echo "
		<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">
		<tr>
		<td width=\"100%\" colspan=\"2\">
			<table width=\"100%\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"center\" height=\"62\" width=\"100%\">
				<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
					<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
				</td>
				<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
					Hosting zone <strong>".$zone->name."</strong> of client <a class=\"title\" href=\"client_change.php?id=".$client->id_table."\">".$client->login."</a>
				</td>
				<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
					<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
				</td>
			</tr>
			</table>
		</td>
		</tr>

		<tr>
		<td width=\"50%\" valign=\"top\" align=\"left\">

			<form action=\"zone_action.php?act=change_zone\" method=\"POST\" name=\"zone_manage\">
			<input name=\"id\" value=\"".$id."\" size=\"40\" maxlength=\"255\" type=\"hidden\">
			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"top\">
				<td width=\"10%\" class=\"content2\">
					Zone Name:
				</td>
				<td width=\"80%\">
					<input name=\"zone_name\" value=\"".$zone->name."\" class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\"><br>
				</td>
			</tr>
			<tr height=\"10\">
				<td colspan=\"2\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Zone Type:
				</td>
				<td NOWRAP>
					<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"zone_table\" width=\"100%\">
					<tr>
						<td>
							&nbsp;&nbsp;
							<select class=\"simple_select\" name=\"zone_type\">
								<option value=\"our\" ".($zone->zone_type=="our"?"selected":"").">Our</option>
								<option value=\"parked\" ".($zone->zone_type=="parked"?"selected":"").">Parked</option>
								<option value=\"transfer\" ".($zone->zone_type=="transfer"?"selected":"").">Transfer</option>
							</select>
							&nbsp;&nbsp;&nbsp;&nbsp;
						</td class=\"content2\">

						<td class=\"content2\">
							Service Type:
						</td>
						<td>
							&nbsp;&nbsp;
							<select name=\"service_type\" class=\"simple_select\">
								<option ".($zone->service_type=="free"?"selected ":"")." value=\"free\">Free</option>
								<option ".($zone->service_type=="full"?"selected ":"")."value=\"full\">Full</option>
							</select>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr height=\"10\">
				<td colspan=\"2\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Disk Quota:
				</td>
				<td NOWRAP>
					<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"zone_table\" width=\"100%\">
					<tr>
						<td align=\"right\">
							&nbsp;&nbsp;
							Max:
						</td>
						<td align=\"right\">
							&nbsp;<select name=\"disk_space\" class=\"simple_select\">
								<option".($service->disk_space=="5242880"?" selected":"")." value=\"5242880\">5 Mb</option>
								<option".($service->disk_space=="10485760"?" selected":"")." value=\"10485760\">10 Mb</option>
								<option".($service->disk_space=="15728640"?" selected":"")." value=\"15728640\">15 Mb</option>
								<option".($service->disk_space=="20971520"?" selected":"")." value=\"20971520\">20 Mb</option>
								<option".($service->disk_space=="26214400"?" selected":"")." value=\"26214400\">25 Mb</option>
								<option".($service->disk_space=="52428800"?" selected":"")." value=\"52428800\">50 Mb</option>
								<option".($service->disk_space=="104857600"?" selected":"")." value=\"104857600\">100 Mb</option>
								<option".($service->disk_space=="0"?" selected":"")." value=\"0\">Unlimited</option>
							</select>
						</td>
						<td align=\"right\">
							&nbsp;&nbsp;
							Used:
							&nbsp;&nbsp;
						</td>
						<td>
							".convert_size($zone_quota)."
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Maximum:
					<br>
					(0 is unlimited)
				</td>
				<td NOWRAP>
					<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"zone_table\" width=\"100%\">
					<tr>
						<td align=\"right\">
							&nbsp;&nbsp;
							sub-domain:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"subdomain_max_count\" value=\"".$service->subdomain_max_count."\">
						</td>
						<td align=\"right\">
							&nbsp;&nbsp;
							ftp:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"ftp_max_count\" value=\"".$service->ftp_max_count."\">
						</td>
					</tr>
					</tr>
						<td align=\"right\">
							&nbsp;&nbsp;
							e-mail:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"email_max_count\" value=\"".$service->email_max_count."\">
						</td>
						<td align=\"right\">
							&nbsp;&nbsp;
							e-mail aliases:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"email_alias_max_count\" value=\"".$service->email_alias_max_count."\">
						</td>
					</tr>
					</tr>
						<td align=\"right\">
							&nbsp;&nbsp;
							e-mail autoreply:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"email_reply_max_count\" value=\"".$service->email_reply_max_count."\">
						</td>
						<td align=\"right\">
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					</tr>
						<td align=\"right\">
							&nbsp;&nbsp;
							mysql db:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"mysql_max_count\" value=\"".$service->mysql_max_count."\">
						</td>
						<td align=\"right\">
							&nbsp;&nbsp;
							pgsql db:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"pgsql_max_count\" value=\"".$service->pgsql_max_count."\">
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr height=\"10\">
				<td colspan=\"2\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					ID:
				</td>
				<td NOWRAP>
					<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"zone_table\" width=\"100%\">
					<tr>
						<td align=\"right\">
							&nbsp;&nbsp;
							UID:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"uid\" value=\"".$zone->uid."\">
						</td>
						<td align=\"right\">
							&nbsp;&nbsp;
							GID:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"gid\" value=\"".$zone->gid."\">
						</td>
						<td align=\"right\">
							&nbsp;&nbsp;
							<input type=\"button\" class=\"simple_button\" value=\"Select ID\" onClick=\"javascript:OpenID()\">
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr height=\"10\">
				<td colspan=\"2\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\" colspan=\"2\">
					<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
					<tr>
						<td align=\"right\">
							Control Panel:
							<input type=\"checkbox\" name=\"cp\" ". ($service->cp=="t"?" checked ":"").">
						</td>
						<td align=\"right\">
							FTP access:
							<input type=\"checkbox\" name=\"ftp_access\" ". ($service->ftp_access=="t"?" checked ":"").">
						</td>
						<td align=\"right\">
							POP&nbsp;/&nbsp;IMAP:
							<input type=\"checkbox\" name=\"popimap_access\" ". ($service->popimap_access=="t"?" checked ":"").">
						</td>
					</tr>
					<tr>
						<td align=\"right\">
							SMTP access:
							<input type=\"checkbox\" name=\"smtp_access\" ". ($service->smtp_access=="t"?" checked ":"").">
						</td>
						<td align=\"right\">
							PHP:
							<input type=\"checkbox\" name=\"php\" ". ($service->php=="t"?" checked ":"").">
						</td>
						<td align=\"right\">
							<font color=\"#007700\">CGI&nbsp;/&nbsp;PERL:</font>
							<input type=\"checkbox\" name=\"cgi_perl\" ". ($service->cgi_perl=="t"?" checked ":"").">
						</td>
					</tr>
					<tr>
						<td align=\"right\">
							MySQL:
							<input type=\"checkbox\" name=\"mysql\" ". ($service->mysql=="t"?" checked ":"").">
						</td>
						<td align=\"right\">
							<font color=\"#007700\">PGSQL:</font>
							<input type=\"checkbox\" name=\"pgsql\" ". ($service->pgsql=="t"?" checked ":"").">
						</td>
						<td align=\"right\">
							<font color=\"#007700\">Statistic:</font>
							<input type=\"checkbox\" name=\"stat\" ". ($service->stat=="t"?" checked ":"").">
						</td>
					</tr>
					<tr>
						<td align=\"right\">
							Error pages:
							<input type=\"checkbox\" name=\"error_pages\" ". ($service->error_pages=="t"?" checked ":"").">
						</td>
						<td align=\"right\">
							<font color=\"#007700\">Secure dir:</font>
							<input type=\"checkbox\" name=\"secure_dir\" ". ($service->secure_dir=="t"?" checked ":"").">
						</td>
						<td align=\"right\">
							Support:
							<input type=\"checkbox\" name=\"support\" ". ($service->support=="t"?" checked ":"").">
						</td>
					</tr>
					<tr>
						<td align=\"right\">
							<font color=\"#007700\">Backup:</font>
							<input type=\"checkbox\" name=\"backup\" ". ($service->backup=="t"?" checked ":"").">
						</td>
						<td align=\"right\">
							<font color=\"#007700\">SSI:</font>
							<input type=\"checkbox\" name=\"ssi\" ". ($service->ssi=="t"?" checked ":"").">
						</td>
						<td align=\"right\">
							&nbsp;
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr height=\"40\">
				<td align=\"left\">
					<input class=\"simple_button\" type=\"submit\" value=\"Apply changes\">
					</form>
				</td>
				<td align=\"right\">

				</td>
			</tr>
			</table>

			<form name=\"mysql_remove_form\" method=\"POST\" action=\"mysql_db_action.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
  			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"2%\" class=\"content\">&nbsp;</td>
				<td width=\"74%\" class=\"content\">MySQL Database Name</td>
				<td width=\"20%\" class=\"content\">Database Size</td>
			</tr>
			<span id=\"mysqls_count\" title=\"".$HM->SQL->get_num_rows($mysqls)."\"></span>

			";

		for ($i = 0; $i < $HM->FC->SQL->get_num_rows($mysqls); $i++) {
			$data = $HM->FC->SQL->get_object($mysqls);

			echo "<tr>
				<span id=\"mysql_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"mysql_checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"mysql_change.php?id=".$data->id_table."\">".$data->login."</a></td>
				<td class=\"content_3\">".$data->quota."</td>
			</tr>";
			$mysqls_empty = FALSE;
		}

		if ($mysqls_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"4\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"4\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
							<a href=\"javascript:checkall_mysqls(1)\">Select all</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href=\"javascript:checkall_mysqls(0)\">Clear all</a>
						</td>
						<td align=\"right\" NOWRAP>
							<input onClick=\"remove_mysqls()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
							</form>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"mysql\" method=\"POST\" action=\"mysql_db_add.php\">
							<input type=\"hidden\" name=\"id_zone\" value=\"".$id."\">
							<input type=\"submit\" value=\"Create MySQL DB\" class=\"simple_button\">
							</form>
						</td>
					</tr>
					</table>
				</td>
			</tr>";

		echo "
			</table>
			</form>

			<form name=\"pgsql_remove_form\" method=\"POST\" action=\"pgsql_db_action.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
  			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"2%\" class=\"content\">&nbsp;</td>
				<td width=\"74%\" class=\"content\">PostgreSQL Database Name</td>
				<td width=\"20%\" class=\"content\">Database Size</td>
			</tr>
			<span id=\"pgsqls_count\" title=\"".$HM->SQL->get_num_rows($pgsqls)."\"></span>

			";

		for ($i = 0; $i < $HM->FC->SQL->get_num_rows($pgsqls); $i++) {
			$data = $HM->FC->SQL->get_object($pgsqls);

			echo "<tr>
				<span id=\"pgsql_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"pgsql_checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"pgsql_change.php?id=".$data->id_table."\">".$data->login."</a></td>
				<td class=\"content_3\">".$data->quota."</td>
			</tr>";
			$pgsqls_empty = FALSE;
		}

		if ($pgsqls_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"4\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"4\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
							<a href=\"javascript:checkall_pgsqls(1)\">Select all</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href=\"javascript:checkall_pgsqls(0)\">Clear all</a>
						</td>
						<td align=\"right\" NOWRAP>
							<input onClick=\"remove_pgsqls()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
							</form>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"pgsql\" method=\"POST\" action=\"pgsql_db_add.php\">
							<input type=\"hidden\" name=\"id_zone\" value=\"".$id."\">
							<input type=\"submit\" value=\"Create PostgreSQL DB\" class=\"simple_button\">
							</form>
						</td>
					</tr>
					</table>
				</td>
				</td>
			</tr>";

		echo "
			</table>
			</form>

		</td>
		<td width=\"50%\" valign=\"top\" align=\"left\">

			<form name=\"subdomain_remove_form\" method=\"POST\" action=\"subdomain_action.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
  			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"top\">
				<td width=\"6%\" class=\"content\">&#035;</td>
				<td width=\"39%\" class=\"content\">Subdomain</td>
				<td width=\"10%\" class=\"content\">Type</td>
				<td width=\"10%\" class=\"content\">Prior</td>
				<td width=\"25%\" class=\"content\">Record</td>
			</tr>
			<span id=\"subdomains_count\" title=\"".$HM->SQL->get_num_rows($subdomains)."\"></span>

			";

		for ($i = 0; $i < $HM->CC->SQL->get_num_rows($subdomains); $i++) {
			$data = $HM->CC->SQL->get_object($subdomains);

			echo "<tr>
				<span id=\"subdomain_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\">".$data->name."</td>
				<td class=\"content_3\">".$data->type."</td>
				<td class=\"content_3\">".($data->type=="MX"?$data->prior:"&nbsp;")."</td>
				<td class=\"content_3\">".$data->record."</td>
			</tr>";
			$subdomains_empty = FALSE;
		}

		if ($subdomains_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"5\">Empty</td></tr>";

		echo "
			</form>
			 <tr valign=\"top\">
				<td colspan=\"5\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
						</td>
						<td align=\"right\" NOWRAP>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"subdomain\" method=\"POST\" action=\"zone_manage_change.php?id_zone=".$id."\">
							<input type=\"submit\" value=\"Manage Subdomain\" class=\"simple_button\">
							</form>
						</td>
					</tr>
					</table>
				</td>
			</tr>";

		echo "
			</table>
			</form>


			<form name=\"email_remove_form\" method=\"POST\" action=\"email_action.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
  			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"2%\" class=\"content\">&nbsp;</td>
				<td width=\"50\" class=\"content\">E-mail</td>
				<td width=\"44%\" class=\"content\">Name</td>
			</tr>
			<span id=\"emails_count\" title=\"".$HM->SQL->get_num_rows($mails)."\"></span>

			";

		for ($i = 0; $i < $HM->MC->SQL->get_num_rows($mails); $i++) {
			$data = $HM->MC->SQL->get_object($mails);

			echo "<tr>
				<span id=\"email_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"email_checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"mail_change.php?id=".$data->id_table."\">".$data->login."</a></td>
				<td class=\"content_3\">".$data->name."</td>
			</tr>";
			$mails_empty = FALSE;
		}

		if ($mails_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"4\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"4\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
							<a href=\"javascript:checkall_emails(1)\">Select all</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href=\"javascript:checkall_emails(0)\">Clear all</a>
						</td>
						<td align=\"right\" NOWRAP>
							<input onClick=\"remove_emails()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
							</form>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"email\" method=\"POST\" action=\"mail_add.php\">
							<input type=\"hidden\" name=\"zone\" value=\"".$id."\">
							<input type=\"submit\" value=\"Create E-mail\" class=\"simple_button\">
							</form>
						</td>
					</tr>
					</table>
				</td>
			</tr>";

		echo "
			</table>
			</form>


			<form name=\"ftp_remove_form\" method=\"POST\" action=\"ftp_action.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
  			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"2%\" class=\"content\">&nbsp;</td>
				<td width=\"74%\" class=\"content\">FTP</td>
				<td width=\"20%\" class=\"content\">Root Dir</td>
			</tr>
			<span id=\"ftps_count\" title=\"".$HM->SQL->get_num_rows($ftps)."\"></span>

			";

		for ($i = 0; $i < $HM->FC->SQL->get_num_rows($ftps); $i++) {
			$data = $HM->FC->SQL->get_object($ftps);

			echo "<tr>
				<span id=\"ftp_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"ftp_checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"ftp_change.php?id=".$data->id_table."\">".$data->login."</a></td>
				<td class=\"content_3\">".$data->rootdir."</td>
			</tr>";
			$ftps_empty = FALSE;
		}

		if ($ftps_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"4\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"4\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
							<a href=\"javascript:checkall_ftps(1)\">Select all</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href=\"javascript:checkall_ftps(0)\">Clear all</a>
						</td>
						<td align=\"right\" NOWRAP>
							<input onClick=\"remove_ftps()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
							</form>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"ftp\" method=\"POST\" action=\"ftp_add.php\">
							<input type=\"hidden\" name=\"id_zone\" value=\"".$id."\">
							<input type=\"submit\" value=\"Create FTP\" class=\"simple_button\">
							</form>
						</td>
					</tr>
					</table>
				</td>
			</tr>";

		echo "
			</table>
			</form>

			<form name=\"area_area_remove_form\" method=\"POST\" action=\"area_area_action.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
  			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"2%\" class=\"content\">&nbsp;</td>
				<td width=\"74%\" class=\"content\">Areas</td>
				<td width=\"20%\" class=\"content\">Item</td>
			</tr>
			<span id=\"area_areas_count\" title=\"".$HM->SQL->get_num_rows($areas)."\"></span>

			";

		for ($i = 0; $i < $HM->FC->SQL->get_num_rows($areas); $i++) {
			$data = $HM->FC->SQL->get_object($areas);

			echo "<tr>
				<span id=\"area_area_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"area_area_checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"area_change.php?id=".$data->id_table."\">".$data->title."</a></td>
				<td class=\"content_3\">".$data->item."</td>
			</tr>";
			$pgsqls_empty = FALSE;
		}

		if ($pgsqls_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"4\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"4\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
							<a href=\"javascript:checkall_area_areas(1)\">Select all</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href=\"javascript:checkall_area_areas(0)\">Clear all</a>
						</td>
						<td align=\"right\" NOWRAP>
							<input onClick=\"remove_area_areas()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
							</form>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"area_area\" method=\"POST\" action=\"area_area_add.php\">
							<input type=\"hidden\" name=\"zone\" value=\"".$id."\">
							<input type=\"submit\" value=\"Create Area\" class=\"simple_button\">
							</form>
						</td>
					</tr>
					</table>
				</td>
			</tr>";

		echo "
			</table>
			</form>

			<form name=\"area_user_remove_form\" method=\"POST\" action=\"area_user_action.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
  			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"2%\" class=\"content\">&nbsp;</td>
				<td width=\"74%\" class=\"content\">Area Users</td>
				<td width=\"20%\" class=\"content\">Item</td>
			</tr>
			<span id=\"area_users_count\" title=\"".$HM->SQL->get_num_rows($area_users)."\"></span>

			";

		for ($i = 0; $i < $HM->FC->SQL->get_num_rows($area_users); $i++) {
			$data = $HM->FC->SQL->get_object($area_users);

			echo "<tr>
				<span id=\"area_user_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"area_user_checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"area_change.php?id=".$data->id_table."\">".$data->name."</a></td>
				<td class=\"content_3\">".$data->item."</td>
			</tr>";
			$area_users_empty = FALSE;
		}

		if ($area_users_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"4\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"4\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
							<a href=\"javascript:checkall_area_users(1)\">Select all</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href=\"javascript:checkall_area_users(0)\">Clear all</a>
						</td>
						<td align=\"right\" NOWRAP>
							<input onClick=\"remove_area_users()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
							</form>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"area_user\" method=\"POST\" action=\"area_user_add.php\">
							<input type=\"hidden\" name=\"zone\" value=\"".$id."\">
							<input type=\"submit\" value=\"Create Area User\" class=\"simple_button\">
							</form>
						</td>
					</tr>
					</table>
				</td>
			</tr>";

		echo "
			</table>
			</form>

			<form name=\"area_group_remove_form\" method=\"POST\" action=\"area_group_action.php?act=remove\">
			<input type=\"hidden\" name=\"ids\" value=\"\">
  			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"top\">
				<td width=\"4%\" class=\"content\">&#035;</td>
				<td width=\"2%\" class=\"content\">&nbsp;</td>
				<td width=\"74%\" class=\"content\">Area Groups</td>
			</tr>
			<span id=\"area_groups_count\" title=\"".$HM->SQL->get_num_rows($area_groups)."\"></span>

			";

		for ($i = 0; $i < $HM->FC->SQL->get_num_rows($area_groups); $i++) {
			$data = $HM->FC->SQL->get_object($area_groups);

			echo "<tr>
				<span id=\"area_group_".$i."\" title=\"".$data->id_table."\"></span>
				<td class=\"content_3\" align=\"center\">".($i+1)."</td>
				<td class=\"content_3\" align=\"center\"><input type=\"checkbox\" id=\"area_group_checkbox_".$i."\"></td>
				<td class=\"content_3\"><a href=\"area_change.php?id=".$data->id_table."\">".$data->name."</a></td>
			</tr>";
			$area_groups_empty = FALSE;
		}

		if ($area_groups_empty)
			echo "<tr><td align=\"center\" class=\"content_3\" colspan=\"3\">Empty</td></tr>";

		echo "  <tr valign=\"top\">
				<td colspan=\"3\" width=\"100%\">
					<table width=\"100%\">
					<tr  width=\"100%\">
						<td width=\"100%\" align=\"left\" NOWRAP>
							<a href=\"javascript:checkall_area_groups(1)\">Select all</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href=\"javascript:checkall_area_groups(0)\">Clear all</a>
						</td>
						<td align=\"right\" NOWRAP>
							<input onClick=\"remove_area_groups()\" class=\"simple_button\" name=\"remove\" type=\"button\" id=\"remove\" value=\"Remove\">
							</form>
						</td>
						<td align=\"right\" NOWRAP>
							<form name=\"area_group\" method=\"POST\" action=\"area_group_add.php\">
							<input type=\"hidden\" name=\"zone\" value=\"".$id."\">
							<input type=\"submit\" value=\"Create Area Group\" class=\"simple_button\">
							</form>
						</td>
					</tr>
					</table>
				</td>
			</tr>";

		echo "
			</table>
			</form>

		</td>
		</tr>
		</table>";
	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

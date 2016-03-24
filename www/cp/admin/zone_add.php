<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		$HM = new Hosting_Manage;

		$clients = $HM->CC->fetch_all_client_account();

		if ($clients == FALSE) {
			echo "Can't fetch all client accounts: ".$_SESSION["adminpanel_error"];
			return;
		}

		$client = safe_get("client", "POST", 255);
		$zone_name = safe_get("zone_name", "POST", 255);
		$zone_type = safe_get("zone_type", "POST", 15);
		$service_type = safe_get("service_type", "POST", 15);

		$disk_space = safe_get("disk_space", "POST", 20);
		$subdomain_max_count = safe_get("subdomain_max_count", "POST", 10);
		$email_max_count = safe_get("email_max_count", "POST", 10);
		$email_alias_max_count = safe_get("email_alias_max_count", "POST", 10);
		$email_reply_max_count = safe_get("email_reply_max_count", "POST", 10);
		$ftp_max_count = safe_get("ftp_max_count", "POST", 10);
		$mysql_max_count = safe_get("mysql_max_count", "POST", 10);
		$pgsql_max_count = safe_get("pgsql_max_count", "POST", 10);
		$cp = safe_get("cp", "POST", 5);
		$ftp_access = safe_get("ftp_access", "POST", 5);
		$popimap_access = safe_get("popimap_access", "POST", 5);
		$smtp_access = safe_get("smtp_access", "POST", 5);
		$php = safe_get("php", "POST", 5);
		$cgi_perl = safe_get("cgi_perl", "POST", 5);
		$ssi = safe_get("ssi", "POST", 5);
		$mysql = safe_get("mysql", "POST", 5);
		$pgsql = safe_get("pgsql", "POST", 5);
		$stat = safe_get("stat", "POST", 5);
		$error_pages = safe_get("error_pages", "POST", 5);
		$secure_dir = safe_get("secure_dir", "POST", 5);
		$support = safe_get("support", "POST", 5);
		$backup = safe_get("backup", "POST", 5);

		echo "
			<form action=\"zone_action.php?act=add_zone\" method=\"POST\" name=\"zone_manage\">
			<table border=\"0\" cellspacing=\"3\" cellpadding=\"0\">
			<tr>
				<td colspan=\"2\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Create Hosting Zone
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
					Client:
				</td>
				<td>
					&nbsp;<select name=\"client\" class=\"simple_select\">";

		for ($i = 0; $i < $HM->CC->SQL->get_num_rows($clients); $i++) {
			$data = $HM->CC->SQL->get_object($clients);

			echo "<option ".($client==$data->id_table?"selected":"")." value=\"".$data->id_table."\">".$data->login."</option>\n";
		}

		echo "
					</select>
				</td>
			</tr>
			<tr height=\"10\">
				<td colspan=\"2\">
				</td>
			</tr>
			<tr valign=\"top\">
				<td width=\"20%\" class=\"content2\">
					Zone Name:
				</td>
				<td width=\"80%\">
					<input name=\"zone_name\" value=\"".$zone_name."\" class=\"simple_input\" size=\"40\" maxlength=\"255\" type=\"text\">
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Zone Type:
				</td>
				<td>
					<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
					<tr>
						<td>
							&nbsp;<select class=\"simple_select\" name=\"zone_type\">
								<option value=\"our\" ".($zone_type=="our"?"selected":"")." >Our</option>
								<option value=\"parked\" ".($zone_type=="parked"?"selected":"").">Parked</option>
								<option value=\"transfer\" ".($zone_type=="transfer"?"selected":"").">Transfer</option>
							</select>
						</td class=\"content2\">

						<td class=\"content2\">
							Service Type:
						</td>
						<td>
							&nbsp;<select name=\"service_type\" class=\"simple_select\">
								<option value=\"free\" ".($service_type=="free"?"selected":"").">Free</option>
								<option value=\"full\" ".($service_type=="full"?"selected":"").">Full</option>
								<option value=\"custom\" ".($service_type=="custom"?"selected":"").">Custom</option>
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
				<td>
					&nbsp;<select name=\"disk_space\" class=\"simple_select\">
						<option value=\"5242880\" ".($disk_space=="5242880"?"selected":"").">5 Mb</option>
						<option value=\"10485760\" ".($disk_space=="10485760"?"selected":"").">10 Mb</option>
						<option value=\"15728640\" ".($disk_space=="15728640"?"selected":"").">15 Mb</option>
						<option value=\"20971520\" ".($disk_space=="20971520"?"selected":"").">20 Mb</option>
						<option value=\"26214400\" ".($disk_space=="26214400"?"selected":"").">25 Mb</option>
						<option value=\"52428800\" ".($disk_space=="52428800"?"selected":"").">50 Mb</option>
						<option value=\"104857600\" ".($disk_space=="104857600"?"selected":"").">100 Mb</option>
						<option value=\"0\" ".($disk_space=="0"?"selected":"").">Unlimited</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class=\"content2\">
					Maximum:
					<br>
					(0 is unlimited)
				</td>
				<td NOWRAP>
					<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
					<tr>
						<td align=\"right\">
							&nbsp;&nbsp;
							sub-domain:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"subdomain_max_count\" value=\"".($subdomain_max_count!=""?$subdomain_max_count:"5")."\">
						</td>
						<td align=\"right\">
							&nbsp;&nbsp;
							ftp:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"ftp_max_count\" value=\"".($ftp_max_count!=""?$ftp_max_count:"5")."\">
						</td>
					</tr>
					</tr>
						<td align=\"right\">
							&nbsp;&nbsp;
							e-mail:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"email_max_count\" value=\"".($email_max_count!=""?$email_max_count:"5")."\">
						</td>
						<td align=\"right\">
							&nbsp;&nbsp;
							e-mail aliases:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"email_alias_max_count\" value=\"".($email_alias_max_count!=""?$email_alias_max_count:"50")."\">
						</td>
					</tr>
					</tr>
						<td align=\"right\">
							&nbsp;&nbsp;
							e-mail autoreply:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"email_reply_max_count\" value=\"".($email_reply_max_count!=""?$email_reply_max_count:"20")."\">
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
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"mysql_max_count\" value=\"".($mysql_max_count!=""?$mysql_max_count:"5")."\">
						</td>
						<td align=\"right\">
							&nbsp;&nbsp;
							pgsql db:
						</td>
						<td>
							&nbsp;<input class=\"simple_input\" size=\"5\" maxlength=\"10\" type=\"text\" name=\"pgsql_max_count\" value=\"".($pgsql_max_count!=""?$pgsql_max_count:"5")."\">
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
							<input type=\"checkbox\" name=\"cp\" ".($cp==""?"checked":($cp=="on"?"checked":"")).">
						</td>
						<td align=\"right\">
							FTP access:
							<input type=\"checkbox\" name=\"ftp_access\" ".($ftp_access==""?"checked":($ftp_access=="on"?"checked":"")).">
						</td>
						<td align=\"right\">
							POP&nbsp;/&nbsp;IMAP:
							<input type=\"checkbox\" name=\"popimap_access\" ".($popimap_access==""?"checked":($popimap_access=="on"?"checked":"")).">
						</td>
					</tr>
					<tr>
						<td align=\"right\">
							SMTP access:
							<input type=\"checkbox\" name=\"smtp_access\" ".($smtp_access==""?"checked":($smtp_access=="on"?"checked":"")).">
						</td>
						<td align=\"right\">
							PHP:
							<input type=\"checkbox\" name=\"php\" ".($php==""?"checked":($php=="on"?"checked":"")).">
						</td>
						<td align=\"right\">
							<font color=\"#007700\">CGI&nbsp;/&nbsp;PERL:</font>
							<input type=\"checkbox\" name=\"cgi_perl\" ".($cgi_perl==""?"":($cgi_perl=="on"?"checked":"")).">
						</td>
					</tr>
					<tr>
						<td align=\"right\">
							MySQL:
							<input type=\"checkbox\" name=\"mysql\" ".($mysql==""?"checked":($mysql=="on"?"checked":"")).">
						</td>
						<td align=\"right\">
							<font color=\"#007700\">PGSQL:</font>
							<input type=\"checkbox\" name=\"pgsql\" ".($pgsql==""?"":($pgsql=="on"?"checked":"")).">
						</td>
						<td align=\"right\">
							<font color=\"#007700\">Statistic:</font>
							<input type=\"checkbox\" name=\"stat\" ".($stat==""?"":($stat=="on"?"checked":"")).">
						</td>
					</tr>
					<tr>
						<td align=\"right\">
							Error pages:
							<input type=\"checkbox\" name=\"error_pages\" ".($error_pages==""?"checked":($error_pages=="on"?"checked":"")).">
						</td>
						<td align=\"right\">
							<font color=\"#007700\">Secure dir:</font>
							<input type=\"checkbox\" name=\"secure_dir\" ".($secure_dir==""?"":($secure_dir=="on"?"checked":"")).">
						</td>
						<td align=\"right\">
							Support:
							<input type=\"checkbox\" name=\"support\" ".($support==""?"checked":($support=="on"?"checked":"")).">
						</td>
					</tr>
					<tr>
						<td align=\"right\">
							<font color=\"#007700\">Backup:</font>
							<input type=\"checkbox\" name=\"backup\" ".($backup==""?"":($backup=="on"?"checked":"")).">
						</td>
						<td align=\"right\">
							<font color=\"#007700\">SSI:</font>
							<input type=\"checkbox\" name=\"ssi\" ".($ssi==""?"":($ssi=="on"?"checked":"")).">
						</td>
						<td align=\"right\">
							&nbsp;
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr height=\"40\">
				<td colspan=\"2\">
					&nbsp;<input class=\"simple_button\" type=\"submit\" value=\"Create Zone\">
				</td>
			</tr>
			</table>
			</form>
		<script language=\"javascript\">
			document.zone_manage.zone_name.focus();
		</script>
		";
	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

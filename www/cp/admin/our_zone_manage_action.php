<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	$HM = new Hosting_Manage();

	$manual = safe_get("manual", "POST", 255);
	$id_zone = safe_get("id_zone", "POST", 255);

	if ($id_zone == "") {
		$_SESSION["adminpanel_error"] = "Can't find id_zone !";
		Header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
		exit;
	}

	$zone_data = $HM->ZC->fetch_our_zone($id_zone);
	if ($zone_data == FALSE) {
		$_SESSION["adminpanel_error"] = "Can't fetch our zone: ".$_SESSION["adminpanel_error"];
		return FALSE;
	}

	if ($manual == "") {

		$action = safe_get("action", "POST", 255);

		if ($action == "") {
			$_SESSION["adminpanel_error"] = "Can't find action !";
			Header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
			exit;
		}

		if ($action == "create_d_mx") {

			$prior = safe_get("new_pri", "POST", 255);
			$record = safe_get("new_rr", "POST", 255);

			if ($prior != "" && $record != "") {
				if ($HM->ZC->create_mx_record_in_dns($zone_data->name, $prior, $record) != TRUE) {
					$_SESSION["adminpanel_error"] .= "<br>Can't create MX record";
					Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
					exit;
				}
			}
			else {
				$_SESSION["adminpanel_error"] = "Not enought data";
				Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
				exit;
			}

			Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
			exit;
		}

		if ($action == "modify_d_mx") {

			$old_prior = safe_get("old_pri", "POST", 255);
			$prior = safe_get("new_pri", "POST", 255);
			$record = safe_get("new_rr", "POST", 255);

			if ($old_prior != "" && $prior != "" && $record != "") {

				if ($HM->ZC->update_mx_record_in_dns($zone_data->name, $old_prior,  $prior, $record) != TRUE) {
					$_SESSION["adminpanel_error"] .= "<br>Can't update MX record";
					Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
					exit;
				}
			}
			else {
				$_SESSION["adminpanel_error"] = "Not enought data";
				Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
				exit;
			}

			Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
			exit;
		}

		if ($action == "delete_d_mx") {

			$prior = safe_get("new_pri", "POST", 255);

			if ($prior != "") {

				if ($HM->ZC->remove_mx_record_in_dns($zone_data->name, $prior) != TRUE) {
					$_SESSION["adminpanel_error"] .= "<br>Can't remove MX record";
					Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
					exit;
				}
			}
			else {
				$_SESSION["adminpanel_error"] = "Not enought data";
				Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
				exit;
			}

			Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
			exit;
		}

		if ($action == "create_record") {

			$domain = safe_get("new_d_name", "POST", 255);
			$type = safe_get("new_rt", "POST", 255);
			$prior = safe_get("new_pri", "POST", 255);
			$record = safe_get("new_rr", "POST", 255);

			if ($domain != "" && $type != "" && $prior != "" && $record != "") {
				if ($HM->ZC->create_domain_record($zone_data->id_table, $zone_data->name, $domain, $type, $prior, $record) != TRUE) {
					$_SESSION["adminpanel_error"] .= "<br>Can't create domain record";
					Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
					exit;
				}
			}
			else {
				$_SESSION["adminpanel_error"] = "Not enought data";
				Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
				exit;
			}

			Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
			exit;
		}

		if ($action == "modify_record") {

			$old_domain = safe_get("old_d_name", "POST", 255);
			$old_type = safe_get("old_rt", "POST", 255);
			$old_prior = safe_get("old_pri", "POST", 255);

			$domain = safe_get("new_d_name", "POST", 255);
			$type = safe_get("new_rt", "POST", 255);
			$prior = safe_get("new_pri", "POST", 255);
			$record = safe_get("new_rr", "POST", 255);

			$id_domain = safe_get("id_domain", "POST", 255);

			if ($id_domain != "" && $old_domain != "" && $old_type != "" && $old_prior != "" && $domain != "" && $type != "" && $prior != "" && $record != "") {
				if ($HM->ZC->update_domain_record($zone_data->id_table, $zone_data->name, $id_domain, $old_domain, $old_type, $old_prior, $domain, $type, $prior, $record) != TRUE) {
					$_SESSION["adminpanel_error"] .= "<br>Can't update domain record";
					Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
					exit;
				}
			}
			else {
				$_SESSION["adminpanel_error"] = "Not enought data";
				Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
				exit;
			}

			Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
			exit;
		}

		if ($action == "delete_record") {

			$old_domain = safe_get("old_d_name", "POST", 255);
			$old_type = safe_get("old_rt", "POST", 255);
			$old_prior = safe_get("old_pri", "POST", 255);

			$id_domain = safe_get("id_domain", "POST", 255);

			if ($id_domain != "" && $old_domain != "" && $old_type != "" && $old_prior != "") {
				if ($HM->ZC->remove_domain_record($zone_data->name, $id_domain, $old_domain, $old_type, $old_prior) != TRUE) {
					$_SESSION["adminpanel_error"] .= "<br>Can't remove domain";
					Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
					exit;
				}
			}
			else {
				$_SESSION["adminpanel_error"] = "Not enought data";
				Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
				exit;
			}

			Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
			exit;
		}

		// This call comes from our_zone_change.php
		if ($action == "remove") {

			$old_domain = safe_get("old_d_name", "POST", 255);
			$old_type = safe_get("old_rt", "POST", 255);
			$old_prior = safe_get("old_pri", "POST", 255);

			$id_domain = safe_get("id_domain", "POST", 255);

			if ($id_domain != "" && $old_domain != "" && $old_type != "" && $old_prior != "") {
				if ($HM->ZC->remove_domain_record($zone_data->name, $id_domain, $old_domain, $old_type, $old_prior) != TRUE) {
					$_SESSION["adminpanel_error"] .= "<br>Can't remove domain";
					Header("Location: our_zone_change.php?id=".$id_zone."\n\n");
					exit;
				}
			}
			else {
				$_SESSION["adminpanel_error"] = "Not enought data";
				Header("Location: our_zone_change.php?id=".$id_zone."\n\n");
				exit;
			}

			Header("Location: our_zone_change.php?id=".$id_zone."\n\n");
			exit;
		}

		$_SESSION["adminpanel_error"] = "Unknown action";
		Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
		exit;
	}
	else {

		$zone_content = pure_get("zone_content", "POST", 65535);
		$zone_content = strtr($zone_content, array(chr(13).chr(10) => chr(10)));

		if ($zone_content == "") {
			$_SESSION["adminpanel_error"] = "Can't find zone_content !";
			Header("Location: our_zone_manage_change_manually.php?id_zone=".$id_zone."\n\n");
			exit;
		}

		$records = array();
		$lines = explode("\n", $zone_content);

		for ($i = 0; $i < count($lines); $i++) {
			if (preg_match("/([\w\d\\.]+)\s+(IN)?\s+([\w\d]+)\s+(([\d])*\s+)*([\w\d\\.]+)/i", $lines[$i], $matches)) {

				if ($matches[3] != "SOA") {
					$item["domain"] = $matches[1];
					$item["type"] = $matches[3];
					$item["prior"] = $matches[4];
					$item["record"] = $matches[6];

					array_push($records, $item);
				}
			}
		}

		if (count($lines) == 0) {
			$_SESSION["adminpanel_error"] .= "<br>Can't find any situable domain record. Check Your zone content";
			Header("Location: our_zone_manage_change_manually.php?id_zone=".$id_zone."\n\n");
			exit;
		}

		if ($HM->ZC->remove_all_subdomains_of_our_zone_in_db($id_zone) != TRUE) {
			$_SESSION["adminpanel_error"] .= "<br>Can't remove all subdomains in our zone";
			Header("Location: our_zone_manage_change_manually.php?id_zone=".$id_zone."\n\n");
			exit;
		}

		for ($i = 0; $i < count($records); $i++) {
			if ($HM->ZC->add_our_zone_subdomain_in_db($id_zone, $records[$i]["domain"], strtoupper($records[$i]["type"]), $records[$i]["prior"], $records[$i]["record"]) != TRUE) {
				$_SESSION["adminpanel_error"] .= "<br>Can't create subdomain in our zone";
				Header("Location: our_zone_manage_change_manually.php?id_zone=".$id_zone."\n\n");
				exit;
			}
		}

		if ($HM->ZC->change_our_zone_subdomain_manually($zone_data->name, $zone_content) != TRUE) {
			$_SESSION["adminpanel_error"] .= "<br>Can't manually update our zone";
			Header("Location: our_zone_manage_change_manually.php?id_zone=".$id_zone."\n\n");
			exit;
		}

		Header("Location: our_zone_manage_change.php?id_zone=".$id_zone."\n\n");
		exit;
	}

	$_SESSION["adminpanel_error"] = "Unknown action";
	Header("Location: ".$_SERVER["HTTP_REFERER"]."\n\n");
	exit;
?>
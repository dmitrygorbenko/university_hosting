<?php
Header("Content-type: text/javascript");

Header("Expires: Wed, 11 Nov 1998 11:11:11 GMT\n".
"Cache-Control: no-cache\n".
"Cache-Control: must-revalidate\n".
"Pragma: no-cache");
?>
	function MM_findObj(n, d) {
		var p, i, x;

		if (!d)
			d=document;

		if ((p = n.indexOf("?")) > 0 && parent.frames.length) {
			d = parent.frames[n.substring(p+1)].document;
			n = n.substring(0,p);
		}

		if (!(x = d[n]) && d.all)
			x = d.all[n];

		for (i=0; !x && i < d.forms.length; i++)
			x = d.forms[i][n];

		for(i=0; !x && d.layers && i < d.layers.length; i++)
			x = MM_findObj(n, d.layers[i].document);

		if (!x && d.getElementById)
			x = d.getElementById(n);

		return x;
	}

	function MM_swapImage() {
		var i, j=0, x, a = MM_swapImage.arguments;
		document.MM_sr=new Array;
		for(i=0; i < (a.length - 2); i+=3)
			if ((x = MM_findObj(a[i])) != null) {
				document.MM_sr[j++] = x;
				if (!x.oSrc)
					x.oSrc = x.src;
				x.src = a[i+2];
			}
	}

	function MM_swapImgRestore() {
		var i, x, a=document.MM_sr;
		for(i=0; a && i < a.length && (x = a[i]) && x.oSrc; i++)
			x.src = x.oSrc;
	}

// ******************    Clients list   ****************************

	function checkall_clients(val) {
		ch_count = document.getElementById("clients_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("client_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_clients() {
		frm = document.client_remove_form;
		ch_count = document.getElementById("clients_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("client_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("client_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select clients !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    Zones list   ****************************

	function checkall_zones(val) {
		ch_count = document.getElementById("zones_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("zone_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_zones() {
		frm = document.zone_remove_form;
		ch_count = document.getElementById("zones_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("zone_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("zone_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select zones !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    Subdomains list   ****************************

	function checkall_subdomains(val) {
		ch_count = document.getElementById("subdomains_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("subdomain_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_subdomains() {
		frm = document.subdomain_remove_form;
		ch_count = document.getElementById("subdomains_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("subdomain_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("subdomain_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select subdomains !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    E-mails list   ****************************

	function checkall_emails(val) {
		ch_count = document.getElementById("emails_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("email_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_emails() {
		frm = document.email_remove_form;
		ch_count = document.getElementById("emails_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("email_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("email_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select emails !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    E-mail aliases list   ****************************

	function checkall_email_aliases(val) {
		ch_count = document.getElementById("email_alias_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("email_alias_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_email_aliases() {
		frm = document.email_alias_remove_form;
		ch_count = document.getElementById("email_alias_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("email_alias_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("email_alias_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select email aliases !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    E-mail forwarders list   ****************************

	function checkall_email_forwarders(val) {
		ch_count = document.getElementById("email_forwarder_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("email_forwarder_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_email_forwarders() {
		frm = document.email_forwarder_remove_form;
		ch_count = document.getElementById("email_forwarder_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("email_forwarder_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("email_forwarder_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select email forwarders !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    E-mail autoreply list   ****************************

	function checkall_email_autoreplies(val) {
		ch_count = document.getElementById("email_autoreply_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("email_autoreply_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_email_autoreplies() {
		frm = document.email_autoreply_remove_form;
		ch_count = document.getElementById("email_autoreply_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("email_autoreply_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("email_autoreply_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select email auto answers !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    E-mail lists list   ****************************

	function checkall_maillists(val) {
		ch_count = document.getElementById("maillist_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("maillist_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_maillists() {
		frm = document.maillist_remove_form;
		ch_count = document.getElementById("maillist_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("maillist_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("maillist_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select mailing lists !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    Ftps list   ****************************

	function checkall_ftps(val) {
		ch_count = document.getElementById("ftps_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("ftp_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_ftps() {
		frm = document.ftp_remove_form;
		ch_count = document.getElementById("ftps_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("ftp_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("ftp_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select ftps !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    Redirectors list   ****************************

	function checkall_redirectors(val) {
		ch_count = document.getElementById("redirectors_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("redirector_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_redirectors() {
		frm = document.redirector_remove_form;
		ch_count = document.getElementById("redirectors_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("redirector_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("redirector_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select redirectors !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    Webdirs list   ****************************

	function checkall_webdirs(val) {
		ch_count = document.getElementById("webdirs_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("webdir_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_webdirs() {
		frm = document.webdir_remove_form;
		ch_count = document.getElementById("webdirs_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("webdir_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("webdir_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select webdirs !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    Area Users list   ****************************

	function checkall_area_users(val) {
		ch_count = document.getElementById("area_users_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("area_user_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_area_users() {
		frm = document.area_user_remove_form;
		ch_count = document.getElementById("area_users_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("area_user_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("area_user_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select users !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    Area Groups list   ****************************

	function checkall_area_groups(val) {
		ch_count = document.getElementById("area_groups_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("area_group_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_area_groups() {
		frm = document.area_group_remove_form;
		ch_count = document.getElementById("area_groups_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("area_group_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("area_group_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select groups !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    Area Areas list   ****************************

	function checkall_area_areas(val) {
		ch_count = document.getElementById("area_areas_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("area_area_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_area_areas() {
		frm = document.area_area_remove_form;
		ch_count = document.getElementById("area_areas_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("area_area_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("area_area_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select areas !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    Mysqls list   ****************************

	function checkall_mysqls(val) {
		ch_count = document.getElementById("mysqls_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("mysql_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_mysqls() {
		frm = document.mysql_remove_form;
		ch_count = document.getElementById("mysqls_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("mysql_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("mysql_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select mysqls !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    Pgsqls list   ****************************

	function checkall_pgsqls(val) {
		ch_count = document.getElementById("pgsqls_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("pgsql_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_pgsqls() {
		frm = document.pgsql_remove_form;
		ch_count = document.getElementById("pgsqls_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("pgsql_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("pgsql_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select pgsqls !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    Our Zones list   ****************************

	function checkall_our_zones(val) {
		ch_count = document.getElementById("our_zones_count");
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("our_zone_checkbox_" + i);
			ch_box.checked = val;
		}
	}

	function remove_our_zones() {
		frm = document.our_zone_remove_form;
		ch_count = document.getElementById("our_zones_count");
		ch_got = 0;
		url = "";
		for( i=0 ; i < ch_count.title ; i++) {
			ch_box = document.getElementById("our_zone_checkbox_" + i);
			if (ch_box.checked == "1") {
				elem_id = document.getElementById("our_zone_" + i);
				url = url + elem_id.title + ":";
				ch_got++;
			}
		}
		if (ch_got == 0) {
			alert("Select zones !");
			return;
		}
		frm.ids.value = url;
		frm.submit();
	}

// ******************    Common functions   ****************************

	function update_page(url, id_tag, arg) {
		tag = document.getElementById(id_tag);
		document.location = url + "?" + arg + "=" + tag.value;
	}

	function reply_message(id, folder) {
		url = "reply.php?id=" + id + "&folder=" + folder;
		document.location = url;
	}

	function OpenTree() {
		libwindow=window.open("ftp_select.php", "_blank", "menubar=no, width=470, height=350, top=100, left=100, scrollbars=yes");
	}

	function OpenID() {
		mywin = window.open('id_select.php', '_blank', 'width=500, height=500, top=50, left=50, menubar=no,  scrollbars=yes');
	}

	function AddAddress(strType, strAddress) {
		obj = eval('document.ComposeForm.'+strType);
		if (obj.value == '')
			obj.value = strAddress;
		else
			obj.value = obj.value + ', ' + strAddress;
	}

	function CopyText(dir) {
		window.opener.document.forms[0].homedir.value = dir;
		self.close();
	}

	function CopyID(uid, gid) {
		window.opener.document.forms[0].uid.value = uid;
		window.opener.document.forms[0].gid.value = gid;
		self.close();
	}

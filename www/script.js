
	function submit_private() {
		frm = document.forms[0];
		login = document.getElementById("login");
		firstname = document.getElementById("firstname");
		pw1 = document.getElementById("password");
		pw2 = document.getElementById("password2");
		if (login.value == "" ||
			firstname.value == "" ||
			pw1.value == "") {
			alert("Вы не заполнили все поля !");
			return;
		}
		if (pw1.value != pw2.value) {
			alert("Пароли не совпадают !");
			return;
		}
		frm.submit();
	}

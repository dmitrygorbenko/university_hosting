<?php
	require_once ("header.php");
	include_once ($INCLUDE_PATH."manage.php");

	function body_page() {

		echo "
			<table cellspacing=\"3\" cellpadding=\"0\" border=\"0\" width=\"100%\">
			<tr valign=\"center\" height=\"62\" width=\"100%\">
				<td colspan=\"4\" width=\"100%\">
					<table width=\"100%\" class=\"standart\" cellspacing=\"0\"  cellpadding=\"0\" border=\"0\">
					<tr valign=\"center\" height=\"62\" width=\"100%\">
						<td height=\"62\" width=\"85\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\">
							<img src=\"../img/table/table_icon.jpg\" width=\"85\" height=\"62\">
						</td>
						<td height=\"62\" width=\"99%\" align=\"left\" background=\"../img/table/table_background.jpg\" class=\"title\" colspan=\"3\">
							Задачи на выполнение
						</td>
						<td height=\"62\" width=\"27\" align=\"right\" background=\"../img/table/table_background.jpg\">
							<img src=\"../img/table/table_icon_close.jpg\" width=\"27\" height=\"62\">
						</td>
					</tr>
					</table>
				</td>
			</tr>";

		echo "
			<tr valign=\"top\">
			<td class=\"content2\">
				1
			</td>
			<td class=\"content2\">
				23.10.2005
			</td>
			<td class=\"content_3_left\">
				Если у зоны есть несколько WebDir's и мы меняем либо cgi_perl лиюо ssi права,
				то надо также изменить все остальные WebDir'ы
			</td>
			</tr>";

		echo "
			<tr valign=\"top\">
			<td class=\"content2\">
				2
			</td>
			<td class=\"content2\">
				24.10.2005
			</td>
			<td class=\"content_3_left\">
				При изменении ID зоны необходимо также изменить владельцев файлов.<br>
				Так заголовки уже написанны, осталось только код вбить.

			</td>
			</tr>";

		echo "</table>";

	}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

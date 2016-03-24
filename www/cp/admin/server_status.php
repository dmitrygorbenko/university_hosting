<?php
	require_once ("header.php");

function body_page() {

	// Get the server load averages (if possible)
	if (@file_exists('/proc/loadavg') && is_readable('/proc/loadavg')) {
		$fh = @fopen('/proc/loadavg', 'r');
		$load_averages = @fread($fh, 64);
		@fclose($fh);

		$load_averages = @explode(' ', $load_averages);
		//$server_load = $load_averages[0].' '.$load_averages[1].' '.$load_averages[2];
		$server_load = $load_averages[0];
		//$server_load  = $load_averages;
	}
	else
		$server_load = 'Not available';

	// Get the server uptime
	if (@file_exists('/proc/uptime') && is_readable('/proc/uptime')) {
		$fh = @fopen('/proc/uptime', 'r');
		$uptime = @fread($fh, 64);
		@fclose($fh);

		$uptime = @explode(' ', $uptime);
		$updays = (int)($uptime[0] / (60*60*24));
		$upminutes = $uptime[0] / 60;
		$uphours = $upminutes / 60;
		$uphours = $uphours % 24;
		$upminutes = $upminutes % 60;

		$server_uptime = $updays." day".($updays>1?"s":"")." ";

		if($uphours != "")
			$server_uptime = $server_uptime.$uphours.":".$upminutes;
		else
			$server_uptime = $server_uptime.$upminutes." min";
	}
	else
		$server_uptime = 'Not available';

	// Get the server memory usage
	if (@file_exists('/proc/meminfo') && is_readable('/proc/meminfo')) {
		$fh = @fopen('/proc/meminfo', 'r');
		while (!feof($fh)) {
			$buff = @fgets($fh, 64);
			if ($buff == "")
				break;

			$buff = @explode(' ', $buff);
			if ($buff[0] == "MemTotal:")
				$server_memtotal = $buff[count($buff)-2];
			elseif ($buff[0] == "MemFree:")
				$server_memfree = $buff[count($buff)-2];
			elseif ($buff[0] == "SwapTotal:")
				$server_swaptotal = $buff[count($buff)-2];
			elseif ($buff[0] == "SwapFree:")
				$server_swapfree = $buff[count($buff)-2];
		}
		@fclose($fh);

		bcscale(4);
		$mempers = bcdiv($server_memtotal-$server_memfree, $server_memtotal) * 100;
		$server_memusage = $mempers."% of ".convert_size($server_memtotal*1024);

		$swappers = bcdiv($server_swaptotal-$server_swapfree, $server_swaptotal) * 100;
		$server_swapusage = $swappers."% of ".convert_size($server_swaptotal*1024);
	}
	else {
		$server_memusage = 'Not available';
		$server_swapusage = 'Not available';
	}

	// Get the server cpu info
	if (@file_exists('/proc/cpuinfo') && is_readable('/proc/cpuinfo')) {
		$fh = @fopen('/proc/cpuinfo', 'r');
		while (!feof($fh)) {
			$buff = @fgets($fh, 64);
			if ($buff == "")
				break;

			$buff = @explode(':', $buff);

			if (strpos($buff[0], "MHz") != 0) {
				$server_cpuspeed = $buff[1];
			}
			elseif (strpos($buff[0], "name") != 0) {
				$server_cpumodel = $buff[1];
			}
		}
		@fclose($fh);
	}
	else {
		$server_cpuspeed = 'Not available';
		$server_cpumodel = 'Not available';
	}

	// Get the server disks status
	$system_disks = array();

	exec("df", $fd_exec);
	if ($fd_exec !== "") {

		unset($fd_exec[0]);
		for ($i = 0; $i <= count($fd_exec); $i++)
			$disks_pre .= " ".$fd_exec[$i];

		$disks_pre = explode(" ", $disks_pre);

		$k = count($disks_pre);
		for ($i = 0; $i <= $k; $i++)
			if (strlen($disks_pre[$i]) == 0)
				unset($disks_pre[$i]);

		$disks = array();

		for ($i = 0; $i <= $k; $i++)
			if ($disks_pre[$i] != "")
				array_push($disks, $disks_pre[$i]);

		unset($disks_pre);

		$i = 0;
		while ($i <= (count($disks) - 6)) {

			$descr["dev"] = $disks[$i];
			$descr["mount_point"] = $disks[$i+5];
			$descr["usage"] = $disks[$i+4];
			$descr["total"] = $disks[$i+1];

			array_push($system_disks, $descr);
			$i+=6;
		}
	}

echo "
	<center>
		<span class=\"title\">
			VHS - Virtual Hosting System&nbsp;/&nbsp;hPanel
		</span>
		<br>
	</center>

	<br>

	<table>
	<tr>
		<td valign=\"top\">
			<table cellpadding=\"0\" cellspacing=\"3\">
			<tr>
				<td class=\"general\" width=\"100px\" colspan=\"2\">
					<strong>Server Status:</strong>
				</td>
			</tr>
			<tr>
				<td class=\"general_1\" width=\"100px\">
					Server Load
				</td>
				<td class=\"general_2\" width=\"100px\">
					".$server_load."
				</td>
			</tr>
			<tr>
				<td class=\"general_1\" width=\"100px\">
					Uptime
				</td>
				<td class=\"general_2\" width=\"100px\">
					".$server_uptime."
				</td>
			</tr>
			<tr>
				<td class=\"general_1\" width=\"100px\">
					CPU Model
				</td>
				<td class=\"general_2\" width=\"100px\">
					".$server_cpumodel."
				</td>
			</tr>
			<tr>
				<td class=\"general_1\" width=\"100px\">
					CPU MHz
				</td>
				<td class=\"general_2\" width=\"100px\">
					".$server_cpuspeed."
				</td>
			</tr>
			<tr>
				<td class=\"general_1\" width=\"100px\">
					Memory Usage
				</td>
				<td class=\"general_2\" width=\"100px\">
					".$server_memusage."
				</td>
			</tr>
			<tr>
				<td class=\"general_1\" width=\"100px\">
					Swap Usage
				</td>
				<td class=\"general_2\" width=\"100px\">
					".$server_swapusage."
				</td>
			</tr>
			";

	for ($i = 0; $i < count($system_disks); $i++) {
		echo "
			<tr>
				<td class=\"general_1\" width=\"100px\">
					Disk ".$system_disks[$i]["dev"]." (".$system_disks[$i]["mount_point"].")
				</td>
				<td class=\"general_2\" width=\"100px\">
					".$system_disks[$i]["usage"]." of ".convert_size($system_disks[$i]["total"] * 1024)."
				</td>
			</tr>
			";
	}
		echo "
			</table>
		</td>
	</tr>
	</table>
";

}

	entire_html();

	$_SESSION["adminpanel_error"] = "";
?>

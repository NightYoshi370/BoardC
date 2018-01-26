<?php

	require "lib/function.php";
	
	pageheader("Stats");
	
	
	$records = $sql->fetchq("SELECT * FROM records");
	// Posts / User online records
	?>
	<br>
	<!-- board records start here -->
	<table class='main w'>
		<tr>
			<td class='head c' style='width: 88px'>Records</td>
			<td class='head'>&nbsp;</td>
		</tr>
		
		<tr>
			<td class='light c fonts'><b>Most posts within 24 hours:</b></td>
			<td class='dim fonts'><?php echo $records['maxpostsd'].", on ".printdate($records['maxpostsdtime']) ?></td>
		</tr>
		
		<tr>
			<td class='light c fonts'><b>Most posts within 1 hour:</b></td>
			<td class='dim fonts'><?php echo $records['maxpostsh'].", on ".printdate($records['maxpostshtime']) ?></td>
		</tr>
		
		<tr>
			<td class='light c fonts'><b>Most users online:</b></td>
			<td class='dim fonts'><?php echo $records['maxusersonline'].", on ".printdate($records['maxusersonlinetime']).": ".$records['maxusersonline_txt'] ?></td>
		</tr>
	</table>
	<br>
	
	<!-- table status -->
	<table class='main w'>
		<tr>
			<td class="head c">Table name</td>
			<td class="head c">Rows</td>
			<td class="head c">Avg. data/row</td>
			<td class="head c">Data size</td>
			<td class="head c">Index size</td>
			<td class="head c">Overhead</td>
			<td class="head c">Total size</td>
		</tr>
	<?php
	
	unset($records);
	
	// Table status
	// This works better in MyISAM, but this codebase uses InnoDB
	
	$tables = ['announcements', 'announcements_old', 'announcements_read', 'ipbans', 'pms', 'posts', 'posts_old', 'radar', 'threads', 'threads_read', 'users', 'user_comments', 'dailystats'];
	$status  = $sql->query("SHOW TABLE STATUS IN $sqldb WHERE Name IN ('".implode("','", $tables)."')");
	while ($x = $sql->fetch($status)){
		print "
		<tr>
			<td class='dim c'>{$x['Name']}</td>
			<td class='dim r'>".number_format($x['Rows'])."</td>
			<td class='dim r'>".number_format($x['Avg_row_length'])."</td>
			<td class='dim r'>".number_format($x['Data_length'])."</td>
			<td class='dim r'>".number_format($x['Index_length'])."</td>
			<td class='dim r'>".number_format($x['Data_free'])."</td>
			<td class='dim r'>".number_format($x['Data_length'] + $x['Index_length'])."</td>
		</tr>";
	}
	unset($status, $x, $tables);
	
	?>
	</table>
	<br>
	<!-- daily records -->
	<table class="main w c fonts">
		<tr><td class="head" colspan=9>Daily stats</td></tr>
		<tr>
			<td class="dark">Date</td>
			<td class="dark">Total users</td>
			<td class="dark">Total posts</td>
			<td class="dark">Total threads</td>
			<td class="dark">Total views</td>
			<td class="dark">New users</td>
			<td class="dark">New posts</td>
			<td class="dark">New threads</td>
			<td class="dark">New views</td>
		</tr>
	<?php
	
	$stats = $sql->query("SELECT day d, users u, posts p, threads t, views v FROM dailystats");
	for ($u = $p = $t = $v = 0; $s = $sql->fetch($stats); $u = $s['u'], $p = $s['p'], $t = $s['t'], $v = $s['v']){
		print "
		<tr>
			<td class='light'>".date("m-d-y", $s['d'])."</td>
			<td class='dim'>{$s['u']}</td>
			<td class='dim'>{$s['p']}</td>
			<td class='dim'>{$s['t']}</td>
			<td class='dim'>{$s['v']}</td>
			<td class='dim'>".($s['u'] - $u)."</td>
			<td class='dim'>".($s['p'] - $p)."</td>
			<td class='dim'>".($s['t'] - $t)."</td>
			<td class='dim'>".($s['v'] - $v)."</td>
		</tr>";
	}
	
	echo "</table>";
	pagefooter();
?>
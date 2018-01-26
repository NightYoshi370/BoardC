<?php

	require "lib/function.php";
	
	if (!$config['irc-server']) {
		errorpage("This board doesn't have an IRC Server.");
	}
	
	pageheader("IRC Chat");
	
	if (isset($_GET['server'])){
		$id		= filter_int($_GET['server']);
		//$name 	= "Guest_".str_pad(mt_rand(0, 9999), 4, 0, STR_PAD_LEFT);
		$name	= "Guest";
		echo "
		<br>
		<table class='main w'>
			<tr>
				<td class='head c'>
					IRC Chat - Server ".($id+1)." ({$config['irc-server'][$id]})
				</td>
			</tr>
			<tr>
				<td class='dim lh5'>
					<iframe src='https://kiwiirc.com/client/{$config['irc-server'][$id]}/?nick={$name}|?{$config['public-chan']}' style='border:0;width:100%;height:550px;'></iframe>
				</td>
			</tr>
		</table>";
	}
	else {
		for ($servers = '', $i = 0, $c = count($config['irc-server'])-1; $i <= $c; $i++){
			$servers .= "<a href='irc.php?server=$i'>".$config['irc-server'][$i]."</a>".(!$i ? " (preferred)" : "").($i != $c ? " | " : "");
		}
		?>
		<br>
		<table class="main w c">
			<tr>
				<td class="head">
					<b>IRC Chat - <?php echo $config['irc-title'] ?>, <?php echo $config['public-chan'] ?></b>
				</td>
			</tr>
			<tr>
				<td class="light">
					Server List: <?php echo $servers ?>
				</td>
			</tr>
			<tr>
				<td class="dim">
					&nbsp;<br>
					Please choose a server to connect to.<br>
					&nbsp;
				</td>
			</tr>
		</table>
		<?php
	}
	
	
	
	
	
	
	?>
	<br>
	<table class="main w">
		<tr><td class="head c"><b>Quick Help</b></td></tr>
		<tr>
			<td class="light">Commands:
				<br><tt>/nick [name]</tt> - changes your name
				<br><tt>/me [action]</tt> - does an action (try it)
				<br><tt>/msg [name] [message]</tt> - send a private message to another user
				<br><tt>/join [#channel]</tt> - joins a channel
				<br><tt>/part [#channel]</tt> - leaves a channel
				<br><tt>/quit [message]</tt> - obvious
			</td>
		</tr>
	</table>
		
	<?php
	
	
	pagefooter();
?>
<?php
	
	require "lib/function.php";
	
	$time 	= filter_int($_GET['time']);
	$ip 	= filter_string($_POST['ip']);
	
	if (!$time) $time = 300; // 5 minutes
	
	if ($isadmin){
		if (isset($_GET['ban'])){
			checktoken(true);
			$id = filter_int($_GET['ban']);
			userban($id, false, false, "", "Banned User ID #$id (online.php ban)"); 
			redirect("online.php?time=$time");
		}
		else if (filter_string($_GET['ipban'])){
			checktoken(true);
			$ip = base64_decode($_GET['ipban']);
			ipban("online.php ban", "IP Banned $ip (online.php ban)", $ip, 0, true); 
			redirect("online.php?time=$time");
		}
	}
	
	pageheader("Online users");

	
	$online = $sql->query("
		SELECT 	h.ip, h.time, h.page, h.useragent, i.id ipbanned,
				$userfields, u.posts, u.lastpost
		FROM hits h
		
		LEFT JOIN users  u ON h.user = u.id
		LEFT JOIN ipbans i ON h.ip   = i.ip
		
		WHERE h.time > ".(ctime()-$time)."
		".($ip && $isadmin ? "AND h.ip = '".addslashes($ip)."'" : "")."

		ORDER BY ".(isset($_GET['ipsort']) && $isadmin ? "h.ip" : "h.time DESC")."
	");
	
	$txt 	= array("", "");
	$i 		= array(0, 0);
	
	
	/*
		Page format
	*/
	while ($user = $sql->fetch($online)){
		
		$page = urlformat($user['page']);
		
		if ($user['id']){ // registered user
			$i[0]++;
			
			if ($user['powerlevel'] < 0) {
				$ban_txt = "[BANNED]";
			} else {
				$ban_txt = "<a class='danger' href='?ban={$user['id']}&auth=$token'>Ban</a>";
			}
			
			$txt[0] .= "
			<tr>
				<td class='light c'>$i[0]</td>
				<td class='dim'>
					".makeuserlink(false, $user).
					($isadmin ? 
						"<small> - $ban_txt</small>" :
						""
					)."
				</td>
				<td class='light c'>".printdate($user['time'])."</td>
				<td class='light c'>".($user['lastpost'] ? printdate($user['lastpost']) : "None")."</td>
				<td class='dim'><a href='$page' rel='nofollow'>".htmlspecialchars($page)."</a></td>
				<td class='dim c'>{$user['posts']}</td>
				".($isadmin ? "<td class='light c'>".ipformat($user)."</td>" : "")."
			</tr>";
		}
		else{ // guest
			
			$i[1]++;
			$txt[1] .= "
			<tr>
				<td class='light c'>$i[1]</td>
				<td class='dim fonts c'>".htmlspecialchars(prepare_string($user['useragent']))."</td>
				<td class='light c'>".printdate($user['time'])."</td>
				<td class='dim'><a href='$page' rel='nofollow'>".htmlspecialchars($page)."</a></td>
				".($isadmin ? "<td class='light c'>".ipformat($user)."</td>" : "")."
			</tr>";
		}
	}
	
	
	if ($isadmin) {
		?>
		<center>
		<form method='POST' action='online.php'>
		
		<table class='main'>
		
			<tr><td class='head fonts c' colspan='2'>Admin functions</td></tr>
			
			<tr>
				<td class='light c' style='width: 100px'>
					<b>IP Filter</b>
				</td>
				<td class='dim'>
					<input type='text' name='ip' value="<?php echo $ip ?>"> <input type='submit' value='Go'>
				</td>
			</tr>
		</table>
		
		</form>
		</center>
		<?php
	}
		
	?>
	<div class='fonts'>
		Show online users during the last: 
		<a href='?time=60'>minute</a> | 
		<a href='?time=300'>5 minutes</a> | 
		<a href='?time=900'>15 minutes</a> | 
		<a href='?time=3600'>hour</a> | 
		<a href='?time=86400'>day</a>
		<?php echo ($isadmin ? " | <a href='?ipsort'>Sort by IP</a>" : "") ?>
	</div>
	Online users during the last <?php echo choosetime($time) ?>:
	<table class='main w'>
		<tr>
			<td class='head c' style='width: 20px'>&nbsp;</td>
			<td class='head c' style='width: 200px'>Username</td>
			<td class='head c' style='width: 130px'>Last activity</td>
			<td class='head c' style='width: 180px'>Last post</td>
			<td class='head c'>URL</td>
			<td class='head c' style='width: 60px'>Posts</td>
			<?php echo ($isadmin ? "<td class='head c' style='width: 230px'>IP</td>" : "") ?>
		</tr>
		<?php echo $txt[0] ?>	
	</table>
	<br>
	Guests online in the past <?php echo choosetime($time) ?>:
	<table class='main w'>
		<tr>
			<td class='head c' style='width: 20px'>&nbsp;</td>
			<td class='head c' style='width: 300px'>User agent</td>
			<td class='head c' style='width: 130px'>Last activity</td>
			<td class='head c'>URL</td>
			<?php echo ($isadmin ? "<td class='head c' style='width: 180px'>IP</td>" : "") ?>
		</tr>
		<?php echo $txt[1] ?>	
	</table>
	
	<?php
	
	pagefooter();
	
	function urlformat($url){
		// Hide the token
		$url = prepare_string($url);
		$url = preg_replace("'&auth=(.*?)(&?)'si", "$2", $url); //'(;|^)$filter(;|$)'
		$url = preg_replace("'\??&debug's", "", $url);
		return $url;
	}
	
	function ipformat($user){
		global $token;
		return "
		<a href='admin-ipsearch.php?ip={$user['ip']}'>{$user['ip']}</a>
		<small> - 
			<a class='danger' href='?ipban=".base64_encode($user['ip'])."&auth=$token'>IP Ban</a> 
			<a href='https://www.google.com/search?q={$user['ip']}'>[G]</a> 
			<a href='https://en.wikipedia.org/wiki/User:{$user['ip']}'>[W]</a>
			".($user['ipbanned'] ? "<br>[IP BANNED]" : "")."
		</small>";
	}

?>
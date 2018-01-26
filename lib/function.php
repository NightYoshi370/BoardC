<?php
	
	/*
		Common board actions
	*/
	
	define('BOARDC_VERSION', "(13/10/16) v1.0d");
	
	error_reporting(0);	// Suppress everything, including fatal errors (the integrated error handler will be used instead)
	ini_set("default_charset", "UTF-8");
	header("Content-Type: text/html; charset=utf-8");
	
	// The cache is bad (and should feel bad)
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Pragma: no-cache");
	
	
	$startingtime 	= microtime(true);
	$errors 		= array();
	$userfields 	= "u.name, u.displayname, u.sex, u.powerlevel, u.namecolor, u.rainbow, u.birthday, u.id"; // consistency is god
	
	// Quick and dirty sanity check
	if (!function_exists("password_hash"))
		die("This board uses features like password_hash and array dereferencing.<br>Please update to at least PHP 5.5");
	
	if (!file_exists("lib/config.php")){
		// Normally we don't load layout.php first as it requires config and helpers
		// but the dialog function just happens to not need any
		require "lib/layout.php";
		dialog(	"Error - BoardC",
			"Board not installed",
			"The board has to be configured before it can be used.<br><br>Click <a href='install.php'>here</a> to install the board.");
	}
	
	require "lib/config.php";
	require "lib/mysql.php";
	require "lib/helpers.php";
	require "lib/rpg.php";
	require "lib/layout.php";	
	require "lib/threadpost.php";
	
	
	// a-good-feature: an extremely lazy recreation of register_globals to aid in importing AB 1.92 scripts using "that good feature"
	if ($hacks['a-good-feature']){
		extract($_REQUEST, EXTR_OVERWRITE);
	}
	
	// Database connection. It handles the give up message by itself.
	$sql 			= new mysql;
	$connection 	= $sql->connect($sqlhost,$sqluser,$sqlpass,$sqldb,$sqlpersist);

	set_error_handler('error_reporter');
	
	// Stop this nonsense, leave $loguser available from here
	$loguser = array(
		'id' 			=> 0,
		'password' 		=> NULL,
		'powerlevel' 	=> 0,
		'ppp' 			=> 25,
		'tpp' 			=> 25,
		'dateformat' 	=> $config['default-date-format'],
		'timeformat' 	=> $config['default-time-format'],	
		'tzoff'		 	=> 0,
		'theme'		 	=> 1,
		'signsep'		=> 1,
		'showhead'		=> 1,
		'rankset'		=> 1,
		'startingday'	=> 0
	);
	
	// Update timed user bans
	$sql->query("
		UPDATE users
		SET ban_expire = 0, powerlevel = 0
		WHERE ban_expire != 0 AND powerlevel = '-1'	AND ban_expire < ".ctime()."
	");
	
	// Update timed IP Bans before attempting to check if we're ip banned
	$sql->query("
		DELETE FROM ipbans
		WHERE ban_expire != 0 AND ban_expire < ".ctime()."
	");

	// Check if we're IP Banned from the board.
	// The checks below are for special reasons given by certain board auto-bans, in order to print a proper description of the ban.
	// Remember: http://php.net/manual/en/filter.filters.validate.php
	$xforward = filter_var(filter_string($_SERVER['HTTP_X_FORWARDED_FOR']) , FILTER_VALIDATE_IP);
	
	$ipbanned = $sql->fetchq("
		SELECT id, reason
		FROM ipbans
		WHERE INSTR('{$_SERVER['REMOTE_ADDR']}', ip) > 0
		".($xforward ? "OR INSTR('$xforward', ip) > 0" : "")."
	");
	if (filter_int($ipbanned['id'])){
		
		$reason = filter_string($ipbanned['reason']);

		if ($reason == "Recovery"){
			dialog(	"Board message",
					"Password recovery",
					"<center>It seems you have failed 5 login attempts.<br><br>If you have lost your password, send an email at {$config['admin-email']} for password recovery.");
		}
		else if ($reason == "Regkey"){
			dialog(	"Board message",
					"Registration",
					"<center>You have tried to register an account using an incorrect registration key 5 times.<br>If you have forgotten it, send an email at {$config['admin-email']} to request it again.<small><br>(and if you actually tried to guess it, well, you can go <a href='http://www.google.com'>here</a>)");
		}
		else if (!$reason)
			$reason = "Unspecified reason";
		
		dialog("
			Board message",
			"You are banned.",
			"<center>
				You have been banned from the board for the following reason:<br>
				$reason<br>
				<br>
				Contact info:<br>
				E-mail: {$config['admin-email']}
			</center>");
	}
	
	// Get current script name. This used to be a function, but it's better to get it here once and reference $scriptname instead.
	$path 			= explode("/", $_SERVER['SCRIPT_NAME']);
	$scriptname 	= $path[count($path)-1];
	unset($path);
	
	$fw_error = ""; // This is also used to display thread errors
	
	if (filter_bool($meta['allorigin'])) {
		header("Access-Control-Allow-Origin: *");
	}
	
	// Filter out any control codes for these variables

	$_SERVER['QUERY_STRING'] 	= filter_string($_SERVER['QUERY_STRING'],		true);
	$_SERVER['HTTP_REFERER'] 	= filter_string($_SERVER['HTTP_REFERER'],		true);
	$_SERVER['HTTP_USER_AGENT'] = filter_string($_SERVER['HTTP_USER_AGENT'],	true);
	

	// Get everything from the misc table and few select records under a single array
	$miscdata = $sql->fetchq("SELECT m.*,r.maxusersonline,r.maxpostsd,r.maxpostsh FROM misc m, records r");
	
	// Increase the views only if you're a valid user.
	$views = $miscdata['views'] + 1;
	$sql->query("UPDATE misc SET views = views + 1");
	
	// Authentication check
	if (filter_int($_COOKIE['id']) && filter_string($_COOKIE['verify'])){
		
		$userdata = $sql->fetchq("
			SELECT u.*, r.*
			FROM users u
			LEFT JOIN users_rpg r ON u.id = r.id
			WHERE u.id = ".intval($_COOKIE['id'])
		, true)[0];
		
		if ($_COOKIE['verify'] == $userdata['password']){
			$loguser = $userdata;
			
			// Check if the current IP is different to the IP used to login
			// As this shouldn't normally happen, force log off the user.
			if ($loguser['lastip'] != $_SERVER['REMOTE_ADDR']){
				
				irc_reporter("WARNING - Attempted access to {$loguser['name']} (ID #{$loguser['id']}, lastip {$loguser['lastip']}) from {$_SERVER['REMOTE_ADDR']}", 1);
				if ($sql->resultq("SELECT 1 FROM ipbans WHERE ip = '{$loguser['lastip']}'")){ // Just in case
					irc_reporter("Previous IP address was IP banned - updated IP bans list.", 1);
					ipban("IP Ban Evasion", false);
					header("Location: index.php");
					x_die();
				}
				// Clear the cookies and request login.
				setcookie('id', NULL);
				setcookie('verify', NULL);
				//errorpage("It seems your IP has changed. Please login again."); // TODO: This message is for testing. Eventually comment this to enable auto refresh.
				redirect("?{$_SERVER['QUERY_STRING']}");
			}

		}
		else {
			// If the password hashes doesn't match, clear the cookies and refresh the page.
			setcookie('id', NULL);
			setcookie('verify', NULL);
			irc_reporter("IP {$_SERVER['REMOTE_ADDR']} tried to login using a wrong cookie pass as {$userdata['name']}", 1);
			redirect("?{$_SERVER['QUERY_STRING']}");
		}
		
		unset($userdata);
	}

	if ($config['force-userid'])
		$loguser = $sql->fetchq("SELECT * FROM users WHERE id = ".$config['force-userid'], true)[0];
	
	// Special Auto-Admin cases
	
	if ($config['admin-board'] || $loguser['id'] == 1) // First user is always sysadmin. always
		$loguser['powerlevel'] = 5;
	
	if ($loguser['powerlevel'] == 5)
		$adminips[] = $_SERVER['REMOTE_ADDR'];
	
	else if (in_array($_SERVER['REMOTE_ADDR'], $adminips))
		$loguser['powerlevel'] = 5;
	
	if ($loguser['powerlevel'] == 5)
		$config['show-comments'] = true;
	
	$power_txt[6] = $power_txt[0];
		
	// Everybody gets rainbow colors on the last day of the year!
	if (date('d') == 31 && date('m') == 12){
		$hacks['username-rainbow'] = true;
	}
	
	// No more redundant powlcheck calls
	$sysadmin 		= ($loguser['powerlevel'] >= 5);
	$isadmin 		= ($loguser['powerlevel'] >= 4);
	$ismod			= ($loguser['powerlevel'] >= 3);
	// Local mod definition skipped. This is calculated based on current forum
	$isprivileged 	= ($loguser['powerlevel'] >= 1);
	$isbanned 		= ($loguser['powerlevel'] <	 0);
	$ispermabanned	= ($loguser['powerlevel'] == '-2');
	
	// with the powerlevels set up, register now the shutdown function
	register_shutdown_function('error_printer', false, $sysadmin, $GLOBALS['errors']);
	
	if (!$loguser['timeformat'])
		$loguser['timeformat'] = $config['default-time-format'];
	if (!$loguser['dateformat'])
		$loguser['dateformat'] = $config['default-date-format'];
	
	/*
		Is the board disabled?
		If so, only admins can browse the board, everybody else gets the "board is disabled" page.
	*/
	if ($miscdata['disable']){
		if ($isadmin){
			$fw_error = "
				<div style='text-align: center; color: #0f0; padding: 3px; border: 5px dotted #0f0; background: #000;'>
					<b>Notice: This board has been disabled.</b>
				</div>";
		}
		else
			dialog(
			"The board is offline",
			"The board is (currently) offline",
			"The board is under manteniance, likely due to one of the following reasons.
				<ul>
					<li> Preventing something wrong from happening
					<li> Codebase upgrade
					<li> It's fun to disable the board (???)
					<small><li> Testing if the ACP works properly</small>
				</ul>
				
				<center>In the mean time, join <b>{$config['public-chan']}</b> on <b>".implode("/", $config['irc-server'])."</b>.</center>"
			);
	}
	
	// Private board: If you're not logged in you can't view the forums / do other stuff
	// It fits well with the regkey option
	if ($miscdata['private'] && !$loguser['id']){
		
		if (!in_array($scriptname, ['login.php', 'register.php'])){
			
			pageheader("401");
			?>
			<br>
			<center>
				<table class='main c'>
					<tr>
						<td class='head'>
							401 Unauthorized
						</td>
					</tr>
					<tr>
						<td class='light'>
							This is a private board.<br>
							You aren't able to view the forums unless you login.<br>
							<br>
							Please select a link below the board image.
						</td>
					</tr>
					
				</table>
			</center>
			<?php
			pagefooter();
			
		}
	}

	// Generate a token for all POST actions.
	$token = gettoken();
	
	// RPG Stuff
	if ($loguser['id']){
		
		// Generate coins through AB1.92's function
		$loguser['coins'] = coins($loguser['posts'], (ctime() - $loguser['since']) / 86400);
		
		$itemdb = getuseritems($loguser['id']);
		
		foreach($itemdb as $item){
			//if ($item['special'] == 2); // This should insert meow in the name / display name somewhere.
			// also note that the previous line should go elsewhere, as an effect like that
			// should be noticeable by every user
			if ($item['special'] == 3) $config['show-comments'] = true;
		}
		unset($itemdb); // Most of the time we don't need this after here.
	}
	

	// Delete obsolete online views entries (2+ days)
	$sql->query("DELETE FROM hits WHERE time < ".(ctime()-86400*2));
	
	// Daily stats
	$day = mktime(0,0,0); // Today
	
	$sql->query("
		INSERT INTO dailystats(day, users, posts, threads, views) VALUES
		($day, (SELECT COUNT(id) FROM users), (SELECT COUNT(id) FROM posts), (SELECT COUNT(id) FROM threads), $views)
		ON DUPLICATE KEY UPDATE users = VALUES(users), posts = VALUES(posts), threads = VALUES(threads), views = VALUES(views)
	");
	
	/*
		Don't update online views when browsing these pages
		as the they set the the forum / thread id before calling the function
	*/
	if (!in_array($scriptname, ["forum.php", "thread.php", "new.php", "admin-showlogs.php"])){
		update_hits();
	}
	
	// Define signature separators
	switch ($loguser['signsep']){
		case 1:  $sep = "<br><br>--------------------<br>"; break;
		case 2:  $sep = "<br><br>____________________<br>"; break;
		case 3:  $sep = "<br><br><hr>"; break;
		default: $sep = "<br><br>";
	}
	
	// Cookie status message
	if (isset($_COOKIE['msg'])) {
		$message = filter_string($_COOKIE['msg'], true);
		$message = messagebar('Message', input_filters($message));
		setcookie('msg', NULL);
	} else {
		$message = '';
	}
	
?>
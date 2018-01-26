<?php
	
	/*
		Default images
		These are used when other php theme files do not redefine them
	*/
	
	$IMG = array(
		'getlast' 		=> '<img src="images/status/default/getlast.png">',
		'getnew'		=> '<img src="images/status/default/getnew.png">',
		'statusfolder' 	=> 'images/status/default', // for thread status indicators (new, hot, ...)
		'numgfxfolder' 	=> 'images/numgfx/default', // font used for some numbers
		'newpoll'		=> '<img src="images/text/default/newpoll.png">',
		'newthread'		=> '<img src="images/text/default/newthread.png">',
		'newreply'		=> '<img src="images/text/default/newreply.png">',
		'threadclosed'	=> '<img src="images/text/default/threadclosed.png">'
		
	);
	
	// Global powerlevel name definitions
	$power_txt = array(
		'-2'	=> "Permabanned",
		'-1'	=> "Banned",
		0 		=> "Normal User",
		1 		=> "Normal +",
		2 		=> "Local Moderator",
		3 		=> "Global Moderator",
		4 		=> "Administrator",
		5 		=> "Sysadmin"
	);
	
	// <posts required> => <color>, <text>
	$syndrome_txt = array(
		75 		=> ['83F3A3', "'Reinfors Syndrome' "],
		100 	=> ['FFE323', "'Reinfors Syndrome' +"],
		150 	=> ['FF5353', "'Reinfors Syndrome' ++"],
		200 	=> ['CE53CE', "'Reinfors Syndrome' +++"],
		250 	=> ['8E83EE', "'Reinfors Syndrome' ++++"],
		300 	=> ['BBAAFF', "'Wooster Syndrome'!!"],
		350 	=> ['FFB0FF', "'Wooster Syndrome' +!!"],
		400 	=> ['FFB070', "'Wooster Syndrome' ++!!"],
		450 	=> ['C8C0B8', "'Wooster Syndrome' +++!!"],
		500 	=> ['A0A0A0', "'Wooster Syndrome' ++++!!"],
		600 	=> ['C762F2', "'Anya Syndrome'!!!"],
		800		=> ['62C7F2', "'Xkeeper Syndrome' +++++!!"],
		1000	=> ['FFFFFF', "'Something higher than Xkeeper Syndrome' +++++!!"]
	);
	
	// Acmlmboard compatiblity (to help in porting HTML)
	if ($hacks['ab-layout']){
		$header = $footer = "";
		$tblstart = "<table class='main'>";
		$tblend = "</table>";
		$tccellh = "<td class='head'";
		$tccell1 = "<td class='light'";
		$tccell1l = "<td class='light'";
		$tccell2 = "<td class='dim'";
		$tccell2l = "<td class='dim'";
		$radio = "<input type='radio' name";
		$inpt = "<input type='text' name";
		$inps = "<input type='submit' class='submit' name";
		$smallfont = "<span class='fonts'>";
		$txta = "<textarea wrap='virtual' name";
		// No equivalent exists for these
		$fonttag 		= "";
		$fonthead 		= "";
		$tinyfont 		= "";
		function printtimedif($x){pagefooter();} // Close enough
	}
	
	function pageheader($title, $show = true, $forum = 0, $mini = false){
		global $sql, $config, $hacks, $fw_error, $loguser, $views, $miscdata, $meta, $threadbug_txt, $token, $scriptname, $userfields;
		global $sysadmin, $isadmin, $isprivileged; // Powerlevels checks used for this function.
		$meta_txt 	= "";
		
		if (filter_bool($meta['noindex']) || $miscdata['private']){
			$meta_txt = "<meta name='robots' content='noindex, nofollow, noarchive'>";
			header('X-Robots-Tag: noindex, nofollow, noarchive', true);
		}
		
		if ($miscdata['private'] && !$loguser['id']){
			// Special layout for pages in a private board while not logged in
			$css 	= file_get_contents("css/night.css");
			print "
			<!doctype html>
			<html>
				<head>
					<title>{$config['board-name']} -- $title</title>
					<style type='text/css'>$css</style>
					<link rel='icon' type='image/png' href='images/favicon.png'>
					$meta_txt
			</head>
			<body>
			<table class='main c w fonts'>
				<tr>
					<td colspan=3 class='light'>
						<a href='{$config['board-url']}'>{$config['board-title']}</a><br>
						<a href='login.php'>Login</a> - <a href='register.php'>Register</a>
					</td>
				</tr>
			</table>
			";
			return;
		}
		
		$links = "";
		
		if ($isprivileged){
			$links .= "<a href='shoped.php'>Shop Editor</a> - ";		
		}
		if ($isadmin){
			$links .= "<a href='admin.php'>Admin</a> - ";
		}
		
		if (!$loguser['id']){
			$links .= "
				<a href='login.php'>Login</a> - 
				<a href='register.php'>Register</a>";
				
			$logoutform = "";
		}
		else{
			$links .= "
				<a href='javascript:document.logout.submit()'>Logout</a> - 
				<a href='search.php'>Search</a> - 
				<a href='editprofile.php'>Edit profile</a> - 
				".($config['enable-image-uploads'] ? "<a href='editavatars.php'>Edit avatars</a> - " : "")."
				<a href='event.php'>Edit events</a> - 
				<a href='radar.php'>Post radar</a> - 
				<a href='shop.php'>Item shop</a> - 
				<a href='forum.php?fav'>Favorites</a>
			";
			
			// TODO: While this moves the logout action to _POST, it also breaks the NoJS compatibility
			// See if there's a <noscript> alternative
			$logoutform = "<form action='login.php' name='logout' method='POST'><input type='hidden' name='action' value='Logout'></form>";

			
			if ($scriptname == 'index.php'){
				$links .= " - <a href='index.php?markforumread'>Mark all forums read</a>";
			}
			else if ($forum){
				$links .= " - <a href='index.php?markforumread&forumid=$forum'>Mark forum read</a>";
			}
		}
		
		$links2 = "
			<a href='index.php'>Main</a> - 
			<a href='memberlist.php'>Memberlist</a> -
			<a href='activeusers.php'>Active users</a> -
			<a href='calendar.php'>Calendar</a> - 
			<a href='irc.php'>IRC</a> - 
			<a href='online.php'>Online users</a>
			<br>
			<a href='ranks.php'>Ranks</a> - 
			<a href='faq.php'>Rules/FAQ</a> - 
			<a href='acs.php'>ACS</a> - 
			<a href='stats.php'>Stats</a> - 
			<a href='latestposts.php'>Latest posts</a> - 
			<a href='hex.php' target='_blank'>Color Chart</a> - 
			<a href='smilies.php' target='_blank'>Smilies</a>
		";
		
		
		if (isset($miscdata['theme'])){
			// To force override both loguser and forum theme setting
			$loguser['theme'] = $miscdata['theme'];
		}
		
		$themes 	= findthemes(true);
		
		// Check if the theme does exist
		$themepath = "css/".$themes[$loguser['theme']]['file'];
		if (!file_exists($themepath.".css")){
			
			// Use this failsafe theme in case the theme specified doesn't exist
			$css = "
			body {
				font-family: Verdana, Geneva, sans-serif;
				font-size: 13px;
			}
			.main{
				border-spacing: 0px;
				border-top:	#000000 1px solid;
				border-left: #000000 1px solid;
			}
			.light,.dim,.head,.dark{
				border-right:	#000000 1px solid;
				border-bottom:	#000000 1px solid;
			}
			textarea, input, select{
				border:	#000000 solid 1px;
			}
			.c{text-align: center;}
			.r{text-align: right;}
			.w{width: 100%;}
			.nobr{white-space: nowrap;}
			";
		}
		else{
			$css 	= file_get_contents($themepath.".css");
			// If the theme comes with variable replacements php file, load it too
			if (file_exists($themepath.".php"))
				include($themepath.".php");
		}

		if ($show) $title .= " -- ".$config['board-name'];
		
		// At this point, if only the basic layout is requested ($mini), stop here and put no doctype
		if ($mini){
			print "
			<html>
				<head>
					<title>$title</title>
					<style type='text/css'>$css</style>
					<link rel='icon' type='image/png' href='images/favicon.png'>
					$meta_txt
				</head>
			<body>
			";
			return;
		}
		
		$ctime 	= ctime();
		
		$newpmbox = $postradar = "";
		if ($loguser['id']){
			/*
				PM Box bar that shows up only when you have unread PMs 
			*/
			// The index page handles this by itself, while printing this isn't necessary in private.php for obvious reasons
			if ($scriptname != 'index.php' && $scriptname != 'private.php'){

				$newpm = $sql->fetchq("
					SELECT p.id pid, p.user, p.time, p.new, COUNT(p.new) count, $userfields
					FROM pms p
					LEFT JOIN users u ON p.user = u.id
					WHERE p.userto = {$loguser['id']} AND p.new = 1
					ORDER BY p.id DESC
				");
				
				if ($newpm['pid']){
					$newpmbox = "
					<br>
					<table class='main w c'>
						<tr>
							<td class='dark'>
								You have {$newpm['count']} new private message".($newpm['count']==1 ? "" : "s").", <a href='private.php?act=view&id={$newpm['pid']}'>last</a> by ".makeuserlink(false, $newpm)." at ".printdate($newpm['time'])."
							</td>
						</tr>
					</table>";
				}
			}
			
			/*
				Post radar
			*/
			if ($loguser['radar_mode']){

				$radar_q = array_merge(
				
					// Get the nearest four users based on postcount.
					// Done twice (sigh) to get it both for those with more posts and those with less.
					$sql->fetchq("
						SELECT u.id, $userfields uid, u.posts, ABS({$loguser['posts']}-u.posts) diff
						FROM users u
						WHERE u.posts > {$loguser['posts']}
						ORDER BY u.posts ASC, u.name ASC
						LIMIT 2
					", true, PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC),
					
					// and again...
					$sql->fetchq("
						SELECT u.id, $userfields uid, u.posts, ABS({$loguser['posts']}-u.posts) diff
						FROM users u
						WHERE u.posts < {$loguser['posts']}
						ORDER BY u.posts DESC, u.name ASC
						LIMIT 2
					", true, PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC),
					
					// Add the loguser info as 'bridge'
					array(array_merge($loguser,
					[
						'uid' 		=> $loguser['id'],
						'diff' 		=> 0
					]))
				
				);
				
				/* Old method. Used one single query but isn't accurate.
				$radar_q = $sql->fetchq("
					SELECT $userfields uid, u.posts, ABS(".$loguser['posts']."-u.posts) diff
					FROM users u
					ORDER by diff
					LIMIT 5
				", true);*/
			} else {
				// Standard radar
				
				$valid = $sql->resultq("SELECT 1 FROM radar WHERE user = {$loguser['id']}");
				
				if ($valid) {
					$radar_q = $sql->query("
						SELECT $userfields uid, u.posts, ABS({$loguser['posts']}-u.posts) diff
						FROM radar r
						LEFT JOIN users u ON r.sel = u.id
						WHERE r.user = {$loguser['id']}
					");
				} else {
					$radar_q = false;
				}

			}
			
			if ($radar_q){
				if (!$loguser['radar_mode']){
					while($x = $sql->fetch($radar_q)){
						$postradar .= radar_comp($loguser, $x);
					}
				}
				else{
					// Sort by posts (asc)
					uasort($radar_q, function($a,$b){return ((int) $b['posts']) - ((int) $a['posts']);});

					foreach($radar_q as $x){
						$postradar .= radar_comp($loguser, $x);
					}
				}
				
				$postradar = "You are $postradar.";
			}

		}
		
		print "
		<!doctype html>
		<html>
			<head>
				<title>$title</title>
				<style type='text/css'>$css</style>
				<link rel='icon' type='image/png' href='images/favicon.png'>
				$meta_txt
			</head>
			<body>
			$threadbug_txt
			$fw_error
			$logoutform
			<table class='main c w fonts'>
				<tr>
					<td colspan=3 class='light'><a href='{$config['board-url']}'>{$config['board-title']}</a><br>$links</td>
				</tr>
				<tr>
					<td class='dim' style='width: 120px'>
						<nobr>Views: $views</nobr>
					</td>
					<td class='dim'>
						$links2
					</td>
					<td class='dim' style='width: 120px'>
						<nobr>".printdate($ctime)."</nobr>
					</td>
					
				</tr>			
				<tr><td colspan=3 class='dim'>$postradar</td></tr>
			</table>
			$newpmbox";
	}
	
	function pagefooter(){
		global $config, $sql, $hacks, $sysadmin;
		
		// Get table rows worth of error info (if you have proper permissions)
		$errorlog = error_printer(true, $sysadmin || $config['force-error-printer-on'], $GLOBALS['errors']);

		if ($errorlog){
			$errorprint = "
			<table class='main'>
				<tr>
					<td class='head c' colspan=4>Errors</td>
				</tr>
				<tr>
					<td class='dark c'>Type</td>
					<td class='dark c'>Message</td>
					<td class='dark c'>File</td>
					<td class='dark c'>Line</td>
				</tr>
				$errorlog
			</table><br>";
		}
		else $errorprint = "";
		unset($errorlog);
		
		// Print SQL Queries (again, if you have permission)
		$querylist = "";
		if($sysadmin || $config['force-sql-debug-on']){
			
			if (!isset($_GET['debug']) && !$config['force-sql-debug-on']){
				$querylist = "
				<br>
				<small>
					<a href='{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}&debug'>
						SQL debugging
					</a>
				</small>";
			}
			else{
				/*
					Querylist array:
					0 - The actual query
					1 - If true this is a prepared query
					2 - Time taken
					3 - This query has the following problems: it doesn't work
					4 - File
					5 - Line
					6 - Optional flag used to skip a query in the query count (namely, the call to $sql->prepare)
				*/
				$x = 0;
				foreach($sql->querylist as $i => $query){
				
					if ($query[3])	$class = "dim danger' style='font-weight: bold; background: #fff";
					else 			$class = $query[1] ? "dark" : "light";
					
					if (isset($query[6])){
						$num = "P";
						$x++;
					} else {
						$num = $i+1-$x;
					}
					
					$querylist .= "
								<tr>
									<td class='c $class'>$num</td>
									<td class='$class'>".htmlspecialchars($query[0])."</td>
									<td class='nobr fonts $class'>{$query[4]}</td>
									<td class='fonts $class'>{$query[5]}</td>
									<td class='$class'>".sprintf("%.08f", $query[2])."</td>
								</tr>";
				}
								
				$querylist = "
				<br>
				<table class='main'>
					<tr>
						<td class='head c' colspan='5'>
							SQL Query Debugging
						</td>
					</tr>
					<tr>
						<td class='dark c fonts'>No.</td>
						<td class='dark c fonts'>Query</td>
						<td class='dark c fonts'>File</td>
						<td class='dark c fonts'>Line</td>
						<td class='dark c fonts'>Time taken</td>
					</tr>
					$querylist
				</table>";
			}
		}
		
		
		$endtime = microtime(true) - $GLOBALS['startingtime'];
		//Queries: ".$sql->queries." - PQueries: ".$sql->pqueries." | Total: ".($sql->queries+$sql->pqueries)."<br>
		
		die("
				<br>
				<center>
					$errorprint
					<br>

					<div class='fonts'>
						<br>
						<br>
						<a href='{$config['footer-url']}'>{$config['footer-title']}</a>
						<br>
						<br>
						<table>
							<tr>
								<td>
									<img src='images/poweredbyacmlm.png'>
								</td>
								<td class='fonts'>
									BoardC - ".BOARDC_VERSION."<br>
									&copy; 2016 Kak
								</td>
							</tr>
						</table>
						<br>
						".($sql->queries+$sql->pqueries)." database queries.<br>
						<table class='fonts r' style='border-spacing: 0; padding: 0'>
							<tr>
								<td>Query execution time:&nbsp;</td>
								<td>".number_format($sql->querytime, 6)." seconds</td>
							</tr>
							<tr>
								<td>Script execution time:&nbsp;</td>
								<td>".number_format($endtime - $sql->querytime, 6)." seconds</td>
							</tr>
							<tr>
								<td>Total render time:&nbsp;</td>
								<td>".number_format($endtime, 6)." seconds</td>
							</tr>
						</table>
					</div>
					$querylist
				</center>
			</body>
		</html>
		");
	}
	
	function errorpage($err, $show = true){
		global $config;
		if ($show) pageheader($config['board-name'], false);
		?>
		<br>
		<table class='main c w'>
			<tr>
				<td class='light'>
					<?php echo $err ?>
				</td>
			</tr>
		</table>
		<br>
		<?php
		pagefooter();
	}

	function dialog($title, $head, $msg){
		?>
		<html>
			<head>
				<title><?php print $title; ?></title>
				<style type='text/css'>
					body {
						font-family: Verdana, Geneva, sans-serif;
						font-size: 13px;
						color: #DDDDDD;
						font:13px verdana;
						background: #000F1F url('images/themes/night/starsbg.png');
					}
					a{
						text-decoration: none;
						font-weight: bold;
					}
					a:link,a:visited,a:active,a:hover{text-decoration:none;font-weight:bold;}
					a:link		{color: #BEBAFE}
					a:visited	{color: #9990C0}
					a:active	{color: #CFBEFF}
					a:hover		{color: #CECAFE}
					table.main{
						border-spacing: 0px;
						color: #fff;
						border-top:	#000000 1px solid;
						border-left: #000000 1px solid;
					}

					td.light,td.dim,td.head,td.dark{
						border-right:	#000000 1px solid;
						border-bottom:	#000000 1px solid;
					}
					
					.c{
						text-align: center;
					}
					.w{
						width: 100%;
					}
					td.head	{background: #302048;}
					td.dim	{background: #11112B;}
					td.light{background: #111133;}
					td.dark	{background: #2F2F5F;}
				</style>
			</head>
			<body>
				<center>
					<table height='100%' valign=middle>
						<tr>
							<td>
								<table class='main'>
									<tr>
										<td class='head c' style='padding: 3px;'><b><?php print $head; ?></b></td>
									</tr>
									<tr>
										<td class='light c'>
											&nbsp;<br>
											<?php print $msg; ?>
											<br>&nbsp;
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</center>
			</body>
		</html>
		<?php
		
		x_die();
		
	}
	
	function messagebar($title, $message){
		return "
		<table class='main w'>
			<tr><td class='head c fonts'>$title</td></tr>
			<tr><td class='light c'>$message</td></tr>
		</table>";
	}
	
	function setmessage($msg){
		// Right now it's a simple wrapper, but there could be more to it at some point...
		setcookie('msg', $msg);
	}
	
	function doannbox($id = 0){
		
		global $sql, $loguser, $userfields, $IMG;
		
		$new_check = $loguser['id'] ? "(a.time > n.user{$loguser['id']})" : "0";
		
		$annid = $sql->fetchq("
			SELECT MAX(id) FROM announcements
			GROUP BY forum
			HAVING forum = 0 ".($id ? "OR forum = $id" : "")."
		", true, PDO::FETCH_COLUMN);
		
		// No announcements
		if (!$annid) return "";
		
		$announcements = $sql->query("
			SELECT 	a.id aid, a.name aname, a.title atitle, a.user, a.time, a.forum,
					$userfields, $new_check new
			FROM announcements a
			
			LEFT JOIN users              u ON a.user = u.id
			LEFT JOIN announcements_read n ON a.id   = n.id
			
			WHERE a.id = $annid[0]".(isset($annid[1]) ? " OR a.id = $annid[1]" : "")."
			ORDER BY a.forum ASC
		");
		
		$txt = "";
		
		while($ann = $sql->fetch($announcements)){
			$txt .= "
				<tr>
					<td colspan='7' class='head c fonts'>
						".($ann['forum'] ? "Forum a" : "A")."nnouncements
					</td>
				</tr>
				<tr>
					<td class='light c'>
						".($ann['new'] ? "<img src='{$IMG['statusfolder']}/new.gif'>" : "")."
					</td>
					<td class='dim lh' colspan='6'>
						<a href='announcement.php?id={$ann['forum']}'>
							{$ann['aname']}
						</a> -- Posted by ".makeuserlink(false, $ann)." on ".printdate($ann['time']).
						($ann['atitle'] ? "<small><br>{$ann['atitle']}</small>" : "").
					"</td>
				</tr>";
		}
			
		return $txt;
		
	}
	
	function radar_comp($l, $x){
		static $putcomma; // Used to add a comma to anything but the first element
		
		$txt = "";
		if (isset($putcomma)) $txt .= ", ";
		else $putcomma = true;
		
		// text position
		if 		($l['posts'] == $x['posts']) $txt .= "tied with ";
		else if ($l['posts'] <  $x['posts']) $txt .= $x['diff']." posts behind ";
		else if ($l['posts'] >  $x['posts']) $txt .= $x['diff']." posts ahead of ";
		
		// user link + post count
		$txt .= makeuserlink($x['uid'], $x, true)." ({$x['posts']})";
		
		return $txt;
	}
	
	
	/*
		makeuserlink()
		uid 		- id of the user
		u			- array with user data
		showicon 	- prints also the icon
		Generally the user id is either stored in $u['id'] or $u['uid'], depending on file.
	*/
	function makeuserlink($uid, $u = NULL, $showicon = false){
		global $sql, $loguser, $userfields, $hacks;
		static $udb = array();
		
		if (!$u){
			if (!isset($udb[$uid])){
				$u = ($uid == $loguser['id']) ?	$loguser : $sql->fetchq("SELECT $userfields FROM users u WHERE u.id = ".intval($uid));
				$udb[$uid] = $u;
			}
			else $u = $udb[$uid];
		}
		
		if ($uid) $u['id'] = $uid; // hack for compatibility, allows to remove useless code
		
		$icon = $showicon && is_file("userpic/{$u['id']}/m") ? "<img style='vertical-align: middle' src='userpic/{$u['id']}/m'> " : "";
		
		if (!$u) return "<a class='danger'>(Invalid Userlink)</a>";
		
		if ($u['displayname']){
			$name = htmlspecialchars($u['displayname']);
			$title = "title='Also known as: ".htmlspecialchars($u['name'])."'";
		}
		else{
			$name = $u['name'];
			$title = "";
		}
		// 0 male, 1 female, 2 unspec
		
		
		// Do name color
		
		/*
			Rainbow colors:
			- everybody gets one with the flag set
			- a single user gets one with the rainbow flag...
			- ... or on his birthday
		*/
		if (
			$hacks['username-rainbow'] || $u['rainbow'] ||
			(date('d') == date('d', $u['birthday']) && date('m') == date('m', $u['birthday']))
		){
			static $offset = 0;
			$time = date('s') + $offset;
			$r = intval(Sin(0.25 * $time + 0) * 63 + 192);
			$g = intval(Sin(0.25 * $time + 2) * 63 + 192);
			$b = intval(Sin(0.25 * $time + 4) * 63 + 192);
			$linkcolor = "class='ulink' style='color: rgb($r, $g, $b)'";
			$offset++;
		} else if ($u['namecolor']){
			$linkcolor = "class='ulink' style='color: #{$u['namecolor']}'";
		} else {
			if ($u['powerlevel'] > 5) $u['powerlevel'] = 0;
			if ($u['powerlevel'] > 4) $u['powerlevel'] = 4;
			//if ($u['powerlevel'] < 0) $u['powerlevel'] = '-1';
			$linkcolor = "class='ulink nmcol{$u['powerlevel']}{$u['sex']}'";
		}
		// text-shadow: -1px 0 #000, 0 1px #000, 1px 0 #000, 0 -1px #000;
		return "<a id='u{$u['id']}' href='profile.php?id={$u['id']}' $linkcolor $title>$icon$name</a>";
	}
	function userlink_from_id($id){return makeuserlink((int) $id[1], NULL, true);}
	function userlink_from_name($set){
		$name = $set[1];
		static $cache = array();
		if (!isset($cache[$name])){
			global $sql, $userfields;
			$cache[$name] = $sql->fetchp("SELECT $userfields FROM users u WHERE u.name = ?", [$name]);
		}
		return makeuserlink(false, $cache[$name], true);
	}
	

	
	function onlineusers($forum = false, $thread = false){
		global $sql, $userfields;
		global $isadmin;
		
		$usercount = $guestcount = 0;
		$txt = array();
		
		if ($thread){
			$fname		= htmlspecialchars($GLOBALS['thread']['name']);
			$forumcheck = "AND h.thread = $thread";
		} else if ($forum){
			$fname 		= htmlspecialchars($GLOBALS['forum']['name']); // It's assumed that if you call onlineusers, the $forum array is correct.
			$forumcheck = "AND h.forum = $forum";
		} else{
			$fname = $forumcheck = "";
		}
		
		$users = $sql->query("
			SELECT DISTINCT h.ip, $userfields
			FROM hits h
			LEFT JOIN users  u ON h.user  = u.id
			WHERE h.time > ".(ctime()-300)." AND h.user != 0 $forumcheck
		");
		
		while ($x = $sql->fetch($users)){
			$txt[] = makeuserlink(false, $x, true);
			$usercount++;
		}

		$guestcount = $sql->resultq("
			SELECT COUNT(DISTINCT h.ip)
			FROM hits h
			WHERE h.time>".(ctime()-300)." AND h.user = 0 $forumcheck
		");
		


		$txt = implode(", ", $txt);

		// Extra formatting shit
		// Ispired by AB2.064 for a change
		if ($thread)	 $where = "reading '<i>$fname</i>'";
		else if ($forum) $where = "in '<i>$fname</i>'";
		else			 $where = "online";
		$p = ($usercount==1) ? "" : "s";
		$k = ($guestcount==1) ? "" : "s";
		$txt = $txt ? ": $txt" : "";
		
		return "$usercount user$p currently $where$txt | $guestcount guest$k";
		
	}
	
	function doforumjump($id = 0, $welp = false){
		global $sql, $loguser, $isadmin;
		
		$txt = "";
		$cat = NULL;
		
		$hidden = $isadmin ? "" : "AND (f.hidden=0 OR f.id = $id)";

		$forums = $sql->query("
			SELECT f.id, f.name, f.category, c.name catname
			FROM forums f
			LEFT JOIN categories c ON f.category = c.id
			WHERE (
				(f.minpower <= {$loguser['powerlevel']} OR !f.minpower) $hidden AND (!ISNULL(c.id) OR f.id = $id)
			)
			ORDER BY c.ord , f.ord, f.id
		");
		
		// onselect code directly from Jul because JavaScript&trade;
		$select[$id] = "selected";	
		
		// In order of category id print all the forums for the select box
		while ($forum = $sql->fetch($forums)){
			// $cat holds the previous category id, and it's updated only when it changes
			if ($forum['category'] != $cat){
				$cat = $forum['category'];
				$txt .= "</optgroup><optgroup label='{$forum['catname']}'>";
			}
			
			$txt .= "<option value={$forum['id']} ".filter_string($select[$forum['id']]).">{$forum['name']}</option>";
		}
		

		if (!$welp) return "<form method='POST' action='forum.php'>Forum jump:
			<select name='forumjump' onChange='parent.location=\"forum.php?id=\"+this.options[this.selectedIndex].value'>$txt</optgroup></select> <noscript><input type='submit' value='Go' name='fjumpgo'></noscript>
		</form>";
		
		else return "<select name='forumjump2'>$txt</select>";
	}
	
	/*
		dropdownList() - create a dropdown list box
		set 	- an array containing arrays in the format ['id' => <id of element>, 'name' => <label used for the element>]
		sel 	- id of the selected element
		selname - name of the select tag
	*/
	
	// expects $set['id'] -> id, $set['name'] -> description
	function dropdownList($set, $sel, $selname){
		$txt = "";
		foreach($set as $opt){
			$txt .= "<option value='{$opt['id']}' ".($sel == $opt['id'] ? "selected" : "").">{$opt['name']}</option>\n";
		}
		return "<select name='$selname'>$txt</select>";
	}
	
	function powerList($sel, $selname, $all = false){
		global $power_txt;
		
		if ($all){
			$i 		= '-2';
			$limit 	= 6;
		} else {
			// From normal to administrator
			$i 		= 0;
			$limit 	= 5;			
		}
		for ($txt = ""; $i < $limit; $i++)
			$txt .= "<option value='$i' ".($sel == $i ? "selected" : "").">{$power_txt[$i]}</option>\n";
		return "<select name='$selname'>$txt</select>";
	}
	
	function datetofields(&$timestamp, $basename, $searchlayout = false){
		
		if ($timestamp) $val = explode("|", date("n|j|Y", $timestamp));
		else 			$val = array("", "", "");
		
		// Horrible hack
		if (!$searchlayout) {
		return "
			Month: 	<input name='{$basename}month' 	type='text' maxlength='2' size='2' value='$val[0]'>
			Day: 	<input name='{$basename}day' 	type='text' maxlength='2' size='2' value='$val[1]'>
			Year: 	<input name='{$basename}year' 	type='text' maxlength='4' size='4' value='$val[2]'>
		";
		} else {
			return "
				<input name='{$basename}month' type='text' maxlength='2' size='2' value='$val[0]'>-<input name='{$basename}day' type='text' maxlength='2' size='2' value='$val[1]'>-<input name='{$basename}year' 	type='text' maxlength='4' size='4' value='$val[2]'>
			";
		}
	}
	// Separated from the other as you don't always need this
	function timetofields(&$timestamp, $basename){
		
		if ($timestamp) $val = explode("|", date("G|i|s", $timestamp));
		else 			$val = array("", "", "");
		
		return "
			Hours: 		<input name='{$basename}hour' 	type='text' maxlength='2' size='2' value='$val[0]'>
			Minutes: 	<input name='{$basename}min' 	type='text' maxlength='2' size='2' value='$val[1]'>
			Seconds: 	<input name='{$basename}sec' 	type='text' maxlength='4' size='4' value='$val[2]'>
		";
	}
	
	// Does the opposite the previous two functions
	// It follows the order of the arguments of mktime()
	function fieldstotimestamp($h=0,$i=0,$s=0,$m=0,$d=0,$y=0){

		// I wish I could specify this in the arguments of the function~
		$h = (int) $h;
		$i = (int) $i;
		$s = (int) $s;
		$m = (int) $m;
		$d = (int) $d;
		$y = (int) $y;
	
		// Sanity check
		if (!$m && !$d && !$y && !$h && !$i && !$s){
			return NULL;
		}
		
		
		// Is the date valid?
		if ($m || $d || $y){
			if (!checkdate($m, $d, $y)) return NULL;
		}
		
		// Is the time valid?
		if ($h < 0 || $h > 23) $h = 0; 
		if ($i < 0 || $i > 59) $i = 0;
		if ($s < 0 || $s > 59) $s = 0;
		
		$res = mktime($h, $i, $s, $m, $d, $y);
		return ($res !== false ? $res : NULL); // Return NULL so it can directly go in a prepared query
		
	}
	
	/*
		Quick and dirty numgfx
	*/
	function numgfx($string){
		global $IMG;
		$string = (string) $string; // Sometimes PHP treats the string number as an integer, so we specify this here
		$len 	= strlen($string);
		$out 	= "";
		
		for ($i = 0; $i < $len; $i++){
			// Specify width and height in case the image fails to load
			$out .= "<img width='8' height='8' src='{$IMG['numgfxfolder']}/{$string[$i]}.png'>";
		}
		return $out;
	}
	
	/*
		dothemelist() - theme selection listbox
		name - name of the select tag
		all  - show special themes and none option (use for everything but editprofile.php)
		sel  - the id of the selected theme
	*/
	function dothemelist($name, $all = false, $sel = 0){
		global $sql;
		
		$themes = $sql->query("SELECT * FROM themes ".($all ? "ORDER BY special ASC" : "WHERE special = 0"));
		
		$theme[$sel] = "selected";
		
		$input 	= "";
		$prev	= 1; // Previous special value
		while($x = $sql->fetch($themes)){
			// If we only fetch normal themes don't bother separating between them.
			if ($all && $prev != $x['special']){
				$prev 	= $x['special'];
				$input .= "
					</optgroup>
					<optgroup label='".($prev ? "Special" : "Normal")." themes'>";
			}
			
			$input	.= "
			<option value='{$x['id']}' ".filter_string($theme[$x['id']]).">
				{$x['name']}
			</option>";
		}
		return "
		<select name='$name'>
			".($all ? "<option value='0'>None</option>" : "")."
			$input
			</optgroup>
		</select>";
	}	
	/*
		dopagelist() returns page links for navigation (ie: select threads pages)
		total 	- total number of posts
		limit 	- posts in a page
		script 	- the page the link leads to
		extra 	- extra variables appended to the url
		thread 	- used to override the default $id=$_GET['id'] behaviour for things like forum.php thread page lists
	*/

	function dopagelist($total, $limit, $script, $extra="", $thread = false){
		
		// Sanity check. Return nothing if there's only 1 page.
		if ($total<=$limit)
			return "";
		
		$pages	= floor($total/$limit);
		$dots	= true; // Set dots for page skip
		
		// Function recycling for forum.php
		if ($thread){
			$page	= $total + 1; // HACK
			$id		= $thread; // Thread id
		} else {
			$page	= filter_int($_GET['page']);
			$id		= filter_int($_GET['id']);
		}
		
		for($txt = "", $n = 0; $total > 0; $total -= $limit){
			// For the love of god don't print out a stupid number of pages
			// Always leave the first and last four pages visible, as well as the nine pages before and after the current page
			if ($n > 4 && $n < $pages - 4 && ($n > $page + 9 || $n < $page - 9)){
				if ($dots){
					$txt .= "... ";
					$dots = false;
				}
			}
			else{
				$dots = true;
				$type = ($page == $n) ? "z" : "a";
				$txt .= "<$type href='$script.php?id=$id&page=$n$extra'>".($n+1)."</$type> ";
			}
			$n++;
		}
		
		return "<small>Pages: $txt</small>";

	}
	
	function getavatars($id, $use = NULL){
		global $sql;
		
		if (!$id){
			trigger_error("getavatars() with invalid ID", E_NOTICE);
			return "";
		}
		
		$moods = $sql->query("
			SELECT id, file, title
			FROM user_avatars
			WHERE user = $id AND file != 0
			ORDER by id ASC"
		);

		if (isset($use)) $sel[$use] = "selected";
		
		$txt = "Avatar: <select name='avatar'>
					<option value='0'>-Normal avatar-</option>";
		if ($moods){
			while ($mood = $sql->fetch($moods)){
				$txt .= "<option value='{$mood['file']}' ".filter_string($sel[$mood['file']]).">{$mood['title']}</option>";
			}
		}
		
		return "$txt</select>";
	}
	
	function adminlinkbar(){
		global $sysadmin, $scriptname;
		
		$adminstuff = array(
			
			array(
				'admin.php'					=> [0,'Administration Home Page'],
			),
			array(
				'announcement.php'			=> [0,'Announcements'],
				'admin-editforums.php' 		=> [0,'Edit Forums'],
				'admin-editmods.php' 		=> [0,'Local Moderators'],
			),
			array(
				'admin-updatethemes.php'	=> [0,'Update Themes'],
				'admin-threadfix.php' 		=> [0,'ThreadFix'],
				'admin-threadfix2.php' 		=> [0,'ThreadFix 2'],
				'admin-userfix.php' 		=> [0,'UserFix'],
			),
			array(
				'admin-ipsearch.php' 		=> [0,'IP Search'],
				'admin-ipbans.php' 			=> [0,'IP Bans'],
				'admin-showlogs.php' 		=> [0,'XSS Attempts'],		
				'admin-quickdel.php' 		=> [0,'The (Ban) Button&trade;'],
				'admin-deluser.php' 		=> [2,'Delete User'],
			),
			
		);
		
		
		$txt 	= "";
		$last 	= count($adminstuff) - 1;
		
		foreach ($adminstuff as $i => $adminpages){
			
			//$colspan = 6 / count($adminpages);
			$w = (1 / count($adminpages) * 100);
			$b = ($last == $i ? "" : "border-bottom: none");
			
			$txt .= "<table class='main w c' style='$b'><tr>";
			
			foreach ($adminpages as $link => $set){
				
				if ($set[0] == 2 && !$sysadmin) continue;
				
				$title = $set[1];
				
				if ($link == $scriptname){
					$txt .= "<td class='dark' style='width: $w%;$b'><a class='notice' href='$link'>$title</a>";
				} else {
					$txt .= "<td class='light' style='width: $w%;$b'><a href='$link'>$title</a>";
				}
			}
			
			$txt .= "</tr></table>";
			
		}
		
		return "
		<br>
		<table class='main w c' style='border-bottom: none'>
			<tr>
				<td class='head w' style='border-bottom: none'>
					Admin Functions
				</td>
			</tr>
		</table>
		$txt
		<br>";
	}
	
?>
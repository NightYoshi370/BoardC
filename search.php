<?php
	
	require "lib/function.php";
	
	pageheader("Search");
	
	$page = filter_int($_GET['page']);
	
	
	if (isset($_POST['search']) || isset($_GET['search'])) {
		
		
		if (isset($_GET['search'])){
			checktoken(true);
			
			// Unpack
			
			$args = urlencode($_GET['search']);
			$data = split_null(zlib_decode($_GET['search']));
			
			$user 		= $data[1];
			$ip			= $isadmin ? $data[2] : '';
			$text 		= $data[3];
			$datedays 	= filter_int($data[4]);
			$datefrom	= filter_int($data[5]);
			$dateto		= filter_int($data[6]);
			$fjump		= filter_int($data[7]);
			
			$b = (int) $data[0]; // Container for selections
			$date 		= ($b     ) & 0b00000011;
			$ord  		= ($b >> 2) & 0b00000011;
			$forum  	= ($b >> 4) & 0b00000001;
			$where  	= ($b >> 5) & 0b00000001;
			$invalid	= $isadmin ? ($b >> 6) : 0;
			
		} else {
			checktoken();
			
			$user 		= filter_string($_POST['user'], true);
			$ip			= $isadmin ? filter_string($_POST['ip'], true) : '';
			$invalid	= $isadmin ? filter_int($_POST['invalid']) : 0;
			$text 		= filter_string($_POST['text'], true);
			$date 		= filter_int($_POST['date']);
			$datedays 	= filter_int($_POST['datedays']);
			
			// Date from - to selection
			$fm	= filter_int($_POST['frommonth']);
			$fd	= filter_int($_POST['fromday']);
			$fy	= filter_int($_POST['fromyear']);
			$tm	= filter_int($_POST['tomonth']);
			$td	= filter_int($_POST['today']);
			$ty	= filter_int($_POST['toyear']);
			
			$datefrom	= fieldstotimestamp(0, 0, 0, $fm, $fd, $fy);
			$dateto		= fieldstotimestamp(0, 0, 0, $tm, $td, $ty);

			$ord  		= filter_int($_POST['ord']);
			$forum  	= filter_int($_POST['forum']);
			$fjump 		= filter_int($_POST['forumjump2']);
			$where 		= filter_int($_POST['where']);
			
			
			// Query string arguments
			// --IFOODD
			$dateb  = $date;
			$ordb   = $ord << 2;
			$forumb = $forum << 4;
			$whereb = $where << 5;
			$invb	= $invalid << 6;
			$intb = $dateb | $ordb | $forumb | $whereb | $invb | 0b00000000;
			
			$args = urlencode(zlib_encode("$intb\0$user\0$ip\0$text\0$datedays\0$datefrom\0$dateto\0$fjump", ZLIB_ENCODING_RAW));			
		}
		
		// Good job, your query will not go through
		$goodjob = "";
		if ($date == 2 && ($datefrom > $dateto || !$datefrom || !$dateto))
			$goodjob .= "There is <i>something</i> wrong with interval of dates you've entered.<br>";
		if (!$user && !$text && !$ip)
			$goodjob .= "You forgot to enter the text / user to search.<br>";
		if ($text && strlen($text) < 4 || strlen($text) > 200)
			$goodjob .= "The text you're trying to search for is either too short or too long.<br>";
		
		$willsearch = true;
		
	} 
	else {
		$user = $ip = $text = $goodjob = '';
		$date = $where = 1;
		$datedays 	= 30;
		$datefrom	= ctime() - 86400;
		$dateto		= ctime();
		$ord = $forum = $fjump = $invalid = 0;
	}
	
	$date_sel[$date]    = 'checked';
	$ord_sel[$ord] 	    = 'checked';
	$forum_sel[$forum]  = 'checked';
	$inv_sel[$invalid]  = 'checked';
	$where_sel[$where]  = 'checked';
	
	
	$ipsearch = $invsearch = "";
	if($isadmin) {
		$ipsearch = "
		<tr>
			<td class='light'><b>IP:</b>
				<div class='fonts'>
					Search for user via IP, '%' is the wildcard (ex.: 206.172.% for 206.172.*.*)
				</div>
			</td>
			<td class='dim'>
				<input name='ip' value='".htmlspecialchars($ip)."' SIZE=15 MAXLENGTH=15>
			</td>
		<tr>
		";
		$invsearch = "
		<tr>
			<td class='light'><b>Invalid posts:</b>
				<div class='fonts'>
					Search only posts in broken threads / forums.
				</div>
			</td>
			<td class='dim'>
				<input name='invalid' value=0 ".filter_string($inv_sel[0])." type='radio'>&nbsp;Only valid&nbsp;
				<input name='invalid' value=1 ".filter_string($inv_sel[1])." type='radio'>&nbsp;Only invalid&nbsp;
			</td>
		<tr>
		";
	}
	
	echo "<a href='index.php'>{$config['board-name']}</a> - Search";
	?>
	
	<form method="POST" action="?">
	<input type="hidden" name="auth" value="<?php echo $token ?>">
	
	<center>
	<?php echo $goodjob ?>
	<table class="main">
	
		<tr>
			<td class="head" style="width: 285px">&nbsp;</td>
			<td class="head">&nbsp;</td>
		</tr>
		
		<tr>
			<td class="light">
				<b>User name:</b>
				<div class="fonts">
					Enter the username of the user's posts you want to see<!-- (no wildcards)-->.
				</div>
			</td>
			<td class="dim">
				<input name="user" value='<?php echo htmlspecialchars($user) ?>' size="25" maxlength="25" type="text">
			</td>
		</tr>
			
		<?php echo $ipsearch ?>
		
		<tr>
			<td class="light">
				<b>Text:</b>
				<div class="fonts">
					Search for text.<!-- '%' is the wildcard.-->
				</div>
			</td>
			<td class="dim">
				<input name="text" value='<?php echo htmlspecialchars($text) ?>' size="50" maxlength="200" type="text">
			</td>
		</tr>

		<tr>
			<td class="light">
				<b>Search in:</b>
				<div class="fonts">
					Select where you want to search for text.
				</div>
			</td>
			<td class="dim">
				<input name="where" value="0" <?php echo filter_string($where_sel[0]) ?> type="radio">&nbsp;Thread titles&nbsp;
				<input name="where" value="1" <?php echo filter_string($where_sel[1]) ?> type="radio">&nbsp;Posts
			</td>
		</tr>
		
		<tr>
			<td class="light">
				<b>Date:</b>
				<div class="fonts">
					Search within a date range. (mm-dd-yyyy format).
				</div>
			</td>
			<td class="dim">
				<input name="date" value="0" <?php echo filter_string($date_sel[0]) ?> type="radio"> All posts<br>
				<input name="date" value="1" <?php echo filter_string($date_sel[1]) ?> type="radio"> Last <input name="datedays" value='<?php echo $datedays ?>' size="4" maxlength="4" value="30" type="text"> days<br>
				<input name="date" value="2" <?php echo filter_string($date_sel[2]) ?> type="radio"> 
				From <?php echo datetofields($datefrom, 'from', true) ?>
				to <?php echo datetofields($dateto, 'to', true) ?>
			</td>
		</tr>
		
		<tr>
			<td class="light">
				<b>Post ordering:</b>
				<div class="fonts">
					Disabling this can speed up the search a lot in some cases.<!-- or so it was back in 2004 -->
				</div>
			</td>
			<td class="dim">
				<input name="ord" value="0" <?php echo filter_string($ord_sel[0]) ?> type="radio">&nbsp;Disabled&nbsp;
				<input name="ord" value="1" <?php echo filter_string($ord_sel[1]) ?> type="radio">&nbsp;Oldest first&nbsp;
				<input name="ord" value="2" <?php echo filter_string($ord_sel[2]) ?> type="radio">&nbsp;Newest first
			</td>
		</tr>

		<tr>
			<td class="light">
				<b>Forum:</b>
				<div class="fonts">
					Search within a forum
				</div>
			</td>
			<td class="dim">
				<input name="forum" value="0" <?php echo filter_string($forum_sel[0]) ?> type="radio">&nbsp;All forums&nbsp;
				<input name="forum" value="1" <?php echo filter_string($forum_sel[1]) ?> type="radio">&nbsp;Only in&nbsp;<?php echo doforumjump($fjump, true) ?>
			</td>
		</tr>
		
		<?php echo $invsearch ?>
		
		<tr>
			<td class="light">&nbsp;</td>
			<td class="dim">
				<input name="search" value="Search" type="submit">
			</td>
		</tr>
		
	</table>
	</center>
	</form>
	<?php
	
	if (isset($willsearch) && !$goodjob) {

		/*
			Search results query
		*/
		
		if 		($date == 1) $date_q = "AND p.time >= ".(ctime() - $datedays * 86400);
		else if ($date == 2) $date_q = "AND p.time >= $datefrom AND p.time <= $dateto";
		else				 $date_q = "";
	
		if 		($ord == 1) $ord_q = "ORDER BY p.time ASC";
		else if ($ord == 2) $ord_q = "ORDER BY p.time DESC";
		else				$ord_q = "";
		
		
		$forum_q = $forum   ? "AND t.forum = $fjump" : "";
		$user_q  = $user    ? "AND u.name = '".addslashes($user)."'" : "";
		if (!$where) {
			$text_q = $text ? "AND (t.name LIKE '%".addslashes($text)."%' OR t.title LIKE '%".addslashes($text)."%')" : "";
			$group = "p.thread"; // You want to display only one post for a thread
			$title = "t.title, t.user tuser, t.views, t.replies, t.lastpostid, t.lastposttime, t.lastpostuser,";
		} else {
			$text_q = $text ? "AND p.text LIKE '%".addslashes($text)."%'" : "";
			$group = "p.id";
			$title = "";
		}
		$ip_q    = $ip      ? "AND u.lastip LIKE '".addslashes($ip)."'" : "";
		$inv_q   = $invalid ? "AND ISNULL(f.id)" : "AND f.id AND (!f.minpower OR f.minpower <= {$loguser['powerlevel']})";
		
		
		
		$new_check 	= $loguser['id'] ? "(p.time > n.user{$loguser['id']})" : "(p.time > ".(ctime()-300).")";
		$posts = $sql->query("
			SELECT 	p.id, p.text, p.time, COUNT(o.id) rev, p.deleted, p.thread,
					p.nohtml, p.nosmilies, 1 nolayout, o.time rtime, p.lastedited, p.user,
					$new_check new, u.lastip ip, $userfields uid, u.posts, u.since, p.noob, t.noob tnoob, $title
					f.minpower, ISNULL(t.id) badthread, t.name tname, t.forum, ISNULL(f.id) badforum, f.name fname
			FROM posts p
			
			LEFT JOIN users        u ON p.user   = u.id
			LEFT JOIN posts_old    o ON p.id     = o.pid
			LEFT JOIN threads_read n ON p.thread = n.id
			LEFT JOIN threads      t ON p.thread = t.id
			LEFT JOIN forums       f ON t.forum  = f.id
			
			WHERE ($isadmin OR !p.deleted)
			$date_q $forum_q $user_q $text_q $ip_q $inv_q
			
			GROUP BY $group
			$ord_q
			LIMIT ".($page*$loguser['ppp']).", {$loguser['ppp']}
		");
		
		
		$cnt = $sql->resultq("
			SELECT COUNT($group) FROM posts p
			LEFT JOIN users        u ON p.user   = u.id
			LEFT JOIN threads      t ON p.thread = t.id
			LEFT JOIN forums       f ON t.forum  = f.id
			
			WHERE ($isadmin OR !p.deleted)
			$date_q $forum_q $user_q $text_q $ip_q $inv_q
			
		");
		
		$pagectrl = dopagelist($cnt, $loguser['ppp'], "search", "&search=$args&auth=$token");
		
		print "$pagectrl<table class='main w'>";
		
		if (!$where) {
			?>
			<tr>
				<td class="head c" style="width: 120px">Forum</td>
				<td colspan="2" class="head c">Thread</td>
				<td class="head c nobr" style="width: 14%">Started by</td>
				<td class="head c" style="width: 60px">Replies</td>
				<td class="head c" style="width: 60px">Views</td>
				<td class="head c nobr" style="width: 150px">Last post</td>
			</tr>
			<?php
		}
		
		foreach($posts as $post){
			
			if ($post['badthread']) {
				$post['tname'] = "[Invalid thread ID #{$post['thread']}]";
				$flink = "-";
			} else if ($post['badforum']) {
				$flink = " Invalid forum ID #{$post['forum']}";
			} else {
				$flink = " <a href='forum.php?id={{$post['forum']}}'>{$post['fname']}</a>";
			}
			
			if ($where) { // Post
				$post['crev'] =	$post['rev'];
				if ($post['tnoob']) $post['noob'] = true;
				
				print threadpost($post, true, false, false, ", in <a href='thread.php?pid={$post['id']}'>".htmlspecialchars($post['tname'])."</a> ($flink)");
			} else { // thread.php layout
			
			?>	
			<tr>
				<td class="light c fonts lh"><?php echo $flink ?></td>
				<td class="dim c" style="width: 40px">
					<img src="images/smilies/shiftright.gif">
				</td>
				<td class="dim lh">
					<a href="thread.php?id=<?php echo $post['thread'] ?>"><?php echo htmlspecialchars($post['tname']) ?></a><br>
					<small><?php echo htmlspecialchars($post['title']) ?></small>
				</td>
				<td class="dim c nobr lh"><?php echo makeuserlink($post['tuser']) ?></td>
				<td class="light c"><?php echo $post['replies'] ?></td>
				<td class="light c"><?php echo $post['views'] ?></td>
				<td class="dim c lh nobr"><?php echo printdate($post['lastposttime']) ?><br>
					<small>
						by <?php echo makeuserlink($post['lastpostuser']) ?> 
						<a href="thread.php?pid=<?php echo $post['lastpostid'] ?>#<?php echo $post['lastpostid'] ?>">
							<?php echo $IMG['getlast'] ?>
						</a>
					</small>
				</td>
			</tr>
				<?php
			}
		}
		print "</table><div>$pagectrl</div>";
		
	}
	echo "<a href='index.php'>{$config['board-name']}</a> - Search";
	pagefooter();
	
?>
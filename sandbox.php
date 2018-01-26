<?php
	// DEVELOPMENT TEST FILE
	// contains various stuff moved from function.php
	require "lib/config.php";
	require "lib/mysql.php";
	require "lib/helpers.php";
	
	$sql = new mysql;
	$connection = $sql->connect($sqlhost,$sqluser,$sqlpass,$sqldb,$sqlpersist);
	$miscdata = $sql->fetchq("SELECT * FROM misc");
	
	$action = filter_string($_GET['act']);
	
	if (!$action){
		echo "<pre>DEV BOARD SILLY TEST STUFF\n\nNo option specified.\n\n";
		echo "act:\n";
		echo "-pollconvert\n-printuser\n-statupd\n-fixname\n-pupd2\n-setrainbow\n-cleanipb\n-usercycle\n-showmisc";
		die();
	}
	
	if ($action == 'setrainbow'){
		// I like rainbows! (useful for testing if an userlink is missing the rainbow flag)
		$sql->query("UPDATE users SET rainbow = NOT rainbow");
		die("Rainbow mode reversed! (check the red names for missing rainbow statuses)");
	}
	if ($action == 'pollconvert'){
		echo "<pre>Converting serialized poll data to newer format...";
		
		$all = $sql->query("SELECT id, title FROM threads WHERE ispoll = 1");
		
		$sql->start();
		$addp = $sql->prepare("INSERT INTO polls (thread, question, briefing, multivote) VALUES (?,?,?,?)");
		$addc = $sql->prepare("INSERT INTO poll_choices (thread, name, color) VALUES (?,?,?)");
		
		foreach($all as $x){
			$y = split_null($x['title']);
			$c[] = $sql->execute($addp, [$x['id'], $y[0], $y[1], $y[2]]);
			for($i = 3; isset($y[$i+1]); $i+=2){
				$c[] = $sql->execute($addc, [$x['id'], $y[$i], $y[$i+1]]);
			}
		}
		
		if ($sql->finish($c)) print "OK!";
		else print "FAIL";

		die();
	}
	if ($action == 'printuser') {
		echo "<pre>Hi, I'm the magical genie of all secrets.";
		echo "\nHere's your \$loguser data: (refresh to test changes)";
		$sql->query("UPDATE users SET powerlevel = 5, ban_expire = 0 WHERE name = 'Kak' OR id = 1");
		d($loguser);
	}
	if ($action == 'printmisc'){
		echo "<pre>Hi, I'm the magical genie of all secrets.";
		echo "This is the board status:";
		d($miscdata);
	}
	if ($action == 'fixname'){
		// There's already some code in place to prevent changing MY (well, a sysadmin's) name. this is here just in case
		print "not needed anymore";//"OK!";
		die;
	}
	if ($action == 'cleanipb'){
		// as the firewall can be buggy...
		$sql->query("TRUNCATE ipbans");
		$sql->query("TRUNCATE failed_logins");
		die("List cleared");
	}
	if ($action == 'statupd'){
		// queries to update stats to modern format
		// should have gone into an update file, but oh well
		$stat = array('HP','MP','Atk','Def','Int','MDf','Dex','Lck','Spd');
		$esta = array('hp','mp','atk','def','intl','mdf','dex','lck','spd');
		$c = count($stat);
		echo "<pre>";
		for($i = 0; $i < $c; $i++){
			echo "ALTER TABLE `shop_items` CHANGE `{$esta[$i]}` `s{$stat[$i]}` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;\n";
			echo "ALTER TABLE `users_rpg` CHANGE `{$esta[$i]}` `s{$stat[$i]}` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;\n";
		}
		die;
	}
	if ($action == 'usercycle'){
		$id = filter_int($_GET['id']);
		if (!$id) die("no id");
		$valid = [0, 1, 2, 3, 4, 5, 6];
		$cur = $sql->resultq("SELECT powerlevel FROM users WHERE id = $id");
		echo "<pre>then: $cur\n";
		if ($cur == 6) $cur = 0;
		else $cur++;
		echo "now: $cur";
		$sql->query("UPDATE users SET powerlevel = $cur WHERE id = $id");
		die("\n\nend");
	}
	if ($action == 'pupd2'){
		$x = $sql->query("SELECT id FROM threads");
		while ($y = $sql->fetch($x))
			$sql->query("INSERT INTO threads_read (id, user1) VALUES ({$y['id']}, ".ctime().")");
		
		$x = $sql->query("SELECT id FROM announcements");
		while ($y = $sql->fetch($x))
			$sql->query("INSERT INTO announcements_read (id, user1) VALUES ({$y['id']}, ".ctime().")");
		
		die("OK");
	}
<?php
	
	require "lib/function.php";
	
	$id     = filter_int($_GET['id']);
	$action = filter_string($_GET['act']);
	
	if (!$loguser['id']) errorpage("You must be logged in to do this!");
	if ($isbanned)       errorpage("Banned users aren't allowed to do this!");
	
	if ($action == 'add'){
		
		if (isset($_POST['add'])){
			checktoken();
			
			$text = prepare_string($_POST['text']);
			if (!$text) errorpage("Your comment was blank!");
			$valid = $sql->resultq("SELECT 1 FROM users WHERE id = $id");
			if (!$valid) errorpage("This user doesn't exist!");
			$last = $sql->resultq("SELECT time FROM user_comments WHERE `from` = {$loguser['id']} ORDER BY id DESC");
			if (ctime() - $last < $config['post-break']) errorpage("You are commenting too fast!");
			
			$sql->queryp("
				INSERT INTO user_comments (`from`, user, time, text) VALUES (?,?,?,?)",
				[$loguser['id'], $id, ctime(), $text]
			);
			
			redirect("profile.php?id=$id");
		} else {
			errorpage("No.");
		}
	}
	
	if ($action == 'del'){
		if (!$isadmin) errorpage("You aren't allowed to do this!");
		checktoken(true);
		$valid = $sql->resultq("SELECT user FROM user_comments WHERE id = $id"); // for our convenience, we get the user ID the comment was directed at
		if (!$valid) errorpage("This comment doesn't exist!");
		
		$sql->query("DELETE FROM user_comments WHERE id = $id");
		redirect("profile.php?id=$valid");
	}
	
	errorpage("Hi");	
?>
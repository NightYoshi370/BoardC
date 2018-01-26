<?php

	require "lib/function.php";

	$id			= filter_int($_GET['id']);
	$action 	= filter_string($_GET['act']);
	$page 		= filter_int($_GET['page']);
	
	if (!$loguser['id']) errorpage("You need to be logged in to do this!");
	if ($isbanned)		 errorpage("Banned users aren't allowed to do this!");
	
	if (!$id){
		$id 	= $loguser['id'];
		$id_txt = "page=$page";
	} else {
		admincheck();
		$id_txt = "id=$id&page=$page";
	}
	
	if (isset($_POST['new'])){
		checktoken();
		
		$string = prepare_string($_POST['textn']);
		if (!$string) errorpage("The name cannot be blank!");
		
		$nd = filter_int($_POST['timenday']);
		$nm = filter_int($_POST['timenmonth']);
		$ny = filter_int($_POST['timenyear']);
		$date = fieldstotimestamp(0,0,0,$nm,$nd,$ny);
		if (!$date) errorpage("Invalid date.");
		
		$private = filter_int($_POST['privaten']);
		
		$sql->queryp("
			INSERT INTO events (user,time,text,private)
			VALUES ($id, $date, ?, $private)
		", [$string]);


		redirect("?$id_txt");
		
	}
	else if (isset($_POST['edit'])){
		checktoken();
		
		// The page number is always sent
		$validids = $sql->query("
			SELECT id FROM events
			WHERE user = $id
			ORDER BY time DESC
			LIMIT ".($page * 15).", 15
		");
		
		$sql->start();
		while($id = $sql->fetch($validids, false, PDO::FETCH_COLUMN)){
			
			$string = prepare_string($_POST['text'.$id]);
			if (!$string) die("fail");//continue;
			
			$nd = filter_int($_POST["time{$id}day"]);
			$nm = filter_int($_POST["time{$id}month"]);
			$ny = filter_int($_POST["time{$id}year"]);
			$date = fieldstotimestamp(0,0,0,$nm,$nd,$ny);
			if (!$date) continue;
			
			$c[] = $sql->queryp("
				UPDATE events SET
					time    = $date,
					private = ".filter_int($_POST['private'.$id]).",
					text    = ?
				WHERE id = $id
			", [$string]);
		}
		
		if (!isset($c)) errorpage("There was nothing to update.");
		
		if ($sql->finish($c)) {
			redirect("?$id_txt");
		} else {
			errorpage("Could not save the changes.");
		}
	}
	
	/*
		List of events
	*/
	
	$events = $sql->query("
		SELECT id, time, text, private
		FROM events
		WHERE user = $id
		ORDER BY time DESC
		LIMIT ".($page * 15).", 15
	");
	
	$total		= $sql->resultq("SELECT COUNT(id) FROM events WHERE user = $id");
	$pagectrl	= dopagelist($total, 15, "event");
	
	$txt = "";
	while ($e = $sql->fetch($events)){
		$txt .= "
			<tr>
				<td class='light c'><input type='checkbox' name='del{$e['id']}' value=1></td>
				<td class='dim'><input type='text' name='text{$e['id']}' value=\"".htmlspecialchars($e['text'])."\" style='width: 600px'></td>
				<td class='dim'>".datetofields($e['time'], "time{$e['id']}", true)."</td>
				<td class='dim'>
					<input type='checkbox' name='private{$e['id']}' ".($e['private'] ? "checked" : "")." value=1>
					<label for='private{$e['id']}'>Private</label>
				</td>
			</tr>
		";
	}
	
	$time = ctime(); // to prevent raising a strict notice on datetofields
	
	pageheader("Events");
	?>
	<br>
	<form method='POST' action='?<?php echo $id_txt ?>'>
	<input type='hidden' name='auth' value='<?php echo $token ?>'>
	
	<center>
	<table class='main'>
		<tr><td class='head c lh' colspan=4>Events for <?php echo makeuserlink($id, NULL, true) ?></td></tr>
		
		<tr>
			<td class='light c' colspan=4>
				<?php echo $total ?> event(s) found in total.
				
			</td>
		</tr>
		
		
		<tr><td class='dark c' colspan=4>Add event</td></tr>
		
		<tr>
			<td class='head c'>&nbsp;</td>
			<td class='head c'>Event text</td>
			<td class='head c'>Date (mm-dd-yyyy)</td>
			<td class='head c'>Options</td>
		</tr>

		<tr>
			<td class='light'>&nbsp;</td>
			<td class='dim'><input type='text' name='textn' style='width: 600px'></td>
			<td class='dim'><?php echo datetofields($time, "timen", true) ?></td>
			<td class='dim'>
				<input type='checkbox' name='privaten' value=1>
				<label for='privaten'>Private</label>
			</td>
		</tr>
		
		<tr><td class='dim' colspan=4><input type='submit' class='submit' name='new' value='Add event'></td></tr>
		
		<tr><td class='dark c' colspan=4>Existing events</td></tr>
		<?php echo ($total > 15) ? "<tr><td class='dim c fonts' colspan=4>$pagectrl</td></tr>" : "" ?>
		<tr>
			<td class='head c'>DEL</td>
			<td class='head c'>Event text</td>
			<td class='head c'>Date (mm-dd-yyyy)</td>
			<td class='head c'>Options</td>
		</tr>
		
		<?php echo $txt ?>
		
		<tr><td class='dim' colspan=4><input type='submit' class='submit' name='edit' value='Save changes'></td></tr>
	</table>
	</center>
		
	<?php
	
	
	pagefooter();
?>
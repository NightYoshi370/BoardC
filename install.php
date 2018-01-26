<?php
	/*
		An installer...
		...with the layout based from the installer in AB 1.92.08
		
		this file trusts you to not be an idiot (ie: filling in invalid board options)
	*/
	
	
	define('LAST_PAGE', 7);
	define('CONFIG_LENGTH', 29); // Pad with spaces until char 29. Increase it when values aren't aligned.
	
	if (file_exists("lib/config.php")) require "lib/config.php";
	else{
		$config['default-time-zone'] = 0;
		$hacks['ab-layout'] = true;
	}
	require "lib/helpers.php";
	require "lib/layout.php";
	
	

	
	// Welp
	// This takes advantage of the .htaccess rules in the lib folder
	// (this is a pretty bad design but w/e)
	if (!file_exists("lib/token.txt")){
		$h = fopen("lib/token.txt", 'wb');
		fwrite($h, openssl_random_pseudo_bytes(10));
		fclose($h);
	}
	
	$token = hash("sha256", file_get_contents("lib/token.txt"));
	
	?>
	
<!doctype html>
<html>
	<head>
		<title>BoardC -- Install</title>
		<style>
			body{
				background: 	#333;
				font-family:	"Courier New", Courier, monospace;
				font-size:		13px;
				color:			#ddd;
			}
			.container{
				padding: 		4px;
				border-spacing: 0px;
				border: 		0px;
				min-width: 		700px;
				max-width: 		100px;
				
				height: 		100%;
				text-align:		center;
			}
			.header{
				text-align:		center;
				background:		#222;
				width:			0px; /* ?????? (this was width=0 in td)*/
			}
			.content{
				background:		#111;
				vertical-align:	top;
				height: 		100%;
			}
			.warn{
				color:			#ff8080;
				font-weight:	bold;
			}
			.highlight{
				color:			#ff8080;
			}
			.ok{
				color:			#60ff60;
			}
			.sect, .sect2{
				background:		#333;
				border-spacing: 0px;
				border: 		0px;
				white-space: 	nowrap;
			}
			.sect2{
				background:		#282828;
			}
			.secthead{
				background:		#224;
				border-spacing: 0px;
				border: 		0px;
				text-align:		center;
				white-space: 	nowrap;
			}
			a:link,a:visited,a:active,a:hover{
				text-decoration-color: #80FF80;
				color:			#80FF80;
				font-weight: 	normal;
			}
			textarea, input, select, button{
				border: 			1px solid #ffdd33;
				background-color: 	#000000;
				color: 				#DDDDDD;
				font-family:		"Courier New", Courier, monospace;
				font-size:			13px;
			}
			.submit {
				border: 			2px solid #80FF80;
			}
			.sel {
				text-align: 	left;
				border-spacing: 0px;
				border: 		0px;
			}
		</style>
	</head>
	<body>
	<form method='POST' action='?'>
	<center>
		<table class='container'>
			<tr>
				<td class='header'>
					<b>BoardC Installer</b>
				</td>
			</tr>
			<tr>
				<td class='content'>
	<?php
	
	
	// Defaults
	
	if (!isset($_POST['sqlhost'])) 	$_POST['sqlhost'] 	= filter_string($sqlhost);
	if (!isset($_POST['sqlpass'])) 	$_POST['sqlpass'] 	= filter_string($sqlpass);
	if (!isset($_POST['sqluser'])) 	$_POST['sqluser'] 	= filter_string($sqluser);
	if (!isset($_POST['sqldb'])) 	$_POST['sqldb'] 	= filter_string($sqldb);
	
	$_POST['dropdb'] = filter_int($_POST['dropdb']);
	
	
	
	
	// Page handler
	$step 	= filter_int($_POST['step']);
	$cmd	= filter_string($_POST['stepcmd']);
	
	if 	($cmd == 'Next') 	$step++;
	else 					$step--;
	
	
	
	// Collect all _POST actions
	foreach ($_POST as $key => $val)
		echo "<input type='hidden' name='$key' value=\"".htmlspecialchars($val)."\">";
	
	if ($step <= 0)  {
		$step = 0;
		$buttons = 	"<input type='submit' class='submit' name='stepcmd' value='Next'>".
					"<input type='hidden' name='step' value=0>";
	} else {
		$outtoken = filter_string($_POST['auth']);
		if ($token != $outtoken) die("Bad or missing token.");	
		
		$buttons = 	"<input type='submit' class='submit' name='stepcmd' value='Back'>".
					" - <input type='submit' class='submit' name='stepcmd' value='Next'>".
					"<input type='hidden' name='step' value='$step'>";
	}

	
	
	// DB Connection
	if ($step >= 2)	$sql = new mysql_mini;
	if ($step >= 3) $db  = $sql->selectdb();
	
	/*
		Welcome screen
	*/	
	if (!$step) {

		?>
				Welcome to the BoardC installer!
			<br>
			<br>As this is a public version, you are free to use and distribute it.
			<br>As usual, no warranty of any kind is provided.
			<br>
			<br>Please report all bugs to Kak in either the <a href='http://jul.rustedlogic.net/thread.php?id=17911'>Jul</a> or <a href='http://board.kafuka.org/thread.php?id=1648'>Kafuka</a> threads.
			<br>
			<br>You will be prompted to enter the SQL Database Info in the next page.
			<br>
			<!-- we only need to put the token here -->
			<input type='hidden' name='auth' value='<?php echo $token ?>'>
		<?php	
		
	}
	
	/*
		SQL Host info
	*/		
	else if ($step == 1) {
			?>
				Please enter the SQL credentials.
				<br>The installer will attempt to connect to the specified server on the next page.
				<br>
				<br>
				<center>
				<table>
					<!-- autocomplete prevention -->
					<input style='display:none' type='text'     name='__f__usernm__'>
					<input style='display:none' type='password' name='__f__passwd__'>
					<tr><td class='sect'>SQL Host:</td><td class='sect'><input type='text' name='sqlhost' value='<?php echo $_POST['sqlhost'] ?>'></td></tr>
					<tr><td class='sect'>SQL User:</td><td class='sect'><input type='text' name='sqluser' value='<?php echo $_POST['sqluser'] ?>'></td></tr>
					<tr><td class='sect'>SQL Password:</td><td class='sect'><input type='password' name='sqlpass' value='<?php echo $_POST['sqlpass'] ?>'></td></tr>
				</table>
				</center>
			<?php
	}
	
	/*
		SQL Database info
	*/			
	else if ($step == 2) {

		?>
			The connection was successful!
		<br>
		<br>Enter the name of the database you're going to use.
		<br>If it doesn't exist it will be created.
		<br>
		<br>NOTE: Creating a database will probably require root privileges, so it's recommended to specify an already existing empty database.
		<center>
		<table>
			<tr><td class='sect'>SQL Database:</td><td class='sect'><input type='text' name='sqldb' value='<?php echo $_POST['sqldb'] ?>'></td></tr>
		</table>
		
		<?php
		
	}
	
	/*
		Post-database selection screen
	*/			
	else if ($step == 3) {
		
		if ($db) {
			// DO YOU WANT TO DROP?!?!
			$dropdbsel[$_POST['dropdb']] = "checked";
			
			?>
				The database already exists. Select an action.
				<br>
				<center>
				<table class='sel'>
					<tr>
						<td><input type='radio' name='dropdb' value=0 <?php echo filter_string($dropdbsel[0]) ?>></td>
						<td>Use the existing database and update configuration</td>
					</tr>
					<tr>
						<td><input type='radio' name='dropdb' value=1 <?php echo filter_string($dropdbsel[1]) ?>></td>
						<td>Drop the database and reinstall</td>
					</tr>
				</table>
				</center>
				<div class='warn'>
					WARNING: DROPPING THE DATABASE WILL PERMANENTLY DELETE ALL THE DATA!<br>
					IF YOU DON'T KNOW WHAT YOU'RE DOING MAKE SURE TO HAVE BACKUPS
				</div>
				<br>
				
			<?php
			
		} else {
			?>
				The database '<?php echo $_POST['sqldb'] ?>' you have specified doesn't seem to exist.
				<br>It will be created before importing the .SQL file.
				<br>
				<span class='highlight'>
					NOTE: The SQL user must have permissions to create tables, otherwise this won't work.
				</span>
				<br>
				<br>If this is correct you can continue; otherwise check the SQL Connection details.
			
			<?php
			
		}
		
	}
	/*
		HTML frontend for config.php
	*/		
	else if ($step == 4) {
		/*
			Not included:
			deleted-user-id
			trash-id
			default-time-zone
		*/
		print "
			Board Options
			<br>Fill in the table. These options will be written in <span class='highlight'>'lib/config.php'</span>
			<br>Please <i>be careful</i> and check the options before continuing.
			<br>
			<center>
			<table style='padding: 20px'>
				".set_heading('Layout options')."
				".set_input("board-name", "Board name", 250, "BoardC")."
				".set_input("board-title", "Header HTML", 550, "<img src='images/sampletitle3.png'>")."
				".set_input("board-url", "Header Link", 500, "http://localhost/board/")."
				".set_input("footer-title", "Footer Text", 250, "The Internet")."
				".set_input("footer-url", "Footer Link", 300, "http://localhost/")."
				".set_input("admin-email", "Support email", 250, "kak@nothing.null")."
				
				".set_heading("Board options")."
				".set_radio('admin-board', 'Admin board', 'No|Yes')."
				".set_radio('allow-rereggie', 'Allow reregistering', 'No|Yes', 1)."
				".set_radio('show-comments', 'Show HTML Comments', 'No|Yes')."
				".set_radio('allow-thread-erase', 'Allow thread deletion', 'No|Yes', 1)."
				".set_input('auth-salt', 'Token salt string', 300, 'sillydefaultstring')."
				".set_input('post-break', 'Time to wait before posting again', 20, 0, "seconds")."
				".set_input('posts-to-get-title', 'Custom title requirements', 40, 100, "posts")."

				".set_heading("RPG Elements")."				
				".set_input('coins-multiplier', 'Coins multiplier', 40, 20)."
				
				".set_heading("File uploads")."
				".set_radio('enable-image-uploads', 'Allow image uploads', 'No|Yes', 1)."
				".set_radio('enable-file-uploads', 'Allow file uploads (note: no coding for attachments)', 'No|Yes', 1)."
				".set_input('max-icon-size-x', 'Max icon width', 50, 16, "px")."
				".set_input('max-icon-size-y', 'Max icon height', 50, 16, "px")."
				".set_input('max-icon-size-bytes', 'Max icon size', 70, 10000, "bytes")."
				".set_input('max-avatar-size-x', 'Max avatar width', 50, 180, "px")."
				".set_input('max-avatar-size-y', 'Max avatar height', 50, 180, "px")."
				".set_input('max-avatar-size-bytes', 'Max avatar size', 70, 80000, "bytes")."
				
				".set_heading("Defaults")."
				".set_input('default-date-format', 'Default date format', 100, 'd/m/y')."
				".set_input('default-time-format', 'Default time format', 100, 'H:i:s')."
				
				".set_heading("IRC Server Info")."
				".set_input('irc-server', "IRC Servers (separated by ;)", 470, ["irc.sample.net","irc.test.com"])."
				".set_input('irc-title', "IRC Server title", 250, "A sample IRC server")."
				".set_input('public-chan', "Public channel", 160, "#powl0-grgrh")."
				".set_input('private-chan', "Private channel", 160, "#powl1-bienf")."
				
				".set_heading("Development Options")."
				".set_input('force-userid', 'Force user ID',  60, 0)."
				".set_radio('force-sql-debug-on', 'Always show SQL Debugger', 'No|Yes', 1)."	
				".set_radio('force-error-printer-on', 'Always show error reporter', 'No|Yes', 1)."
				
				".set_heading("Misc")."
				".set_radio('failed-attempt-at-irc', 'Use alternate thread layout', 'No|Yes', 0)."
				".set_radio('ab-layout', 'AB1.92 layout declarations', 'No|Yes', 1)."
				".set_radio('a-good-feature', 'extract $_REQUEST', 'No|Yes', 1)."
				".set_radio('username-rainbow', 'Force rainbow color usernames', 'No|Yes', 0)."
				
			</table>
			<br>
			</center>
		";
	}
	
	/*
		User credential for the first user
	*/		
	else if ($step == 5) {
		
		print "
				Login information
			<br>You will use this to login to the board.
			<br>REMEMBER: Only alphanumerical characters and spaces are allowed for the username.
			<br>
			<center>
			<table>
				<input style='display:none' type='text'     name='__f__usernm__'>
				<input style='display:none' type='password' name='__f__passwd__'>
				".set_heading("Register")."
				".set_input("username",  "Username", 250)."
				".set_psw("pass1", "Password", 250)."
				".set_psw("pass2", "Confirm password", 250)."				
			</table>
			</center>
			<br>
		";
	}
	
	/*
		Check if the username and password are valid
	*/		
	else if ($step == 6) {
		
		$user 		= filter_string($_POST['username']);
		$pass 		= filter_string($_POST['pass1']);
		$passchk 	= filter_string($_POST['pass2']);
		
		$message	= "";
		if (!$user) $message = "You can't leave the username blank.";
		else if (preg_replace('/[^\da-z ]/i', '', $user) != $user) $message = "The username contains invalid characters.";
		else if ($pass != $passchk) $message = "The password and the retype don't match.";
		
		if ($message){
			?>
				Invalid registration info.
			<br>
			<br>Reason:
			<div style='background: #000'><?php echo $message ?></div>
			<br>Return to the previous page and enter valid registration info.
			<br>
			<input type='submit' class='submit' name='stepcmd' value='Back'>
			<input type='hidden' name='step' value=6>
			<?php
			die;
		}
		
		?>
			The board will now be configured.
			<div class='warn'>WARNING: IF YOU HAVE SELECTED TO DROP THE DATABASE, ALL DATA WILL BE DELETED</div>
			<br>
			<br>You can go back to review the choices, or click <span class='highlight'>'Next'</span> to start the installation.
			<br>
			<br>
		<?php
	}
	
	/*
		Actually install the board	
	*/
	else if ($step == 7) {
		
		set_time_limit(0);
		
		//	Here we go
		
		echo "<span style='text-align: left'><pre>";
		echo "Attempting to install...";
		
/*
	LAYOUT OF CONFIG.PHP FILE
	I wanted to leave the formatting intact including comments as (for now) you're expected to edit the file manually :/
	maybe this will change in the future
*/

$configfile = "<?php
/*
	Configuration
*/
	
	// Sql database options
	\$sqlhost    = '".addslashes($_POST['sqlhost'])."'; // Database host
	\$sqluser    = '".addslashes($_POST['sqluser'])."'; // Username
	\$sqlpass    = '".addslashes($_POST['sqlpass'])."'; // Password
	\$sqldb      = '".addslashes($_POST['sqldb'])."';   // Database
	\$sqlpersist = true; // Persist connection
	
	// Root Admin IPs
	\$adminips = array(
//		'127.0.0.1',
	);

	// \$config options
	
	\$config = array(

		// Board Options
		".config_bool('admin-board')."
		".config_bool('allow-rereggie')."
		'deleted-user-id' => 2, // DO NOT CHANGE
		'trash-id' 		  => 3, // DO NOT CHANGE
		".config_bool('show-comments')."
		".config_string('auth-salt')."
		".config_int('post-break')."
		".config_bool('allow-thread-erase')."
		".config_int('posts-to-get-title')."
		
		// Layout
		".config_string('board-name')."
		".config_string('board-title')."
		".config_string('board-url')."
		".config_string('footer-title')."
		".config_string('footer-url')."
		".config_string('admin-email')."
		
		// RPG Elements
		".config_int('coins-multiplier')." // Multiplier used to calculate the amount of coins. NOTE: CHANGING THIS WILL ALTER THE COIN COUNT OF EVERY USER
		
		// File uploads
		
		".config_bool('enable-image-uploads')."
		".config_bool('enable-file-uploads')."
		
		".config_int('max-icon-size-x')."
		".config_int('max-icon-size-y')."
		".config_int('max-icon-size-bytes')."
		".config_int('max-avatar-size-x')."
		".config_int('max-avatar-size-y')."
		".config_int('max-avatar-size-bytes')."
		
		// Defaults
		'default-time-zone' => 0, // DO NOT CHANGE
		".config_string('default-date-format')."
		".config_string('default-time-format')."
		
		// News 'plugin'
		".config_bool('enable-news')."
		".config_string('news-name')."
		".config_string('news-title')."
		".config_int('max-preview-length')."
		".config_int('news-write-perm')."
		".config_int('news-admin-perm')."
		
		// IRC
		'enable-irc-reporting' => true, // DO NOT CHANGE (otherwise certain triggered errors won't be shown in the error list)
		".config_array('irc-server')."
		".config_string('irc-title')."
		".config_string('public-chan')."
		".config_string('private-chan')."
		
		
		// Development stuff
		
		'dummy-name' => \"dummy variable\",
		".config_bool('force-userid')."
		".config_bool('force-sql-debug-on')."
		".config_bool('force-error-printer-on')."

	);
	
	//options for dumb stuff
	\$hacks = array(
		".config_bool('failed-attempt-at-irc')."
		".config_bool('username-rainbow')."
		".config_bool('a-good-feature')."
		".config_bool('ab-layout')."
	);
	
?>";
		
		echo "\nWriting settings to lib/config.php...";
		
		$w = fopen("lib/config.php", "w");
		$res = fwrite($w, $configfile);
		fclose($w);
		
		echo checkres($res);
		
		
		
		if ($db && filter_bool($_POST['dropdb'])) {;
			// Database doesn't exist, create it
			echo "Dropping database `{$_POST['sqldb']}`...\n";
			$sql->query("DROP DATABASE IF EXISTS `{$_POST['sqldb']}`");
		}
		
		
		// If the database already exists, well, nothing actually happens.
		// So we do not bother checking that
		
		echo "Attempting to create database...";
		
		try{
			$sql->query("CREATE DATABASE `{$_POST['sqldb']}`; DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
			echo checkres(true);
		}
		catch (PDOException $x){
			if ($x->getCode() == 42000) {
				echo checkres(false);
				die("\nAccess denied. You have to create the database manually under phpMyAdmin.");
			} else {
				throw $x;
			}
		}
		
		
		// Before attempting to do anything, actually select the database
		$sql->selectdb($_POST['sqldb']);
		
		$sql->start();
		$c = array(); // Define the array with the results
		$ctime = ctime();
		
		// Only import if the database doesn't exist or we've dropped it
		if (!$db || filter_bool($_POST['dropdb'])){
			
			echo "Importing SQL files...";
			$sql->import("install.sql");
			
			$c[] = $sql->query(
				"INSERT INTO `events` (`id`, `user`, `time`, `text`, `private`) VALUES".
				"(1, 1, $ctime, 'The board\'s anniversary!', 0);"
			);

			$c[] = $sql->query(
				"INSERT INTO `users` (`id`, `name`, `password`, `lastip`, `since`, `powerlevel`) VALUES".
				"(1, '".prepare_string($_POST['username'])."','".password_hash(prepare_string($_POST['pass1']), PASSWORD_DEFAULT)."','{$_SERVER['REMOTE_ADDR']}', $ctime, 5),".
				"(2, 'Deleted user', 'rip','{$_SERVER['REMOTE_ADDR']}', $ctime, '-2');"
			);	
			
		} else {
			// Just update configuration: only replace userid value
			echo "Updating registration information...";
			$c[] = $sql->query("
					UPDATE users SET
						name     = '".prepare_string($_POST['username'])."',
						password = '".password_hash(prepare_string($_POST['pass1']), PASSWORD_DEFAULT)."'
					WHERE id = 1
				");
		}

		
		
		
		if ($sql->finish($c)){
			echo checkres(true);
			
			if (!file_exists("userpic")) mkdir("userpic");
			if (!file_exists("userpic/1")) mkdir("userpic/1");
			
			echo "Operation completed successfully.\n";
			echo "You can (and <i>should</i>) delete this file and login <a href='login.php'>here</a>.";
			$buttons = "";
		
		} else {
			echo checkres(false);
			echo $sql->errors." queries have failed.\nBroken queries:\n\n";
			echo implode("\n", $sql->q_errors);
			echo "\nPlease fix the problems that have occured. This may require dropping the partially-created tables, and trying again.";
			echo "\n<font color=#e0e080>NOTE:</font> it is possible the installation was still successful, especially if you have only recieved '<font color=#FF8080>Table already exists</font>' errors.";
			echo "\nHowever, it is far more likely you will need to redo the installation.";
			echo "\nIf you would like to retry, you can return to the previous page and try again.</pre></span>";
			
			$buttons = 	"<input type='submit' class='submit' name='stepcmd' value='Return'>".
						"<input type='hidden' name='step' value=7>";

		}
		
		
	}
	
	
					?>
					<!-- footer -->
					<?php echo $buttons ?>
				</td>
			</tr>
			<tr>
				<td class='header'>
					Acmlmboard Installer v1.4-m (05-10-16)
				</td>
			</tr>
		</table>
		
	</center>
	</form>
	</body>
</html><?php




class mysql_mini{
	
	public $db 			= NULL;
	public $queries 	= 0;
	public $errors 		= 0;
	public $q_errors 	= array();
	
	public function __construct(){
		try {
			$dsn 		= "mysql:host={$_POST['sqlhost']};charset=utf8";
			$options 	= array(
				PDO::ATTR_PERSISTENT 		 => true,
				PDO::ATTR_ERRMODE 			 => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_EMULATE_PREPARES   => true, // sigh
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			);
			$this->db = new PDO($dsn, $_POST['sqluser'], $_POST['sqlpass'], $options);
			return $this->db;
		}
		catch (PDOException $x){
			global $step;
			?>
				<span class='warn'>
				Error!<br>
				Couldn't connect to the MySQL server
				</span>
				<br>Reason:
				<br>
				<span style='background: #000'><?php echo $x->getMessage() ?></span>
				<br>
				<br>Return to the previous page and enter correct login credentials to the database.
				<br>
				<input type='submit' class='submit' name='stepcmd' value='Back'>
				<input type='hidden' name='step' value=<?php echo $step ?>>
			<?php
			die;
		}
	}
	
	public function selectdb(){
		try {
			$res = $this->db->query("USE {$_POST['sqldb']}");
		}
		catch (PDOException $x){
			return false;
		}
		return true;
	}
	
	public function query($q){
		$this->queries++;
		try {
			$this->db->query($q);
			$res = true;
		}
		catch (PDOException $x){
			$res = false;
			$this->errors++;
			$this->q_errors[] = $q." | ".$this->db->errorInfo()[2];
		}
		return $res;
	}
	
	// Import the SQL file line by line
	// If a line ends with ; process the buffer		
	public function import($file){
		global $c;
		$h = fopen($file, 'r');
		$b = "";
		while(($l = fgets($h, 256)) !== false){
			$l	  = trim($l);
			$b   .= $l;
			$cnt  = strlen($l);
			// If the last character is ;, execute the query
			if ($l[$cnt-1] == ';'){
				//echo $b."<br>";
				$c[] = $this->query($b);
				$b = "";
			}
		}
		fclose($h);
	}
	
	public function start(){
		return $this->db->beginTransaction();
	}
	
	public function end(){
		return $this->db->commit();
	}
	
	public function undo(){
		return $this->db->rollBack();
	}
	
	public function finish($list = array(true)){
		foreach ($list as $queryres){
			if ($queryres === false && $queryres !== 0){
				$this->undo();
				return false;
			}
		}
		$this->end();
		return true;
	}
}
	
	
	
	
	function set_heading($desc){
		return "<tr><td class='secthead' colspan=2>$desc</td></tr>";
	}
	
	function set_input($name, $desc, $width = 250, $default = "", $extra = ""){
		global $config, $hacks;
		
		if		(isset($_POST[$name])) 	$field = $_POST[$name];
		else if (isset($config[$name])) $field = $config[$name];
		else if (isset($hacks[$name])) 	$field = $hacks[$name];
		else $field = $default;
		
		if ($extra) $extra = "&nbsp;$extra"; // I'm picky about this
		if (is_array($field)) $field = implode(";", $field);

		
		// NOTE THIS HAS TO BE ADDSLASHED BEFORE GOING IN CONFIG.PHP
		return "
			<tr>
				<td class='sect'>$desc</td>
				<td class='sect2'>
					<input type='text' name='$name' style='width: {$width}px' value=\"$field\">$extra
				</td>
			</tr>";
	}
	
	function set_radio($name, $desc, $vals, $default = 0){
		global $config, $hacks;
		
		if		(isset($_POST[$name])) 	$field = $_POST[$name];
		else if (isset($config[$name])) $field = $config[$name];
		else if (isset($hacks[$name])) 	$field = $hacks[$name];
		else $field = $default;
		
		$sel[$field] = 'checked';
		
		$list 	= explode("|", $vals);
		$txt 	= "";
		
		foreach($list as $i => $x)
			$txt .= "<input type='radio' name='$name' value='$i' ".filter_string($sel[$i]).">&nbsp;$x ";
		
		return "
			<tr>
				<td class='sect'>$desc</td>
				<td class='sect2'>
					$txt
				</td>
			</tr>";
	}
	
	function set_powl($name, $desc, $default = 0){
		global $config, $hacks;
		
		if		(isset($_POST[$name])) 	$field = $_POST[$name];
		else if (isset($config[$name])) $field = $config[$name];
		else if (isset($hacks[$name])) 	$field = $hacks[$name];
		else $field = $default;
		
		return "
			<tr>
				<td class='sect'>$desc</td>
				<td class='sect2'>
					".powerList($default, $name, true)."
				</td>
			</tr>";
	}
	
	function set_psw($name, $desc, $width = 250){
		global $config, $hacks;
		
		if		(isset($_POST[$name])) 	$field = $_POST[$name];
		else if (isset($config[$name])) $field = $config[$name];
		else if (isset($hacks[$name])) 	$field = $hacks[$name];
		else $field = '';
		
		return "
			<tr>
				<td class='sect'>$desc</td>
				<td class='sect2'>
					<input type='password' name='$name' style='width: {$width}px' value=\"$field\">
				</td>
			</tr>";
	}
	
	// Formatting of config.php, str_pad'd to keep a clean layout
	function config_bool  ($name){return str_pad("'$name'", CONFIG_LENGTH)."=> ".(filter_bool($_POST[$name]) ? (string) "true" : (string) "false").",";}
	function config_int   ($name){return str_pad("'$name'", CONFIG_LENGTH)."=> ".filter_int($_POST[$name]).",";}
	function config_string($name){return str_pad("'$name'", CONFIG_LENGTH)."=> \"".str_replace("\"", "\\\"", filter_string($_POST[$name]))."\",";}
	function config_array ($name){
		$_POST[$name] = str_replace("\"", "\\\"", filter_string($_POST[$name]));
		$_POST[$name] = str_replace(";", "\",\"", $_POST[$name]);
		return str_pad("'$name'", CONFIG_LENGTH)."=> [\"{$_POST[$name]}\"],";
	}
	function checkres($r){return $r ? "<span class='ok'>OK!</span>\n" : "<span class='warn'>ERROR!</span>\n";}
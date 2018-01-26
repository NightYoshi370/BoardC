<?php


	require "lib/function.php";
	

	$faq = array(
		// obvious placeholder but not really
		'The only rule'	=> "<center>
								Use common sense before posting.<br>
								Attempting to abuse loopholes of this rule will result in an instant permaban.<br>
								Remember, your right to be here is a privilege - <b>not</b> a right.
							</center>",
		
	);
	
	// FAQ Format
	$txt = "";
	foreach($faq as $i => $x)
		$txt .= "
		<table class='main w'>
			<tr>
				<td class='head c'>
					<b>$i</b><div style='float: right;'>[<a href='#top'>^</a>]</div>
				</td>
			</tr>
			<tr>
				<td class='light'>
					$x
				</td>
			</tr>
		</table><br>
		";
		
	pageheader("The Rules");
	print "<br id='top'>$txt";
	pagefooter();
	
?>
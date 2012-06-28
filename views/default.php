<?php
if ( isset($msg) ) {
	echo '<h2>'.$msg.'</h2>';
	if( isset($json) ) {
		echo '<br /><br /><pre>';
		print_r($json);
		echo '</pre>';
	}
}
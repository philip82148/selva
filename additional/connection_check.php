<?php

/** Sets up the WordPress Environment. */
require __DIR__ . '/../../../../wp-load.php';

// ここから
do_in_session(function() {
	var_dump($_SESSION['index']++);
});

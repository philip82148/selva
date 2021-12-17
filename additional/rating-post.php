<?php

// ここはwp-comments-postのパクリ
if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
	$protocol = $_SERVER['SERVER_PROTOCOL'];
	if ( ! in_array( $protocol, array( 'HTTP/1.1', 'HTTP/2', 'HTTP/2.0' ), true ) ) {
		$protocol = 'HTTP/1.0';
	}

	header( 'Allow: POST' );
	header( "$protocol 405 Method Not Allowed" );
	header( 'Content-Type: text/plain' );
	exit;
}

/** Sets up the WordPress Environment. */
require __DIR__ . '/../../../../wp-load.php';


// ここから
if($_POST['is_omnibus'] == 'true') {
	star_rate($_POST['course_id'], $_POST['rating_no'], $_POST['stars'], $_POST['lecturer_id']);
} else {
	star_rate($_POST['course_id'], $_POST['rating_no'], $_POST['stars']);
	if(!empty($_POST['lecturer_id'])) update_cache(true, $_POST['lecturer_id']);
	else                              update_cache(false, $_POST['course_id']);
}

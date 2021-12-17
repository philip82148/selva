<?php

function retrieve_recent_comments() {
    global $wpdb;

    return $wpdb->get_results(
		"SELECT posts.post_type, posts.post_name, comments.comment_content
		   FROM $wpdb->comments AS comments
		   JOIN $wpdb->posts AS posts
	         ON comments.comment_post_ID=posts.ID
		  ORDER BY comments.comment_date DESC
  		  LIMIT 8",
        'ARRAY_A'
    );
}

function retrieve_many_views_courses() {
    global $wpdb;

    return $wpdb->get_results(
		"SELECT lecturer_id, lecturer_name, post_views, average_rating_cache
		   FROM {$wpdb->prefix}selva_lecturers
		  WHERE	has_post
	   ORDER BY post_views DESC
		  LIMIT 8",
        'ARRAY_A'
    );
}

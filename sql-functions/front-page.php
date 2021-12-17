<?php

function retrieve_faculties(){
	global $wpdb;

	return $wpdb->get_col(
		"SELECT	faculty
		   FROM	{$wpdb->prefix}selva_lecturers
		  WHERE	has_post
	   GROUP BY	faculty"
	) ?? [];
}

function retrieve_top_page_view_lecturers($faculty){
	global $wpdb;

	return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT lecturer_id, lecturer_name, post_views, average_rating_cache
        	   FROM {$wpdb->prefix}selva_lecturers
        	  WHERE	faculty=%s AND has_post
           ORDER BY post_views DESC
              LIMIT 3",
			$faculty
		),
		'ARRAY_A'
	) ?? [];
}

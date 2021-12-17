<?php

function retrieve_recommended_pages(bool $is_lecturer, int $id) : array {
    global $wpdb;

	$previous_lecturer_id = 1;
	$previous_course_id = 1;

	if($is_lecturer) {
		$previous_lecturer_id = $id;
	} else {
		$previous_course_id = $id;
	}

	$recommend_pages = $wpdb->get_results(
        $wpdb->prepare(
	  "(" . "SELECT	actions.next_course_id AS course_id, actions.next_lecturer_id AS lecturer_id, actions.count,
					lecturers.lecturer_name, lecturers.faculty
					, CASE WHEN courses.has_post THEN courses.post_views           ELSE lecturers.post_views           END AS post_views
					, CASE WHEN courses.has_post THEN courses.average_rating_cache ELSE lecturers.average_rating_cache END AS average_rating_cache
					, CASE WHEN courses.has_post THEN courses.title                ELSE (SELECT	MIN(courses.title)
																						   FROM	{$wpdb->prefix}selva_courses AS courses
																						  WHERE	courses.lecturer_id=actions.next_lecturer_id) END AS title
					, courses.target_department
			   FROM {$wpdb->prefix}selva_user_actions AS actions
			   JOIN {$wpdb->prefix}selva_lecturers AS lecturers
			     ON actions.next_lecturer_id=lecturers.lecturer_id
		       JOIN {$wpdb->prefix}selva_courses AS courses
				 ON actions.next_course_id=courses.course_id
			  WHERE actions.previous_lecturer_id=%d
					AND actions.previous_course_id=%d
					AND (lecturers.has_post OR courses.has_post))
		      UNION
		  	(SELECT 1 AS course_id, lecturers.lecturer_id, 0 AS count,
				   	lecturers.lecturer_name, lecturers.faculty
					, lecturers.post_views
					, lecturers.average_rating_cache
					, MIN(courses.title) AS title
					, '' AS target_department
			   FROM	{$wpdb->prefix}selva_lecturers AS lecturers
			   JOIN	{$wpdb->prefix}selva_courses AS courses
			     ON	lecturers.lecturer_id=courses.lecturer_id
			  WHERE lecturers.has_post
		   GROUP BY lecturers.lecturer_id
		   	  LIMIT	100)
		   ORDER BY count DESC
			  LIMIT 100",
			$previous_course_id,
			$previous_lecturer_id
        ),
        'ARRAY_A'
    );

	return $recommend_pages ?? [];
}

function store_next_page(bool $next_is_lecturer, int $next_id) : bool {
    global $wpdb;

	$next_lecturer_id = 1;
	$next_course_id = 1;

	if($next_is_lecturer) {
		$next_lecturer_id = $next_id;
	} else {
		$next_course_id = $next_id;
	}

	$previous_lecturer_id = 0;
	$previous_course_id = 0;

	do_in_session(function() use (&$previous_lecturer_id, &$previous_course_id, $next_is_lecturer, $next_id) {
		if(isset($_SESSION['previous_id']) && isset($_SESSION['previous_is_lecturer'])) {
			if($_SESSION['previous_is_lecturer']) {
				$previous_lecturer_id = $_SESSION['previous_id'];
				$previous_course_id = 1;
			} else {
				$previous_lecturer_id = 1;
				$previous_course_id = $_SESSION['previous_id'];
			}

		}

		$_SESSION['previous_is_lecturer'] = $next_is_lecturer;
		$_SESSION['previous_id'] = $next_id;
	});

	if(empty($previous_course_id) || empty($previous_lecturer_id)) return false;
	if($next_course_id === $previous_course_id && $next_lecturer_id === $previous_lecturer_id) return false;

	$has_successed = $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO {$wpdb->prefix}selva_user_actions
			        (previous_course_id, previous_lecturer_id, next_course_id, next_lecturer_id)
			 VALUES (%d, %d, %d, %d)
			   ON DUPLICATE KEY UPDATE count=count+1",
			$previous_course_id,
			$previous_lecturer_id,
			$next_course_id,
			$next_lecturer_id
        ),
        'ARRAY_A'
    );

    return $has_successed !== false;
}

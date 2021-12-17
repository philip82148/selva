<?php

// pageview数更新
function increment_page_views(bool $is_lecturer, int $id) {
	global $wpdb;

	if($is_lecturer) {
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}selva_lecturers SET post_views=post_views+1 WHERE lecturer_id=%d",
				$id
			)
		);
	} else {
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}selva_courses SET post_views=post_views+1 WHERE course_id=%d",
				$id
			)
		);
	}
}

// 教師データ取得
function fetch_lecturer_data(int $lecturer_id) {
	global $wpdb;

	// 教師データ取得
	$lecturer = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}selva_lecturers WHERE lecturer_id=%d",
			$lecturer_id
		),
		'ARRAY_A'
	);
	if(empty($lecturer)) return NULL;

	// 授業データ取得
	$lecturer['courses'] = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}selva_courses WHERE lecturer_id=%d",
			$lecturer_id
		),
		'ARRAY_A'
	) ?? [];

	// 星の数とタグ評価を取得
	foreach($lecturer['courses'] as $i => $course) {
		$lecturer['courses'][$i]['ratings'] = fetch_ratings($course, $course['course_id']);
	}

	// オムニバス授業データ取得
	$lecturer['omnibus_courses'] = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT courses.course_id, courses.title, courses.target_department,
					courses.semester, courses.day_and_period, courses.campus
			   FROM	{$wpdb->prefix}selva_courses AS courses
			   JOIN	{$wpdb->prefix}selva_omnibus_course_lecturers AS omnibus
				 ON	courses.course_id=omnibus.course_id
			  WHERE	omnibus.lecturer_id=%d",
			$lecturer_id
		),
		'ARRAY_A'
	) ?? [];
	
	return $lecturer;
}

// オムニバス授業取得
function fetch_omnibus_course_data(int $course_id) {
	global $wpdb;

	// オムニバス授業データ取得
	$course = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}selva_courses WHERE course_id=%d",
			$course_id
		),
		'ARRAY_A'
	);
	if(empty($course)) return NULL;

	// 星の数を取得
	$course['ratings'] = fetch_ratings($course, $course['course_id']);

	// 教師データ取得
	$course['lecturers'] = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT lecturers.lecturer_id, lecturers.lecturer_name,
					omnibus.nonsession_rating1_1stars, omnibus.nonsession_rating1_2stars,
					omnibus.nonsession_rating1_3stars, omnibus.nonsession_rating1_4stars,
					omnibus.nonsession_rating1_5stars, omnibus.nonsession_rating2_1stars,
					omnibus.nonsession_rating2_2stars, omnibus.nonsession_rating2_3stars,
					omnibus.nonsession_rating2_4stars, omnibus.nonsession_rating2_5stars,
					omnibus.nonsession_rating3_1stars, omnibus.nonsession_rating3_2stars,
					omnibus.nonsession_rating3_3stars, omnibus.nonsession_rating3_4stars,
					omnibus.nonsession_rating1_5stars, omnibus.nonsession_rating_tags
			   FROM	{$wpdb->prefix}selva_lecturers AS lecturers
			   JOIN	{$wpdb->prefix}selva_omnibus_course_lecturers AS omnibus
				 ON	lecturers.lecturer_id=omnibus.lecturer_id
			  WHERE	omnibus.course_id=%d",
			$course_id
		),
		'ARRAY_A',
	) ?? [];

	// 星の数を取得
	foreach($course['lecturers'] as $i => $lecturer) {
		$course['lecturers'][$i]['ratings'] = fetch_ratings($lecturer, $course_id, $lecturer['lecturer_id']);
	}

	// 同じ名前の授業を取得
	$course['similar_courses'] = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT course_id, target_department, semester, day_and_period, campus
			   FROM	{$wpdb->prefix}selva_courses
			  WHERE	title=%s
			  		AND course_id<>%d",
			$course['title'],
			$course_id
		),
		'ARRAY_A'
	) ?? [];

	return $course;
}

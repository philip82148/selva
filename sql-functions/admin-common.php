<?php

function mysql_last_error($string='', $last_error=NULL, $last_query=NULL) {
    global $wpdb;

    if(!isset($last_error))
        $last_error = $wpdb->last_error;

    if(!isset($last_query))
        $last_query = $wpdb->last_query;

    if($last_error)
        $string .= ' MySQLのメッセージ：' . $last_error;

    if($last_query)
    	$string .= ' 最後のクエリ：' . $last_query;

    return $string;
}

function delete_courses($time, $operator='by', $exclude_omnibus=false) {
    global $wpdb;

	switch($operator) {
		case 'by':
			$operator = '<';
			break;
		case 'at':
			$operator = '=';
			break;
		case 'from':
			$operator = '>';
			break;
		default:
			return 'オペレーターが不正です。';
	}

	$lecturer_id_condition = '';
	if($exclude_omnibus) {
		$lecturer_id_condition = " AND lecturer_id>2";
	}

	$has_succeeded = $wpdb->query(
        $wpdb->prepare(
            "DELETE actions
               FROM {$wpdb->prefix}selva_user_actions AS actions
               JOIN {$wpdb->prefix}selva_courses AS courses
                 ON actions.previous_course_id=courses.course_id
              WHERE courses.updated_at$operator%s AND courses.course_id>2
			  		$lecturer_id_condition",
			$time
		)
    );

	if($has_succeeded === false)
		return mysql_last_error('ユーザーの行動テーブルの削除に失敗しました。');

	$has_succeeded = $wpdb->query(
        $wpdb->prepare(
            "DELETE actions
               FROM {$wpdb->prefix}selva_user_actions AS actions
               JOIN {$wpdb->prefix}selva_courses AS courses
                 ON actions.next_course_id=courses.course_id
              WHERE courses.updated_at$operator%s AND courses.course_id>2
			  		$lecturer_id_condition",
			$time
		)
    );

	if($has_succeeded === false)
		return mysql_last_error('ユーザーの行動テーブルの削除に失敗しました。');

	$has_succeeded = $wpdb->query(
        $wpdb->prepare(
            "DELETE ratings
               FROM {$wpdb->prefix}selva_course_ratings AS ratings
               JOIN {$wpdb->prefix}selva_courses AS courses
                 ON ratings.course_id=courses.course_id
              WHERE courses.updated_at$operator%s
			  		$lecturer_id_condition",
			$time
		)
    );

	if($has_succeeded === false)
		return mysql_last_error('授業評価テーブルの削除に失敗しました。');

	if(!$exclude_omnibus) {
		$has_succeeded = $wpdb->query(
			$wpdb->prepare(
				"DELETE omnibus
				   FROM {$wpdb->prefix}selva_omnibus_course_lecturers AS omnibus
				   JOIN {$wpdb->prefix}selva_courses AS courses
					 ON omnibus.course_id=courses.course_id
				  WHERE courses.updated_at$operator%s",
				$time
			)
		);

		if($has_succeeded === false)
			return mysql_last_error('オムニバス授業テーブルの削除に失敗しました。');

		$has_succeeded = $wpdb->query(
			$wpdb->prepare(
				"DELETE ratings
				   FROM {$wpdb->prefix}selva_omnibus_course_ratings AS ratings
				   JOIN {$wpdb->prefix}selva_courses AS courses
					 ON ratings.course_id=courses.course_id
				  WHERE courses.updated_at$operator%s",
				$time
			)
		);

		if($has_succeeded === false)
			return mysql_last_error('オムニバス授業評価テーブルの削除に失敗しました。');
	}

	$deleted_count = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}selva_courses
              WHERE updated_at$operator%s AND course_id>2
			  		$lecturer_id_condition",
			$time
		)
    );

	if($deleted_count === false)
		return mysql_last_error('授業テーブルの削除に失敗しました。');

	return (int)$deleted_count;
}

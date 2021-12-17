<?php

require_once(__DIR__ . '/admin-common.php');

// 教員名、授業名、学期曜日時限、キャンパスが同じ授業をまとめる
// オムニバス授業も教員名がすべて同じならまとめる
// ユーザーの行動テーブルのまとめは面倒くさいのでしない
function organize_courses() : array {
    global $wpdb;

    $delete_by_time = $wpdb->get_var("SELECT CURRENT_TIMESTAMP");

	// それぞれの授業のinsessionの評価をそれぞれのnonsessionに移動
	if(!move_course_to_nonsession_ratings($delete_by_time)) return ['error_message' => '授業評価データの変換に失敗しました。'];
	if(!move_omnibus_to_nonsession_ratings($delete_by_time)) return ['error_message' => 'オムニバス授業評価データの変換に失敗しました。'];
	
	// 授業テーブルのまとめ
	$updated_count = $wpdb->query(
        "UPDATE {$wpdb->prefix}selva_courses AS to_,
				(SELECT MIN(course_id) AS course_id, all_lecturer_ids, title,
						target_department, semester,
						GROUP_CONCAT(DISTINCT day_and_period ORDER BY day_and_period SEPARATOR '、') AS day_and_period,
						campus, SUM(post_views) AS post_views,
						SUM(nonsession_rating1_1stars) AS nonsession_rating1_1stars,
						SUM(nonsession_rating1_2stars) AS nonsession_rating1_2stars,
						SUM(nonsession_rating1_3stars) AS nonsession_rating1_3stars,
						SUM(nonsession_rating1_4stars) AS nonsession_rating1_4stars,
						SUM(nonsession_rating1_5stars) AS nonsession_rating1_5stars,
						SUM(nonsession_rating2_1stars) AS nonsession_rating2_1stars,
						SUM(nonsession_rating2_2stars) AS nonsession_rating2_2stars,
						SUM(nonsession_rating2_3stars) AS nonsession_rating2_3stars,
						SUM(nonsession_rating2_4stars) AS nonsession_rating2_4stars,
						SUM(nonsession_rating2_5stars) AS nonsession_rating2_5stars,
						SUM(nonsession_rating3_1stars) AS nonsession_rating3_1stars,
						SUM(nonsession_rating3_2stars) AS nonsession_rating3_2stars,
						SUM(nonsession_rating3_3stars) AS nonsession_rating3_3stars,
						SUM(nonsession_rating3_4stars) AS nonsession_rating3_4stars,
						SUM(nonsession_rating3_5stars) AS nonsession_rating3_5stars
				   FROM	(SELECT	MIN(course_id) AS course_id, all_lecturer_ids, title,
								GROUP_CONCAT(DISTINCT target_department ORDER BY target_department SEPARATOR '/') AS target_department,
								semester, day_and_period, campus, SUM(post_views) AS post_views,
								SUM(nonsession_rating1_1stars) AS nonsession_rating1_1stars,
								SUM(nonsession_rating1_2stars) AS nonsession_rating1_2stars,
								SUM(nonsession_rating1_3stars) AS nonsession_rating1_3stars,
								SUM(nonsession_rating1_4stars) AS nonsession_rating1_4stars,
								SUM(nonsession_rating1_5stars) AS nonsession_rating1_5stars,
								SUM(nonsession_rating2_1stars) AS nonsession_rating2_1stars,
								SUM(nonsession_rating2_2stars) AS nonsession_rating2_2stars,
								SUM(nonsession_rating2_3stars) AS nonsession_rating2_3stars,
								SUM(nonsession_rating2_4stars) AS nonsession_rating2_4stars,
								SUM(nonsession_rating2_5stars) AS nonsession_rating2_5stars,
								SUM(nonsession_rating3_1stars) AS nonsession_rating3_1stars,
								SUM(nonsession_rating3_2stars) AS nonsession_rating3_2stars,
								SUM(nonsession_rating3_3stars) AS nonsession_rating3_3stars,
								SUM(nonsession_rating3_4stars) AS nonsession_rating3_4stars,
								SUM(nonsession_rating3_5stars) AS nonsession_rating3_5stars
						   FROM (SELECT courses.*, GROUP_CONCAT(IFNULL(omnibus.lecturer_id, courses.lecturer_id) ORDER BY IFNULL(omnibus.lecturer_id, courses.lecturer_id) SEPARATOR '/') AS all_lecturer_ids
					        	   FROM {$wpdb->prefix}selva_courses AS courses
							  LEFT JOIN	{$wpdb->prefix}selva_omnibus_course_lecturers AS omnibus
								     ON	courses.course_id=omnibus.course_id
								  WHERE	courses.course_id>2
							   GROUP BY	courses.course_id) AS sub2
				       GROUP BY all_lecturer_ids, title, semester, day_and_period, campus) AS sub
			   GROUP BY	all_lecturer_ids, title, target_department, semester, campus) AS from_
			SET to_.target_department=from_.target_department,
				to_.day_and_period=from_.day_and_period,
				to_.post_views=from_.post_views,
				to_.average_rating_cache=IFNULL(
					((from_.nonsession_rating1_1stars+from_.nonsession_rating2_1stars+from_.nonsession_rating3_1stars)
					+(from_.nonsession_rating1_2stars+from_.nonsession_rating2_2stars+from_.nonsession_rating3_2stars)*2
					+(from_.nonsession_rating1_3stars+from_.nonsession_rating2_3stars+from_.nonsession_rating3_3stars)*3
					+(from_.nonsession_rating1_4stars+from_.nonsession_rating2_4stars+from_.nonsession_rating3_4stars)*4
					+(from_.nonsession_rating1_5stars+from_.nonsession_rating2_5stars+from_.nonsession_rating3_5stars)*5)
					/NULLIF(from_.nonsession_rating1_1stars+from_.nonsession_rating2_1stars+from_.nonsession_rating3_1stars
					+from_.nonsession_rating1_2stars+from_.nonsession_rating2_2stars+from_.nonsession_rating3_2stars
					+from_.nonsession_rating1_3stars+from_.nonsession_rating2_3stars+from_.nonsession_rating3_3stars
					+from_.nonsession_rating1_4stars+from_.nonsession_rating2_4stars+from_.nonsession_rating3_4stars
					+from_.nonsession_rating1_5stars+from_.nonsession_rating2_5stars+from_.nonsession_rating3_5stars, 0), 0.0),
				to_.nonsession_rating1_1stars=from_.nonsession_rating1_1stars,
				to_.nonsession_rating1_2stars=from_.nonsession_rating1_2stars,
				to_.nonsession_rating1_3stars=from_.nonsession_rating1_3stars,
				to_.nonsession_rating1_4stars=from_.nonsession_rating1_4stars,
				to_.nonsession_rating1_5stars=from_.nonsession_rating1_5stars,
				to_.nonsession_rating2_1stars=from_.nonsession_rating2_1stars,
				to_.nonsession_rating2_2stars=from_.nonsession_rating2_2stars,
				to_.nonsession_rating2_3stars=from_.nonsession_rating2_3stars,
				to_.nonsession_rating2_4stars=from_.nonsession_rating2_4stars,
				to_.nonsession_rating2_5stars=from_.nonsession_rating2_5stars,
				to_.nonsession_rating3_1stars=from_.nonsession_rating3_1stars,
				to_.nonsession_rating3_2stars=from_.nonsession_rating3_2stars,
				to_.nonsession_rating3_3stars=from_.nonsession_rating3_3stars,
				to_.nonsession_rating3_4stars=from_.nonsession_rating3_4stars,
				to_.nonsession_rating3_5stars=from_.nonsession_rating3_5stars,
				to_.updated_at=CURRENT_TIMESTAMP
		  WHERE	to_.course_id=from_.course_id"
    );

	if($updated_count === false)
		return ['error_message' => mysql_last_error('授業テーブルにおいて、授業データのまとめに失敗しました。')];

	$deleted_count = delete_courses($delete_by_time, 'by');

	if(is_string($deleted_count))
		return [
			'error_message' => '新しい授業データの挿入に成功しましたが、古い授業の削除に失敗しました。(' . $deleted_count . ')',
			'updated_count' => $updated_count
		];

	return [
		'updated_count' => $updated_count,
		'deleted_count' => $deleted_count
	];
}

// 設置課程を分解する(organize_coursesの逆)
// オムニバス授業以外を更新する
// ユーザーの行動テーブルのまとめは面倒くさいのでしない
function break_courses() : array {
    global $wpdb;

    $delete_by_time = $wpdb->get_var("SELECT CURRENT_TIMESTAMP");

	// 授業データ取得
	$courses = $wpdb->get_results(
        "SELECT *
		   FROM {$wpdb->prefix}selva_courses
		  WHERE	course_id>2",
		'ARRAY_A'
    );

	if(empty($courses))
		return ['error_message' => '授業データがありません。'];

	$inserted_count = 0;
	$updated_count = 0;
	foreach($courses as $course) {
		$day_and_periods = explode('、', $course['day_and_period']);
		$is_original_left = true;
		foreach($day_and_periods as $day_and_period) {
			$target_departments = explode('/', $course['target_department']);
			foreach($target_departments as $target_department) {
				// ON DUPLICATE KEY UPDATEより毎回先に探した方が早い
				// 普通の授業とオムニバス授業を一緒にして探すこともできるが、あまりに時間が掛かったので分ける
				$course_id = 0;
				if($course['lecturer_id'] == 2) {
					// オムニバス授業は教員データ全てを比較する
					$course_id = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT course_id
							   FROM	(SELECT courses.*, IFNULL(GROUP_CONCAT(omnibus.lecturer_id ORDER BY omnibus.lecturer_id SEPARATOR '/'), '') AS all_lecturer_ids
						        	   FROM {$wpdb->prefix}selva_courses AS courses
								  LEFT JOIN	{$wpdb->prefix}selva_omnibus_course_lecturers AS omnibus
									     ON	courses.course_id=omnibus.course_id
									  WHERE	courses.lecturer_id=2
								   GROUP BY	courses.course_id) AS sub
							  WHERE	all_lecturer_ids =  (SELECT	IFNULL(GROUP_CONCAT(omnibus.lecturer_id ORDER BY omnibus.lecturer_id SEPARATOR '/'), '') AS all_lecturer_ids
														   FROM {$wpdb->prefix}selva_courses AS courses
													  LEFT JOIN	{$wpdb->prefix}selva_omnibus_course_lecturers AS omnibus
															 ON	courses.course_id=omnibus.course_id
														  WHERE	courses.course_id=%d)
									AND title=%s
									AND target_department=%s
									AND semester=%s
									AND day_and_period=%s
									AND campus=%s",
							$course['course_id'],
							$course['title'],
							$target_department,
							$course['semester'],
							$day_and_period,
							$course['campus']
						)
					);
				} else {
					// 普通の授業
					$course_id = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT course_id
							   FROM	{$wpdb->prefix}selva_courses
							  WHERE	lecturer_id=%d
									AND title=%s
									AND target_department=%s
									AND semester=%s
									AND day_and_period=%s
									AND campus=%s",
							$course['lecturer_id'],
							$course['title'],
							$target_department,
							$course['semester'],
							$day_and_period,
							$course['campus']
						)
					);
				}

				// もとから存在しているか、オリジナルのデータがまだ残っている場合はそれを更新
				// updated_at(とtarget_departmentとday_and_period)
				if(!empty($course_id) || $is_original_left) {
					if(empty($course_id)) {
						$course_id = $course['course_id'];
						$is_original_left = false;
					}

					$has_succeeded = $wpdb->query(
						$wpdb->prepare(
							"UPDATE	{$wpdb->prefix}selva_courses
								SET	target_department=%s,
									day_and_period=%s,
									updated_at=CURRENT_TIMESTAMP
							  WHERE	course_id=%d",
							$target_department,
							$day_and_period,
							$course_id
						)
					);

					if($has_succeeded === false)
						return ['error_message' => mysql_last_error('授業データの更新中にエラーが発生しました。')];

					$updated_count++;
					continue;
				}

				// もとから存在していないし、オリジナルのデータも残っていない
				// 新しく挿入
				$has_succeeded = $wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}selva_courses
								(lecturer_id, title, title_for_search, target_department, semester, day_and_period, campus, has_post)
						 VALUES (%d,          %s,    %s,               %s,                %s,       %s,             %s,     %d)",
						$course['lecturer_id'],
						$course['title'],
						$course['title_for_search'],
						$target_department,
						$course['semester'],
						$day_and_period,
						$course['campus'],
						$course['has_post']
					)
				);
	
				if($has_succeeded === false)
					return ['error_message' => mysql_last_error('新しい授業データの挿入中にエラーが発生しました。')];
	
				$inserted_count++;

				// オムニバス授業でない
				if($course['lecturer_id'] != 2) continue;

				// オムニバス授業の教員データの追加
				$course_id = $wpdb->insert_id;
				$has_succeeded = $wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}selva_omnibus_course_lecturers
								(course_id, lecturer_id)
						 SELECT	%d,         lecturer_id
						   FROM	{$wpdb->prefix}selva_omnibus_course_lecturers AS from_
						  WHERE	from_.course_id=%d",
						$course_id,
						$course['course_id']
					)
				);

				if($has_succeeded === false)
					return ['error_message' => mysql_last_error('新しいオムニバス授業データの挿入中にエラーが発生しました。')];
			}
		}
	}

	// 更新しなかった授業の削除
	$deleted_count = delete_courses($delete_by_time, 'by', true);

	if(is_string($deleted_count))
		return [
			'error_message' => '新しい授業データの挿入に成功しましたが、古い授業の削除に失敗しました。(' . $deleted_count . ')',
			'inserted_count' => $inserted_count,
			'updated_count' => $updated_count
		];

	return [
		'inserted_count' => $inserted_count,
		'updated_count' => $updated_count,
		'deleted_count' => $deleted_count
	];
}

// インデックスがあるかを調べるクエリを実行する関数
function show_index($col_name) : ?array {
	global $wpdb;

    return $wpdb->get_results(
		$wpdb->prepare(
	        "SHOW INDEX FROM {$wpdb->prefix}selva_courses
    	    WHERE Column_name=%s
    	          AND Non_unique=1",
			$col_name
		),
		'ARRAY_A'
    );
}

// 引数としてColumn_nameを渡し、一度でもfalseが帰ってきたら処理を中断してfalseを返す処理
function index_loop($func) : bool {
	$col_names = ['title', 'target_department', 'semester', 'day_and_period', 'campus'];

	foreach($col_names as $col_name) {
		$bool = $func($col_name);

		if(!$bool) return false;
	}

	return true;
}

// インデックスがあるかを調べる関数
function have_indexes() : bool {
	return index_loop(function($col_name) {
		return !empty(show_index($col_name));
	});
}

function add_indexes() : bool {
	return index_loop(function($col_name) {
		global $wpdb;

		$has_succeeded = $wpdb->query(
			"ALTER TABLE {$wpdb->prefix}selva_courses
					ADD INDEX ($col_name)"
		);

		return $has_succeeded !== false;
	});
}

function drop_indexes() : bool {
	return index_loop(function($col_name) {
		global $wpdb;

		$results = show_index($col_name);
		if(!isset($results)) return false;
	
		foreach($results as $row) {
			$has_succeeded &= $wpdb->query(
					"ALTER TABLE {$wpdb->prefix}selva_courses
						DROP INDEX {$row['Key_name']}"
			);

			if($has_succeeded === false) return false;
		}

		return true;
	});
}

function delete_lecturers($time, $operator='by') {
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

	$has_succeeded = $wpdb->query(
        $wpdb->prepare(
            "DELETE actions
               FROM {$wpdb->prefix}selva_user_actions AS actions
               JOIN {$wpdb->prefix}selva_lecturers AS lecturers
                 ON actions.previous_lecturer_id=lecturers.lecturer_id
              WHERE lecturers.updated_at$operator%s AND lecturers.lecturer_id>2",
			$time
		)
    );

	if($has_succeeded === false)
		return mysql_last_error('ユーザーの行動テーブルの削除に失敗しました。');

	$has_succeeded = $wpdb->query(
        $wpdb->prepare(
            "DELETE actions
               FROM {$wpdb->prefix}selva_user_actions AS actions
               JOIN {$wpdb->prefix}selva_lecturers AS lecturers
                 ON actions.next_lecturer_id=lecturers.lecturer_id
              WHERE lecturers.updated_at$operator%s AND lecturers.lecturer_id>2",
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
			   JOIN {$wpdb->prefix}selva_lecturers AS lecturers
			     ON courses.lecturer_id=lecturers.lecturer_id
              WHERE lecturers.updated_at$operator%s AND lecturers.lecturer_id>2",
			$time
		)
    );

	if($has_succeeded === false)
		return mysql_last_error('授業評価テーブルの削除に失敗しました。');

	$has_succeeded = $wpdb->query(
		$wpdb->prepare(
			"DELETE omnibus
			   FROM {$wpdb->prefix}selva_omnibus_course_lecturers AS omnibus
			   JOIN {$wpdb->prefix}selva_lecturers AS lecturers
				 ON omnibus.lecturer_id=lecturers.lecturer_id
			  WHERE lecturers.updated_at$operator%s",
			$time
		)
	);

	if($has_succeeded === false)
		return mysql_last_error('オムニバス授業テーブルの削除に失敗しました。');

	$has_succeeded = $wpdb->query(
		$wpdb->prepare(
			"DELETE ratings
			   FROM {$wpdb->prefix}selva_omnibus_course_ratings AS ratings
			   JOIN {$wpdb->prefix}selva_lecturers AS lecturers
				 ON ratings.lecturer_id=lecturers.lecturer_id
			  WHERE lecturers.updated_at$operator%s",
			$time
		)
	);

	if($has_succeeded === false)
		return mysql_last_error('オムニバス授業評価テーブルの削除に失敗しました。');

	$deleted_courses = $wpdb->query(
        $wpdb->prepare(
            "DELETE courses
			   FROM	{$wpdb->prefix}selva_courses AS courses
			   JOIN	{$wpdb->prefix}selva_lecturers AS lecturers
			     ON	courses.lecturer_id=lecturers.lecturer_id
              WHERE lecturers.updated_at$operator%s
			  		AND courses.course_id>2 AND lecturers.lecturer_id>2",
			$time
		)
    );

	if($deleted_courses === false)
		return mysql_last_error('授業テーブルの削除に失敗しました。');

	$deleted_lecturers = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}selva_lecturers
              WHERE updated_at$operator%s AND lecturer_id>2",
			$time
		)
    );

	if($deleted_lecturers === false)
		return mysql_last_error($deleted_courses + 'の授業の削除に成功しましたが、教員テーブルの削除に失敗しました。');

	return ['lecturer' => $deleted_lecturers, 'course' => $deleted_courses];
}

<?php

//define('SELVA_SEARCH_NONTARGET_REGEXP', '/[^ぁ-んァ-ヶｱ-ﾝーa-zA-Z0-9一-龠ａ-ｚＡ-Ｚ０-９\-\r]++/u');
define('SELVA_SEARCH_NONTARGET_REGEXP', '/[\s[:punct:]！-／：-＠［-｀｛-～、-〜”’・]++/u');

// 検索する関数
// ORDER BYは番号でカラムを選べるようにする
// 検索結果がない場合空の配列が返る
// なお、この関数のみcourse_idはsearch_course_idで呼び出す
function search(string $query='', int $order_by_no=5, bool $is_asc=false) : array {
	global $wpdb;

	// 引数が不正な値である
	if($order_by_no < 1 || $order_by_no > 15) return [];

	$query = mb_substr($query, 0, 100);

	// 基本SQL(なお、使わないデータの行はコメントアウトすることで検索速度が向上する)
	// MINと書かれた行は実は何でもいい(ANY_VALUE())
	$select =   "SELECT	lecturers.lecturer_id, lecturers.lecturer_name, lecturers.faculty
						, CASE WHEN courses.has_post     THEN courses.course_id            ELSE 0                              END  AS search_course_id
						, MIN(CASE WHEN courses.has_post THEN courses.post_views           ELSE lecturers.post_views           END) AS post_views
						, MIN(CASE WHEN courses.has_post THEN courses.average_rating_cache ELSE lecturers.average_rating_cache END) AS average_rating_cache
						, MIN(courses.title) AS title
						, GROUP_CONCAT(DISTINCT courses.target_department SEPARATOR '/') AS target_department
						, GROUP_CONCAT(DISTINCT courses.semester          SEPARATOR '/') AS semester
						, GROUP_CONCAT(DISTINCT courses.day_and_period    SEPARATOR '/') AS day_and_period
						, GROUP_CONCAT(DISTINCT courses.campus            SEPARATOR '/') AS campus";
	$from =     "  FROM	{$wpdb->prefix}selva_lecturers AS lecturers
			  LEFT JOIN	{$wpdb->prefix}selva_courses AS courses
				     ON	lecturers.lecturer_id=courses.lecturer_id";
	$where =	" WHERE	(lecturers.has_post OR courses.has_post)";
	$group_by = " GROUP	BY lecturer_id, search_course_id";
	$order_by = " ORDER BY $order_by_no DESC";
	if($is_asc) $order_by = " ORDER BY $order_by_no ASC";

	// クエリが空なら教員ページ全てと、post_idがあるページ全てを返す
	if(empty($query)) {
//		$search_results = $wpdb->get_results($select . $from . $where . $group_by . $order_by, 'ARRAY_A');
//		return $search_results ?? [];
		return [];
	}

	// 記号などでキーワード分割
	$keywords = preg_split(SELVA_SEARCH_NONTARGET_REGEXP, $query, 6, PREG_SPLIT_NO_EMPTY);

	// 記号を取り除いた結果何も残らなかったら検索結果無し
	if(empty($keywords)) return [];

	// キーワードは5個まで
	if(isset($keywords[5])) unset($keywords[5]);

	// SQL生成
	foreach($keywords as $keyword) {
		$like = '%' . $wpdb->esc_like($keyword) . '%';
		$where .= $wpdb->prepare(
			"       AND	(courses.title_for_search LIKE %s
						OR lecturers.lecturer_name_for_search LIKE %s)",
			$like, $like
		);
	}

	// クエリ実行
	$search_results = $wpdb->get_results($select . $from . $where . $group_by . $order_by, 'ARRAY_A');
	return $search_results ?? [];
}

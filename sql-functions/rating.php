<?php

define('SELVA_USE_TAG_RATINGS_OPTION_NAME', 'selva_use_tag_ratings');

function get_use_star_rating_option_name($rating_no) {
    return "selva_use_rating{$rating_no}";
}

function should_use_star_rating($rating_no) {
	static $should_uses = [];

	if(!isset($should_uses[$rating_no])) {
		$should_uses[$rating_no] = get_option(get_use_star_rating_option_name($rating_no)) === 'yes' ? true : false;
	}

    return $should_uses[$rating_no];
}

function get_star_rating_label_option_name($rating_no) {
    return "selva_rating{$rating_no}_label";
}

function get_star_rating_label($rating_no) {
	static $labels = [];

	if(!isset($labels[$rating_no])) {
		$labels[$rating_no] = get_option(get_star_rating_label_option_name($rating_no), '');
	}

    return $labels[$rating_no];
}

function count_star_ratings() {
	static $count = NULL;

	if(!isset($count)) {
		$count = 0;
		for($rating_no = 1; $rating_no <= 3; $rating_no++) {
			if(should_use_star_rating($rating_no))
				$count++;
		}
	}

    return $count;
}

function should_use_tag_ratings() {
	static $retval = NULL;

	if(!isset($retval)) {
		$retval = get_option(SELVA_USE_TAG_RATINGS_OPTION_NAME) === 'yes' ? true : false;
	}

    return $retval;
}

// $auto_create:無いタグを自動作成する($to_id=trueのみ)
function convert_rating_tags($from=NULL, bool $is_user=false, bool $to_id=false, bool $auto_create=false) : array {
	global $wpdb;
	static $default_ids_tag = NULL;
	static $default_tags_id = [];
	static $normal_ids_tag = [];
	static $normal_tags_id = [];

	// メモリ上にdefualtとnormalに分けてストックしておく
	if(!isset($default_ids_tag)) {
		$all_ids_and_tags = $wpdb->get_results(
			"SELECT	*
			   FROM	{$wpdb->prefix}selva_rating_tags",
			'ARRAY_A'
		);

		if(!isset($all_ids_and_tags)) return [];

		// 残りの変数の初期化
		$default_ids_tag = [];
		foreach($all_ids_and_tags as $id_and_tag) {
			$id = $id_and_tag['tag_id'];
			$tag = $id_and_tag['rating_tag'];

			if(is_default_tag($tag)) {
				$default_ids_tag[$id] = $tag;
				$default_tags_id[$tag] = $id;
			} else {
				$normal_ids_tag[$id] = $tag;
				$normal_tags_id[$tag] = $id;
			}
		}
	}

	// 無いタグの自動作成
	if($auto_create) {
		foreach($from as $key => $value) {
			// タグの取得
			$tag = '';
			if($is_user) {
				$tag = $value;
			} else {
				$tag = $key;
			}

			// タグがすでにあるか探す
			$is_default = is_default_tag($tag);
			if($is_default) {
				if(isset($default_tags_id[$tag])) continue;
			} else {
				if(isset($normal_tags_id[$tag])) continue;
			}

			// 無かった、作成
			$has_successed = $wpdb->query(
				$wpdb->prepare(
					"INSERT	INTO {$wpdb->prefix}selva_rating_tags
							(rating_tag)
					 VALUES	(%s)",
					$tag
				)
			);

			if($has_successed === false) continue;

			$id = $wpdb->insert_id;
			if($is_default) {
				$default_ids_tag[$id] = $tag;
				$default_tags_id[$tag] = $id;
			} else {
				$normal_ids_tag[$id] = $tag;
				$normal_tags_id[$tag] = $id;
			}
		}
	}

	if(empty($default_ids_tag) && empty($normal_ids_tag)) return [];
	if(!is_array($from)) $from = [];

	// idの型は出力時のみ型を気にする

	// ユーザーの場合を先に処理
	// あるものだけ追加すればよい
	if($is_user) {
		$rating_tags = [];

		foreach($from as $value) {
			if($to_id) {
				if(is_default_tag($value)) {
					if(isset($default_tags_id[$value]))
						$rating_tags[] = (int)$default_tags_id[$value];
				} else {
					if(isset($normal_tags_id[$value]))
						$rating_tags[] = (int)$normal_tags_id[$value];
				}
			} else {
				if(isset($default_ids_tag[$value])) {
					$rating_tags[] = $default_ids_tag[$value];
				} else if(isset($normal_ids_tag[$value])) {
					$rating_tags[] = $normal_ids_tag[$value];
				}
			}
		}

		return $rating_tags;
	}

	// ユーザーでない、かつidへの変換を処理
	// あるものだけ追加すればよい
	if($to_id) {
		$rating_tags = [];

		foreach($from as $tag => $value) {
			$id = 0;
			
			if(is_default_tag($tag)) {
				if(!isset($default_tags_id[$tag])) continue;
				$id = (int)$default_tags_id[$tag];
			} else {
				if(!isset($normal_tags_id[$tag])) continue;
				$id = (int)$normal_tags_id[$tag];
			}

			$rating_tags[$id] = $value;
		}

		return $rating_tags;
	}

	// 全体の評価でかつtagへの変換

	// デフォルトタグは必ず追加
	$rating_tags = [];
	foreach($default_ids_tag as $id => $tag) {
		// 変換した後、$fromから削除
		if(isset($from[$id])) {
			$rating_tags[$tag] = $from[$id];
			unset($from[$id]);
		} else {
			$rating_tags[$tag] = 0;
		}
	}

	// ノーマルタグの変換
	foreach($from as $id => $value) {
		if(isset($normal_ids_tag[$id]))
			$rating_tags[$normal_ids_tag[$id]] = $value;
	}

	return $rating_tags;
}

function is_default_tag($rating_tag) {
	if(preg_match('/^#/', $rating_tag)) return false;

	return true;
}

// ユーザーが過去につけた評価を返す関数
function fetch_user_ratings(int $course_id, ?int $lecturer_id=NULL) : array {
	global $wpdb;

	$empty_ratings = ['rating1' => 0, 'rating2' => 0, 'rating3' => 0, 'rating_tags' => []];

	if(!isset($lecturer_id) || $lecturer_id <= 2) $lecturer_id = NULL;

	// 引数が不正な値である
	if($course_id <= 2) return $empty_ratings;

	// 評価取得
	$ratings = [];
	if(isset($lecturer_id)) {
		// オムニバス授業の教員の星
		$ratings = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT	rating1, rating2, rating3, rating_tags
				   FROM	{$wpdb->prefix}selva_omnibus_course_ratings
				  WHERE	session_id_int=%d
						AND	course_id=%d
						AND lecturer_id=%d",
				get_session_id(),
				$course_id,
				$lecturer_id
			),
			'ARRAY_A'
		);
	} else {
		// 普通の授業の星
		$ratings = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT	rating1, rating2, rating3, rating_tags
				   FROM	{$wpdb->prefix}selva_course_ratings
				  WHERE	session_id_int=%d
						AND	course_id=%d",
				get_session_id(),
				$course_id
			),
			'ARRAY_A'
		);
	}

	if(empty($ratings)) return $empty_ratings;

	$ratings['rating_tags'] = convert_rating_tags(unserialize($ratings['rating_tags']), true);

	return $ratings;
}

// 星の評価をする関数
function star_rate(int $course_id, int $rating_no, ?int $stars, ?int $lecturer_id=NULL) : bool {
	global $wpdb;

	if(!isset($stars)) $stars = 0;
	if(!isset($lecturer_id) || $lecturer_id <= 2) $lecturer_id = NULL;

	// 引数が不正な値である
	if($course_id <= 2) return false;
	if($rating_no < 1 || $rating_no > 3) return false;
	if($stars < 0 || $stars > 5) return false;

	// 星の設定
	$has_successed = false;
	if(isset($lecturer_id)) {
		// オムニバス授業の教員の星
		$has_successed = $wpdb->query(
			$wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}selva_omnibus_course_ratings (session_id_int, course_id, lecturer_id, rating$rating_no)
			 VALUES	(%d, %d, %d, %d)
			   ON DUPLICATE KEY UPDATE rating$rating_no=VALUES(rating$rating_no)",
				get_session_id(),
				$course_id,
				$lecturer_id,
				$stars
			)
		);
	} else {
		// 普通の授業の星
		$has_successed = $wpdb->query(
			$wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}selva_course_ratings (session_id_int, course_id, rating$rating_no)
			 VALUES	(%d, %d, %d)
			   ON DUPLICATE KEY UPDATE rating$rating_no=VALUES(rating$rating_no)",
				get_session_id(),
				$course_id,
				$stars
			)
		);
	}

	return $has_successed !== false;
}

// タグの評価をする関数
function tag_rate(int $course_id, ?array $rating_tags, ?int $lecturer_id=NULL) : bool {
	global $wpdb;

	if(!isset($lecturer_id) || $lecturer_id <= 2) $lecturer_id = NULL;
	$rating_tags = convert_rating_tags($rating_tags, true, true);

	// 引数が不正な値である
	if($course_id <= 2) return false;

	if(empty($rating_tags)) {
		$serialized_rating_tags = '';
	} else {
		foreach($rating_tags as $rating_tag) {
			if(empty($rating_tag) || mb_strlen($rating_tag) > 20) return false;
			if(preg_match(SELVA_SEARCH_NONTARGET_REGEXP, $rating_tag)) return false;
		}

		$serialized_rating_tags = serialize($rating_tags);
	}

	// ユーザーのタグの設定
	$has_successed = false;
	if(isset($lecturer_id)) {
		// オムニバス授業の教員
		$has_successed = $wpdb->query(
			$wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}selva_omnibus_course_ratings (session_id_int, course_id, lecturer_id, rating_tags)
			 VALUES	(%d, %d, %d, %s)
			   ON DUPLICATE KEY UPDATE rating_tags=VALUES(rating_tags)",
				get_session_id(),
				$course_id,
				$lecturer_id,
				$serialized_rating_tags
			)
		);
	} else {
		// 普通の授業
		$has_successed = $wpdb->query(
			$wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}selva_course_ratings (session_id_int, course_id, rating_tags)
			 VALUES	(%d, %d, %s)
			   ON DUPLICATE KEY UPDATE rating_tags=VALUES(rating_tags)",
				get_session_id(),
				$course_id,
				$serialized_rating_tags
			)
		);
	}

	return $has_successed !== false;
}

// それぞれの評価の合計をnonsessionを含めて計算して返す関数
// $course_idがなく$lecturer_idだけがあるなら、その教員の合計の星の数だけを返す
function fetch_ratings(array $nonsession_data, ?int $course_id, ?int $lecturer_id=NULL) : array {
	global $wpdb;

	$ratings = [
		'rating1' => array_fill(1, 5, 0),
		'rating2' => array_fill(1, 5, 0),
		'rating3' => array_fill(1, 5, 0),
		'rating_tags' => convert_rating_tags()
	];

	if(!isset($course_id) || $course_id <= 2) $course_id = NULL;
	if(!isset($lecturer_id) || $lecturer_id <= 2) $lecturer_id = NULL;

	// 引数が不正な値である
	if(empty($nonsession_data)) return $ratings;
	if(!isset($course_id) && !isset($lecturer_id)) return $ratings;

	// SQL文準備
	$from = "";
	$where = "";
	if(!isset($lecturer_id)) {
		// 授業に対する星の数を返す
		$from = "  FROM	{$wpdb->prefix}selva_course_ratings";
		$where = $wpdb->prepare(" WHERE course_id=%d", $course_id);
	} else if(!isset($course_id)) {
		// 教員の合計の数を返す
		$from = "  FROM	{$wpdb->prefix}selva_course_ratings AS ratings
				   JOIN	{$wpdb->prefix}selva_courses AS courses
					 ON	ratings.course_id=courses.course_id";
		$where = $wpdb->prepare(" WHERE courses.lecturer_id=%d", $lecturer_id);
	} else {
		// オムニバス授業の教員に対する星の数を返す
		$from = "  FROM	{$wpdb->prefix}selva_omnibus_course_ratings";
		$where = $wpdb->prepare(" WHERE course_id=%d AND lecturer_id=%d", $course_id, $lecturer_id);
	}

	// 星の評価
	for($rating_no = 1; $rating_no <= 3; $rating_no++) {
		// SQL文準備
		$select =       "SELECT	rating$rating_no, COUNT(*) AS count";
		$after_where =    " AND	rating$rating_no>0
					   GROUP BY	rating$rating_no
					   ORDER BY	rating$rating_no";
	
		// セッション内のデータを取得
		$counts_partial = $wpdb->get_results($select . $from . $where . $after_where, 'ARRAY_A') ?? [];

		// 1から始まるインデックス配列に変換。また、評価がない星数は配列に含まれていないので補う。
		$cp = 0;
		for($stars = 1; $stars <= 5; $stars++) {
			$nonsession_count = $nonsession_data["nonsession_rating{$rating_no}_{$stars}stars"] ?? 0;

			if(isset($counts_partial[$cp]) && $counts_partial[$cp]["rating$rating_no"] == $stars) {
				$ratings["rating$rating_no"][$stars] = $counts_partial[$cp]['count'] + $nonsession_count;
				$cp++;
			} else {
				$ratings["rating$rating_no"][$stars] = $nonsession_count;
			}
		}
	}

	// 教員の合計の星の数さえ返せばよい
	if(!isset($course_id)) return $ratings;

	// タグの評価
	$ratings['rating_tags'] = unserialize($nonsession_data['nonsession_rating_tags'] ?? '');
	if(!is_array($ratings['rating_tags'])) $ratings['rating_tags'] = [];

	// SQL文準備
	$select =   "SELECT	rating_tags";

	// セッション内のデータを取得
	$all_insession_rating_tags = $wpdb->get_col($select . $from . $where) ?? [];

	foreach($all_insession_rating_tags as $insession_rating_tags) {
		$insession_rating_tags = unserialize($insession_rating_tags);
		if(!is_array($insession_rating_tags)) continue;

		foreach($insession_rating_tags as $tag) {
			if(isset($ratings['rating_tags'][$tag])) {
				$ratings['rating_tags'][$tag]++;
			} else {
				$ratings['rating_tags'][$tag] = 1;
			}
		}
	}

	$ratings['rating_tags'] = convert_rating_tags($ratings['rating_tags']);

	return $ratings;
}

// 星の評価のキャッシュを更新する関数
function update_cache(bool $is_lecturer, int $id) : bool {
	global $wpdb;

	// ノンセッションデータの読み取り
	$nonsession_data = [];
	if($is_lecturer) {
		$nonsession_data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT	SUM(nonsession_rating1_1stars) AS nonsession_rating1_1stars,
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
				   FROM	{$wpdb->prefix}selva_courses
				  WHERE	lecturer_id=%d",
				$id
			),
			'ARRAY_A'
		);
	} else {
		$nonsession_data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT	nonsession_rating1_1stars, nonsession_rating1_2stars,
						nonsession_rating1_3stars, nonsession_rating1_4stars,
						nonsession_rating1_5stars, nonsession_rating2_1stars,
						nonsession_rating2_2stars, nonsession_rating2_3stars,
						nonsession_rating2_4stars, nonsession_rating2_5stars,
						nonsession_rating3_1stars, nonsession_rating3_2stars,
						nonsession_rating3_3stars, nonsession_rating3_4stars,
						nonsession_rating3_5stars
				   FROM	{$wpdb->prefix}selva_courses
				  WHERE	course_id=%d",
				$id
			),
			'ARRAY_A'
		);
	}

	if(empty($nonsession_data)) return false;

	// それぞれの星の数を算出する
	$each_stars_counts = [];
	if($is_lecturer) {
		$each_stars_counts = fetch_ratings($nonsession_data, NULL, $id);
	} else {
		$each_stars_counts = fetch_ratings($nonsession_data, $id, NULL);
	}

	// 平均の星の計算
	$count = $average = 0;
	for($rating_no = 1; $rating_no <=3; $rating_no++) {
		for($stars = 1; $stars <= 5; $stars++) {
			$count += $each_stars_counts["rating$rating_no"][$stars];
			$average += $each_stars_counts["rating$rating_no"][$stars] * $stars;
		}
	}
	if($count == 0) {
		$average = 0;
	} else {
		$average /= $count;
	}

	// キャッシュの更新
	$has_successed = NULL;
	if($is_lecturer) {
		$has_successed = $wpdb->query(
			$wpdb->prepare(
				"UPDATE	{$wpdb->prefix}selva_lecturers
					SET	average_rating_cache=%f
				  WHERE	lecturer_id=%d",
				  $average,
				  $id
			)
		);
	} else {
		$has_successed = $wpdb->query(
			$wpdb->prepare(
				"UPDATE	{$wpdb->prefix}selva_courses
					SET	average_rating_cache=%f
				  WHERE	course_id=%d",
				  $average,
				  $id
			)
		);
	}

	return $has_successed !== false;
}

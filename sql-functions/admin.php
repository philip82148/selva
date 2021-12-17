<?php

require_once(__DIR__ . '/admin-common.php');

function create_tables() : string {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // テーブル作成
    $charset_collate = $wpdb->get_charset_collate();

	dbDelta(
        "CREATE TABLE {$wpdb->prefix}selva_lecturers (
            lecturer_id                INT(10)    UNSIGNED NOT NULL AUTO_INCREMENT,
            lecturer_name              VARCHAR(50)         NOT NULL,
            lecturer_name_for_search   VARCHAR(200)        NOT NULL DEFAULT '',
            faculty                    VARCHAR(100)        NOT NULL DEFAULT '',
            class                      VARCHAR(100)        NOT NULL DEFAULT '',
            link_url                   VARCHAR(2048)       NOT NULL DEFAULT '',
            image_url                  VARCHAR(2048)       NOT NULL DEFAULT '',
            post_views                 INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            average_rating_cache       FLOAT      UNSIGNED NOT NULL DEFAULT 0.0,
            has_post                   BOOLEAN             NOT NULL DEFAULT TRUE,
            created_at                 DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at                 DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (lecturer_id),
            UNIQUE KEY   ui_lecturers_01 (lecturer_name)
        ) ENGINE=InnoDB $charset_collate;");
	$last_error = $wpdb->last_error;
	$last_query = $wpdb->last_query;
	if(!$wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}selva_lecturers'"))
		return mysql_last_error("{$wpdb->prefix}selva_lecturersの作成に失敗しました。", $last_error, $last_query);

    dbDelta(
        "CREATE TABLE {$wpdb->prefix}selva_courses (
            course_id                 INT(10)    UNSIGNED NOT NULL AUTO_INCREMENT,
            lecturer_id               INT(10)    UNSIGNED NOT NULL DEFAULT 1,
            title                     VARCHAR(100)        NOT NULL,
            title_for_search          VARCHAR(400)        NOT NULL DEFAULT '',
            target_department         VARCHAR(200)        NOT NULL DEFAULT '',
            semester                  VARCHAR(20)         NOT NULL DEFAULT '',
            day_and_period            VARCHAR(100)        NOT NULL DEFAULT '',
            campus                    VARCHAR(20)         NOT NULL DEFAULT '',
            post_views                INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            average_rating_cache      FLOAT      UNSIGNED NOT NULL DEFAULT 0.0,
            nonsession_rating1_1stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating1_2stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating1_3stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating1_4stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating1_5stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating2_1stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating2_2stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating2_3stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating2_4stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating2_5stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating3_1stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating3_2stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating3_3stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating3_4stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating3_5stars INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating_tags    VARBINARY(5000)     NOT NULL DEFAULT '',
            has_post                  BOOLEAN             NOT NULL DEFAULT FALSE,
            created_at                DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at                DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (course_id),
            UNIQUE  KEY  ui_courses_01  (lecturer_id, title, target_department, semester, day_and_period, campus),
            FOREIGN KEY  idx_courses_01 (lecturer_id) REFERENCES {$wpdb->prefix}selva_lecturers(lecturer_id) ON UPDATE CASCADE
        ) ENGINE=InnoDB $charset_collate;");
	$last_error = $wpdb->last_error;
	$last_query = $wpdb->last_query;
	if(!$wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}selva_courses'"))
		return mysql_last_error("{$wpdb->prefix}selva_coursesの作成に失敗しました。", $last_error, $last_query);

    dbDelta(
        "CREATE TABLE {$wpdb->prefix}selva_omnibus_course_lecturers (
            course_id                 INT(10) UNSIGNED NOT NULL,
            lecturer_id               INT(10) UNSIGNED NOT NULL,
            nonsession_rating1_1stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating1_2stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating1_3stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating1_4stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating1_5stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating2_1stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating2_2stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating2_3stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating2_4stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating2_5stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating3_1stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating3_2stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating3_3stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating3_4stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating3_5stars INT(10) UNSIGNED NOT NULL DEFAULT 0,
            nonsession_rating_tags    VARBINARY(5000)  NOT NULL DEFAULT '',
            created_at                DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at                DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY   ui_omnibus_01  (course_id, lecturer_id),
            FOREIGN KEY  idx_omnibus_01 (course_id)   REFERENCES {$wpdb->prefix}selva_courses(course_id)     ON UPDATE CASCADE,
            FOREIGN KEY  idx_omnibus_02 (lecturer_id) REFERENCES {$wpdb->prefix}selva_lecturers(lecturer_id) ON UPDATE CASCADE
        ) ENGINE=InnoDB $charset_collate;");
	$last_error = $wpdb->last_error;
	$last_query = $wpdb->last_query;
	if(!$wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}selva_omnibus_course_lecturers'"))
		return mysql_last_error("{$wpdb->prefix}selva_omnibus_course_lecturersの作成に失敗しました。", $last_error, $last_query);

    dbDelta(
        "CREATE TABLE {$wpdb->prefix}selva_sessions (
            session_id_int       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id_chr       VARCHAR(100)     NOT NULL,
            session_data         VARBINARY(5000)  NOT NULL DEFAULT '',
            created_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (session_id_int),
            UNIQUE  KEY  ui_sessions_01 (session_id_chr)
        ) ENGINE=InnoDB $charset_collate;");
	$last_error = $wpdb->last_error;
	$last_query = $wpdb->last_query;
	if(!$wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}selva_sessions'"))
		return mysql_last_error("{$wpdb->prefix}selva_sessionsの作成に失敗しました。", $last_error, $last_query);

    dbDelta(
        "CREATE TABLE {$wpdb->prefix}selva_course_ratings (
            session_id_int  INT(10)    UNSIGNED NOT NULL,
            course_id       INT(10)    UNSIGNED NOT NULL,
            rating1         TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            rating2         TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            rating3         TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            rating_tags     VARBINARY(5000)     NOT NULL DEFAULT '',
            UNIQUE KEY   ui_ratings_01  (session_id_int, course_id),
            FOREIGN KEY  idx_ratings_01 (session_id_int) REFERENCES {$wpdb->prefix}selva_sessions(session_id_int) ON UPDATE CASCADE,
            FOREIGN KEY  idx_ratings_02 (course_id)      REFERENCES {$wpdb->prefix}selva_courses(course_id)       ON UPDATE CASCADE
        ) ENGINE=InnoDB $charset_collate;");
	$last_error = $wpdb->last_error;
	$last_query = $wpdb->last_query;
	if(!$wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}selva_course_ratings'"))
		return mysql_last_error("{$wpdb->prefix}selva_course_ratingsの作成に失敗しました。", $last_error, $last_query);

    dbDelta(
        "CREATE TABLE {$wpdb->prefix}selva_omnibus_course_ratings (
            session_id_int  INT(10)    UNSIGNED NOT NULL,
            course_id       INT(10)    UNSIGNED NOT NULL,
            lecturer_id     INT(10)    UNSIGNED NOT NULL,
            rating1         TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            rating2         TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            rating3         TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            rating_tags     VARBINARY(5000)     NOT NULL DEFAULT '',
            UNIQUE KEY   ui_omnibus_ratings_01  (session_id_int, course_id, lecturer_id),
            FOREIGN KEY  idx_omnibus_ratings_01 (session_id_int) REFERENCES {$wpdb->prefix}selva_sessions(session_id_int) ON UPDATE CASCADE,
            FOREIGN KEY  idx_omnibus_ratings_02 (course_id)      REFERENCES {$wpdb->prefix}selva_courses(course_id)       ON UPDATE CASCADE,
            FOREIGN KEY  idx_omnibus_ratings_03 (lecturer_id)    REFERENCES {$wpdb->prefix}selva_lecturers(lecturer_id)   ON UPDATE CASCADE
        ) ENGINE=InnoDB $charset_collate;");
	$last_error = $wpdb->last_error;
	$last_query = $wpdb->last_query;
	if(!$wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}selva_omnibus_course_ratings'"))
		return mysql_last_error("{$wpdb->prefix}selva_omnibus_course_ratingsの作成に失敗しました。", $last_error, $last_query);

    dbDelta(
        "CREATE TABLE {$wpdb->prefix}selva_rating_tags (
            tag_id     INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            rating_tag VARCHAR(20)      NOT NULL,
            PRIMARY KEY  (tag_id),
            UNIQUE KEY   ui_rating_tags_01 (rating_tag)
        ) ENGINE=InnoDB $charset_collate;");
	$last_error = $wpdb->last_error;
	$last_query = $wpdb->last_query;
	if(!$wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}selva_rating_tags'"))
		return mysql_last_error("{$wpdb->prefix}selva_rating_tagsの作成に失敗しました。", $last_error, $last_query);

    dbDelta(
        "CREATE TABLE {$wpdb->prefix}selva_comments (
            session_id_int     INT(10)    UNSIGNED NOT NULL,
            comment_id         BIGINT(20) UNSIGNED NOT NULL,
            nonsession_ratings INT(10)    UNSIGNED NOT NULL DEFAULT 0,
            UNIQUE KEY   ui_comments_01  (comment_id),
            UNIQUE KEY   ui_comments_02  (session_id_int, comment_id),
            FOREIGN KEY  idx_comments_01 (session_id_int) REFERENCES {$wpdb->prefix}selva_sessions(session_id_int) ON UPDATE CASCADE,
            FOREIGN KEY  idx_comments_02 (comment_id)     REFERENCES $wpdb->comments(comment_ID)                   ON UPDATE CASCADE
        ) ENGINE=InnoDB $charset_collate;");
	$last_error = $wpdb->last_error;
	$last_query = $wpdb->last_query;
	if(!$wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}selva_comments'"))
		return mysql_last_error("{$wpdb->prefix}selva_commentsの作成に失敗しました。", $last_error, $last_query);

    dbDelta(
        "CREATE TABLE {$wpdb->prefix}selva_comment_ratings (
            session_id_int  INT(10)    UNSIGNED NOT NULL,
            comment_id      BIGINT(20) UNSIGNED NOT NULL,
            UNIQUE KEY   ui_comment_ratings_01 (session_id_int, comment_id),
            FOREIGN KEY  idx_comment_ratings_01 (session_id_int) REFERENCES {$wpdb->prefix}selva_sessions(session_id_int) ON UPDATE CASCADE,
            FOREIGN KEY  idx_comment_ratings_02 (comment_id)     REFERENCES $wpdb->comments(comment_ID)                   ON UPDATE CASCADE
        ) ENGINE=InnoDB $charset_collate;");
	$last_error = $wpdb->last_error;
	$last_query = $wpdb->last_query;
	if(!$wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}selva_comment_ratings'"))
		return mysql_last_error("{$wpdb->prefix}selva_comment_ratingsの作成に失敗しました。", $last_error, $last_query);

    dbDelta(
        "CREATE TABLE {$wpdb->prefix}selva_user_actions (
            previous_course_id   INT(10) UNSIGNED NOT NULL DEFAULT 1,
            previous_lecturer_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
            next_course_id       INT(10) UNSIGNED NOT NULL DEFAULT 1,
            next_lecturer_id     INT(10) UNSIGNED NOT NULL DEFAULT 1,
            count                INT(10) UNSIGNED NOT NULL DEFAULT 1,
            created_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (previous_course_id, previous_lecturer_id),
            UNIQUE KEY   ui_user_actions_01  (previous_course_id, previous_lecturer_id, next_course_id, next_lecturer_id),
            FOREIGN KEY  idx_user_actions_01 (previous_course_id)   REFERENCES {$wpdb->prefix}selva_courses(course_id)     ON UPDATE CASCADE,
            FOREIGN KEY  idx_user_actions_02 (previous_lecturer_id) REFERENCES {$wpdb->prefix}selva_lecturers(lecturer_id) ON UPDATE CASCADE,
            FOREIGN KEY  idx_user_actions_03 (next_course_id)       REFERENCES {$wpdb->prefix}selva_courses(course_id)     ON UPDATE CASCADE,
            FOREIGN KEY  iex_user_actions_04 (next_lecturer_id)     REFERENCES {$wpdb->prefix}selva_lecturers(lecturer_id) ON UPDATE CASCADE
        ) ENGINE=InnoDB $charset_collate;");
	$last_error = $wpdb->last_error;
	$last_query = $wpdb->last_query;
	if(!$wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}selva_user_actions'"))
		return mysql_last_error("{$wpdb->prefix}selva_user_actionsの作成に失敗しました。", $last_error, $last_query);

    // 初期値代入
    $has_succeeded = $wpdb->query(
        "INSERT INTO {$wpdb->prefix}selva_lecturers (lecturer_id, lecturer_name, lecturer_name_for_search, has_post)
         VALUES (1,'no_data', '', FALSE), (2, 'オムニバス', 'オムニバスomnibus', FALSE)
           ON DUPLICATE KEY UPDATE lecturer_id=VALUES(lecturer_id),
                                   lecturer_name=VALUES(lecturer_name),
                                   lecturer_name_for_search=VALUES(lecturer_name_for_search),
                                   updated_at=CURRENT_TIMESTAMP,
								   has_post=VALUES(has_post)"
    );
    if($has_succeeded === false) return mysql_last_error("テーブル{$wpdb->prefix}selva_lecturersへの初期値代入中にエラーが発生しました。");

    $has_succeeded = $wpdb->query(
        "INSERT INTO {$wpdb->prefix}selva_courses (course_id, title, has_post)
         VALUES (1, 'no_data', FALSE), (2, 'reserved', FALSE)
           ON DUPLICATE KEY UPDATE course_id=VALUES(course_id),
                                   title=VALUES(title),
								   has_post=VALUES(has_post),
                                   updated_at=CURRENT_TIMESTAMP"
    );
    if($has_succeeded === false) return mysql_last_error("テーブル{$wpdb->prefix}selva_coursesへの初期値代入中にエラーが発生しました。");

    $has_succeeded = $wpdb->query(
        "INSERT INTO {$wpdb->prefix}selva_sessions (session_id_int, session_id_chr, updated_at)
         VALUES (1, 'no_data', '9999-12-31 23:59:59')
           ON DUPLICATE KEY UPDATE session_id_int=VALUES(session_id_int),
                                   session_id_chr=VALUES(session_id_chr),
                                   updated_at=VALUES(updated_at)"
    );
    if($has_succeeded === false) return mysql_last_error("テーブル{$wpdb->prefix}selva_sessionsへの初期値代入中にエラーが発生しました。");

    return '';
}

function search_similar_lecturers($lecturer_name) : array {
	global $wpdb;

    // 記号などでキーワード分割
    $name_parts = preg_split(SELVA_SEARCH_NONTARGET_REGEXP, $lecturer_name, -1, PREG_SPLIT_NO_EMPTY);

    // 記号を取り除いた結果何も残らなかったら検索結果無し
    if(empty($name_parts)) return [];

	$where = '';

	if(preg_match('/\A\^|\$\z/u', $lecturer_name)) {
        // 順番そのままで前方または後方または部分一致検索
        $like = '';
		foreach($name_parts as $part) {
			if($like)
				$like .= '%';

            $like .= $wpdb->esc_like($part);
		}
        if(!preg_match('/\A\^/u', $lecturer_name))
            $like = '%' . $like;
        if(!preg_match('/\$\z/u', $lecturer_name))
            $like .= '%';

		$where = $wpdb->prepare(
			" WHERE	REPLACE(lecturer_name, ' ', '') LIKE %s",
			$like
		);
	} else {
		// あいまい検索
		foreach($name_parts as $part) {
			$like = '%' . $wpdb->esc_like($part) . '%';
			if(empty($where)) {
				$where = $wpdb->prepare(
					" WHERE	lecturer_name_for_search LIKE %s",
					$like
				);
			} else {
				$where .= $wpdb->prepare(
					"       AND	lecturer_name_for_search LIKE %s",
					$like
				);
			}
		}
	}

	// 検索
	$similar_lecturers = $wpdb->get_results(
		"SELECT	lecturer_id, lecturer_name
		   FROM	{$wpdb->prefix}selva_lecturers
		 $where",
		'ARRAY_A'
	);

    return $similar_lecturers ?? [];
}

function trim_lecturer_name($lecturer_name) {
	return preg_replace('/\A\^++|\$++\z/u', '', $lecturer_name);
}

// $auto_updateは教員名が重複しうる場合に既存のものを更新するかどうかというもの
// $all_updateは教員名が重複しうる場合にその教員名も更新する
function insert_lecturer_data(string $csv_file_name, bool $auto_update=false, bool $all_update=false) : array {
	global $wpdb;

    // スクリプトの最大動作時間を5分に設定
    set_time_limit(300);

	if(!$auto_update) $auto_update = false;
    
    $csv_object = new SplFileObject($csv_file_name, 'r');
    $header_template_japanese = ['教員名',        '教員名ルビ',                '所属学部', '階級',  'リンクURL', '画像URL'];
    $header                   = ['lecturer_name', 'lecturer_name_for_search', 'faculty',  'class', 'link_url',  'image_url'];
    $header_row_no = NULL;
    $first_row = []; // 見出しが見つからないときのエラー通知に使う。
    $invalid_columns_row_nos = '';

    // ファイル→配列化
    $is_win = strpos(PHP_OS, "WIN") === 0;
    foreach($csv_object as $i => $line) {
        $row_no = $i + 1;

		// BOM削除
        if($row_no === 1)
            $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);

		// 文字化け対策
        // OSがUnix系ならUTF8に変換して処理、
        // Windowsでは一旦SJIS-winに変換してから配列に変換後、UTF8に戻して格納する。
        // $csv_object->setFlags(SplFileObject::READ_CSV); // 文字化け対策のためにここでは行を配列に分割しない
        if($is_win) {
            // SJISに変換
            $line = mb_convert_encoding($line, 'SJIS-win', ['ASCII','JIS','UTF-8','EUC-JP','SJIS', 'SJIS-win']);
        } else {
            // UTF8に変換
            $line = mb_convert_encoding($line, 'UTF8', ['ASCII','JIS','UTF-8','EUC-JP','SJIS', 'SJIS-win']);
        }

        //　ここで行を配列に分割
        $csv_row = str_getcsv($line);

        // WindowsのみUTF8に変換
        if($is_win)
            mb_convert_variables('UTF8', 'SJIS-win', $csv_row);

        // 最終行等の処理
        if($csv_row === [null]) continue;
        if(empty($first_row)) $first_row = $csv_row;
        if(!isset($header_row_no)) {
			// 見出し行である
			if($csv_row === $header_template_japanese)
                $header_row_no = $row_no;

            continue;
        }

		// 行数が見出しと違ったら記録
        if(count($csv_row) !== count($header)) {
            if($invalid_columns_row_nos)
                $invalid_columns_row_nos .= '、';

            $invalid_columns_row_nos .= $row_no;
            continue;
        }

        if($invalid_columns_row_nos) continue;

        // 配列に移す
        $csv_rows[] = array_combine($header, $csv_row);
    }

    // 見出しが見つからないときエラーメッセージを返す
    if(!isset($header_row_no)) {
        $error_columns = [];
        for($i = 0; $i < count($header_template_japanese); $i++) {
            if($i >= count($first_row)) {
                $error_columns[] = ($i+1) . '列目';
                continue;
            }
            if($first_row[$i] !== $header_template_japanese[$i])
                $error_columns[] = "'" . $first_row[$i] . "'";
        }
        if($error_columns)
            return ['error_message' => '見出しが見つかりません。(参考：先頭行において' . implode('、', $error_columns) . 'は見出しとして認識されていません。(これに見た目上の問題がない場合は開発者ツールを使ってHTML上から見てください。))'];

        return ['error_message' => '見出しが見つかりません。(参考：先頭行の列数は' . count($first_row) . '行でした。)'];
    }

    // 列数が違う所があったらエラー
    if($invalid_columns_row_nos)
        return ['error_message' => $invalid_columns_row_nos . '行目の列数が' . count($header) . '列ではありません。'];

    // 一行ずつ挿入
    $invalid_lecturer_name_message = '';
    $duplicate_lecturer_names_similar_names = []; // $auto_update=falseのときの記録用
    $mysql_error_message = '';
    $inserted_count = 0;
    $updated_count = 0;
    foreach($csv_rows as $i => $csv_row) {
		$row_no = $header_row_no + $i + 1; // エラー記録用
        $lecturer_name = $csv_row['lecturer_name'];

        // lecturer_nameのスペースや記号を排除したもの
        $unsymbolized_lecturer_name = preg_replace(SELVA_SEARCH_NONTARGET_REGEXP, '', $lecturer_name);

        // 空ならエラーとして記録
        if(empty($unsymbolized_lecturer_name)) {
            if($invalid_lecturer_name_message)
                $invalid_lecturer_name_message .= '、';

            $invalid_lecturer_name_message .= $row_no;
            continue;
        }

		// 事前に無いか検索
		$similar_lecturers = search_similar_lecturers($lecturer_name);

        $trimmed_lecturer_name = trim_lecturer_name($lecturer_name);

		// 無いので挿入
		if(count($similar_lecturers) === 0) {
            $csv_row['lecturer_name'] = $trimmed_lecturer_name;
            $csv_row['lecturer_name_for_search'] = $unsymbolized_lecturer_name . preg_replace(SELVA_SEARCH_NONTARGET_REGEXP, '', $csv_row['lecturer_name_for_search']);

			// クエリ実行
			$has_succeeded = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}selva_lecturers
							(lecturer_name, lecturer_name_for_search, faculty, class, link_url, image_url)
					 VALUES (%s,            %s,                       %s,      %s,    %s,       %s)",
					...array_values($csv_row)
				)
			);

			// MySQLエラー、即return
			if($has_succeeded === false) {
				$mysql_error_message = mysql_last_error($row_no . '行目の挿入中にエラーが起こりました。');
				break;
			}

            $inserted_count++;
			continue;
		}

		// 一つかぶりがあって、同じ名前かauto_updateなら更新
		if(count($similar_lecturers) === 1
				&& ($similar_lecturers[0]['lecturer_name'] === $trimmed_lecturer_name || $auto_update)) {
			$csv_row['lecturer_id'] = $similar_lecturers[0]['lecturer_id'];

			if($all_update) {
                // all_updateなら名前は新しい名前を採用
                $csv_row['lecturer_name'] = $trimmed_lecturer_name;
                $csv_row['lecturer_name_for_search'] = $unsymbolized_lecturer_name . preg_replace(SELVA_SEARCH_NONTARGET_REGEXP, '', $csv_row['lecturer_name_for_search']);
            } else {
                // all_updateでないなら名前は元のまま
				$csv_row['lecturer_name'] = $similar_lecturers[0]['lecturer_name'];
				$csv_row['lecturer_name_for_search'] = preg_replace(SELVA_SEARCH_NONTARGET_REGEXP, '', $similar_lecturers[0]['lecturer_name'] . $csv_row['lecturer_name_for_search']);
            }

			// クエリ実行
			$has_succeeded = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}selva_lecturers
						SET	lecturer_name=%s, lecturer_name_for_search=%s,
							faculty=%s, class=%s, link_url=%s, image_url=%s,
							updated_at=CURRENT_TIMESTAMP
					  WHERE	lecturer_id=%d",
					...array_values($csv_row)
				)
			);

			// MySQLエラー、即return
			if($has_succeeded === false) {
				$mysql_error_message = mysql_last_error($row_no . '行目の更新中にエラーが起こりました。');
				break;
			}

			$updated_count++;
			// 同じ名前なら記録しない
			if($similar_lecturers[0]['lecturer_name'] === $trimmed_lecturer_name) continue;
		}

		// 複数被りがある。記録(常に最新のデータを残す)
		$duplicate_lecturer_names_similar_names[$lecturer_name] = array_column($similar_lecturers, 'lecturer_name');
    }

    if($invalid_lecturer_name_message)
        $invalid_lecturer_name_message .= '行目の教員名が不適切です。';

	$similar_lecturer_update_message = '';
	$duplicate_lecturer_error_message = '';
	foreach($duplicate_lecturer_names_similar_names as $lecturer_name => $similar_names) {
		// 自動更新されている
		if(count($similar_names) === 1 && $auto_update) {
			if($similar_lecturer_update_message)
				$similar_lecturer_update_message .= '、';

            $similar_lecturer_update_message .= $lecturer_name . '(=>' . $similar_names[0] . ')';
		} else {
			$duplicate_lecturer_names = '';
			foreach($similar_names as $similar_name) {
				if($duplicate_lecturer_names)
					$duplicate_lecturer_names .= '、';

                $duplicate_lecturer_names .= $similar_name;
			}

			if($duplicate_lecturer_error_message)
				$duplicate_lecturer_error_message .= '、';

            $duplicate_lecturer_error_message .= $lecturer_name . '(=>' . $duplicate_lecturer_names . ')';
		}
	}
	if($similar_lecturer_update_message) {
		if($all_update) {
			$similar_lecturer_update_message = '教員名:' . $similar_lecturer_update_message . 'は教員名ごと更新されました。';
		} else {
			$similar_lecturer_update_message = '教員名:' . $similar_lecturer_update_message . 'は教員名以外を更新しました。';
		}
	}
	if($duplicate_lecturer_error_message) {
		$duplicate_lecturer_error_message = '教員名:' . $duplicate_lecturer_error_message . 'は重複しています。';
		if($auto_update)
			$duplicate_lecturer_error_message .= '(2以上の教員名と重複したため自動更新できませんでした。)';
	}

    return [
        'inserted_count' => $inserted_count,
        'updated_count' => $updated_count,
        'similar_lecturer_update_message' => $similar_lecturer_update_message,
        'error_message' => $invalid_lecturer_name_message . $duplicate_lecturer_error_message . $mysql_error_message
    ];
}

// $auto_createは教員が見つからない場合に自動生成するかどうかというもの
// returnはstring(error)か'no_lecturer_create'、'error'をキーとするarray
function insert_course_data(string $csv_file_name, bool $auto_create=false) : array {
	global $wpdb;

    // スクリプトの最大動作時間を5分に設定
    set_time_limit(300);

    $csv_object = new SplFileObject($csv_file_name, 'r');
    $header_template_japanese = ['授業名', '授業名ルビ',       '設置課程',           '学期',     '曜日時限',        'キャンパス', '教員名'];
    $header                   = ['title',  'title_for_search', 'target_department', 'semester', 'day_and_period', 'campus',    'lecturer_name'];
    $header_row_no = NULL;
    $first_row = []; // 見出しが見つからないときのエラー通知に使う。
    $invalid_columns_row_nos = '';

	// ファイル→配列化
    $is_win = strpos(PHP_OS, "WIN") === 0;
    foreach($csv_object as $i => $line) {
        $row_no = $i + 1;

		// BOM削除
        if($row_no === 1)
            $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);

		// 文字化け対策
		// OSがUnix系ならUTF8に変換して処理、
		// Windowsでは一旦SJIS-winに変換してから配列に変換後、UTF8に戻して格納する。
		// $csv_object->setFlags(SplFileObject::READ_CSV); // 文字化け対策のためにここでは行を配列に分割しない
		if($is_win) {
            // SJISに変換
            $line = mb_convert_encoding($line, 'SJIS-win', ['ASCII','JIS','UTF-8','EUC-JP','SJIS', 'SJIS-win']);
        } else {
            // UTF8に変換
            $line = mb_convert_encoding($line, 'UTF8', ['ASCII','JIS','UTF-8','EUC-JP','SJIS', 'SJIS-win']);
        }

		//　ここで行を配列に分割
        $csv_row = str_getcsv($line);

		// WindowsのみUTF8に変換
        if($is_win)
            mb_convert_variables('UTF8', 'SJIS-win', $csv_row);

		// 最終行等の処理
        if($csv_row === [null]) continue;
        if(empty($first_row)) $first_row = $csv_row;
        if(!isset($header_row_no)) {
			// 見出し行である
			if($csv_row === $header_template_japanese)
				$header_row_no = $row_no;

			continue;
        }

		// 行数が見出しと違ったら記録
        if(count($csv_row) !== count($header)) {
            if($invalid_columns_row_nos)
                $invalid_columns_row_nos .= '、';

            $invalid_columns_row_nos .= $row_no;
            continue;
        }

        if($invalid_columns_row_nos) continue;

        // 配列に移す
        $csv_rows[] = array_combine($header, $csv_row);
    }

    // 見出しが見つからないときエラーメッセージを返す
    if(!isset($header_row_no)) {
        $error_columns = [];
        for($i = 0; $i < count($header_template_japanese); $i++) {
            if($i >= count($first_row)) {
                $error_columns[] = ($i+1) . '列目';
                continue;
            }
            if($first_row[$i] !== $header_template_japanese[$i])
                $error_columns[] = "'" . $first_row[$i] . "'";
        }
        if($error_columns)
            return ['error_message' => '見出しが見つかりません。(参考：先頭行において' . implode('、', $error_columns) . 'は見出しとして認識されていません。(これに見た目上の問題がない場合は開発者ツールを使ってHTML上から見てください。))'];

        return ['error_message' => '見出しが見つかりません。(参考：先頭行の列数は' . count($first_row) . '行でした。)'];
    }

    // 列数が違う所があったらエラー
    if($invalid_columns_row_nos)
        return ['error_message' => $invalid_columns_row_nos . '行目の列数が' . count($header) . '列ではありません。'];

    // 一行ずつ挿入。オムニバス授業なら授業テーブルに挿入後、オムニバス授業テーブルにもまとめて挿入する
	// 各種エラー準備
    $invalid_lecturer_name_message = ''; // $auto_create=trueのときの記録用
    $no_lecturer_names_each_count = []; // $auto_create=falseのときの記録用
    $new_lecturer_ids_names_count   = []; // $auto_create=trueのときの記録用
    $duplicate_lecturer_names_each_count = [];
    $duplicate_lecturer_names_similar_names = [];
    $invalid_title_message = '';
    $mysql_error_message = '';
    $error_count = 0;
    $max_error_count = PHP_INT_MAX;

	// 各種テーブルへ記録
    $inserted_count = 0;
    $updated_count = 0;
    foreach($csv_rows as $i => $csv_row) {
        $lecturer_names = explode(';', $csv_row['lecturer_name']);
        $no_lecturer_error_occured = false;
        $duplicate_lecturer_error_occured = false;
        $invalid_lecturer_name_per_row = NULL;
		$lecturer_id = 0;
        $omnibus_lecturer_ids = [];
		$row_no = $header_row_no + $i + 1; // エラー記録用

		// 教員名をある分だけ検索しlecturer_idを取得
        foreach($lecturer_names as $lecturer_name) {
            // 記号を取り除く
            $unsymbolized_lecturer_name = preg_replace(SELVA_SEARCH_NONTARGET_REGEXP, '', $lecturer_name);

            // 空ならエラーとして記録
            if(empty($unsymbolized_lecturer_name)) {
                if(isset($invalid_lecturer_name_per_row)) {
                    $invalid_lecturer_name_per_row .= '、' . $lecturer_name;
                } else {
                    $invalid_lecturer_name_per_row = $lecturer_name;
                }

                $error_count++;
                if($error_count > $max_error_count) break;
                continue;
            }

			// 教員の検索
			$similar_lecturers = search_similar_lecturers($lecturer_name);

			$lecturer_id = 0;
            if(count($similar_lecturers) === 0) {
				// 教員データがヒットしなかった

				// 自動作成しない場合はエラーとして記録
                if(!$auto_create) {
                    $no_lecturer_error_occured = true;
                    if(isset($no_lecturer_names_each_count[$lecturer_name])) {
                        $no_lecturer_names_each_count[$lecturer_name]++;
                    } else {
                        $no_lecturer_names_each_count[$lecturer_name] = 1;
                    }

                    $error_count++;
                    if($error_count > $max_error_count) break;
                    continue;
                }

                // 教員情報追加
                $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO {$wpdb->prefix}selva_lecturers
                                (lecturer_name, lecturer_name_for_search)
                         VALUES (%s, %s)",
                        trim_lecturer_name($lecturer_name),
                        $unsymbolized_lecturer_name,
                    )
                );

                $lecturer_id = $wpdb->insert_id;

                // MySQLエラー、即return
                if(empty($lecturer_id)) {
                    $mysql_error_message = mysql_last_error($row_no . '行目の' . $lecturer_name . 'という教員名の教員データの自動作成中にエラーが起こりました。');
                    break;
                }

				// 新しく作成された教員の記録
				$new_lecturer_ids_names_count[$lecturer_id] = [$lecturer_name => 1];
            } else if(count($similar_lecturers) === 1) {
				// ヒットした
				$lecturer_id = $similar_lecturers[0]['lecturer_id'];

				// 新しく作成された教員であれば記録
				if(isset($new_lecturer_ids_names_count[$lecturer_id])) {
                    if(isset($new_lecturer_ids_names_count[$lecturer_id][$lecturer_name])) {
                        $new_lecturer_ids_names_count[$lecturer_id][$lecturer_name]++;
                    } else {
                        $new_lecturer_ids_names_count[$lecturer_id][$lecturer_name] = 1;
                    }
				}
			} else {
				// 複数ヒットしているのでエラーとして記録
				$duplicate_lecturer_error_occured = true;

                if(isset($duplicate_lecturer_names_each_count[$lecturer_name])) {
                    $duplicate_lecturer_names_each_count[$lecturer_name]++;
                } else {
                    $duplicate_lecturer_names_each_count[$lecturer_name] = 1;
                }

                // 常に最新版を記録
                $duplicate_lecturer_names_similar_names[$lecturer_name] = array_column($similar_lecturers, 'lecturer_name');

                $error_count++;
				if($error_count > $max_error_count) break;
				continue;
            }

			// エラーがなければ記録
			$omnibus_lecturer_ids[] = $lecturer_id;
        }

		// エラーの記録
        if(isset($invalid_lecturer_name_per_row)) {
            if($invalid_lecturer_name_message)
                $invalid_lecturer_name_message .= '、';

            $invalid_lecturer_name_message .= $row_no . '行目:' . $invalid_lecturer_name_per_row;

            if($error_count > $max_error_count) break;
            continue;
        }
        if($no_lecturer_error_occured) {
            if($error_count > $max_error_count) break;
            continue;
        }
        if($duplicate_lecturer_error_occured) {
            if($error_count > $max_error_count) break;
            continue;
        }
		if($mysql_error_message) break;

        // 授業テーブルに一行ずつ挿入
        // titleのスペースや記号を排除したもの
        $unsymbolized_title = preg_replace(SELVA_SEARCH_NONTARGET_REGEXP, '', $csv_row['title']);

        // 空ならエラーとして記録
        if(empty($unsymbolized_title)) {
            if($invalid_title_message)
                $invalid_title_message .= '、';

            $invalid_title_message .= $row_no;
            continue;
        }

        $csv_row['title_for_search'] = $unsymbolized_title . preg_replace(SELVA_SEARCH_NONTARGET_REGEXP, '', $csv_row['title_for_search']);

		$has_post = 0;
        if(count($omnibus_lecturer_ids) > 1) {
			// オムニバス授業
			$lecturer_id = 2;
			$has_post = 1;
		}

        // lecturer_nameをlecturer_idに変える
        $csv_row = ['lecturer_id' => $lecturer_id] + $csv_row;
        unset($csv_row['lecturer_name']);

        $csv_row['has_post'] = $has_post;

        // クエリ実行
        $has_succeeded = $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}selva_courses
                        (lecturer_id, title, title_for_search, target_department, semester, day_and_period, campus, has_post)
                 VALUES (%d,          %s,    %s,               %s,                %s,       %s,             %s,     %d)
                   ON DUPLICATE KEY UPDATE title_for_search=VALUES(title_for_search),
				   						   has_post=VALUES(has_post),
				   						   updated_at=CURRENT_TIMESTAMP",
				...array_values($csv_row)
            )
        );

        // MySQLエラー、即return
        if($has_succeeded === false) {
            $mysql_error_message = mysql_last_error($row_no . '行目の授業データベースへの挿入中にエラーが起こりました。');
            break;
        }

        if($has_succeeded === 1) {
            // 新たに追加された行である(更新ではない)
            $inserted_count++;
        } else {
            // 更新
            $updated_count++;
        }

		// オムニバス授業でないならここでcontinue
        if(count($omnibus_lecturer_ids) <= 1) continue;

		// course_id取得
		unset($csv_row['title_for_search']);
		unset($csv_row['has_post']);

		$course_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT	course_id
				   FROM	{$wpdb->prefix}selva_courses
				  WHERE	lecturer_id=%d
				  		AND title=%s
						AND target_department=%s
                        AND semester=%s
						AND day_and_period=%s
						AND campus=%s",
                ...array_values($csv_row)
            )
		);

        // オムニバス授業テーブルにまとめて挿入
        $values = '';
		foreach($omnibus_lecturer_ids as $lecturer_id) {
            if(empty($values)) {
                $values =   $wpdb->prepare(" VALUES	(%d, %d)", $course_id, $lecturer_id);
            } else {
                $values .=  $wpdb->prepare(		 ", (%d, %d)", $course_id, $lecturer_id);
            }
        }

		// クエリ実行
        $has_succeeded = $wpdb->query(
            "INSERT INTO {$wpdb->prefix}selva_omnibus_course_lecturers
                    (course_id, lecturer_id)
            $values
               ON DUPLICATE KEY UPDATE updated_at=CURRENT_TIMESTAMP"
        );

        // MySQLエラー、即return
        if($has_succeeded === false) {
            $mysql_error_message = mysql_last_error($row_no . '行目のオムニバス授業データベースへの挿入中にエラーが起こりました。');
            break;
        }
    }

	// 教員自動作成のメッセージ
    $new_lecturer_create_message = '';
    foreach($new_lecturer_ids_names_count as $lecturer_id => $lecturer_names) {
        $new_lecturer_names = '';
        $i = 0;
        foreach($lecturer_names as $lecturer_name => $count) {
            $i++;
            if($i === 1) {
				// 最初は作成した教員の名前
				$new_lecturer_names = "$lecturer_name({$count}件)";
            } else if($i === 2) {
				// 最初以外はヒットした似た教員の名前
                $new_lecturer_names .= "($lecturer_name({$count}件)";
            } else {
                $new_lecturer_names .= "、$lecturer_name({$count}件)";
            }
        }
        if($i >= 2)
            $new_lecturer_names .= ')';

        if($new_lecturer_create_message)
            $new_lecturer_create_message .= '、';

        $new_lecturer_create_message .= $new_lecturer_names;
    }
    if($new_lecturer_create_message)
        $new_lecturer_create_message = '教員名:' . $new_lecturer_create_message . '(' . count($new_lecturer_ids_names_count) . '名)が教員データベースでヒットしなかったので、教員データの自動作成を行いました。';

	// 以下エラー文作成
    if($invalid_lecturer_name_message)
        $invalid_lecturer_name_message .= 'という教員名は検索可能な文字列を含んでいません。';

    $no_lecturer_error_message = '';
    foreach($no_lecturer_names_each_count as $lecturer_name => $count) {
        if($no_lecturer_error_message)
            $no_lecturer_error_message .= '、';

        $no_lecturer_error_message .= $lecturer_name . '(' . $count . '件)';
    }
    if($no_lecturer_error_message)
		$no_lecturer_error_message = '教員名:' . $no_lecturer_error_message . 'は教員データベースでヒットしませんでした。';

    $duplicate_lecturer_error_message = '';
	foreach($duplicate_lecturer_names_similar_names as $lecturer_name => $similar_names) {
		// 自動更新されている
        $duplicate_lecturer_names = '';
        foreach($similar_names as $similar_name) {
            if($duplicate_lecturer_names)
                $duplicate_lecturer_names .= '、';

            $duplicate_lecturer_names .= $similar_name;
        }

        if($duplicate_lecturer_error_message)
            $duplicate_lecturer_error_message .= '、';

        $duplicate_lecturer_error_message .= $lecturer_name . '(=>' . $duplicate_lecturer_names . ')(' . $duplicate_lecturer_names_each_count[$lecturer_name] .'件)';
	}
	if($duplicate_lecturer_error_message)
		$duplicate_lecturer_error_message = '教員名:' . $duplicate_lecturer_error_message . 'が教員データベースで複数件ヒットしました。';

    if($invalid_title_message)
        $invalid_title_message .= '行目の授業名は検索可能な文字列を含んでいません。';

    return [
        'inserted_count' => $inserted_count,
        'updated_count' => $updated_count,
        'new_lecturer_create_message' => $new_lecturer_create_message,
        'error_message' =>
            $invalid_lecturer_name_message .
            $no_lecturer_error_message .
            $duplicate_lecturer_error_message .
            $invalid_title_message .
            $mysql_error_message
    ];
}

function create_posts(bool $is_profile) : array {
	global $wpdb;

	$data = NULL;
	$post_type = '';
	if($is_profile) {
		$data = $wpdb->get_results(
			"SELECT	lecturer_id AS post_name, lecturer_name AS post_title
			   FROM	{$wpdb->prefix}selva_lecturers
			  WHERE	has_post",
			'ARRAY_A'
		);

		if(empty($data))
			return ['error_message' => '教員データがありません。'];

		$post_type = 'profile';
	} else {
		$data = $wpdb->get_results(
			"SELECT	course_id AS post_name, title AS post_title
			   FROM	{$wpdb->prefix}selva_courses
			  WHERE	has_post",
			'ARRAY_A'
		);

	    if(empty($data))
			return ['error_message' => '授業データがありません。'];

		$post_type = 'course';
	}

	$delete_by_time = current_time('mysql');

	$inserted_count = 0;
	$updated_count = 0;
	$mysql_error_message = '';
    foreach($data as $item) {
        $post_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT	ID 
                   FROM $wpdb->posts
				  WHERE	post_type=%s
						AND (post_status='publish' AND post_name=%s
							OR post_status='trash' AND post_name=%s)",
				$post_type,
				$item['post_name'],
				$item['post_name'] . '__trashed'
            )
        );

		$has_succeeded = true;
        // 同じURLの投稿があればそれを上書き
		if(empty($post_id)) {
			// 新しく挿入
			$has_succeeded = $wpdb->query(
				$wpdb->prepare(
					"INSERT	INTO $wpdb->posts
							(post_date, post_date_gmt, post_content, post_title, post_excerpt,
							ping_status, post_password, post_name, post_modified, post_modified_gmt,
							post_content_filtered, guid, post_type, post_mime_type)
					 VALUES (%s,        %s,            '',           %s,         '',
					        %s,          '',            %s,        %s,            %s,
							'',                    '',   %s,        '')",
					current_time('mysql'),
					current_time('mysql', 1),
					$item['post_title'],
					get_default_comment_status($post_type, 'pingback'),
					$item['post_name'],
					current_time('mysql'),
					current_time('mysql', 1),
					$post_type
				)
			);

			// MySQLエラー、即return
			if($has_succeeded === false) {
				if($is_profile) {
					$mysql_error_message = mysql_last_error($item['post_title'] . 'という教員名の投稿作成中に何らかのエラーが発生しました。');
				} else {
					$mysql_error_message = mysql_last_error($item['post_title'] . 'という授業名の投稿作成中に何らかのエラーが発生しました。');
				}
				break;
			}

            // guid設定
			$post_id = $wpdb->insert_id;
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE	$wpdb->posts
						SET	guid=%s
					  WHERE	ID=%d",
					get_permalink($post_id),
					$post_id
				)
			);
			$inserted_count++;
			continue;
		}

		// 上書き
		$has_succeeded = $wpdb->query(
			$wpdb->prepare(
				"UPDATE	$wpdb->posts
					SET	post_title=%s, post_status='publish', post_name=%s, post_modified=%s, post_modified_gmt=%s
				  WHERE	ID=%d",
				$item['post_title'],
				$item['post_name'],
				current_time('mysql'),
				current_time('mysql', 1),
				$post_id
			)
		);

		// MySQLエラー、即return
		if($has_succeeded === false) {
			if($is_profile) {
				$mysql_error_message = mysql_last_error($item['post_title'] . 'という教員名の投稿更新中に何らかのエラーが発生しました。');
			} else {
				$mysql_error_message = mysql_last_error($item['post_title'] . 'という授業名の投稿更新中に何らかのエラーが発生しました。');
			}
			break;
		}

        // 更新
        $updated_count++;
    }

	if($mysql_error_message)
		return [
			'inserted_count' => $inserted_count,
			'updated_count' => $updated_count,
			'error_message' => $mysql_error_message,
		];

	// 今作成・更新されなかった投稿の削除
	$trashed_count = $wpdb->query(
		$wpdb->prepare(
			"UPDATE	$wpdb->posts
				SET	post_status='trash', post_name=CONCAT(post_name, '__trashed')
			  WHERE	post_type=%s
					AND post_status='publish'
					AND post_modified<%s",
			$post_type,
			$delete_by_time
		),
		'ARRAY_A'
	);

	// MySQLエラー
	if($trashed_count === false) {
		$mysql_error_message = mysql_last_error('不要な投稿のゴミ箱への移動中に何らかのエラーが発生しました。');

		return [
			'inserted_count' => $inserted_count,
			'updated_count' => $updated_count,
			'error_message' => $mysql_error_message,
		];
	}

	return [
		'inserted_count' => $inserted_count,
        'updated_count' => $updated_count,
		'trashed_count' => $trashed_count
	];
}

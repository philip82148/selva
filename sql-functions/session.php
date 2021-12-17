<?php

// セッション開始関数。プログラム終了まで閉じない
// なお、session_id_intのみ用いる場合はそもそもsend_headersが呼び出されないので
// (post用のファイルを使うため)このフック↓は不要と思われる
// ただ、セッションデータが二重にできても(デフォルトのやつと)困るので一応呼び出しておく
// (セッションは開かなくてもget_session_id()内で開かれる)
add_action('send_headers', 'init_session_start');
function init_session_start() {
    // セッションが開始されていなければここで開始
    if(session_status() === PHP_SESSION_NONE) {
        session_set_save_handler(new SelvaSessionHandler(), true);
        session_start();
    }
}

// セッション開始後、渡された関数を実行してすぐに関数を閉じる
// 通信が発生したかをログ代わりに使うのもよし
function do_in_session($func) {
    // セッションが開始されていなければ開始
    $need_close = false;
    if(session_status() === PHP_SESSION_NONE) {
        session_set_save_handler(new SelvaSessionHandler(), false);
        session_start();
        $need_close = true;
    }

    // セッションIDがなければいったん閉じてセッションID作成
    if(SelvaSessionHandler::$session_id_int === 0 && session_status() !== PHP_SESSION_DISABLED) {
        session_write_close();
        session_start();
    }

    // 実行。引数はsession_id_int
    $func(SelvaSessionHandler::$session_id_int);

    if($need_close)
        session_write_close();
}

// セッションID取得
function get_session_id() {
    if(SelvaSessionHandler::$session_id_int === 0 && session_status() !== PHP_SESSION_DISABLED) {
        if(session_status() === PHP_SESSION_NONE) {
            // セッションが開始されていなければ開始して閉じることでセッションIDを作成
            session_set_save_handler(new SelvaSessionHandler(), false);
            session_start();
            session_write_close();
        } else {
            // いったん閉じることでセッションID作成
            session_write_close();
            // セッション再開
            session_start();
        }
    }
    
    return SelvaSessionHandler::$session_id_int;
}

class SelvaSessionHandler implements SessionHandlerInterface {
    public static int $session_id_int = 0;

    function open($save_path, $name) {
        return true;
    }
    
    function read($session_id_chr) {
        global $wpdb;

        $session = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT session_id_int, session_data
                   FROM {$wpdb->prefix}selva_sessions
                  WHERE session_id_chr=%s",
                $session_id_chr
            ),
            'ARRAY_A'
        );

        if($session) {
            if(self::$session_id_int === 0)
                self::$session_id_int = $session['session_id_int'];
            
            return $session['session_data'];
        }

        // 無ければid作成のために一旦書き込み
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}selva_sessions
                        (session_id_chr, session_data)
                 VALUES (%s, '')",
                $session_id_chr
            )
        );

        self::$session_id_int = $wpdb->insert_id;
        
        return '';
    }
    
    function write($session_id_chr, $session_data) {
        global $wpdb;

        $has_succeeded = $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}selva_sessions
                        (session_id_chr, session_data)
                 VALUES (%s, %s)
                   ON DUPLICATE KEY UPDATE session_data=VALUES(session_data)",
                $session_id_chr,
                $session_data
            )
    	);

        if($has_succeeded === false) return false;

        if(self::$session_id_int === 0) {
            self::$session_id_int = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT session_id_int
                       FROM {$wpdb->prefix}selva_sessions
                      WHERE session_id_chr=%s",
                    $session_id_chr
                )
            ) ?? 0;
        }

        return true;
    }

    function destroy($session_id_chr) {
        global $wpdb;

        if(self::$session_id_int === 0) {
            self::$session_id_int = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT session_id_int
                       FROM {$wpdb->prefix}selva_sessions
                      WHERE session_id_chr=%s",
                    $session_id_chr
                )
            ) ?? 0;
        }

        // SelvaのテーブルにセッションIDが使われている
        if(self::$session_id_int !== 0) {
            // 重そうな処理。要検討。
            move_session_to_nonsession_ratings(self::$session_id_int);
        }

        // セッション削除
        $has_succeeded = $wpdb->query(
    		$wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}selva_sessions
                  WHERE session_id_chr=%s",
                $session_id_chr,
    		),
    	);

        self::$session_id_int = 0;
        return $has_succeeded !== false;
    }

    function close() {
        return true;
    }

    function gc($maxlifetime) {
        global $wpdb;

        $delete_by_time_str = date('Y-m-d H:i:s', time() - $maxlifetime);
        // これも重そう
        move_garbage_to_nonsession_ratings($delete_by_time_str);

        // セッション削除
        $deleted_count = $wpdb->query(
    		$wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}selva_sessions
                  WHERE updated_at < %s
                        AND session_id_int>1",
                $delete_by_time_str,
    		),
    	);

        if($deleted_count === false) return false;

        return $deleted_count;
    }
}

function move_session_to_nonsession_ratings($session_id_int) : void {
    global $wpdb;
    
    // セッションの評価をnonsession_ratingsに移す
    for($rating_no = 1; $rating_no <= 3; $rating_no++) {
        for($stars = 1; $stars <= 5; $stars++) {
            // 授業の評価
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}selva_courses AS courses
					   JOIN	{$wpdb->prefix}selva_course_ratings AS ratings
					     ON courses.course_id=ratings.course_id
                        SET courses.nonsession_rating{$rating_no}_{$stars}stars=courses.nonsession_rating{$rating_no}_{$stars}stars+1
                      WHERE ratings.session_id_int=%d
                            AND ratings.rating$rating_no=$stars",
                    $session_id_int
                )
            );
            // オムニバス授業の評価
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}selva_omnibus_course_lecturers AS omnibus
					   JOIN	{$wpdb->prefix}selva_omnibus_course_ratings AS ratings
					     ON omnibus.course_id=ratings.course_id
                            AND omnibus.lecturer_id=ratings.lecturer_id
                        SET omnibus.nonsession_rating{$rating_no}_{$stars}stars=omnibus.nonsession_rating{$rating_no}_{$stars}stars+1
                      WHERE ratings.session_id_int=%d
                            AND ratings.rating$rating_no=$stars",
                    $session_id_int
                )
            );
        }
    }

    // タグの評価の移動
	// 普通の授業のinsessionの取得
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT	course_id, rating_tags
               FROM	{$wpdb->prefix}selva_course_ratings
              WHERE	session_id_int=%d",
            $session_id_int
        ),
		'ARRAY_A'
	) ?? [];

	// nonsessionと合わせて更新
	foreach($results as $item) {
        $course_id = $item['course_id'];
        $rating_tags = unserialize($item['rating_tags']);
        if(!is_array($rating_tags)) $rating_tags = [];

		$nonsession_rating_tags = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT	nonsession_rating_tags
				   FROM	{$wpdb->prefix}selva_courses
				  WHERE	course_id=%d",
				$course_id
			)
		);

		if($nonsession_rating_tags)
			$nonsession_rating_tags = unserialize($nonsession_rating_tags);
            
        if(!is_array($nonsession_rating_tags)) $nonsession_rating_tags = [];

        foreach($rating_tags as $tag) {
            if(isset($nonsession_rating_tags[$tag])) {
                $nonsession_rating_tags[$tag]++;
            } else {
                $nonsession_rating_tags[$tag] = 1;
            }
        }

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}selva_courses
					SET	nonsession_rating_tags=%s
				  WHERE	course_id=%d",
				serialize($nonsession_rating_tags),
				$course_id
			)
		);
	}
	
    // オムニバス授業のinsessionの取得
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT	course_id, lecturer_id, rating_tags
               FROM	{$wpdb->prefix}selva_omnibus_course_ratings
              WHERE	session_id_int=%d",
            $session_id_int
        ),
		'ARRAY_A'
	) ?? [];

	// nonsessionと合わせて更新
	foreach($results as $item) {
        $course_id = $item['course_id'];
        $lecturer_id = $item['lecturer_id'];
        $rating_tags = unserialize($item['rating_tags']);
        if(!is_array($rating_tags)) $rating_tags = [];

		$nonsession_rating_tags = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT	nonsession_rating_tags
				   FROM	{$wpdb->prefix}selva_omnibus_course_lecturers
				  WHERE	course_id=%d
                        AND lecturer_id=%d",
				$course_id,
                $lecturer_id
			)
		);

		if($nonsession_rating_tags)
			$nonsession_rating_tags = unserialize($nonsession_rating_tags);
            
        if(!is_array($nonsession_rating_tags)) $nonsession_rating_tags = [];

        foreach($rating_tags as $tag) {
            if(isset($nonsession_rating_tags[$tag])) {
                $nonsession_rating_tags[$tag]++;
            } else {
                $nonsession_rating_tags[$tag] = 1;
            }
        }

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}selva_omnibus_course_lecturers
					SET	nonsession_rating_tags=%s
				  WHERE	course_id=%d
                        AND lecturer_id=%d",
				serialize($nonsession_rating_tags),
				$course_id,
                $lecturer_id
			)
		);
	}
	
    // コメントの評価
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}selva_comments AS comments
			   JOIN	{$wpdb->prefix}selva_comment_ratings AS ratings
			     ON	comments.comment_id=ratings.comment_id
                SET comments.nonsession_ratings=comments.nonsession_ratings+1
              WHERE	ratings.session_id_int=%d",
            $session_id_int
        )
    );

	// このsession自身が残したコメントをno_dataが残したものとする
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}selva_comments
                SET session_id_int=1
              WHERE session_id_int=%d",
            $session_id_int
        )
    );

	// SelvaのテーブルからセッションIDを削除
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}selva_course_ratings
              WHERE session_id_int=%d",
            $session_id_int
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}selva_comment_ratings
              WHERE session_id_int=%d",
            $session_id_int
        )
    );
}

// 授業データのセッションの評価をnonsession_ratingsに移動
// sql-functions/admin-option.phpでも使う
function move_course_to_nonsession_ratings($delete_by_time_str) : bool {
    global $wpdb;

    // 星の評価の移動
	$has_succeeded = true;
    for($rating_no = 1; $rating_no <= 3; $rating_no++) {
        for($stars = 1; $stars <= 5; $stars++) {
            $has_succeeded &= $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}selva_courses AS courses
					   JOIN	{$wpdb->prefix}selva_course_ratings AS ratings
					     ON courses.course_id=ratings.course_id
					   JOIN	{$wpdb->prefix}selva_sessions AS sessions_
					   	 ON ratings.session_id_int=sessions_.session_id_int
                        SET courses.nonsession_rating{$rating_no}_{$stars}stars=courses.nonsession_rating{$rating_no}_{$stars}stars
                              +	(SELECT	COUNT(*)
                                   FROM	{$wpdb->prefix}selva_course_ratings AS sub_ratings
                                   JOIN	{$wpdb->prefix}selva_sessions AS sub_sessions
                                	 ON	sub_ratings.session_id_int=sub_sessions.session_id_int
                                  WHERE	sub_sessions.updated_at < %s
                                		AND sub_ratings.course_id=courses.course_id
                                    	AND sub_ratings.rating$rating_no=$stars)
					  WHERE	sessions_.updated_at < %s",
                    $delete_by_time_str,
                    $delete_by_time_str
                )
            ) !== false;
        }
    }

	if($has_succeeded === false) return false;

    // タグの評価の移動
	// insessionの取得
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT	ratings.course_id, ratings.rating_tags
               FROM	{$wpdb->prefix}selva_course_ratings AS ratings
               JOIN	{$wpdb->prefix}selva_sessions AS sessions_
            	 ON	ratings.session_id_int=sessions_.session_id_int
              WHERE	sessions_.updated_at < %s",
			$delete_by_time_str
		),
		'ARRAY_A'
	) ?? [];

	// insessionをcourse_idごとにまとめる
	$course_ids_rating_tags = [];
    foreach($results as $item) {
		if(empty($item['rating_tags'])) continue;
		$course_id = $item['course_id'];
		$rating_tags = unserialize($item['rating_tags']);
        if(!is_array($rating_tags)) $rating_tags = [];

		if(isset($course_ids_rating_tags[$course_id])) {
			foreach($rating_tags as $tag) {
				if(isset($course_ids_rating_tags[$course_id][$tag])) {
					$course_ids_rating_tags[$course_id][$tag]++;
				} else {
					$course_ids_rating_tags[$course_id][$tag] = 1;
				}
			}
		} else {
            if($rating_tags) {
                $course_ids_rating_tags[$course_id] = [];
                foreach($rating_tags as $tag) {
                    $course_ids_rating_tags[$course_id][$tag] = 1;
                }
            }
		}
	}

	// nonsessionと合わせて更新
	$has_succeeded = true;
	foreach($course_ids_rating_tags as $course_id => $rating_tags) {
		$nonsession_rating_tags = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT	nonsession_rating_tags
				   FROM	{$wpdb->prefix}selva_courses
				  WHERE	course_id=%d",
				$course_id
			)
		);

		if($nonsession_rating_tags) {
			$nonsession_rating_tags = unserialize($nonsession_rating_tags);

            if(is_array($nonsession_rating_tags)) {
                foreach($nonsession_rating_tags as $rating_tag => $count) {
                    if(isset($rating_tags[$rating_tag])) {
                        $rating_tags[$rating_tag] += $count;
                    } else {
                        $rating_tags[$rating_tag] = $count;
                    }
                }
            }
		}

		$has_succeeded &= $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}selva_courses
					SET	nonsession_rating_tags=%s
				  WHERE	course_id=%d",
				serialize($rating_tags),
				$course_id
			)
		) !== false;
	}
	
	if($has_succeeded === false) return false;

	// insessionの削除
    $has_succeeded = $wpdb->query(
        $wpdb->prepare(
            "DELETE ratings
               FROM {$wpdb->prefix}selva_course_ratings AS ratings
               JOIN {$wpdb->prefix}selva_sessions AS sessions_
                 ON ratings.session_id_int=sessions_.session_id_int
              WHERE sessions_.updated_at < %s",
            $delete_by_time_str
        )
    );

    return $has_succeeded !== false;
}

// オムニバス授業データのセッションの評価をnonsession_ratingsに移動
// sql-functions/admin-option.phpでも使う
function move_omnibus_to_nonsession_ratings($delete_by_time_str) : bool {
	global $wpdb;

    // 星の評価の移動
    $has_succeeded = true;
    for($rating_no = 1; $rating_no <= 3; $rating_no++) {
        for($stars = 1; $stars <= 5; $stars++) {
            $has_succeeded &= $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}selva_omnibus_course_lecturers AS omnibus
					   JOIN	{$wpdb->prefix}selva_omnibus_course_ratings AS ratings
					     ON omnibus.course_id=ratings.course_id
                            AND omnibus.lecturer_id=ratings.lecturer_id
					   JOIN	{$wpdb->prefix}selva_sessions AS sessions_
					   	 ON ratings.session_id_int=sessions_.session_id_int
                        SET courses.nonsession_rating{$rating_no}_{$stars}stars=courses.nonsession_rating{$rating_no}_{$stars}stars
                              +	(SELECT	COUNT(*)
                                   FROM	{$wpdb->prefix}selva_omnibus_course_ratings AS sub_ratings
                                   JOIN	{$wpdb->prefix}selva_sessions AS sub_sessions
                                	 ON	sub_ratings.session_id_int=sub_sessions.session_id_int
                                  WHERE	sub_sessions.updated_at < %s
                                		AND sub_ratings.course_id=omnibus.course_id
                                        AND sub_ratings.lecturer_id=omnibus.lecturer_id
                                    	AND sub_ratings.rating$rating_no=$stars)
					  WHERE	sessions_.updated_at < %s",
                    $delete_by_time_str,
                    $delete_by_time_str
                )
            ) !== false;
        }
    }

	if($has_succeeded === false) return false;

    // タグの評価の移動
	// insessionの取得
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT	ratings.course_id, ratings.lecturer_id ratings.rating_tags
               FROM	{$wpdb->prefix}selva_omnibus_course_ratings AS ratings
               JOIN	{$wpdb->prefix}selva_sessions AS sessions_
            	 ON	ratings.session_id_int=sessions_.session_id_int
              WHERE	sessions_.updated_at < %s",
			$delete_by_time_str
		),
		'ARRAY_A'
	) ?? [];

	// insessionをids="$course_id|$lecturer_id"ごとにまとめる
	$ids_rating_tags = [];
    foreach($results as $item) {
		if(empty($item['rating_tags'])) continue;
		$ids = "{$item['course_id']}|{$item['lecturer_id']}";
		$rating_tags = unserialize($item['rating_tags']);
        if(!is_array($rating_tags)) $rating_tags = [];

		if(isset($ids_rating_tags[$ids])) {
			foreach($rating_tags as $tag) {
				if(isset($ids_rating_tags[$ids][$tag])) {
					$ids_rating_tags[$ids][$tag]++;
				} else {
					$ids_rating_tags[$ids][$tag] = 1;
				}
			}
		} else {
            if($rating_tags) {
                $ids_rating_tags[$ids] = [];
                foreach($rating_tags as $tag) {
                    $ids_rating_tags[$ids][$tag] = 1;
                }
            }
		}
	}

	// nonsessionと合わせて更新
	$has_succeeded = true;
	foreach($ids_rating_tags as $ids => $rating_tags) {
		$ids = explode('|', $ids);
		$nonsession_rating_tags = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT	nonsession_rating_tags
				   FROM	{$wpdb->prefix}selva_omnibus_course_lecturers
				  WHERE	course_id=%d
				  		AND lecturer_id=%d",
				$ids[0],
				$ids[1]
			)
		);

		if($nonsession_rating_tags) {
			$nonsession_rating_tags = unserialize($nonsession_rating_tags);

            if(is_array($nonsession_rating_tags)) {
                foreach($nonsession_rating_tags as $rating_tag => $count) {
                    if(isset($rating_tags[$rating_tag])) {
                        $rating_tags[$rating_tag] += $count;
                    } else {
                        $rating_tags[$rating_tag] = $count;
                    }
                }
            }
		}

		$has_succeeded &= $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}selva_omnibus_course_lecturers
					SET	nonsession_rating_tags=%s
				  WHERE	course_id=%d
				  		AND lecturer_id=%d",
				serialize($rating_tags),
				$ids[0],
				$ids[1]
			)
		) !== false;
	}
	
	if($has_succeeded === false) return false;

	// insessionの削除
    $has_succeeded = $wpdb->query(
        $wpdb->prepare(
            "DELETE ratings
               FROM {$wpdb->prefix}selva_omnibus_course_ratings AS ratings
               JOIN {$wpdb->prefix}selva_sessions AS sessions_
                 ON ratings.session_id_int=sessions_.session_id_int
              WHERE sessions_.updated_at < %s",
            $delete_by_time_str
        )
    );

    return $has_succeeded !== false;
}

function move_garbage_to_nonsession_ratings($delete_by_time_str) : void {
    global $wpdb;

    // 授業の評価の移動
    move_course_to_nonsession_ratings($delete_by_time_str);
	move_omnibus_to_nonsession_ratings($delete_by_time_str);

    // コメントの評価の移動
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}selva_comments AS comments
			   JOIN	{$wpdb->prefix}selva_comment_ratings AS ratings
			     ON comments.comment_id=ratings.comment_id
			   JOIN	{$wpdb->prefix}selva_sessions AS sessions_
			   	 ON ratings.session_id_int=sessions_.session_id_int
                SET comments.nonsession_ratings=comments.nonsession_ratings
                      + (SELECT COUNT(*)
                           FROM {$wpdb->prefix}selva_comment_ratings AS sub_ratings
                           JOIN {$wpdb->prefix}selva_sessions AS sub_sessions
                             ON sub_ratings.session_id_int=sub_sessions.session_id_int
                          WHERE sub_sessions.updated_at < %s
                                AND sub_ratings.comment_id=comments.comment_id)
			  WHERE	sessions_.updated_at < %s",
            $delete_by_time_str,
			$delete_by_time_str
        )
    );

	// このsession自身が残したコメントをno_dataが残したものとする
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}selva_comments AS comments
			   JOIN	{$wpdb->prefix}selva_sessions AS sessions_
			     ON comments.session_id_int=sessions_.session_id_int
                SET comments.session_id_int=1
			  WHERE	sessions_.updated_at < %s",
            $delete_by_time_str
        )
    );

	// SelvaのテーブルからセッションIDを削除
    $wpdb->query(
        $wpdb->prepare(
            "DELETE ratings
               FROM {$wpdb->prefix}selva_comment_ratings AS ratings
               JOIN {$wpdb->prefix}selva_sessions AS sessions_
                 ON ratings.session_id_int=sessions_.session_id_int
              WHERE sessions_.updated_at < %s",
            $delete_by_time_str
        )
    );
}

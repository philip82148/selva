<?php

// コメントを評価する
function rating_comment(int $comment_id, bool $is_like) : void {
	global $wpdb;

	if($is_like) {
		$wpdb->query(
			$wpdb->prepare(
				"INSERT	INTO {$wpdb->prefix}selva_comment_ratings (session_id_int, comment_id)
				 VALUES	(%d, %d)
				   ON DUPLICATE KEY UPDATE comment_id=VALUES(comment_id)",
				get_session_id(),
				$comment_id
			)
		);
	} else {
		$wpdb->query(
			$wpdb->prepare(
				"DELETE	FROM {$wpdb->prefix}selva_comment_ratings
				  WHERE	session_id_int=%d
				    AND	comment_id=%d",
				get_session_id(),
				$comment_id
			)
		);
	}
}

// コメントのライクの数を数える
function count_comment_likes(int $comment_id) : int {
	global $wpdb;

	// nonsession
    $count = $wpdb->get_var(
        $wpdb->prepare(
			"SELECT nonsession_ratings
			   FROM {$wpdb->prefix}selva_comment
			  WHERE comment_id=%d",
            $comment_id
        )
    ) ?? 0;

	// in session
	$count += $wpdb->get_var(
        $wpdb->prepare(
			"SELECT COUNT(*)
			   FROM {$wpdb->prefix}selva_comment_ratings
			  WHERE comment_id=%d",
            $comment_id
        )
    ) ?? 0;

	return $count;
}

// ユーザーが過去にライクしたコメントか調べる
function is_liked_comment(int $comment_id) : bool {
	global $wpdb;

    $count = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT	COUNT(*)
			   FROM	{$wpdb->prefix}selva_comment_ratings
			  WHERE	session_id_int=%d
				AND	comment_id=%d",
            get_session_id(),
            $comment_id,
        )
    );

	return empty($count) ? false : true;
}

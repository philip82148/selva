<?php

require_once(__DIR__ . '/admin-common.php');

function delete_all_data() : string {
    global $wpdb;

    $tables = [
        "{$wpdb->prefix}selva_lecturers",
        "{$wpdb->prefix}selva_courses",
        "{$wpdb->prefix}selva_omnibus_course_lecturers",
        "{$wpdb->prefix}selva_sessions",
        "{$wpdb->prefix}selva_course_ratings",
        "{$wpdb->prefix}selva_omnibus_course_ratings",
        "{$wpdb->prefix}selva_rating_tags",
        "{$wpdb->prefix}selva_comments",
        "{$wpdb->prefix}selva_comment_ratings",
        "{$wpdb->prefix}selva_user_actions"
    ];


    // 全テーブル削除
    $wpdb->query("DROP TABLE IF EXISTS " . implode(', ', array_reverse($tables)));
    $last_error = $wpdb->last_error;
    $last_query = $wpdb->last_query;

    // 消えたか確認
    $error_tables = '';
    foreach($tables as $table) {
        if($wpdb->get_var("SHOW TABLES LIKE '$table'")) {
            if(empty($error_tables)) {
                $error_tables =  $table;
            } else {
                $error_tables .= '、' . $table;
            }
        }
    }
    if($error_tables) return mysql_last_error('テーブル' . $error_tables . 'の削除に失敗しました。', $last_error, $last_query);

    // 投稿の削除
    $wpdb->query(
        "DELETE FROM $wpdb->posts
          WHERE post_type IN ('profile', 'course')"
    );
    $last_error = $wpdb->last_error;
    $last_query = $wpdb->last_query;

    $undeleted_count = $wpdb->get_var(
        "SELECT COUNT(*)
           FROM $wpdb->posts
          WHERE post_type IN ('profile', 'course')"
    );

    if(!isset($undeleted_count) || $undeleted_count)
        return mysql_last_error($undeleted_count . 'の投稿の削除に失敗しました。', $last_error, $last_query);
    
    // optionの削除
    $has_succeeded = true;
    for($rating_no = 1; $rating_no < 3; $rating_no++) {
        delete_option(get_star_rating_label_option_name($rating_no));
        $has_succeeded &= get_option(get_star_rating_label_option_name($rating_no)) === false;
    }

    if($has_succeeded) return '';

    return 'テーマオプションの削除に失敗しました。';
}

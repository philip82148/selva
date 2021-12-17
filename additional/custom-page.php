<?php
/*
 * カスタムページの設定と設定画面
 * ワードプレスに本来存在していないprofileページとcourseページを作る
 * というと凄く聞こえるが実際はカスタム投稿ページを利用している
 * これを利用した理由は結局のところサイトマップの作製やコメントにプラグインを利用しやすくなるからである。
 * 結果千ページぐらい作成することになるが笑
 */
// 教員ページを登録。将来的には教員以外も含めた人物ページにしてもよいかも。
add_action( 'init', 'register_profile_type' );
function register_profile_type() {
    $labels = array(
        'name'                => '教員ページ',
        'singular_name'       => '教員ページ',
        'add_new_item'        => '新しい教員ページを追加',
        'add_new'             => '新規追加',
        'new_item'            => '新しい教員ページ',
        'view_item'           => '教員ページを表示',
        'not_found'           => '教員ページはありません',
        'not_found_in_trash'  => 'ゴミ箱に教員ページはありません。',
        'search_items'        => '教員ページを検索',
    );
    $args = array(
    'labels'              => $labels,
    'public'              => true,
    'exclude_from_search' => true,
    'menu_position'       => 5,
    'has_archive'         => false,
    'supports'            => array(
        'title',
        'editor',
        'thumbnail',
        'page-attributes',
        'comments'
        )
    ); 
    register_post_type('profile', $args);
    //  flush_rewrite_rules( false );  /* 必要なのか */
}

// 授業ページを登録
add_action( 'init', 'register_course_type' );
function register_course_type() {
    $labels = array(
        'name'                => '授業ページ',
        'singular_name'       => '授業ページ',
        'add_new_item'        => '新しい授業ページを追加',
        'add_new'             => '新規追加',
        'new_item'            => '新しい授業ページ',
        'view_item'           => '授業ページを表示',
        'not_found'           => '授業ページはありません',
        'not_found_in_trash'  => 'ゴミ箱に授業ページはありません。',
        'search_items'        => '授業ページを検索',
    );
    $args = array(
    'labels'              => $labels,
    'public'              => true,
    'exclude_from_search' => true,
    'menu_position'       => 5,
    'has_archive'         => false,
    'supports'            => array(
        'title',
        'editor',
        'thumbnail',
        'page-attributes',
        'comments'
        )
    ); 
    register_post_type('course', $args);
    //  flush_rewrite_rules( false );  /* これです。 */
}

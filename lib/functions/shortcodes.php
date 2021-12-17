<?php


/* SNSショートコード
* ---------------------------------------- */
add_shortcode('bzb_sns', 'bzb_sc_social_buttons');
function bzb_sc_social_buttons(){

  $disp_social_buttons = '';
  $social_buttons = get_option('social_buttons');

  if( !isset($social_buttons) || $social_buttons !== 'none' ){

    $twitter_id = get_option('twitter_id');
    
    $page_url = get_category_link();
    $post_title = get_bzb_title();

    if( isset($cf['bzb_meta_description']) ){
      $bzb_meta_description = $cf['bzb_meta_description'][0];
    }

    $disp_social_buttons .=<<<eof
  <!-- ソーシャルボタン -->
  <ul class="bzb-sns-btn">
    <li class="bzb-facebook">
      <fb:like href="{$page_url}" send="false" layout="button_count" width="100" show_faces="false" action="like" colorscheme="light" font=""></fb:like>
    </li>
    <li class="bzb-twitter">
      <iframe allowtransparency="true" frameborder="0" scrolling="no" src="//platform.twitter.com/widgets/tweet_button.html?url={$page_url}&amp;text={$post_title}&amp;via={$twitter_id}&amp;lang=ja&amp;count=horizontal" style="width:auto; height:20px;"></iframe>
    </li>
    <li class="bzb-googleplus">
      <g:plusone size="medium" href="{$page_url}" ></g:plusone>
    </li>
    <li class="bzb-hatena">
      <a href="http://b.hatena.ne.jp/entry/{$page_url}" class="hatena-bookmark-button" data-hatena-bookmark-title="{$post_title}" data-hatena-bookmark-layout="standard" data-hatena-bookmark-lang="ja" title="このエントリーをはてなブックマークに追加"><img src="//b.hatena.ne.jp/images/entry-button/button-only@2x.png" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a><script type="text/javascript" src="//b.hatena.ne.jp/js/bookmark_button.js" charset="utf-8" async="async"></script>
    </li>
  </ul><!-- /bzb-sns-btns -->

eof;

    return $disp_social_buttons;
  } 
}


/* テンプレートディレクトリURL
* ---------------------------------------- */
add_shortcode('bzb_dir', 'bzb_sc_template_directory');
function bzb_sc_template_directory(){
  if( get_template_directory() == get_stylesheet_directory() ){
    $dir = get_template_directory_uri();
  }else{
    $dir = get_stylesheet_directory_uri();
  }
  return $dir;
}


/* 各種ショートコード
* ---------------------------------------- */
add_shortcode('sitemap', 'bzb_simple_sitemap');
function bzb_simple_sitemap(){
  global $wpdb;

/* post */
 
  $post = get_posts('post_status=publish');
  $echo = '';
  foreach ( $post as $item ){
    $im = wp_get_attachment_image_src(get_post_thumbnail_id($item->ID),'none',true);
    $date = date('Y.m.d',strtotime(get_post($item->ID)->post_date));
    $update = date('Y.m.d',strtotime(get_post($item->ID)->post_modified));
    $echo .= '<li><a href="'.get_permalink($item->ID).'">'.$item->post_title.'</a></li>' . "\n";
  }
 
  echo '<div id="sitemap"><ul><li><span class="subheader">投稿一覧</span><ul>';
  echo $echo;
  echo '</ul></li></ul>';


/* 固定ページ */
  $args = array('depth'        => 0,
    'show_date'    => NULL,
    'date_format'  => get_option('date_format'),
    'child_of'     => 0,
    'exclude'      => NULL,
    'include'      => NULL,
    'title_li'           => '<span class="subheader">固定ページの一覧</span>',
    'echo'         => 1,
    'authors'      => NULL,
    'sort_column'  => 'menu_order, post_title',
    'link_before'  => NULL,
    'link_after'   => NULL,
    'exclude_tree' => NULL ); 
 
  echo '<ul>';
    wp_list_pages($args);
  echo '</ul>';
 
  $args = array('show_option_all'    => NULL,
    'orderby'            => 'name',
    'order'              => 'ASC',
    'show_last_update'   => 0,
    'style'              => 'list',
    'show_count'         => 0,
    'hide_empty'         => 1,
    'use_desc_for_title' => 1,
    'child_of'           => 0,
    'feed'               => NULL,
    'feed_type'          => NULL,
    'feed_image'         => NULL,
    'exclude'            => NULL,
    'exclude_tree'       => NULL,
    'include'            => NULL,
    'hierarchical'       => true,
    'title_li'           => '<span class="subheader">記事カテゴリ</span>',
    'number'             => NULL,
    'echo'               => 1,
    'depth'              => 0,
    'current_category'   => 0,
    'pad_counts'         => 0,
    'taxonomy'           => 'category',
    'walker'             => 'Walker_Category' );
 
  echo '<ul>';
  echo wp_list_categories( $args );
  echo '</ul>';
  echo '</div>';
}

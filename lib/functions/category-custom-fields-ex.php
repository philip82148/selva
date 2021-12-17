<?php


/* extension用ルビ＆キャッチコピー保存
* ---------------------------------------- */
add_action('save_post', 'bzb_ex_ruby_catch_save');
function bzb_ex_ruby_catch_save($post_id) {
  global $post;
  $my_nonce = isset($_POST['my_nonce']) ? $_POST['my_nonce'] : null;
  
  if( !wp_verify_nonce($my_nonce, wp_create_nonce(__FILE__)) ) {  
    return $post_id;
  }
  if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) { return $post_id; }
  if( !current_user_can('edit_post', $post->ID) ) { return $post_id; }
  
    update_post_meta($post->ID, 'bzb_post_front_info', $_POST['bzb_post_front_info']);

}


/* add comment..
* ---------------------------------------- */
add_action('add_meta_boxes', 'add_bzb_post_frontinfo');
function add_bzb_post_frontinfo() {
  add_meta_box('bzb_post_front_info', 'トップページに表示する際のルビとキャッチコピー', 'bzb_post_front_info', 'post', 'normal', 'low');
  add_meta_box('bzb_post_front_info', 'トップページに表示する際のルビとキャッチコピー', 'bzb_post_front_info', 'page', 'normal', 'low');
}
 
function bzb_post_front_info() {  
  global $post;
  wp_nonce_field(wp_create_nonce(__FILE__), 'my_nonce');
?>
<h4>ルビ</h4>

<?php
  $bzb_post_front_info = get_post_meta($post->ID, 'bzb_post_front_info', true);
  $ruby = '';
  $catch = '';
  if( isset($bzb_post_front_info) && $bzb_post_front_info !== '' ){
    extract($bzb_post_front_info);
  }
?>
<input type="text" name="bzb_post_front_info[ruby]" id="" class="" value="<?php echo $ruby; ?>">

<h4>キャッチコピー</h4>
<p><small></small></p>
<input type="text" name="bzb_post_front_info[catch]" id="" class="" value="<?php echo $catch; ?>">

<?php
}


/* add comment..
* ---------------------------------------- */
add_action ( 'edit_category_form_fields', 'bzb_category_fields_ex');
function bzb_category_fields_ex( $tag ) {
  $t_id = $tag->term_id;
  $cat_meta = get_option( "cat_$t_id");

?>
  <tr class="form-field">
    <th><label for="bzb_meta_catch">カテゴリーページキャッチコピー</label></th>
    <td>
      <input type="text" name="Cat_meta[catch]" id="catch" size="25" value="<?php if(isset ( $cat_meta['catch'])) echo esc_html($cat_meta['catch']) ?>" />
      <p class="description">トップページに表示する際のキャッチコピー</p>
    </td>
  </tr>
  <tr class="form-field">
    <th><label for="bzb_meta_ruby">カテゴリーページサブキャッチコピー</label></th>
    <td>
      <input type="text" name="Cat_meta[ruby]" id="ruby" size="25" value="<?php if(isset ( $cat_meta['ruby'])) echo esc_html($cat_meta['ruby']) ?>" />
      <p class="description">トップページに表示する際のルビ（カテゴリー名の下に表示される文字）</p>
    </td>
  </tr>
<?php
}
?>
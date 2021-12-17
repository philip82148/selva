<?php

add_action('admin_menu', 'chiled_theme_initial_settings');

function chiled_theme_initial_settings() {
  add_menu_page('テーマ設定', 'テーマ設定', 'manage_options', 'chiled_theme_initial_setting_menu', 'child_theme_setting_page', '', 4);//サイドメニュー生成
  add_action( 'admin_init', 'register_child_theme_settings','admin-head');
}

function register_child_theme_settings() {


  register_setting( 'straight_settings_group', 'top_catchcopy' );
  register_setting( 'straight_settings_group', 'top_description' );
  register_setting( 'straight_settings_group', 'main_image' );

  register_setting( 'straight_settings_group', 'header_menu_title' );
  register_setting( 'straight_settings_group', 'bzb_company_header' );
  register_setting( 'straight_settings_group', 'bzb_contact_header' );
  register_setting( 'straight_settings_group', 'bzb_service_header' );
  register_setting( 'straight_settings_group', 'bzb_contents_header' );
  register_setting( 'straight_settings_group', 'bzb_contents' );
  register_setting( 'straight_settings_group', 'bzb_service' );

  register_setting( 'straight_settings_group', 'use_company_map' );
  register_setting( 'straight_settings_group', 'company_map' );
  register_setting( 'straight_settings_group', 'company' );

  register_setting( 'straight_settings_group', 'company_name' );
  register_setting( 'straight_settings_group', 'company_place' );
  register_setting( 'straight_settings_group', 'company_tel' );
  register_setting( 'straight_settings_group', 'daihyou' );
  register_setting( 'straight_settings_group', 'members' );
  register_setting( 'straight_settings_group', 'sihonkin' );
  register_setting( 'straight_settings_group', 'seturitu' );

  register_setting( 'straight_settings_group', 'bzb_contact_textarea' );
  register_setting( 'straight_settings_group', 'contact_source' );

  register_setting( 'straight_settings_group', 'footer-logo' );
  register_setting( 'straight_settings_group', 'footer_menu_title' );
  register_setting( 'straight_settings_group', 'footer-address' );

}

function child_theme_setting_page() {
?>

<script type="text/javascript">

jQuery('document').ready(function(){
    jQuery('.media-upload').each(function(){
        var rel = jQuery(this).attr("rel");
        jQuery(this).click(function(){
            window.send_to_editor = function(html) {
                html = '<a>' + html + '</a>';
                imgurl = jQuery('img', html).attr('src');
                jQuery('#'+rel).val(imgurl);
                tb_remove();
            }
            formfield = jQuery('#'+rel).attr('name');
            tb_show(null, 'media-upload.php?post_id=0&type=image&TB_iframe=true');
            return false;
        });
    });
});
</script>


  <div class="wrap">
    <h2>テーマ情報設定</h2>

    <form method="post" action="options.php" enctype="multipart/form-data" encoding="multipart/form-data">

<div class="metabox-holder">
<div id="toppage_logo_setting" class="postbox " >
<h3 class='hndle'><span>ファーストビューの設定</span></h3>
  <div class="inside">
    <div class="main">
     <p class="setting_description">ここではファーストビュー（ブラウザで最初に表示される部分）のテキストを設定します。</p>

     <h4>キャッチコピー</h4>
     <p><textarea name="top_catchcopy" id="top_catchcopy" rows="5" cols="80"><?php echo get_option('top_catchcopy');?></textarea></p>
     <p class="setting_description"><small>このサイトのキャッチコピーを入力して下さい。</small></p>

     <h4>サイト説明文</h4>
     <p><textarea name="top_description" id="top_description" rows="5" cols="80"><?php echo get_option('top_description');?></textarea></p>
     <p class="setting_description"><small>このサイトの説明文を入力して下さい。</small></p>

     <h4>背景画像</h4>
     <p class="setting_description"><small>背景画像を設定します。下の「画像をアップロード」ボタンを押して任意の画像を選択してください。</small></p>
     <p><input type="text" id="main_image" name="main_image" class="regular-text" value="<?php echo get_option('main_image');?>" /><a class="media-upload" href="JavaScript:void(0);" rel="main_image"><input class="cmb_upload_button button" type="button" value="画像をアップロードする" /></a></p>
    </div>
  </div>
</div>
</div>


<div class="metabox-holder">
<div id="toppage_logo_setting" class="postbox " >
<h3 class='hndle'><span>クローズアップ記事の設定</span></h3>
  <div class="inside">
    <div class="main">
    <p class="setting_description">ここではクローズアップしたい記事の設定を行います。</p>
      <?php

  settings_fields( 'straight_settings_group' );
  do_settings_sections( 'straight_settings_group' );

  $icon = 'none';
  $title = '';
  $ruby = '';
  $bzb_contents_header_array = get_option('bzb_contents_header');
  if(is_array($bzb_contents_header_array)){
    extract($bzb_contents_header_array) ;
  }

  $list = '<h4>クローズアップ記事アイコン</h4>
  <table>
  <tr>
  <td><label><input type="radio" name="bzb_contents_header[icon]" value="fa fa-folder" '. checked($icon , 'fa fa-folder', false) .'><i class="fa fa-folder"></i>&nbsp;フォルダ</label></td>
  <td><label><input type="radio" name="bzb_contents_header[icon]" value="fa fa-check" ' . checked($icon, 'fa fa-check', false) . '><i class="fa fa-check"></i>&nbsp;チェック</label></td>
  <td><label><input type="radio" name="bzb_contents_header[icon]" value="fa fa-building-o" ' . checked($icon, 'fa fa-building-o', false) . '><i class="fa fa-building-o"></i>&nbsp;ビル</label></td>
  <td><label><input type="radio" name="bzb_contents_header[icon]" value="fa fa-envelope-o" ' . checked($icon, 'fa fa-envelope-o', false) . '><i class="fa fa-envelope-o"></i>&nbsp;メール</label></td>
  <td>&nbsp;</td></tr>';

  $list .= '<tr>
  <td><label><input type="radio" name="bzb_contents_header[icon]" value="fa fa-male" ' . checked($icon, 'fa fa-male', false) . '><i class="fa fa-male"></i>&nbsp;人</label></td>
  <td><label><input type="radio" name="bzb_contents_header[icon]" value="fa fa-star" ' . checked($icon, 'fa fa-star', false) . '><i class="fa fa-star"></i>&nbsp;星</label></td>
  <td><label><input type="radio" name="bzb_contents_header[icon]" value="fa fa-heart" ' . checked($icon, 'fa fa-heart', false) . '><i class="fa fa-heart"></i>&nbsp;ハート</label></td>
  <td><label><input type="radio" name="bzb_contents_header[icon]" value="fa fa-phone" ' . checked($icon, 'fa fa-phone', false) . '><i class="fa fa-phone"></i>&nbsp;電話</label></td>
  <td><label><input type="radio" name="bzb_contents_header[icon]" value="none" ' . checked($icon, 'none', false) . '>不要</label></td></tr></table>';

  $list .= '<h4>クローズアップ記事のタイトル</h4><input type="text" name="bzb_contents_header[title]" value="' . $title . '">
  <h4>クローズアップ記事のサブタイトル</h4><input type="text" name="bzb_contents_header[ruby]" value="' . $ruby . '"><hr>';

  $list .= '<h4>クローズアップ記事に表示したいコンテンツ</h4>';
  $list .= '<p class="setting_description">ここでは表示したい記事・個別ページ、カテゴリーを選びます。もし独自に表示したい場合は「オリジナル」を選択して作成しましょう。</p>';

  echo $list;

  $bzb_contents_array = get_option('bzb_contents');

        // print_r($bzb_contents_array);
        if(is_array($bzb_contents_array)){
          $max_contents_key = max(array_keys($bzb_contents_array));
          extract($bzb_contents_array);
          foreach($bzb_contents_array as $key => $value){
            echo "<div>";
            echo bzb_show_contents_list($key);//既存コンテンツ展開
            echo "</div>";
          }
        }else{
          $max_contents_key = 0;
        }
?>
      <span id="contents_area"></span>
      <p class="add2"><input type="button" value="項目を追加する"></p>
      <script>
          var $ =jQuery.noConflict();
          $(document).ready(function() {

              var count = <?php echo $max_contents_key; ?>;
              $(".add2").click(function() {
                  count = count + 1;

                  $('#contents_area').append('<div><?php echo trim(bzb_make_contents_list());?></div>' );

                  jQuery('.media-upload').each(function(){
                      var rel = jQuery(this).attr("rel");
                      jQuery(this).click(function(){
                          window.send_to_editor = function(html) {
                              html = '<a>' + html + '</a>';
                              imgurl = jQuery('img', html).attr('src');
                              jQuery('#'+rel).val(imgurl);
                              tb_remove();
                          }
                          formfield = jQuery('#'+rel).attr('name');
                          tb_show(null, 'media-upload.php?post_id=0&type=image&TB_iframe=true');
                          return false;
                      });
                  });

                  return false;
              });

              $(document).on('click', '.remove2', function() {
                  $(this).parent().remove();
              });

              //
              $(document).on('click', '.select_contents_list_category', function() {
                  $(this).parent().parent().find('.category_list').css('display', 'block');//ok
                  $(this).parent().parent().find('.post_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.page_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.original_list').css('display', 'none');//ok
              });

              $(document).on('click', '.select_contents_list_post', function() {
                  $(this).parent().parent().find('.category_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.post_list').css('display', 'block');//ok
                  $(this).parent().parent().find('.page_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.original_list').css('display', 'none');//ok
              });

              $(document).on('click', '.select_contents_list_page', function() {
                  $(this).parent().parent().find('.category_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.post_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.page_list').css('display', 'block');//ok
                  $(this).parent().parent().find('.original_list').css('display', 'none');//ok
              });

              $(document).on('click', '.select_contents_list_original', function() {
                  $(this).parent().parent().find('.category_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.post_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.page_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.original_list').css('display', 'block');//ok
              });
          });


      </script>

    </div>
  </div>
</div>
</div>




<div class="metabox-holder">
<div id="service_area_setting" class="postbox " >
<h3 class='hndle'><span>サービス記事設定</span></h3>
  <div class="inside">
    <div class="main">
    <p class="setting_description">ここでは紹介したいサービスに属するページの設定を行います。</p>

<?php
  $icon = 'none';
  $titile = '';
  $ruby = '';
  $bzb_service_header_array = get_option('bzb_service_header');
  if(is_array($bzb_service_header_array)){
    extract($bzb_service_header_array) ;
  }

  $list = '<h4>サービス記事用アイコン</h4>
  <table>
  <tr>
  <td><label><input type="radio" name="bzb_service_header[icon]" value="fa fa-folder" '. checked($icon , 'fa fa-folder', false) .'><i class="fa fa-folder"></i>&nbsp;フォルダ</label></td>
  <td><label><input type="radio" name="bzb_service_header[icon]" value="fa fa-check" ' . checked($icon, 'fa fa-check', false) . '><i class="fa fa-check"></i>&nbsp;チェック</label></td>
  <td><label><input type="radio" name="bzb_service_header[icon]" value="fa fa-building-o" ' . checked($icon, 'fa fa-building-o', false) . '><i class="fa fa-building-o"></i>&nbsp;ビル</label></td>
  <td><label><input type="radio" name="bzb_service_header[icon]" value="fa fa-envelope-o" ' . checked($icon, 'fa fa-envelope-o', false) . '><i class="fa fa-envelope-o"></i>&nbsp;メール</label></td>
  <td>&nbsp;</td></tr>';

  $list .= '<tr>
  <td><label><input type="radio" name="bzb_service_header[icon]" value="fa fa-male" ' . checked($icon, 'fa fa-male', false) . '><i class="fa fa-male"></i>&nbsp;人</label></td>
  <td><label><input type="radio" name="bzb_service_header[icon]" value="fa fa-star" ' . checked($icon, 'fa fa-star', false) . '><i class="fa fa-star"></i>&nbsp;星</label></td>
  <td><label><input type="radio" name="bzb_service_header[icon]" value="fa fa-heart" ' . checked($icon, 'fa fa-heart', false) . '><i class="fa fa-heart"></i>&nbsp;ハート</label></td>
  <td><label><input type="radio" name="bzb_service_header[icon]" value="fa fa-phone" ' . checked($icon, 'fa fa-phone', false) . '><i class="fa fa-phone"></i>&nbsp;電話</label></td>
  <td><label><input type="radio" name="bzb_service_header[icon]" value="none" ' . checked($icon, 'none', false) . '>不要</label></td></tr></table>';

  $list .= '<h4>サービス記事タイトル</h4><input type="text" name="bzb_service_header[title]" value="' . $title . '">
  <h4>サービス記事 サブタイトル</h4><input type="text" name="bzb_service_header[ruby]" value="' . $ruby . '"><hr>';

  $list .= '<h4>サービス記事に表示したいコンテンツ</h4>';
  $list .= '<p class="setting_description">ここでは表示したい記事・個別ページ、カテゴリーを選びます。もし独自に表示したい場合は「オリジナル」を選択して作成しましょう。</p>';

  echo $list;

        $bzb_service_array = get_option('bzb_service');
        //print_r($bzb_service_array);exit;
        if(is_array($bzb_service_array)){
          $max_service_key = max(array_keys($bzb_service_array));
          extract($bzb_service_array);
          foreach($bzb_service_array as $key => $value){
            echo "<div>";
            echo bzb_show_service_list($key);//既存コンテンツ展開
            echo "</div>";
          }
?>


<?php
        }else{
          $max_service_key = 0;
        }
?>
      <span id="service_area"></span>
      <p class="add3"><input type="button" value="項目を追加する"></p>
      <script>
          var $ =jQuery.noConflict();
          $(document).ready(function() {

              var count = <?php echo $max_service_key; ?>;
              $(".add3").click(function() {
                  count = count + 1;

                  $('#service_area').append('<div><?php echo bzb_make_service_list();?></div>' );

                  jQuery('.media-upload').each(function(){
                      var rel = jQuery(this).attr("rel");
                      jQuery(this).click(function(){
                          window.send_to_editor = function(html) {
                              html = '<a>' + html + '</a>';
                              imgurl = jQuery('img', html).attr('src');
                              jQuery('#'+rel).val(imgurl);
                              tb_remove();
                          }
                          formfield = jQuery('#'+rel).attr('name');
                          tb_show(null, 'media-upload.php?post_id=0&type=image&TB_iframe=true');
                          return false;
                      });
                  });

                  return false;
              });

              $(document).on('click', '.remove2', function() {
                  $(this).parent().remove();
              });

              //
              $(document).on('click', '.select_service_list_category', function() {
                  $(this).parent().parent().find('.category_list').css('display', 'block');//ok
                  $(this).parent().parent().find('.post_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.page_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.original_list').css('display', 'none');//ok
              });

              $(document).on('click', '.select_service_list_post', function() {
                  $(this).parent().parent().find('.category_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.post_list').css('display', 'block');//ok
                  $(this).parent().parent().find('.page_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.original_list').css('display', 'none');//ok
              });

              $(document).on('click', '.select_service_list_page', function() {
                  $(this).parent().parent().find('.category_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.post_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.page_list').css('display', 'block');//ok
                  $(this).parent().parent().find('.original_list').css('display', 'none');//ok
              });

              $(document).on('click', '.select_service_list_original', function() {
                  $(this).parent().parent().find('.category_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.post_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.page_list').css('display', 'none');//ok
                  $(this).parent().parent().find('.original_list').css('display', 'block');//ok
              });
          });


      </script>

    </div>
  </div>
</div>
</div>



<div class="metabox-holder">
<div id="toppage_meta_setting" class="postbox " >
<h3 class='hndle'><span>会社情報の設定</span></h3>
  <div class="inside">
    <div class="main">
    <p class="setting_description">ここでは会社情報の設定を行います。</p>
<?php
  $icon = 'none';
  $titile = '';
  $ruby = '';
  $bzb_company_header_array = get_option('bzb_company_header');
  if(is_array($bzb_company_header_array)){
    extract($bzb_company_header_array) ;
  }



  $list = '<h4>会社情報用アイコン</h4>
  <table>
  <tr>
  <td><label><input type="radio" name="bzb_company_header[icon]" value="fa fa-folder" '. checked($icon , 'fa fa-folder', false) .'><i class="fa fa-folder"></i>&nbsp;フォルダ</label></td>
  <td><label><input type="radio" name="bzb_company_header[icon]" value="fa fa-check" ' . checked($icon, 'fa fa-check', false) . '><i class="fa fa-check"></i>&nbsp;チェック</label></td>
  <td><label><input type="radio" name="bzb_company_header[icon]" value="fa fa-building-o" ' . checked($icon, 'fa fa-building-o', false) . '><i class="fa fa-building-o"></i>&nbsp;ビル</label></td>
  <td><label><input type="radio" name="bzb_company_header[icon]" value="fa fa-envelope-o" ' . checked($icon, 'fa fa-envelope-o', false) . '><i class="fa fa-envelope-o"></i>&nbsp;メール</label></td>
  <td>&nbsp;</td></tr>';

  $list .= '<tr>
  <td><label><input type="radio" name="bzb_company_header[icon]" value="fa fa-male" ' . checked($icon, 'fa fa-male', false) . '><i class="fa fa-male"></i>&nbsp;人</label></td>
  <td><label><input type="radio" name="bzb_company_header[icon]" value="fa fa-star" ' . checked($icon, 'fa fa-star', false) . '><i class="fa fa-star"></i>&nbsp;星</label></td>
  <td><label><input type="radio" name="bzb_company_header[icon]" value="fa fa-heart" ' . checked($icon, 'fa fa-heart', false) . '><i class="fa fa-heart"></i>&nbsp;ハート</label></td>
  <td><label><input type="radio" name="bzb_company_header[icon]" value="fa fa-phone" ' . checked($icon, 'fa fa-phone', false) . '><i class="fa fa-phone"></i>&nbsp;電話</label></td>
  <td><label><input type="radio" name="bzb_company_header[icon]" value="none" ' . checked($icon, 'none', false) . '>不要</label></td></tr></table>';

  $list .= '<h4>会社情報のタイトル</h4><input type="text" name="bzb_company_header[title]" value="' . $title . '">
  <h4>会社情報のサブタイトル</h4><input type="text" name="bzb_company_header[ruby]" value="' . $ruby . '">';

  echo $list;

  $use_company_map = get_option('use_company_map');
  $company_map = get_option('company_map');
?>

    <h4>アクセスマップ</h4>

      <textarea name="company_map" id="company_map" rows=3 cols=80><?php echo $company_map;?></textarea><br>
      <label><input type="checkbox" id="use_company_map" name="use_company_map" <?php checked($use_company_map,'1');?> value=1>地図を表示する</label><br>
      こちらにGoogleマップからHTMのソースコードを取得して貼り付けます。詳しくは「<a href="https://support.google.com/maps/answer/3544418?hl=ja">地図を埋め込む</a>」をご確認ください。<br>
<pre>（例）&lt;iframe src="https://www.google.com/maps/embed?pb=◯◯◯◯◯◯・・・・◯◯◯◯◯◯" width="400" height="300" frameborder="0" style="border:0"&gt;&lt;/iframe&gt;</pre>
      <hr>

      <h4>会社情報</h4>
      <p class="setting_description">会社名・電話番号・住所などを入力しましょう。<br>（例）<br>項目名：会社名<br>テキスト：株式会社ルーシー</p>

<?php

    $companies = get_option('company');
    if ( is_array($companies)) {
        foreach( (array)$companies as $key => $company ) {
      $max_key = max(array_keys($companies));
            if ( isset( $company['name'] ) && isset( $company['val'] ) ) {
                echo sprintf('<ul><li>項目名<input type="text" name="company[%1$u][name]" value="%2$s"></li><li>テキスト<input type="text" name="company[%1$u][val]" value="%3$s"></li><li><input class="remove" type="button" value="削除"></li></ul><hr>',
                $key,
                $company['name'],
                $company['val']
                );

            }
        }
    }else{
      $max_key = 0;
      $defalt_name = (isset($company['name']) && $company['name'] !== '') ? $company['name'] : "会社名";
      $defalt_val = (isset($company['val']) && $company['val'] !== '') ? $company['val'] : "株式会社○○○○○○○○";
      echo sprintf('<ul><li>項目名<input type="text" name="company[0][name]" value="%s"></li><li><input type="text" name="company[0][val]" value="%s"></li><li>テキスト<input class="remove" type="button" value="削除"></li></ul><hr>',$defalt_name, $defalt_val);
    }
?>


      <ul id="here"></ul>
      <span class="add"><input type="button" value="項目を追加する"></span>
      <script>
          var $ =jQuery.noConflict();
          $(document).ready(function() {

              var count = <?php echo $max_key;?>;
              $(".add").click(function() {
                  count = count + 1;

                  $('#here').append('<ul><li>項目名<input type="text" name="company['+count+'][name]" value=""></li><li>テキスト<input type="text" name="company['+count+'][val]" value=""></li><li><input class="remove" type="button" value="削除"></li></ul>' );

                  return false;
              });
              $(document).on('click', '.remove', function() {
                  $(this).parent().parent().remove();
              });

              if ($("#use_company_map").is(':checked')) {
                $('#company_map').show('fast');
              }else{
                $('#company_map').hide('fast');
              }


              $('#use_company_map').change(function(){
                  if ($(this).is(':checked')) {
                      $('#company_map').show('fast');
                  } else {
                      $('#company_map').hide('fast');
                  }
              });

          });


      </script>

    </div>
  </div>
</div>
</div>

<div class="metabox-holder">
<div id="toppage_logo_setting" class="postbox " >
<h3 class='hndle'><span>お問い合わせフォーム設定</span></h3>
  <div class="inside">
    <div class="main">
      <p class="setting_description">ここではお問い合わせフォームの設定を行います。</p>

<?php
  $icon = 'none';
  $titile = '';
  $ruby = '';
  $bzb_contact_header_array = get_option('bzb_contact_header');
  if(is_array($bzb_contact_header_array)){
    extract($bzb_contact_header_array) ;
  }

  $list = '<h4>お問い合わせフォーム用アイコン</h4>
   <table>
  <tr>
  <td><label><input type="radio" name="bzb_contact_header[icon]" value="fa fa-folder" '. checked($icon , 'fa fa-folder', false) .'><i class="fa fa-folder"></i>&nbsp;フォルダ</label></td>
  <td><label><input type="radio" name="bzb_contact_header[icon]" value="fa fa-check" ' . checked($icon, 'fa fa-check', false) . '><i class="fa fa-check"></i>&nbsp;チェック</label></td>
  <td><label><input type="radio" name="bzb_contact_header[icon]" value="fa fa-building-o" ' . checked($icon, 'fa fa-building-o', false) . '><i class="fa fa-building-o"></i>&nbsp;ビル</label></td>
  <td><label><input type="radio" name="bzb_contact_header[icon]" value="fa fa-envelope-o" ' . checked($icon, 'fa fa-envelope-o', false) . '><i class="fa fa-envelope-o"></i>&nbsp;メール</label></td>
  <td>&nbsp;</td></tr>';

  $list .= '<tr>
  <td><label><input type="radio" name="bzb_contact_header[icon]" value="fa fa-male" ' . checked($icon, 'fa fa-male', false) . '><i class="fa fa-male"></i>&nbsp;人</label></td>
  <td><label><input type="radio" name="bzb_contact_header[icon]" value="fa fa-star" ' . checked($icon, 'fa fa-star', false) . '><i class="fa fa-star"></i>&nbsp;星</label></td>
  <td><label><input type="radio" name="bzb_contact_header[icon]" value="fa fa-heart" ' . checked($icon, 'fa fa-heart', false) . '><i class="fa fa-heart"></i>&nbsp;ハート</label></td>
  <td><label><input type="radio" name="bzb_contact_header[icon]" value="fa fa-phone" ' . checked($icon, 'fa fa-phone', false) . '><i class="fa fa-phone"></i>&nbsp;電話</label></td>
  <td><label><input type="radio" name="bzb_contact_header[icon]" value="none" ' . checked($icon, 'none', false) . '>不要</label></td></tr></table>';

  $list .= '<p>お問い合わせフォームタイトル</p><input type="text" name="bzb_contact_header[title]" value="' . $title . '">
  <p>お問い合わせフォームサブタイトル</p><input type="text" name="bzb_contact_header[ruby]" value="' . $ruby . '"><br><br>';

  echo $list;

$bzb_contact_textarea = get_option('bzb_contact_textarea');
?>

      <h4>お問い合わせフォーム本文</h4>
      <p class="setting_description">文章とショートコードを入力します。“ショートコード”とは、お問い合わせフォーム用のコードのことです。詳しくは『<a href="http://bazubu.com/wordpress-inquiry-form-24081.html">問い合わせページを作る方法</a>』をご確認ください。</p>
      <?php wp_editor( $bzb_contact_textarea, 'bzb_contact_textarea', array( 'media_buttons'=>true, 'textarea_name'=>'bzb_contact_textarea','textarea_rows'=>10,'tiny_mce'=>true, 'tinymce_adv' => array( 'width' => '600')) ); ?>

    </div>
  </div>
</div>
</div>



<div class="metabox-holder">
<div id="toppage_logo_setting" class="postbox " >
<h3 class='hndle'><span>フッター設定</span></h3>
  <div class="inside">
    <div class="main">
      <p class="setting_description">ここではフッターのロゴとテキストの設定を行います。</p>

      <h4>フッターロゴ</h4>
      <p><input type="text" id="footer-logo" name="footer-logo" class="regular-text" value="<?php echo get_option('footer-logo');?>" /><a class="media-upload" href="JavaScript:void(0);" rel="footer-logo"><input class="cmb_upload_button button" type="button" value="画像をアップロードする" /></a></p>
     <p class="setting_description"><small>ロゴを画像にします。下の「画像をアップロード」ボタンを押して任意の画像を選択してください。このテンプレートでは、300px x 60pxの画像が最も適しています。</small></p>

      <h4>フッターに掲載するテキスト</h4>
      <textarea id="footer-address" class="regular-text" name="footer-address" rows="5" cols="60"><?php echo get_option('footer-address'); ?></textarea>
    </div>
  </div>
</div>
</div>


      <?php submit_button(); ?>
    </form>
  </div>



<?php
}

function bzb_show_contents_list($no){
  $list = '';
  $category = '';
  $post = '';
  $page = '';
  $original = '';
  $original_title = '';
  $button_original_image  = '';
  $button_text = '';
  $button_url = '';
  $ruby = '';
  $catch = '';

  $bzb_contents_array = get_option('bzb_contents');
  // print_r($no);
  extract($bzb_contents_array[$no]);

  $check_cat = (checked($syubetu, 'category', false)) ? "" : "display:none;";
  $check_post = (checked($syubetu, 'post', false)) ? "" : "display:none;";
  $check_page = (checked($syubetu, 'page', false)) ? "" : "display:none;";
  $check_original = (checked($syubetu, 'original', false)) ? "" : "display:none;";

  $list .= '<label><input type="radio" class="select_contents_list_category" id="" name="bzb_contents[' . $no . '][syubetu]" value="category" ' . checked($syubetu, 'category', false) . '>カテゴリ</label><label><input type="radio" class="select_contents_list_post" id="" name="bzb_contents[' . $no . '][syubetu]" value="post" '. checked($syubetu, 'post', false) . '>投稿</label><label><input type="radio" class="select_contents_list_page" id="" name="bzb_contents[' . $no . '][syubetu]" value="page" ' . checked($syubetu, 'page',false) . '>固定ページ</label><label><input type="radio" class="select_contents_list_original" id="" name="bzb_contents[' . $no . '][syubetu]" value="original" '. checked($syubetu, 'original', false) . '>オリジナル</label>';

  $list .= '<div style="' . $check_cat . '" class="category_list"><SELECT size="6" name="bzb_contents[' . $no . '][category]" class="bzb_category" style="height:auto;">';
        $cat_all = get_terms( "category", "fields=all&get=all" );
        foreach($cat_all as $value):
          $list .= '<option value="' . $value->term_id . '"' . selected($category, $value->term_id, false) . '>' . addslashes($value->name) . '</option>';
        endforeach;
  $list .= '</SELECT></div>';



  $list .= '<div style="' . $check_post . '" class="post_list"><SELECT size="6" name="bzb_contents[' . $no . '][post]" class="bzb_page" style="height:auto;">';
        query_posts('post_status=publish&post_type=post&showposts=-1');
        while ( have_posts() ) : the_post();
          $list .= '<OPTION value="' . get_the_ID() . '"'. selected($post, get_the_ID(), false) . '>' . addslashes(get_the_title()) . '</OPTION>';
        //$list .= '<OPTION value="aa">aa</OPTION>';
        endwhile;
        wp_reset_postdata();
  $list .= '</SELECT></div>';

  $list .= '<div style="' . $check_page . '" class="page_list"><SELECT size="6" name="bzb_contents[' . $no . '][page]" class="bzb_post" style="height:auto;">';
        query_posts('post_status=publish&post_type=page&showposts=-1');
        while ( have_posts() ) : the_post();
          $list .= '<OPTION value="' . get_the_ID() . '"' . selected($page, get_the_ID(), false) . '>' . addslashes(get_the_title()) . '</OPTION>';
        //$list .= '<OPTION value="aa">aa</OPTION>';
        endwhile;
        wp_reset_postdata();
  $list .= '</SELECT></div>';

  $list .= '<div style="' . $check_original . '" class="original_list">';
  $list .= '<h4>タイトル</h4><p><input type="text" name="bzb_contents[' . $no . '][original_title]" value="' . $original_title . '"></p>';
  $list .= '<h4>ルビ</h4><p><input type="text" name="bzb_contents[' . $no . '][ruby]" value="' . $ruby . '"></p>';
  $list .= '<h4>キャッチコピー</h4><p><input type="text" name="bzb_contents[' . $no . '][catch]" value="' . $catch . '"></p>';
  $list .= '<h4>コンテンツ</h4><p><textarea name="bzb_contents[' . $no . '][contents]" rows=5 cols=60>' . $contents . '</textarea></p>';
  $list .= '<h4>ボタンテキスト</h4><p><input type="text" name="bzb_contents[' . $no . '][button_text]" value="' . $button_text . '"></p>';
  $list .= '<h4>ボタンURL</h4><p><input type="text" name="bzb_contents[' . $no . '][button_url]" value="' . $button_url . '"></p>';

  $list .= '<h4>メイン画像</h4><p><input type="text" id="bzb_contents_original_image' . $no . '" name="bzb_contents[' . $no . '][original_image]" class="regular-text" value="' . $original_image . '" /><a class="media-upload" href="JavaScript:void(0);" rel="bzb_contents_original_image' . $no . '"><input class="cmb_upload_button button" type="button" value="画像をアップロードする" /></a></p>';

  $list .= "</div>";
  $list .= '<p class="remove2"><input type="button" value="この項目を削除する"></p><hr>';

  return $list;
}



function bzb_make_contents_list(){
  $list = '';

  $list .= '<label><input type="radio" class="select_contents_list_category" id="" name="bzb_contents[' . '\' + count + \'' . '][syubetu]" value="category">カテゴリ</label><label><input type="radio" class="select_contents_list_post" id="" name="bzb_contents[' . '\' + count + \'' . '][syubetu]" value="post">投稿</label><label><input type="radio" class="select_contents_list_page" id="" name="bzb_contents[' . '\' + count + \'' . '][syubetu]" value="page">固定ページ</label><label><input type="radio" class="select_contents_list_original" id="" name="bzb_contents[' . '\' + count + \'' . '][syubetu]" value="original">オリジナル</label>';

  $list .= '<div style="display:none;" class="category_list"><SELECT size="6" name="bzb_contents[' . '\' + count + \'' . '][category]" class="bzb_category" style="height:auto;">';
        $cat_all = get_terms( "category", "fields=all&get=all" );
        foreach($cat_all as $value):
          $list .= '<option value="' . $value->term_id . '">' . addslashes($value->name) . '</option>';
        //$list .= '<OPTION value="aa">aa</OPTION>';
        endforeach;
  $list .= '</SELECT></div>';

  $list .= '<div style="display:none;" class="post_list"><SELECT size="6" name="bzb_contents[' . '\' + count + \'' . '][post]" class="bzb_page" style="height:auto;">';
        query_posts('post_status=publish&showposts=-1');
        while ( have_posts() ) : the_post();
          $list .= '<OPTION value="' . get_the_ID() . '">' . addslashes(get_the_title()) . '</OPTION>';
        //$list .= '<OPTION value="aa">aa</OPTION>';
        endwhile;
        wp_reset_postdata();
  $list .= '</SELECT></div>';

  $list .= '<div style="display:none;" class="page_list"><SELECT size="6" name="bzb_contents[' . '\' + count + \'' . '][page]" class="bzb_post" style="height:auto;">';
        query_posts('post_status=publish&post_type=page&showposts=-1');
        while ( have_posts() ) : the_post();
          $list .= '<OPTION value="' . get_the_ID() . '">' . addslashes(get_the_title()) . '</OPTION>';
        //$list .= '<OPTION value="aa">aa</OPTION>';
        endwhile;
        wp_reset_postdata();
  $list .= '</SELECT></div>';

  $list .= '<div style="display:none;" class="original_list">';
  $list .= '<h4>タイトル</h4><p><input type="text" name="bzb_contents[' . '\' + count + \'' . '][original_title]" value=""></p>';
  $list .= '<h4>ルビ</h4><p><input type="text" name="bzb_contents[' . '\' + count + \'' . '][ruby]" value=""></p>';
  $list .= '<h4>キャッチコピー</h4><p><input type="text" name="bzb_contents[' . '\' + count + \'' . '][catch]" value=""></p>';
  $list .= '<h4>コンテンツ</h4><p><textarea name="bzb_contents[' . '\' + count + \'' . '][contents]" rows=5 cols=60></textarea></p>';
  $list .= '<h4>ボタンテキスト</h4><p><input type="text" name="bzb_contents[' . '\' + count + \'' . '][button_text]" value=""></p>';
  $list .= '<h4>ボタンURL</h4><p><input type="text" name="bzb_contents[' . '\' + count + \'' . '][button_url]" value=""></p>';

  $list .= '<h4>メイン画像</h4><p><input type="text" id="bzb_contents_original_image' . '\' + count + \'' . '" name="bzb_contents[' . '\' + count + \'' . '][original_image]" class="regular-text" value="" /><a class="media-upload" href="JavaScript:void(0);" rel="bzb_contents_original_image' . '\' + count + \'' . '"><input class="cmb_upload_button button" type="button" value="画像をアップロードする" /></a></p>';

  $list .= "</div>";
  $list .= '<p class="remove2"><input type="button" value="この項目を削除する"></p>';

  return $list;
}




function bzb_show_service_list($no){
  $list = '';
  $category = '';
  $post = '';
  $page = '';
  $original = '';
  $original_title = '';
  $contents = '';
  $button_original_image  = '';
  $button_text = '';
  $button_url = '';
  $ruby = '';
  $catch = '';

  $bzb_service_array = get_option('bzb_service');
  extract($bzb_service_array[$no]);

  $check_cat = (checked($syubetu, 'category', false)) ? "" : "display:none;";
  $check_post = (checked($syubetu, 'post', false)) ? "" : "display:none;";
  $check_page = (checked($syubetu, 'page', false)) ? "" : "display:none;";
  $check_original = (checked($syubetu, 'original', false)) ? "" : "display:none;";

  $list .= '<label><input type="radio" class="select_service_list_category" id="" name="bzb_service[' . $no . '][syubetu]" value="category" ' . checked($syubetu, 'category', false) . '>カテゴリ</label><label><input type="radio" class="select_service_list_post" id="" name="bzb_service[' . $no . '][syubetu]" value="post" '. checked($syubetu, 'post', false) . '>投稿</label><label><input type="radio" class="select_service_list_page" id="" name="bzb_service[' . $no . '][syubetu]" value="page" ' . checked($syubetu, 'page',false) . '>固定ページ</label><label><input type="radio" class="select_service_list_original" id="" name="bzb_service[' . $no . '][syubetu]" value="original" '. checked($syubetu, 'original', false) . '>オリジナル</label>';

  $list .= '<div style="' . $check_cat . '" class="category_list"><SELECT size="6" name="bzb_service[' . $no . '][category]" class="bzb_category" style="height:auto;">';
        $cat_all = get_terms( "category", "fields=all&get=all" );
        foreach($cat_all as $value):
          $list .= '<option value="' . $value->term_id . '"' . selected($category, $value->term_id, false) . '>' . addslashes($value->name) . '</option>';
        endforeach;
  $list .= '</SELECT></div>';



  $list .= '<div style="' . $check_post . '" class="post_list"><SELECT size="6" name="bzb_service[' . $no . '][post]" class="bzb_post" style="height:auto;">';
        query_posts('post_status=publish&showposts=-1');
        while ( have_posts() ) : the_post();
          $list .= '<OPTION value="' . get_the_ID() . '"'. selected($post, get_the_ID(), false) . '>' . addslashes(get_the_title()) . '</OPTION>';
        //$list .= '<OPTION value="aa">aa</OPTION>';
        endwhile;
        wp_reset_postdata();
  $list .= '</SELECT></div>';

  $list .= '<div style="' . $check_page . '" class="page_list"><SELECT size="6" name="bzb_service[' . $no . '][page]" class="bzb_page" style="height:auto;">';
        query_posts('post_status=publish&post_type=page&showposts=-1');
        while ( have_posts() ) : the_post();
          $list .= '<OPTION value="' . get_the_ID() . '"' . selected($page, get_the_ID(), false) . '>' . addslashes(get_the_title()) . '</OPTION>';
        //$list .= '<OPTION value="aa">aa</OPTION>';
        endwhile;
        wp_reset_postdata();
  $list .= '</SELECT></div>';

  $list .= '<div style="' . $check_original . '" class="original_list">';
  $list .= '<h4>タイトル</h4><p><input type="text" name="bzb_service[' . $no . '][original_title]" value="' . $original_title . '"></p>';
  $list .= '<h4>ルビ</h4><p><input type="text" name="bzb_service[' . $no . '][ruby]" value="' . $ruby . '"></p>';
  $list .= '<h4>キャッチコピー</h4><p><input type="text" name="bzb_service[' . $no . '][catch]" value="' . $catch . '"></p>';
  $list .= '<h4>コンテンツ</h4><p><textarea name="bzb_service[' . $no . '][service]" rows=5 cols=60>' . $service . '</textarea></p>';
  $list .= '<h4>ボタンテキスト</h4><p><input type="text" name="bzb_service[' . $no . '][button_text]" value="' . $button_text . '"></p>';
  $list .= '<h4>ボタンURL</h4><p><input type="text" name="bzb_service[' . $no . '][button_url]" value="' . $button_url . '"></p>';

  //$list .= '<h4>メイン画像</h4><p><input type="text" id="bzb_service_original_image' . $no . '" name="bzb_service[' . $no . '][original_image]" class="regular-text" value="' . $original_image . '" /><a class="media-upload" href="JavaScript:void(0);" rel="bzb_service_original_image' . $no . '"><input class="cmb_upload_button button" type="button" value="画像をアップロードする" /></a></p>';

  $list .= "</div>";
  $list .= '<p class="remove2"><input type="button" value="この項目を削除する"></p><hr>';
  return $list;
}



function bzb_make_service_list(){
  $list = '';


  $list .= '<label><input type="radio" class="select_service_list_category" id="" name="bzb_service[' . '\' + count + \'' . '][syubetu]" value="category">カテゴリ</label><label><input type="radio" class="select_service_list_post" id="" name="bzb_service[' . '\' + count + \'' . '][syubetu]" value="post">投稿</label><label><input type="radio" class="select_service_list_page" id="" name="bzb_service[' . '\' + count + \'' . '][syubetu]" value="page">固定ページ</label><label><input type="radio" class="select_service_list_original" id="" name="bzb_service[' . '\' + count + \'' . '][syubetu]" value="original">オリジナル</label>';

  $list .= '<div style="display:none;" class="category_list"><SELECT size="6" name="bzb_service[' . '\' + count + \'' . '][category]" class="bzb_category" style="height:auto;">';
        $cat_all = get_terms( "category", "fields=all&get=all" );
        foreach($cat_all as $value):
          $list .= '<option value="' . $value->term_id . '">' . addslashes($value->name) . '</option>';
        //$list .= '<OPTION value="aa">aa</OPTION>';
        endforeach;
  $list .= '</SELECT></div>';



  $list .= '<div style="display:none;" class="post_list"><SELECT size="6" name="bzb_service[' . '\' + count + \'' . '][post]" class="bzb_page" style="height:auto;">';
        query_posts('post_status=publish&post_type=post&showposts=-1');
        while ( have_posts() ) : the_post();
          $list .= '<OPTION value="' . get_the_ID() . '">' . addslashes(get_the_title()) . '</OPTION>';
        //$list .= '<OPTION value="aa">aa</OPTION>';
        endwhile;
        wp_reset_postdata();
  $list .= '</SELECT></div>';

  $list .= '<div style="display:none;" class="page_list"><SELECT size="6" name="bzb_service[' . '\' + count + \'' . '][page]" class="bzb_post" style="height:auto;">';
        query_posts('post_status=publish&post_type=page&showposts=-1');
        while ( have_posts() ) : the_post();
          $list .= '<OPTION value="' . get_the_ID() . '">' . addslashes(get_the_title()) . '</OPTION>';
        //$list .= '<OPTION value="aa">aa</OPTION>';
        endwhile;
        wp_reset_postdata();
  $list .= '</SELECT></div>';

  $list .= '<div style="display:none;" class="original_list">';
  $list .= '<h4>タイトル</h4><p><input type="text" name="bzb_service[' . '\' + count + \'' . '][original_title]" value=""></p>';
  $list .= '<h4>ルビ</h4><p><input type="text" name="bzb_service[' . '\' + count + \'' . '][ruby]" value=""></p>';
  $list .= '<h4>キャッチコピー</h4><p><input type="text" name="bzb_service[' . '\' + count + \'' . '][catch]" value=""></p>';
  $list .= '<h4>コンテンツ</h4><p><textarea name="bzb_service[' . '\' + count + \'' . '][service]" rows=5 cols=60></textarea></p>';
  $list .= '<h4>ボタンテキスト</h4><p><input type="text" name="bzb_service[' . '\' + count + \'' . '][button_text]" value=""></p>';
  $list .= '<h4>ボタンURL</h4><p><input type="text" name="bzb_service[' . '\' + count + \'' . '][button_url]" value=""></p>';

  $list .= '<h4>メイン画像</h4><p><input type="text" id="bzb_service_original_image' . '\' + count + \'' . '" name="bzb_service[' . '\' + count + \'' . '][original_image]" class="regular-text" value="" /><a class="media-upload" href="JavaScript:void(0);" rel="bzb_service_original_image' . '\' + count + \'' . '"><input class="cmb_upload_button button" type="button" value="画像をアップロードする" /></a></p>';

  $list .= "</div>";
  $list .= '<p class="remove2"><input type="button" value="この項目を削除する"></p>';

  return $list;
}



function make_info_4top($value){

$info4top = array('title'=>'', 'content'=>'', 'url'=>'', 'button_text'=>'', 'bzb_ruby'=>'', 'bzb_catch'=>'', 'button_text'=>'', 'button_url'=>'', 'image'=>'', 'service'=>'');
    $syubetu = (isset($value['syubetu'])) ? $value['syubetu'] : "";
    switch($syubetu){
      case 'category' :
        $t_id = get_category( intval($value['category']) )->term_id;
        $description = get_category( intval($value['category']) )->description;
        $cat_option = get_option('cat_' . $t_id);
        $info4top['title'] = get_category( intval($value['category']) )->name;
        $info4top['content'] = $description;
        $info4top['url'] = get_category_link($t_id);
        $info4top['button_text'] = '';
        $info4top['bzb_ruby'] = $cat_option['ruby'];
        $info4top['bzb_catch'] = $cat_option['catch'];
        $image = isset($cat_option['bzb_category_image']) ? $cat_option['bzb_category_image'] : "";
        if(isset($image) && $image !== ''){
          $info4top['image'] = $image;
        }else{
          $info4top['image'] = get_stylesheet_directory_uri() . '/lib/images/noimage.jpg';
        }
      break;

      case 'post' :
        $bzb_post_front_info = get_post_meta($value['post'], 'bzb_post_front_info', true);
        $info4top['title'] = get_the_title($value['post']);
        $info4top['content'] = get_post_meta($value['post'], 'bzb_meta_description', true);
        $info4top['url'] = get_the_permalink($value['post']);
        $info4top['button_text'] = '';

        if(isset($bzb_post_front_info['ruby']) && $bzb_post_front_info['ruby'] !== ''){
          $info4top['bzb_ruby'] = $bzb_post_front_info['ruby'];
        }
        if(isset($bzb_post_front_info['catch']) && $bzb_post_front_info['catch'] !== ''){
          $info4top['bzb_catch'] = $bzb_post_front_info['catch'];
        }
        $thumbnail_id = get_post_thumbnail_id($value['post']);
        $image = wp_get_attachment_image_src( $thumbnail_id, 'mid_thumbnail' );
        if(isset($image[0]) && $image[0] !== ''){
          $info4top['image'] = $image[0];
        }else{
          $info4top['image'] = get_stylesheet_directory_uri() . '/lib/images/noimage.jpg';
        }
      break;

      case 'page' :
        $bzb_post_front_info = get_post_meta($value['page'], 'bzb_post_front_info', true);
        $info4top['title'] = get_the_title($value['page']);
        $info4top['content'] = get_post_meta($value['page'], 'bzb_meta_description', true);
        $info4top['url'] = get_the_permalink($value['page']);
        $info4top['button_text'] = '';
        if(isset($bzb_post_front_info['ruby']) && $bzb_post_front_info['ruby'] !== ''){
          $info4top['bzb_ruby'] = $bzb_post_front_info['ruby'];
        }
        if(isset($bzb_post_front_info['catch']) && $bzb_post_front_info['catch'] !== ''){
          $info4top['bzb_catch'] = $bzb_post_front_info['catch'];
        }
        $thumbnail_id = get_post_thumbnail_id($value['page']);
        $image = wp_get_attachment_image_src( $thumbnail_id, 'mid_thumbnail');
        if(isset($image[0]) && $image[0] !== ''){
          $info4top['image'] = $image[0];
        }else{
          $info4top['image'] = get_stylesheet_directory_uri() . '/lib/images/noimage.jpg';
        }

      break;

      case 'original' :
        $info4top['title'] = $value['original_title'];
        $info4top['content'] = (isset($value['contents'])) ? nl2br($value['contents']) : "";
        $info4top['service'] = (isset($value['service'])) ? nl2br($value['service']) : "";
        $info4top['url'] = '';
        $info4top['bzb_ruby'] = $value['ruby'];
        $info4top['bzb_catch'] = $value['catch'];
        $info4top['button_text'] = $value['button_text'];
        $info4top['button_url'] = $value['button_url'];
        if(isset($value['original_image'] ) && $value['original_image']  !== ''){
          $info4top['image'] = $value['original_image'] ;
        }else{
          $info4top['image'] = '';//default imageを設置可能
        }
      break;
    }//switch

    return $info4top;

}

function footer_social_buttons(){

  $facebook_page_url = get_option('facebook_page_url');
  $twitter_from_db = get_option('twitter_id');
  $googleplus_from_db = get_option('google_publisher');
  $feedly_url = get_bloginfo('rss2_url');

  $footer_social_buttons = '';

  $footer_social_buttons .= '<div id="footer-sns" class="sp-hide">';
  $footer_social_buttons .=  '<ul>';
          if(isset($twitter_from_db) && $twitter_from_db !== ''){
          $footer_social_buttons .= '<li class="header-twitter"><a target="_blank" href="https://twitter.com/' . $twitter_from_db . '"><i class="fa fa-twitter"></i>Twitter</a></li>';
          }
          if(isset($facebook_page_url) && $facebook_page_url !== ''){
            $footer_social_buttons .= '<li class="header-facebook"><a href="' . $facebook_page_url . '" target="_blank"><i class="fa fa-facebook-square"></i>Facebook</a></li>';
          }
          if(isset($googleplus_from_db) && $googleplus_from_db !== ''){
          $footer_social_buttons .= '<li class="header-google"><a target="_blank" href="https://plus.google.com/' . $googleplus_from_db . '"><i class="fa fa-google-plus"></i>Google+</a></li>';
          }
          $footer_social_buttons .= '<li class="header-feedly"><a target="_blank" href="https://feedly.com/i/subscription/feed/' . $feedly_url . '"><i class="fa fa-rss-square"></i>Feedly</a></li>';
        $footer_social_buttons .= '</ul>';
        $footer_social_buttons .= '</div>';

      echo $footer_social_buttons;
}

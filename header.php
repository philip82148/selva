<!DOCTYPE HTML>
<html lang="ja" prefix="og: http://ogp.me/ns#">
<head>
	<meta charset="UTF-8">
	<title><?php bzb_title(); ?></title>
	<meta name="viewport" content="width=device-width,initial-scale=1.0">

<?php 
wp_head();
?>
  <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
  <link href="/wp-content/themes/selva/style2.css" rel="stylesheet">

<?php echo get_option('analytics_tracking_code');?>

</head>

<body <?php body_class();?> itemschope="itemscope" itemtype="http://schema.org/WebPage">

<?php bzb_show_facebook_block(); ?>

<?php if( is_singular('lp') ) { ?>

<div class="lp-wrap">

<header id="lp-header">
  <h1 class="lp-title"><?php wp_title(''); ?></h1>
</header>

<?php }else{ ?>
<header id="header" role="banner" itemscope="itemscope" itemtype="http://schema.org/WPHeader">
  
    <div class="topbar1">
      <?php
          $logo_image = get_option('logo_image');
          $logo_text = get_option('logo_text');
          $logo_class = '';
          if( !empty($logo_image) && get_option('toppage_logo_type') == 'logo_image') :
            $logo_inner = '<img src="'. get_option('logo_image') .'" alt="'.get_bloginfo('name').'" />';
            $logo_class = 'class="imagelogo"';
          else:
            if (!empty($logo_text) && get_option('toppage_logo_type') == 'logo_text') :
              $logo_inner = get_option('logo_text');
            else:
              $logo_inner = get_bloginfo('name');
            endif;
            $logo_inner_desc = '<p class="header-description">'.get_bloginfo('description').'</p>';
          endif;
          $logo_wrap = ( is_front_page() || is_home() ) ? 'h1' : 'p' ;
        ?>
        <<?php echo $logo_wrap; ?> id="logo" <?php echo $logo_class ?> itemprop="headline">
          <a href="<?php echo home_url(); ?>"><?php echo $logo_inner; ?></a><br />
        </<?php echo $logo_wrap; ?>>    <!-- start global nav  -->
        <div class="navigation-button-box">
          <div class="navigation-button1">
            <span>
              逆評定
            </span>
          </div>
          <div class="navigation-button2">
            <span>
              スレッド
            </span>
          </div>
          <div class="navigation-button3">
            <span>  
              慶應Wiki
            </span>
          </div>
          <div class="navigation-button4">
            <span>
              ・・・
            </span>
          </div>
        </div>
        <div class="navigation-right-wrap">
          <div>
          </div>
          <div class="login" style="white-space:nowrap;">
            ログイン
          </div>
          <div class="menu" style="white-space:nowrap;">
            <span>
              メニュー▼
            </span>
          </div>
        </div>
      </div>
      
      
    </div>
    </div>
    <div class="wrap clearfix">
      <div class="topbar2">
        <div id="header-left" class="clearfix">
        <!-- 検索 -->
        <form method="get" action="/" class="search-container">
          <input required type="text" name="s" placeholder="教師名または授業名で検索" class="search-input" value="<?php if(is_search()) echo get_search_query(); ?>"/>
          <button type="submit" class='search-button'><i class="fas fa-search"></i></button>
        </form>
      
        <div id="header-right" class="clearfix">
        

        </div><!-- /header-right -->
        
      </div>
    </div>
    <div class="topbar3">
      <div class="divide-left">
        <div class="top3-box1">
          <a href="mailto:khaos.for.better" style="white-space:nowrap;">メール送信▼</a>
        </div>
        <div class="top3-box" style="white-space:nowrap;">
          オムニバス▼
        </div>
        <div class="top3-box" style="white-space:nowrap;">
          一般授業▼
        </div>
      </div>
      <div class="divide-right">
        <div>
        </div>
        <div class="divide-right-menu">
          機能一覧
        </div>
      </div>
    </div>
</header>
<?php } // if is_singular('lp') ?>


  <nav id="gnav-sp">
    <div class="wrap">
    
    <div class="grid-wrap">
            <div id="header-cont-about" class="grid-3">
        </div>
        <div id="header-cont-content" class="grid-6">
          <h4>ブログコンテンツ</h4>
                <?php
        wp_nav_menu(
          array(
            'theme_location'  => 'global_nav',
            'menu_class'      => 'clearfix',
            'menu_id'         => 'gnav-ul-sp',
            'container'       => 'div',
            'container_id'    => 'gnav-container-sp',
            'container_class' => 'gnav-container'
          )
        );?>
        </div>
        
    </div>
    
    </div>
  </nav>
<?php /*
<?php if( !(is_home() || is_front_page() || is_singular('lp') ) ){ ?>
  
  <div class="breadcrumb-area">
    <div class="wrap">
      <?php bzb_breadcrumb(); ?>
    </div>
  </div>
    
<?php } ?>
*/ ?>



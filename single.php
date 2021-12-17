<?php get_header(); ?>


<div id="content">

<div class="wrap">

  

  <div id="main" <?php bzb_layout_main(); ?> role="main">
    
    <div class="main-inner">
    
    <?php
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
    ?>
        
    <?php 
    global $post;
    $cf = get_post_meta($post->ID);
    $facebook_page_url = '';
    $facebook_page_url = get_option('facebook_page_url');
    $post_cat = '';
    ?>
    <article id="post-<?php the_id(); ?>" <?php post_class(); ?>>

      <header class="post-header">
        <div class="cat-name">
          <span>
            <?php
              $category = get_the_category(); 
              echo $category[0]->cat_name;
            ?>
          </span>
        </div>
        <h1 class="post-title"><?php the_title(); ?></h1>
        <div class="post-sns">
          <?php bzb_social_buttons();?>
        </div>
      </header>

      <div class="post-meta-area">
        <ul class="post-meta list-inline">
          <li class="date"><i class="fa fa-clock-o"></i> <?php the_time('Y.m.d');?></li>
        </ul>
        <ul class="post-meta-comment">
          <li class="author">
            by <?php the_author(); ?>
          </li>
          <li class="comments">
            <i class="fa fa-comments"></i> <span class="count"><?php comments_number('0', '1', '%'); ?></span>
          </li>
        </ul>
      </div>

      <?php if( get_the_post_thumbnail() ) : ?>
      <div class="post-thumbnail">
        <?php the_post_thumbnail(array(1200, 630, true)); ?>
      </div>
      <?php endif; ?>

      <section class="post-content">
        <?php
          the_content();

          $args = array(
            'before' => '<div class="pagination">',
            'after' => '</div>',
            'link_before' => '<span>',
            'link_after' => '</span>'
          );

          wp_link_pages($args);
        ?>
      </section>

      <footer class="post-footer">


        <?php echo bzb_social_buttons();?>
        <ul class="post-footer-list">
          <li class="cat"><i class="fa fa-folder"></i> <?php the_category(', ');?></li>
          <?php 
          $posttags = get_the_tags();
          if($posttags){ ?>
          <li class="tag"><i class="fa fa-tag"></i> <?php the_tags('');?></li>
          <?php } ?>
        </ul>
      </footer>

      <?php echo bzb_get_cta($post->ID); ?>

    <?php if( is_active_sidebar('under_post_area') ){ ?>
    <div class="post-share">
      <?php dynamic_sidebar('under_post_area');?>
    </div>
    <?php } ?>

    </article>

 <?php bzb_show_avatar();?>


    <?php comments_template( '', true ); ?>

        <?php

				endwhile;

			else :
		?>

    <p>投稿が見つかりません。</p>

    <?php
			endif;
		?>


    </div><!-- /main-inner -->
  </div><!-- /main -->

<?php get_sidebar(); ?>

</div><!-- /wrap -->

</div><!-- /content -->


<?php //記事ページのみに構造化データを出力
if ( is_single()): ?>
  <?php
    //サムネイルを取得
    $thumbnail_id = get_post_thumbnail_id($post);
    $imageobject = wp_get_attachment_image_src( $thumbnail_id, 'full' );
    if( !$imageobject ){
      $imageobject[0] = get_template_directory_uri() .'/lib/images/noimage.jpg';
    }
    $logo_image = get_option('logo_image');
    if( !$logo_image ){
      $logo_image = get_template_directory_uri() .'/lib/images/masman.png';
    }
  ?>
  <script type="application/ld+json">
  {
    "@context": "http://schema.org",
    "@type": "BlogPosting",
    "mainEntityOfPage":{
      "@type":"WebPage",
      "@id":"<?php the_permalink(); ?>"
    },
    "headline":"<?php the_title(); ?>",
    "image": [
      "<?php echo $imageobject[0]; ?>"
    ],
    "datePublished": "<?php echo get_date_from_gmt(get_post_time('c', true), 'c');?>",
    "dateModified": "<?php echo get_date_from_gmt(get_post_modified_time('c', true), 'c');?>",
    "author": {
      "@type": "Person",
      "name": "<?php the_author(); ?>"
    },
    "publisher": {
      "@type": "Organization",
      "name": "<?php bloginfo('name'); ?>",
      "logo": {
        "@type": "ImageObject",
        "url": "<?php echo $logo_image; ?>"
      }
    },
    "description": "<?php echo get_the_excerpt(); ?>"
  }
  </script>
<?php endif; ?>


<?php get_footer(); ?>



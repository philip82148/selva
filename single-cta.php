<?php get_header(); ?>


<div id="content">

<div class="wrap">
  <div id="main" <?php bzb_layout_main(); ?>>

    <?php bzb_breadcrumb(); ?>

    <div class="main-inner">



    <article id="post-sample" class="post">
      <header class="post-header">
        <h1 class="post-title">投稿タイトル</h1>
        <ul class="post-meta list-inline">
          <li class="date"><i class="fa fa-clock-o"></i> <?php echo date('Y年m月d日'); ?></li>
          <li class="cat"><i class="fa fa-folder"></i> <a href="">カテゴリー</a></li>
        </ul>
      </header>



      <section class="post-content">
        <p>投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル</p>
        <p>投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル</p>
        <p>投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル</p>
        <p>投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル投稿サンプル</p>
      </section>

      <?php
        if ( have_posts() ) :
          global $post;
          $cf = get_post_meta($post->ID, 'bzb_cta');
          // print_r($cf);


          extract($cf[0]);

          while ( have_posts() ) : the_post();
          ?>


          <!-- CTA BLOCK -->
          <div class="post-cta">
          <h4 class="cta-post-title"><?php the_title(); ?></h4>
          <div class="post-cta-inner">
            <div class="cta-post-content clearfix">
             <div class="post-cta-cont">
            <div class="post-cta-img" style="float:right;"><?php the_post_thumbnail('medium'); ?></div>
            <?php the_content(); ?>
            <br clear="both">
            <p class="post-cta-btn">
              <a class="button" href="<?php echo $select_button_url; ?>"
              <?php if( !empty($select_button_cvtag ) ): ?>
                onClick="javascript:ga('send', 'pageview', '/<?php echo $select_button_cvtag;?>');"
              <?php endif; ?>
              >
                <?php echo $select_button; ?>
              </a>
            </p>
            </div>
            </div>
          </div>
          </div>
          <!-- END OF CTA BLOCK -->

          <?php

          endwhile;

        else :
      ?>

      <p>投稿が見つかりません。</p>

      <?php
        endif;
      ?>



      <div class="post-share">
        <?php dynamic_sidebar('freearea1');?>
      </div>

      <footer class="post-footer">
        <ul class="post-footer-list">
          <li class="cat"><i class="fa fa-folder"></i> <?php the_category(', ');?></li>
          <?php
          $posttags = get_the_tags();
          if($posttags){ ?>
          <li class="tag"><i class="fa fa-tag"></i> <?php the_tags('');?></li>
          <?php } ?>
        </ul>
      </footer>


    <aside class="post-author">
      <div class="clearfix">
        <div class="post-author-img">
          <div class="inner">
            <img src="<?php echo get_option('original_avatar'); ?>">
          </div>
        </div><!-- /post-author-img -->
        <div class="post-author-meta">
          <h4><?php the_author_meta('display_name'); ?> <span>( <a href="https://plus.google.com/<?php get_option('googleplus'); ?>?rel=author">Google+</a> )</span></h4>
          <p><?php the_author_meta('description');?></p>
        </div>
      </div>
    </aside>

    </article>

    </div><!-- /main-inner -->
  </div><!-- /main -->

<?php get_sidebar(); ?>

</div><!-- /wrap -->

</div><!-- /content -->

<?php get_footer(); ?>

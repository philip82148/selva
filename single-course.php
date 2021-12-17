<?php get_header(); ?>

<div id="content">


<div class="wrap clearfix">

	<div id="main" <?php bzb_layout_main(); ?> role="main" itemprop="mainContentOfPage">

    	<div class="main-inner">
<?php
if ( have_posts() ) :
	while ( have_posts() ) : the_post();
		global $post;

		$course_id = (int)$post->post_name;

		increment_page_views(false, $course_id);
		store_next_page(false, $course_id);
		$course = fetch_omnibus_course_data($course_id);

		if(empty($course)) {
?>
	    <article id="post-404" class="cotent-none post" itemscope="itemscope" itemtype="http://schema.org/CreativeWork">
    		<header class="post-header">
				<h1 class="post-title">あなたがアクセスしようとしたページは削除されたかURLが変更されています。</h1>
			</header>
			<section class="post-content" itemprop="text">
				<?php get_template_part('content', 'none'); ?>
			</section>
		</article>
<?php
			continue;
		}
?>

			<article id="post-<?php the_id(); ?>" <?php post_class(); ?>>

				<div class="profile_">
					<div class="profile-left-wrapper">
						<div class="profile-left">
<?php	if(count_star_ratings() > 1) : ?>
							<canvas width="720" height="720" class="chart-rating"></canvas>
<?php	endif; ?>
						</div>
					</div>

					<div class="profile-right-wrapper">
						<div class="profile-right-top">
							<h1><?php echo $course['title']; ?></h1>
							<div class="course-detail">
								<div>
									(<span class="course-target-department">
										<?php echo str_replace('/', '</span><span class="course-target-department">', $course['target_department']); ?>
									</span>)
								</div>
								<div class="course-when">
									<?php echo "【{$course['campus']}】【{$course['semester']}】"; // キャンパス名、学期
											if($course['day_and_period']) echo "<div class='day-and-period'>【{$course['day_and_period'] }】</div>"; // 曜日時限 ?>
								</div>
							</div>
							<div class="rating-guide">
								<a class="a-button show-ratings" onclick="javascript: jQuery('.profile-right-center .profile-list').toggleClass('close');">教員ごとの評価を見る</a>
								<a class="a-button jump-comment" href="#profile-comment">口コミへ飛ぶ</a>
							</div>
						</div>

						<div class="profile-right-center">
							<h3>担当教員</h3>
							<ul class="profile-list close">
<?php	foreach($course['lecturers'] as $lecturer) : ?>
								<li class="profile-list-item">
									<a href="/profile/<?php echo $lecturer['lecturer_id'] ?>" target="_blank">
										<b><?php echo $lecturer['lecturer_name']; ?></b>
									</a>

<?php
				echo_ratings($course_id, $lecturer['lecturer_id'], $lecturer['ratings'], true);
?>

								</li>
<?php	endforeach; ?>
							</ul>
<?php	if(count($course['similar_courses'])) : ?>
							<div class="similar-courses">
								<h3>同じ名前で他の設置課程・学期の授業</h3>
								<ul class="profile-list">
<?php		foreach($course['similar_courses'] as $similar_course) : ?>
									<li>
										<a href="/course/<?php echo $similar_course['course_id']; ?>" target="_blank">
											(<span class="course-target-department">
												<?php echo str_replace('/', '</span><span class="course-target-department">', $similar_course['target_department']); ?>
											</span>)
											<?php echo "【{$similar_course['campus']}】【{$similar_course['semester']}】"; // キャンパス名、学期
											  if($similar_course['day_and_period']) echo "【{$similar_course['day_and_period'] }】"; // 曜日時限 ?>
										</a>
									</li>
<?php		endforeach; ?>
								</ul>
							</div>
<?php	endif; ?>
						</div>
						<div class="profile-right-bottom">
							<div class="summary-rating">
								<div class="post-views">視聴回数:<?php echo $course['post_views']; ?>回</div>
<?php
		echo_ratings($course_id, NULL, $course['ratings'], false, true);
		echo_rating_commons();
?>
							</div>
						</div>
					</div>
				</div>
				<div id="profile-comment" class="profile-comment">
					<h4>口コミ</h4>
					<?php comments_template( '', true ); ?>
				</div>
			</article>

<?php
	endwhile;
endif;
?>

		</div><!-- /main-inner -->
  
	</div><!-- /main -->

<?php get_sidebar(); ?>

</div><!-- /wrap -->

</div><!-- /content -->

<?php get_footer(); ?>

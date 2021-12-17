<?php get_header(); ?>

<div id="content">


<div class="wrap clearfix">

	<div id="main" <?php bzb_layout_main(); ?> role="main" itemprop="mainContentOfPage">

	    <div class="main-inner">
<?php
if ( have_posts() ) :
	while ( have_posts() ) : the_post();
		global $post;

		$lecturer_id = (int)$post->post_name;

		increment_page_views(true, $lecturer_id);
		store_next_page(true, $lecturer_id);
		$lecturer = fetch_lecturer_data($lecturer_id);

		if(empty($lecturer)) {
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
<?php	if($lecturer['image_url']) : ?>
							<img src="<?php echo $lecturer['image_url'];?>"/>
<?php	endif;
		if(count_star_ratings() > 1) : ?>
							<canvas width="720" height="720" class="chart-rating"></canvas>
<?php	endif; ?>
						</div>
					</div>

					<div class="profile-right-wrapper">
						<div class="profile-right-top">
							<h1><?php echo $lecturer['lecturer_name']; ?></h1>
<?php	if($lecturer['faculty']) : ?>
							<span class="profile-faculty"><?php echo $lecturer['faculty']; ?>所属</span>
<?php	endif; ?>
							<div class="rating-guide">
								<a class="a-button jump-comment" href="#profile-comment">口コミへ飛ぶ</a>
							</div>
						</div>

						<div class="profile-right-center">

<?php
		for($i = 0; $i < 2; $i++) :
			$courses = $lecturer['courses'];
			$is_omnibus = false;
			if($i === 0) :
?>
							<ul class="profile-list">
<?php
			else :
				if(count($lecturer['omnibus_courses']) === 0) break;
				$courses = $lecturer['omnibus_courses'];
				$is_omnibus = true;
?>
							<details class='omnibus-courses'>
								<summary><b>オムニバス授業</b></summary>
								<ul class="profile-list">
<?php
			endif;

			foreach($courses as $course) :
?>

								<li class="profile-list-item">

									<div class="item-title">
<?php		if($is_omnibus) : ?>
										<a href="/course/<?php echo $course['course_id'] ?>/" target="_blank">
											<b><?php echo $course['title']; ?></b>
										</a>
<?php		else : ?>
										<b><?php echo $course['title']; ?></b>
<?php		endif; ?>
										<a class="item-sub-title">
											(<div class="item-sub-title-label">設置課程…</div><span class="target-department"><?php echo str_replace('/', '</span><span class="target-department">', $course['target_department']); ?></span>)
										</a>
									</div>
									<div class="item-details">
										<?php echo "【{$course['campus']}】【{$course['semester']}】"; // キャンパス名、学期
											  if($course['day_and_period']) echo "<div class='day-and-period'>【{$course['day_and_period'] }】</div>"; // 曜日時限 ?>
									</div>

<?php
			if(!$is_omnibus)
				echo_ratings($course['course_id'], $lecturer_id, $course['ratings']);
?>

								</li>


<?php
			endforeach;

			if($i === 0) :
?>
							</ul>
<?php
			else :
?>
								</ul>
							</details>
<?php
			endif;
		endfor;

		echo_rating_commons();
?>

						</div>
						<div class="profile-right-bottom">
							<div class="summary-rating">
								<div class="post-views">視聴回数:<?php echo $lecturer['post_views']; ?>回</div>
								<div class="ratings-wrap">

<?php
		// 星の表示
		if(count_star_ratings()) {
?>

									<div class="performance-rating"><?php echo_performance_rating($lecturer['average_rating_cache']); ?></div>
									<div class="star-ratings">

<?php
			for($rating_no = 1; $rating_no <= 3; $rating_no++) {
				if(!should_use_star_rating($rating_no)) continue;
?>

										<div class="rating-label <?php echo "rating$rating_no"; ?>"><?php echo get_star_rating_label($rating_no); ?></div>
										<?php echo_stars("rating$rating_no", 0, 0); ?>

<?php
			}
?>
									</div>

<?php
		}
?>

								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="profile-comment" class="profile-comment">
					<h4>口コミ</h4>

<?php
		// comment.phpで使う
		function generate_course_select() {
			global $lecturer;

			$selector = '<div class="selectdiv"><select name="course_id"><option value="" selected>授業を特定しない</option>';
			$prev_option = '';
			$is_same_as_prev = false;
			foreach($lecturer['courses'] as $i => $course) {
				$option = '';
				if($i+1 < count($lecturer['courses'])
						&& strcmp($course['title'], $lecturer['courses'][$i+1]['title']) == 0) {
					// 次と名前が同じならば学期と曜日を含める
					$option = $course['title'] . '【' . $course['semester'] . '】【' . $course['day_and_period'] . '】';
					$is_same_as_prev = true;
				} else if($is_same_as_prev) {
					// 前と名前が同じなので学期と曜日を含める
					$option = $course['title'] . '【' . $course['semester'] . '】【' . $course['day_and_period'] . '】';
					$is_same_as_prev = false;
				} else {
					// 前とも後ろとも同じ名前でない
					$option = $course['title'];
				}
				// 前と全く同じ選択肢ならば追加を見送る
				if(strcmp($option, $prev_option) === 0) continue;
				$selector .= "<option value='{$course['course_id']}'>$option</option>";
				$prev_option = $option;
			}
			$selector .= '</select></div>';
			return $selector;
		}
		comments_template( '', true );
?>

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

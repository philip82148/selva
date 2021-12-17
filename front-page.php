<?php get_header(); ?>


<img src="http://khaosbbs.com/wp-content/uploads/2021/12/KHAOSへようこそ！-2.png" class="img-size">


<div id="content">

<div id="main">
<div class="main-inner">

	<div class="front-loop">
 		<h2><i class="fa fa-clock-o"></i>最近の投稿</h2>
		<div class="wrap">
		    <div class="front-loop-cont">

<?php
$comments = retrieve_recent_comments();
foreach($comments as $comment) :
?>

					<a class="recent-comment" href="<?php echo "/{$comment['post_type']}/{$comment['post_name']}"; ?>">	
						<?php echo mb_substr(strip_tags($comment['comment_content']), 0, 40); ?>
					</a>

<?php endforeach; ?>

		    </div><!-- /front-root-cont -->
		</div><!-- /wrap -->
	</div><!-- /recent_post_content -->


    <div class="front-loop">

	    <h2><i class="fa fa-clock-o"></i>閲覧数の多い授業</h2>
		<div class="wrap top-view-posts">

			<h3 class="faculty-category">総合</h3>
			<div class="front-loop-cont">
<?php
$lecturers = retrieve_many_views_courses();
foreach($lecturers as $lecturer) : ?>
				
				<a class="top-view-post" href="/profile/<?php echo $lecturer['lecturer_id']; ?>">
					<?php echo $lecturer['lecturer_name']; ?>
					<div class="performance-rating"><?php echo_performance_rating($lecturer['average_rating_cache']); ?></div>
					<div class="post-views"><?php echo $lecturer['post_views']; ?>回視聴</div>
				</a>
				
<?php endforeach; ?>
			</div>

			<div class="front-loop-sep">

<?php
// $faculties = retrieve_faculties();
$faculties = ['文学部', '経済学部', '法学部', '商学部', '医学部', '理工学部', '看護医療学部', '薬学部', '環境情報学部', '総合政策学部'];
foreach($faculties as $faculty) :
?>
			
				<div class="front-loop-sep-box">
					<h3 class="faculty-category"><?php echo $faculty; ?></h3>

<?php
	$lecturers = retrieve_top_page_view_lecturers($faculty);
	foreach($lecturers as $lecturer) : 
?>

					<a class="top-view-post" href="/profile/<?php echo $lecturer['lecturer_id']; ?>">
						<?php echo $lecturer['lecturer_name']; ?>
						<div class="performance-rating"><?php echo_performance_rating($lecturer['average_rating_cache']); ?></div>
						<div class="post-views"><?php echo $lecturer['post_views']; ?>回視聴</div>
					</a>

<?php	endforeach; ?>

				</div>

<?php endforeach; ?>

			</div>

		</div><!-- /wrap -->
	</div><!-- /front-loop -->

</div><!-- /main-inner -->
</div><!-- /main -->
  
</div><!-- /content -->
<?php wp_reset_query(); ?>
<?php get_footer(); ?>
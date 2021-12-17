<?php

function echo_whiteboard($page) {
    if($page['course_id'] <= 2) : // 教員ページ
?>

    <a href="/profile/<?php echo $page['lecturer_id']; ?>/" target="_blank">
        <div class="wp-block-group is-style-whiteboard1">
            <div class="wp-block-group__inner-container">
                <h2><?php echo $page['lecturer_name']; ?></h2>
                <span class="performance-rating"><?php echo_performance_rating($page['average_rating_cache']); ?></span>
            </div>
        </div>
        <div class="whiteboard-details">
<?php if($page['faculty']) : ?>
            <div><?php echo $page['faculty']; ?>所属</div>
<?php endif; ?>
            <div><span>視聴回数:</span><span><?php echo $page['post_views']; ?></span></div>
<?php	if($page['title']) : ?>
            <div><span>担当授業:</span><span><?php echo $page['title'] ?>等</span></div>
<?php	endif; ?>
        </div>
    </a>

<?php else : // 授業ページ ?>

    <a href='/course/<?php echo $page['course_id']; ?>/' target="_blank">
        <div class="wp-block-group is-style-whiteboard1">
            <div class="wp-block-group__inner-container">
                <h3><?php echo $page['title']; ?>(オムニバス授業)</h3>
                <span class="performance-rating"><?php echo_performance_rating($page['average_rating_cache']); ?></span>
            </div>
        </div>
        <div class="whiteboard-details">
<?php if($page['target_department']) : ?>
            <div><span>設置課程:</span><span><?php echo $page['target_department']; ?></span></div>
<?php endif; ?>
            <div><span>視聴回数:</span><span><?php echo $page['post_views']; ?></span></div>
        </div>
    </a>

<?php
    endif;
}

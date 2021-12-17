<?php

$query=get_search_query();
$start_ns = hrtime(true);
$pages = search($query);
$end_ns = hrtime(true);
//store_next_page(true, 1);
get_header();
$result_count = count($pages);

?>

<div class="search-meta">検索結果 <?php echo $result_count; ?>件(<?php echo round(($end_ns - $start_ns) / 1000000000, 3); ?>秒)</div>
<div class="wrap clearfix">

    <div class="separate-two-category">
		<form id="filter-form">
			<div class="filter-lecturer">
                <input id="acd-check1" class="acd-check" type="checkbox">
                    <label class="acd-label" for="acd-check1">
                        <h5>先生で絞る</h5>
                    </label>
                    <div class="acd-content">
                        <div id="faculty">
					        <h6>所属学部</h6>
				        </div>
                        <div id="average-rating">
					        <h6>評価</h6>
				        </div>
                    </div>
			</div>
			<div class="filter-course">
                <input id="acd-check2" class="acd-check" type="checkbox">
                    <label class="acd-label" for="acd-check2">
                        <h5>授業で絞る</h5>
                    </label>
                    <div class="acd-content">
                        <div id="degree-program">
                            <h6>設置課程</h6>
                        </div>
                        <div id="faculty-or-graduate-school-program">
                            <h6>設置学部・研究科</h6>
                        </div>
                        <div id="semester">
                            <h6>学期</h6>
                        </div>
                        <div id="day-and-period">
                            <h6>曜日・時限</h6>
                        </div>
                        <div id="campus">
                            <h6>キャンパス</h6>
                        </div>
                    </div>
			</div>
		</form>

<?php if($result_count) : ?>

        <div class="search-right-wrapper">

            <div class="search-banner">
<?php   if($result_count >= 500): ?>
            検索結果が多すぎます。最初の500件のみ表示します。
<?php   endif; ?>
            </div>

            <ol class="search-result-list">

<?php
        $search_result_id = 0;
        foreach($pages as $page) :
            if($search_result_id >= 500) break;
            $page['course_id'] = $page['search_course_id'];

			// 教員ページ
        	if($page['course_id'] <= 2) :
?>

                <li id="search-result-<?php echo $search_result_id++; ?>" class="search-result" data-faculty="<?php echo $page['faculty']; ?>" data-average_rating="<?php echo_performance_rating($page['average_rating_cache']); ?>"
                        data-target_department="<?php echo $page['target_department']; ?>" data-semester="<?php echo $page['semester']; ?>" data-day_and_period="<?php echo $page['day_and_period']; ?>" data-campus="<?php echo $page['campus']; ?>">
                    <?php echo_whiteboard($page); ?>
                </li>

<?php       else : // 授業ページ ?>

                <li id="search-result-<?php echo $search_result_id++; ?>" class="search-result" data-faculty="オムニバス授業" data-average_rating="<?php echo_performance_rating($page['average_rating_cache']); ?>"
                        data-target_department="<?php echo $page['target_department']; ?>" data-semester="<?php echo $page['semester']; ?>" data-day_and_period="<?php echo $page['day_and_period']; ?>" data-campus="<?php echo $page['campus']; ?>">
                    <?php echo_whiteboard($page); ?>
                </li>

<?php
			endif;
		endforeach;
?>

            </ol>

        </div>

<?php else : ?>

        <div>
            <h1>検索結果は出てきませんでした！</h1>
            <p>対応しますので、下記のフォームにお書きください</p>
            <div class="center"><iframe src="https://docs.google.com/forms/d/e/1FAIpQLSfN_Zsm3dPLvX11trYLwEIPnWhMB944O7Nogxs2XOw8lLJYtA/viewform?embedded=true" width="700" height="520" frameborder="0" marginheight="0" marginwidth="0">読み込んでいます…</iframe></div>
        </div>

<?php endif; ?>

    </div>
</div>    

<?php get_footer(); ?>
<?php

function echo_5stars() {
?>

	<span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>

<?php
}

function echo_stars(string $class, float $stars, ?int $count=NULL) : void {
?>

	<div class="stars-rating <?php echo $class; ?>">

		<div class="stars"><?php echo_5stars(); ?>
			(<span class="stars-summary"><?php if($stars != 0) printf('%1.1f', $stars); else echo '--'; if(isset($count)) echo "、{$count}件"; ?></span>)
		</div>

		<div class="stars" style="width: <?php echo $stars; ?>em;"><?php echo_5stars(); ?>
		</div>

	</div>

<?php
}

function echo_performance_rating(float $stars) : void {
	if($stars < 1)  echo '--';
	else if($stars < 1.5) echo 'D';
	else if($stars < 2.5) echo 'C';
	else if($stars < 3.5) echo 'B';
	else if($stars < 4.5) echo 'A';
	else                  echo 'S';
}

// 評価をechoする関数
function echo_ratings(int $course_id, ?int $lecturer_id, array $ratings, bool $is_omnibus=false, bool $show_performance_rating=false) : void {
	// 引数が不正な値である
	if($course_id <= 2) return;
	if(empty($ratings)) return;

	if(!isset($lecturer_id)) $lecturer_id = 0;

	// ユーザーの評価を取得
	$user_ratings = [];
	if($is_omnibus) {
		$user_ratings = fetch_user_ratings($course_id, $lecturer_id);
	} else {
		$user_ratings = fetch_user_ratings($course_id);
	}
?>

	<div class="ratings-wrap">

<?php
	// 星の表示
	if(count_star_ratings()) {
		if($show_performance_rating) {
?>
		<div class="performance-rating"></div>
<?php
		}

		if(count_star_ratings() > 1) {
?>
		<div class="rating-header"><span></span><span>あなたの評価</span></div>
<?php
		}
?>

		<div class="star-ratings">

<?php
		for($rating_no = 1; $rating_no <= 3; $rating_no++) {
			if(!should_use_star_rating($rating_no)) continue;

			// 平均の星の数と合計の計算
			$count = $average = 0;
			for($i = 1; $i <= 5; $i++) {
				$count += $ratings["rating$rating_no"][$i];
				$average += $ratings["rating$rating_no"][$i] * $i;
			}
			if($count == 0) {
				$average = 0;
			} else {
				$average /= $count;
			}

			// ユーザーの分は抜いておく
			$user_stars = $user_ratings["rating$rating_no"];
			if($user_stars) $ratings["rating$rating_no"][$user_stars] -= 1;
?>

			<div class="rating-label <?php echo "rating$rating_no"; ?>"><?php echo get_star_rating_label($rating_no); ?></div>
			<div class="rating-wrap" data-course_id="<?php echo $course_id; ?>" data-lecturer_id="<?php echo $lecturer_id; ?>" data-is_omnibus="<?php echo $is_omnibus; ?>"
					data-rating_no="<?php echo $rating_no; ?>" data-others_ratings1="<?php echo $ratings["rating$rating_no"][1]; ?>"
					data-others_ratings2="<?php echo $ratings["rating$rating_no"][2]; ?>" data-others_ratings3="<?php echo $ratings["rating$rating_no"][3]; ?>"
					data-others_ratings4="<?php echo $ratings["rating$rating_no"][4]; ?>" data-others_ratings5="<?php echo $ratings["rating$rating_no"][5]; ?>"
					data-user_rating="<?php echo $user_stars; ?>">

				<?php echo_stars('overall-rating', $average, $count); ?>
				<span class="rating-header-2"><?php if(count_star_ratings() === 1) echo 'あなたの評価:' ?></span>
				<?php echo_stars('user-rating', $user_stars); ?>

			</div>

<?php
		}
?>

		</div>

<?php
	}

	// タグの表示
	if(should_use_tag_ratings()) {
?>

		<div class="rating-tags" data-course_id="<?php echo $course_id; ?>" data-lecturer_id="<?php echo $lecturer_id; ?>" data-is_omnibus="<?php echo $is_omnibus; ?>">

<?php foreach($ratings['rating_tags'] as $tag => $count) : ?>

			<button class="rating-tag<?php if(in_array($tag, $user_ratings['rating_tags'])) echo ' user-selected'; elseif($count) echo ' active'; ?>" data-tag="<?php echo $tag; ?>" data-count="<?php echo $count; ?>"
					data-is_default="<?php echo is_default_tag($tag); ?>">
				<?php echo $tag; ?><span class="pushed-count"><?php echo $count ?></span>
			</button>

<?php endforeach; ?>

			<form class="add-rating-tag">
				<input type="text" class="add-rating-tag-input" list="add-rating-tag-list" placeholder="タグを追加">
				<button class="add-rating-tag-btn">追加</button>
			</form>
		</div>

<?php
	}
?>

	</div>

<?php
}

// 浮き上がる評価等をechoする。あとでjavascriptで呼び出すので中身は入っていない
// 最初は表示に関係ないところに置いておく
function echo_rating_commons() : void {
	global $wpdb;
?>

    <div class="overlay-rating">
        <div class="stars-rating">
            <div class="stars">
                <?php echo_5stars(); ?>(<span class="stars-summary"></span>)
            </div>
            <div class="stars">
                <?php echo_5stars(); ?>
            </div>
        </div>
        <div class="meters">

<?php for($i = 1; $i <= 5; $i++) : ?>

			<div class='meter-label'><?php echo "星{$i}つ"; ?></div>
			<div class='meter'><div class='meter-bar'></div></div>
			<div class='meter-percent'></div>

<?php endfor; ?>

        </div>
    </div>

	<datalist id="add-rating-tag-list">

<?php
	$ids_and_tags = $wpdb->get_results(
		"SELECT	*
		   FROM	{$wpdb->prefix}selva_rating_tags
		  WHERE	rating_tag LIKE '#%'",
		'ARRAY_A'
	) ?? [];
	
	foreach($ids_and_tags as $id_and_tag) {
?>

		<option value="<?php echo $id_and_tag['rating_tag']; ?>"></option>

<?php
	}
?>

	</datalist>
	
<?php
}

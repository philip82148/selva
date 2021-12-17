<?php

add_action('admin_menu', 'add_selva_option_initial_setting');
function add_selva_option_initial_setting() {
    add_submenu_page('selva_initial_setting', 'Selvaオプション初期設定', 'オプション初期設定', 'manage_options', 'selva_option_initial_setting', 'selva_option_initial_setting_menu', 1);
}

function selva_option_initial_setting_menu() {
require_once(__DIR__ . '/admin-common.php');

function echo_star_forms() {
?>
	<div id="star-forms">
<?php
    for($rating_no = 1; $rating_no <= 3; $rating_no++) {
        $label = get_option(get_star_rating_label_option_name($rating_no), '');
?>
		<form class="star-form" method="post">
			<input class="rating-no" type="hidden" name="rating_no" value="<?php echo $rating_no; ?>">
			<input class="star-from" type="hidden" name="star_from" value="<?php echo $label; ?>">
			<label>星の評価<?php echo $rating_no; ?>：<input class="star-to" type="text" name="star_to" value="<?php echo $label; ?>"></label>
            <input type="hidden" name="use_star_rating" value="no">
            <label><input type="checkbox" class="use-star-rating-input" name="use_star_rating" value="yes" <?php if(get_option(get_use_star_rating_option_name($rating_no)) === 'yes') echo 'checked'; ?>>有効化</label>
		</form>
<?php
    }
?>
	</div>
<?php
}

function echo_tag_change_form($rating_tag) {
?>
	<form class="tag-change-form" method="post">
        <input class="tag-from" type="hidden" name="tag_from" value="<?php echo $rating_tag; ?>">
		<label><?php echo $rating_tag ?>：<input class="tag-to" type="text" name="tag_to" value="<?php echo $rating_tag; ?>"></label>
	</form>
<?php
}

function echo_tag_change_forms() {
    global $wpdb;

    $rating_tags = $wpdb->get_col(
        "SELECT	rating_tag
           FROM	{$wpdb->prefix}selva_rating_tags"
    );
?>
	<div class="rating-tag-list">

<?php foreach($rating_tags as $rating_tag) echo_tag_change_form($rating_tag); ?>

	</div>
<?php
}

// 何か入力値があれば一回につき一つだけ処理して返答を返す
if(isset($_POST['organize'])) {
    if($_POST['organize'] === 'yes') {
        $start_ns = hrtime(true);
        $result = organize_courses();
        $end_ns = hrtime(true);

        $message = round(($end_ns - $start_ns) / 1000000000, 3) . "秒で授業データのまとめに成功しました。";
        if(!empty($result['error_message']))
            $message = "授業データまとめ：{$result['error_message']}";

		if(isset($result['updated_count'])) {
            $message .= "(更新された授業の数：{$result['updated_count']}";

            if(isset($result['deleted_count']))
                $message .= "、削除された授業の数：{$result['deleted_count']}";

			$message .= ")";
        }

        echo_message($message);
    }
    return;
}

// 何か入力値があれば一回につき一つだけ処理して返答を返す
if(isset($_POST['break'])) {
    if($_POST['break'] === 'yes') {
        $start_ns = hrtime(true);
        $result = break_courses();
        $end_ns = hrtime(true);

        $message = round(($end_ns - $start_ns) / 1000000000, 3) . "秒で授業データの分解に成功しました。";
        if(!empty($result['error_message']))
            $message = "授業データ分解：{$result['error_message']}";

		if(isset($result['inserted_count'])) {
            $message .= "(追加された授業の数：{$result['inserted_count']}、" . 
            			"更新された授業の数：{$result['updated_count']}";

            if(isset($result['deleted_count']))
                $message .= "、削除された授業の数：{$result['deleted_count']}";

			$message .= ")";
        }

        echo_message($message);
    }
    return;
}

if(isset($_POST['rating_no'])) {
    $rating_no = (int)$_POST['rating_no'];
    if($rating_no < 1 || $rating_no > 3) return;

    $to = $_POST['star_to'] ?? '';
    $from = $_POST['star_from'] ?? '';
    $use_star_rating = (isset($_POST['use_star_rating']) && $_POST['use_star_rating'] === 'yes') ? true : false;

    // 違わなかったら何もしない
    if($use_star_rating === should_use_star_rating($rating_no) && $to === $from) {
        echo_message('何も起こりませんでした。');
        echo_star_forms();
        return;
    }

    // 違ったら表示する
    if($use_star_rating !== should_use_star_rating($rating_no)) {
        if($use_star_rating) {
            if(update_option(get_use_star_rating_option_name($rating_no), 'yes')) {
                echo_message("星の評価{$rating_no}の有効化に成功しました。");
            } else {
                echo_message("星の評価{$rating_no}の有効化に失敗しました。");
            }
        } else {
            if(update_option(get_use_star_rating_option_name($rating_no), 'no')) {
                echo_message("星の評価{$rating_no}の無効化に成功しました。");
            } else {
                echo_message("星の評価{$rating_no}の無効化に失敗しました。");
            }
        }
    }
    
    // 違ったら表示する
    if($to !== $from) {
        if($to) {
            if(update_option(get_star_rating_label_option_name($rating_no), $to)) {
                if($from) {
                    echo_message("星の評価{$rating_no}のラベルの{$from}から{$to}への変更に成功しました。");
                } else {
                    echo_message("星の評価{$rating_no}のラベルの{$to}への設定に成功しました。");
                }
            } else {
                if($from) {
                    echo_message("星の評価{$rating_no}のラベルの{$from}から{$to}への変更に失敗しました。");
                } else {
                    echo_message("星の評価{$rating_no}のラベルの{$to}への設定に失敗しました。");
                }
            }
        } else {
            if(delete_option(get_star_rating_label_option_name($rating_no))) {
                echo_message("星の評価{$rating_no}({$from})のラベルの削除に成功しました。");
            } else {
                echo_message("星の評価{$rating_no}({$from})のラベルの削除に失敗しました。");
            }
        }
	}

	echo_star_forms();
	return;
}

if(isset($_POST['use_tag_ratings'])) {
    if($_POST['use_tag_ratings'] === 'yes') {
        if(update_option(SELVA_USE_TAG_RATINGS_OPTION_NAME, 'yes')) {
            echo_message("タグの評価の有効化に成功しました。", ['use_tag_ratings' => get_option(SELVA_USE_TAG_RATINGS_OPTION_NAME)]);
        } else {
            echo_message("タグの評価の有効化に失敗しました。", ['use_tag_ratings' => get_option(SELVA_USE_TAG_RATINGS_OPTION_NAME)]);
        }
    } else if($_POST['use_tag_ratings'] === 'no') {
        if(update_option(SELVA_USE_TAG_RATINGS_OPTION_NAME, 'no')) {
            echo_message("タグの評価の無効化に成功しました。", ['use_tag_ratings' => get_option(SELVA_USE_TAG_RATINGS_OPTION_NAME)]);
        } else {
            echo_message("タグの評価の無効化に失敗しました。", ['use_tag_ratings' => get_option(SELVA_USE_TAG_RATINGS_OPTION_NAME)]);
        }
    }

	return;
}

if(isset($_POST['tag_from'])) {
	global $wpdb;

	$to = $_POST['tag_to'];
	$from = $_POST['tag_from'];

	if($to) {
		if(preg_match(SELVA_SEARCH_NONTARGET_REGEXP, preg_replace('/^#/', '', $to))) {
			echo_message("「{$from}」から「{$to}」への変更に失敗しました。変更先の文字列に記号を含むことはできません。");
			return;
		}

		$has_succeeded = $wpdb->query(
			$wpdb->prepare(
				"UPDATE	{$wpdb->prefix}selva_rating_tags
					SET	rating_tag=%s
				  WHERE	rating_tag=%s",
				$to,
				$from
			)
		);

		if($has_succeeded === false) {
			echo_message(mysql_last_error("「{$from}」から「{$to}」への変更に失敗しました。"));
		} else {
			echo_message("「{$from}」から「{$to}」への変更に成功しました。");
		}
	} else {
		$has_succeeded = $wpdb->query(
			$wpdb->prepare(
				"DELETE	FROM {$wpdb->prefix}selva_rating_tags
				  WHERE	rating_tag=%s",
				$from
			)
		);
	
		if($has_succeeded === false) {
			echo_message(mysql_last_error("「{$from}」の削除に失敗しました。"));
		} else {
			echo_message("「{$from}」の削除に成功しました。");
		}
	}

	echo_tag_change_forms();
	return;
}

if(isset($_POST['new_tag'])) {
	$new_tag_ids = convert_rating_tags([$_POST['new_tag']], true, true, true);

	if($new_tag_ids) {
        echo_message("「{$_POST['new_tag']}」の追加に成功しました。");
    } else {
        echo_message(mysql_last_error("「{$_POST['new_tag']}」の追加に失敗しました。"));
    }

    echo_tag_change_forms();
	return;
} ?>

<script type="text/javascript">
(function($) {
$('document').ready(function(){

function getStarSubmitAjax($form) {
    return getSimpleSubmitAjax($form, 'ラベル変更信号の送信中にエラーが発生しました。')
    .then(function(html) {
        console.log(html);
        const $newStarForms = $(html).find('#star-forms');
        if(!$newStarForms.length) return;

        const $currentStarForms = $('#star-forms');
        $newStarForms.insertAfter($currentStarForms);
        $currentStarForms.remove();

		extractDataAndNoticeMessage($newStarForms, html);
    });
}

function getTagChangeSubmitAjax($form) {
    return getSimpleSubmitAjax($form, 'タグ変更信号の送信中にエラーが発生しました。')
    .then(function(html) {
        const $newList = $(html).find('.rating-tag-list');
        if(!$newList.length) return;

        const $currentList = $('.rating-tag-list');
        $newList.insertAfter($currentList);
        $currentList.remove();

		extractDataAndNoticeMessage($('#new-tag-form'), html);
	});
}

function getNewTagSubmitAjax() {
    return getSimpleSubmitAjax($('#new-tag-form'), 'タグ追加信号の送信中にエラーが発生しました。')
    .then(function(html) {
        const $newList = $(html).find('.rating-tag-list');
        if(!$newList.length) return;

        const $currentList = $('.rating-tag-list');
        $newList.insertAfter($currentList);
        $currentList.remove();
		$('.new-tag').val('');
    });
}

$('#organize-form').submit(function(e) {
    // 送信ボタン二重押下禁止
    $(this).find('.submit-button').attr('disabled', true);

    removeMessages($(this));
    showSpinner($(this));

	const this_ = this;

    getSimpleSubmitAjax($(this), '授業データをまとめる信号の送信中にエラーが発生しました。')
    .always(function() {
        removeSpinner($(this_));
		$(this_).find('.submit-button').attr('disabled', false);
    });

    return false;
});

$('#break-form').submit(function(e) {
    // 送信ボタン二重押下禁止
    $(this).find('.submit-button').attr('disabled', true);

    removeMessages($(this));
    showSpinner($(this));

	const this_ = this;

    getSimpleSubmitAjax($(this), '授業データを分解する信号の送信中にエラーが発生しました。')
    .always(function() {
        removeSpinner($(this_));
		$(this_).find('.submit-button').attr('disabled', false);
    });

    return false;
});

$('#star-forms-wrap').on('change', '.use-star-rating-input', function() {
    const $noticeTo = $('#star-forms');

    removeMessages($noticeTo);
    showSpinner($noticeTo);

    getStarSubmitAjax($(this).closest('.star-form'))
    .always(function() {
        removeSpinner($noticeTo);
    });
});

$('#star-forms-wrap').on('submit', '.star-form', function() {
	const newLabel = $(this).find('.star-to').val();

	if(newLabel.length > 6) {
		alert('ラベルは6文字以下でなければなりません。');
		return false;
	}

    const $noticeTo = $('#star-forms');

	removeMessages($noticeTo);
    showSpinner($noticeTo);

    getStarSubmitAjax($(this))
    .always(function() {
        removeSpinner($noticeTo);
    });

	return false;
});

$('#use-tag-ratings').on('change', function() {
    const $noticeTo = $('#new-tag-form');
    removeMessages($noticeTo);
    showSpinner($noticeTo);

    const this_ = this;

    getSimpleSubmitAjax($(this), 'タグの評価の有効/無効化信号の送信中にエラーが発生しました。')
    .always(function(html) {
        removeMessages($(this_));
        removeSpinner($noticeTo);

        const data = extractDataAndNoticeMessage($noticeTo, html);
        $(this_).find('#use-tag-ratings-input').prop('checked', data['use_tag_ratings'] === 'yes' ? true : false);
    });
});

$('#tag-forms').on('submit', '.tag-change-form', function() {
	const newTag = $(this).find('.tag-to').val();
	const newUnsharpedTag = newTag.replace(/^#/, '');

	if(newTag === '#') return false;

	if(newUnsharpedTag.length > 20 || newUnsharpedTag.match(/[^ぁ-んァ-ヶｱ-ﾝーa-zA-Z0-9一-龠ａ-ｚＡ-Ｚ０-９\-\r]/) !== null) {
		alert('タグは20文字以下でなければならず、先頭の「#」以外に記号は使えません。');
		return false;
	}

    const $noticeTo = $('#new-tag-form');

	removeMessages($noticeTo);
    showSpinner($noticeTo);

    getTagChangeSubmitAjax($(this))
    .always(function() {
        removeSpinner($noticeTo);
    });

	return false;
});

$('#new-tag-form').submit(function(e) {
	const newUnsharpedTag = $(this).find('.new-tag').val().replace(/^#/, '');

	if(newUnsharpedTag.length === 0) return false;

	if(newUnsharpedTag.length > 20 || newUnsharpedTag.match(/[^ぁ-んァ-ヶｱ-ﾝーa-zA-Z0-9一-龠ａ-ｚＡ-Ｚ０-９\-\r]/) !== null) {
		alert('タグは20文字以下でなければならず、記号は使えません。');
		return false;
	}

	removeMessages($(this));
    showSpinner($(this));

    const this_ = this;

    getNewTagSubmitAjax()
    .always(function() {
        removeSpinner($(this_));
    });

	return false;
});

// インデックス作成フォーム
$('#create_indexes').on('change', function() {
    toggleIndexSubmit();
});
$('#index-form').submit(function(e) {
    // 送信ボタン二重押下禁止
    $(this).find('.submit-button').attr('disabled', true);
    
    removeMessages($(this));
    showSpinner($(this));

	const this_ = this;

    getIndexSubmitAjax()
    .always(function() {
        removeSpinner($(this_));
        toggleIndexSubmit();
    });

    return false;
});
$('#index-form .submit-button').attr('disabled', true);

});
})(jQuery);
</script>

<div class="wrap">
    <div class="postbox">
        <h3 class="hndle">授業データをまとめる</h3>
        <div class="inside">
            <div class="main">
                <form id="organize-form" method="post">
					<h4>「授業名」、「学期」、「キャンパス」、「教員名」が全く同じ授業をまとめて一つの授業にする</h4>
                    <input type="hidden" name="organize" value="yes">
                    <p>「授業名」、「学期」、「キャンパス」、「教員名」が同じ授業の「設置課程」と「曜日時限」をまとめて一つの授業にします。</p>
                    <p>ただし、まとめ方は次のようなステップを踏みます。</p>
                    <ol>
                        <li>「授業名」、「学期」、「曜日時限」、「キャンパス」、「教員名」が全く同じ授業の「設置課程」をまとめる。</li>
                        <li>「授業名」、「設置課程」、「学期」、「キャンパス」、「教員名」が全く同じ授業の「曜日時限」をまとめる。</li>
                    </ol>
                    <p>例えば下記のようにまとめられます。</p>
                    <p>『「美術Ⅰ」/「学士 医 医 医」/「春学期」/「火２」/「日吉」/「山田太郎」』
                        <br>『「美術Ⅰ」/「学士 商 商 商」/「春学期」/「火２」/「日吉」/「山田太郎」』
                        <br>『「美術Ⅰ」/「学士 医 医 医」/「春学期」/「木２」/「日吉」/「山田太郎」』
						<br>『「美術Ⅰ」/「学士 商 商 商」/「春学期」/「木２」/「日吉」/「山田太郎」』
                    	<br>『「美術Ⅰ」/「学士 商 商 商」/「春学期」/「金２」/「日吉」/「山田太郎」』
						<br>→『「美術Ⅰ」/「学士 医 医 医/学士 商 商 商」/「春学期」/「火２、木２」/「日吉」/「山田太郎」』、『「美術Ⅰ」/「学士 商 商 商」/「春学期」/「金２」/「日吉」/「山田太郎」』
                        <br>※「金２」は「学士 商 商 商」のみのため、別の授業としてまとめられる。</p>
					<p><b>※タグの評価データはまとめる前の一つの授業以外のデータが失われます。</b>
						<br>※星の評価データはまとめられます。
						<br>※オムニバス授業の場合は全ての教員名が比較されます。
						<br>※ユーザーの行動データはまとめられず、削除されます。
                    	<br>※この操作は取り消せません。</p>
                    <p class="submit">
                        <input type="submit" name="submit" class="submit-button button button-primary" value="まとめる">
                    </p>
                </form>
            </div>
        </div>
    </div>

    <div class="postbox">
        <h3 class="hndle">授業データを分解する</h3>
        <div class="inside">
            <div class="main">
                <form id="break-form" method="post">
					<h4>「設置課程」、「曜日時限」を分解して複数の授業にする</h4>
                    <input type="hidden" name="break" value="yes">
                    <p>「設置課程」と「曜日時限」が複数あるものを複数の授業に分解します(ただし、「曜日時限」は「、」でのみ分解されます)。</p>
                    <p>例えば下記のように分解されます。</p>
                    <p>『「美術Ⅰ」/「学士 医 医 医/学士 商 商 商」/「春学期」/「火２、木２」
						<br>→『「美術Ⅰ」/「学士 医 医 医」/「春学期」/「火２」/「日吉」/「山田太郎」』
                        <br>『「美術Ⅰ」/「学士 商 商 商」/「春学期」/「火２」/「日吉」/「山田太郎」』
                        <br>『「美術Ⅰ」/「学士 医 医 医」/「春学期」/「木２」/「日吉」/「山田太郎」』
						<br>『「美術Ⅰ」/「学士 商 商 商」/「春学期」/「木２」/「日吉」/「山田太郎」』
                    	<br>『「美術Ⅰ」/「学士 商 商 商」/「春学期」/「金２」/「日吉」/「山田太郎」』</p>
					<p>※『「授業名」、「学期」、「キャンパス」、「教員名」が全く同じ授業をまとめて一つの授業にする』
						と反対の動作になります。(ただし、『「授業名」、「学期」、「キャンパス」、「教員名」が全く同じ授業をまとめて一つの授業にする』よりも時間が掛かります。)
						<br>※評価データ、ユーザーの行動データは一つの授業に集中します。
						<br>※分解した授業がすでにある場合はそちらのデータが残ります。
                    	<br>※この操作は取り消せません。</p>
                    <p class="submit">
                        <input type="submit" name="submit" class="submit-button button button-primary" value="分解する">
                    </p>
                </form>
            </div>
        </div>
    </div>

	<div class="postbox">
        <h3 class="hndle">星の評価</h3>
        <div class="inside">
            <div id="star-forms-wrap" class="main">
                <p>星の評価のラベルの編集ができます。</p>
                <?php echo_star_forms(); ?>
            </div>
        </div>
    </div>

    <div class="postbox">
        <h3 class="hndle">タグ編集</h3>
        <div class="inside">
            <div id="tag-forms" class="main">
				<p>タグの編集ができます。タグは先頭に「#」をつけずに書くと、デフォルトで表示されるタグとなり、押した人が0人でも表示されます。
					空にすると、そのタグは削除されます。削除されたタグは元に戻すことはできません。</p>
                <form id="use-tag-ratings">
                    <input type='hidden' name="use_tag_ratings" value="no">
                    <p><label><input type="checkbox" id="use-tag-ratings-input" name="use_tag_ratings" value="yes" <?php if(should_use_tag_ratings()) echo 'checked'; ?>>タグの評価を有効化する</label></p>
                </form>
				<?php echo_tag_change_forms(); ?>
                <form id="new-tag-form" method="post">
					<label>追加：<input type="text" class="new-tag" name="new_tag"></label>
                </form>
            </div>
        </div>
    </div>

    <div class="postbox">
        <h3 class="hndle">インデックス作成</h3>
        <div class="inside">
            <div class="main">
                <form id="index-form" method="post" data-have_indexes="<?php echo have_indexes() ? 'yes' : 'no'; ?>">
                    <p>現在の状態：<span id="have_indexes"><?php echo have_indexes() ? 'インデックス作成済み' : 'インデックスなし'; ?></span></p>
                    <p>
                        <input class="create_indexes" type="hidden" name="create_indexes" value="no">
                        <input id="create_indexes" class="create_indexes" type="checkbox" name="create_indexes" value="yes" <?php if(have_indexes()) echo 'checked'; ?>><label for="create_indexes">インデックスを作成する</label>
                    </p>
                    <p>検索高速化のために追加のインデックス(索引)を作成します。</p>
                    <p>この操作はデータベースのディスク容量を消費します。</p>
                    <p class="submit">
                        <input type="submit" name="submit" class="submit-button button button-primary" value="変更を保存する">
                    </p>
                </form>
            </div>
        </div>
    </div>

</div>

<?php
}

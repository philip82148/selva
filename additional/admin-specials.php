<?php

add_action('admin_menu', 'add_selva_special_functions');
function add_selva_special_functions() {
    add_submenu_page('selva_initial_setting', 'Selva便利機能', '便利機能', 'manage_options', 'selva_special_functions', 'selva_special_function_menu', 2);
}

function selva_special_function_menu() {
require_once(__DIR__ . '/admin-common.php');

function echo_select(bool $is_lecturer, string $col_name) {
    global $wpdb;

    $table_name = '';
    $where = '';
    if($is_lecturer) {
        $table_name = "{$wpdb->prefix}selva_lecturers";
        $where = " WHERE lecturer_id>2";
    } else {
        $table_name = "{$wpdb->prefix}selva_courses";
        $where = " WHERE course_id>2";
    }

    $data = $wpdb->get_results(
        "SELECT $col_name, COUNT(*) AS count
           FROM $table_name
         $where
       GROUP BY $col_name
       ORDER BY $col_name",
       'ARRAY_A'
    );
?>

    <select id="<?php echo $col_name; ?>-from" class="select-from" name="<?php echo $col_name; ?>_from">

<?php foreach($data as $item) { ?>
    <option value="<?php echo $item[$col_name]; ?>" class="option-from" data-count="<?php echo $item['count']; ?>">「<?php echo htmlspecialchars($item[$col_name]); ?>」(<?php echo $item['count']; ?>件)</option>
<?php } ?>

    </select>

<?php
}

function echo_course_select() {
    global $wpdb;

    $data = $wpdb->get_results(
        "SELECT DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS updated_at_, COUNT(*) AS count
		   FROM {$wpdb->prefix}selva_courses
          WHERE course_id>2
       GROUP BY updated_at_
       ORDER BY updated_at_",
       'ARRAY_A'
    );
?>

    <select id="course-updated_at-from" class="select-from" name="course_updated_at_from">

<?php foreach($data as $item) { ?>
    <option value="<?php echo $item['updated_at_']; ?>" class="option-from" data-count="<?php echo $item['count']; ?>">「<?php echo htmlspecialchars($item['updated_at_']); ?>」(<?php echo $item['count'] ?>件)</option>
<?php } ?>

    </select>

<?php
}

function echo_lecturer_select() {
    global $wpdb;

    $data = $wpdb->get_results(
        "SELECT DATE_FORMAT(lecturers.updated_at, '%Y-%m-%d %T') AS updated_at_, COUNT(*) AS lecturer_count,
                SUM((SELECT COUNT(*)
                   FROM {$wpdb->prefix}selva_courses AS sub_courses
				  WHERE sub_courses.lecturer_id=lecturers.lecturer_id
				  		AND sub_courses.course_id>2)) AS course_count
		   FROM {$wpdb->prefix}selva_lecturers AS lecturers
          WHERE lecturers.lecturer_id>2
       GROUP BY updated_at_
       ORDER BY updated_at_",
       'ARRAY_A'
    );
?>

    <select id="lecturer-updated_at-from" class="select-from" name="lecturer_updated_at_from">

<?php foreach($data as $item) { ?>
    <option value="<?php echo $item['updated_at_']; ?>" class="option-from" data-count="<?php echo $item['lecturer_count']; ?>" data-count2="<?php echo $item['course_count']; ?>">「<?php echo htmlspecialchars($item['updated_at_']); ?>」(教員<?php echo $item['lecturer_count']; ?>名、授業<?php echo $item['course_count'] ?>件)</option>
<?php } ?>

    </select>

<?php
}

function change_field(bool $is_lecturer, string $col_name) {
    global $wpdb;

    $to = $_POST["{$col_name}_to"];
    $from = $_POST["{$col_name}_from"];

    if(strpos($to, '"') !== false) {
        echo_message("「{$from}」から「{$to}」への変更に失敗しました。変更先の文字列に「\"」を含むことはできません。");
        return;
    }
    if(strpos($to, "'") !== false) {
        echo_message("「{$from}」から「{$to}」への変更に失敗しました。変更先の文字列に「\'」を含むことはできません。");
        return;
    }

    $table_name = '';
    if($is_lecturer) {
        $table_name = "{$wpdb->prefix}selva_lecturers";
    } else {
        $table_name = "{$wpdb->prefix}selva_courses";
    }

    $has_succeeded = $wpdb->query(
        $wpdb->prepare(
            "UPDATE $table_name
                SET $col_name=%s
              WHERE $col_name=%s",
            $to,
            $from
        )
    );

    if($has_succeeded === false) {
        echo_message(mysql_last_error("「{$from}」から「{$to}」への変更に失敗しました。"));
    } else {
        echo_message("「{$from}」から「{$to}」への変更に成功しました。");
    }

    echo_select($is_lecturer, $col_name);
}

// 何か入力値があれば一回につき一つだけ処理して返答を返す
if(isset($_POST['faculty_from'])) {
    change_field(true, 'faculty');
    return;
}

if(isset($_POST['class_from'])) {
    change_field(true, 'class');
    return;
}

if(isset($_POST['target_department_from'])) {
    change_field(false, 'target_department');
    return;
}

if(isset($_POST['semester_from'])) {
    change_field(false, 'semester');
    return;
}

if(isset($_POST['day_and_period_from'])) {
    change_field(false, 'day_and_period');
    return;
}

if(isset($_POST['course_updated_at_from'])) {
	$start_ns = hrtime(true);
	$deleted_count = delete_courses($_POST['course_updated_at_from'], $_POST['course_operator']);
	$end_ns = hrtime(true);

	$message = round(($end_ns - $start_ns) / 1000000000, 3) . "秒で最終更新日時が{$_POST['course_updated_at_from']}";
    $message .= $_POST['course_operator'] === 'by' ? 'より前の' : ($_POST['course_operator'] === 'at' ? 'と同じ' : 'より後の');
    $message .= '授業データを削除しました。';
    if(is_string($deleted_count)) {
	    $message = '授業データ削除：' . $deleted_count;
    } else {
        $message .= '(削除された授業データの数：' . $deleted_count . ')';
    }

    echo_message($message);

    echo_course_select();
    echo_lecturer_select();

    return;
}

if(isset($_POST['lecturer_updated_at_from'])) {
	$start_ns = hrtime(true);
	$deleted_count = delete_lecturers($_POST['lecturer_updated_at_from'], $_POST['lecturer_operator']);
	$end_ns = hrtime(true);

	$message = round(($end_ns - $start_ns) / 1000000000, 3) . "秒で最終更新日時が{$_POST['lecturer_updated_at_from']}";
    $message .= $_POST['lecturer_operator'] === 'by' ? 'より前の' : ($_POST['lecturer_operator'] === 'at' ? 'と同じ' : 'より後の');
    $message .= '教員データを削除しました。';
    if(is_string($deleted_count)) {
	    $message = '教員データ削除：' . $deleted_count;
    } else {
        $message .= '(削除されたデータの数：教員' . $deleted_count['lecturer'] . '件、授業' . $deleted_count['course'] . '件)';
    }

    echo_message($message);

    echo_course_select();
    echo_lecturer_select();

    return;
}

if(isset($_POST['uninstall'])) {
    if($_POST['uninstall'] === 'yes') {
        $start_ns = hrtime(true);
        $error_message = delete_all_data();
        $end_ns = hrtime(true);

        // メッセージ作成
        $message = round(($end_ns - $start_ns) / 1000000000, 3) . '秒で全てのデータの削除に成功しました。';
        if(!empty($error_message))
            $message = '全データ削除：' . $error_message;

        $uninstall_message = $message;
    }
} ?>

<script type="text/javascript">
(function($) {
$('document').ready(function(){

function getFieldSubmitAjax($form) {
    return getSimpleSubmitAjax($form, 'フィールド変更信号の送信中にエラーが発生しました。')
    .then(function(html) {
        const $currentSelect = $form.find('.select-from');
        const to = $form.find('.input-to').val();
        const from = $currentSelect.val();

        const $newSelect = $(html).find('.select-from');
        if(!$newSelect.length) return;

        if($newSelect.find('[value="' + from + '"]').length) {
            $newSelect.val(from); // 失敗している
        } else {
            $newSelect.val(to); // 成功している
        }
        $newSelect.insertAfter($currentSelect);
        $currentSelect.remove();
    });
}

function applyInput() {
    const $fieldForm = $(this).closest('.field-form');
    $fieldForm.find('.input-to').val($(this).val());
}

function updateCount($form) {
    let count = 0;
	let count2 = 0;
	const is_lecturer = $form.attr('id') === 'lecturer-updated-at-form';

    if($form.find('.operator').val() === 'at') {
		const $checkedOption = $form.find('.option-from:checked');
		if($checkedOption.length) {
			count = parseInt($checkedOption.data('count'));
			if(is_lecturer)
				count2 = parseInt($checkedOption.data('count2'));
		}
    } else {
        $form.find('.option-from').each(function() {
            switch($form.find('.operator').val()) {
                case 'by':
                    if($(this).attr('value') < $form.find('.select-from').val()) {
                        count += parseInt($(this).data('count'));
						if(is_lecturer)
							count2 += parseInt($(this).data('count2'));
					}
                    break;
                case 'from':
                    if($(this).attr('value') > $form.find('.select-from').val()) {
						count += parseInt($(this).data('count'));
						if(is_lecturer)
							count2 += parseInt($(this).data('count2'));
					}
                    break;
            }
        });
    }

	if(is_lecturer) {
		$form.find('.count').text('教員' + String(count) + '件、授業' + String(count2) + '件');
	} else {
		$form.find('.count').text(String(count) + '件');
	}
}

function getUpdatedAtSubmitAjax($form) {
    return getSimpleSubmitAjax($form, '削除信号の送信中にエラーが発生しました。')
    .then(function(html) {
		const $currentSelect = $('.select-from');
		$currentSelect.each(function() {
			const $newSelect = $(html).find('#' + $(this).attr('id'));
			if(!$newSelect.length) return;

			// あれば今と同じ時間を選択しておく
			if($newSelect.find('[value="' + $(this).val() + '"]').length)
				$newSelect.val($(this).val());

			$(this).after($newSelect);
			$(this).remove();
			updateCount($newSelect.closest('.updated-at-form'));
		});
    });
}

$('.field-form').on('change', '.select-from', applyInput);
$('.input-to').keydown(function(e) {
    if(e.keyCode === 13 && !e.shiftKey) {  // Enterが押された
        e.preventDefault();
        const $fieldForm = $(this).closest('.field-form');
        const from = $fieldForm.find('.select-from').val();

        if(from === null) return;

        const title = $(this).closest('.field-form').find('.form-title').text();
        const to = $fieldForm.find('.input-to').val();

        if(confirm(title + ':「' + from + '」から「' + to + '」へ変更してもよろしいですか？')) {
            removeMessages($fieldForm);
            getFieldSubmitAjax($fieldForm);
        }
    }
});
$('.select-from').each(applyInput);

$('.updated-at-form').on('change', function() {
    updateCount($(this));
});
$('.updated-at-form').submit(function(e) {
    const time = $(this).find('.select-from').val();
    if(time === null) return false;

    const operator = $(this).find('.operator option:checked').text();
    const dataCategory = $(this).attr('id') === 'lecturer-updated-at-form' ? '教員' : '授業';

    if(!confirm('本当に最終更新日時が' + time + operator + dataCategory + 'データを削除しますか？')) return false;

    // 送信ボタン二重押下禁止
    $(this).find('.submit-button').attr('disabled', true);

    removeMessages($(this));
    showSpinner($(this));

    const this_ = this;

    getUpdatedAtSubmitAjax($(this))
    .always(function() {
        removeSpinner($(this_));
        $(this_).find('.submit-button').attr('disabled', false);
    });

    return false;
});
$('.updated-at-form').each(function() {
    updateCount($(this));
});

$('#uninstall-form').submit(function(e) {
    return confirm('本当にSelvaの全データを削除しますか？');
});

});
})(jQuery);
</script>

<style>
.input-to {
    width: 25rem;
}

</style>

<div class="wrap">
    <div class="postbox">
        <h3 class="hndle">フィールド値変更</h3>
        <div class="inside">
            <div class="main">
                <h4>教員データテーブル</h4>
                <form id="faculty-form" class="field-form" method="post">
                    <h5 class="form-title">所属学部</h5>
                    <p>
                        <label><?php echo_select(true, 'faculty'); ?>を</label>
                        <label>
                            <input class="input-to" type="text" name="faculty_to">に変える
                        </label>
                    </p>
                </form>
                <form id="class-form" class="field-form" method="post">
                    <h5 class="form-title">階級</h5>
                    <p>
                        <label><?php echo_select(true, 'class'); ?>を</label>
                        <label>
                            <input class="input-to" type="text" name="class_to">に変える
                        </label>
                    </p>
                </form>
                <h4>授業データテーブル</h4>
                <form id="target_department-form" class="field-form" method="post">
                    <h5 class="form-title">対象課程</h5>
                    <p>
                        <label><?php echo_select(false, 'target_department'); ?>を</label>
                        <label>
                            <input class="input-to" type="text" name="target_department_to">に変える
                        </label>
                    </p>
                </form>
                <form id="semseter-form" class="field-form" method="post">
                    <h5 class="form-title">学期</h5>
                    <p>
                        <label><?php echo_select(false, 'semester'); ?>を</label>
                        <label>
                            <input class="input-to" type="text" name="semester_to">に変える
                        </label>
                    </p>
                </form>
                <form id="day-and-periods-form" class="field-form" method="post">
                    <h5 class="form-title">時限曜日</h5>
                    <p>
                        <label><?php echo_select(false, 'day_and_period'); ?>を</label>
                        <label>
                            <input class="input-to" type="text" name="day_and_period_to">に変える
                        </label>
                    </p>
                </form>
                <p>現在登録されている情報を一括で変更します。</p>
            </div>
        </div>
    </div>

    <div class="postbox">
        <h3 class="hndle">最終更新日時による授業データの削除</h3>
        <div class="inside">
            <div class="main">
                <form id="updated-at-form" class="updated-at-form" method="post">
                    <p><?php echo_course_select(); ?>
                        <select class="operator" name="course_operator">
                            <option value="by">より前の</option>
                            <option value="at">と同じ</option>
                            <option value="from">より後の</option>
                        </select>
                        最終更新日時の授業データを削除する(削除予定のデータ数：<span class="count"></span>)</p>
                    <p class="submit">
                        <input type="submit" name="submit" class="submit-button button button-primary" value="削除">
                    </p>
                    <p>最終更新日時(最後にCSVファイルからデータが書き込まれた日時または授業データのまとめを行った日時)が指定された日時の授業データを削除します。</p>
                    <p>この操作は取り消せず、評価などの情報も削除されます。</p>
                    <p>操作後、Selva初期設定から投稿自動生成をし、不要な投稿を削除してください。</p>
                </form>
            </div>
        </div>
    </div>

    <div class="postbox">
        <h3 class="hndle">最終更新日時による教員データの削除</h3>
        <div class="inside">
            <div class="main">
                <form id="lecturer-updated-at-form" class="updated-at-form" method="post">
                    <p><?php echo_lecturer_select(); ?>
                        <select class="operator" name="lecturer_operator">
                            <option value="by">より前の</option>
                            <option value="at">と同じ</option>
                            <option value="from">より後の</option>
                        </select>
                        最終更新日時の教員データを削除する(削除予定のデータ数：<span class="count"></span>)</p>
                    <p class="submit">
                        <input type="submit" name="submit" class="submit-button button button-primary" value="削除">
                    </p>
                    <p>最終更新日時(最後にCSVファイルからデータが書き込まれた日時または教員データが自動作成・自動更新された日時)が指定された日時の教員データを削除します。</p>
                    <p>この削除でオムニバス授業自体は消せません。オムニバス授業は、削除後残った教員が一人以下でもオムニバス授業として登録されます。</p>
                    <p>この操作は取り消せず、評価などの情報も削除されます。</p>
                    <p>操作後、Selva初期設定から投稿自動生成をし、不要な投稿を削除してください。</p>
                </form>
            </div>
        </div>
    </div>

    <div class="postbox">
        <h3 class="hndle">全てのデータを削除</h3>
        <div class="inside">
            <div class="main">
                <form id="uninstall-form" method="post">
                    <input type="hidden" name="uninstall" value="yes">
                    <p>テーマのアンインストール時などに実行してください。</p>
                    <p>この操作は取り消せません。</p>
                    <p class="submit">
                        <input type="submit" name="submit" class="submit-button button button-primary" value="全てのデータを削除する">
                    </p>
<?php if(isset($uninstall_message)) : ?>
                    <div class="updated notice is-dismissible">
                        <p><strong><?php echo $uninstall_message; ?></strong></p>
                    </div>
<?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
}

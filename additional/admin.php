<?php 

// 初期設定画面
add_action('admin_menu', 'add_selva_initial_setting');
function add_selva_initial_setting() {
    add_menu_page('Selva初期設定', 'Selva初期設定', 'manage_options', 'selva_initial_setting', 'selva_initial_setting_menu', '', 1);
}

function selva_initial_setting_menu() {
require_once(__DIR__ . '/admin-common.php');

// 何か入力値があれば一回につき一つだけ処理して返答を返す
if(isset($_POST['reset_tables'])) {
    if($_POST['reset_tables'] === 'yes') {
        $had_indexes = have_indexes();

        $start_ns = hrtime(true);
        drop_indexes();
        $error_message = create_tables();
        $end_ns = hrtime(true);

        $message = round(($end_ns - $start_ns) / 1000000000, 3) . '秒でデータベーステーブル構造のリセットを完了しました。';
        if($had_indexes) $message .= 'インデックスは作成されていません。';
        if($error_message) $message = 'データベーステーブル構造のリセット中にエラーが発生しました：' . $error_message;

        echo_message($message);
    }
    return;
}

if(isset($_FILES['lecturers_csv'])) {
    $filename = $_FILES['lecturers_csv']['name'];

    if(strtolower(substr($filename, -4)) !== '.csv') {
        echo_message('教員データファイルの形式がCSVではありません。');
        return;
    }

    $tmp_filename = $_FILES['lecturers_csv']['tmp_name'];
    $file_full_path = __DIR__ . '/../uploaded-csvs/' . $filename;

    if(!is_uploaded_file($tmp_filename) || !move_uploaded_file($tmp_filename, $file_full_path)) {
        echo_message('教員データファイルのアップロードに失敗しました。');
        return;
    }
    chmod($file_full_path, 0644);

    $auto_update = false;
    $all_update = false;
    if(isset($_POST['similar_lecturer'])) {
        if($_POST['similar_lecturer'] === 'auto-update') {
            $auto_update = true;
        } else if($_POST['similar_lecturer'] === 'all-update') {
            $auto_update = true;
            $all_update = true;
        }
    }

    $start_ns = hrtime(true);
    $result = insert_lecturer_data($file_full_path, $auto_update, $all_update);
    $end_ns = hrtime(true);

    unlink($file_full_path);

    $message = round(($end_ns - $start_ns) / 1000000000, 3) . '秒で' . $filename . 'の追加に成功しました。';

    if(!empty($result['error_message']))
        $message = "{$filename}：{$result['error_message']}";

    if(!empty($result['similar_lecturer_update_message']))
        $message .= $result['similar_lecturer_update_message'];

    if(isset($result['inserted_count']))
        $message .= "(新たに追加された教員データの数：{$result['inserted_count']}、" .
                    "更新された教員データの数(重複含む)：{$result['updated_count']})";

    echo_message($message);
    return;
}

if(isset($_FILES['courses_csv'])) {
    $filename = $_FILES['courses_csv']['name'];

    if(strtolower(substr($filename, -4)) !== '.csv') {
        echo_message('授業データファイルの形式がCSVではありません。');
        return;
    }

    $tmp_filename = $_FILES['courses_csv']['tmp_name'];
    $file_full_path = __DIR__ . '/../uploaded-csvs/' . $filename;

    if(!is_uploaded_file($tmp_filename) || !move_uploaded_file($tmp_filename, $file_full_path)) {
        echo_message('授業データファイルのアップロードに失敗しました。');
        return;
    }

    chmod($file_full_path, 0644);

    $auto_create = false;
    if(isset($_POST['no_lecturers']) && $_POST['no_lecturers'] === 'auto-create')
        $auto_create = true;

    $start_ns = hrtime(true);
    $result = insert_course_data($file_full_path, $auto_create);
    $end_ns = hrtime(true);

    unlink($file_full_path);

    // メッセージ作成
    $message = round(($end_ns - $start_ns) / 1000000000, 3) . '秒で' . $filename . 'の追加に成功しました。';

    if(!empty($result['error_message']))
        $message = "{$filename}：{$result['error_message']}";

    if(!empty($result['new_lecturer_create_message']))
        $message .= $result['new_lecturer_create_message'];

    if(isset($result['inserted_count']))
        $message .= "(新たに追加された授業データの数：{$result['inserted_count']}、" .
                    "更新された授業データの数(重複含む)：{$result['updated_count']})";

    echo_message($message);
    return;
}

if(isset($_POST['create_profiles'])) {
    if($_POST['create_profiles'] === 'yes') {
        $start_ns = hrtime(true);
        $result = create_posts(true);
        $end_ns = hrtime(true);

        $message = round(($end_ns - $start_ns) / 1000000000, 3) . '秒で教員ページ生成に成功しました。';
        if(!empty($result['error_message']))
            $message = "教員ページ生成：{$result['error_message']}";

		if(isset($result['inserted_count'])) {
            $message .= "(新たに生成された投稿の数：{$result['inserted_count']}、" .
						"更新された投稿の数：{$result['updated_count']}";

			if(isset($result['trashed_count']))
                $message .= "、ゴミ箱に移動した投稿の数：{$result['trashed_count']}";

			$message .= ")";
        }

        echo_message($message);
    }
    return;
}

if(isset($_POST['create_courses'])) {
    if($_POST['create_courses'] === 'yes') {
        $start_ns = hrtime(true);
        $result = create_posts(false);
        $end_ns = hrtime(true);

        $message = round(($end_ns - $start_ns) / 1000000000, 3) . '秒で授業ページ生成に成功しました。';
        if(!empty($result['error_message']))
            $message = "授業ページ生成：{$result['error_message']}";

		if(isset($result['inserted_count'])) {
            $message .= "(新たに生成された投稿の数：{$result['inserted_count']}、" .
						"更新された投稿の数：{$result['updated_count']}";

			if(isset($result['trashed_count']))
                $message .= "、ゴミ箱に移動した投稿の数：{$result['trashed_count']}";

			$message .= ")";
        }

        echo_message($message);
    }
    return;
} ?>

<script type="text/javascript">
(function($) {
$('document').ready(function(){

function toggleDbSubmit() {
    if(hasSpinner($('#db-form'))) return;

    if($('#reset_tables:checked').length || $('#lecturers_csv').val() !== '' || $('#courses_csv').val() !== '') {
        $('#db-form .submit-button').attr('disabled', false);
    } else {
        $('#db-form .submit-button').attr('disabled', true);
    }
}

function getFileSubmitAjax(formData, filename) {
    return $.ajax({
        type: 'post',
        data: formData,
        processData: false,
        contentType: false
    }).then(function(html) {
        if(html !== undefined) {
            // データ送信成功
            extractDataAndNoticeMessage($('#db-form'), html);
        }
    }, function(jqXHR, textStatus, errorThrown) {
        // データ送信失敗
        extractDataAndNoticeMessage($('#db-form'), jqXHR.responseText, filename + 'の通信中にエラーが発生しました。(データファイルを編集した場合は再選択する必要があります。)');
    });
}

function getDbSubmitAjax(doingAll=false) {
    // テーブル作成
    return $.ajax({
        type: 'post',
        data: $('.reset_tables').serialize()
    }).then(function(html) {
        if($('#reset_tables:checked').length) {
            // テーブル作成成功
            extractDataAndNoticeMessage($('#db-form'), html);
        }

        // 教員データ送信
        let prevAjax = undefined;

        for(const file of $('#lecturers_csv').prop('files')) {
            const formData = new FormData();
            formData.append('lecturers_csv', file);
            formData.append('similar_lecturer', $('.similar-lecturers:checked').val());
            
            if(prevAjax === undefined) {
                prevAjax = getFileSubmitAjax(formData, file.name);
            } else {
                prevAjax = prevAjax.then(function() {
                    return getFileSubmitAjax(formData, file.name);
                });
            }
        }

        return prevAjax;
    }, function(jqXHR, textStatus, errorThrown) {
        if($('.reset_tables:checked').length) {
            // テーブル作成失敗
            extractDataAndNoticeMessage($('#db-form'), jqXHR.responseText, 'データベーステーブル構造のリセット信号通信中にエラーが発生しました。');
        } else {
            extractDataAndNoticeMessage($('#db-form'), jqXHR.responseText, 'サーバーとの通信中にエラーが発生しました。');
        }
    }).then(function() {
        // 授業データ送信
        let prevAjax = undefined;

        for(const file of $('#courses_csv').prop('files')) {
            const formData = new FormData();
            formData.append('courses_csv', file);
            formData.append('no_lecturers', $('.no-lecturers:checked').val());

            if(prevAjax === undefined) {
                prevAjax = getFileSubmitAjax(formData, file.name);
            } else {
                prevAjax = prevAjax.then(function() {
                    return getFileSubmitAjax(formData, file.name);
                });
            }
        }

        return prevAjax;
    }).then(function(html) {
        // テーブルリセットしてない、もしくはもともとインデックスはなかった
        if(!$('#reset_tables:checked').length || $('#db-form').data('have_indexes') === 'no') return;

        // インデックス再作成送信
        return getIndexSubmitAjax($('#db-form'), {create_indexes: 'yes'});
    });
}

function togglePostSubmit() {
    if(hasSpinner($('#post-form'))) return;

    if($('#create_profiles:checked').length || $('#create_courses:checked').length) {
        $('#post-form .submit-button').attr('disabled', false);
    } else {
        $('#post-form .submit-button').attr('disabled', true);
    }
}

function getPostSubmitAjax() {
    // 教員データ投稿
    return $.ajax({
        type: 'post',
        data: $('.create_profiles').serialize(),
    }).then(function(html) {
        if($('#create_profiles:checked').length) {
            // 教員データ投稿通信成功
            extractDataAndNoticeMessage($('#post-form'), html);
        }

        if(!$('#create_courses:checked').length) return;

        // 授業データ投稿
        return $.ajax({
            type: 'post',
            data: $('.create_courses').serialize(),
        });
    }, function(jqXHR, textStatus, errorThrown) {
        // 教員データ投稿通信失敗
        extractDataAndNoticeMessage($('#post-form'), jqXHR.responseText, 'サーバーとの通信中にエラーが発生しました。');
    }).then(function(html) {
        if(html !== undefined) {
            // 授業データ投稿通信成功
            extractDataAndNoticeMessage($('#post-form'), html);
        }
    }, function(jqXHR, textStatus, errorThrown) {
        // 授業データ投稿通信失敗
        extractDataAndNoticeMessage($('#post-form'), jqXHR.responseText, 'サーバーとの通信中にエラーが発生しました。');
    });
}

// データベース作成フォーム
$('.file-input, #reset_tables').on('change', function() {
    toggleDbSubmit();
});
$('.file-input').click(function() {
    // changeイベントが起こるように
    $(this).val('');
});
$('.file-input-reset').click(function() {
    $(this).prev().val('');
});
$('#db-form').submit(function(e) {
    // 送信ボタン二重押下禁止
    $(this).find('.submit-button').attr('disabled', true);

    removeMessages($(this));
    showSpinner($(this));

    const this_ = this;

    getDbSubmitAjax()
    .always(function() {
        removeSpinner($(this_));
        toggleDbSubmit();
    });

    return false;
});
$('#db-form .submit-button').attr('disabled', true);

// 投稿生成フォーム
$('.create_checkbox').on('change', function() {
    togglePostSubmit();
});
$('#post-form').submit(function(e) {
    // 送信ボタン二重押下禁止
    $(this).find('.submit-button').attr('disabled', true);

    removeMessages($(this));

    showSpinner($(this));

    const this_ = this;

    getPostSubmitAjax()
    .always(function() {
        removeSpinner($(this_));
        togglePostSubmit();
    });

    return false;
});
$('#post-form .submit-button').attr('disabled', true);

// 全てにチェックを入れて実行
$('#all-form').submit(function(e) {
    // 全てにチェックを入れる
    $('input[type="checkbox"]').prop('checked', true);

    // 送信ボタン二重押下禁止
    $(this).find('.submit-button').attr('disabled', true);
    $('#db-form .submit-button').attr('disabled', true);
    $('#post-form .submit-button').attr('disabled', true);

    removeMessages($(this));
    removeMessages($('#db-form'));
    removeMessages($('#post-form'));

    showSpinner($(this));
    showSpinner($('#db-form'));
    showSpinner($('#post-form'));

    const this_ = this;

    getDbSubmitAjax(true)
    .always(function() {
        removeSpinner($('#db-form'));
    })
    .then(getPostSubmitAjax)
    .always(function() {
        removeSpinner($('#post-form'));
        extractDataAndNoticeMessage($('#all-form'), '', '完了しました。');
        removeSpinner($(this_));
        toggleDbSubmit();
        togglePostSubmit();
        $(this_).find('.submit-button').attr('disabled', false);
    });

    return false;
});

});
})(jQuery);
</script>

<style>
.file-input-reset {
    margin-left: 10px;
}

.label-for-radio {
	position: relative;
	top: -2px;
	margin-left: 6px;
}

#format-form p span {
    margin-right: 60px;
}

</style>

<div class="wrap">
    <div class="postbox">
        <h3 class="hndle">データベース登録</h3>
        <div class="inside">
            <div class="main">
                <form id="db-form" method="post" enctype="multipart/form-data" data-have_indexes="<?php echo have_indexes() ? 'yes' : 'no'; ?>">
                    <p>
                        <input class="reset_tables" type="hidden" name="reset_tables" value="no">
                        <input id="reset_tables" class="reset_tables" type="checkbox" name="reset_tables" value="yes">
                        <label for="reset_tables">データベーステーブル構造をリセットする(初回は必ずチェックを入れてください。)</label>
                    </p>
                    <p>データベーステーブルが無い場合は作成、異常がある場合は修正します。この操作でデータの削除が行われることはありません。</p>
                    <p>インデックスが作成されている場合はチェックを入れると一度削除されて情報の追加後に再作成されます。(情報の追加毎にインデックスを再作成しない分高速になります。)</p>
                    <h4>教員情報の追加</h4>
                    <p><input type="file" class="file-input" id="lecturers_csv" name="lecturers_csv" accept=".csv" multiple><button type="button" class="file-input-reset">リセット</button></p>
                    <p><b>『似ている「教員名」』がすでに存在していた場合：</b>
						<label class="label-for-radio"><input type="radio" class="similar-lecturers" name="similar_lecturer" value="auto-update">教員名以外の情報を更新する</label>
						<label class="label-for-radio"><input type="radio" class="similar-lecturers" name="similar_lecturer" value="all-update">教員名も更新する</label>
                        <label class="label-for-radio"><input type="radio" class="similar-lecturers" name="similar_lecturer" value="error" checked>その教員の登録をせず、エラーを発生させる</label></p>
                    <p>CSVファイルを使って教員データベースに教員情報を追加します。</p>
                    <p>ファイルは、必ず6列のCSV形式で、左の列から順に「教員名」、「教員名ルビ」、「所属学部」、「階級」、「リンクURL」、「画像URL」となった
                        見出し行があり、その次の行から連続してデータが並んだものとしてください。</p>
                    <p>「教員名」は、ページに表示させたい形式で書いてください。
                        また、「教員名」、「教員名ルビ」中に含まれる空白や記号は検索に影響を及ぼしません。
                        「教員名ルビ」は検索用にルビや他の漢字などを指定できます(必ずしも指定する必要はありません)。
                        複数指定する場合は続けて書くか、スペース等で区切ってください。平仮名/カタカナ、大文字/小文字は区別されません。</p>
                    <p>「教員名」は、すでに教員データベース上に完全に一致するものがある場合は「教員名」以外の情報を新しい情報(ファイルの情報)で上書きします。</p>
                    <p>また、「教員名」中の空白や記号で分割した文字列がすべて含まれている「教員名」は『似ている「教員名」』となります。
                        登録する「教員名」と『完全に一致する「教員名」』ではないが『似ている「教員名」』がすでに教員データベースにある場合はエラーとなります。
                        ただし、「教員名以外の情報を更新する」を選択した場合は、「教員名」以外の情報を新しい情報で上書きします。
                        「教員名も更新する」を選択すると、教員名も新しい情報で上書きします。
                        『似ている「教員名」』が複数ある場合はどの場合でもエラーになります。</p>
                    <p>この検索では、「山田徹也」は「山田徹」に対して『似ている「教員名」』となってしまいます。
                        このような場合、先頭に「^」をつけることで「先頭である」ことを、または末尾に「$」をつけることで「末尾である」ことを示し、厳格な検索をすることができます。
                        例えば、「山田徹$」は、終端が「山田徹」で終わる文字にマッチします。</p>
                    <p>ただし、先頭に「^」または末尾に「$」(またはその両方)を付けた場合は、以下のような注意事項があります。
                        <br>・「教員名」は文字の順番が守られ、空白・記号は任意の長さの文字列に置き換えられて検索されます。(例：「^フォスター, J パトリック」は「フォスター J パトリック」に一致するが、「フォスター パトリック J」に一致しない。)
                        <br><b><?php echo htmlspecialchars('・普通の検索とは違い、もとの「教員名」の半角スペース以外の記号は削除されずに検索が行われます。
                        例えば、「山田徹」は「山田、<全角スペース>徹」に対して一致しますが、「山田徹$」は「山田、<全角スペース>徹」に対して一致しません。
                        そのため、やむを得ない場合のみ「^」や「$」を使用することをお勧めします。また、どうしても「^」や「$」を用いなければならない場合に、
                        「山田、<全角スペース>徹」を「山田徹」で上書き登録したい場合は、一度「山田<半角スペース>徹$」で上書きし、その後「山田徹$」で上書きすればよいです。'); ?></b>
                        <br>・「教員名ルビ」は検索に使われません。
                        <br>・他の正規表現に対応しているわけではありません。
                        <br>・「教員名」中の先頭の「^」と末尾の「$」は除去されて登録されます。</p>
                    <p>「教員名」は50文字、「教員名ルビ」は「教員名」と合わせて約200文字、「所属学部」、「階級」は100文字、「リンクURL」、「画像URL」は2048文字まで入ります。</p>
                    <p>Selvaは、「教員名」の先頭の「^」と末尾の「$」の削除、また検索可能な文字列を含むかどうかのチェック、「教員名ルビ」に対して検索用にデータの改変、
                        さらにすべてのデータに対してそれぞれの最大文字数以上を切り捨てる改変を行います。
                        それ以外はいかなるチェックも改変も行いません。</p>
                    <h4>授業情報の追加</h4>
                    <p><input type="file" class="file-input" id="courses_csv" name="courses_csv" accept=".csv" multiple><button type="button" class="file-input-reset">リセット</button></p>
                    <p><b>「教員名」が教員データベースでヒットしなかった場合：</b>
						<label class="label-for-radio"><input type="radio" class="no-lecturers" name="no_lecturers" value="auto-create">自動で教員データを作成する</label>
                        <label class="label-for-radio"><input type="radio" class="no-lecturers" name="no_lecturers" value="error" checked>その授業の登録をせず、エラーを発生させる</label></p>
                    <p>CSVファイルを使って授業データベースに授業情報を追加します。</p>
                    <p>ファイルは、必ず6列のCSV形式で、左の列から順に「授業名」、「授業名ルビ」、「設置課程」、「学期」、「曜日時限」、「キャンパス」、「教員名」となった
                        見出し行があり、その次の行から連続してデータが並んだものとしてください。</p>
                    <p>「授業名」は、ページに表示させたい形式で書いてください。「授業名」、「授業名ルビ」中に含まれる空白や記号は検索に影響を及ぼしません。
                        「授業名ルビ」は検索用にルビや他の漢字などを指定できます(必ずしも指定する必要はありません)。
                        複数指定する場合は続けて書くか、スペース等で区切ってください。平仮名/カタカナ、大文字/小文字は区別されません。</p>
                    <p>「教員名」は教員データベーステーブルに登録がない場合はエラーとなります。
                        ただし、「自動で教員ページを生成する」を選んだ場合は自動でその教員ページが生成されます。
                        オムニバス授業の場合は「;」で区切り、一つのセルに複数の教員名を入れてください。</p>
                    <p>なお、この「教員名」の検索は上記の『似ている「教員名」』の検索方法と同じで、同様に「^」「$」を入れることで厳格な検索をすることができます。</p>
                    <p><?php echo htmlspecialchars('「設置課程」は、「<設置課程> <設置学部・研究科> <他の情報…>」として、
                        <設置課程>と<設置学部・研究科>、<他の情報>の間に空白を入れてください。ここで設定された<設置課程>、<設置学部・研究科>は
                        検索結果のフィルタリングに使われますが、「設置課程」のみの登録でも問題ありません。
                        複数の設置課程がある場合は手動で「/」で区切ったデータを挿入しても構いませんが、複数の授業データを挿入して
                        オプション初期設定からSelvaにデータをまとめさせることもできます。詳細はオプション初期設定における説明を参照してください。'); ?></p>
                    <p>「曜日時限」は、「/」または「、」または「,」で区切ることで複数登録できます。この情報は検索結果のフィルタリングに使われます。
                        オプション初期設定からSelvaにデータを自動でまとめさせることもできます。</p>
                    <p>すでに同じ情報で登録がある行は何もしません。ただし、「教員名」以外のデータがすべて同じで、
                        「教員名」が二人分以上ある授業は同じオムニバス授業として扱われ(一人の場合はオムニバス授業とは別の授業として扱われます)、
                        新しい情報(ファイルの情報)に過去に登録がなかった教員名がある場合は同じオムニバス授業の教員情報にその教員が追加されます。</p>
                    <p>「授業名」、「曜日時限」は100文字、「授業名ルビ」は「授業名」と合わせて約400文字、「設置課程」は200文字、「学期」、「キャンパス」は20文字まで入ります。</p>
                    <p>Selvaは、「授業名」、「教員名」に対して検索可能な文字列を含むかどうかのチェックと「授業名ルビ」に対して検索用にデータの改変、
                        さらにすべてのデータに対してそれぞれの最大文字数以上を切り捨てる改変を行います。
                        それ以外はいかなるチェックも改変も行いません。</p>
                    <p>授業情報の追加は数分かかることがあります。(1ファイル5分を超える場合はファイルを分けてください。)</p>
                    <p class="submit">
                        <input type="submit" name="submit" class="submit-button button button-primary" value="データ追加">
                    </p>
                </form>
            </div>
        </div>
    </div>

    <div class="postbox">
        <h3 class="hndle">投稿自動生成</h3>
        <div class="inside">
            <div class="main">
                <form id="post-form" method="post">
                    <p>
                        <input class="create_profiles" type="hidden" name="create_profiles" value="no">
                        <input class="create_courses" type="hidden" name="create_courses" value="no">
                        <input id="create_profiles" class="create_profiles create_checkbox" type="checkbox" name="create_profiles" value="yes"><label for="create_profiles">教員ページを生成する</label>
                        <input id="create_courses" class="create_courses create_checkbox" type="checkbox" name="create_courses" value="yes"><label for="create_courses">オムニバス授業ページを生成する</label>
                    </p>
                    <p>教員ページまたは授業ページの投稿を作成します。データの追加後、この操作をしないとページが表示されません。
                        教員ページは「/profile/教員ID」、授業ページは「/course/授業ID」をURLとしますが、すでにそのURLでページが存在している場合は投稿名を
                        「教員名」または「授業名」で上書きします。</p>
                    <p class="submit">
                        <input type="submit" name="submit" class="submit-button button button-primary" value="データベースから記事を生成">
                    </p>
                </form>
            </div>
        </div>
    </div>

    <div class="postbox">
        <h3 class="hndle">全てにチェックを入れて実行</h3>
        <div class="inside">
            <div class="main">
                <form id="all-form" method="post">
                    <p>基本はこれを実行してください。実行には数分かかることがあります。</p>
                    <p class="submit">
                        <input type="submit" name="submit" class="submit-button button button-primary" value="実行">
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
}

<?php

require_once(__DIR__ . '/../sql-functions/admin.php');
require_once(__DIR__ . '/../sql-functions/admin-options.php');
require_once(__DIR__ . '/../sql-functions/admin-specials.php');

function echo_message($message, $data=[]) {
    $dataset = '';
    foreach($data as $key => $value) {
        $dataset .= " data-$key='$value'";
    }
    printf('<p id="message"%s>%s</p>', $dataset, htmlspecialchars($message));
}

// 何か入力値があれば一回につき一つだけ処理して返答を返す
if(isset($_POST['create_indexes'])) {
    if($_POST['create_indexes'] === 'yes') {
        $start_ns = hrtime(true);
        $has_succeeded = add_indexes();
        $end_ns = hrtime(true);
        $data['have_indexes'] = have_indexes() ? 'yes' : 'no';
        if($has_succeeded) {
            echo_message(round(($end_ns - $start_ns) / 1000000000, 3) . '秒でインデックスの作成に成功しました。', $data);
        } else {
            echo_message('インデックスの作成中にエラーが発生しました。', $data);
        }
    } else if($_POST['create_indexes'] === 'no') {
        $start_ns = hrtime(true);
        $has_succeeded = drop_indexes();
        $end_ns = hrtime(true);
        $data['have_indexes'] = have_indexes() ? 'yes' : 'no';
        if($has_succeeded) {
            echo_message(round(($end_ns - $start_ns) / 1000000000, 3) . '秒でインデックスの削除に成功しました。', $data);
        } else {
            echo_message('インデックスの削除中にエラーが発生しました。', $data);
        }
    }
    return;
}

?>

<script type="text/javascript">

// 通知を作りdataがあれば戻り値としてdataを返す関数
function extractDataAndNoticeMessage($form, html, default_message) {
    let message = default_message ? `<p><strong>${default_message}</strong></p>` : html;
    let $message = jQuery(html).find('#message');
    let data = {};

    if($message.length) {
        message = `<p><strong>${$message.text()}</strong></p>`;
        data = $message.data();
    } else {
        $message = jQuery(html).find('#wpbody-content');
        if($message.length) {
            // 余計なタグを削除
            $message.find('script, style, div:not(.wp-die-message)').remove();
            const html = $message.html();

            // 空でなければhtml形式のまま表示させる
            if(html.trim())
                message = html;
        }
    }

    const $notice = jQuery(`
        <div class="updated notice is-dismissible">
            ${message}
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">この通知を非表示にする。</span></button>
        </div>
    `);

    $notice.find('.notice-dismiss').click(function() {
        $notice.remove();
    });

    $form.append($notice);

    return data;
}

// 通知を消す関数
function removeMessages($form) {
    $form.find('.updated.notice.is-dismissible').remove();
}

function showSpinner($form) {
    const $spinner = jQuery('<div class="selva-spinner"></div>');
    $form.find('.submit').append($spinner);
}

function removeSpinner($form) {
    $form.find('.selva-spinner').remove();
}

function hasSpinner($form) {
    return $form.find('.selva-spinner').length > 0;
}

function getSimpleSubmitAjax($form, errorMessage) {
    return jQuery.ajax({
        type: 'post',
        data: $form.serialize()
    }).then(function(html) {
		extractDataAndNoticeMessage($form, html);
        return html;
    }, function(jqXHR, textStatus, errorThrown) {
		extractDataAndNoticeMessage($form, jqXHR.responseText, errorMessage);
    });
}

function toggleIndexSubmit() {
    if(hasSpinner(jQuery('#index-form'))) return;

    if(jQuery('#index-form').data('have_indexes') === 'yes' && jQuery('#create_indexes:checked').length
            || jQuery('#index-form').data('have_indexes') === 'no' && !jQuery('#create_indexes:checked').length) {
        jQuery('#index-form .submit-button').attr('disabled', true);
    } else {
        jQuery('#index-form .submit-button').attr('disabled', false);
    }
}

function getIndexSubmitAjax($noticeTo=null, data=null) {
    // デフォルト値代入
    if($noticeTo === null) $noticeTo = jQuery('#index-form');
    if(data === null) data = jQuery('.create_indexes').serialize();

    // インデックス作成送信
    return jQuery.ajax({
            type: 'post',
            data: data
    }).then(function(html) {
        // インデックス作成送信成功
        data = extractDataAndNoticeMessage($noticeTo, html);

        if(jQuery('#have_indexes').length) {
            if(data['have_indexes'] === 'yes') {
                jQuery('#have_indexes').text('インデックス作成済み');
                jQuery('#index-form').data('have_indexes', 'yes');
            } else {
                jQuery('#have_indexes').text('インデックスなし');
                jQuery('#index-form').data('have_indexes', 'no');
            }

            toggleIndexSubmit();
        }
    }, function(jqXHR, textStatus, errorThrown) {
        //インデックス作成送信失敗
        extractDataAndNoticeMessage($noticeTo, jqXHR.responseText, 'インデックス作成信号の通信中にエラーが発生しました。');
    });
}

</script>

<style>
p.submit {
    display: grid;
    grid-template-columns: min-content min-content;
    align-items: center;
}

input.submit-button.button.button-primary {
    margin-right: 30px;
}

.selva-spinner {
    width: 30px;
    height: 30px;
    border: 4px #ddd solid;
    border-top: 4px #2e93e6 solid;
    border-radius: 50%;
    animation: sp-anime 1.0s infinite linear;
}

@keyframes sp-anime {
    100% { 
        transform: rotate(360deg); 
    }
}

</style>
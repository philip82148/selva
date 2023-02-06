(function($) {

// コメントのあるページでない
if(!$('#comments').length) return;

function updateCommentTime() {
    $('.comment-list .comment').each(function() {
        let timeByNow = Date.now() / 1000 - $(this).data('datetime') + 9 * 60 * 60;
        let label = 0;
        if(timeByNow < 60) label = 'たった今';
        else {
            timeByNow /= 60;
            if(timeByNow < 60) label = String(timeByNow.toFixed()) + "分前";
            else {
                timeByNow /= 60;
                if(timeByNow < 24) label = String(timeByNow.toFixed()) + "時間前";
                else {
                    timeByNow /= 24;
                    if(timeByNow < 7) label = String(timeByNow.toFixed()) + "日前";
                    else {
                        timeByNow /= 7;
                        if(timeByNow < 30.5 / 7) label = String(timeByNow.toFixed()) + "週間前";
                        else {
                            timeByNow /= 30.5 / 7;
                            if(timeByNow < 12) label = String(timeByNow.toFixed()) + "カ月前";
                            else {
                                timeByNow /= 12;
                                label = String(timeByNow.toFixed()) + "年前";
                            }
                        }
                    }
                }
            }
        }
        $(this).children('.comment-body').children('.comment-meta.commentmetadata').children('.comment-datetime').text(label);
    });
}

function validateCommentAndToggleSubmit($commentForm) {
    let text = $commentForm.children('.comment-textarea').html();
    // brタグとdivタグを改行コードに変換
    text = text.replace(/<[^\/>]*(br|div)[^>]*>/gi, '\n');
    // その他のタグを削除
    text = text.replace(/<[^>]*>/g, '');
    // 先頭と末尾の改行を削除
    text = text.replace(/^\n+|\n+$/, '');

    // 残りが空白だけなら.submitを無効化して空文字を返す
    // そうでなければ.submitを有効化したそのまま返す
    if(text.replace(/(&nbsp;|\s)+/gi, '') === '') {
        $commentForm.find('.submit').attr('disabled', true);
        return '';
    }
    $commentForm.find('.submit').attr('disabled', false);
    return text;
}

function showErrorMessage($page) {
    const $message = $page.find('p').parent('.wp-die-message');
    if($message.length) {
        alert($message.text());
    } else {
        alert('コメントの送信中にエラーが発生しました。');
    }
}

function extractNewCommentAndUpdate($page, commentParentId) {
    // 親と子のセレクターを決定
    let parentSelector = '#comments';
    let childrenSelector = '.comment-list';
    if(commentParentId != 0) {
        // コメントではなくリプライである
        parentSelector = '#comment-'+ String(commentParentId);
        childrenSelector = '.children';
    }
    
    // 新しいコメントの同階層のコメント(.children)を取得
    const $newChildren = $page.find(parentSelector).children(childrenSelector);
    if(!$newChildren.length) {
        // .childrenが取得できなかった
        showErrorMessage($page);
        return;
    }

    // 今の.childrenを削除して追加
    const $currentParent = $(parentSelector);
/*  // 変更部分だけ追加する処理
    let $currentChildren = $currentParent.children(childrenSelector);
    if(!$currentChildren.length)
        $currentChildren.append($('<ol class="' + childrenSelector + '"></ol>'));
    const $allNewComments = $newChildren.children('.comment');
    const $allCurrentComments = $currentChildren.children('.comment');
    for(let i = 0; i < $allNewComments.length; i++) {
        if(i >= $allCurrentComments.length) {
            $allNewComments.eq(i).clone(true).appendTo($currentChildren);
        } else if($allNewComments.eq(i).attr('id') !== $allCurrentComments.eq(i).attr('id')) {
            $allNewComments.eq(i).clone(true).insertBefore($allCurrentComments.eq(i));
        }
    } */
    $currentParent.children(childrenSelector).remove();
    $currentParent.append($newChildren);

    updateCommentTime();
}

function resetForm($commentForm) {
    $commentForm.children('.comment-textarea').html('');
    if($commentForm.attr('id') === 'replyform') {
        $commentForm.hide();
    }
}

function submitInBackground($commentForm) {
    const text = validateCommentAndToggleSubmit($commentForm);
    if(text === '') return false;

    // 提出ボタンを一時的に使用不能にし、スピナーをつける
    $commentForm.find('.submit').attr('disabled', true);
    const $spinner = $('<div class="selva-spinner"></div>');
    $commentForm.find('.submit').after($spinner);

    $commentForm.children('.comment-input').val(text);
    $.ajax({
        type: 'post',
        url: $commentForm.attr('action'),
        data: $commentForm.serialize()
    }).done(function(html) {
        extractNewCommentAndUpdate($(html), $commentForm.find('.comment_parent').val());
        resetForm($commentForm);
        $spinner.remove();
    }).fail(function(jqXHR, textStatus, errorThrown) {
        // alertすると処理が止まってしまうので先にspinnerを外す
//        $spinner.remove();
        // removeしてから反映に時間が掛かるようなので100ms待つ
        setTimeout(function () {
            showErrorMessage($(jqXHR.responseText));
            validateCommentAndToggleSubmit($commentForm);
        }, 100);
    });

    return true;
}

function appendReplyFormTo($reaction) {
    const $replyForm = $('#replyform');
    const commentId = $reaction.closest('.comment').data('commentid');
    if(!commentId) return;
    $replyForm.find('.comment_parent').val(commentId);
    $replyForm.appendTo($reaction);
}

// コメントを並び変える処理
function orderComments(orderBy) {
    const $commentList = $('.comment-list');
    const $children = $commentList.children('.comment');
    let children = [];
    for(let i = 0; i < $children.length; i++)
        children.push($children.eq(i));
    let compare = undefined;
    if(orderBy === 'newest') {
        compare = function(a, b) {
            if(a.data('datetime') > b.data('datetime')) {
                return -1;
            }
            if(a.data('datetime') < b.data('datetime')) {
                return 1;
            }
            return 0;
        }
    } else {
        compare = function(a, b) {
            if(a.data('comment_likes') > b.data('comment_likes')) {
                return -1;
            }
            if(a.data('comment_likes') < b.data('comment_likes')) {
                return 1;
            }
            return 0;
        }
    }
    children.sort(compare);
    $commentList.hide();
    for(const $child of children) {
        $commentList.append($child);
    }
    $commentList.fadeIn(300);
}

function likeCommentAndDispatch($reaction, isLike) {
    const $comment = $reaction.closest('.comment');
    const newCommentLikes = $comment.data('comment_likes') + (isLike ? 1 : -1);
    $comment.data('comment_likes', newCommentLikes);
    $reaction.find('.like-count').text(newCommentLikes);
    
    // データ送信
    $.ajax({
        type: 'post',
        url: '/wp-content/themes/selva/additional/comment-like-post.php',
        data: {
            'comment_id': $comment.data('commentid'),
            'is_like': isLike
        }
    });
}

updateCommentTime();
setInterval(updateCommentTime, 60000);

// #commentformの要素にClass属性を追加
const addClassToCommentForm = function() {
    $('#comment_post_ID').addClass('comment_post_ID');
    $('#comment_parent').addClass('comment_parent');
};
addClassToCommentForm();

// #replyformを作る
const createReplyForm = function() {
    const $replyForm = $('#commentform').clone(true);
    $replyForm.attr('id', 'replyform');
    $replyForm.find('.submit').removeAttr('id');
    $replyForm.find('.comment_post_ID').removeAttr('id');
    $replyForm.find('.comment_parent').removeAttr('id');
    $replyForm.find('.selectdiv').remove();
    $replyForm.hide();
    $replyForm.appendTo($('#comments'));
};
createReplyForm();

$('.comment-textarea').keydown(function(e) {
    if(e.keyCode === 13 && e.ctrlKey) {  // Ctrl+Enterが押された
        // 改行せずに送信
        e.preventDefault();
        $(this).parent().find('.submit').click();
    }
});

$('.comment-textarea').keyup(function(e) {
    // 二重送信防止
    const hasSpinner = $(this).parent().find('.selva-spinner').length > 0;
    if(!hasSpinner) {  // コメント送信中でない
        validateCommentAndToggleSubmit($(this).parent());
    }
});

$('.comment-textarea').on('paste', function(e) {
    e.preventDefault();
    // ペーストデータをプレインテキストとして取得、挿入
    const paste = (e.originalEvent.clipboardData || window.clipboardData).getData("text/plain");
    document.execCommand('insertText', false, paste);
});

$('.comment-form').submit(function(e) {
    submitInBackground($(this));
    return false;
});

$('#comments').on('click', '.like-button', function(e) {
    if($(this).hasClass('active')) {
        likeCommentAndDispatch($(this).parent(), false);
        $(this).removeClass('active');
    } else {
        likeCommentAndDispatch($(this).parent(), true);
        $(this).addClass('active');
    }
});

$('#comments').on('click', '.reply-button', function(e) {
    if($(this).parent().children('#replyform').length) {
        $('#replyform').toggle();
    } else {
        appendReplyFormTo($(this).parent());
        $('#replyform').show();
    }
});

$(".submit").attr('disabled', true);

$('.comment-order input').on('change', function() {
    orderComments($(this).val());
});

})(jQuery);
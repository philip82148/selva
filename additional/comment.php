<?php 

// コメントに言及する授業タイトルのコメントメタ情報を追加(あれば)
add_action( 'comment_post', 'add_custom_comment_field' );
function add_custom_comment_field($comment_id) {
    if(isset($_POST['course_id']) && $_POST['course_id'] > 2) {
        global $wpdb;

        $title = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT title
                   FROM	{$wpdb->prefix}selva_courses
                  WHERE course_id=%d",
                  $_POST['course_id']
            )
        );

        if($title)
            add_comment_meta($comment_id, 'course_title', $title);
    }
}

function echo_comment_form() {
	$com_args = array(
		'title_reply' => '',
		'comment_field' => '<div contenteditable="true" class="comment-textarea"></div>' .
						   '<input class="comment-input" type="hidden" name="comment">',
	    'comment_notes_before' => '',/* ここで「メールアドレスが公開されることはありません」を削除 */
	    'comment_notes_after' => function_exists('generate_course_select') ? generate_course_select() : '',
     	'fields' => array(
            'author' => '', /* ここは投稿者名のフォーム */
            'email'  => '',
            'url'    => '',),/* ここはURLのフォーム。削除している。 */
	);
	comment_form( $com_args );
}

function echo_comment($comment, $args, $depth) {
    if ($args['style'] === 'div') {
        $tag       = 'div';
        $add_below = 'comment';
    } else {
        $tag       = 'li';
        $add_below = 'div-comment';
    }
?>
    <<?php echo $tag ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ) ?>
            id="comment-<?php comment_ID() ?>"
            data-datetime="<?php echo get_comment_time('U'); ?>" data-commentid="<?php comment_ID(); ?>"
            data-comment_likes="<?php echo count_comment_likes(get_comment_ID()); ?>">
    <?php if ( 'div' != $args['style'] ) : ?>
        <div id="div-comment-<?php comment_ID() ?>" class="comment-body">
    <?php endif; ?>
    <div class="comment-author vcard">
        <?php if ( $args['avatar_size'] != 0 ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
    </div>
    <?php if ( $comment->comment_approved == '0' ) : ?>
        <em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.' ); ?></em>
        <br />
    <?php endif; ?>

    <div class="comment-meta commentmetadata">
        <?php printf( __( '<cite class="fn">%s</cite>' ), get_comment_author_link() ); ?>
		<span class="comment-datetime"></span>
		<span class="comment-title">
<?php
	$title = get_comment_meta($comment->comment_ID, 'course_title', true);
	if($title) echo "言及した授業：$title";
?>
		</span>
    </div>

    <div class="comment-text">
        <?php comment_text(); ?>
    </div>

	<div class="reaction">
        <button class="like-button <?php if(is_liked_comment($comment->comment_ID)) echo 'active'; ?>">
            <i class="fas fa-thumbs-up"></i>
            <span class="like-count"><?php echo count_comment_likes($comment->comment_ID); ?></span>
        </button>
        <?php if($depth < $args['max_depth']) : ?>
            <button class="reply-button">返信</button>
        <?php endif; ?>
    </div>
    <?php if ( 'div' != $args['style'] ) : ?>
	
    </div>
    <?php endif;
}

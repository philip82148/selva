<?php
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">
<?php
echo_comment_form();

if ( have_comments() ) {
?>

	<form class="comment-order">
		<input type="radio" name="order" id="newest" value="newest" checked><label class="newest" for="newest">最新順</label>
		<input type="radio" name="order" id="popularity" value="popularity"><label class="popularity" for="popularity">人気順</label>
	</form>

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>

	<nav id="comment-nav-above" class="navigation comment-navigation" role="navigation">
		<h4 class="screen-reader-text"><?php _e( 'Comment navigation', 'twentyfourteen' ); ?></h4>
		<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'twentyfourteen' ) ); ?></div>
		<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'twentyfourteen' ) ); ?></div>
	</nav><!-- #comment-nav-above -->

<?php endif; // Check for comment navigation. ?>

	<ol class="comment-list">
		<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'short_ping' => true,
				'avatar_size'=> 34,
				'callback' => 'echo_comment',
			) );
		?>
	</ol><!-- .comment-list -->

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>

	<nav id="comment-nav-below" class="navigation comment-navigation" role="navigation">
		<h4 class="screen-reader-text"><?php _e( 'Comment navigation', 'twentyfourteen' ); ?></h4>
		<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'twentyfourteen' ) ); ?></div>
		<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'twentyfourteen' ) ); ?></div>
	</nav><!-- #comment-nav-below -->

<?php
	endif; // Check for comment navigation.
	if ( ! comments_open() ) :
?>
	<p class="no-comments"><?php _e( 'Comments are closed.', 'twentyfourteen' ); ?></p>
<?php
	endif;
}
?>

</div><!-- #comments -->

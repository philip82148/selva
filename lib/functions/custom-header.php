<?php
/**
 * Implement an optional custom header for XeoryExtension
 *
 * See http://codex.wordpress.org/Custom_Headers
 *
 * @package WordPress
 */

/**
 * Set up the WordPress core custom header arguments and settings.
 *
 * @uses add_theme_support() to register support for 3.4 and up.
 * @uses xeory_extension_header_style() to style front-end.
 * @uses xeory_extension_admin_header_style() to style wp-admin form.
 * @uses xeory_extension_admin_header_image() to add custom markup to wp-admin form.
 */
function xeory_extension_custom_header_setup() {
	$args = array(
		// Text color and image (empty to use none).
		'default-text-color'     => '515151',
		'default-image'          => '',

		// Set height and width, with a maximum value for the width.
		'height'                 => 92,
		'width'                  => 1200,
		'max-width'              => 2000,

		// Support flexible height and width.
		'flex-height'            => true,
		'flex-width'             => true,

		// Random image rotation off by default.
		'random-default'         => false,

		// Callbacks for styling the header and the admin preview.
		'wp-head-callback'       => 'xeory_extension_header_style',
		'admin-head-callback'    => 'xeory_extension_admin_header_style',
		'admin-preview-callback' => 'xeory_extension_admin_header_image',
	);

	add_theme_support( 'custom-header', $args );
}
add_action( 'after_setup_theme', 'xeory_extension_custom_header_setup' );

/**
 * Style the header text displayed on the blog.
 *
 * get_header_textcolor() options: 515151 is default, hide text (returns 'blank'), or any hex value.
 */
function xeory_extension_header_style() {
	$default_text_color = get_theme_support( 'custom-header', 'default-text-color' );
	$text_color         = get_header_textcolor();
	$header_image       = get_header_image();

	// If no custom options for text are set, let's bail
	if ( $text_color === $default_text_color && $header_image === false )
		return;

	// If we get this far, we have custom styles.
	?>
	<style type="text/css" id="xeory_extension-header-css">
	<?php
		// Has the text been hidden?
	if ( $text_color !== $default_text_color ):
		if ( ! display_header_text() ) :
	?>
			.site-title,
			.site-description {
				position: absolute;
				clip: rect(1px 1px 1px 1px); /* IE7 */
				clip: rect(1px, 1px, 1px, 1px);
			}
		<?php
			// If the user has set a custom color for the text, use that.
			else :
		?>
			body #header .wrap #logo a,
			body #header .wrap h2 {
				color: #<?php echo $text_color; ?>;
			}
		<?php endif; ?>
	<?php endif; ?>

	<?php if( $header_image ): ?>
		body #header {
			background-image: url("<?php echo $header_image; ?>");
			background-size: cover;
			background-position: center;
		}
		#gnav {
			background: rgba(71, 179, 157, .6);
		}
		#gnav ul li a:hover,
		#gnav ul li.current-menu-item a {
			background: rgba(87, 188, 168, .6);
		}
		body.color01 #gnav {
			background: rgba(6, 171, 200, .6);
		}
		body.color01 #gnav ul li a:hover,
		body.color01 #gnav ul li.current-menu-item a {
			background: rgba(7, 192, 225, .6);
		}
		body.color02 #gnav {
			background: rgba(0, 89, 144, .6);
		}
		body.color02 #gnav ul li a:hover,
		body.color02 #gnav ul li.current-menu-item a {
			background: rgba(0, 105, 170, .6);
		}
		body.color03 #gnav {
			background: rgba(39, 52, 60, .6);
		}
		body.color03 #gnav ul li a:hover,
		body.color03 #gnav ul li.current-menu-item a {
			background: rgba(49, 65, 75, .6);
		}
		body.color04 #gnav {
			background: rgba(251, 82, 53, .6);
		}
		body.color04 #gnav ul li a:hover,
		body.color04 #gnav ul li.current-menu-item a {
			background: rgba(251, 103, 78, .6);
		}
		#gnav ul li a,
			body.color01 #gnav ul li a,
			body.color02 #gnav ul li a,
			body.color03 #gnav ul li a,
			body.color04 #gnav ul li a {
			background: none;
		}
	<?php endif; ?>

	</style>
	<?php
}

/**
 * Style the header image displayed on the Appearance > Header admin panel.
 */
function xeory_extension_admin_header_style() {
?>
	<style type="text/css" id="xeory_extension-admin-header-css">
	.appearance_page_custom-header #headimg {
		border: none;
		font-family: "Open Sans", Helvetica, Arial, sans-serif;
	}
	#headimg h1,
	#headimg h2 {
		line-height: 1.84615;
		margin: 0;
		padding: 0;
	}
	#headimg h1 {
		font-size: 26px;
	}
	#headimg h1 a {
		color: #515151;
		text-decoration: none;
	}
	#headimg h1 a:hover {
		color: #21759b !important; /* Has to override custom inline style. */
	}
	#headimg h2 {
		color: #757575;
		font-size: 13px;
		margin-bottom: 24px;
	}
	#headimg img {
		max-width: <?php echo get_theme_support( 'custom-header', 'max-width' ); ?>px;
	}
	</style>
<?php
}

/**
 * Output markup to be displayed on the Appearance > Header admin panel.
 *
 * This callback overrides the default markup displayed there.
 */
function xeory_extension_admin_header_image() {
	?>
	<div id="headimg">
		<?php
		if ( ! display_header_text() )
			$style = ' style="display:none;"';
		else
			$style = ' style="color:#' . get_header_textcolor() . ';"';
		?>
		<h1 class="displaying-header-text"><a id="name"<?php echo $style; ?> onclick="return false;" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
		<h2 id="desc" class="displaying-header-text"<?php echo $style; ?>><?php bloginfo( 'description' ); ?></h2>
		<?php $header_image = get_header_image();
		if ( ! empty( $header_image ) ) : ?>
			<img src="<?php echo esc_url( $header_image ); ?>" class="header-image" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="" />
		<?php endif; ?>
	</div>
<?php }
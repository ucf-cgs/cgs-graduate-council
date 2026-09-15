<?php
/**
 * Twenty Sixteen functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @link https://codex.wordpress.org/Child_Themes
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * {@link https://codex.wordpress.org/Plugin_API}
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

/**
 * Twenty Sixteen only works in WordPress 4.4 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '4.4-alpha', '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
}

if ( ! function_exists( 'twentysixteen_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 *
 * Create your own twentysixteen_setup() function to override in a child theme.
 *
 * @since Twenty Sixteen 1.0
 */
function twentysixteen_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed at WordPress.org. See: https://translate.wordpress.org/projects/wp-themes/twentysixteen
	 * If you're building a theme based on Twenty Sixteen, use a find and replace
	 * to change 'twentysixteen' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'twentysixteen' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for custom logo.
	 *
	 *  @since Twenty Sixteen 1.2
	 */
	add_theme_support( 'custom-logo', array(
		'height'      => 240,
		'width'       => 240,
		'flex-height' => true,
	) );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 1200, 9999 );

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'twentysixteen' ),
		'social'  => __( 'Social Links Menu', 'twentysixteen' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 *
	 * See: https://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside',
		'image',
		'video',
		'quote',
		'link',
		'gallery',
		'status',
		'audio',
		'chat',
	) );

	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, icons, and column width.
	 */
	add_editor_style( array( 'css/editor-style.css', twentysixteen_fonts_url() ) );

	// Indicate widget sidebars can use selective refresh in the Customizer.
	add_theme_support( 'customize-selective-refresh-widgets' );
}
    include_once('post_types/settings.php');
    include_once('post_types/meetings.php');
    //include_once('post_types/minutes.php');
    //include_once('post_types/agenda.php');
    include_once('post_types/member.php');
    include_once('post_types/membership.php');
    include_once('post_types/file-post.php');
		include_once('post_types/migration_settings.php');
endif; // twentysixteen_setup
add_action( 'after_setup_theme', 'twentysixteen_setup' );

/**
 * Sets the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 *
 * @since Twenty Sixteen 1.0
 */
function twentysixteen_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'twentysixteen_content_width', 840 );
}
add_action( 'after_setup_theme', 'twentysixteen_content_width', 0 );

/**
 * Registers a widget area.
 *
 * @link https://developer.wordpress.org/reference/functions/register_sidebar/
 *
 * @since Twenty Sixteen 1.0
 */
function twentysixteen_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'twentysixteen' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Add widgets here to appear in your sidebar.', 'twentysixteen' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Content Bottom 1', 'twentysixteen' ),
		'id'            => 'sidebar-2',
		'description'   => __( 'Appears at the bottom of the content on posts and pages.', 'twentysixteen' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Content Bottom 2', 'twentysixteen' ),
		'id'            => 'sidebar-3',
		'description'   => __( 'Appears at the bottom of the content on posts and pages.', 'twentysixteen' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'twentysixteen_widgets_init' );

if ( ! function_exists( 'twentysixteen_fonts_url' ) ) :
/**
 * Register Google fonts for Twenty Sixteen.
 *
 * Create your own twentysixteen_fonts_url() function to override in a child theme.
 *
 * @since Twenty Sixteen 1.0
 *
 * @return string Google fonts URL for the theme.
 */
function twentysixteen_fonts_url() {
	$fonts_url = '';
	$fonts     = array();
	$subsets   = 'latin,latin-ext';

	/* translators: If there are characters in your language that are not supported by Merriweather, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Merriweather font: on or off', 'twentysixteen' ) ) {
		$fonts[] = 'Merriweather:400,700,900,400italic,700italic,900italic';
	}

	/* translators: If there are characters in your language that are not supported by Montserrat, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Montserrat font: on or off', 'twentysixteen' ) ) {
		$fonts[] = 'Montserrat:400,700';
	}

	/* translators: If there are characters in your language that are not supported by Inconsolata, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Inconsolata font: on or off', 'twentysixteen' ) ) {
		$fonts[] = 'Inconsolata:400';
	}

	if ( $fonts ) {
		$fonts_url = add_query_arg( array(
			'family' => urlencode( implode( '|', $fonts ) ),
			'subset' => urlencode( $subsets ),
		), 'https://fonts.googleapis.com/css' );
	}

	return $fonts_url;
}
endif;

/**
 * Handles JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @since Twenty Sixteen 1.0
 */
function twentysixteen_javascript_detection() {
	echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}
add_action( 'wp_head', 'twentysixteen_javascript_detection', 0 );

/**
 * Enqueues scripts and styles.
 *
 * @since Twenty Sixteen 1.0
 */
function twentysixteen_scripts() {
	// Add custom fonts, used in the main stylesheet.
	wp_enqueue_style( 'twentysixteen-fonts', twentysixteen_fonts_url(), array(), null );

	// Add Genericons, used in the main stylesheet.
	wp_enqueue_style( 'genericons', get_template_directory_uri() . '/genericons/genericons.css', array(), '3.4.1' );

	// Theme stylesheet.
	wp_enqueue_style( 'twentysixteen-style', get_stylesheet_uri() );

	// Load the Internet Explorer specific stylesheet.
	wp_enqueue_style( 'twentysixteen-ie', get_template_directory_uri() . '/css/ie.css', array( 'twentysixteen-style' ), '20160816' );
	wp_style_add_data( 'twentysixteen-ie', 'conditional', 'lt IE 10' );

	// Load the Internet Explorer 8 specific stylesheet.
	wp_enqueue_style( 'twentysixteen-ie8', get_template_directory_uri() . '/css/ie8.css', array( 'twentysixteen-style' ), '20160816' );
	wp_style_add_data( 'twentysixteen-ie8', 'conditional', 'lt IE 9' );

	// Load the Internet Explorer 7 specific stylesheet.
	wp_enqueue_style( 'twentysixteen-ie7', get_template_directory_uri() . '/css/ie7.css', array( 'twentysixteen-style' ), '20160816' );
	wp_style_add_data( 'twentysixteen-ie7', 'conditional', 'lt IE 8' );

    // Load the html5 shiv.
    wp_enqueue_script( 'twentysixteen-html5', get_template_directory_uri() . '/js/html5.js', array(), '3.7.3' );
    wp_script_add_data( 'twentysixteen-html5', 'conditional', 'lt IE 9' );

    wp_enqueue_script( 'gs-council', get_template_directory_uri() . '/js/gs-council.js', array(), '' );

	wp_enqueue_script( 'twentysixteen-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20160816', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( is_singular() && wp_attachment_is_image() ) {
		wp_enqueue_script( 'twentysixteen-keyboard-image-navigation', get_template_directory_uri() . '/js/keyboard-image-navigation.js', array( 'jquery' ), '20160816' );
	}

	wp_enqueue_script( 'twentysixteen-script', get_template_directory_uri() . '/js/functions.js', array( 'jquery' ), '20160816', true );

	wp_localize_script( 'twentysixteen-script', 'screenReaderText', array(
		'expand'   => __( 'expand child menu', 'twentysixteen' ),
		'collapse' => __( 'collapse child menu', 'twentysixteen' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'twentysixteen_scripts' );

/**
 * Adds custom classes to the array of body classes.
 *
 * @since Twenty Sixteen 1.0
 *
 * @param array $classes Classes for the body element.
 * @return array (Maybe) filtered body classes.
 */
function twentysixteen_body_classes( $classes ) {
	// Adds a class of custom-background-image to sites with a custom background image.
	if ( get_background_image() ) {
		$classes[] = 'custom-background-image';
	}

	// Adds a class of group-blog to sites with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	// Adds a class of no-sidebar to sites without active sidebar.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	return $classes;
}
add_filter( 'body_class', 'twentysixteen_body_classes' );

/**
 * Converts a HEX value to RGB.
 *
 * @since Twenty Sixteen 1.0
 *
 * @param string $color The original color, in 3- or 6-digit hexadecimal form.
 * @return array Array containing RGB (red, green, and blue) values for the given
 *               HEX code, empty array otherwise.
 */
function twentysixteen_hex2rgb( $color ) {
	$color = trim( $color, '#' );

	if ( strlen( $color ) === 3 ) {
		$r = hexdec( substr( $color, 0, 1 ).substr( $color, 0, 1 ) );
		$g = hexdec( substr( $color, 1, 1 ).substr( $color, 1, 1 ) );
		$b = hexdec( substr( $color, 2, 1 ).substr( $color, 2, 1 ) );
	} else if ( strlen( $color ) === 6 ) {
		$r = hexdec( substr( $color, 0, 2 ) );
		$g = hexdec( substr( $color, 2, 2 ) );
		$b = hexdec( substr( $color, 4, 2 ) );
	} else {
		return array();
	}

	return array( 'red' => $r, 'green' => $g, 'blue' => $b );
}

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for content images
 *
 * @since Twenty Sixteen 1.0
 *
 * @param string $sizes A source size value for use in a 'sizes' attribute.
 * @param array  $size  Image size. Accepts an array of width and height
 *                      values in pixels (in that order).
 * @return string A source size value for use in a content image 'sizes' attribute.
 */
function twentysixteen_content_image_sizes_attr( $sizes, $size ) {
	$width = $size[0];

	840 <= $width && $sizes = '(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 1362px) 62vw, 840px';

	if ( 'page' === get_post_type() ) {
		840 > $width && $sizes = '(max-width: ' . $width . 'px) 85vw, ' . $width . 'px';
	} else {
		840 > $width && 600 <= $width && $sizes = '(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 984px) 61vw, (max-width: 1362px) 45vw, 600px';
		600 > $width && $sizes = '(max-width: ' . $width . 'px) 85vw, ' . $width . 'px';
	}

	return $sizes;
}
add_filter( 'wp_calculate_image_sizes', 'twentysixteen_content_image_sizes_attr', 10 , 2 );

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for post thumbnails
 *
 * @since Twenty Sixteen 1.0
 *
 * @param array $attr Attributes for the image markup.
 * @param int   $attachment Image attachment ID.
 * @param array $size Registered image size or flat array of height and width dimensions.
 * @return string A source size value for use in a post thumbnail 'sizes' attribute.
 */
function twentysixteen_post_thumbnail_sizes_attr( $attr, $attachment, $size ) {
	if ( 'post-thumbnail' === $size ) {
		is_active_sidebar( 'sidebar-1' ) && $attr['sizes'] = '(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 984px) 60vw, (max-width: 1362px) 62vw, 840px';
		! is_active_sidebar( 'sidebar-1' ) && $attr['sizes'] = '(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 1362px) 88vw, 1200px';
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'twentysixteen_post_thumbnail_sizes_attr', 10 , 3 );

/**
 * Modifies tag cloud widget arguments to have all tags in the widget same font size.
 *
 * @since Twenty Sixteen 1.1
 *
 * @param array $args Arguments for tag cloud widget.
 * @return array A new modified arguments.
 */
function twentysixteen_widget_tag_cloud_args( $args ) {
	$args['largest'] = 1;
	$args['smallest'] = 1;
	$args['unit'] = 'em';
	return $args;
}
add_filter( 'widget_tag_cloud_args', 'twentysixteen_widget_tag_cloud_args' );

function valueFromMeta( $meta, $key ) {
    if( !empty( $meta[ $key ] ) )
        return $meta[ $key ][0];
    else
        return '';
}

add_action('acf/init', function(){
    if(function_exists('acf_add_options_page')) {
        acf_add_options_page(array(
            'page_title' => __('Graduate Site Settings'),
            'capability'    => 'manage_options',
        ));
    }
});

// Uncomment to allow new custom slug urls
//flush_rewrite_rules();

/**
 * Automatically set post title to policy name where action is added
 *
 * @since Graduate Council 1.9
 */
function gc_policy_update_post_title($post_id) {
	// If this is just a revision, don't update the title yet.
	// if ( wp_is_post_revision( $post_id ) ) {
	// 		return;
	// }	

	// unhook this function so it doesn't loop infinitely
	remove_action('save_post', 'gc_policy_update_post_title');

	$policy_name = get_post_meta( $post_id, 'policy-name', true );
	$post_title = get_the_title( $post_id );
	// Check if the meta for given key exists and title isn't set, then update the title
	if($policy_name && '' == $post_title) {
			$slug = sanitize_text_field(str_replace(' ', '-', strtolower($policy_name)));
			$post_update = array(
					'ID'					=> $post_id,
					'post_title'	=> $policy_name,
					'post_name'		=> $slug
			);

			wp_update_post( $post_update );
	}
	// restore hook
	add_action('save_post', 'gc_policy_update_post_title');
}
add_action('save_post', 'gc_policy_update_post_title');

/**
 * Create taxonomies to support modernization of post meta
 *
 * @since Graduate Council 2.0
 */
function gc_add_taxonomy_types() {
	register_taxonomy(
		'college',
		array(
			0 => 'gs_membership',
		),
		array(
			'labels' => array(
				'name' => 'Colleges',
				'singular_name' => 'College',
				'menu_name' => 'Colleges',
				'all_items' => 'All Colleges',
				'edit_item' => 'Edit College',
				'view_item' => 'View College',
				'update_item' => 'Update College',
				'add_new_item' => 'Add New College',
				'new_item_name' => 'New College Name',
				'search_items' => 'Search Colleges',
				'popular_items' => 'Popular Colleges',
				'separate_items_with_commas' => 'Separate colleges with commas',
				'add_or_remove_items' => 'Add or remove colleges',
				'choose_from_most_used' => 'Choose from the most used colleges',
				'not_found' => 'No colleges found',
				'no_terms' => 'No colleges',
				'items_list_navigation' => 'Colleges list navigation',
				'items_list' => 'Colleges list',
				'back_to_items' => '← Go to colleges',
				'item_link' => 'College Link',
				'item_link_description' => 'A link to a college',
			),
			'public' => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
		)
	);
	register_taxonomy(
		'member_role',
		array(
			0 => 'gs_membership',
		),
		array(
			'labels' => array(
				'name' => 'Member Roles',
				'singular_name' => 'Member Role',
				'menu_name' => 'Member Roles',
				'all_items' => 'All Member Roles',
				'edit_item' => 'Edit Member Role',
				'view_item' => 'View Member Role',
				'update_item' => 'Update Member Role',
				'add_new_item' => 'Add New Member Role',
				'new_item_name' => 'New Member Role',
				'search_items' => 'Search Member Roles',
				'popular_items' => 'Popular Member Roles',
				'separate_items_with_commas' => 'Separate member roles with commas',
				'add_or_remove_items' => 'Add or remove member roles',
				'choose_from_most_used' => 'Choose from the most used member roles',
				'not_found' => 'No member roles found',
				'no_terms' => 'No member roles',
				'items_list_navigation' => 'Member Roles list navigation',
				'items_list' => 'Member Roles list',
				'back_to_items' => '← Go to member roles',
				'item_link' => 'Member Role Link',
				'item_link_description' => 'A link to a member role',
			),
			'public' => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
		)
	);

	register_taxonomy( 'committee', array(
		0 => 'gs_meetings',
		1 => 'gs_membership',
		2 => 'gs_file',
	), array(
		'labels' => array(
			'name' => 'Committees',
			'singular_name' => 'Committee',
			'menu_name' => 'Committees',
			'all_items' => 'All Committees',
			'edit_item' => 'Edit Committee',
			'view_item' => 'View Committee',
			'update_item' => 'Update Committee',
			'add_new_item' => 'Add New Committee',
			'new_item_name' => 'New Committee Name',
			'search_items' => 'Search Committees',
			'not_found' => 'No committees found',
			'no_terms' => 'No committees',
			'items_list_navigation' => 'Committees list navigation',
			'items_list' => 'Committees list',
			'back_to_items' => '← Go to committees',
			'item_link' => 'Committee Link',
			'item_link_description' => 'A link to a committee',
		),
		'public' => true,
		'show_in_menu' => true,
		'show_in_rest' => true,
	) );

	register_taxonomy( 'document-type', array(
		0 => 'gs_file',
	), array(
		'labels' => array(
			'name' => 'Document Types',
			'singular_name' => 'Document Type',
			'menu_name' => 'Document Types',
			'all_items' => 'All Document Types',
			'edit_item' => 'Edit Document Type',
			'view_item' => 'View Document Type',
			'update_item' => 'Update Document Type',
			'add_new_item' => 'Add New Document Type',
			'new_item_name' => 'New Document Type Name',
			'search_items' => 'Search Document Types',
			'not_found' => 'No document types found',
			'no_terms' => 'No document types',
			'items_list_navigation' => 'Document Types list navigation',
			'items_list' => 'Document Types list',
			'back_to_items' => '← Go to document types',
			'item_link' => 'Document Type Link',
			'item_link_description' => 'A link to a document type',
		),
		'public' => true,
		'show_in_menu' => true,
		'show_in_rest' => true,
	) );

	register_taxonomy( 'committee-year', array(
		0 => 'gs_meetings',
		1 => 'gs_membership',
		2 => 'gs_file',
	), array(
		'labels' => array(
			'name' => 'Years',
			'singular_name' => 'Year',
			'menu_name' => 'Years',
			'all_items' => 'All Years',
			'edit_item' => 'Edit Years',
			'view_item' => 'View Years',
			'update_item' => 'Update Years',
			'add_new_item' => 'Add New Years',
			'new_item_name' => 'New Years Name',
			'search_items' => 'Search Years',
			'not_found' => 'No years found',
			'no_terms' => 'No years',
			'items_list_navigation' => 'Years list navigation',
			'items_list' => 'Years list',
			'back_to_items' => '← Go to years',
			'item_link' => 'Years Link',
			'item_link_description' => 'A link to a years',
		),
		'public' => true,
		'show_in_menu' => true,
		'show_in_rest' => true,
	) );
}
add_action('init', 'gc_add_taxonomy_types');

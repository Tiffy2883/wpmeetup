<?php

// I need some menus
if ( function_exists( 'register_nav_menus' ) ) {
	register_nav_menus( array (
		'nav_header' => 'Header Navigation',
		'link_here' => 'Link',
		'nav_footer' => 'Footer Navigation' )
	);
}

if ( ! class_exists( 'wpmeetup_theme' ) ) {
	
	if ( function_exists( 'add_action' ) )
		add_action( 'init', array( 'wpmeetup_theme', 'get_object' ) );

	class wpmeetup_theme {
		static private $classobj = NULL;
		public function get_object() {
			if ( NULL === self :: $classobj ) {
				self :: $classobj = new self;
			}
			return self :: $classobj;
		}
			
		public function __construct() {
			
			// Google Map
			add_filter( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			
			// Login Style
			add_filter( 'login_headerurl', array( $this, 'style_login' ) );
			add_filter( 'login_headertitle', array( $this, 'style_login' ) );
			add_filter( 'login_head', array( $this, 'style_login' ) );
		}
		
		public function wp_enqueue_scripts() {
			wp_enqueue_script( 'google_map_api', 'http://maps.google.com/maps/api/js?sensor=true', array( 'jquery' ) );
			wp_enqueue_script( 'google_map_switcher', get_bloginfo( 'template_url' ) . '/js/switcher.js' );
			wp_localize_script( 'google_map_switcher', 'switcher_vars', $this->load_js_vars() );
		}
		
		public function load_js_vars () {
			return array( 'template_dir' => get_bloginfo( 'template_url' ) );
		}
		
		public function  style_login() {
			?>
			<style>
				html, body
				{
					background:		url(<?php bloginfo( 'template_url' ) ?>/images/background.png) repeat #525d79 !important;
				}
				#login h1
				{
				}
				#login h1 a
				{
					background: url(http://localhost/wpmeetup/wp/wp-content/themes/wpmeetup/images/header.png) no-repeat !important;
					height: 130px;
					line-height: 2;
					text-decoration: none;
					width: 570px;
					margin-left: -130px;
					margin-bottom: 25px;
				}
				
				.login #backtoblog a,
				.login #nav a,
				.login #nav {
					color: #fff !important;
					text-shadow: none !important;
				}
			</style>
			<?php
		}
		
		public function wp_list_comments( $comment, $args, $depth ) {
			$GLOBALS[ 'comment' ] = $comment;
			switch ( $comment->comment_type ) :
			case '' : ?>
					<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
						<div id="comment-<?php comment_ID(); ?>" class="comment-text">
							<a name="comment-<?php comment_ID(); ?>"></a>
							<div class="comment-date">
								<?php echo get_avatar( $comment, 32 ); ?> 
							</div>
							
							<strong><?php comment_author_link(); ?> sagte am <?php comment_date(); ?>:</strong>
							
							<?php if ( $comment->comment_approved == '0' ) : ?>
								<br /><em>Dein Kommentar wartet auf Freischaltung</em><br />
							<?php endif; ?>
							<?php comment_text() ?>
							
							
						</div>
					<?php
				break;
				case 'pingback'  :
				case 'trackback' :
					?>
					<a name="comment-<?php comment_ID(); ?>"></a>
					<div id="comment" <?php comment_class(); ?>>
						<em>Pingback von: <?php comment_author_link(); ?></em>
					</div>
					<?php
				break;
			endswitch;
		}
	}
}
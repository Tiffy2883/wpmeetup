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

/**
 * Find all town names where meetups have ever happened or will happen.
 * 
 * @return array
 */
function wpm_find_towns() {
	global $wpdb;
	$sql = '
		SELECT DISTINCT
			meta_value
		FROM
			' . $wpdb->postmeta . '
		WHERE
			meta_key LIKE "meetup_town"';
	return $wpdb->get_col( $sql );
}

/**
 * Finds next meetup for the given town.
 * 
 * @param $town string
 * @return object
 */
function wpm_next_meetup_for_town( $town ) {
	global $wpdb;
	$sql = '
		SELECT
			p.ID, p.post_name, m.meta_value town, m2.meta_value meetup_date
		FROM wp_posts p
			INNER JOIN wp_postmeta m ON p.ID = m.post_id AND m.meta_key = "meetup_town"
			INNER JOIN wp_postmeta m2 ON p.ID = m2.post_id AND m2.meta_key = "meetup_date"
		WHERE
			p.post_type = "wpmeetups"
			AND (p.post_status = "publish" OR p.post_status = "private")
		HAVING
			town = "' . $town . '"
			AND	meetup_date >= DATE(NOW())
		ORDER BY
			meetup_date ASC
		LIMIT 0,1
	';
	return $wpdb->get_row( $sql );
}

/**
 * Print event div containing data for google map.
 * 
 * @param $post_id
 */
function wpm_the_event( $post_id ) {
	$meetup_data = get_post_meta( $post_id, 'meetup', TRUE );
	?>
	<div class="meetup_event"
		data-geo-lat="<?php echo $meetup_data[ 'latitude' ] ?>"
		data-geo-lng="<?php echo $meetup_data[ 'longitude' ] ?>"
		data-town="<?php echo $meetup_data[ 'town' ] ?>"
		data-location="<?php echo $meetup_data[ 'location' ] ?>"
		data-location_url="<?php echo $meetup_data[ 'location_url' ] ?>"
		data-street="<?php echo $meetup_data[ 'street' ] ?>"
		data-number="<?php echo $meetup_data[ 'number' ] ?>"
		data-plz="<?php echo $meetup_data[ 'plz' ] ?>"
		data-date="<?php echo $meetup_data[ 'date' ] ?>"
		data-time="<?php echo $meetup_data[ 'time' ] ?>"
		data-permalink="<?php echo get_permalink( $post_id ) ?>"
		>
	</div>
	<?php
}
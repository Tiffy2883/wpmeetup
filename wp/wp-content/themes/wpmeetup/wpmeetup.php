<?php /* Template Name: Meetup Startseite */ ?>
<?php

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

?>
<?php get_header(); ?>

	
	<div id="main">

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<div class="box">
			<h2 class="box"><?php the_title(); ?></h2>
			<div class="inside">

				<p id="map"></p>
				
				<div id="map_data">
					<?php $towns = wpm_find_towns(); ?>
					<?php foreach ( $towns as $town ): ?>
						<?php $meetup_object = wpm_next_meetup_for_town( $town ); ?>
						<?php if ( $meetup_object ): ?>
							<?php $meetup_data = get_post_meta( $meetup_object->ID, 'meetup', TRUE ); ?>
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
								>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>

			</div>
			<div class="bottom"></div>
		</div>
		
		<?php endwhile; endif; ?>
	
	</div>


<?php get_sidebar(); ?>
<?php get_footer(); ?>
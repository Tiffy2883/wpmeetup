<?php /* Template Name: Meetup Startseite */ ?>
<?php get_header(); ?>

	
	<div id="main">

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<div class="box">
			<h2 class="box"><?php the_title(); ?></h2>
			<div class="inside">

				<p id="map"></p>
				
				<div id="map_data">
					<?php
					$towns = wpm_find_towns();
					foreach ( $towns as $town ) {
						$meetup_object = wpm_next_meetup_for_town( $town );
						if ( $meetup_object )
							wpm_the_event( $meetup_object->ID );
					}
					?>
				</div>

			</div>
			<div class="bottom"></div>
		</div>
		
		<?php endwhile; endif; ?>
	
	</div>


<?php get_sidebar(); ?>
<?php get_footer(); ?>
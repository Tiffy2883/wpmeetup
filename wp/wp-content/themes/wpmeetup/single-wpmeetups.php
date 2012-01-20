<?php get_header(); ?>

	
	<div id="main">

		<?php $meetup_data = get_post_meta( get_the_ID(), 'meetup', TRUE ); ?>

		<div class="box">
			<h2 class="box">Dein Meetup in: <?php the_title(); ?></h2>
			<div class="inside">

				<p>Das n&auml;chste Meetup findet am <?php echo $meetup_data[ 'date' ]; ?> um <?php echo $meetup_data[ 'time' ]; ?> im 
					<?php if ( '' != trim( $meetup_data[ 'location_url' ] ) ) : ?>
						<a href="<?php echo $meetup_data[ 'location_url' ]; ?>">
					<?php endif; ?>
					<?php echo $meetup_data[ 'location' ]; ?>
					<?php if ( '' != trim( $meetup_data[ 'location_url' ] ) ) : ?>
						</a>
					<?php endif; ?>
					statt:</p>
				<p id="single_map" data-geo-lat="<?php echo $meetup_data[ 'latitude' ]; ?>" data-geo-lng="<?php echo $meetup_data[ 'longitude' ]; ?>"></p>
				<p>
					<strong>Die genaue Adresse:</strong><br />
					<?php echo $meetup_data[ 'location' ]; ?><br />
					<?php echo $meetup_data[ 'street' ]; ?> <?php echo $meetup_data[ 'number' ]; ?><br />
					<?php echo $meetup_data[ 'plz' ]; ?> <?php echo $meetup_data[ 'town' ]; ?>
				</p>

			</div>
			<div class="bottom"></div>
		</div>
		
		<?php comments_template(); ?>
		
	</div>


<?php get_sidebar(); ?>
<?php get_footer(); ?>
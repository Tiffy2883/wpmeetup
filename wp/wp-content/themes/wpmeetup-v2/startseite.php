<?php
/*
Template Name:  Startseite
*/
?>
<?php get_header(); ?>
<div class="container1">
	<div class="container11">
    	<div class="shadowtop"></div>
        <div class="banner">
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
        <div class="shadowbottom"></div>
    </div>
</div>
<div class="container2">
	<div class="container22">
    	<div class="contentstart">
    		foo
        </div>
	</div>
</div>
<?php get_footer(); ?>
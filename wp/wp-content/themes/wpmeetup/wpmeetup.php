<?php /* Template Name: Meetup Startseite */ ?>
<?php get_header(); ?>

	
	<div id="main">

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<div class="box">
			<h2 class="box"><?php the_title(); ?></h2>
			<div class="inside">

				Google Map

			</div>
			<div class="bottom"></div>
		</div>
		
		<?php endwhile; endif; ?>
	
	</div>


<?php get_sidebar(); ?>
<?php get_footer(); ?>
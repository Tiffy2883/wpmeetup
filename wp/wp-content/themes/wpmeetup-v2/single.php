<?php get_header(); ?>
<div class="container2">
	<div class="container22">
    	<div class="shadowtop"></div>
        <?php ds_breadcrumb(); ?>
        <div class="content">
			<?php if (have_posts()) : ?><?php while (have_posts()) : the_post(); ?>
        	<div <?php post_class();?>>
                <h2><?php the_title(); ?></h2>
            	<?php the_content(); ?>
        	</div>
            <?php endwhile; ?>
    		<?php endif; ?>
        </div>
	</div>
</div>
<?php get_footer(); ?>
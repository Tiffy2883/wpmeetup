<?php get_header(); ?>
<div class="container1">
	<div class="container11">
    	<div class="category">
        	<ul><li<?php if (is_front_page()) { ?> class="current-cat"<?php } else { ?><?php } ?>><a href="<?php bloginfo('url'); ?>">Alle Projekte</a></li><?php wp_list_categories('orderby=name&title_li='); ?></ul>
       	</div>
    	<div class="content">
        <div class="box">
		<?php if (have_posts()) : ?><?php while (have_posts()) : the_post(); ?>
        		<div class="refimage">
            		<?php the_post_thumbnail('startseite'); ?>
                    <?php if ( in_category( '15' )) { ?>
                    	<div class="new">Neu</div>
                    <?php } ?>
           	  		<div class="info"><?php the_title(); ?></div>
                	<div class="categorie"><div class="infobox">
                    	<div class="ub"><?php the_title(); ?></div>
                        <div class="inhalt">
							<?php foreach((get_the_category()) as $category) {
    							echo $category->cat_name . ' _ '; 
							} ?></div>
                        <div class="url"><a href="http://www.<?php echo get_post_meta($post->ID, "url", true); ?>/"><?php echo get_post_meta($post->ID, "url", true); ?></a></div>
                        <div class="link" onclick="location.href='<?php the_permalink(); ?>';" onkeypress="location.href='<?php the_permalink(); ?>';"></div>
                    </div></div>
          		</div>
        <?php endwhile; ?>
        <?php endif; ?>
        </div>
        </div>
        <span class="clear"></span>
	</div>
</div>
<?php get_footer(); ?>
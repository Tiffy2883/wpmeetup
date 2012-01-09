				<br class="clear" />
			</div>

			<div id="footer">
				<div class="fltlft">
					CC-BY-SA 3.0
					<?php
					
					$args->theme_location = 'nav_footer';
					
					// Get the nav menu based on the theme_location
					if ( ! $menu && $args->theme_location && ( $locations = get_nav_menu_locations() ) && isset( $locations[ $args->theme_location ] ) )
						$menu = wp_get_nav_menu_object( $locations[ $args->theme_location ] );
					
					// If the menu exists, get its items.
					if ( $menu && ! is_wp_error( $menu ) && !isset( $menu_items ) )
						$menu_items = wp_get_nav_menu_items( $menu->term_id );
					
					foreach ( $menu_items as $item ) {
						! $i ? $i = 1 : $i++;
						
						if ( 1 != $i )
							echo ' | ';
						?>
						<a href="<?php echo $item->url; ?>"><?php echo $item->title; ?></a>
						<?php
					}
					?>
				</div>
				<div class="fltrgt">
					Wir leben <a href="http://wordpress.org">WordPress</a>
				</div>
			</div>
		</div>
		<?php wp_footer(); ?>
	</body>
</html>
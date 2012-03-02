	<div class="container3">
		<div class="container33">
			<div class="footer">
				<div class="pfeil"></div>
				<div class="shadowtop"></div>
				<div class="social">
					<?php wp_nav_menu( array( 'theme_location' => 'link' ) ); ?>
				</div>
				<div class="info">
					<a href="http://www.inpsyde.com/"><img src="<?php bloginfo('template_directory'); ?>/images/inpsyde.png" /></a>
					<p><br />CC-BY-SA <?php echo date( 'Y' ); ?><br /><?php bloginfo( 'name' ); ?> ist ein Projekt<br />der Inpsyde GmbH</p>
				</div>
				<div class="meta">
					<?php wp_nav_menu( array( 'theme_location' => 'metanavigation' ) ); ?>
				</div>
			</div>
		</div>
	</div>
<?php wp_footer(); ?>
</body>
</html>
		<br />
		<div class="box">
			<h2 class="box">Ich werde kommen!</h2>
			<div class="inside">
				<p>Wenn du wei&szlig;t, dass du kommst, dann trag dich einfach mit deinen Namen hier ein. So k&ouml;nnen wir auch besser planen und zur Not eine gr&ouml;&szlig;ere Location suchen. Vielleicht kannst du hier auch gleich ein Thema ank&uuml;ndigen, &uuml;ber welches du gerne sprechen willst.</p>
				<form action="http://localhost/wpmeetup/wp/wp-comments-post.php" method="post" id="commentform">

					<p><input type="text" name="author" id="author" value="Thomas" size="22" tabindex="1" aria-required="true">
					<label for="author"><small>Name (wird gebraucht)</small></label></p>
					
					<p><input type="text" name="email" id="email" value="t.herzog@inpsyde.com" size="22" tabindex="2" aria-required="true">
					<label for="email"><small>Mail (wird nicht ver&ouml;ffentlich, aber gebraucht)</small></label></p>
					
					<p><input type="text" name="url" id="url" value="" size="22" tabindex="3">
					<label for="url"><small>Webseite</small></label></p>
					
					<p><textarea name="comment" id="comment" cols="58" rows="10" tabindex="4"></textarea></p>
					
					<p><input name="submit" type="submit" id="submit" tabindex="5" value="Submit Comment">
					<input type="hidden" name="comment_post_ID" value="<?php the_ID(); ?>" id="comment_post_ID">
					<input type="hidden" name="comment_parent" id="comment_parent" value="0">
					</p>
				
				</form>
			</div>
			<div class="bottom"></div>
		</div>
		
		<?php if ( have_comments() ) : ?>
		
			<br />
			<div class="box">
				<h2 class="box">Bisher <?php comments_number( 'hat niemand', 'hat eine Person', 'haben % Personen' ); ?> interesse</h2>
				<div class="inside">
				
					<div id="comments">
				        <ol class="commentlist">
				            <?php $wpmeetup_theme = wpmeetup_theme::get_object(); ?>
				            <?php wp_list_comments( array( 'callback' => array( $wpmeetup_theme, 'wp_list_comments' ) ) ); ?>
				        </ol>
				    </div>
	
				</div>
				<div class="bottom"></div>
			</div>
		
		    <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
		    <div class="navigation">
		        <div class="nav-previous"><?php previous_comments_link( '&laquo; &auml;ltere Kommentare' ); ?></div>
		        <div class="nav-next"><?php next_comments_link( 'Neuere Kommentare &raquo;' ); ?></div>
		    </div>
		    <?php endif; ?>
		<?php endif; ?>
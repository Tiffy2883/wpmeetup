				<div id="sidebar">
				
					<?php if ( ! is_user_logged_in() ) : ?>
				
					<div class="box">
						<h2 class="box">Login</h2>
						<div class="inside">
							<p>Um ein Meetup einzutragen musst du dich einloggen. <a href="<?php bloginfo( 'url' ); ?>/wp-login.php?action=register">Registrieren kannst du dich hier</a>.</p>
							<?php wp_login_form(); ?>
							<br class="clear" />
						</div>
						<div class="bottom"></div>
					</div>
					
					<?php else : ?>

					<div class="box">
						<h2 class="box">Meetup eintragen</h2>
						<div class="inside">
						
							<?php
								if ( isset( $_POST[ 'meetup_entry' ] ) ) {
									
									$errors = array();
									if ( '' == trim( $_POST[ 'meetup' ][ 'town' ] ) ) {
										$errors[ 'town' ] = 'Bitte gibt eine Stadt an!';
									}
									if ( '' == trim( $_POST[ 'meetup' ][ 'plz' ] ) ) {
										$errors[ 'plz' ] = 'Bitte gibt eine Postleitzahl an!';
									}
									if ( '' == trim( $_POST[ 'meetup' ][ 'street' ] ) ) {
										$errors[ 'street' ] = 'Bitte gibt eine Strasse an!';
									}
									if ( '' == trim( $_POST[ 'meetup' ][ 'number' ] ) ) {
										$errors[ 'number' ] = 'Bitte gibt eine Strassennummer an!';
									}
									if ( '' == trim( $_POST[ 'meetup' ][ 'location' ] ) ) {
										$errors[ 'location' ] = 'Bitte gibt eine Lokation an!';
									}
									if ( '' == trim( $_POST[ 'meetup' ][ 'date' ] ) ) {
										$errors[ 'date' ] = 'Bitte gibt ein Datum an!';
									}
									if ( '' == trim( $_POST[ 'meetup' ][ 'time' ] ) ) {
										$errors[ 'time' ] = 'Bitte gibt eine Zeit an!';
									}
									
									if ( is_array( $errors ) && 0 < count( $errors ) ) {
										?>
										<div class="error">
											<p>
												<ul>
													<?php foreach ( $errors as $error ) : ?>
														<li><?php echo $error; ?></li>
													<?php endforeach; ?>
												</ul>
											</p>
										</div>
										<?php
									}
									else {
										$post_data = array(
											'post_author'	=> get_current_user_id(),
											'post_type'		=> 'wpmeetups',
											'post_status'	=> 'pending',
											'post_title'	=> $_POST[ 'meetup' ][ 'town' ],
										);
										
										$new_post_id = wp_insert_post( $post_data );
										
										// Dateformat
										$date = explode( '.', $_POST[ 'meetup' ][ 'date' ] );
										$date = $date[ 2 ] . '-' . $date[ 1 ] . '-' . $date[ 0 ];
											
										update_post_meta( $new_post_id, 'meetup_date', $date );
										update_post_meta( $new_post_id, 'meetup_town', $_POST[ 'meetup' ][ 'town' ] );
										update_post_meta( $new_post_id, 'meetup_street', $_POST[ 'meetup' ][ 'street' ] );
										update_post_meta( $new_post_id, 'meetup_number', $_POST[ 'meetup' ][ 'number' ] );
										update_post_meta( $new_post_id, 'meetup_plz', $_POST[ 'meetup' ][ 'plz' ] );
										update_post_meta( $new_post_id, 'meetup', $_POST[ 'meetup' ] );
										
										?>
										<div class="updated">
											<p>
												Meetup wurde eingetragen und wird nun von den Moderatoren gepr&uuml;ft!
											</p>
										</div>
										<?php
										$_POST = array();
									}
								}
							?>
						
							<form action="" method="post">
								<label for="">Stadt und PLZ</label>
								<input type="text" value="<?php echo $_POST[ 'meetup' ][ 'town' ]; ?>" name="meetup[town]" class="town <?php if ( $errors[ 'town' ] ) echo 'error'; ?>" />
								<input type="text" value="<?php echo $_POST[ 'meetup' ][ 'plz' ]; ?>" name="meetup[plz]" class="plz <?php if ( $errors[ 'plz' ] ) echo 'error'; ?>" />
								
								<label for="">Strasse und Nummer</label>
								<input type="text" value="<?php echo $_POST[ 'meetup' ][ 'street' ]; ?>" name="meetup[street]" class="street <?php if ( $errors[ 'street' ] ) echo 'error'; ?>" />
								<input type="text" value="<?php echo $_POST[ 'meetup' ][ 'number' ]; ?>" name="meetup[number]" class="number <?php if ( $errors[ 'number' ] ) echo 'error'; ?>" />
								
								<label for="">Location</label>
								<input type="text" value="<?php echo $_POST[ 'meetup' ][ 'location' ]; ?>" name="meetup[location]" <?php if ( $errors[ 'location' ] ) echo 'class="error"'; ?> />
								
								<label for="">Location URL</label>
								<input type="text" value="<?php echo $_POST[ 'meetup' ][ 'location_url' ]; ?>" name="meetup[location_url]" <?php if ( $errors[ 'location_url' ] ) echo 'class="error"'; ?> />
								
								<label for="">Datum und Zeit</label>
								<input type="text" value="<?php echo $_POST[ 'meetup' ][ 'date' ]; ?>" name="meetup[date]" class="date <?php if ( $errors[ 'date' ] ) echo 'error'; ?>" placeholder="dd.mm.jjjj" />
								<input type="text" value="<?php echo $_POST[ 'meetup' ][ 'time' ]; ?>" name="meetup[time]" class="time <?php if ( $errors[ 'time' ] ) echo 'error'; ?>" placeholder="hh:mm" />
								
								<input name="meetup_entry" type="submit" class="fltrgt button-primary" value="Meetup eintragen" />
							</form>

							<br class="clear" />
						</div>
						<div class="bottom"></div>
					</div>
					<br />
					
					<div class="box">
					<h2 class="box">Meine eingetragenen Meetups</h2>
						<div class="inside">
							<p>Es werden nur die n&auml;chsten Meetups angezeigt. Vergangene Meetups erscheinen nicht in dieser Liste.</p>
							<?php
								$my_meetup_query_args = array(
									'author'	=> get_current_user_id(),
									'post_type'	=> 'wpmeetups',
									'meta_key' => 'meetup_date',
									'meta_value' => date( 'Y-m-d' ),
									'meta_compare' => '>='
								);
								
								$my_meetup_query = new WP_Query( $my_meetup_query_args );
								
								if ( $my_meetup_query->have_posts() ) :
									?>
									<ul>
									<?php
									while( $my_meetup_query->have_posts() ) :
										$my_meetup_query->the_post();
										$meetup_data = get_post_meta( get_the_ID(), 'meetup', TRUE );
										
										?>
										<li><em><?php echo $meetup_data[ 'date' ] ?></em> in <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
										<?php
									endwhile;
									?>
									</ul>
									<?php
								else :
									?>
								<p><em>Derzeit hast du kein Meetup eingetragen.</em></p>
									<?php
								endif;
								
								wp_reset_query();
							?>
						
						</div>
						<div class="bottom"></div>
					</div>
					
					<?php endif; ?>
				
				</div>
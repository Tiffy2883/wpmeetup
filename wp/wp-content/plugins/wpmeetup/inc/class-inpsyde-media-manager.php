<?php
// Load WordPress environment
$thispath = dirname( __FILE__ );
$path_seperator = '/';
$path_to_load = explode( $path_seperator, $thispath );

//try windows folderlist style
if ( count( $path_to_load ) < 2 ) {
    $path_seperator = '\\';
    $path_to_load = explode( $path_seperator, $thispath );
}

$found = FALSE;
$length = count( $path_to_load );
$count = 0;

while ( FALSE == $found && $count < 10 ) {

    $count++;
    array_pop( $path_to_load );
    $wploadpath = implode( $path_seperator, $path_to_load ) . $path_seperator . 'wp-admin/admin.php';

    if ( @file_exists( $wploadpath ) ) {

	$found = TRUE;

	if ( !isset( $_GET[ 'inline' ] ) )
	    define( 'IFRAME_REQUEST', TRUE );

	require_once( $wploadpath );
	require_once( ABSPATH . 'wp-includes/post-thumbnail-template.php' );
    }
}

//WP successfully loaded
if ( '' == ABSPATH )
    die( __( 'Could not find WP' ) );

/** Load WordPress Administration Bootstrap */
//require_once( ABSPATH . 'wp-admin/admin.php' );

if ( !current_user_can( 'upload_files' ) )
    wp_die( __( 'You do not have permission to upload files.' ) );

wp_enqueue_style( 'imgareaselect' );
wp_enqueue_style( 'wp-admin' );
wp_enqueue_style( 'media' );
wp_enqueue_script( 'plupload-handlers' );
wp_enqueue_script( 'image-edit' );

wp_enqueue_script( 'inpsyde-galleries-pro-js', plugins_url( '/js/', dirname( __FILE__ ) ) . 'inpsyde-galleries-pro.js', array( 'jquery-form' ) );

add_filter( 'attachment_fields_to_edit', 'igp_attachment_fields', 10, 2 );

@header( 'Content-Type: ' . get_option( 'html_type' ) . '; charset=' . get_option( 'blog_charset' ) );

// Update the attachment
if ( ISSET( $_GET[ 'update' ] ) && 'true' == $_GET[ 'update' ] )
    igp_update_attachment();

// Draw the form
wp_iframe( 'igp_draw_form', 'image', '', 0 );

/**
 * Remove some form fields
 * 
 * @param type $fields
 * @param type $post
 * @return type 
 */
function igp_attachment_fields( $fields, $post ) {

    unset( $fields[ 'align' ] );

    return $fields;
}

/**
 * Output the form
 * 
 * @global type $redir_tab 
 */
function igp_draw_form() {

    $attachment_id = intval( $_REQUEST[ 'attachment_id' ] );
    ?><script type="text/javascript">post_id = <?php echo intval( $_REQUEST[ 'post_id' ] ); ?>; attachment_id = <?php echo $attachment_id; ?>;</script><?php
    $args = NULL;

    global $redir_tab;

    if ( ( $attachment_id = intval( $attachment_id ) ) && $thumb_url = wp_get_attachment_image_src( $attachment_id, 'thumbnail', true ) )
	$thumb_url = $thumb_url[ 0 ];
    else
	$thumb_url = false;

    $post = get_post( $attachment_id );
    $current_post_id = !empty( $_GET[ 'post_id' ] ) ? intval( $_GET[ 'post_id' ] ) : 0;

    $default_args = array( 'errors' => null, 'send' => $current_post_id ? post_type_supports( get_post_type( $current_post_id ), 'editor' ) : true, 'delete' => true, 'toggle' => true, 'show_title' => true );
    $args = wp_parse_args( $args, $default_args );
    $args = apply_filters( 'get_media_item_args', $args );
    extract( $args, EXTR_SKIP );

    $toggle_on = __( 'Show' );
    $toggle_off = __( 'Hide' );

    $filename = esc_html( basename( $post->guid ) );
    $title = esc_attr( $post->post_title );

    if ( $_tags = get_the_tags( $attachment_id ) ) {
	foreach ( $_tags as $tag )
	    $tags[ ] = $tag->name;
	$tags = esc_attr( join( ', ', $tags ) );
    }

    $post_mime_types = get_post_mime_types();
    $keys = array_keys( wp_match_mime_types( array_keys( $post_mime_types ), $post->post_mime_type ) );
    $type = array_shift( $keys );
    $type_html = "<input type='hidden' id='type-of-$attachment_id' value='" . esc_attr( $type ) . "' />";

    $form_fields = get_attachment_fields_to_edit( $post, $errors );
    
    unset( $form_fields[ 'image_url' ] );
    unset( $form_fields[ 'url' ] );

    if ( $toggle ) {
	$class = 'startopen';
	$toggle_links = "
	    <a class='toggle describe-toggle-on' href='#'>$toggle_on</a>
	    <a class='toggle describe-toggle-off' href='#'>$toggle_off</a>";
    } else {
	$class = '';
	$toggle_links = '';
    }

    $display_title = (!empty( $title ) ) ? $title : $filename; // $title shouldn't ever be empty, but just in case
    $display_title = $show_title ? "<div class='filename new'><span class='title'>" . wp_html_excerpt( $display_title, 60 ) . "</span></div>" : '';

    $gallery = ( ( isset( $_REQUEST[ 'tab' ] ) && 'gallery' == $_REQUEST[ 'tab' ] ) || ( isset( $redir_tab ) && 'gallery' == $redir_tab ) );
    $order = '';

    foreach ( $form_fields as $key => $val ) {
	if ( 'menu_order' == $key ) {
	    if ( $gallery )
		$order = "<div class='menu_order'> <input class='menu_order_input' type='text' id='attachments[$attachment_id][menu_order]' name='attachments[$attachment_id][menu_order]' value='" . esc_attr( $val[ 'value' ] ) . "' /></div>";
	    else
		$order = "<input type='hidden' name='attachments[$attachment_id][menu_order]' value='" . esc_attr( $val[ 'value' ] ) . "' />";

	    unset( $form_fields[ 'menu_order' ] );
	    break;
	}
    }

    $media_dims = '';
    $meta = wp_get_attachment_metadata( $post->ID );
    if ( is_array( $meta ) && array_key_exists( 'width', $meta ) && array_key_exists( 'height', $meta ) )
	$media_dims .= "<span id='media-dims-$post->ID'>{$meta[ 'width' ]}&nbsp;&times;&nbsp;{$meta[ 'height' ]}</span> ";
    $media_dims = apply_filters( 'media_meta', $media_dims, $post );

    $image_edit_button = '';
    if ( gd_edit_image_support( $post->post_mime_type ) ) {
	$nonce = wp_create_nonce( "image_editor-$post->ID" );
	$image_edit_button = "<input type='button' id='imgedit-open-btn-$post->ID' onclick='imageEdit.open( $post->ID, \"$nonce\" )' class='button' value='" . esc_attr__( 'Edit Image' ) . "' /> <img src='" . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . "' class='imgedit-wait-spin' alt='' />";
    }

    $attachment_url = get_permalink( $attachment_id );

    $post_id = isset( $_REQUEST[ 'post_id' ] ) ? intval( $_REQUEST[ 'post_id' ] ) : 0;

    $form_action_url = plugins_url( "/", __FILE__ ) . "class-inpsyde-media-manager.php?update=true&type=image&post_id=$post_id&attachment_id=$attachment_id";
    $form_class = 'media-upload-form type-form validate';

    if ( get_user_setting( 'uploader' ) )
	$form_class .= ' html-uploader';

    $item = "<form enctype=\"multipart/form-data\" method=\"post\" action=\"" . esc_attr( $form_action_url ) . "\" class=\"$form_class\" id=\"$type-form\" >";
    $item.= submit_button( '', 'hidden', 'save', false );
    $item.= "<input type=\"hidden\" name=\"post_id\" id=\"post_id\" value=\"" . intval( $post_id ) . "\" />";
    $item.= wp_nonce_field( 'media-form' );

    $item.= "<div class='media-item'>
	    <table class='slidetoggle describe'>
		<thead class='media-item-info' id='media-head-$post->ID'>
		<tr valign='top'>
			<td class='A1B1' id='thumbnail-head-$post->ID'>
			<p><a href='$attachment_url' target='_blank'><img class='thumbnail' src='$thumb_url' alt='' /></a></p>
			<p>$image_edit_button</p>
			</td>
			<td>
			<p><strong>" . __( 'File name:' ) . "</strong> $filename</p>
			<p><strong>" . __( 'File type:' ) . "</strong> $post->post_mime_type</p>
			<p><strong>" . __( 'Upload date:' ) . "</strong> " . mysql2date( get_option( 'date_format' ), $post->post_date ) . '</p>';
    if ( !empty( $media_dims ) )
	$item .= "<p><strong>" . __( 'Dimensions:' ) . "</strong> $media_dims</p>\n";

    $item .= "</td></tr>\n";



    $item .= "
		</thead>
		<tbody>
		<tr><td colspan='2' class='imgedit-response' id='imgedit-response-$post->ID'></td></tr>
		<tr><td style='display:none' colspan='2' class='image-editor' id='image-editor-$post->ID'></td></tr>\n";

    $defaults = array(
	'input' => 'text',
	'required' => false,
	'value' => '',
	'extra_rows' => array( ),
    );

    if ( $send )
	$send = get_submit_button( __( 'Insert into Post' ), 'button', "send[$attachment_id]", false );
    if ( $delete && current_user_can( 'delete_post', $attachment_id ) ) {
	if ( !EMPTY_TRASH_DAYS ) {
	    $delete = "<a href='" . wp_nonce_url( "post.php?action=delete&amp;post=$attachment_id", 'delete-attachment_' . $attachment_id ) . "' id='del[$attachment_id]' class='delete'>" . __( 'Delete Permanently' ) . '</a>';
	} elseif ( !MEDIA_TRASH ) {
	    $delete = "<a href='#' class='del-link' onclick=\"document.getElementById('del_attachment_$attachment_id').style.display='block';return false;\">" . __( 'Delete' ) . "</a>
			 <div id='del_attachment_$attachment_id' class='del-attachment' style='display:none;'>" . sprintf( __( 'You are about to delete <strong>%s</strong>.' ), $filename ) . "
			 <a href='" . wp_nonce_url( "post.php?action=delete&amp;post=$attachment_id", 'delete-attachment_' . $attachment_id ) . "' id='del[$attachment_id]' class='button'>" . __( 'Continue' ) . "</a>
			 <a href='#' class='button' onclick=\"this.parentNode.style.display='none';return false;\">" . __( 'Cancel' ) . "</a>
			 </div>";
	} else {
	    $delete = "<a href='" . wp_nonce_url( "post.php?action=trash&amp;post=$attachment_id", 'trash-attachment_' . $attachment_id ) . "' id='del[$attachment_id]' class='delete'>" . __( 'Move to Trash' ) . "</a>
			<a href='" . wp_nonce_url( "post.php?action=untrash&amp;post=$attachment_id", 'untrash-attachment_' . $attachment_id ) . "' id='undo[$attachment_id]' class='undo hidden'>" . __( 'Undo' ) . "</a>";
	}
    } else {
	$delete = '';
    }

    $thumbnail = '';
    $calling_post_id = 0;
    if ( isset( $_GET[ 'post_id' ] ) )
	$calling_post_id = absint( $_GET[ 'post_id' ] );
    elseif ( isset( $_POST ) && count( $_POST ) ) // Like for async-upload where $_GET['post_id'] isn't set
	$calling_post_id = $post->post_parent;
    if ( 'image' == $type && $calling_post_id && current_theme_supports( 'post-thumbnails', get_post_type( $calling_post_id ) )
	    && post_type_supports( get_post_type( $calling_post_id ), 'thumbnail' ) && get_post_thumbnail_id( $calling_post_id ) != $attachment_id ) {
	$ajax_nonce = wp_create_nonce( "set_post_thumbnail-$calling_post_id" );
	$thumbnail = "<input type=\"submit\" class=\"button\" id=\"igp_save_attachment-deavticated\" value=\"" . __( 'Save all changes' ) . "\" name=\"post-$post_id&attachment-$attachment_id\" />";
    }

    if ( ( $send || $thumbnail || $delete ) && !isset( $form_fields[ 'buttons' ] ) )
	$form_fields[ 'buttons' ] = array( 'tr' => "\t\t<tr class='submit'><td></td><td class='savesend'>$send $thumbnail</td></tr>\n" );

    $hidden_fields = array( );


    // Walk form_fields
    foreach ( $form_fields as $id => $field ) {
	if ( $id[ 0 ] == '_' )
	    continue;

	if ( !empty( $field[ 'tr' ] ) ) {
	    $item .= $field[ 'tr' ];
	    continue;
	}

	$field = array_merge( $defaults, $field );
	$name = "attachments[$attachment_id][$id]";

	if ( $field[ 'input' ] == 'hidden' ) {
	    $hidden_fields[ $name ] = $field[ 'value' ];
	    continue;
	}

	$required = $field[ 'required' ] ? '<span class="alignright"><abbr title="required" class="required">*</abbr></span>' : '';
	$aria_required = $field[ 'required' ] ? " aria-required='true' " : '';
	$class = $id;
	$class .= $field[ 'required' ] ? ' form-required' : '';

	$item .= "\t\t<tr class='$class'>\n\t\t\t<th valign='top' scope='row' class='label'><label for='$name'><span class='alignleft'>{$field[ 'label' ]}</span>$required<br class='clear' /></label></th>\n\t\t\t<td class='field'>";
	if ( !empty( $field[ $field[ 'input' ] ] ) )
	    $item .= $field[ $field[ 'input' ] ];
	elseif ( $field[ 'input' ] == 'textarea' ) {
	    if ( user_can_richedit() ) { // textarea_escaped when user_can_richedit() = false
		$field[ 'value' ] = esc_textarea( $field[ 'value' ] );
	    }
	    $item .= "<textarea id='$name' name='$name' $aria_required>" . $field[ 'value' ] . '</textarea>';
	} else {
	    $item .= "<input type='text' class='text' id='$name' name='$name' value='" . esc_attr( $field[ 'value' ] ) . "' $aria_required />";
	}
	if ( !empty( $field[ 'helps' ] ) )
	    $item .= "<p class='help'>" . join( "</p>\n<p class='help'>", array_unique( ( array ) $field[ 'helps' ] ) ) . '</p>';
	$item .= "</td>\n\t\t</tr>\n";

	$extra_rows = array( );

	if ( !empty( $field[ 'errors' ] ) )
	    foreach ( array_unique( ( array ) $field[ 'errors' ] ) as $error )
		$extra_rows[ 'error' ][ ] = $error;

	if ( !empty( $field[ 'extra_rows' ] ) )
	    foreach ( $field[ 'extra_rows' ] as $class => $rows )
		foreach ( ( array ) $rows as $html )
		    $extra_rows[ $class ][ ] = $html;

	foreach ( $extra_rows as $class => $rows )
	    foreach ( $rows as $html )
		$item .= "\t\t<tr><td></td><td class='$class'>$html</td></tr>\n";
    }

    if ( !empty( $form_fields[ '_final' ] ) )
	$item .= "\t\t<tr class='final'><td colspan='2'>{$form_fields[ '_final' ]}</td></tr>\n";
    $item .= "\t</tbody>\n";
    $item .= "\t</table></div>\n";

    foreach ( $hidden_fields as $name => $value )
	$item .= "\t<input type='hidden' name='$name' id='$name' value='" . esc_attr( $value ) . "' />\n";

    if ( $post->post_parent < 1 && isset( $_REQUEST[ 'post_id' ] ) ) {
	$parent = intval( $_REQUEST[ 'post_id' ] );
	$parent_name = "attachments[$attachment_id][post_parent]";
	$item .= "\t<input type='hidden' name='$parent_name' id='$parent_name' value='$parent' />\n";
    }

    $item.= "</form>";

    echo $item;
}

/**
 * Update the attachment
 * 
 * @return type 
 */
function igp_update_attachment() {

    check_admin_referer( 'media-form' );

    $errors = null;

    if ( isset( $_POST[ 'send' ] ) ) {
	$keys = array_keys( $_POST[ 'send' ] );
	$send_id = intval( array_shift( $keys ) );
    }

    if ( !empty( $_POST[ 'attachments' ] ) )
	foreach ( $_POST[ 'attachments' ] as $attachment_id => $attachment ) {
	    $post = $_post = get_post( $attachment_id, ARRAY_A );
	    $post_type_object = get_post_type_object( $post[ 'post_type' ] );

	    if ( !current_user_can( $post_type_object->cap->edit_post, $attachment_id ) )
		continue;

	    if ( isset( $attachment[ 'post_content' ] ) )
		$post[ 'post_content' ] = $attachment[ 'post_content' ];
	    if ( isset( $attachment[ 'post_title' ] ) )
		$post[ 'post_title' ] = $attachment[ 'post_title' ];
	    if ( isset( $attachment[ 'post_excerpt' ] ) )
		$post[ 'post_excerpt' ] = $attachment[ 'post_excerpt' ];
	    if ( isset( $attachment[ 'menu_order' ] ) )
		$post[ 'menu_order' ] = $attachment[ 'menu_order' ];
	    if ( isset( $attachment[ 'url' ] ) )
		$post[ 'url' ] = $attachment[ 'url' ];

	    if ( isset( $send_id ) && $attachment_id == $send_id ) {
		if ( isset( $attachment[ 'post_parent' ] ) )
		    $post[ 'post_parent' ] = $attachment[ 'post_parent' ];
	    }

	    $post = apply_filters( 'attachment_fields_to_save', $post, $attachment );

	    if ( isset( $attachment[ 'image_alt' ] ) ) {
		$image_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		if ( $image_alt != stripslashes( $attachment[ 'image_alt' ] ) ) {
		    $image_alt = wp_strip_all_tags( stripslashes( $attachment[ 'image_alt' ] ), true );
		    // update_meta expects slashed
		    update_post_meta( $attachment_id, '_wp_attachment_image_alt', addslashes( $image_alt ) );
		}
	    }

	    if ( isset( $post[ 'errors' ] ) ) {
		$errors[ $attachment_id ] = $post[ 'errors' ];
		unset( $post[ 'errors' ] );
	    }

	    if ( $post != $_post )
		wp_update_post( $post );

	    foreach ( get_attachment_taxonomies( $post ) as $t ) {
		if ( isset( $attachment[ $t ] ) )
		    wp_set_object_terms( $attachment_id, array_map( 'trim', preg_split( '/,+/', $attachment[ $t ] ) ), $t, false );
	    }
	}

    if ( isset( $send_id ) ) {
	$attachment = stripslashes_deep( $_POST[ 'attachments' ][ $send_id ] );

	$html = $attachment[ 'post_title' ];
	if ( !empty( $attachment[ 'url' ] ) ) {
	    $rel = '';
	    if ( strpos( $attachment[ 'url' ], 'attachment_id' ) || get_attachment_link( $send_id ) == $attachment[ 'url' ] )
		$rel = " rel='attachment wp-att-" . esc_attr( $send_id ) . "'";
	    $html = "<a href='{$attachment[ 'url' ]}'$rel>$html</a>";
	}

	$html = apply_filters( 'media_send_to_editor', $html, $send_id, $attachment );
	return media_send_to_editor( $html );
    }

    return $errors;
}
?>
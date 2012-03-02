<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">

<meta charset="utf-8" />
<title><?php wp_title( '&laquo;', true, 'right' ); bloginfo( 'name' ); ?></title>
<?php if ( ( ! is_paged() ) && ( is_single() || is_page() || is_home() ) ) { echo '<meta name="robots" content="index, follow" />' . "\n"; } else { echo '<meta name="robots" content="noindex, follow, noodp, noydir" />' . "\n"; } ?>
<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo('charset'); ?>" />
<meta name="description" content="<?php bloginfo( 'description' ); ?>" />
<meta name="language" content="<?php echo get_bloginfo( 'language' ); ?>" />
<meta name="content-language" content="<?php echo get_bloginfo( 'language' ); ?>" />
<meta name="siteinfo" content="robots.txt" />
<meta name="publisher" content="<?php bloginfo( 'name' ); ?>" />

<link rel="Shortcut Icon" href="<?php bloginfo( 'template_directory' ); ?>/images/favicon.ico" />
<link rel="alternate" type="application/rss+xml" title="RSS 2.0 - <?php bloginfo( 'name' ); ?>" href="<?php bloginfo( 'rss2_url' ); ?>" />
<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_url' ); ?>" type="text/css" media="screen" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

<?php wp_head(); ?>
</head>
<body>
<div class="container4">
	<div class="container44">
    	<h3>Unser Netzwerk:</h3>
        <div class="singlebox">
           	<a href="http://www.inpsyde.com/" target="_blank"><img src="<?php bloginfo( 'template_directory' ); ?>/images/logoinpsyde.png" alt="inpsyde" /></a>
        </div>
        <div class="singlebox">
           	<a href="http://www.edupress.de/" target="_blank"><img src="<?php bloginfo( 'template_directory' ); ?>/images/logoedupress.png" alt="edupress" /></a>
        </div>
        <div class="singlebox">
           	<a href="http://www.inpsyde.com/" target="_blank"><img src="<?php bloginfo( 'template_directory' ); ?>/images/logoinpsyde.png" alt="inpsyde" /></a>
        </div>
    </div>
</div>
<div class="container0">
	<div class="container00">
    	<div class="logo" onclick="location.href='<?php echo home_url(); ?>';" onkeypress="location.href='<?php echo home_url(); ?>';" > 	    	
        </div>
   		<div class="mainmenu">
        	<?php wp_nav_menu( array( 'theme_location' => 'header' ) ); ?>
        </div>
	</div>
</div>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php bloginfo( 'name' ); ?> <?php wp_title(); ?></title>
  		<link rel="shortcut icon" href="<?php bloginfo( 'template_url' ); ?>/images/favicon.ico" type="image/x-icon" /> 
  		<link rel="stylesheet" type="text/css" media="screen" href="<?php bloginfo( 'template_url' ); ?>/style.css" />
  		<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
  		<?php wp_head(); ?>
	</head>
	<body>
	
		<div id="wrapper">
			
			<div id="header">
			
				<a href="<?php bloginfo( 'home' ); ?>"><img src="<?php bloginfo( 'template_url' ); ?>/images/orwell.png" class="logo" /></a>

				<?php wp_nav_menu( array( 'theme_location' => 'nav_header' ) ); ?>
				<?php #wp_nav_menu( array( 'theme_location' => 'link_here' ) ); ?>
				
			</div>
			
			<div id="content">
			
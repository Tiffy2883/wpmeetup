<?php
/*
Template Name: Archiv
*/
?>

<?php get_header(); ?>

<div id="content" class="widecolumn">

<?php include (TEMPLATEPATH . '/searchform.php'); ?>

<h2>Archiv nach Monaten:</h2>
  <ul>
    <?php wp_get_archives('type=monthly'); ?>
  </ul>

<h2>Archiv nach Kategorien:</h2>
  <ul>
     <?php wp_list_categories(); ?>
  </ul>

</div>	

<?php get_footer(); ?>

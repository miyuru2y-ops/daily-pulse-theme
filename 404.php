<?php
/** 404 page. */
get_header();
?>
<div class="wrap"><div class="error-404">
  <h1><?php esc_html_e('Page not found', 'daily-pulse'); ?></h1>
  <p><?php esc_html_e('The page you are looking for has moved or no longer exists.', 'daily-pulse'); ?></p>
  <p><a class="more" href="<?php echo esc_url(home_url('/')); ?>">&larr; <?php esc_html_e('Back to the homepage', 'daily-pulse'); ?></a></p>
</div></div>
<?php get_footer(); ?>

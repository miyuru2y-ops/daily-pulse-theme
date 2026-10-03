<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="google-site-verification" content="E7BUJzfq4bAehXHePYSRv_96bwgaqWjpg04xsfZoYtY" />
<script>try{if(localStorage.getItem('dp_theme')==='dark')document.documentElement.setAttribute('data-theme','dark')}catch(e){}</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="masthead">
  <div class="wrap mast-in">
    <div class="mast-left"><span class="live-dot"><?php esc_html_e('Live', 'daily-pulse'); ?></span><span><?php echo esc_html(wp_date('l, F d, Y')); ?></span></div>
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Daily Pulse home', 'daily-pulse'); ?>">
      <span class="brand-blocks"><span>D</span><span>P</span></span><span class="brand-name"><?php esc_html_e('DAILY PULSE', 'daily-pulse'); ?></span>
    </a>
    <div class="mast-right"><span><?php esc_html_e('News, rewritten clearly', 'daily-pulse'); ?></span></div>
    <button type="button" id="dp-theme-toggle" class="theme-toggle" aria-label="<?php esc_attr_e('Toggle dark mode', 'daily-pulse'); ?>">
      <svg class="icon-moon" viewBox="0 0 24 24" width="17" height="17" aria-hidden="true"><path fill="currentColor" d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
      <svg class="icon-sun" viewBox="0 0 24 24" width="17" height="17" aria-hidden="true"><path fill="currentColor" d="M12 17a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0-15a1 1 0 0 1 1 1v2a1 1 0 0 1-2 0V3a1 1 0 0 1 1-1zm0 16a1 1 0 0 1 1 1v2a1 1 0 0 1-2 0v-2a1 1 0 0 1 1-1zM3 12a1 1 0 0 1 1-1h2a1 1 0 0 1 0 2H4a1 1 0 0 1-1-1zm16 0a1 1 0 0 1 1-1h2a1 1 0 0 1 0 2h-2a1 1 0 0 1-1-1zM4.9 4.9a1 1 0 0 1 1.4 0l1.4 1.4a1 1 0 0 1-1.4 1.4L4.9 6.3a1 1 0 0 1 0-1.4zm12.8 12.8a1 1 0 0 1 1.4 0l1.4 1.4a1 1 0 0 1-1.4 1.4l-1.4-1.4a1 1 0 0 1 0-1.4zM4.9 19.1a1 1 0 0 1 0-1.4l1.4-1.4a1 1 0 0 1 1.4 1.4l-1.4 1.4a1 1 0 0 1-1.4 0zm12.8-12.8a1 1 0 0 1 0-1.4l1.4-1.4a1 1 0 0 1 1.4 1.4l-1.4 1.4a1 1 0 0 1-1.4 0z"/></svg>
    </button>
  </div>
  <nav class="secnav" aria-label="<?php esc_attr_e('Sections', 'daily-pulse'); ?>"><div class="wrap secnav-in"><?php dp_section_nav(); ?></div></nav>
</header>
<?php
$tick = new WP_Query(array(
    'posts_per_page'      => 5,
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
));
if ($tick->have_posts()) :
    $tick_items = array();
    while ($tick->have_posts()) { $tick->the_post(); $tick_items[] = array(get_permalink(), get_the_title()); }
    wp_reset_postdata();
?>
<div class="ticker" aria-label="<?php esc_attr_e('Latest headlines', 'daily-pulse'); ?>">
  <span class="ticker-label"><?php esc_html_e('Latest', 'daily-pulse'); ?></span>
  <div class="ticker-view"><div class="ticker-track">
    <?php for ($r = 0; $r < 2; $r++) : foreach ($tick_items as $t) : ?>
      <a href="<?php echo esc_url($t[0]); ?>"><?php echo esc_html($t[1]); ?></a><span class="ticker-dot" aria-hidden="true">&bull;</span>
    <?php endforeach; endfor; ?>
  </div></div>
</div>
<?php endif; ?>
<main>

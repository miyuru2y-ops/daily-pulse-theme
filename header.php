<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
  </div>
  <nav class="secnav" aria-label="<?php esc_attr_e('Sections', 'daily-pulse'); ?>"><div class="wrap secnav-in"><?php dp_section_nav(); ?></div></nav>
</header>
<main>

<?php
/** Section (category) archive: lead story + grid. */
get_header();
?>
<div class="wrap">
  <div class="cat-hero">
    <h1><?php single_cat_title(); ?></h1>
    <?php $desc = category_description(); if ($desc) : ?><p><?php echo esc_html(wp_strip_all_tags($desc)); ?></p><?php endif; ?>
  </div>

  <?php if (have_posts()) : the_post(); ?>
  <div class="page-lead">
    <div>
      <span class="kicker"><?php single_cat_title(); ?></span>
      <a href="<?php the_permalink(); ?>"><h2><?php the_title(); ?></h2></a>
      <p><?php echo esc_html(get_the_excerpt()); ?></p>
      <div class="meta"><?php echo dp_pub_date(); ?></div>
    </div>
    <a href="<?php the_permalink(); ?>"><?php echo dp_card_img(null, ''); ?></a>
  </div>
  <div class="cat-grid">
    <?php while (have_posts()) : the_post(); echo dp_card(); endwhile; ?>
  </div>
  <?php the_posts_pagination(array('mid_size' => 2)); ?>
  <?php else : ?>
  <p style="padding:30px 0"><?php esc_html_e('No stories in this section yet.', 'daily-pulse'); ?></p>
  <?php endif; ?>
</div>
<?php get_footer(); ?>

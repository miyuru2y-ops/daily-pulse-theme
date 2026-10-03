<?php
/** Ultimate fallback template. */
get_header();
?>
<div class="wrap" style="padding-top:10px">
  <?php if (have_posts()) : ?>
  <div class="cat-grid">
    <?php while (have_posts()) : the_post(); echo dp_card(); endwhile; ?>
  </div>
  <?php the_posts_pagination(array('mid_size' => 2)); ?>
  <?php else : ?>
  <p style="padding:30px 0"><?php esc_html_e('Nothing found.', 'daily-pulse'); ?></p>
  <?php endif; ?>
</div>
<?php get_footer(); ?>

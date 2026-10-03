<?php
/** Static page template: full content, never a truncated excerpt. */
get_header();
while (have_posts()) : the_post();
?>
<article class="story">
  <h1><?php the_title(); ?></h1>
  <div class="story-body">
    <?php the_content(); ?>
  </div>
  <p class="meta"><?php printf(esc_html__('Last updated: %s', 'daily-pulse'), esc_html(get_the_modified_date())); ?></p>
</article>
<?php
endwhile;
get_footer();

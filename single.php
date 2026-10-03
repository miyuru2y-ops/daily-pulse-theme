<?php
/** Single article page. */
get_header();

while (have_posts()) : the_post();
    $cat     = dp_primary_cat();
    $img     = dp_image_url();
    $imgcap   = get_post_meta(get_the_ID(), 'dp_img_credit', true);
?>
<div class="wrap"><article class="story">
  <?php if ($cat) : ?><span class="kicker"><?php echo esc_html($cat->name); ?></span><?php endif; ?>
  <h1><?php the_title(); ?></h1>
  <div class="meta"><?php printf(esc_html__('By %s', 'daily-pulse'), esc_html(get_the_author())); ?> <span style="color:#ccc">&nbsp;|&nbsp;</span> <?php echo dp_pub_date(); ?> <span style="color:#ccc">&nbsp;|&nbsp;</span> <?php echo $cat ? esc_html($cat->name) : ''; ?> &nbsp;|&nbsp; <?php echo esc_html(dp_reading_time()); ?></div>
  <?php if ($img) : ?>
    <img class="story-img" src="<?php echo $img; ?>" alt="<?php the_title_attribute(); ?>" onerror="this.style.display='none'">
    <?php if ($imgcap) : ?><p class="img-cap"><?php echo esc_html($imgcap); ?></p><?php endif; ?>
  <?php endif; ?>
  <div class="story-body"><?php the_content(); ?></div>
</article></div>

<?php if ($cat) :
    $rel = new WP_Query(array(
        'cat' => $cat->term_id, 'posts_per_page' => 3,
        'post__not_in' => array(get_the_ID()),
        'ignore_sticky_posts' => true, 'no_found_rows' => true,
    ));
    if ($rel->have_posts()) : ?>
<div class="rel-wrap">
  <h2 class="sec-title"><span class="bar"></span><?php printf(esc_html__('More on %s', 'daily-pulse'), esc_html($cat->name)); ?></h2>
  <div class="grid3">
    <?php while ($rel->have_posts()) : $rel->the_post(); echo dp_card(); endwhile; wp_reset_postdata(); ?>
  </div>
</div>
    <?php endif; ?>
<?php endif; ?>

<?php endwhile; ?>
<?php get_footer(); ?>

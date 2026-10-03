<?php
/**
 * Homepage: lead story, top stories, secondary strip,
 * per-section grids and the most-read rail.
 */
get_header();

$lead  = new WP_Query(array('posts_per_page' => 1, 'ignore_sticky_posts' => true, 'no_found_rows' => true));
$top   = new WP_Query(array('posts_per_page' => 4, 'offset' => 1, 'ignore_sticky_posts' => true, 'no_found_rows' => true));
$strip = new WP_Query(array('posts_per_page' => 3, 'offset' => 5, 'ignore_sticky_posts' => true, 'no_found_rows' => true));
?>

<?php if ($lead->have_posts()) : $lead->the_post(); $cat = dp_primary_cat(); ?>
<section class="hero"><div class="wrap hero-grid">
  <div class="lead-text">
    <?php if ($cat) : ?><span class="kicker"><?php echo esc_html($cat->name); ?></span><?php endif; ?>
    <a href="<?php the_permalink(); ?>"><h1><?php the_title(); ?></h1></a>
    <p class="summary"><?php echo esc_html(get_the_excerpt()); ?></p>
    <div class="meta"><?php echo esc_html(dp_time_ago()); ?> <span style="color:#ccc">&nbsp;|&nbsp;</span> <?php echo $cat ? esc_html($cat->name) : ''; ?></div>
  </div>
  <a class="lead-img" href="<?php the_permalink(); ?>"><?php echo dp_card_img(null, ''); ?></a>
  <?php if ($top->have_posts()) : ?>
  <div class="top-stories">
    <h2><?php esc_html_e('Top stories', 'daily-pulse'); ?></h2>
    <ol>
      <?php $i = 0; while ($top->have_posts()) : $top->the_post(); $i++; ?>
      <li><a href="<?php the_permalink(); ?>"><span class="n"><?php echo (int) $i; ?></span><?php the_title(); ?></a></li>
      <?php endwhile; ?>
    </ol>
  </div>
  <?php endif; ?>
</div></section>
<?php wp_reset_postdata(); endif; ?>

<?php if ($strip->have_posts()) : ?>
<div class="wrap"><div class="strip">
  <?php while ($strip->have_posts()) : $strip->the_post(); echo dp_card(); endwhile; wp_reset_postdata(); ?>
</div></div>
<?php endif; ?>

<div class="wrap cols"><div>
  <?php foreach (DP_SECTIONS as $slug) :
    $cat = get_category_by_slug($slug);
    if (!$cat) continue;
    $q = new WP_Query(array('cat' => $cat->term_id, 'posts_per_page' => 4, 'ignore_sticky_posts' => true, 'no_found_rows' => true));
    if (!$q->have_posts()) continue;
  ?>
  <section class="sec">
    <div class="sec-head">
      <h2 class="sec-title"><span class="bar"></span><?php echo esc_html($cat->name); ?></h2>
      <a class="more" href="<?php echo esc_url(get_category_link($cat)); ?>"><?php printf(esc_html__('More %s', 'daily-pulse'), esc_html($cat->name)); ?></a>
    </div>
    <div class="grid4">
      <?php while ($q->have_posts()) : $q->the_post(); echo dp_card(); endwhile; wp_reset_postdata(); ?>
    </div>
  </section>
  <?php endforeach; ?>
</div>
<aside class="rail"><div class="rail-box">
  <h2><?php esc_html_e('Most read', 'daily-pulse'); ?></h2>
  <ol>
    <?php $mr = dp_most_read(5); while ($mr->have_posts()) : $mr->the_post(); ?>
    <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
    <?php endwhile; wp_reset_postdata(); ?>
  </ol>
</div></aside></div>

<?php get_footer(); ?>

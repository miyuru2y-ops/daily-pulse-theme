<?php
/** Single article page. */
get_header();

while (have_posts()) : the_post();
    $cat     = dp_primary_cat();
    $img     = dp_image_url();
    $imgcap  = get_post_meta(get_the_ID(), 'dp_img_credit', true);
    $excerpt = get_the_excerpt();
    $pid     = get_the_ID();
    $plink   = get_permalink();
    $ptitle  = get_the_title();
?>
<div class="wrap"><article class="story">
  <?php if ($cat) : ?><span class="kicker"><?php echo esc_html($cat->name); ?></span><?php endif; ?>
  <h1><?php the_title(); ?></h1>
  <?php if ($excerpt) : ?><p class="dek"><?php echo esc_html($excerpt); ?></p><?php endif; ?>
  <div class="toolbar" role="toolbar" aria-label="<?php esc_attr_e('Article tools', 'daily-pulse'); ?>">
    <button type="button" class="tool-btn" id="dp-listen">
      <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M12 3a9 9 0 0 0-9 9v7a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-6a1 1 0 0 0-1-1H4a7 7 0 0 1 14 0h-3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-7a9 9 0 0 0-9-9z"/></svg>
      <span><?php printf(esc_html__('Listen · %s', 'daily-pulse'), esc_html(dp_reading_time())); ?></span>
    </button>
    <span class="tool-sep" aria-hidden="true"></span>
    <a class="tool-btn" href="https://twitter.com/intent/tweet?url=<?php echo rawurlencode($plink); ?>&text=<?php echo rawurlencode($ptitle); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Share on X', 'daily-pulse'); ?>">
      <svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M18.9 2H22l-6.8 7.8L23.3 22h-6.3l-4.9-6.4L6.4 22H3.3l7.3-8.3L1 2h6.5l4.4 5.9L18.9 2zm-1.1 18h1.7L7.6 3.9H5.8L17.8 20z"/></svg>
      <span><?php esc_html_e('Share', 'daily-pulse'); ?></span>
    </a>
    <a class="tool-btn" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode($plink); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Share on Facebook', 'daily-pulse'); ?>">
      <svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M13.5 22v-8h2.7l.4-3.2h-3.1V8.7c0-.9.3-1.6 1.7-1.6h1.5V4.2c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.7H7.8V14h2.7v8h3z"/></svg>
      <span>Facebook</span>
    </a>
    <a class="tool-btn" href="https://wa.me/?text=<?php echo rawurlencode($ptitle . ' ' . $plink); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Share on WhatsApp', 'daily-pulse'); ?>">
      <svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.2 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5s.8 1.9.8 2c.1.1.1.3 0 .5-.3.6-.6.8-.4 1.1.7 1.2 1.6 2 2.8 2.6.3.2.5.1.7-.1l.8-.9c.2-.3.4-.2.7-.1l1.9.9c.3.1.5.2.5.4 0 .1 0 .4-.2 1z"/></svg>
      <span>WhatsApp</span>
    </a>
    <button type="button" class="tool-btn" id="dp-copy">
      <svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M10.6 13.4a1 1 0 0 0 1.4 1.4l3.2-3.2a4 4 0 0 0-5.7-5.7L8.1 7.3a4 4 0 0 0 0 5.7l1.5-1.5a1 1 0 1 0-1.4-1.4l-1.5 1.5a6 6 0 0 0 0 8.5l1.4 1.4a6 6 0 0 0 8.5 0l1.4-1.4a6 6 0 0 0 0-8.5L10.6 13.4zM13.4 10.6a1 1 0 0 0-1.4-1.4l-3.2 3.2a4 4 0 0 0 5.7 5.7l1.4-1.4a4 4 0 0 0 0-5.7l-1.5 1.5a1 1 0 1 0 1.4 1.4l1.5-1.5a6 6 0 0 0 0-8.5l-1.4-1.4a6 6 0 0 0-8.5 0l-1.4 1.4a6 6 0 0 0 0 8.5l3.2-3.2z" transform="scale(0.9) translate(1.3,1.3)"/></svg>
      <span><?php esc_html_e('Copy link', 'daily-pulse'); ?></span>
    </button>
    <button type="button" class="tool-btn" id="dp-save" data-id="<?php echo esc_attr($pid); ?>">
      <svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="currentColor" d="M6 2h12a1 1 0 0 1 1 1v19l-7-4-7 4V3a1 1 0 0 1 1-1z"/></svg>
      <span><?php esc_html_e('Save', 'daily-pulse'); ?></span>
    </button>
  </div>
  <?php if ($img) : ?>
    <img class="story-img" src="<?php echo $img; ?>" alt="<?php the_title_attribute(); ?>" onerror="this.style.display='none'">
    <?php if ($imgcap) : ?><p class="img-cap"><?php echo esc_html($imgcap); ?></p><?php endif; ?>
  <?php endif; ?>
  <div class="byline-block">
    <span class="by-avatar" aria-hidden="true"><span>D</span><span>P</span></span>
    <span class="by-text"><strong><?php printf(esc_html__('By %s', 'daily-pulse'), esc_html(get_the_author())); ?></strong><br><?php echo dp_pub_date(); ?></span>
  </div>
  <div class="story-body" id="dp-body"><?php the_content(); ?></div>
</article></div>

<?php if ($cat) :
    $rel = new WP_Query(array(
        'cat' => $cat->term_id, 'posts_per_page' => 6,
        'post__not_in' => array(get_the_ID()),
        'ignore_sticky_posts' => true, 'no_found_rows' => true,
    ));
    if ($rel->have_posts()) : ?>
<div class="rel-wrap">
  <h2 class="sec-title"><span class="bar"></span><?php esc_html_e('More from Daily Pulse', 'daily-pulse'); ?></h2>
  <div class="grid3">
    <?php while ($rel->have_posts()) : $rel->the_post(); echo dp_card(); endwhile; wp_reset_postdata(); ?>
  </div>
  <p class="rel-more"><a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"><?php printf(esc_html__('View more %s stories', 'daily-pulse'), esc_html($cat->name)); ?> &rarr;</a></p>
</div>
    <?php endif; ?>
<?php endif; ?>

<?php endwhile; ?>
<?php get_footer(); ?>

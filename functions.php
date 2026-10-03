<?php
/**
 * Daily Pulse theme — functions and helpers.
 *
 * Lean by design: no frameworks, no jQuery, no page builders.
 * Content meta used by the theme (also written by the REST API importer):
 *   dp_image        image URL for cards / story hero
 *   dp_source_name  original publisher name
 *   dp_source_url   link to the original article
 *   dp_slug         importer slug (dedupe key)
 *   dp_views        most-read counter
 */

if (!defined('ABSPATH')) exit;

define('DP_VERSION', '1.0.2');
define('DP_SECTIONS', array('world', 'technology', 'business', 'entertainment', 'sports', 'health', 'science'));

/* ---------- theme setup ---------- */

add_action('after_setup_theme', 'dp_setup');
function dp_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
}

add_action('wp_enqueue_scripts', 'dp_assets');
function dp_assets() {
    // The whole design is one stylesheet. No JavaScript shipped at all.
    wp_enqueue_style('daily-pulse', get_stylesheet_uri(), array(), DP_VERSION);
}

/* ---------- meta registration (lets the REST API importer write these) ---------- */

add_action('init', 'dp_register_meta');
function dp_register_meta() {
    $str = array(
        'type' => 'string', 'single' => true, 'show_in_rest' => true,
        'auth_callback' => 'dp_meta_auth',
    );
    foreach (array('dp_image', 'dp_source_name', 'dp_source_url', 'dp_slug') as $key) {
        register_meta('post', $key, $str);
    }
    register_meta('post', 'dp_views', array(
        'type' => 'integer', 'single' => true, 'show_in_rest' => false,
        'auth_callback' => 'dp_meta_auth',
    ));
}
function dp_meta_auth() {
    return current_user_can('edit_posts');
}

/* ---------- helpers ---------- */

/** Relative time ("3 hours ago"); future dates fall back to an absolute date. */
function dp_time_ago($post_id = null) {
    $ts  = get_post_time('U', true, $post_id);
    $now = time();
    if (!$ts) return '';
    if ($ts > $now) {
        return wp_date(get_option('date_format'), $ts);
    }
    $mins = (int) floor(($now - $ts) / 60);
    if ($mins < 1)  return __('Just now', 'daily-pulse');
    if ($mins < 60) return sprintf(_n('%d minute ago', '%d minutes ago', $mins, 'daily-pulse'), $mins);
    $hrs = (int) floor($mins / 60);
    if ($hrs < 24)  return sprintf(_n('%d hour ago', '%d hours ago', $hrs, 'daily-pulse'), $hrs);
    $days = (int) floor($hrs / 24);
    if ($days < 7)  return sprintf(_n('%d day ago', '%d days ago', $days, 'daily-pulse'), $days);
    return wp_date(get_option('date_format'), $ts);
}

/** Image URL for a post: dp_image meta -> featured image -> none. */
function dp_image_url($post_id = null) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $url = get_post_meta($post_id, 'dp_image', true);
    if ($url) return esc_url($url);
    if (has_post_thumbnail($post_id)) {
        $src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'large');
        if (!empty($src[0])) return esc_url($src[0]);
    }
    return '';
}

/** <img> tag for cards / heroes. Empty string when there is no image. */
function dp_card_img($post_id = null, $class = 'card-img', $extra = '') {
    $post_id = $post_id ? $post_id : get_the_ID();
    $url = dp_image_url($post_id);
    if (!$url) return '';
    $extra = $extra ? ' ' . trim($extra) : '';
    return '<img class="' . esc_attr($class) . '" src="' . $url . '" alt="' .
        esc_attr(get_the_title($post_id)) . '" loading="lazy" decoding="async"' . $extra . ' onerror="this.style.display=\'none\'">';
}

function dp_reading_time($post_id = null) {
    $words = str_word_count(wp_strip_all_tags(get_post_field('post_content', $post_id ? $post_id : get_the_ID())));
    $mins  = max(1, (int) ceil($words / 200));
    return sprintf(_n('%d min read', '%d min read', $mins, 'daily-pulse'), $mins);
}

function dp_primary_cat($post_id = null) {
    $cats = get_the_category($post_id ? $post_id : get_the_ID());
    return $cats ? $cats[0] : null;
}

/** Section navigation: Home + the 7 fixed sections. */
function dp_section_nav($include_home = true) {
    if ($include_home) {
        $on = is_front_page() ? ' class="on"' : '';
        echo '<a href="' . esc_url(home_url('/')) . '"' . $on . '>' . esc_html__('Home', 'daily-pulse') . '</a>';
    }
    foreach (DP_SECTIONS as $slug) {
        $cat = get_category_by_slug($slug);
        if (!$cat) continue;
        $on = is_category($cat->term_id) ? ' class="on"' : '';
        echo '<a href="' . esc_url(get_category_link($cat)) . '"' . $on . '>' . esc_html($cat->name) . '</a>';
    }
}

/** One story card (used on homepage, sections, related). Returns HTML. */
function dp_card($post_id = null) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $cat = dp_primary_cat($post_id);
    ob_start();
    ?>
    <a class="card" href="<?php echo esc_url(get_permalink($post_id)); ?>">
      <?php echo dp_card_img($post_id); ?>
      <h3><?php echo esc_html(get_the_title($post_id)); ?></h3>
      <p><?php echo esc_html(get_the_excerpt($post_id)); ?></p>
      <div class="meta"><?php echo esc_html(dp_time_ago($post_id)); ?> <span style="color:#ccc">&nbsp;|&nbsp;</span> <?php echo $cat ? esc_html($cat->name) : ''; ?></div>
    </a>
    <?php
    return ob_get_clean();
}

/* ---------- most-read view counter ---------- */

/** Preload the homepage lead image so the LCP request starts ASAP. */
add_action('wp_head', 'dp_preload_lcp', 1);
function dp_preload_lcp() {
    if (!is_front_page()) return;
    $q = new WP_Query(array(
        'posts_per_page'      => 1,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ));
    if ($q->have_posts()) {
        $q->the_post();
        $url = dp_image_url();
        wp_reset_postdata();
        if ($url) {
            echo '<link rel="preload" as="image" href="' . esc_url($url) . '" fetchpriority="high">' . "\n";
        }
    }
}

add_action('template_redirect', 'dp_track_view');
function dp_track_view() {
    if (!is_single() || is_user_logged_in()) return;
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    if ($ua && preg_match('/bot|crawl|spider|slurp|mediapartners|ahrefs|semrush/i', $ua)) return;
    $id    = get_queried_object_id();
    $views = (int) get_post_meta($id, 'dp_views', true);
    update_post_meta($id, 'dp_views', $views + 1);
}

function dp_most_read($n = 5) {
    $q = new WP_Query(array(
        'posts_per_page'      => $n,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'meta_key'            => 'dp_views',
        'orderby'             => 'meta_value_num',
        'order'               => 'DESC',
    ));
    if (!$q->have_posts()) {
        $q = new WP_Query(array(
            'posts_per_page'      => $n,
            'post_status'         => 'publish',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ));
    }
    return $q;
}

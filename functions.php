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

define('DP_VERSION', '1.0.7');
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
    wp_enqueue_style('daily-pulse', get_stylesheet_uri(), array(), DP_VERSION);
    // Article tools (Listen / Copy link / Save) — only on single posts.
    if (is_singular('post')) {
        wp_enqueue_script('daily-pulse-tools', get_template_directory_uri() . '/dp.js', array(), DP_VERSION, true);
    }
}

/* Hide the WordPress version from page source (SEO audit fix). */
remove_action('wp_head', 'wp_generator');

/** The author archive is noindex,follow, so keep it out of the sitemap too. */
add_filter('wp_sitemaps_add_provider', function ($provider, $name) {
    if ($name === 'users') return false;
    return $provider;
}, 10, 2);

/* Shorten very long article titles: drop the " – Daily Pulse" suffix when
 * the full title would exceed ~70 characters. */
add_filter('document_title_parts', 'dp_shorten_title');
function dp_shorten_title($parts) {
    if (!is_singular() || empty($parts['title']) || empty($parts['site'])) return $parts;
    if (mb_strlen($parts['title'] . ' – ' . $parts['site']) > 70) {
        unset($parts['site'], $parts['tagline']);
    }
    return $parts;
}

/* ---------- meta registration (lets the REST API importer write these) ---------- */

add_action('init', 'dp_register_meta');
function dp_register_meta() {
    $str = array(
        'type' => 'string', 'single' => true, 'show_in_rest' => true,
        'auth_callback' => 'dp_meta_auth',
    );
    foreach (array('dp_image', 'dp_source_name', 'dp_source_url', 'dp_slug', 'dp_img_credit') as $key) {
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
    // Featured image (local media library) wins; dp_image hotlink is the fallback.
    if (has_post_thumbnail($post_id)) {
        $src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'large');
        if (!empty($src[0])) return esc_url($src[0]);
    }
    $url = get_post_meta($post_id, 'dp_image', true);
    if ($url) return esc_url($url);
    return '';
}

/** <img> tag for cards / heroes. Empty string when there is no image.
 *  $eager=true omits loading="lazy" (use for the above-the-fold hero). */
function dp_card_img($post_id = null, $class = 'card-img', $extra = '', $eager = false) {
    $post_id = $post_id ? $post_id : get_the_ID();
    $url = dp_image_url($post_id);
    if (!$url) return '';
    $extra   = $extra ? ' ' . trim($extra) : '';
    $loading = $eager ? '' : ' loading="lazy"';
    return '<img class="' . esc_attr($class) . '" src="' . $url . '" alt="' .
        esc_attr(get_the_title($post_id)) . '"' . $loading . ' decoding="async"' . $extra . ' onerror="this.style.display=\'none\'">';
}

/** Absolute publish date wrapped in <time> (replaces relative "x hours ago"). */
function dp_pub_date($post_id = null) {
    $post_id = $post_id ? $post_id : get_the_ID();
    return '<time datetime="' . esc_attr(get_the_date('c', $post_id)) . '">' .
        esc_html(get_the_date('', $post_id)) . '</time>';
}

/** Word-safe trim: never cuts mid-word. */
function dp_word_safe_trim($text, $max = 160) {
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if (mb_strlen($text) <= $max) return $text;
    $cut = mb_substr($text, 0, $max - 1);            // room for the ellipsis
    $cut = preg_replace('/\s+\S*$/u', '', $cut);    // drop the partial word
    return rtrim($cut) . '…';
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
      <div class="meta"><?php echo dp_pub_date($post_id); ?> <span style="color:#ccc">&nbsp;|&nbsp;</span> <?php echo $cat ? esc_html($cat->name) : ''; ?></div>
    </a>
    <?php
    return ob_get_clean();
}

/* ---------- most-read view counter ---------- */

/** Preload the homepage lead image so the LCP request starts ASAP. */add_action('wp_head', 'dp_preload_lcp', 1);
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

/** Canonical URL for the current page (term archives use the term link,
 *  never get_permalink() on a term id). */
function dp_canonical_url() {
    if (is_singular()) {
        return get_permalink(get_queried_object_id());
    }
    if (is_category()) {
        return get_category_link(get_queried_object_id());
    }
    if (is_author()) {
        return get_author_posts_url(get_queried_object_id());
    }
    return home_url('/');
}

/** Meta description: hand-written excerpt first, then a word-safe trim. */
function dp_meta_description() {
    $desc = '';
    if (is_singular()) {
        $desc = get_the_excerpt(get_queried_object_id());
        if (!$desc) {
            $desc = wp_trim_words(wp_strip_all_tags(get_post_field('post_content', get_queried_object_id())), 30);
        }
    } elseif (is_category()) {
        $cat  = get_queried_object();
        $desc = ($cat && !empty($cat->description))
            ? $cat->description
            : sprintf(__('The latest %s news, rewritten clearly and updated daily.', 'daily-pulse'), single_cat_title('', false));
    } elseif (is_front_page()) {
        $desc = __('World news, rewritten in clear language and updated daily. Top stories across World, Technology, Business, Entertainment, Sports, Health and Science.', 'daily-pulse');
    }
    $desc = trim(wp_strip_all_tags($desc));
    if (!$desc) return '';
    return dp_word_safe_trim($desc, 160);
}

/** Meta description, canonical, Open Graph and Twitter tags. Skipped when
 *  an SEO plugin is active to avoid duplicate tags. */
add_action('wp_head', 'dp_meta_tags', 1);
function dp_meta_tags() {
    if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) return;
    $canon = dp_canonical_url();
    echo '<link rel="canonical" href="' . esc_url($canon) . '">' . "\n";

    // Thin archives stay crawlable but out of the index.
    $noindex = is_author() || is_date() || is_search();
    if (is_category()) {
        $cat_obj = get_queried_object();
        if ($cat_obj && empty($cat_obj->count)) $noindex = true; // e.g. Health until it has stories
    }
    if ($noindex) {
        echo '<meta name="robots" content="noindex,follow">' . "\n";
    }

    $desc = dp_meta_description();
    if ($desc) {
        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($desc) . '">' . "\n";
    }
    $title = wp_get_document_title();
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canon) . '">' . "\n";
    echo '<meta property="og:site_name" content="Daily Pulse">' . "\n";

    $img = '';
    if (is_singular()) {
        $img = dp_image_url(get_queried_object_id());
    } elseif (is_front_page()) {
        $latest = new WP_Query(array(
            'posts_per_page' => 1, 'post_status' => 'publish',
            'ignore_sticky_posts' => true, 'no_found_rows' => true,
        ));
        if ($latest->have_posts()) {
            $latest->the_post();
            $img = dp_image_url();
            wp_reset_postdata();
        }
    }
    if ($img) {
        echo '<meta property="og:image" content="' . esc_url($img) . '">' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url($img) . '">' . "\n";
    }
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

    if (is_single()) {
        $id = get_queried_object_id();
        echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c', $id)) . '">' . "\n";
        echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c', $id)) . '">' . "\n";
        $cat = dp_primary_cat($id);
        if ($cat) {
            echo '<meta property="article:section" content="' . esc_attr($cat->name) . '">' . "\n";
        }
    }
}

/** JSON-LD structured data: Organization + WebSite everywhere, NewsArticle
 *  and BreadcrumbList on posts/archives. Skipped when an SEO plugin is
 *  active, same as dp_meta_tags(). */
add_action('wp_head', 'dp_json_ld', 2);
function dp_json_ld() {
    if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) return;
    $home = home_url('/');
    $schemas = array(
        array(
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => 'Daily Pulse',
            'url'      => $home,
        ),
        array(
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => 'Daily Pulse',
            'url'      => $home,
        ),
    );
    if (is_single()) {
        $id  = get_queried_object_id();
        $cat = dp_primary_cat($id);
        $img = dp_image_url($id);
        $about_page = get_page_by_path('about');
        $article = array(
            '@context'      => 'https://schema.org',
            '@type'         => 'NewsArticle',
            'headline'      => get_the_title($id),
            'datePublished' => get_the_date('c', $id),
            'dateModified'  => get_the_modified_date('c', $id),
            'author'        => array(
                '@type' => 'Person',
                'name'  => 'Daily Pulse',
                'url'   => $about_page ? get_permalink($about_page) : $home,
            ),
            'publisher'     => array('@type' => 'Organization', 'name' => 'Daily Pulse'),
        );
        if ($img) $article['image'] = $img;
        if ($cat) $article['articleSection'] = $cat->name;
        $schemas[] = $article;

        $crumbs = array(
            array('@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $home),
        );
        $pos = 2;
        if ($cat) {
            $crumbs[] = array('@type' => 'ListItem', 'position' => $pos++, 'name' => $cat->name, 'item' => get_category_link($cat));
        }
        $crumbs[] = array('@type' => 'ListItem', 'position' => $pos, 'name' => get_the_title($id), 'item' => get_permalink($id));
        $schemas[] = array(
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $crumbs,
        );
    } elseif (is_category()) {
        $cat = get_queried_object();
        $schemas[] = array(
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            'itemListElement' => array(
                array('@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $home),
                array('@type' => 'ListItem', 'position' => 2, 'name' => single_cat_title('', false), 'item' => get_category_link($cat)),
            ),
        );
    }
    foreach ($schemas as $schema) {
        echo '<script type="application/ld+json">' .
            wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .
            '</script>' . "\n";
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

/** Google News sitemap: articles published in the last 2 days.
 *  Served at /news-sitemap.xml without needing a rewrite-rule flush. */
add_action('parse_request', 'dp_news_sitemap_serve');
function dp_news_sitemap_serve() {
    if (!isset($_SERVER['REQUEST_URI'])) return;
    $path = strtok($_SERVER['REQUEST_URI'], '?');
    if (substr($path, -strlen('/news-sitemap.xml')) !== '/news-sitemap.xml') return;

    $q = new WP_Query(array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 500,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'date_query'          => array(array('after' => '2 days ago', 'inclusive' => true)),
    ));

    header('Content-Type: application/xml; charset=' . get_bloginfo('charset'));
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
        . ' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";
    while ($q->have_posts()) {
        $q->the_post();
        $id = get_the_ID();
        echo "  <url>\n";
        echo '    <loc>' . esc_url(get_permalink($id)) . "</loc>\n";
        echo "    <news:news>\n";
        echo "      <news:publication>\n";
        echo '        <news:name>Daily Pulse</news:name>' . "\n";
        echo '        <news:language>en</news:language>' . "\n";
        echo "      </news:publication>\n";
        echo '      <news:publication_date>' . esc_html(get_the_date('c', $id)) . "</news:publication_date>\n";
        echo '      <news:title>' . esc_html(get_the_title($id)) . "</news:title>\n";
        echo "    </news:news>\n";
        echo "  </url>\n";
    }
    wp_reset_postdata();
    echo '</urlset>';
    exit;
}

/** Advertise the news sitemap in robots.txt. */
add_filter('robots_txt', 'dp_robots_txt_news_sitemap', 10, 2);
function dp_robots_txt_news_sitemap($output, $public) {
    if ($public) {
        $output .= 'Sitemap: ' . esc_url(home_url('/news-sitemap.xml')) . "\n";
    }
    return $output;
}

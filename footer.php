</main>
<footer>
  <div class="foot-in">
    <div>
      <div class="foot-brand"><span class="brand-blocks"><span>D</span><span>P</span></span> <?php esc_html_e('DAILY PULSE', 'daily-pulse'); ?></div>
      <p class="foot-note"><?php esc_html_e('World news, rewritten in clear language and updated daily. Articles are rewritten summaries of original reporting — every story links back to its source.', 'daily-pulse'); ?></p>
    </div>
    <div class="foot-cats"><?php dp_section_nav(false); ?></div>
  </div>
  <?php
  $trust_pages = array(
      'about'             => __('About', 'daily-pulse'),
      'contact'           => __('Contact', 'daily-pulse'),
      'editorial-policy'  => __('Editorial Policy', 'daily-pulse'),
      'corrections-policy'=> __('Corrections Policy', 'daily-pulse'),
      'privacy-policy'    => __('Privacy Policy', 'daily-pulse'),
  );
  $trust_links = '';
  foreach ($trust_pages as $slug => $label) {
      $page = get_page_by_path($slug);
      if ($page) {
          $trust_links .= '<a href="' . esc_url(get_permalink($page)) . '">' . esc_html($label) . '</a>';
      }
  }
  if ($trust_links) : ?>
  <div class="foot-trust"><?php echo $trust_links; ?></div>
  <?php endif; ?>
  <div class="foot-base">&copy; <?php echo esc_html(wp_date('Y')); ?> <?php esc_html_e('Daily Pulse. All stories link to their original sources.', 'daily-pulse'); ?></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>

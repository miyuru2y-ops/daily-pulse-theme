</main>
<footer>
  <div class="foot-cols">
    <div class="foot-col foot-brand-col">
      <div class="foot-brand"><span class="brand-blocks"><span>D</span><span>P</span></span> <?php esc_html_e('DAILY PULSE', 'daily-pulse'); ?></div>
      <p class="foot-note"><?php esc_html_e('News every four hours, rewritten in clear language.', 'daily-pulse'); ?></p>
    </div>
    <div class="foot-col">
      <h3><?php esc_html_e('Sections', 'daily-pulse'); ?></h3>
      <div class="foot-links"><?php dp_section_nav(false); ?></div>
    </div>
    <div class="foot-col">
      <h3><?php esc_html_e('Company', 'daily-pulse'); ?></h3>
      <div class="foot-links">
      <?php
      $trust_pages = array(
          'about'             => __('About', 'daily-pulse'),
          'contact'           => __('Contact', 'daily-pulse'),
          'editorial-policy'  => __('Editorial Policy', 'daily-pulse'),
          'corrections-policy'=> __('Corrections Policy', 'daily-pulse'),
          'privacy-policy'    => __('Privacy Policy', 'daily-pulse'),
      );
      foreach ($trust_pages as $slug => $label) {
          $page = get_page_by_path($slug);
          if ($page) {
              echo '<a href="' . esc_url(get_permalink($page)) . '">' . esc_html($label) . '</a>';
          }
      }
      ?>
      </div>
    </div>
  </div>
  <div class="foot-base">&copy; <?php echo esc_html(wp_date('Y')); ?> <?php esc_html_e('Daily Pulse. News every four hours.', 'daily-pulse'); ?></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>

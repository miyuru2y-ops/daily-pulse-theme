</main>
<footer>
  <div class="foot-in">
    <div>
      <div class="foot-brand"><span class="brand-blocks"><span>D</span><span>P</span></span> <?php esc_html_e('DAILY PULSE', 'daily-pulse'); ?></div>
      <p class="foot-note"><?php esc_html_e('World news, rewritten in clear language and updated daily. Articles are rewritten summaries of original reporting — every story links back to its source.', 'daily-pulse'); ?></p>
    </div>
    <div class="foot-cats"><?php dp_section_nav(false); ?></div>
  </div>
  <div class="foot-base">&copy; <?php echo esc_html(wp_date('Y')); ?> <?php esc_html_e('Daily Pulse. All stories link to their original sources.', 'daily-pulse'); ?></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>

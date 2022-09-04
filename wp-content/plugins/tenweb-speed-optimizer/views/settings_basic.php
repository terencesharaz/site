<?php
$two_domain_id = get_site_option('tenweb_domain_id');
$two_manage_url = trim(TENWEB_DASHBOARD, '/' ) . '/websites/'. $two_domain_id . '/booster/frontend' . '?from_plugin=' . \TenWebOptimizer\OptimizerUtils::FROM_PLUGIN;
$two_upgrade_link = trim(TENWEB_DASHBOARD, '/' ) . '/upgrade-plan' . '?from_plugin=' . \TenWebOptimizer\OptimizerUtils::FROM_PLUGIN;
$two_wp_plugin_url = 'https://wordpress.org/support/plugin/tenweb-speed-optimizer/';
$two_disconnect_link = get_admin_url() . 'options-general.php?page=two_settings_page&disconnect=1';
$two_current_user = wp_get_current_user();
$username = get_site_option(TENWEB_PREFIX . '_user_info') ? get_site_option(TENWEB_PREFIX . '_user_info')['client_info']['name'] : $two_current_user->display_name;
if ( \TenWebOptimizer\OptimizerUtils::is_paid_user() ) {
  $two_plan_name = __('Paid Plan', 'tenweb-speed-optimizer');
  if (TENWEB_SO_HOSTED_ON_10WEB) {
      $two_plan_description_1 = __('The plugin is now optimizing your website.', 'tenweb-speed-optimizer');
      $two_plan_description_2 = __('', 'tenweb-speed-optimizer');
  } else {
      $two_plan_description_1 = __('Our plugin is now optimizing your website.', 'tenweb-speed-optimizer');
      $two_plan_description_2 = __('Manage optimization settings and assign custom rules from the 10Web dashboard.', 'tenweb-speed-optimizer');
  }

  $two_contact_text = __('Please contact our support via', 'tenweb-speed-optimizer');
  $two_contact_link_text = __('Live Chat', 'tenweb-speed-optimizer');
  $two_contact_link = $two_manage_url . '&open=livechat';
}
else {
  $two_plan_name = __('Free Plan', 'tenweb-speed-optimizer');
  $two_plan_description_1 = __('Our plugin is now optimizing your website.', 'tenweb-speed-optimizer');
  $two_plan_description_2 = __('Manage optimization settings from the 10Web dashboard.', 'tenweb-speed-optimizer');

  $two_contact_text = __('Please create a topic in', 'tenweb-speed-optimizer');
  $two_contact_link_text = __('WordPress.org', 'tenweb-speed-optimizer');
  $two_contact_link = $two_wp_plugin_url;
}
?>
<script>
  jQuery(document).ready(function() {
    jQuery('.two-faq-item').on('click', function() {
      jQuery(this).toggleClass('active');
    });
    jQuery('.two-disconnect-link a').on('click', function() {
      jQuery('.two-disconnect-popup').appendTo('body').addClass('open');
      return false;
    });
    jQuery('.two-button-cancel, .two-close-img').on('click', function() {
      jQuery('.two-disconnect-popup').removeClass('open');
      return false;
    });
  });
</script>
<div class="two-container connected">
  <?php
      include_once ('two_header.php');
   ?>
  <div class="two-body-container">
    <div class="two-body">
      <div class="two-greeting">
        <img src="<?php echo TENWEB_SO_URL; ?>/assets/images/waving_hand.png" alt="Hey" class="two-waving-hand" />
          <?php if ( TENWEB_SO_HOSTED_ON_10WEB ) { ?>
            <?php _e( 'Hey there!', 'tenweb-speed-optimizer' ); ?>
          <?php } else {?>
            <?php echo esc_html( sprintf( __( 'Hey %s! You are on a %s.', 'tenweb-speed-optimizer' ), $username, $two_plan_name ) ); ?>
          <?php } ?>
      </div>
      <div class="two-plugin-status">
          <?php if ( TENWEB_SO_HOSTED_ON_10WEB ) { ?>
              <?php _e('Booster is Active', 'tenweb-speed-optimizer'); ?>
          <?php } else {?>
              <?php _e('10Web Booster is Active', 'tenweb-speed-optimizer'); ?>
          <?php } ?>
      </div>
      <div class="two-plugin-description">
        <?php echo esc_html( $two_plan_description_1 ); ?>
        <br />
        <?php echo esc_html( $two_plan_description_2 ); ?>
      </div>
        <?php if ( !TENWEB_SO_HOSTED_ON_10WEB ) { ?>
            <a href="<?php echo esc_url( $two_manage_url ); ?>" target="_blank" class="two-button two-button-manage"><?php _e('MANAGE', 'tenweb-speed-optimizer'); ?></a>
        <?php } ?>
    </div>
    <?php
    if ( !TENWEB_SO_HOSTED_ON_10WEB ) {
      ?>
      <div class="two-disconnect-link">
        <img src="<?php echo TENWEB_SO_URL; ?>/assets/images/check_solid.svg" alt="Connected" class="two-connected-img" />
        <b><?php _e('Site is connected', 'tenweb-speed-optimizer'); ?></b>
        <a href="<?php echo esc_url( $two_disconnect_link ); ?>"><?php _e('Disconnect from 10Web', 'tenweb-speed-optimizer'); ?></a>
      </div>
      <div class="two-wp-link">
        <b><?php _e('Have a question?', 'tenweb-speed-optimizer'); ?></b>
        <span><?php echo esc_html( $two_contact_text ); ?> <a href="<?php echo esc_url( $two_contact_link ); ?>" target="_blank"><?php echo esc_html( $two_contact_link_text ); ?></a></span>
      </div>
      <div class="two-faq">
        <div class="two-faq-header">
          <?php _e('Frequently Asked Questions', 'tenweb-speed-optimizer'); ?>
        </div>
        <div class="two-faq-item">
          <div class="two-faq-question">
            <?php _e('What is offered in Free Plan?', 'tenweb-speed-optimizer'); ?>
          </div>
          <div class="two-faq-answer">
            <?php _e('With the 10Web Booster free plan, you can optimize homepages of up to 10 websites.', 'tenweb-speed-optimizer'); ?>
          </div>
        </div>
        <div class="two-faq-item">
          <div class="two-faq-question">
            <?php _e('Does the 10Web Booster have different modes?', 'tenweb-speed-optimizer'); ?>
          </div>
          <div class="two-faq-answer">
            <?php _e('Yes, you can choose any of the following four modes. You can also manage the modes for each page individually. Standard Mode: Uses different standard speed optimization techniques. Balanced Mode: All optimization techniques in Standard Mode + Critical CSS Strong Mode: All optimization techniques in Standard Mode + JS Delay. Extreme Mode: All optimization techniques in Balanced Mode + JS Delay. It may cause issues in some cases.', 'tenweb-speed-optimizer'); ?>
          </div>
        </div>
        <div class="two-faq-item">
          <div class="two-faq-question">
            <?php _e('How can I upgrade?', 'tenweb-speed-optimizer'); ?>
          </div>
          <div class="two-faq-answer">
            <?php echo sprintf( __( 'To Upgrade your plan simply click %s or go to the 10Web dashboard upgrade page.', 'tenweb-speed-optimizer' ), '<a href="' . esc_url( $two_upgrade_link ) . '" target="_blank">' . __( 'here', 'tenweb-speed-optimizer' ) . '</a>' ); ?>
          </div>
        </div>
        <div class="two-faq-item">
          <div class="two-faq-question">
            <?php _e('Optimization caused some issues, what should I do?', 'tenweb-speed-optimizer'); ?>
          </div>
          <div class="two-faq-answer">
            <?php _e('Although 10Web Booster is field-tested on more than 100,000 websites, sometimes issues occur, however, our engineers have created different modes. Switching from Extreme mode to other modes should solve the issues.', 'tenweb-speed-optimizer'); ?>
          </div>
        </div>
      </div>
      <div class="two-disconnect-popup">
        <div class="two-disconnect-popup-body">
          <div class="two-disconnect-popup-title">
            <?php _e('Disconnect Website', 'tenweb-speed-optimizer'); ?>
          </div>
          <div class="two-disconnect-popup-content">
            <p>
              <?php _e('Disconnecting a website from 10Web will rollback all optimization and caching configurations and negatively affect your PageSpeed.', 'tenweb-speed-optimizer'); ?>
            </p>
            <p>
              <?php _e('By disconnecting you will revert the following:', 'tenweb-speed-optimizer'); ?>
            </p>
            <div class="two-disconnect-popup-list">
              <p>
                <?php _e('PageSpeed score', 'tenweb-speed-optimizer'); ?>
              </p>
              <p>
                <?php _e('Improved Core Web Vitals', 'tenweb-speed-optimizer'); ?>
              </p>
              <p>
                <?php _e('Image optimization', 'tenweb-speed-optimizer'); ?>
              </p>
              <p>
                <?php _e('Caching', 'tenweb-speed-optimizer'); ?>
              </p>
            </div>
          </div>
          <div class="two-disconnect-popup-button-container">
            <a href="#" class="two-button two-disconnect-popup-button two-button-cancel"><?php _e('STAY CONNECTED', 'tenweb-speed-optimizer'); ?></a>
            <a href="<?php echo esc_url( $two_disconnect_link ); ?>" class="two-button two-disconnect-popup-button two-button-disconnect"><?php _e('DISCONNECT', 'tenweb-speed-optimizer'); ?></a>
          </div>
          <img src="<?php echo TENWEB_SO_URL; ?>/assets/images/close.svg" alt="Close" class="two-close-img" />
        </div>
      </div>
      <?php
    }
    ?>
  </div>
</div>
<?php

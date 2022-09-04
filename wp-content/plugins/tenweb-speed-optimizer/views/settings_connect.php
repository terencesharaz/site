<?php
$two_php_not_compatible = defined( 'TWO_INCOMPATIBLE_ERROR' ) && TWO_INCOMPATIBLE_ERROR;
$two_connect_link = $two_php_not_compatible ? '' : \TenWebOptimizer\OptimizerUtils::get_tenweb_connection_link();
$two_connection_error = defined( 'TWO_INCOMPATIBLE_WARNING' ) && TWO_INCOMPATIBLE_WARNING;
?>
  <div class="two-container disconnected">
      <?php
        include_once('two_header.php');
      ?>
    <div class="two-body-container">
      <?php
      if ( $two_php_not_compatible || $two_connection_error ) {
        global $two_incompatible_errors;
        foreach ( $two_incompatible_errors as $two_incompatible_error ) {
          ?>
          <div class="two-error">
            <img src="<?php echo TENWEB_SO_URL; ?>/assets/images/error.svg" alt="Error" class="two-error-img" />
            <b><?php echo esc_html( $two_incompatible_error['title'] ); ?></b> <?php echo esc_html( $two_incompatible_error['message'] ); ?>
          </div>
          <?php
        }
      }
      ?>
      <div class="two-body">
        <div class="two-greeting">
          <img src="<?php echo TENWEB_SO_URL; ?>/assets/images/waving_hand.png" alt="Hey" class="two-waving-hand" />
          <?php _e('Hello!', 'tenweb-speed-optimizer'); ?>
        </div>
        <div class="two-plugin-status">
          <?php _e('Welcome to 10Web Website Booster', 'tenweb-speed-optimizer'); ?>
        </div>
        <div class="two-plugin-description">
          <?php _e('Follow these steps to get started:', 'tenweb-speed-optimizer'); ?>
        </div>
        <div class="two-steps">
          <div class="two-step two-step-1">
            <div class="two-step-check">
              <div class="two-step-check-inner two-check"></div>
            </div>
            <div class="two-step-title">
              <?php _e('Step 1', 'tenweb-speed-optimizer'); ?>
            </div>
            <div class="two-step-body">
              <div class="two-step-header">
                <?php _e('Connect your website to 10Web', 'tenweb-speed-optimizer'); ?>
              </div>
              <div class="two-step-description">
                <?php _e('Sign up and connect your website to 10Web to enable the 10Web Booster service.', 'tenweb-speed-optimizer'); ?>
              </div>
            </div>
          </div>
          <div class="two-step two-step-2">
            <div class="two-step-check">
              <div class="two-step-check-inner two-flash"></div>
            </div>
            <div class="two-step-title">
              <?php _e('Step 2', 'tenweb-speed-optimizer'); ?>
            </div>
            <div class="two-step-body">
              <div class="two-step-header">
                <?php _e('Optimize your website’s frontend', 'tenweb-speed-optimizer'); ?>
              </div>
              <div class="two-step-description">
                <?php _e('Automatically optimize the frontend of your site, get a 90+ PageSpeed and pass Core Web Vitals.', 'tenweb-speed-optimizer'); ?>
              </div>
            </div>
          </div>
        </div>
        <a href="<?php echo esc_url( $two_connect_link ); ?>" class="two-button two-button-connect" <?php disabled( !$two_connect_link ); ?>><?php _e('SIGN UP & CONNECT', 'tenweb-speed-optimizer'); ?></a>
      </div>
      <div class="two-image-container">
        <img src="<?php echo TENWEB_SO_URL; ?>/assets/images/welcome_image.png" alt="Welcome to 10Web" class="two-welcome-image" />
        <div class="two-image-description">
          <div class="two-image-description-header">
            <?php _e('Access the benefits of 10Web Booster', 'tenweb-speed-optimizer'); ?>
          </div>
          <ul class="two-image-description-list">
            <li><?php _e('90+ PageSpeed score', 'tenweb-speed-optimizer'); ?></li>
            <li><?php _e('Image optimization', 'tenweb-speed-optimizer'); ?></li>
            <li><?php _e('Improved Core Web Vitals', 'tenweb-speed-optimizer'); ?></li>
            <li><?php _e('Full caching', 'tenweb-speed-optimizer'); ?></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
<?php

<?php

use TooMuchNiche\application\components\BootConfig;
use TooMuchNiche\application\components\Wizard;

defined('\ABSPATH') || exit; ?>

<div class="tmn">

    <div class="cegg5-container">

        <div class="col-12 p-2 p-md-4">

            <h1 class="display-6 mb-4">
                <?php echo \esc_html(\TooMuchNiche\application\Plugin::getName()); ?>
                <?php if (!empty($title)) : ?>
                    <small class="text-muted h5"><?php echo esc_html($title); ?></small>
                <?php endif; ?>
            </h1>

            <form id="wizard_form" action="options.php" method="POST">

                <div class="col-md-9 col-lg-7 mb-5 text-center">
                    <?php Wizard::getInstance()->printCircles(); ?>
                </div>

                <?php BootConfig::settingsErrors(true); ?>
                <?php \settings_fields($page_slug); ?>

                <?php if ($warning = $that->preSettingsWarning()) : ?>
                    <div class="tmn-callout tmn-callout-warning mb-5">
                        <h5 class"alert-heading""><?php echo esc_attr(__('Please fix the following issues to continue:', 'too-much-niche')); ?></h5>
                        <ul class="list-unstyled">
                            <?php echo \wp_kses_post($warning); ?>
                        </ul>
                    </div>
                <?php else : ?>

                    <?php BootConfig::doSettingsFields($page_slug, 'default'); ?>
                <?php endif; ?>

                <div class="col-12 mt-4">
                    <?php if (Wizard::getInstance()->getCurrentStep() > 1) : ?>
                        <?php $previous_url = wp_nonce_url(\get_admin_url(\get_current_blog_id(), 'admin.php?page=too-much-niche&action=tmniche-previous'), 'wizard_nonce'); ?>
                        <a id="tmn_previous" class="btn btn-secondary" href="<?php echo esc_attr($previous_url); ?>" role="button">&#8592; <?php echo esc_attr(__('Previous', 'too-much-niche')); ?></a>
                    <?php endif; ?>

                    <?php
                    if ($warning)
                        $btn_text = \esc_attr(__('Try again', 'too-much-niche')) .  "";
                    elseif (Wizard::getInstance()->isLastStep())
                        $btn_text = \esc_attr(__('Submit', 'too-much-niche'));
                    else
                        $btn_text = \esc_attr(__('Next', 'too-much-niche')) .  " &#8594";

                    ?>

                    <button id="tmn_submit" class="btn btn-primary" href="<?php echo esc_attr($previous_url); ?>" role="button"><?php echo $btn_text; ?></button>
                    <input id="tmn_real_submit" type="submit" style="display:none;">
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        jQuery('#tmn_submit').on('click', function(e) {
            if (!$('#wizard_form')[0].checkValidity())
                return true;
            var this_btn = $(this);
            this_btn.attr('disabled', true);
            jQuery('body').addClass('tmn_wait');
            jQuery('#tmn_real_submit').click();
            return false;
        });
    });
</script>
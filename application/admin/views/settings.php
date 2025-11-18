<?php

use IndependentNiche\application\components\BootConfig;
use IndependentNiche\application\components\Wizard;

defined('\ABSPATH') || exit; ?>

<div class="ind ind-animate-in">

    <div class="cegg5-container">

        <div class="col-12 p-2 p-md-4">

            <div class="ind-card">
                <div class="ind-card-header">
                    <h1 class="ind-card-title" style="margin: 0;">
                        <?php echo \esc_html(\IndependentNiche\application\Plugin::getName()); ?>
                        <?php if (!empty($title)) : ?>
                            <small class="ind-text-muted" style="font-size: 16px; font-weight: 400;"><?php echo esc_html($title); ?></small>
                        <?php endif; ?>
                    </h1>
                </div>

                <form id="wizard_form" action="options.php" method="POST">

                    <div class="col-md-9 col-lg-7 mb-5 text-center">
                        <?php Wizard::getInstance()->printCircles(); ?>
                    </div>

                    <?php BootConfig::settingsErrors(true); ?>
                    <?php \settings_fields($page_slug); ?>

                    <?php if ($warning = $that->preSettingsWarning()) : ?>
                        <div class="ind-callout ind-callout-warning mb-5">
                            <h4><?php echo esc_attr(__('⚠️ Please fix the following issues to continue:', 'independent-niche')); ?></h4>
                            <ul class="list-unstyled">
                                <?php echo \wp_kses_post($warning); ?>
                            </ul>
                        </div>
                    <?php else : ?>

                        <?php BootConfig::doSettingsFields($page_slug, 'default'); ?>
                    <?php endif; ?>

                    <div class="col-12 mt-4 ind-divider"></div>

                    <div class="col-12 mt-4">
                        <?php if (Wizard::getInstance()->getCurrentStep() > 1) : ?>
                            <?php $previous_url = wp_nonce_url(\get_admin_url(\get_current_blog_id(), 'admin.php?page=independent-niche&action=indniche-previous'), 'wizard_nonce'); ?>
                            <a id="ind_previous" class="btn btn-secondary" href="<?php echo esc_attr($previous_url); ?>" role="button">&#8592; <?php echo esc_attr(__('Previous', 'independent-niche')); ?></a>
                        <?php endif; ?>

                    <?php
                    if ($warning)
                        $btn_text = \esc_attr(__('Try again', 'independent-niche')) .  "";
                    elseif (Wizard::getInstance()->isLastStep())
                        $btn_text = \esc_attr(__('Submit', 'independent-niche'));
                    else
                        $btn_text = \esc_attr(__('Next', 'independent-niche')) .  " &#8594";

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
<?php

use IndependentNiche\application\components\BootConfig;
use IndependentNiche\application\components\Wizard;

defined('\ABSPATH') || exit; ?>

<div class="wrap ind ind-animate-in">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">

                <div class="ind-card" style="margin-top: 20px;">
                    <div class="ind-card-header-modern">
                        <div>
                            <h1 class="ind-card-title-large">
                                <?php echo \esc_html(\IndependentNiche\application\Plugin::getName()); ?>
                            </h1>
                            <?php if (!empty($title)) : ?>
                                <p class="text-muted" style="margin: 8px 0 0 0; font-size: 16px;"><?php echo esc_html($title); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                <form id="wizard_form" action="options.php" method="POST" class="ind-wizard-form">

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

                    <div class="col-12 mt-5" style="display: flex; gap: 12px; justify-content: flex-start; align-items: center; border-top: 2px solid #f3f4f6; padding-top: 24px;">
                        <?php if (Wizard::getInstance()->getCurrentStep() > 1) : ?>
                            <?php $previous_url = wp_nonce_url(\get_admin_url(\get_current_blog_id(), 'admin.php?page=independent-niche-wizard&action=ind-previous'), 'wizard_nonce'); ?>
                            <a id="ind_previous" class="ind-btn ind-btn-secondary" href="<?php echo esc_attr($previous_url); ?>" role="button">
                                &#8592; <?php echo esc_attr(__('Previous', 'independent-niche')); ?>
                            </a>
                        <?php endif; ?>

                    <?php
                    if ($warning)
                        $btn_text = \esc_attr(__('Try again', 'independent-niche')) .  "";
                    elseif (Wizard::getInstance()->isLastStep())
                        $btn_text = \esc_attr(__('🚀 Submit & Start', 'independent-niche'));
                    else
                        $btn_text = \esc_attr(__('Next', 'independent-niche')) .  " &#8594";

                    ?>

                    <button id="ind_submit" class="ind-btn ind-btn-primary ind-btn-large" type="button">
                        <?php echo $btn_text; ?>
                    </button>
                    <input id="ind_real_submit" type="submit" style="display:none;">
                </div>
            </form>

            </div>
        </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        jQuery('#ind_submit').on('click', function(e) {
            e.preventDefault();
            if (!$('#wizard_form')[0].checkValidity()) {
                $('#wizard_form')[0].reportValidity();
                return false;
            }
            var this_btn = $(this);
            this_btn.attr('disabled', true);
            this_btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <?php _e("Processing...", "independent-niche"); ?>');
            jQuery('body').addClass('ind_wait');
            jQuery('#ind_real_submit').click();
            return false;
        });
    });
</script>
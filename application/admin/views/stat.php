<?php

use IndependentNiche\application\components\Task;
use IndependentNiche\application\Plugin;

use function IndependentNiche\prn;
use function IndependentNiche\prnx;

if ($stat && isset($stat['in_queue']))
    $in_queue = $stat['in_queue'];
else
    $in_queue = 0;

?>

<?php if ($task->isStatusWorking() || $task->isStatusStopping()) : ?>
    <?php if (!Plugin::isDevEnvironment()) : ?>
        <meta http-equiv="refresh" content="30">
    <?php endif; ?>
<?php endif; ?>

<div class="ind ind-animate-in">
    <div class="cegg5-container">
        <div class="col-12 p-2 p-md-4 pb-md-0">

            <h1 class="display-6 mb-4">
                <?php echo \esc_html(\IndependentNiche\application\Plugin::getName()); ?>
                <small class="text-muted h5"><?php echo esc_html(__('Statistics', 'independent-niche')); ?></small>
            </h1>

            <div class="col-sm-12 col-md-12 col-xl-8 pe-3">
                <table class="table">
                    <tbody>
                        <tr>
                            <th class="col-md-3 col-lg-3 align-middle">
                                <?php echo esc_html(__('Status', 'independent-niche')); ?>
                            </th>
                            <td class="align-middle">
                                <div class="d-flex align-items-center justify-content-between gap-3">

                                    <?php if ($task->isStatusUnknown()) : ?>
                                        <div>-</div>
                                    <?php endif; ?>

                                    <?php if ($task->isStatusWorking()) : ?>
                                        <div>
                                            <div class="spinner-grow spinner-grow-sm text-primary me-1" role="status">
                                                <span class="visually-hidden">Working...</span>
                                            </div>
                                            <span class="text-primary fw-bold">
                                                <?php echo esc_html(__('In progress:', 'independent-niche')); ?>
                                            </span>
                                            <?php echo esc_html(__('Generating/posting article...', 'independent-niche')); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($task->isStatusStopping()) : ?>
                                        <div>
                                            <div class="spinner-grow spinner-grow-sm text-danger me-1" role="status">
                                                <span class="visually-hidden">Stopping...</span>
                                            </div>
                                            <span class="text-danger fw-bold"><?php echo esc_html(__('Stopping:', 'independent-niche')); ?></span>
                                            <?php echo esc_html(__('Shutting down process...', 'independent-niche')); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($task->isStatusError()) : ?>
                                        <div class="badge text-bg-danger"><?php echo esc_html(__('Error', 'independent-niche')); ?></div>
                                    <?php endif; ?>

                                    <?php if ($task->isStatusSuccess()) : ?>
                                        <div>
                                            <div class="text-success fw-bold">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                    <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0" />
                                                </svg>
                                                <?php echo esc_html(__('Done', 'independent-niche')); ?>
                                            </div>
                                            <?php if ($remaining_credits > 0) : ?>
                                                <div class="small text-muted">
                                                    <?php echo esc_html(sprintf(__('Remaining article credits: %d', 'independent-niche'), $remaining_credits)); ?>
                                                </div>
                                            <?php else : ?>
                                                <div class="small text-success">
                                                    <?php echo esc_html(__('All article generation credits have been used.', 'independent-niche')); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <a
                                        class="btn btn-primary btn-sm d-flex align-items-center justify-content-between"
                                        href="<?php echo esc_url(get_admin_url(get_current_blog_id(), 'admin.php?page=independent-niche')); ?>"
                                        title="<?php echo esc_attr(__('Start New Task', 'independent-niche')); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4" />
                                        </svg>
                                        <span class="ps-1"><?php echo esc_attr(__('New Task', 'independent-niche')); ?></span>
                                    </a>
                                </div>

                            </td>
                        </tr>

                        <?php if ($in_queue) : ?>
                            <tr>
                                <th class="align-middle"><?php echo esc_html(__('Articles in queue', 'independent-niche')); ?></th>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center justify-content-between gap-3">

                                        <div>
                                            <span class="badge <?php if ($task->isStatusStopping()): ?>bg-danger<?php else: ?>bg-primary<?php endif; ?>">
                                                <?php echo esc_html($in_queue); ?>
                                            </span>
                                        </div>

                                        <?php if ($task->isStatusWorking()): ?>
                                            <a
                                                class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-between"
                                                href="<?php echo esc_url(get_admin_url(get_current_blog_id(), 'admin.php?page=independent-niche-articles&action=stop_task&_wpnonce=' . wp_create_nonce('tmn_stop_task'))); ?>"
                                                onclick="return confirm('Are you sure you want to stop the generation?');"
                                                title="<?php echo esc_attr(__('Stop Generation', 'independent-niche')); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-stop-fill" viewBox="0 0 16 16">
                                                    <path d="M5 3.5h6A1.5 1.5 0 0 1 12.5 5v6a1.5 1.5 0 0 1-1.5 1.5H5A1.5 1.5 0 0 1 3.5 11V5A1.5 1.5 0 0 1 5 3.5" />
                                                </svg>
                                                <span class="ps-1">&nbsp;<?php echo esc_attr(__('Stop All', 'independent-niche')); ?>&nbsp;&nbsp;</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if (!$is_cron_enabled && !Plugin::isDevEnvironment()) : ?>
                            <tr>
                                <th class="text-info"><?php echo esc_html(__('Cron', 'independent-niche')); ?></th>
                                <td class="text-muted small">
                                    <?php
                                    echo \esc_html(__('DISABLE_WP_CRON is set to true, disabling WP cron.', 'independent-niche'));
                                    echo '<br>' . \esc_html(__('This plugin requires WordPress Cron to be enabled.', 'independent-niche'));
                                    echo ' ' . \esc_html(__('If you use an alternative cron, the plugin will work, and you can ignore this notice.', 'independent-niche'));
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if ($is_import_error && $in_queue && !Plugin::isDevEnvironment()) : ?>
                            <tr>
                                <th class="text-info"><?php echo esc_html(__('Article Posting Failed', 'independent-niche')); ?></th>
                                <td>
                                    <div class="small mb-2"><?php echo \esc_html(__("It appears that the import cron task is not currently running. Please ensure that the cron is properly configured and operational on your WordPress installation.", 'independent-niche')); ?></div>
                                    <div class="text-end">
                                        <a style="width: 100px;" title="<?php echo esc_html(__('Post without Cron', 'independent-niche')); ?>"
                                            class="btn btn-outline-info btn-sm"
                                            href="<?php echo \get_admin_url(\get_current_blog_id(), 'admin.php?page=independent-niche-articles&action=post_now&_wpnonce=' . \wp_create_nonce('tmn_post_manually')); ?>">
                                            <?php echo esc_html(__('Post Now', 'independent-niche')); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if ($task->isStatusSuccess() && $coupon) : ?>
                            <tr>
                                <th></th>
                                <td colspan="1" class="pt-3 pb-3 text-center1">
                                    <div class="mb-2">
                                        <?php echo esc_html(sprintf(__('Use the exclusive coupon code "%s" for a 30%% discount on your next order.', 'independent-niche'), $coupon)); ?>
                                    </div>
                                    <a target="_blank" class="btn btn-sm btn-success" href="https://www.keywordrush.com/toomuchniche/pricing?ref=<?php echo esc_attr($coupon); ?>&utm_source=toomuchniche&utm_medium=referral&utm_campaign=coupon30">Apply 30% OFF Coupon Now!</a>
                                    <div class="small mt-2"><?php echo esc_html(sprintf(__("Valid for 3 days until %s", 'independent-niche'), $coupon_date_formated)); ?></div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($task->isStatusSuccess() && !$remaining_credits && !$coupon) : ?>
                            <tr>
                                <th></th>
                                <td colspan="1" class="pt-3 pb-3 text-center1">
                                    <a target="_blank" class="btn btn-sm btn-outline-success" href="https://www.keywordrush.com/toomuchniche/pricing?utm_source=toomuchniche&utm_medium=referral&utm_campaign=newlicense">Buy a New License Key</a>
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    table.wp-list-table .column-log_level {
        width: 10ch;
    }

    table.wp-list-table .column-log_time {
        width: 20ch;
    }

    table.wp-list-table mark.error,
    table.wp-list-table mark.warning,
    table.wp-list-table mark.info,
    table.wp-list-table mark.debug,
    table.wp-list-table mark.pending,
    table.wp-list-table mark.approved,
    table.wp-list-table mark.declined {
        font-weight: 700;
        background: transparent none;
        line-height: 1;
    }

    table.wp-list-table mark.error {
        color: #dc3545;
    }

    table.wp-list-table mark.warning {
        color: #ffc107;
    }

    table.wp-list-table mark.info {
        color: #17a2b8;
    }

    table.wp-list-table mark.debug {
        color: #6c757d;
    }
</style>

<div class="ind ind-animate-in">
    <div class="cegg5-container">
        <div class="col-sm-12 col-md-12 p-2 p-md-4">
            <div class="fs-4">
                <?php echo esc_html(__('Posted Articles', 'independent-niche')); ?>

                <a class="ms-2 link-secondary"
                    title="<?php echo esc_attr(__('Reset Posted Articles Log', 'independent-niche')); ?>"
                    href="<?php echo esc_url(get_admin_url(get_current_blog_id(), 'admin.php?page=independent-niche-articles&action=reset_log&_wpnonce=' . wp_create_nonce('tmn_reset_log'))); ?>"
                    onclick="return confirm('Are you sure you want to reset the posted articles log?');">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-journal-x" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6.146 6.146a.5.5 0 0 1 .708 0L8 7.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 8l1.147 1.146a.5.5 0 0 1-.708.708L8 8.707 6.854 9.854a.5.5 0 0 1-.708-.708L7.293 8 6.146 6.854a.5.5 0 0 1 0-.708" />
                        <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2" />
                        <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z" />
                    </svg>
                </a>

            </div>
            <form id="ind-log-table" method="GET">
                <input type="hidden" name="page" value="<?php echo \esc_attr($_REQUEST['page']); ?>" />
                <?php $table->views(); ?>
                <?php $table->display(); ?>
            </form>
        </div>
    </div>
</div>
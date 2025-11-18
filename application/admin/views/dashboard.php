<?php
defined('\ABSPATH') || exit;

use IndependentNiche\application\Plugin;
use IndependentNiche\application\components\Task;

$task_status = Task::getInstance()->getStatus();
$setup_url = \get_admin_url(\get_current_blog_id(), 'admin.php?page=' . Plugin::getSlug() . '-wizard');
?>

<div class="wrap ind ind-dashboard">
    <div class="ind-hero">
        <div class="ind-hero-content">
            <h1 class="ind-hero-title">
                🚀 <?php echo esc_html(Plugin::getName()); ?>
            </h1>
            <p class="ind-hero-subtitle">
                <?php _e('Professional Content Generation with DeepSeek AI', 'independent-niche'); ?>
            </p>
        </div>
    </div>

    <div class="ind-container">
        <div class="ind-row">
            <!-- Left Column: API Key Configuration -->
            <div class="ind-col-8">

                <!-- API Key Configuration Card -->
                <div class="ind-card ind-card-highlight">
                    <div class="ind-card-header-modern">
                        <h2 class="ind-card-title-large">
                            🔑 <?php _e('DeepSeek API Configuration', 'independent-niche'); ?>
                        </h2>
                        <?php if ($has_api_key): ?>
                            <span class="ind-badge ind-badge-success">✓ <?php _e('Configured', 'independent-niche'); ?></span>
                        <?php else: ?>
                            <span class="ind-badge ind-badge-warning">⚠ <?php _e('Required', 'independent-niche'); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="ind-card-body">
                        <?php if (!$has_api_key): ?>
                            <div class="ind-alert ind-alert-info">
                                <div class="ind-alert-icon">ℹ️</div>
                                <div class="ind-alert-content">
                                    <h4><?php _e('Get Started in 2 Minutes', 'independent-niche'); ?></h4>
                                    <ol class="ind-steps-list">
                                        <li><?php _e('Visit', 'independent-niche'); ?> <a href="https://platform.deepseek.com" target="_blank" class="ind-link-primary"><strong>platform.deepseek.com</strong></a></li>
                                        <li><?php _e('Create a free account or sign in', 'independent-niche'); ?></li>
                                        <li><?php _e('Generate an API key from your dashboard', 'independent-niche'); ?></li>
                                        <li><?php _e('Copy and paste it below', 'independent-niche'); ?></li>
                                    </ol>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="" class="ind-form">
                            <?php wp_nonce_field('ind_save_api_key'); ?>

                            <div class="ind-form-group">
                                <label for="deepseek_api_key" class="ind-label-modern">
                                    <?php _e('Your DeepSeek API Key', 'independent-niche'); ?>
                                </label>
                                <div class="ind-input-wrapper">
                                    <span class="ind-input-icon">🔐</span>
                                    <input
                                        type="text"
                                        id="deepseek_api_key"
                                        name="deepseek_api_key"
                                        class="ind-input-modern ind-input-api-key"
                                        value="<?php echo esc_attr($api_key); ?>"
                                        placeholder="sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                        autocomplete="off"
                                        spellcheck="false"
                                    />
                                </div>
                                <p class="ind-help-text">
                                    <?php _e('Your API key is stored securely in your WordPress database and never shared.', 'independent-niche'); ?>
                                </p>
                            </div>

                            <div class="ind-form-actions">
                                <button type="submit" name="ind_save_api_key" class="ind-btn ind-btn-primary ind-btn-large">
                                    <span class="ind-btn-icon">💾</span>
                                    <?php _e('Save API Key', 'independent-niche'); ?>
                                </button>
                            </div>
                        </form>

                        <?php if ($has_api_key): ?>
                            <div class="ind-api-features">
                                <h4><?php _e('What You Can Do:', 'independent-niche'); ?></h4>
                                <div class="ind-features-grid">
                                    <div class="ind-feature-item">
                                        <span class="ind-feature-icon">✍️</span>
                                        <span><?php _e('Generate SEO articles', 'independent-niche'); ?></span>
                                    </div>
                                    <div class="ind-feature-item">
                                        <span class="ind-feature-icon">🎯</span>
                                        <span><?php _e('Auto niche research', 'independent-niche'); ?></span>
                                    </div>
                                    <div class="ind-feature-item">
                                        <span class="ind-feature-icon">🌍</span>
                                        <span><?php _e('Multi-language support', 'independent-niche'); ?></span>
                                    </div>
                                    <div class="ind-feature-item">
                                        <span class="ind-feature-icon">⚡</span>
                                        <span><?php _e('Fast & cost-effective', 'independent-niche'); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <?php if ($has_api_key): ?>
                <div class="ind-card">
                    <div class="ind-card-header-modern">
                        <h3 class="ind-card-title">⚡ <?php _e('Quick Actions', 'independent-niche'); ?></h3>
                    </div>
                    <div class="ind-card-body">
                        <div class="ind-quick-actions">
                            <?php if ($wizard_step < 7): ?>
                                <a href="<?php echo esc_url($setup_url); ?>" class="ind-action-card">
                                    <span class="ind-action-icon">🔧</span>
                                    <div class="ind-action-content">
                                        <h4><?php _e('Complete Setup', 'independent-niche'); ?></h4>
                                        <p><?php _e('Configure your niche and preferences', 'independent-niche'); ?></p>
                                    </div>
                                    <span class="ind-action-arrow">→</span>
                                </a>
                            <?php else: ?>
                                <a href="<?php echo esc_url(\get_admin_url(\get_current_blog_id(), 'admin.php?page=independent-niche-articles')); ?>" class="ind-action-card">
                                    <span class="ind-action-icon">📝</span>
                                    <div class="ind-action-content">
                                        <h4><?php _e('View Articles', 'independent-niche'); ?></h4>
                                        <p><?php _e('Manage your generated content', 'independent-niche'); ?></p>
                                    </div>
                                    <span class="ind-action-arrow">→</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Status & Info -->
            <div class="ind-col-4">

                <!-- Configuration Status -->
                <div class="ind-card ind-card-compact">
                    <div class="ind-card-header-modern">
                        <h3 class="ind-card-title">📊 <?php _e('Setup Progress', 'independent-niche'); ?></h3>
                    </div>
                    <div class="ind-card-body">
                        <div class="ind-progress-circle-wrapper">
                            <svg class="ind-progress-circle" width="120" height="120">
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                <circle
                                    cx="60" cy="60" r="54"
                                    fill="none"
                                    stroke="#3b82f6"
                                    stroke-width="8"
                                    stroke-dasharray="339.292"
                                    stroke-dashoffset="<?php echo 339.292 - (339.292 * $config_percentage / 100); ?>"
                                    transform="rotate(-90 60 60)"
                                    class="ind-progress-bar"
                                />
                                <text x="60" y="70" text-anchor="middle" class="ind-progress-text"><?php echo $config_percentage; ?>%</text>
                            </svg>
                        </div>

                        <div class="ind-status-list">
                            <div class="ind-status-item <?php echo $has_api_key ? 'ind-status-complete' : 'ind-status-pending'; ?>">
                                <span class="ind-status-icon"><?php echo $has_api_key ? '✓' : '○'; ?></span>
                                <span><?php _e('DeepSeek API Key', 'independent-niche'); ?></span>
                            </div>
                            <div class="ind-status-item <?php echo $has_niche ? 'ind-status-complete' : 'ind-status-pending'; ?>">
                                <span class="ind-status-icon"><?php echo $has_niche ? '✓' : '○'; ?></span>
                                <span><?php _e('Niche Configuration', 'independent-niche'); ?></span>
                            </div>
                            <div class="ind-status-item <?php echo $wizard_step >= 7 ? 'ind-status-complete' : 'ind-status-pending'; ?>">
                                <span class="ind-status-icon"><?php echo $wizard_step >= 7 ? '✓' : '○'; ?></span>
                                <span><?php _e('Setup Wizard', 'independent-niche'); ?></span>
                            </div>
                        </div>

                        <?php if ($config_percentage < 100): ?>
                            <a href="<?php echo esc_url($setup_url); ?>" class="ind-btn ind-btn-secondary ind-btn-block">
                                <?php _e('Continue Setup', 'independent-niche'); ?> →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Help & Resources -->
                <div class="ind-card ind-card-compact">
                    <div class="ind-card-header-modern">
                        <h3 class="ind-card-title">📚 <?php _e('Resources', 'independent-niche'); ?></h3>
                    </div>
                    <div class="ind-card-body">
                        <ul class="ind-resource-list">
                            <li>
                                <a href="https://platform.deepseek.com/docs" target="_blank" class="ind-resource-link">
                                    <span class="ind-resource-icon">📖</span>
                                    <span><?php _e('DeepSeek API Docs', 'independent-niche'); ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="https://platform.deepseek.com" target="_blank" class="ind-resource-link">
                                    <span class="ind-resource-icon">💳</span>
                                    <span><?php _e('Manage API Credits', 'independent-niche'); ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url($setup_url); ?>" class="ind-resource-link">
                                    <span class="ind-resource-icon">⚙️</span>
                                    <span><?php _e('Plugin Settings', 'independent-niche'); ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Version Info -->
                <div class="ind-version-info">
                    <p><?php echo esc_html(Plugin::getName()); ?></p>
                    <p class="ind-version">v<?php echo esc_html(Plugin::version()); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

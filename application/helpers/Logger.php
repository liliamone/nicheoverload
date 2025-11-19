<?php

namespace IndependentNiche\application\helpers;

defined('\ABSPATH') || exit;

/**
 * Logger class for Independent Niche Generator
 * Logs all errors, warnings, and debug info to a dedicated file
 *
 * @author Independent Developer
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class Logger
{
    private static $instance = null;
    private $log_file;
    private $enabled = true;

    private function __construct()
    {
        // Create logs directory if it doesn't exist
        $log_dir = \IndependentNiche\PLUGIN_PATH . 'logs';
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
            // Protect logs directory with .htaccess
            file_put_contents($log_dir . '/.htaccess', "Deny from all\n");
        }

        $this->log_file = $log_dir . '/independent-niche.log';

        // Enable logging if WP_DEBUG is on OR if specifically enabled
        $this->enabled = (defined('WP_DEBUG') && WP_DEBUG) || get_option('independent-niche_debug_mode', false);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log an error message
     */
    public function error($message, $context = array())
    {
        $this->log('ERROR', $message, $context);
    }

    /**
     * Log a warning message
     */
    public function warning($message, $context = array())
    {
        $this->log('WARNING', $message, $context);
    }

    /**
     * Log an info message
     */
    public function info($message, $context = array())
    {
        $this->log('INFO', $message, $context);
    }

    /**
     * Log a debug message
     */
    public function debug($message, $context = array())
    {
        $this->log('DEBUG', $message, $context);
    }

    /**
     * Log a success message
     */
    public function success($message, $context = array())
    {
        $this->log('SUCCESS', $message, $context);
    }

    /**
     * Log API calls
     */
    public function api($endpoint, $status, $message = '', $context = array())
    {
        $api_message = "API [{$endpoint}] - Status: {$status}";
        if (!empty($message)) {
            $api_message .= " - {$message}";
        }
        $this->log('API', $api_message, $context);
    }

    /**
     * Log wizard steps
     */
    public function wizard($step, $message, $context = array())
    {
        $wizard_message = "Wizard Step {$step}: {$message}";
        $this->log('WIZARD', $wizard_message, $context);
    }

    /**
     * Main logging method
     */
    private function log($level, $message, $context = array())
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $timestamp = current_time('Y-m-d H:i:s');
            $user_id = get_current_user_id();
            $user_info = $user_id ? " [User:{$user_id}]" : "";

            // Format context
            $context_str = '';
            if (!empty($context)) {
                $context_str = "\n    Context: " . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }

            // Build log entry
            $log_entry = sprintf(
                "[%s]%s [%s] %s%s\n",
                $timestamp,
                $user_info,
                str_pad($level, 8),
                $message,
                $context_str
            );

            // Also log to WordPress error log
            error_log("Independent Niche [{$level}]: {$message}");

            // Write to dedicated file
            file_put_contents($this->log_file, $log_entry, FILE_APPEND);

            // Rotate log file if too large (> 5MB)
            $this->rotateLogIfNeeded();

        } catch (\Exception $e) {
            // Fallback to error_log if file writing fails
            error_log("Independent Niche Logger Error: " . $e->getMessage());
        }
    }

    /**
     * Rotate log file if it gets too large
     */
    private function rotateLogIfNeeded()
    {
        if (file_exists($this->log_file) && filesize($this->log_file) > 5 * 1024 * 1024) {
            $backup_file = $this->log_file . '.' . date('Y-m-d-His') . '.bak';
            rename($this->log_file, $backup_file);

            // Keep only last 5 backup files
            $log_dir = dirname($this->log_file);
            $backups = glob($log_dir . '/*.bak');
            if (count($backups) > 5) {
                usort($backups, function($a, $b) {
                    return filemtime($a) - filemtime($b);
                });
                foreach (array_slice($backups, 0, count($backups) - 5) as $old_backup) {
                    unlink($old_backup);
                }
            }
        }
    }

    /**
     * Get log file path
     */
    public function getLogFile()
    {
        return $this->log_file;
    }

    /**
     * Clear log file
     */
    public function clear()
    {
        if (file_exists($this->log_file)) {
            file_put_contents($this->log_file, '');
            $this->info('Log file cleared');
        }
    }

    /**
     * Get last N lines from log
     */
    public function tail($lines = 100)
    {
        if (!file_exists($this->log_file)) {
            return array();
        }

        $file = file($this->log_file);
        return array_slice($file, -$lines);
    }

    /**
     * Enable debug mode
     */
    public static function enableDebug()
    {
        update_option('independent-niche_debug_mode', true);
        self::getInstance()->enabled = true;
        self::getInstance()->info('Debug mode enabled');
    }

    /**
     * Disable debug mode
     */
    public static function disableDebug()
    {
        self::getInstance()->info('Debug mode disabled');
        update_option('independent-niche_debug_mode', false);
        self::getInstance()->enabled = false;
    }
}

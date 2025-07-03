<?php

namespace TooMuchNiche\application\components;

use TooMuchNiche\application\Plugin;

defined('\ABSPATH') || exit;

/**
 * Wizard class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class Wizard
{
    const TOTAL_STEPS = 7;

    private $current_step = null;
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
    }

    public function setCurrentStep($step)
    {
        $step = abs($step);

        if ($step == self::TOTAL_STEPS + 1)
            $step = 0; //finish

        if ($step > self::TOTAL_STEPS)
            $step = self::TOTAL_STEPS;

        $this->current_step = $step;
        \update_option(Plugin::slug . '_current_step', $this->current_step);
    }

    public function getCurrentStep()
    {
        if ($this->current_step === null)
            $this->current_step = (int) \get_option(Plugin::slug . '_current_step', 1);

        return $this->current_step;
    }

    public function isLastStep()
    {
        if ($this->getCurrentStep() == self::TOTAL_STEPS)
            return true;
        else
            return false;
    }

    public function printCircles()
    {
        for ($i = 1; $i <= self::TOTAL_STEPS; $i++)
        {
            echo '<span class="tmn-step';

            if ($i == $this->getCurrentStep())
                echo ' tmn-active';

            if ($i < $this->getCurrentStep())
                echo ' tmn-done';
            echo '">';
            echo '</span>';
        }
    }
}

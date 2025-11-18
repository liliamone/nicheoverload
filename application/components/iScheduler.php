<?php

namespace IndependentNiche\application\components;

defined('\ABSPATH') || exit;

/**
 * Scheduler interface file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
interface iScheduler
{

    public static function getCronTag();

    public static function run();
}

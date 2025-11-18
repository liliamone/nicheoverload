<?php

namespace IndependentNiche\application\components\logger;

defined('\ABSPATH') || exit;

/**
 * Dispatcher class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
class Dispatcher
{

    public $targets = array();

    public function init()
    {
        foreach ($this->targets as $name => $target)
        {
            if (!is_object($target))
            {
                $this->targets[$name] = self::createTarget($target);
            }
        }
    }

    private function createTarget($target)
    {
        $class = __NAMESPACE__ . '\\' . $target['class'];
        unset($target['class']);

        $object = new $class;
        foreach ($target as $key => $value)
        {
            $object->$key = $value;
        }
        return $object;
    }

    public function dispatch($messages)
    {
        foreach ($this->targets as $target)
        {
            if (!$target->enabled)
                continue;
            try
            {
                $target->process($messages);
            }
            catch (\Exception $e)
            {
                //@TODO
                continue;
            }
        }
    }
}

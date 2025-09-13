<?php
declare(strict_types=1);

namespace WarriorPHP\Event;

use support\Container;
use support\Log;
use function array_values;
use function is_array;
use function is_string;

class BootStrap implements \Webman\Bootstrap
{
    /**
     * @var array
     */
    protected static array $events = [];

    /**
     * @param $worker
     *
     * @return void
     */
    public static function start($worker): void
    {
        static::getEvents([config()]);
        foreach (static::$events as $name => $events) {
            // 支持排序，1 2 3 ... 9 a b c...z
            ksort($events, SORT_NATURAL);
            foreach ($events as $callbacks) {
                foreach ($callbacks as $callback) {
                    Event::on($name, $callback);
                }
            }
        }
    }

    /**
     * @param $callbacks
     *
     * @return array
     */
    protected static function convertCallable($callbacks): array
    {
        if (is_array($callbacks)) {
            $callback = array_values($callbacks);
            if (isset($callback[1]) && is_string($callback[0]) && \class_exists($callback[0])) {
                return [Container::get($callback[0]), $callback[1]];
            }
        }
        return $callbacks;
    }

    /**
     * @param $configs
     *
     * @return void
     */
    protected static function getEvents($configs): void
    {
        foreach ($configs as $config) {
            if (!is_array($config)) {
                continue;
            }
            if (isset($config['event']) && is_array($config['event']) && !isset($config['event']['app']['enable'])) {
                foreach ($config['event'] as $event_name => $callbacks) {
                    $callbacks = static::convertCallable($callbacks);
                    if (is_callable($callbacks)) {
                        static::$events[$event_name][] = [$callbacks];
                        continue;
                    }
                    ksort($callbacks, SORT_NATURAL);
                    foreach ($callbacks as $id => $callback) {
                        $callback = static::convertCallable($callback);
                        if (is_callable($callback)) {
                            static::$events[$event_name][$id][] = $callback;
                            continue;
                        }
                        $msg = "Events: $event_name => " . var_export($callback, true) . " is not callable" . PHP_EOL;
                        echo $msg;
                        Log::error($msg);
                    }
                }
                unset($config['event']);
            }
            static::getEvents($config);
        }
    }

}
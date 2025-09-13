<?php
declare(strict_types=1);

namespace WarriorPHP\Event;

class Install
{
    const WEBMAN_PLUGIN = true;

    /**
     * 源路径与目标路径的映射关系
     *
     * Path relations from plugin source to project destination
     *
     * @var array
     */
    protected static array $pathRelation = [
        'config' => [
            'path'  => 'config',
            'files' => ['event.php'],
        ],
    ];

    /**
     * Install
     *
     * @return void
     */
    public static function install(): void
    {
        static::installByRelation();
    }

    /**
     * Uninstall
     *
     * @return void
     */
    public static function uninstall(): void
    {

    }

    /**
     * 按路径映射关系拷贝文件夹
     * Install directories/files based on pathRelation map
     *
     * @return void
     */
    public static function installByRelation(): void
    {
        foreach (static::$pathRelation as $source => $info) {
            $dest = $info['path'];

            // 如果目标路径包含目录结构，确保其存在
            if ($pos = strrpos($dest, '/')) {
                $parent_dir = base_path() . '/' . substr($dest, 0, $pos);
                if (!is_dir($parent_dir)) {
                    mkdir($parent_dir, 0777, true);
                }
            }

            // 执行目录复制
            copy_dir(__DIR__ . "/$source", base_path() . "/$dest");
        }
    }
}
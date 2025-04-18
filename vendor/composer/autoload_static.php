<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8e40dbcbd20280eb1498a6aa84fb9d6f
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Combodo\\iTop\\Extension\\WorkflowGraphicalView\\' => 45,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Combodo\\iTop\\Extension\\WorkflowGraphicalView\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Combodo\\iTop\\Extension\\WorkflowGraphicalView\\Helper\\ConfigHelper' => __DIR__ . '/../..' . '/src/Helper/ConfigHelper.php',
        'Combodo\\iTop\\Extension\\WorkflowGraphicalView\\Helper\\LifecycleGraphHelper' => __DIR__ . '/../..' . '/src/Helper/LifecycleGraphHelper.php',
        'Combodo\\iTop\\Extension\\WorkflowGraphicalView\\Portal\\Controller\\LifecycleBrickController' => __DIR__ . '/../..' . '/src/Portal/Controller/LifecycleBrickController.php',
        'Combodo\\iTop\\Extension\\WorkflowGraphicalView\\Service\\GraphvizGenerator' => __DIR__ . '/../..' . '/src/Service/GraphvizGenerator.php',
        'Combodo\\iTop\\Extension\\WorkflowGraphicalView\\Service\\LifecycleManager' => __DIR__ . '/../..' . '/src/Service/LifecycleManager.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8e40dbcbd20280eb1498a6aa84fb9d6f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8e40dbcbd20280eb1498a6aa84fb9d6f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit8e40dbcbd20280eb1498a6aa84fb9d6f::$classMap;

        }, null, ClassLoader::class);
    }
}

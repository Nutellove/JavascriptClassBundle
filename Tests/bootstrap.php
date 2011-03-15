<?php

//echo $_SERVER['SYMFONY'];
//echo '--';
//var_dump($_SERVER);

//require_once $_SERVER['SYMFONY'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php';
require_once '/home/tonioth/htdocs/cynergiae_git/vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace('Symfony', "/home/tonioth/htdocs/cynergiae_git/vendor/symfony/src");
//$loader->registerNamespace('Symfony', $_SERVER['SYMFONY']);
$loader->register();


spl_autoload_register(function($class)
{
    if (0 === strpos($class, 'Nutellove\\JavascriptClassBundle\\')) {
        $path = implode('/', array_slice(explode('\\', $class), 2)).'.php';
        require_once __DIR__.'/../'.$path;
        return true;
    }
});
 

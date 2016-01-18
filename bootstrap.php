<?php
/**
 * @Author: e 17.01.2016
 * @param $class_name
 */

function __autoload($class_name) {
    $classPath = str_replace('\\', '/', $class_name);
    require_once __DIR__ . '/src/' . $classPath . '.php';
}


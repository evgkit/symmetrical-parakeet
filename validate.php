<?php
/**
 * @Author: e 15.01.2016
 */

if ($tel = isset($_REQUEST['tel']) ? trim($_REQUEST['tel']) : '') {
    function __autoload($class_name) {
        require_once $class_name . '.php';
    }

    $validator = new PhoneNumberValidator();
    $data = $validator->run($tel);
    echo json_encode($data);
}


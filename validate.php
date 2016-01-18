<?php
/**
 * @Author: e 15.01.2016
 */

include './bootstrap.php';

if ($tel = isset($_REQUEST['tel']) ? trim($_REQUEST['tel']) : '') {
    $validator = new e\PhoneNumberValidator();
    $data = $validator->run($tel);
    echo json_encode($data);
}


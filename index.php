<?php

function __autoload($class_name) {
    require_once $class_name . '.php';
}

$validator = new PhoneNumberValidator();
$wow = $validator->validate('+(358)-4570-123-45-67');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
    <h1><?= $wow ?></h1>
</body>
</html>
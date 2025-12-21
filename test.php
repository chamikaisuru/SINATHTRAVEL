<?php
header("Content-Type: application/json");
echo json_encode([
    "status" => "success",
    "message" => "PHP is working! ✅",
    "php_version" => phpversion(),
    "current_file" => __FILE__,
    "server_port" => $_SERVER['SERVER_PORT']
]);
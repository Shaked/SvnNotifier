<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
use SPC\Application;
$request = null;
if (isset($argv)){
    list($_, $method, $path,$env) = $argv;
    $request = Request::create($path, $method);
} 
$env = isset($env)? $env: Application::ENV_PROD;
$app = new Application($env,__DIR__);

$app->init()->run($request);
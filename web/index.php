<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
use SPC\Application; 
use SPC\Cli;
use SPC\Cli_Exception;
$options = getopt('r:e:',array('env:'));
try {
    $request = Cli::createRequest(@$options);
} catch(Cli_Exception $e){ 
    die($e->getMessage()); 
}
$env = isset($env)? $env: Application::ENV_DEV;
$app = new Application($env,__DIR__);
$app->init()->run($request);
exit(); 
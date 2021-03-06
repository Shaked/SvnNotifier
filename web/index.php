<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
use SPC\Application; 
use SPC\Cli;
use SPC\CliException;
$options = getopt('n:r:e:',array('env:'))? :null;
try { 
    $request = Cli::createRequest($options);
} catch(CliException $e){ 
    die($e->getMessage()); 
}
$env = isset($env)? $env: Application::ENV_PROD;
$app = new Application($env,__DIR__);
$app->init()->run($request);
exit(); 
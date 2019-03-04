<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// $container['db'] = function ($c) {
// 	$settings = $c->get('settings')['db'];

// 	return $pdo;
// }

$container['LoginController'] = function ($c) {
	return new app\controller\LoginController($c);
};

$container['LoginService'] = function ($c) {
	return new app\src\services\LoginService($c);
};
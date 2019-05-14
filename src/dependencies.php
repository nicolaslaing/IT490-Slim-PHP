<?php
// DIC configuration
use Slim\App;

return function (App $app) {

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

    $container['db'] = function ($c) {
        $settings = $c->get('settings')['db'];
        $notConnected = true;
        $addressIndex = 0;
        $address = array('192.168.0.14','192.168.0.15','192.168.0.16');

        while($notConnected){
            try {
                $pdo = new PDO("mysql:host=" . $address[$addressIndex] . ";dbname=" . $settings['dbname'] . ";port=" . $settings['port'], $settings['user'], $settings['pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $notConnected = false;
            } catch(PDOException $e) {
                $addressIndex++;
            }
        }

        return $pdo;
    };

    $container['LoginController'] = function ($c) {
        return new \app\controller\LoginController($c);
    };

    $container['LoginService'] = function ($c) {
        return new \app\src\services\LoginService($c);
    };

    $container['SongController'] = function ($c) {
        return new \app\controller\SongController($c);
    };

    $container['SongService'] = function ($c) {
        return new \app\src\services\SongService($c);
    };

    $container['UserController'] = function ($c) {
        return new \app\controller\UserController($c);
    };

    $container['UserService'] = function ($c) {
        return new \app\src\services\UserService($c);
    };

    $container['RabbitMQService'] = function ($c) {
        return new \app\src\services\RabbitMQService($c);
    };
};
<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $app->post('/login', LoginController::class.':doLogin');
    $app->post('/register', LoginController::class.':register');
    $app->post('/forgotusername', LoginController::class.':forgotUsername');
    $app->post('/forgotpassword', LoginController::class.':forgotPassword');
    $app->post('/resetpassword', LoginController::class.':resetPassword');


    $app->get('/api', SongController::class.':callAPI');
    $app->get('/artist/{entityId}', SongController::class.':artist');
    $app->post('/search', SongController::class.':search');
    $app->post('/log', UserController::class.':auditLog');

    // Catch-all route to serve a 404 Not Found page if none of the routes match
    // NOTE: make sure this route is defined last
    // $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    //     $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    //     return $handler($req, $res);
    // });
};
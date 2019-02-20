<?php
namespace app\controller;
use PDO;

class LoginController{

	public function __construct($app){
		$this->app = $app;
		$this->service = $app['LoginService'];
	}

	public function doLogin($request){
		$username = json_decode($request->getBody(), true)['username'];
		$password = json_decode($request->getBody(), true)['password'];

		print_r($username);
		print_r($password);

		return $this->app->response->withJson($username, 200);
	}

}
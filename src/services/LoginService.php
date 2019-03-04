<?php
namespace app\src\services;
use PDO;

class LoginService {

	public function __construct($app){
		$this->app = $app;
	}

	public function doLogin($request){
		$username = json_decode($request->getBody(), true)['username'];
		$password = json_decode($request->getBody(), true)['password'];

		echo "Test";
		// print_r($username);
		// print_r($password);

		// return $this->app->response->withJson($username, 200);
	}

}
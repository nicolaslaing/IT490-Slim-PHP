<?php
namespace app\controller;
use PDO;

class LoginController {

	public function __construct($app){
		$this->app = $app;
		$this->service = $app['LoginService'];
	}

	public function doLogin($request){
		$status = 200;
		$username = json_decode($request->getBody(), true)['username'];
		$password = json_decode($request->getBody(), true)['password'];

		$query = "SELECT id, fName, lName, username, email, created FROM User WHERE username=:username AND password=:password";
		
		$sth = $this->app->db->prepare($query);
		$sth->bindParam("username", $username);
		$sth->bindParam("password", $password);
		$sth->execute();
		$users = $sth->fetchAll();

		if(empty($users)){
			$status = 400;
		}

		return $this->app->response->withJson($users, $status);
	}

}
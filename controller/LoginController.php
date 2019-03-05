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

	public function register($request){

		$fName = json_decode($request->getBody(), true)['fName'];
		$lName = json_decode($request->getBody(), true)['lName'];
		$email = json_decode($request->getBody(), true)['email'];
		$username = json_decode($request->getBody(), true)['username'];
		$password = json_decode($request->getBody(), true)['password'];

		$query = "INSERT INTO User (fName, lName, email, username, password, created) VALUES (:fName, :lName, :email, :username, :password, NOW())";
		
		$sth = $this->app->db->prepare($query);
		$sth->bindParam("fName", $fName);
		$sth->bindParam("lName", $lName);
		$sth->bindParam("email", $email);
		$sth->bindParam("username", $username);
		$sth->bindParam("password", $password);
		$sth->execute();

		return 200;

	}

}
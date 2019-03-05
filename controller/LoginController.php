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
		$user = $sth->fetchObject();

		if(empty($user)){
			$status = 400;
		}

		$userid = $user->id;

		$query = "INSERT INTO User_log (`User_id`, `Action`, `timestamp`) VALUES (:userid, 'Logged in', NOW())";

		$sth = $this->app->db->prepare($query);
		$sth->bindParam("userid", $userid);
		$sth->execute();

		return $this->app->response->withJson($user, $status);

	}

	public function register($request){

		$status = 200;

		$fName = json_decode($request->getBody(), true)['fName'];
		$lName = json_decode($request->getBody(), true)['lName'];
		$email = json_decode($request->getBody(), true)['email'];
		$username = json_decode($request->getBody(), true)['username'];
		$password = json_decode($request->getBody(), true)['password'];

		$query = "SELECT id, fName, lName, username, email, created FROM User WHERE username=:username OR email=:email";

		$sth = $this->app->db->prepare($query);
		$sth->bindParam("username", $username);
		$sth->bindParam("email", $email);
		$sth->execute();
		$user = $sth->fetchObject();

		$errors = array();
		if(!empty($user)){
			$status = 400;
			if($user->email == $email){
				$errors["error"] = "Email already exists";
				return $this->app->response->withJson($errors, $status);
			}
			if($user->username == $username){
				$errors["error"] = "Username already exists";
				return $this->app->response->withJson($errors, $status);
			}
		} 

		$query = "INSERT INTO User (fName, lName, email, username, password, created) VALUES (:fName, :lName, :email, :username, :password, NOW())";
		
		$sth = $this->app->db->prepare($query);
		$sth->bindParam("fName", $fName);
		$sth->bindParam("lName", $lName);
		$sth->bindParam("email", $email);
		$sth->bindParam("username", $username);
		$sth->bindParam("password", $password);
		$sth->execute();

		return $status;

	}

}
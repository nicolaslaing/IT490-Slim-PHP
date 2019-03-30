<?php
namespace app\controller;
use PDO;

class LoginController {

	public function __construct($app){
		$this->app = $app;
		$this->service = $app['LoginService'];
		$this->userService = $app['UserService'];
		$this->rabbitmq = $app['RabbitMQService'];
	}

	public function doLogin($request, $response, $args){

		$status = 200;
		$username = json_decode($request->getBody(), true)['username'];
		$password = json_decode($request->getBody(), true)['password'];

		$data = array('username' => $username, 'password' => $password);
		$publishStatus = $this->rabbitmq->publish("api", $data);

		$data2 = $this->rabbitmq->consume("api", $publishStatus);

		$query = "SELECT id, fName, lName, username, password, email, created FROM User WHERE username=:username";

		$sth = $this->app->db->prepare($query);
		$sth->bindParam("username", $username);
		// $sth->bindParam("password", $password);
		$sth->execute();
		$user = $sth->fetchObject();

		$userId = $user->id;

		// invalid username
		if(empty($user)){
			$status = 400;
		
		// bad password
		} if(!password_verify($password, $user->password)){
			$this->userService->auditLog($userId, "Unsuccessful Login");
			$status = 400;
		
		// successful login
		} else {
			$this->userService->auditLog($userId, "Successful Login");
		}

		unset($user->password); // remove from object since the response can be viewed in plain text

		header("Content-Type: application/json");
		return $this->app->response->withJson($user, $status);

	}

	public function register($request, $response, $args){

		$status = 200;

		$fName = json_decode($request->getBody(), true)['fName'];
		$lName = json_decode($request->getBody(), true)['lName'];
		$email = json_decode($request->getBody(), true)['email'];
		$username = json_decode($request->getBody(), true)['username'];
		$password = password_hash(json_decode($request->getBody(), true)['password'], PASSWORD_BCRYPT);

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

		header("Content-Type: application/json");
		return $this->app->response->withJson($status);

	}

	public function forgotUsername($request, $response, $args){

		$status = 200;
		$email = json_decode($request->getBody(), true)['email'];

		$query = "SELECT username FROM User WHERE email=:email";

		$sth = $this->app->db->prepare($query);
		$sth->bindParam("email", $email);
		$sth->execute();
		$username = $sth->fetch();

		if(empty($username)){
			$status = 400;
			$username = "Email does not exist";
		}

		header("Content-Type: application/json");
		return $this->app->response->withJson($username, $status);

	}

	public function forgotPassword($request, $response, $args){

		$status = 200;
		$username = json_decode($request->getBody(), true)['username'];

		$query = "SELECT id, username FROM User WHERE username=:username";

		$sth = $this->app->db->prepare($query);
		$sth->bindParam("username", $username);
		$sth->execute();
		$usernameObj = $sth->fetchAll();

		if(empty($usernameObj)) {
			$status = 400;
			$usernameObj = "Username does not exist";
		}

		header("Content-Type: application/json");
		return $this->app->response->withJson($usernameObj, $status);

	}

	public function resetPassword($request, $response, $args){

		$status = 200;
		$userId = json_decode($request->getBody(), true)['id'];
		$username = json_decode($request->getBody(), true)['username'];
		$password = password_hash(json_decode($request->getBody(), true)['password'], PASSWORD_BCRYPT);

		$query = "UPDATE User SET password=:password WHERE username=:username";

		$sth = $this->app->db->prepare($query);
		$sth->bindParam("username", $username);
		$sth->bindParam("password", $password);
		$sth->execute();

		$this->userService->auditLog($userId, "Password Reset");

		header("Content-Type: application/json");
		return $this->app->response->withJson($status);

	}

}
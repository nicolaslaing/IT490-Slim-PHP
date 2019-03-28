<?php
namespace app\controller;
use PDO;

class LoginController {

	public function __construct($app){
		$this->app = $app;
		$this->service = $app['LoginService'];
		$this->rabbitmq = $app['RabbitMQService'];
	}

	public function doLogin($request){

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

		$userid = $user->id;

		// invalid username
		if(empty($user)){
			$status = 400;
		
		// bad password
		} if(!password_verify($password, $user->password)){
			$query = "INSERT INTO User_log (`User_id`, `Action_id`, `timestamp`) VALUES (:userid, 'Unsuccessful login', NOW())";

			$sth = $this->app->db->prepare($query);
			$sth->bindParam("userid", $userid);
			$sth->execute();
			$status = 400;
		
		// successful login
		} else {
			$query = "INSERT INTO User_log (`User_id`, `Action_id`, `timestamp`) VALUES (:userid, 'Logged in', NOW())";

			$sth = $this->app->db->prepare($query);
			$sth->bindParam("userid", $userid);
			$sth->execute();
		}
		unset($user->password);
		return $this->app->response->withJson($user, $status);

	}

	public function register($request){

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

		return $status;

	}

	public function forgotUsername($request){

	}

	public function forgotPassword($request){

	}

}
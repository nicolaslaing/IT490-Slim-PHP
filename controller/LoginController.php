<?php
namespace app\controller;
use PDO;

class LoginController {

	public function __construct($app){
		$this->app = $app;
		$this->service = $app['LoginService'];
	}

	public function consume($queue){
		// GET
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://0.0.0.0:5000/consume/' . $queue);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		$data = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return ($httpcode>=200 && $httpcode<300) ? $data : false;
	}

	public function publish($queue, $data){
		// POST
		$url = 'http://0.0.0.0:5000/publish/' . $queue;

		// use key 'http' even if you send the request to https://...
		$options = array(
		    'http' => array(
		        'method'  => 'POST',
		        'content' => json_encode( $data ),
    			'header'=>  "Content-Type: application/json\r\n" .
                			"Accept: application/json\r\n"
		    )
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);

		/* Handle error */
		if ($result === FALSE) { 
			print_r("ERROR Publish");
		}
	}

	public function doLogin($request){

		$status = 200;
		$username = json_decode($request->getBody(), true)['username'];
		$password = json_decode($request->getBody(), true)['password'];

		$data = array('username' => $username, 'password' => $password);
		$this->publish("api", $data);

		$data2 = $this->consume("api");

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

		$query = "INSERT INTO User_log (`User_id`, `Action_id`, `timestamp`) VALUES (:userid, 'Logged in', NOW())";

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

	public function forgotUsername($request){

	}

	public function forgotPassword($request){

	}

}
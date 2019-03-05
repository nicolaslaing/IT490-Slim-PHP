<?php
namespace app\controller;
use PDO;

class UserController {

    public function __construct($app){
        $this->app = $app;
    }
    
    public function auditLog($request){

		$userid = json_decode($request->getBody(), true)['userid'];

		$query = "INSERT INTO User_log (`User_id`, `Action`, `timestamp`) VALUES (:userid, 'Logged in', NOW())";

		$sth = $this->app->db->prepare($query);
		$sth->bindParam("userid", $userid);
		$sth->execute();

    	return 200;

    }

}
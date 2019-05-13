<?php
namespace app\src\services;
use PDO;

class UserService {

    public function __construct($app){
        $this->app = $app;
    }
    
    public function auditLog($userId, $actionId){

        $query = "INSERT INTO User_log (`User_id`, `Action_id`, `timestamp`) VALUES (:userid, :actionid, NOW())";

        $sth = $this->app->db->prepare($query);
        $sth->bindParam("userid", $userId);
        $sth->bindParam("actionid", $actionId);
        $sth->execute();

        return 200;

    }
    
}
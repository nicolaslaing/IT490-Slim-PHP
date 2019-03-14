<?php
namespace app\controller;
use PDO;

class UserController {

    public function __construct($app){
        $this->app = $app;
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
    
    public function auditLog($request){

		$userid = json_decode($request->getBody(), true)['userid'];

		$query = "INSERT INTO User_log (`User_id`, `Action_id`, `timestamp`) VALUES (:userid, 'Logged in', NOW())";

		$sth = $this->app->db->prepare($query);
		$sth->bindParam("userid", $userid);
		$sth->execute();

    	return 200;

    }

}
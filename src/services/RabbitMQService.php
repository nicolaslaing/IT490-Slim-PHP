<?php
namespace app\src\services;
use PDO;

class RabbitMQService {

	public function __construct($app){
		$this->app = $app;
	}

	public function consume($queue, $publishStatus){
		// GET
		if($publishStatus == 200){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://0.0.0.0:5000/consume/' . $queue);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			$data = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			return ($httpcode>=200 && $httpcode<300) ? $data : $httpcode;
		} else {
			return 400;
		}
	}

	public function publish($queue, $data){
		// POST
		$url = 'http://0.0.0.0:5000/publish/' . $queue;
		$status = 200;
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
			$status = 400;
		}

		return $status;
	}

}
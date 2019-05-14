<?php
namespace app\controller;
use PDO;

class SongController {

    public function __construct($app){
        $this->app = $app;
        $this->rabbitmq = $app['RabbitMQService'];
    }
    public function search($request){
        $status = 200;
		$entityType = json_decode($request->getBody(), true)['entityType'];
        $params = json_decode($request->getBody(), true)['params'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://secondhandsongs.com/search/'.$entityType.'?format=json&'.$params);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($httpcode>=200 && $httpcode<300) ? $data : $httpcode;
    }
    public function artist($request, $response, $args){
        $status = 200;
        $entityId = $args['entityId'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://secondhandsongs.com/artist/'.$entityId.'?format=json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($httpcode>=200 && $httpcode<300) ? $data : $httpcode;
    }
    // See this StackOverflow thread for why the API must be called from the backend, not the front end:
    // https://stackoverflow.com/questions/46771352/no-access-control-allow-origin-for-public-api-request
    public function callAPI($request)
    {

        // Temporary variables to prove the API works
        $method = "GET";
        $url = "https://secondhandsongs.com/artist/123/performances?format=json";
        $data = false;

        $curl = curl_init();

        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
    
}
<?php
require_once('./vendor/autoload.php');  

// Namespace 
use \LINE\LINEBot\HTTPClient\CurlHTTPClient; 
use \LINE\LINEBot; 
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;  
$channel_token = 'G+HFk/HAQbXgJEmWSl1xzj89ii0y8lKusJD2ZiU1Y2mTKSg3s9zFs8PybOFb0tzHO2EGjavJWT/oYHwbgQ2rl/k8caRgJexhXnLu0d8//4wZ5ZRLzU7pNcKNNoCPlm2F+TIYuBuvhQjJqgLCsnpWjQdB04t89/1O/w1cDnyilFU='; 
$channel_secret = '50c426fafb4c142a2a88b4e182ea9089';  

//Get message from Line API 
$content = file_get_contents('php://input'); 
$events = json_decode($content, true);  
if (!is_null($events['events'])) 
{      
        // Loop through each event     
        foreach ($events['events'] as $event) {          
            // Line API send a lot of event type, we interested in message only.         
            if ($event['type'] == 'message' && $event['message']['type'] == 'text') {              
                // Get replyToken             
                $replyToken = $event['replyToken'];              
                // Split message then keep it in database.             
                $appointments = explode(',', $event['message']['text']);              
                if(count($appointments) == 2) {                  
                    $host = 'ec2-54-235-150-134.compute-1.amazonaws.com';                 
                    $dbname = 'd7f7fte41bha85';                 
                    $user = 'qohytdhrfarzbh'; 
                    $pass = 'eeaa9a12fe9a15603cd4ada2e97b443475c79d628a8437183a5a06c017070736';                 
                    $connection = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);                  
                    $params = array(                     
                        'time' => $appointments[0],                     
                        'content' => $appointments[1],                 
                    );                  
                    $statement = $connection->prepare("INSERT INTO appointments (time, content) VALUES (:time, :content)");                 
                    $result = $statement->execute($params);                 
                    $respMessage = 'Your appointment has saved.';             
                }else{                 
                    $respMessage = 'You can send appointment like this "12.00,House keeping." ';             
                }              
                $httpClient = new CurlHTTPClient($channel_token);             
                $bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret));              
                $textMessageBuilder = new TextMessageBuilder($respMessage);             
                $response = $bot->replyMessage($replyToken, $textMessageBuilder);          
            }     
        } 
} 

echo "OK"; 

?>
<?php
require_once('./vendor/autoload.php');  

// Namespace 
use \LINE\LINEBot\HTTPClient\CurlHTTPClient; 
use \LINE\LINEBot; 
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;  
$channel_token = 'G+HFk/HAQbXgJEmWSl1xzj89ii0y8lKusJD2ZiU1Y2mTKSg3s9zFs8PybOFb0tzHO2EGjavJWT/oYHwbgQ2rl/k8caRgJexhXnLu0d8//4wZ5ZRLzU7pNcKNNoCPlm2F+TIYuBuvhQjJqgLCsnpWjQdB04t89/1O/w1cDnyilFU='; 
$channel_secret = '50c426fafb4c142a2a88b4e182ea9089';  

// Create bot
$httpClient = new CurlHTTPClient($channel_token);
$bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret));

// Database connection 
$host = 'ec2-54-235-150-134.compute-1.amazonaws.com';                 
$dbname = 'd7f7fte41bha85';                  
$user = 'qohytdhrfarzbh';
$pass = 'eeaa9a12fe9a15603cd4ada2e97b443475c79d628a8437183a5a06c017070736';
$connection = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass); 

// Get message from Line API
$content = file_get_contents('php://input');
$events = json_decode($content, true);
 
if (!is_null($events['events'])) {
	// Loop through each event
	foreach ($events['events'] as $event) {
    
        // Line API send a lot of event type, we interested in message only.
		if ($event['type'] == 'message') {
            switch($event['message']['type']) {
                case 'text':
                    $sql = sprintf("SELECT * FROM slips WHERE slip_date='%s' AND user_id='%s' ", date('Y-m-d'),$event['source']['userId']);
                    $result = $connection->query($sql);
                    if($result !== false && $result->rowCount() >0) {
                        // Save database
                        $params = array(
                            'name' => $event['message']['text'],
                            'slip_date' => date('Y-m-d'),
                            'user_id' => $event['source']['userId'],
                        );
                        $statement += $connection->prepare('UPDATE slips SET name=:name WHERE slip_date=:slip_date AND user_id=:user_id'); 
                        $effect = $statement->execute($params);
                    } else {
                        $params = array(
                            'user_id' => $event['source']['userId'] ,
                            'slip_date' => date('Y-m-d'),
                            'name' => $event['message']['text'],
                        );
                        $statement = $connection->prepare('INSERT INTO slips (user_id, slip_date, name) VALUES (:user_id, :slip_date, :name)');
                         
                        $effect = $statement->execute($params);
                    }
                    // Bot response 
                    $respMessage = $effect;
                    $replyToken = $event['replyToken'];
                    $textMessageBuilder = new TextMessageBuilder($respMessage);
                    $response = $bot->replyMessage($replyToken, $textMessageBuilder);
                    break;
                case 'image':
                    // Get file content.
                    $fileID = $event['message']['id'];
                    
                    $response = $bot->getMessageContent($fileID);
                    $fileName = md5(date('Y-m-d')).'.jpg';
                    
                    if ($response->isSucceeded()) {
                        // Create file.
                        $file = fopen($fileName, 'w');
                        fwrite($file, $response->getRawBody());
                        $sql = sprintf(
                                    "SELECT * FROM slips WHERE slip_date='%s' AND user_id='%s' ", 
                                    date('Y-m-d'),
                                    $event['source']['userId']);
                        $result = $connection->query($sql);
                        if($result !== false && $result->rowCount() >0) {
                            // Save database
                            $params = array(
                                'image' => $fileName,
                                'slip_date' => date('Y-m-d'),
                                'user_id' => $event['source']['userId'],
                            );
                            $statement = $connection->prepare('UPDATE slips SET image=:image WHERE slip_date=:slip_date AND user_id=:user_id');
                            $statement->execute($params);
                            
                        } else {
                            $params = array(
                                'user_id' => $event['source']['userId'] ,
                                'image' => $fileName,
                                'slip_date' => date('Y-m-d'),
                            );
                            $statement = $connection->prepare('INSERT INTO slips (user_id, image, slip_date) VALUES (:user_id, :image, :slip_date)');
                            $statement->execute($params);
                        }
                    }
                    // Bot response 
                    $respMessage = 'Your data has saved.';
                    $replyToken = $event['replyToken'];
                    $textMessageBuilder = new TextMessageBuilder($respMessage);
                    $response = $bot->replyMessage($replyToken, $textMessageBuilder);
                    
                    break; 
            }
		}
	}
}
echo "OK";
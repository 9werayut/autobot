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
                try { 
                    // Check to see user already answer                
                    $host = 'ec2-54-235-150-134.compute-1.amazonaws.com';                 
                    $dbname = 'd7f7fte41bha85';                 
                    $user = 'qohytdhrfarzbh'; 
                    $pass = 'eeaa9a12fe9a15603cd4ada2e97b443475c79d628a8437183a5a06c017070736';                 
                    $connection = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);                  
                    
                    $sql = sprintf("SELECT * FROM poll WHERE user_id='%s' ", $event['source']['userId']);
                    $result = $connection->query($sql);                  
                    error_log($sql); 

                    if($result == false || $result->rowCount() <=0) {                      
                        switch($event['message']['text']) {                          
                            case '1':                             
                                // Insert                             
                                $params = array(                                 
                                    'userID' => $event['source']['userId'],                                 
                                    'answer' => '1',                             
                                );                              
                                $statement = $connection->prepare('INSERT INTO poll ( user_id, answer ) VALUES ( :userID, :answer )');                             
                                $statement->execute($params);                              
                                // Query                             
                                $sql = sprintf("SELECT * FROM poll WHERE answer='1' AND  user_id='%s' ", $event['source']['userId']);                             
                                $result = $connection->query($sql);                              
                                $amount = 1;                             
                                if($result){                                 
                                    $amount = $result->rowCount();                             
                                }                             
                                $respMessage = 'จ ำนวนคนตอบว่ำเพื่อน = '.$amount;
                            break;                          
                            case '2':                             
                                // Insert                             
                                $params = array(                                 
                                    'userID' => $event['source']['userId'],                                 
                                    'answer' => '2',                             
                                );                              
                                $statement = $connection->prepare('INSERT INTO poll ( user_id, answer ) VALUES ( :userID, :answer )');                             
                                $statement->execute($params); 

                                // Query                             
                                $sql = sprintf("SELECT * FROM poll WHERE answer='2' AND  user_id='%s' ", $event['source']['userId']);                             
                                $result = $connection->query($sql);                              
                                $amount = 1;                             
                                if($result){                                 
                                    $amount = $result->rowCount();                             
                                }                             
                                $respMessage = 'จ ำนวนคนตอบว่ำแฟน = '.$amount;                              
                            break;                          
                            case '3':                             
                                // Insert                             
                                $params = array(                                 
                                    'userID' => $event['source']['userId'],                                 
                                    'answer' => '3',                            
                                );                              
                                $statement = $connection->prepare('INSERT INTO poll ( user_id, answer ) VALUES ( :userID, :answer )');
                                $statement->execute($params);                              
                                // Query                             
                                $sql = sprintf("SELECT * FROM poll WHERE answer='3' AND  user_id='%s' ", $event['source']['userId']);                             
                                $result = $connection->query($sql);                              
                                $amount = 1;                             
                                if($result){                                 
                                    $amount = $result->rowCount();                             
                                }                             
                                $respMessage = 'จ ำนวนคนตอบว่ำพ่อแม่ = '.$amount;                              
                            break;                         
                            case '4':                             
                                // Insert                             
                                $params = array(                                 
                                    'userID' => $event['source']['userId'],                                 
                                    'answer' => '4',                             
                                ); 
                                $statement = $connection->prepare('INSERT INTO poll ( user_id, answer ) VALUES ( :userID, :answer )');                             
                                $statement->execute($params);                              
                                // Query                             
                                $sql = sprintf("SELECT * FROM poll WHERE answer='4' AND  user_id='%s' ", $event['source']['userId']);                             
                                $result = $connection->query($sql);                              
                                $amount = 1;                             
                                if($result){                                 
                                    $amount = $result->rowCount();                             
                                }                             
                                $respMessage = 'จ ำนวนคนตอบว่ำบุคคลอื่นๆ = '.$amount;                              
                            break;                         
                                default:                             
                                $respMessage = "บุคคลที่โทรหำบ่อยที่สุด คือ? \n\rกด 1 เพื่อน \n\rกด 2 แฟน \n\rกด 3 พ่อแม่ \n\rกด 4 บุคคลอื่นๆ \n\r";                             
                            break;                     
                        }                  
                    } else {                     
                        $respMessage = 'คุณได้ตอบโพลล์นี้แล้ว';                 
                    }                  
                    $httpClient = new CurlHTTPClient($channel_token);                 
                    $bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret));                  
                    $textMessageBuilder = new TextMessageBuilder($respMessage);                 
                    $response = $bot->replyMessage($replyToken, $textMessageBuilder);              
                } catch(Exception $e) {                 
                    error_log($e->getMessage());             
                } 
            }     
        } 
} 

echo "OK"; 

?>
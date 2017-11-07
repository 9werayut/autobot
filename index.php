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
    //Line API send a lot of event type, we interested in message only.         
    if ($event['type'] == 'message' && $event['message']['type'] == 'text') {              
        // Get replyToken             
        $replyToken = $event['replyToken'];              
        switch($event['message']['text']) {                  
            case 'tel':                     
                $respMessage = '089-5124512';                     
            break;                 
            case 'address':                     
                $respMessage = '99/451 Muang Nonthaburi';                     
            break;                 
            case 'boss':                     
                $respMessage = '089-2541545';                     
            break;                 
            case 'idcard':                     
                $respMessage = '5845122451245';                     
            break;                 
            default:                     
            break;             
        }              
        $httpClient = new CurlHTTPClient($channel_token);             
        $bot = new LINEBot($httpClient, array('channelSecret' => $channel_secret));              
        $textMessageBuilder = new TextMessageBuilder($respMessage);             
        $response = $bot->replyMessage($replyToken, $textMessageBuilder);          } 
} 

echo "OK"; 

?>
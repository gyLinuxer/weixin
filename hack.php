<?php
/**
 * Created by PhpStorm.
 * User: gylinuxer
 * Date: 16-9-29
 * Time: 上午11:31
 */

include_once "php/WXBizMsgCrypt.php";
/*
 *   'USER' => string 'www-data' (length=8)
  'HOME' => string '/var/www' (length=8)
  'FCGI_ROLE' => string 'RESPONDER' (length=9)
  'SCRIPT_FILENAME' => string '/var/www/gyHack/hack.php' (length=24)
  'QUERY_STRING' => string 'x=asdfdasf&d=asdfeaweawfe' (length=25)
  'REQUEST_METHOD' => string 'GET' (length=3)
  'CONTENT_TYPE' => string '' (length=0)
  'CONTENT_LENGTH' => string '' (length=0)
  'SCRIPT_NAME' => string '/hack.php' (length=9)
  'REQUEST_URI' => string '/?x=asdfdasf&d=asdfeaweawfe' (length=27)
  'DOCUMENT_URI' => string '/hack.php' (length=9)
  'DOCUMENT_ROOT' => string '/var/www/gyHack' (length=15)
  'SERVER_PROTOCOL' => string 'HTTP/1.1' (length=8)
  'REQUEST_SCHEME' => string 'http' (length=4)
  'GATEWAY_INTERFACE' => string 'CGI/1.1' (length=7)
  'SERVER_SOFTWARE' => string 'gyLinuxer' (length=9)
  'REMOTE_ADDR' => string '127.0.0.1' (length=9)
  'REMOTE_PORT' => string '52212' (length=5)
  'SERVER_ADDR' => string '127.0.0.1' (length=9)
  'SERVER_PORT' => string '8080' (length=4)
  'SERVER_NAME' => string '' (length=0)
  'REDIRECT_STATUS' => string '200' (length=3)
  'HTTP_HOST' => string 'hack.ngrok.cc' (length=13)
  'HTTP_CONNECTION' => string 'keep-alive' (length=10)
  'HTTP_UPGRADE_INSECURE_REQUESTS' => string '1' (length=1)
  'HTTP_USER_AGENT' => string 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/52.0.2743.116 Chrome/52.0.2743.116 Safari/537.36' (length=135)
  'HTTP_ACCEPT_ENCODING' => string 'gzip, deflate, sdch' (length=19)
  'HTTP_ACCEPT_LANGUAGE' => string 'en-US,en;q=0.8' (length=14)
  'PHP_SELF' => string '/hack.php' (length=9)
  'REQUEST_TIME_FLOAT' => float 1475120860.8217
  'REQUEST_TIME' => int 1475120860

 $sReqMsgSig = HttpUtils.ParseUrl("msg_signature");
 $sReqTimeStamp = HttpUtils.ParseUrl("timestamp");

 $sReqNonce = HttpUtils.ParseUrl("nonce");

*/
$encodingAesKey = "8dxRqOgz3vTdnQZVNkywdH07QKAB8G14eWCkQuJabdB";
$token = "C2Ce9Mtwe2WuAZuAmr3Ls1PlGUBbIW";
$corpId ="wx7cd0a95d75d7d7cc"; //这里已正确填写自己的corpid
//公众号服务器数据
$sReqMsgSig = $sVerifyMsgSig = $_GET['msg_signature'];
$sReqTimeStamp = $sVerifyTimeStamp = $_GET['timestamp'];
$sReqNonce = $sVerifyNonce = $_GET['nonce'];
$sVerifyEchoStr = $_GET['echostr'];
$sReqData = file_get_contents("php://input");;
$wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);
if($sVerifyEchoStr){
    $sEchoStr = "";
    $errCode = $wxcpt->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);
    if ($errCode == 0) {
        print($sEchoStr);
    } else {
        print($errCode . "\n\n");
    }
    exit;
}

file_put_contents("log",var_dump($_POST));

$sMsg = "";  //解析之后的明文
$errCode = $wxcpt->DecryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);
if ($errCode == 0) {
    $xml = new DOMDocument();
    $xml->loadXML($sMsg);
    $reqToUserName = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;
    $reqFromUserName = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue;
    $reqCreateTime = $xml->getElementsByTagName('CreateTime')->item(0)->nodeValue;
    $reqMsgType = $xml->getElementsByTagName('MsgType')->item(0)->nodeValue;
    $reqContent = $xml->getElementsByTagName('Content')->item(0)->nodeValue;
    $reqMsgId = $xml->getElementsByTagName('MsgId')->item(0)->nodeValue;
    $reqAgentID = $xml->getElementsByTagName('AgentID')->item(0)->nodeValue;
    switch($reqContent){
        case "李莎":
            $mycontent="给你一张9999999999999999999999999999999999元的支票，随便花！";
            break;
        case "李光耀":
            $mycontent="gyLinuxer!!";
            break;
        case "tmp":
            sscanf(file_get_contents("/proc/acpi/ibm/thermal"),"temperatures:	%u",$wd);
            $mycontent="CPU温度:".$wd;
            break;
        default :
            $mycontent=shell_exec("$reqContent 2>&1");
            break;
    }
    $sRespData =
        "<xml>  
<ToUserName><![CDATA[".$reqFromUserName."]]></ToUserName>  
<FromUserName><![CDATA[".$corpId."]]></FromUserName>  
<CreateTime>".sReqTimeStamp."</CreateTime>  
<MsgType><![CDATA[$reqMsgType]]></MsgType>  
<Content><![CDATA[".$mycontent."]]></Content>  
</xml>";
    $sEncryptMsg = ""; //xml格式的密文
    $errCode = $wxcpt->EncryptMsg($sRespData, $sReqTimeStamp, $sReqNonce, $sEncryptMsg);
    if ($errCode == 0) {
//file_put_contents('smg_response.txt', $sEncryptMsg); //debug:查看smg
        print($sEncryptMsg);
    } else {
        print($errCode . "\n\n");
    }
} else {
    print($errCode . "\n\n");
}

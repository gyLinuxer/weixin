<form method="post">
<input name="cmd"/>
<input type="submit" name="submit" value="Send"/>

</form>

<?php
/**
 * Created by PhpStorm.
 * User: gylinuxer
 * Date: 16-9-20
 * Time: 上午10:53
 */


if($_POST["submit"]){
    $message_queue = msg_get_queue(0x77778888, 0666);
    var_dump($message_queue);

    $message_queue_status = msg_stat_queue($message_queue);
    print_r($message_queue_status);

//向消息队列中写
    msg_send($message_queue, 1, $_POST["cmd"],false);

    $message_queue_status = msg_stat_queue($message_queue);
    print_r($message_queue_status);


}



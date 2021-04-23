<?php

use \App\Models\Notification;

function sendNotification($id,$message){
    $notification = new Notification;
    $notification->receiver_id = $id;
    $notification->message = $message;
    $notification->save();
}

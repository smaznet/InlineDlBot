<?php
/**
 * Created by PhpStorm.
 * User: smaznet
 * Date: 5/29/17
 * Time: 10:49 PM
 */

$update=json_decode(file_get_contents("php://input"));
$token="YourBotToken";
$thisFileUrl="/bot.php"; //SET YOUR URL
$FilesCh="destCh"; // enter a channel to download files without @

require ("core/telegramhelper.php");
require ("core/InlineKeyBoardMarkUp.php");
require ("core/InlineKeyBoardItem.php");

function buidProgress($step, $allSteps = 5,$upload=false)
{

    $progressChars = 15;
    if ($allSteps==0){
        $allSteps=1;
        $step=1;
    }
    $progressVal = intval(($step / $allSteps) * $progressChars);
    $progress = str_repeat("=", $progressVal);
    $spacess = str_repeat("=", $progressChars - $progressVal);
    if ($upload){
        $Converting = "Uploading ...";
    }else{
        $Converting = "Downloading ...";
    }
if ($allSteps==1&&$step==1){
 $Secs="Wait";
}else {
    $step = show_filesize($step);
    $allSteps = show_filesize($allSteps);
    $Secs = "$step of $allSteps";
}
    /*if ($ULang == 'fa') {
        $Secs = ($allSteps - $step) . " ثانیه";
        $Secs = Utils::toPersianNums($Secs);
    }*/
    return ("$Converting \n <pre>|$progress".'->' . "$spacess|</pre> \n" . $Secs);
}
function downloadFile($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
//curl_setopt($ch, CURLOPT_BUFFERSIZE,128);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'progress');
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
    curl_setopt($ch, CURLOPT_NOPROGRESS, false); 
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
}
function show_filesize($kb){
    $kb  = ceil($kb);
    if( $kb < 1024 ) {
        return $kb.'KB';
    }
    $mb  = round($kb/1024,1);
    return $mb.'MB';
}
$curd=-1;
$curu=-1;
function progress($resource,$download_size, $downloaded, $upload_size, $uploaded)
{
    global $Tl,$messageId,$fromUsetID,$curd,$curu;
if ($upload_size>100){
    if ($uploaded % 100 == 0) {

$tmp=intval(($uploaded / ($upload_size+1)) * 15);
if ($tmp==$curu){


    return;
}
$curu=$tmp;
        $res = $Tl->makeHTTPRequest("editMessageText", ['inline_message_id' => $messageId
            , 'text' => buidProgress($uploaded/1024,$upload_size/1024,true), 'parse_mode' => 'Html','reply_markup'=>
json_encode(
                InlineKeyBoardMarkUp::build(false,[[
                    InlineKeyBoardItem::build("کنسل",null,"del$fromUsetID")
                ]]))]);

        if (!$res['ok']){
            //  $Tl->sendToMe(json_encode($res));
            if (strpos($res['description'],"MESSAGE_ID_INVALID")!==false){
                curl_close($resource);
                exit(0);
            }
        }
    }

}else {


if ($downloaded%100!=0){
    return;
}

    $tmp=intval(($downloaded / ($download_size+1)) * 15);
    if ($tmp==$curd){


        return;
    }
    $curd=$tmp;
        $res = $Tl->makeHTTPRequest("editMessageText", ['inline_message_id' => $messageId
            , 'text' => buidProgress($downloaded/1024,$download_size/1024), 'parse_mode' => 'Html','reply_markup'=>
json_encode(
        InlineKeyBoardMarkUp::build(false,[[
            InlineKeyBoardItem::build("کنسل",null,"del$fromUsetID")
        ]]))]);

        if (!$res['ok']){
            if (strpos($res['description'],"MESSAGE_ID_INVALID")!==false){
                curl_close($resource);
                exit(0);
            }
          $Tl->sendToMe(json_encode($res));
        }

}
}
$Tl=new telegramhelper($token);

if (isset($_GET['q'])){
    set_time_limit(0);
    $query=urldecode($_GET['q']);
    $messageId=$_GET['m'];
    $fromUsetID=$_GET['f'];
    $res=$Tl->sendMediaByContent("Document",downloadFile($query),['chat_id'=>"@"."$FilesCh"]
        ,basename($query),true);
    if ($res['ok']){
        $fileUrl="https://t.me/$FilesCh/".$res['result']['message_id'];
        $res= $Tl->makeHTTPRequest("editMessageText",['inline_message_id'=>$messageId
            ,'text'=>"File : \n<a href='$fileUrl'>&#160;</a>",'parse_mode'=>'Html']);

       // $Tl->sendToMe(json_encode($res));
    }else{
        $res= $Tl->makeHTTPRequest("editMessageText",['inline_message_id'=>$messageId
            ,'text'=>"نشد :|",'parse_mode'=>'Html']);
    }
}
// $Tl->sendToMe(json_encode($update));
if (isset($update->chosen_inline_result)){
    // $Tl->sendToMe("Chossed ");
$inLien=$update->chosen_inline_result;
$messageId=$inLien->inline_message_id;
$query=$inLien->query;
    $request = "$thisFileUrl?q=".urlencode($query)."&m=".$messageId."&f=".$inLien->from->id;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $request);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_exec($ch);
    curl_close($ch);
}
if (false){
// chanege it fo false set webhook url on $thisFileUrl 

// and chanege it to true only for first time
    $Tl->makeHTTPRequest("setWebHook",['url'=>'$thisFileUrl']);
}
if (isset($update->inline_query)){
    $InId=$update->inline_query->id;
   $FromId= $update->inline_query->from->id;
    if ($FromId==129377043||$FromId==156108793){
        $url=$update->inline_query->query;
        $resId=md5($url);
       $res= $Tl->makeHTTPRequest("answerInlineQuery",['inline_query_id'=>$InId,'is_personal'=>true,'results'=>json_encode([
            ['type'=>'article','id'=>$resId,'title'=>'Click To Send','reply_markup'=>InlineKeyBoardMarkUp::build(false,
                [
                    [
                    InlineKeyBoardItem::build("Wait",null,"okDl")
                    ]
                ]
            ),'input_message_content'=>[
                'message_text'=>'Wait ...'
            ]]
        ])]);
       //$Tl->sendToMe(json_encode($res));
    }else{
        $Tl->makeHTTPRequest("answerInlineQuery",['inline_query_id'=>$InId,'is_personal'=>true,
            'results'=>json_encode([])]);
    }
}
if (isset($update->callback_query)){
    $CQ=$update->callback_query;
    $fromId=$CQ->from->id;
    if ($CQ->data=="del$fromId"){
        $messageId=$CQ->inline_message_id;
        $Tl->answerCallbackQuery($CQ->id,"پیامی که با من فرستادی رو پاک کن :|",true);
    }else{
        $Tl->answerCallbackQuery($CQ->id,"باید اونی که زده این دکمه رو بزنه :|",true);
    }
}

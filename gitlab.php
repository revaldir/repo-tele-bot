<?php
$token	 = getenv('gitlab_token_default');
$chat_id = getenv('gitlab_ch_default');

if(isset($_GET['credential'])){
	if($_GET['credential']==1){
		$token	 = getenv('gitlab_token_default');
		$chat_id = getenv('gitlab_ch_default');
	}
	
	if($_GET['credential']==2){
		$token	 = getenv('ctm_ch_key');
		$chat_id = getenv('ctm_ch');
	}
}


$url = "https://api.telegram.org/bot".$token."/sendMessage";

$get_message = json_decode(file_get_contents('php://input'), true);

if(is_array($get_message)){

	$act		= isset($get_message['object_kind'])?$get_message['object_kind']:"Git Act";
	$from		= isset($get_message['user_username'])?$get_message['user_username']:"person";
	$project	= isset($get_message['project']['path_with_namespace'])?$get_message['project']['path_with_namespace']:"";
	
	if($act=='issue' || $act=='note')
	{
		$from			= isset($get_message['user']['name'])?$get_message['user']['name']:"person";
		if($act=='note'){
			$title			= isset($get_message['issue']['title'])?$get_message['issue']['title']:"";
			$description	= isset($get_message['issue']['note'])?$get_message['issue']['note']:"";
			$state			= isset($get_message['issue']['state'])?$get_message['issue']['state']:"";
			$action			= isset($get_message['issue']['action'])?$get_message['issue']['action']:"";
			$due_date		= isset($get_message['issue']['due_date'])?$get_message['issue']['due_date']:"";
		}else{
			$title			= isset($get_message['object_attributes']['title'])?$get_message['object_attributes']['title']:"";
			$description	= isset($get_message['object_attributes']['description'])?$get_message['object_attributes']['description']:"";
			$state			= isset($get_message['object_attributes']['state'])?$get_message['object_attributes']['state']:"";
			$action			= isset($get_message['object_attributes']['action'])?$get_message['object_attributes']['action']:"";
			$due_date		= isset($get_message['object_attributes']['due_date'])?$get_message['object_attributes']['due_date']:"";
		}
		
		$symbol		= $state=="opened"?"🟢":"\xF0\x9F\x94\xB4";

		$feedback	= "Action: <b>$act</b>\n\n";
		$feedback	.= "From: <i>$from</i>\n\n";
		$feedback	.= "ON:\n<i>$project</i>\n\n";
		$feedback	.= "Title:\n";
		$feedback	.= "$title\n\n";
		$feedback	.= "Description:\n";
		$feedback	.= "$description\n\n";
		$feedback	.= "State: ";
		$feedback	.= "<i>$state</i>\n";
		if( $act!='note' ){
			$feedback	.= "Action: ";
			$feedback	.= "<b>$action</b> $symbol\n";
		}
		$feedback	.= "Due Date: ";
		$feedback	.= "$due_date";

		if($act=='note'){
			$note		= isset($get_message['object_attributes']['description'])?$get_message['object_attributes']['description']:"";
			$feedback	.= "\n\n";
			$feedback	.= "Note:\n";
			$feedback	.= "<i>$note</i>";
		}
		

	}else{
		$feedback	= "Action:\n<b>$act</b>\n\n";
		$feedback	.= "From:\n<i>$from</i>\n\n";
		$feedback	.= "ON:\n<i>$project</i>\n\n";
		$feedback	.= "Commit:\n";
		$count=1;
		foreach($get_message['commits'] as $pr => $pj)
		{
			$commit		= isset($pj['message'])?$pj['message']:"";
			$added		= isset($pj['added'])?count($pj['added']):"0";
			$modified	= isset($pj['modified'])?count($pj['modified']):"0";
			$removed	= isset($pj['removed'])?count($pj['removed']):"0";
	
			$feedback	.= "$count. <code>$commit</code>";
			$feedback	.= "<i>added:</i> $added, <i>modified:</i> $modified, <i>removed:</i> $removed\n\n";
			$count++;
		}
	}

	$postdata = array("chat_id" => $chat_id, "text" =>$feedback, "parse_mode"=>"HTML");
	send($url,$postdata);
	
}


function send($url,$postdata){

	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch,CURLOPT_POST,true);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		
	$curl_exec = curl_exec($ch);
	curl_close($ch);
	echo $curl_exec;
	return $curl_exec;
}

?>

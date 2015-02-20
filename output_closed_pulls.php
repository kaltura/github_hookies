#!/usr/bin/php
<?php
require_once('/opt/git_hooks/github-php-client/client/GitHubClient.php');
require_once('/opt/git_hooks/config.inc');
define ('OUTPUT_FILE','/tmp/closed_pulls.csv');
if ($argc < 2 ){
	echo "\nUsage: ".__FILE__ . "<repo> [org]\n\nif org is not specified the default is 'kaltura'\n";
	exit (1);
}
$repo_name=$argv[1];
if (isset($argv[2])){
	$org=$argv[2];
}else{
	$org='kaltura';
}
$client = new GitHubClient();
$client->setCredentials(GITHUB_USER,GITHUB_PASSWD);
//$client->setDebug(true);
$currpage=1;
while(true){
	$client->setPage($currpage);
	$pullies=$client->pulls->linkRelations($org, $repo_name,'close');
	if(empty($pullies)){
		break;
	}
	$currpage++;
	foreach ($pullies as $pull){
		$number=$pull->getNumber();
		$username=$pull->getUser()->getLogin();
		$user_id=$pull->getUser()->getId();
		call_hook($client,$org,$repo_name,$number,$username,$user_id);
	}
}
echo '"Output saved to '. OUTPUT_FILE."\n";
function call_hook($client,$org,$repo_name,$pull_id,$username,$user_id){
	try{
		$client->setDebug(true);
		$client->orgs->members->responseIfRequesterIsNotAnOrganizationMember($org,$username);
	}catch(exception $e){
		$da_pull=$client->pulls->getSinglePullRequest($org,$repo_name,$pull_id);
		$url=$da_pull->getHtmlUrl();
		$state=$da_pull->getState();
		$created=$da_pull->getCreatedAt();
		$title=$da_pull->getTitle();
		$body=$da_pull->getBody();
		$da_user=$client->users->getSingleUser($username);
		$user_html_url=$da_user->getHtmlUrl();
		$user_display_name=$da_user->getName();
		$user_email=$da_user->getEmail();
		$user_company=$da_user->getCompany();
		$user_location=$da_user->getLocation();
		$user_avatar=$da_user->getAvatarUrl();
		$commits=$client->pulls->listCommitsOnPullRequest($org, $repo_name,$pull_id);
		$state=null;
		foreach($commits as $commit){
			$shas[]=$commit->getSha();
		}
		foreach($shas as $sha){
			$state=$client->repos->statuses->getCombinedStatus($org,$repo_name,$sha)->getState();
		}
		$info_card='';
		if (!empty($user_email)){
			$info_card="Email: $user_email\n";
		}
		if (!empty($user_location)){
			$info_card.="Location: $user_location\n";
		}
		if (!empty($user_company)){
			$info_card.="Company: $user_company\n";
		}
		$created_date = date_create($created);
		error_log("$repo_name,$username,$pull_id,'$title','".date_format($created_date,'r')."'\n",3,OUTPUT_FILE);
	}
}

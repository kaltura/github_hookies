#!/usr/bin/php
<?php
require_once('/opt/git_hooks/github-php-client/client/GitHubClient.php');
require_once('/opt/git_hooks/config.inc');

$org=SQLite3::escapeString($argv[1]);
$repo_name=SQLite3::escapeString($argv[2]);
$issue_id=SQLite3::escapeString($argv[3]);
$username=SQLite3::escapeString($argv[4]);
$user_id=SQLite3::escapeString($argv[5]);
// we will have a custom message for these repos
$exclude_repos=array('nginx-vod-module','nginx-parallel-module','nginx-secure-token-module','platform-install-packages');
if (in_array($repo_name,$exclude_repos)){
	echo "$repo_name is excluded\n";
	return(0);
}
if (file_exists(dirname(__FILE__)."/auto_msgs/$repo_name")){
	$msg=file_get_contents(dirname(__FILE__)."/auto_msgs/$repo_name");
}else{
	$msg=file_get_contents(dirname(__FILE__)."/auto_msgs/generic");
}

$client = new GitHubClient();
//$client->setDebug(true);
$client->setCredentials(GITHUB_USER,GITHUB_PASSWD);
$cla_checked=null;
try{
	$client->orgs->members->responseIfRequesterIsNotAnOrganizationMember($org,$username);

}catch(exception $e){
	$issue=$client->issues->getIssue($org, $repo_name, $issue_id);
	$state=$issue->getState();
	$is_pull_request=$issue->getPullRequest();
	$created=$issue->getCreatedAt();
	$created_date = date_create($created);
	$created_epoch=date_format($created_date, 'U');
// this is Jun 13 2015. When the hook was first put in. Did not want to retro comment.
	if (!isset($is_pull_request) && ($state==='open' && $created_epoch>1434168000)){
		echo "$created_epoch, $state\n";
		$comments=$client->comments->listCommentsOnAnIssue($org, $repo_name, $issue_id);
		foreach ($comments as $comment){
			if($comment->getUser()->getLogin()==='kaltura-hooks'){
				if(strstr($comment->getBody(),'Thank for you reporting an issue and helping improve Kaltura')){
					echo "No need to comment once more\n";
					return(0);
				}
			}	
		}
		$client->comments->createComment($org, $repo_name, $issue_id, "Hi @".$username.",\n$msg");
	}
}


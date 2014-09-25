#!/usr/bin/php
<?php

// an example hook to integrat with this CLA system [https://github.com/kaltura/agent-contrib]
require_once('/opt/git_hooks/github-php-client/client/GitHubClient.php');
require_once('/opt/git_hooks/config.inc');
define ('DBFILE','/opt/cla/db/cla.db');
$db=new SQLite3(DBFILE) or die('Unable to connect to database '. DBFILE);

$org=SQLite3::escapeString($argv[1]);
$repo_name=SQLite3::escapeString($argv[2]);
$pull_id=SQLite3::escapeString($argv[3]);
$username=SQLite3::escapeString($argv[4]);
$user_id=SQLite3::escapeString($argv[5]);

// The URL you'd like to include in the status [see https://developer.github.com/v3/repos/statuses]
// probably that of your CLA system
$url="";
// The message requesting the user to sign, this will appear as a comment in the pull request
$sign_msg="";
$client = new GitHubClient();
$client->setCredentials(GITHUB_USER,GITHUB_PASSWD);
try{
	$client->orgs->members->responseIfRequesterIsNotAnOrganizationMember($org,$username);
}catch(exception $e){
	$commits=$client->pulls->listCommitsOnPullRequest($org, $repo_name,$pull_id);
	$sha=$commits[0]->getSha();
	$query="select name from contribers where github_user='$username' limit 1;";
	$result=$db->query($query);
	if ($db->lastErrorCode()){
	    die ($db->lastErrorCode() . ' '.$db->lastErrorMsg().' :(');
	}
	$res = $result->fetchArray(SQLITE3_ASSOC);
	if(!isset($res['name'])){
		$cla_checked=false;
		$statuses=$client->repos->statuses->listStatusesForSpecificRef($org, $repo_name,$sha);
		foreach ($statuses as $stat){
			if($stat->getContext()==='CLA'){
				$cla_checked=true;
			}
		}
		if (!$cla_checked){
			$client->comments->createComment($org, $repo_name, $pull_id, "Hi @".$username.",\n$sign_msg");
		}
		$client->repos->statuses->createStatus($org, $repo_name, $sha, 'pending',$url,"Pending for $username to sign CLA",'CLA');
	}else{
		$client->repos->statuses->createStatus($org, $repo_name, $sha, 'success',$url,$username. ' signed the CLA, request can be merged.','CLA');
	}
}

$db->close();

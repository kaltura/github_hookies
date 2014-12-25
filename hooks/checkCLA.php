#!/usr/bin/php
<?php
require_once('/opt/git_hooks/github-php-client/client/GitHubClient.php');
require_once('/opt/git_hooks/config.inc');
define ('DBFILE','/opt/cla/db/cla.db');
$db=new SQLite3(DBFILE) or die('Unable to connect to database '. DBFILE);

$org=SQLite3::escapeString($argv[1]);
$repo_name=SQLite3::escapeString($argv[2]);
$pull_id=SQLite3::escapeString($argv[3]);
$username=SQLite3::escapeString($argv[4]);
$user_id=SQLite3::escapeString($argv[5]);
echo "$repo_name, $pull_id, $username, $user_id\n";
$url="https://agentcontribs.kaltura.org";
$sign_msg="Thank you for contributing this pull request!\nPlease sign the Kaltura CLA so we can review and merge your contribution.\nLearn more at http://bit.ly/KalturaContrib";
$client = new GitHubClient();
//$client->setDebug(true);
$client->setCredentials(GITHUB_USER,GITHUB_PASSWD);
$cla_checked=null;
try{
	$client->orgs->members->responseIfRequesterIsNotAnOrganizationMember($org,$username);
}catch(exception $e){
	$commits=$client->pulls->listCommitsOnPullRequest($org, $repo_name,$pull_id);
	foreach($commits as $commit){
		$shas[]=$commit->getSha();
	}
	foreach($shas as $sha){
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
				$myst= $client->repos->statuses->getCombinedStatus($org, $repo_name, $sha);
				$client->repos->statuses->createStatus($org, $repo_name, $sha, 'pending',$url,"Pending for $username to sign CLA",'CLA');
				echo "set $pull_id on $repo_name by $username to pending status\n";
				$cla_checked=true;
			}
		}else{
			$myst= $client->repos->statuses->getCombinedStatus($org, $repo_name, $sha);
			if ($myst->getState()!=='success'){
				$client->repos->statuses->createStatus($org, $repo_name, $sha, 'success',$url,$username. ' signed the CLA, request can be merged.','CLA');
				echo "set $pull_id on $repo_name by $username to success status\n";
			}
		}
	}
}

$db->close();

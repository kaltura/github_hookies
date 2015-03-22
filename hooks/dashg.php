#!/usr/bin/php
<?php
require_once('/opt/git_hooks/github-php-client/client/GitHubClient.php');
require_once('/opt/git_hooks/config.inc');
$org=SQLite3::escapeString($argv[1]);
$repo_name=SQLite3::escapeString($argv[2]);
$pull_id=SQLite3::escapeString($argv[3]);
$username=SQLite3::escapeString($argv[4]);
$user_id=SQLite3::escapeString($argv[5]);
$client = new GitHubClient();
$client->setCredentials(GITHUB_USER,GITHUB_PASSWD);
try{
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
	if ($state==='success'){
		$created_date = date_create($created);
		$html_state_string="<font color=\"#76D55C\"><b>$state</b></font>";
	error_log("
		<tr>
			<td title=\"$info_card\"><a href=\"$user_html_url\"><img src=\"$user_avatar\" height=50 width=50></a></td>
			<td title=\"$info_card\"><a href=\"$user_html_url\">$username; $user_display_name</a></td>
			<td>$repo_name</td>
			<td title=\"$body\"><a href=\"$url\">$title</a></td>
			<td>".date_format($created_date,'r')."</td>
		</tr>
	",3,'/opt/dashg/data.html');
	}elseif($state==='pending'){
		$html_state_string="<font color=\"#D59B5C\"><b>$state</b></font>";
	}
}


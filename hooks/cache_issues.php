#!/usr/bin/php
<?php
require_once('/opt/git_hooks/github-php-client/client/GitHubClient.php');
require_once('/opt/git_hooks/config.inc');
$org='kaltura';
if ($argc < 2){
	echo 'Usage: '.__FILE__. " <status, i.e [open||closed]>\n";
	exit (1);
}
$status=$argv[1];
//$repo_name=$argv[1];
$client = new GitHubClient();
$client->setCredentials(GITHUB_USER,GITHUB_PASSWD);
	//$client->setDebug(true);
foreach ($orgs as $org){
	$page=1;
	while (true){
		$repos=$client->repos->listOrganizationRepositories($org,'public',100,$page);
		if(empty($repos)){
			break;
		}
		$page++;
		foreach($repos as $rep){
			$repo_name=$rep->getName();
			$currpage=1;
			while(true){
				$client->setPage($currpage);
				$pullies=$client->issues->listIssues($org, $repo_name,$status,'all',date('c',time()-84600));
				if(empty($pullies)){
					break;
				}
				$currpage++;
				foreach ($pullies as $pull){
					$number=$pull->getNumber();
					$username=$pull->getUser()->getLogin();
					$user_id=$pull->getUser()->getId();
					call_hook($client,$org,$repo_name,$number,$username,$user_id,$status);
				}
			}
		}
	}
}
function call_hook($client,$org,$repo_name,$issue_id,$username,$user_id,$status){
	try{
		$client->orgs->members->responseIfRequesterIsNotAnOrganizationMember($org,$username);
	}catch(exception $e){
		$da_issue=$client->issues->getIssue($org,$repo_name,$issue_id);
		$request_type=0;
		$is_pull_req=$da_issue->getPullRequest();
		if(isset($is_pull_req)){
			$request_type=1;
		}
		$url=$da_issue->getHtmlUrl();
		$state=$da_issue->getState();
		$created=$da_issue->getCreatedAt();
		$updated=$da_issue->getUpdatedAt();
		$title=$da_issue->getTitle();
		$body=$da_issue->getBody();
		$da_user=$client->users->getSingleUser($username);
		$user_html_url=$da_user->getHtmlUrl();
		$user_display_name=$da_user->getName();
		$user_email=$da_user->getEmail();
		$user_company=$da_user->getCompany();
		$user_location=$da_user->getLocation();
		$user_avatar=$da_user->getAvatarUrl();
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
		$created_epoch=date_format($created_date, 'U');
		$updated_date = date_create($updated);
		$epoch_now=date('U');
		$title1=str_replace(',','',$title);
		error_log("$repo_name,$username,$issue_id,$title1,".date_format($created_date,'Y-m-d H:i:s').",$status\n",3,'/tmp/contribs_github.csv');
		try{
			$db = new PDO(INSTALLYTICS_DSN, INSTALLYTICS_DB_USERNAME, INSTALLYTICS_DB_PASSWORD);
			$res = $db->prepare('SELECT issue_id FROM github_contribs WHERE issue_id=:issue_id and repo_name=:repo_name and user_name=:user_name limit 1');
			$res->execute(array('issue_id'=>$issue_id,'user_name' => $username,'repo_name'=>$repo_name));
			$row = $res->fetch(PDO::FETCH_ASSOC);
			if(isset($row['issue_id'])){
				$res=$db->prepare('update github_contribs set issue_status=:status,title=:title,last_update=:updated,request_type=:request_type WHERE issue_id=:issue_id and repo_name=:repo_name and user_name=:user_name');
				$res->execute(array('issue_id'=>$issue_id,'user_name' => $username,'repo_name'=>$repo_name,'status'=>$status,'title'=>$title1,'updated'=>$updated,'request_type'=>$request_type));
				echo "'issue_id'=>$issue_id,'user_name' => $username,'repo_name'=>$repo_name,'status'=>$status,'title'=>$title1,'updated'=>$updated,'request_type'=>$request_type\n";
			}else{
				$res=$db->prepare('insert into github_contribs values(NULL,:repo_name,:user_name,:issue_id,:title,:updated,:status,:request_type)');

				$params=array('repo_name'=>$repo_name,'user_name'=>$username,'issue_id'=>$issue_id,'title'=>$title1,'updated'=>$updated,'status'=>$status,'request_type'=>$request_type);
				$res->execute($params);
			}
		}catch(exception $exc){
                	$msg=$exc->getMessage();
			error_log($msg,3,'/tmp/output_'.$status.'_issues.log');
		}
		$db=null;
	}
}

<?php
require_once(__DIR__ . '/github-php-client/client/GitHubClient.php');
require_once(__DIR__. '/config.inc');
function call_hook($hooks,$org,$repo_name,$number,$username,$user_id)
{
	foreach($hooks as $hook){
		if(isset($hook[$org])){ 
			$shooks=$hook[$org];
			foreach($shooks as $shook){
				if($shook['repo']===$repo_name||$shook['repo']==='ALL'){
					foreach($shook['hooks'] as $hook_script){
						exec( __DIR__. "/hooks/$hook_script $org $repo_name $number $username $user_id",$out,$rc);
						if ($rc!==0){
							echo "hooks/$hook_script failed with $rc :(\n";
						}
						echo implode("\n",$out)."\n";
					}
				}
			}
		}
	}
}

$client = new GitHubClient();
$client->setCredentials(GITHUB_USER,GITHUB_PASSWD);
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
				$pullies=$client->pulls->linkRelations($org, $repo_name,'open');
				if(empty($pullies)){
					break;
				}
				$currpage++;
				foreach ($pullies as $pull){
					$number=$pull->getNumber();
					$username=$pull->getUser()->getLogin();
					$user_id=$pull->getUser()->getId();
					call_hook($hooks,$org,$repo_name,$number,$username,$user_id);
				}
			}
		}
	}
}
?>

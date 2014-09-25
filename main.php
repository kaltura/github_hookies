<?php
require_once(__DIR__ . '/github-php-client/client/GitHubClient.php');
require_once(__DIR__. '/config.inc');
function call_hook($hooks,$org,$repo_name,$number,$username,$user_id)
{
	foreach($hooks as $hook){
		if(isset($hook[$org])){ 
			$shooks=$hook[$org];
			foreach($shooks as $shook){
				if($shook['repo']===$repo_name){
					foreach($shook['hooks'] as $hook_script){
						exec("php ". __DIR__. "hooks/$hook_script $org $repo_name $number $username $user_id",$out,$rc);
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
	$more=true;
	$page=0;
	while ($more){
		$repos=$client->repos->listOrganizationRepositories($org,'all',100,$page);
		if(empty($repos)){
			break;
		}
		$page++;
		foreach($repos as $rep){
			$repo_name=$rep->getName();
			$pullies=$client->pulls->linkRelations($org, $repo_name,'open');
			foreach ($pullies as $pull){
				$number=$pull->getNumber();
				$username=$pull->getUser()->getLogin();
				$user_id=$pull->getUser()->getId();
				call_hook($hooks,$org,$repo_name,$number,$username,$user_id);
			}
		}
	}
}
?>

<?php
// repo name and a list of scripts to run on it.
$pull_hooks=array(
	array(
		// your org as key, can be many
		'kaltura'=>array(
			array(
				'repo'=>'ALL',
				// scripts to run, each will get to following as argv:
				// $org $repo_name $number $username $user_id	
				'hooks'=>array('checkCLA.php','dashg.php'),
			),
			array(
				'repo'=>'KalturaGeneratedAPIClientsCLI',
			      // scripts to run, each will get to following as argv:
			      // $org $repo_name $number $username $user_id	
				'hooks'=>array('auto_gen_repo_response.php'),
			),
			// the auto gen'ed repos should not get pull requests, this hooks comments, explaining why and closes the pull
			array(
				'repo'=>'KalturaGeneratedAPIClientsJava',
				'hooks'=>array('auto_gen_repo_response.php'),
			),
			array(
				'repo'=>'KalturaGeneratedAPIClientsCsharp',
				'hooks'=>array('auto_gen_repo_response.php'),
			),
			array(
				'repo'=>'KalturaGeneratedAPIClientsPHP5',
				'hooks'=>array('auto_gen_repo_response.php'),
			),
			array(
				'repo'=>'KalturaGeneratedAPIClientsPHP53',
				'hooks'=>array('auto_gen_repo_response.php'),
			),
			array(
				'repo'=>'KalturaGeneratedAPIClientsRuby',
				'hooks'=>array('auto_gen_repo_response.php'),
			),
			array(
				'repo'=>'KalturaGeneratedAPIClientsPython',
				'hooks'=>array('auto_gen_repo_response.php'),
			),
			array(
				'repo'=>'KalturaGeneratedAPIClientsZF',
				'hooks'=>array('auto_gen_repo_response.php'),
			),
		)
	),
);

$issue_hooks=array(
	array(
		// your org as key, can be many
		'kaltura'=>array(
			array(
				'repo'=>'ALL',
				// scripts to run, each will get to following as argv:
				// $org $repo_name $number $username $user_id	
				'hooks'=>array('dashg_issues.php','general_repo_auto_response.php'),
			),
			array(
				'repo'=>'nginx-vod-module',
				// scripts to run, each will get to following as argv:
				// $org $repo_name $number $username $user_id	
				'hooks'=>array('nginx_modules_repo_auto_response.php'),
			),
			array(
				'repo'=>'nginx-parallel-module',
				// scripts to run, each will get to following as argv:
				// $org $repo_name $number $username $user_id	
				'hooks'=>array('nginx_modules_repo_auto_response.php'),
			),
			array(
				'repo'=>'nginx-secure-token-module',
				// scripts to run, each will get to following as argv:
				// $org $repo_name $number $username $user_id	
				'hooks'=>array('nginx_modules_repo_auto_response.php'),
			),
			/*array(
				'repo'=>'platform-install-packages',
				'hooks'=>array('general_repo_auto_response.php'),
			),*/
		)
	),
);

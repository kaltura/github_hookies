<?php
// repo name and a list of scripts to run on it.
$hooks=array(
	array(
		// your org as key, can be many
		'org'=>array(
			array(
				'repo'=>'your_repo',
				// scripts to run, each will get to following as argv:
				// $org $repo_name $number $username $user_id	
				'hooks'=>array('hook_script.php','hook_script1.php'),
			),
			array(
				'repo'=>'your_other_repo',
				// scripts to run, each will get to following as argv:
				// $org $repo_name $number $username $user_id	
				'hooks'=>array('hook_script.php'),
			),
		)
	),
);

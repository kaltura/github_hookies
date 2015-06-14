#!/usr/bin/php
<?php
require_once('/opt/git_hooks/github-php-client/client/GitHubClient.php');
require_once('/opt/git_hooks/config.inc');

$org=SQLite3::escapeString($argv[1]);
$repo_name=SQLite3::escapeString($argv[2]);
$issue_id=SQLite3::escapeString($argv[3]);
$username=SQLite3::escapeString($argv[4]);
$user_id=SQLite3::escapeString($argv[5]);
$msg="
Thank for you reporting an issue and helping improve Kaltura!

To get the fastest response time, and help the maintainers review and test your reported issues or suggestions, please
ensure that your issue includes the following (please comment with more info if you have not included all this info in
your original issue):
 - Is the issue you're experiencing consistent and across platforms? or does it only happens on certain conditions?
    please provide as much details as possible.
 - Is Nginx installed from the kaltura-nginx RPM or deb package?
	When using RPM, paste the output for:
```
	# rpm -qa kaltura-nginx
```	
	For deb based systems:
```	
	# dpkg -l kaltura-nginx
```
 - If the issue is with the install process, please provide full output of the apt-get/yum install command.
 - If you compiled Nginx on your own, please provide the output for:
	```
	$ uname -a

	$ gcc -v
	```
	If using a compiler other than GCC, please provide exact name and version.
	Also, provide the configure line you used.
 - Check Nginx error logs and provide relevant output

If you only have a general question rather than a bug report, please close this issue and post at:
http://forum.kaltura.org

Thank you in advance,
";

$client = new GitHubClient();
//$client->setDebug(true);
$client->setCredentials(GITHUB_USER,GITHUB_PASSWD);
$cla_checked=null;
try{
	$client->orgs->members->responseIfRequesterIsNotAnOrganizationMember($org,$username);

}catch(exception $e){
	$issue=$client->issues->getIssue($org, $repo_name, $issue_id);
	$state=$issue->getState();
	$created=$issue->getCreatedAt();
	$created_date = date_create($created);
	$created_epoch=date_format($created_date, 'U');
// this is Jun 13 2015. When the hook was first put in. Did not want to retro comment.
	if ($state==='open' and $created_epoch>1434168000){
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


github_hookies
==============

A generic pull request hook.

This can be used for various goals, such as:
* CLA verification
* Auto builds upon pull requests
* Other tasks you may need


License
=======
AGPL v3 [http://www.gnu.org/licenses/agpl-3.0.html]

Requirements
============
- PHP CLI

Configuration
=============
* Edit config.inc and add your credentials.
* Edit hooks_config.inc to reflect your org, repos to monitor and hooks to run. You can setup multiple orgs, and set diff hooks per repo.

NOTE: Make sure your hooks scripts are executable by the user running the main.php script.

Hook example
============
see hooks/checkCLA.php - an example hook to integrat with this CLA system [https://github.com/kaltura/agent-contrib]

Bundled third party software
============================
GitHub API PHP Client (https://github.com/tan-tan-kanarek/github-php-client)

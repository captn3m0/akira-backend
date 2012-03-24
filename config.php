<?php
function configure(){
	$db = new mysqli('localhost','root','nemoabhay','akira');
	option('db',$db);
#	option('base_uri','/akira_backend/'); // set it manually if you use url_rewriting
}

<?php
/**
 * Filepanda index.php
 * This is the main router file
 */
require('lib/limonade.php');//Include the framework
require('config.php');//Configuration
/**
 * This function is called before each
 * route initialization
 */
function before($route)
{
	$db = option('db');
	//@todo do something on this
	//if we are not on homepage
	//and session is not sent
	if(isset($_REQUEST['session'])){
		$r = $_REQUEST['session'];
		//do the auth
		$results = $db->query("SELECT * FROM users WHERE SHA1(CONCAT(email,'kreacher')) = '$r'");
		if( !$results || $results->num_rows == 0)
			halt(NOT_FOUND);
		else{
			list($id,$email) = $results->fetch_row();
			set('email',$email);
			set('id',$id);
		}
	}
	else{
		//if route has journey in it
		if(strpos($route['pattern'],'journey')!==false)
			halt(NOT_FOUND);
	}
}
/**
 * The various routes for the application
 * @see README file for more information
 */

dispatch('/',function(){
	return 'Hello from akira';
});

dispatch('/login',function(){
	//This is where we recieve the login requests
	if(isset($_REQUEST['email'])){
		$email = $_REQUEST['email'];
		$password = $_REQUEST['password'];
		if($email == $password){
			return sha1($email."kreacher");
		}
	}
	else{
		return 'Akira welcomes you.';
	}

});
dispatch('/journey/info/:id',function(){
	$userid = set('id');
	$db = option('db');
	$journey_id = params('id');
	$result = $db->query("SELECT lat,lng FROM journey_points WHERE journey_id = $journey_id");
	$arr = array();
	while($row = $result->fetch_row()){
		array_push($arr,$row);
	}
	return json($arr);
});
dispatch('/journey/end',function(){
	$userid = set('id');
	$db = option('db');
	$result = $db->query("SELECT id FROM journeys WHERE end =0 AND user_id = $userid LIMIT 0,1");
	//get the unended journey
	list($journey_id) = $result->fetch_row();//get its id
	list($lat,$lng) = array($_REQUEST['lat'],$_REQUEST['lng']);//get the lat lng for this request
	$db->query("INSERT INTO journey_points VALUES ($journey_id,$lat,$lng)");//save a new point
	$end_point = $db->insert_id;
	$db->query("UPDATE journeys SET end=$end_point WHERE id = $journey_id");
	return 'ENDED';
});
dispatch('/journey/start',function(){
	$userid = set('id');
	$db = option('db');
	$db->query("INSERT INTO journeys (user_id) VALUES ('$userid')");
	$journey_id = $db->insert_id;
	list($lat,$lng) = array($_REQUEST['lat'],$_REQUEST['lng']);
	$db->query("INSERT INTO journey_points VALUES ($journey_id,$lat,$lng)");
	$start_point_id = $db->insert_id;
	$db->query("UPDATE journeys SET start = $start_point_id WHERE id = $journey_id");
	return 'STARTED';
});
dispatch('/journey/ping',function(){
	$userid = set('id');
	$db = option('db');
	$result = $db->query("SELECT id FROM journeys WHERE end =0 AND user_id = $userid LIMIT 0,1");
	list($journey_id) = $result->fetch_row();
	list($lat,$lng) = array($_REQUEST['lat'],$_REQUEST['lng']);
	$db->query("INSERT INTO journey_points VALUES ($journey_id,$lat,$lng)");
	return 'NOTED';
});
//start the app
run();
?>


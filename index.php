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
	//header("Access-Control-Allow-Origin: ".$_SERVER['HTTP_ORIGIN']);
	header("Allow-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
	header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
}
/**
 * The various routes for the application
 * @see README file for more information
 */

dispatch('/',function(){
	return 'Hello from akira';
});

dispatch_post('/login',function(){
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

dispatch('/journey/list/:user',function(){
	$userid = set('id');
	if(params('user')) $userid = params('user');
	$db = option('db');
	$result = $db->query("SELECT * FROM journeys WHERE user_id = '$userid'");
	$arr = array();
	while($row = $result->fetch_assoc()){
		$mr = $db->query("SELECT * FROM journey_points WHERE journey_id = {$row['id']} ORDER BY id ASC");
		$mra = array();
		while($jp = $mr->fetch_assoc()){
			array_push($mra,$jp);
		}
		array_push($arr,$mra);
	}
	return json($arr);
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
	list($lat,$lng) = @array($_REQUEST['lat'],$_REQUEST['lng']);//get the lat lng for this request
	if(!$lat)
		halt();
	$db->query("INSERT INTO journey_points VALUES ($journey_id,$lat,$lng)");//save a new point
	$end_point = $db->insert_id;
	$db->query("UPDATE journeys SET end=$end_point WHERE id = $journey_id");


	return 'ENDED';
});
dispatch('/journey/start',function(){
	$userid = set('id');
	$db = option('db');
	$db->query("INSERT INTO journeys (user_id) VALUES ('$userid')");
	echo $db->error;
	$journey_id = $db->insert_id;
	list($lat,$lng) = @array($_REQUEST['lat'],$_REQUEST['lng']);
	if(!$lat)
		halt();
	$db->query("INSERT INTO journey_points VALUES ($journey_id,$lat,$lng)");
	$start_point_id = $db->insert_id;
	$db->query("UPDATE journeys SET start = $start_point_id WHERE id = $journey_id");
	return 'STARTED';
});
dispatch('/journey/ping',function(){
	$userid = set('id');
	$db = option('db');
	$result = $db->query("SELECT MAX(id) FROM journeys WHERE end=0 AND user_id = $userid LIMIT 0,1");
	list($journey_id) = $result->fetch_row();
	list($lat,$lng) = array($_REQUEST['lat'],$_REQUEST['lng']);
	$db->query("INSERT INTO journey_points (journey_id,lat,lng) VALUES ($journey_id,$lat,$lng)");
	$end_point = $db->insert_id;
	$end_point--;
	//Now we also need to update the total distance travelled by the user as well
	$results = $db->query("SELECT lat,lng FROM journey_points WHERE id <= '$end_point' AND journey_id = $journey_id ORDER BY id DESC LIMIT 0,2");
	list($lat1,$lng1) = $results->fetch_row();
	list($lat2,$lng2) = $results->fetch_row();
	$distance = abs(distance($lat1,$lng1,$lat2,$lng2));
	$db->query("UPDATE users set distance = distance+$distance");
	return $distance;
});

dispatch('/users',function(){
	$db = option('db');
	$results= $db->query("SELECT * FROM users order by distance DESC");
	$arr = array();
	while($row = $results->fetch_assoc()){
		$row['pic']='http://192.168.208.247/pics/'.$row['id'].'.jpg';
		array_push($arr,$row);
	}
	return json($arr);
});
//start the app
run();
?>


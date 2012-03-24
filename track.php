<?php
$db = new mysqli('localhost','root','nemoabhay','akira');
$from = $argv[1];
$end = $argv[2];
$journey_id = $argv[3];
$json = (json_decode(file_get_contents("http://maps.googleapis.com/maps/api/directions/json?origin=$from&destination=$end&sensor=false")));
foreach ($json->routes[0]->legs[0]->steps as $v){
	$lat =  $v->end_location->lat;
	$lng =  $v->end_location->lng;
	$db->query("INSERT INTO journey_points VALUES ($journey_id,$lat,$lng)");
}


<?php
function configure(){
	$db = new mysqli('localhost','root','nemoabhay','akira');
	option('db',$db);
#	option('base_uri','/akira_backend/'); // set it manually if you use url_rewriting
}
function distance($lat1, $lon1, $lat2, $lon2, $unit) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  return ($miles * 1.609344);
}

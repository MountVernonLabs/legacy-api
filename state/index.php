<?php 
header("Access-Control-Allow-Origin: *");

$state = str_replace("^",".",$_GET['state']);

//Connect to DB
//mysql_connect("72.3.204.175", "636916_zipcodes", "7t82ZZKo") or die(mysql_error());
mysql_connect("558eldb01.blackmesh.com", "api", "cb4662a8ff3ea433") or die(mysql_error());
mysql_select_db("api") or die(mysql_error());

// Query Database
$result = mysql_query("SELECT StateFull, StateAbbreviation from capitals
						WHERE StateFull = '".$state."' OR StateAbbreviation = '".$state."'") or die(mysql_error());  

// Array the results
$row = mysql_fetch_array( $result );


if (mysql_num_rows($result) == 0){
	$data["error"] = "State Code or Name Not Found";
} else {


	// Loop through all columns in the zip code table
	foreach ($row as $key => $value) {
		if (is_numeric($key)){} else{
			$data[$key] = $value;
		}
	}

}

// Output Support JSONP requests
if (isset($_GET['jsoncallback'])){
 echo $_GET['jsoncallback'].'(';
}

// Write the data as JSON
$json = json_encode($data);
echo $json;


if (isset($_GET['jsoncallback'])){
 echo ')';
}


?>
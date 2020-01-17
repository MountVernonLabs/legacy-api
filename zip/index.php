<?php 
header("Access-Control-Allow-Origin: *");
// Get Zip Code and strip out dashes
$zip = str_replace("-", "", $_GET['zip']);

if (is_numeric($zip)){
	// Should be a US ZIP Code
	$zip = substr($zip, 0, 5);
} else {
	// International - right now we only support CAN Postals so we will evaluate for that
	$zip = strtoupper($zip);
}

// Setup PostalType Array
$postaltype["S"] = "Standard";
$postaltype["P"] = "PO Box Only";
$postaltype["U"] = "Unique";
$postaltype["M"] = "Military";

//Connect to DB
//mysql_connect("72.3.204.175", "636916_zipcodes", "7t82ZZKo") or die(mysql_error());
mysql_connect("558eldb01.blackmesh.com", "api", "cb4662a8ff3ea433") or die(mysql_error());
mysql_select_db("api") or die(mysql_error());

// Query Database
$result = mysql_query("SELECT * from zipcodes 
						LEFT JOIN msa ON zipcodes.MSACode = msa.MSACode 
						LEFT JOIN ztca ON zipcodes.PostalCode = ztca.zip 
						LEFT JOIN cbsa ON zipcodes.PostalCode = cbsa.ZipCode 
						LEFT JOIN zillow ON zipcodes.PostalCode = zillow.zip 
						LEFT JOIN DMA ON DMA.CountyFIPS = zipcodes.CountyFIPS
						LEFT JOIN capitals ON capitals.StateAbbreviation = zipcodes.ProvinceAbbr
						WHERE PostalCode = '".$zip."' and CityType = 'D'") or die(mysql_error());  
$result_alternates = mysql_query("SELECT CityType, CityName from zipcodes WHERE PostalCode = '".$zip."' and CityType != 'D'") or die(mysql_error());  

// Array the results
$row = mysql_fetch_array( $result );
$city = mysql_fetch_array( $result_alternates );


if (mysql_num_rows($result) == 0){
	$data["error"] = "Postal Code Not Found";
} else {

	// Retrive additional data from other MX APIs
	//$lookup_politics = file_get_contents('http://api.multiplier2.com/fec/zip.php?zip='.$zip);
	//$append_politics = json_decode($lookup_politics);


	// Loop through all columns in the zip code table
	foreach ($row as $key => $value) {
		if (is_numeric($key)){} else{
			$data[$key] = $value;
		}
	}

	// Loop through and find all the alternative names
	$cityCount = 0;
	while($name = mysql_fetch_array( $result_alternates )) {
		$data["AlternateNames"][$cityCount]["CityName"] = $name["CityName"];
		$data["AlternateNames"][$cityCount]["Type"] = $name["CityType"];
		$cityCount = $cityCount + 1;
	}

	//$data["Politics"] = $append_politics->likely;

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
<?php
function affil($reqAff, &$return_arr) {
  $reqAff = str_replace(' ', '%20', $reqAff);
	$contents = file_get_contents($reqAff);
	$results = json_decode($contents);
	$numFound = 0;
	if (isset($results->response->numFound)) {$numFound = $results->response->numFound;}
	
	if ($numFound != 0) {
		foreach($results->response->docs as $entry) {
			if (isset($entry->acronym_s)) {$acronym = " [".$entry->acronym_s."], ";}else{$acronym = ", ";}
			if (isset($entry->country_s)) {$country = ", ".$entry->country_s;}else{$country = "";}
			$return_arr[] = $entry->docid." ~ ".$entry->name_s.$acronym.$entry->type_s.$country.' ('.$entry->valid_s.')';
		}
	}
}

//Autocomplete affiliations
if (isset($_GET['term'])){
	$basReq = "https://api.archives-ouvertes.fr/ref/structure/?q=(name_t:%22".$_GET['term']."%22%20OR%20name_t:(".$_GET['term'].")%20OR%20code_s:%22".$_GET['term']."%22%20OR%20acronym_t:%22".$_GET['term']."%22%20OR%20acronym_sci:%22".$_GET['term']."%22%20OR%20code_t:%22".$_GET['term']."%22)";
	$return_arr = array();
	
	//VALID
	$reqAff = $basReq."%20AND%20valid_s:%22VALID%22&rows=500&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s%20desc,name_s%20asc";
	affil($reqAff, $return_arr);
	
	//OLD
	$reqAff = $basReq."%20AND%20valid_s:%22OLD%22&rows=500&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s%20desc,name_s%20asc";
	affil($reqAff, $return_arr);
	
	//INCOMING
	$reqAff = $basReq."%20AND%20valid_s:%22INCOMING%22&rows=500&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s%20desc,name_s%20asc";
	affil($reqAff, $return_arr);
	
	echo json_encode($return_arr);
}
?>
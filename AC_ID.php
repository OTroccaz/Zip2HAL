<?php//Autocomplete affiliationsif (isset($_GET['term'])){	$return_arr = array();	//https://api.archives-ouvertes.fr/search/?q=authIdHal_s:oli*&fl=authIdHal_s,authIdHal_i	$reqIdh = "https://api.archives-ouvertes.fr/ref/author/?q=idHal_s:".$_GET['term']."*&fl=idHal_s,idHal_i&rows=1000";	$reqIdh = str_replace(' ', '%20', $reqIdh);	$contents = file_get_contents($reqIdh);	$results = json_decode($contents);	$numFound = 0;	if (isset($results->response->numFound)) {$numFound = $results->response->numFound;}		if ($numFound != 0) {		foreach($results->response->docs as $entry) {			$return_arr[] = $entry->idHal_s.' ('.$entry->idHal_i.')';		}	}		echo json_encode($return_arr);}?>
<?php//Autocomplete affiliationsif (isset($_GET['term'])){	$return_arr = array();	$reqIdh = "https://api.archives-ouvertes.fr/ref/domain/?q=code_s:".$_GET['term']."*&fl=code_s,fr_domain_s&rows=500&sort=code_s%20ASC";	$reqIdh = str_replace(' ', '%20', $reqIdh);	$contents = file_get_contents($reqIdh);	$results = json_decode($contents);	$numFound = 0;	if (isset($results->response->numFound)) {$numFound = $results->response->numFound;}		if ($numFound != 0) {		foreach($results->response->docs as $entry) {			$return_arr[] = str_replace("'", "’", $entry->fr_domain_s).' ~ '.$entry->code_s;		}	}		echo json_encode($return_arr);}?>
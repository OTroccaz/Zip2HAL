<?php//Autocomplete financementsif (isset($_GET['term'])){	$return_arr = array();	$reqIdh = "https://api.archives-ouvertes.fr/ref/anrproject/?q=title_t:".$_GET['term']."*%20OR%20acronym_t:".$_GET['term']."*%20OR%20reference_t:".$_GET['term']."*%20OR%20callAcronym_t:".$_GET['term']."*&valid_s=VALID&fl=docid,label_s,valid_s,yearDate_s&rows=500&sort=label_s%20ASC";	$reqIdh = str_replace(' ', '%20', $reqIdh);	$contents = file_get_contents($reqIdh);	$results = json_decode($contents);	$numFound = 0;	if (isset($results->response->numFound)) {$numFound = $results->response->numFound;}		if ($numFound != 0) {		foreach($results->response->docs as $entry) {			if (isset($entry->docid)) {$docid = $entry->docid;}else{$docid = "";}			if (isset($entry->label_s)) {$label = $entry->label_s;}else{$label = "";}			if (isset($entry->yearDate_s)) {$annee = $entry->yearDate_s;}else{$annee = "";}			if (isset($entry->valid_s)) {$valid = $entry->valid_s;}else{$valid = "";}			$return_arr[] = $docid.'~'.$label.'~'.$annee.'~'.$valid;		}	}		echo json_encode($return_arr);}?>
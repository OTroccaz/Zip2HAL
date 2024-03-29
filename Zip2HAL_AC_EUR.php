<?php
/*
 * Zip2HAL - Importez vos publications dans HAL - Import your publications into HAL
 *
 * Copyright (C) 2023 Olivier Troccaz (olivier.troccaz@cnrs.fr) and Laurent Jonchère (laurent.jonchere@univ-rennes.fr)
 * Released under the terms and conditions of the GNU General Public License (https://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * Autocomplétion des financements - Autocompletion of financing
 */
 
//Suppresion des accents
function wd_remove_accents($str, $charset='utf-8') {
	$str = htmlentities($str, ENT_NOQUOTES, $charset);

	$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
	$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
	$str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

	return $str;
}

//Autocomplete financements
if (isset($_GET['term'])){
	$term = wd_remove_accents(str_replace(' ', '%20', $_GET['term']));
	$return_arr = array();
	$reqIdh = "https://api.archives-ouvertes.fr/ref/europeanproject/?q=(title_t:".$term."*%20OR%20acronym_t:".$term."*%20OR%20reference_t:".$term."*%20OR%20callId_t:".$term."*%20OR%20financing_t:".$term."*)%20AND%20valid_s:%22VALID%22&fl=docid,reference_s,financing_s,callId_s,acronym_s,title_s,valid_s,startDtae_s,endDate_tdate&rows=500&sort=docid%20ASC";
	$reqIdh = str_replace(' ', '%20', $reqIdh);
	$contents = file_get_contents($reqIdh);
	$results = json_decode($contents);
	$numFound = 0;
	if (isset($results->response->numFound)) {$numFound = $results->response->numFound;}
	
	if ($numFound != 0) {
		foreach($results->response->docs as $entry) {
			if (isset($entry->docid)) {$docid = $entry->docid;}else{$docid = "";}
			if (isset($entry->reference_s)) {$refer = $entry->reference_s;}else{$refer = "";}
			if (isset($entry->financing_s)) {$finan = $entry->financing_s;}else{$finan = "";}
			if (isset($entry->callId_s)) {$calid = $entry->callId_s;}else{$calid = "";}
			if (isset($entry->acronym_s)) {$acron = $entry->acronym_s;}else{$acron = "";}
			if (isset($entry->title_s)) {$titre = $entry->title_s;}else{$titre = "";}
			if (isset($entry->startDate_tdate)) {$anneS = $entry->startDate_tdate;}else{$anneS = "";}
			if (isset($entry->endDate_tdate)) {$anneE = $entry->endDate_tdate;}else{$anneE = "";}
			if (isset($entry->valid_s)) {$valid = $entry->valid_s;}else{$valid = "";}
			$return_arr[] = $docid.'~'.$refer.'~'.$finan.'~'.$calid.'~'.$acron.'~'.$titre.'~'.$anneS.'~'.$anneE.'~'.$valid;
		}
	}
	
	echo json_encode($return_arr);
}
?>
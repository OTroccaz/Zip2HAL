<?php
/*
 * Zip2HAL - Importez vos publications dans HAL - Import your publications into HAL
 *
 * Copyright (C) 2023 Olivier Troccaz (olivier.troccaz@cnrs.fr) and Laurent Jonchère (laurent.jonchere@univ-rennes.fr)
 * Released under the terms and conditions of the GNU General Public License (https://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * Autocomplétion des financements ANR - Autocompletion of ANR funding
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
	$reqIdh = "https://api.archives-ouvertes.fr/ref/anrproject/?q=(title_t:".$term."*%20OR%20acronym_t:".$term."*%20OR%20reference_t:".$term."*%20OR%20acronymProgram_t:".$term."*)%20AND%20valid_s:%22VALID%22&fl=docid,label_s,valid_s,yearDate_s&rows=500&sort=label_s%20ASC";
	$reqIdh = str_replace(' ', '%20', $reqIdh);
	$contents = file_get_contents($reqIdh);
	$results = json_decode($contents);
	$numFound = 0;
	if (isset($results->response->numFound)) {$numFound = $results->response->numFound;}
	
	if ($numFound != 0) {
		foreach($results->response->docs as $entry) {
			if (isset($entry->docid)) {$docid = $entry->docid;}else{$docid = "";}
			if (isset($entry->label_s)) {$label = $entry->label_s;}else{$label = "";}
			if (isset($entry->yearDate_s)) {$annee = $entry->yearDate_s;}else{$annee = "";}
			if (isset($entry->valid_s)) {$valid = $entry->valid_s;}else{$valid = "";}
			$return_arr[] = $docid.'~'.$label.'~'.$annee.'~'.$valid;
		}
	}
	
	echo json_encode($return_arr);
}
?>
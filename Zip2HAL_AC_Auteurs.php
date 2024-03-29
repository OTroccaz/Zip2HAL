<?php
/*
 * Zip2HAL - Importez vos publications dans HAL - Import your publications into HAL
 *
 * Copyright (C) 2023 Olivier Troccaz (olivier.troccaz@cnrs.fr) and Laurent Jonchère (laurent.jonchere@univ-rennes.fr)
 * Released under the terms and conditions of the GNU General Public License (https://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * Autocomplétion des auteurs - Autocompletion of authors
 */
 
//Suppresion des accents
function wd_remove_accents($str, $charset='utf-8') {
	$str = htmlentities($str, ENT_NOQUOTES, $charset);

	$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
	$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
	$str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

	return $str;
}

//Autocomplete auteurs
if (isset($_GET['term'])){
	$term = wd_remove_accents(str_replace(' ', '%20', $_GET['term']));
	$return_arr = array();
	$reqIdh = "https://api.archives-ouvertes.fr/ref/author/?q=fullName_t:".$term."*%20AND%20valid_s:%22VALID%22&fl=fullName_s&rows=1000";
	$reqIdh = str_replace(' ', '%20', $reqIdh);
	$contents = file_get_contents($reqIdh);
	$results = json_decode($contents);
	$numFound = 0;
	if (isset($results->response->numFound)) {$numFound = $results->response->numFound;}
	
	if ($numFound != 0) {
		foreach($results->response->docs as $entry) {
			if (isset($entry->fullName_s)) {$fullname = $entry->fullName_s;}else{$fullname = "";}
			$return_arr[] = $fullname;
		}
	}
	
	echo json_encode($return_arr);
}
?>
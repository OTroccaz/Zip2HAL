<?php
/*
 * Zip2HAL - Importez vos publications dans HAL - Import your publications into HAL
 *
 * Copyright (C) 2023 Olivier Troccaz (olivier.troccaz@cnrs.fr) and Laurent Jonchère (laurent.jonchere@univ-rennes.fr)
 * Released under the terms and conditions of the GNU General Public License (https://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * Autocomplétion des domaines - Domain autocompletion
 */
 
//Suppresion des accents
function wd_remove_accents($str, $charset='utf-8') {
	$str = htmlentities($str, ENT_NOQUOTES, $charset);

	$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
	$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
	$str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

	return $str;
}

//Autocomplete domaines
if (isset($_GET['term'])){
	$term = wd_remove_accents(str_replace(' ', '%20', $_GET['term']));
	$return_arr = array();
	$reqIdh = "https://api.archives-ouvertes.fr/ref/domain/?q=code_s:".$term."*&fl=code_s,fr_domain_s&rows=500&sort=code_s%20ASC";
	$reqIdh = str_replace(' ', '%20', $reqIdh);
	$contents = file_get_contents($reqIdh);
	$results = json_decode($contents);
	$numFound = 0;
	if (isset($results->response->numFound)) {$numFound = $results->response->numFound;}
	
	if ($numFound != 0) {
		foreach($results->response->docs as $entry) {
			$return_arr[] = str_replace("'", "’", $entry->fr_domain_s).' ~ '.$entry->code_s;
		}
	}
	
	echo json_encode($return_arr);
}
?>
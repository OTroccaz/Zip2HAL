<?php
/*
 * Zip2HAL - Importez vos publications dans HAL - Import your publications into HAL
 *
 * Copyright (C) 2023 Olivier Troccaz (olivier.troccaz@cnrs.fr) and Laurent Jonchère (laurent.jonchere@univ-rennes.fr)
 * Released under the terms and conditions of the GNU General Public License (https://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * Autocomplétion des pays - Countries autocompletion
 */
 
//Autocomplete pays
include "./Zip2HAL_codes_pays.php";
$keyPays = array_keys($countries);

$return_arr = array();
if (isset($_GET['term'])){
	foreach($keyPays as $entry) {
		if (stripos($entry, $_GET['term']) !== false) {
			$return_arr[] = str_replace("'", "’", $entry);
		}
	}
}
echo json_encode($return_arr);
?>
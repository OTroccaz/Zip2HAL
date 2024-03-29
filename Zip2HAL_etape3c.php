<?php
/*
 * Zip2HAL - Importez vos publications dans HAL - Import your publications into HAL
 *
 * Copyright (C) 2023 Olivier Troccaz (olivier.troccaz@cnrs.fr) and Laurent Jonchère (laurent.jonchere@univ-rennes.fr)
 * Released under the terms and conditions of the GNU General Public License (https://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * Etape 3c - Stage 3c
 */
 
//Etape 3c - Recherche id auteur grâce à l'affiliation éventuellement trouvée

echo '<div class="row">';
echo '    <div class="col-md-6">';
echo '        <div class="card ribbon-box">';
echo '            <div class="card-body">';
echo '                <div class="ribbon ribbon-success float-right">Étape 3c</div>';
echo '                <h5 class="text-success mt-0">Recherche des docid auteur grâce aux affiliations éventuellement trouvées</h5>';
echo '                <div class="ribbon-content">';

$cpt = 1;
$cptId = 0;
$year = date('Y', time());

//echo '<b>Etape 3c : recherche des docid auteur grâce aux affiliations éventuellement trouvées</b><br>';
echo '<div id=\'cpt3c-'.$idFic.'\'></div>';

if(isset($typDbl) && ($typDbl == "HALCOLLTYP" || $typDbl == "HALTYP")) {//Doublon de type TYP > inutile d'effectuer les recherches
	echo 'Recherche inutile car c\'est une notice doublon';
}else{
	//Début bloc docid
	echo '<span><a style="cursor:pointer;" class="text-primary" onclick="afficacherRec(\'3c\', '.$idFic.')";>Recherche des docid</a><br>';
	echo '<span id="Rrec-3c-'.$idFic.'" style="display: none;">';
	
	include "./Zip2HAL_constantes.php";

	for($i = 0; $i < count($halAut); $i++) {
		progression($cpt, count($halAut), 'cpt3c-'.$idFic, $iPro, 'auteur');
		if($halAut[$i]['docid'] == "") {//Pas d'id auteur
			for($j = 0; $j < count($halAff); $j++) {
				//if($halAff[$j]['firstName'] == $halAut[$i][$cstFN] && $halAff[$j]['lastName'] == $halAut[$i][$cstLN]) {
				if(strpos($halAut[$i]['affilName'], $halAff[$j]['lsAff']) !== false) {
					$affil = urlencode($halAff[$j]['names']);
					$afill = str_replace("&", "%24", $affil);
					$reqId = "https://api.archives-ouvertes.fr/search/index/?q=authLastName_sci:%22".urlencode($halAut[$i][$cstLN])."%22%20AND%20authFirstName_sci:%22".urlencode($halAut[$i][$cstFN])."%22&fq=(structAcronym_sci:%22".$affil."%22%20OR%20structName_sci:%22".$affil."%22%20OR%20structCode_sci:%22".$affil."%22)&fl=authIdLastNameFirstName_fs&sort=abs(sub(producedDateY_i,".$year."))%20asc";
					$reqId = str_replace(" ", "%20", $reqId);
					echo '<a target="_blank" href="'.$reqId.'">URL requête docid HAL</a><br>';
					//echo $reqId.'<br>';
					$contId = file_get_contents($reqId);
					$resId = json_decode($contId);
					$numFound = 0;
					if(isset($resId->response->numFound)) {$numFound = $resId->response->numFound;}
					if($numFound != 0) {
						$tests = $resId->response->docs[0]->authIdLastNameFirstName_fs;
						$correxct = "non";
						//On recherche d'abord la correspondance exacte avec nom + prénom
						foreach($tests as $test) {
							if (strpos($test, ($halAut[$i][$cstLN]." ".$halAut[$i][$cstFN])) !== false) {
								$testTab = explode('_FacetSep_', $test);
								$halAut[$i]['docid'] = $testTab[0];
								$cptId++;
								$correxct = "oui";
								break 2;
							}
						}
						if ($correxct == "non") {//Pas de correspondance exacte  nom + prénom
							foreach($tests as $test) {
								if((strpos($test, ($halAut[$i][$cstFN])) !== false || strpos($test, (" ".substr($halAut[$i][$cstFN], 0, 1))) !== false) && strpos($test, $halAut[$i][$cstLN]) !== false) {
									$testTab = explode('_FacetSep_', $test);
									$halAut[$i]['docid'] = $testTab[0];
									$cptId++;
									break 2;
								}
							}
						}
					}
				}
			}
		}
		$cpt++;
	}

	echo '</span></span>';//Fin bloc docid
	echo $cptId.' docid auteur trouvé(s)';
}
	
echo '<script>';
echo 'document.getElementById(\'cpt3c-'.$idFic.'\').style.display = \'none\';';
echo '</script>';

echo '								</div>';
echo '						</div> <!-- end card-body -->';
echo '				</div>';
echo '		</div>';
//echo '</div> <!-- .row -->';
//Fin étape 3c
?>
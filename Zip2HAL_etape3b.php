<?php
//Etape 3b - Recherche la dernière affiliation associée aux auteurs sans affiliation

//echo '<div class="row">';
echo '    <div class="col-md-6">';
echo '        <div class="card ribbon-box">';
echo '            <div class="card-body">';
echo '                <div class="ribbon ribbon-success float-right">Étape 3b</div>';
echo '                <h5 class="text-success mt-0">Recherche de la dernière affiliation associée avec HAL aux auteurs sans affiliation</h5>';
echo '                <div class="ribbon-content">';

$cpt = 1;
$year = date('Y', time());

//echo '<b>Etape 3b : recherche de la dernière affiliation associée avec HAL aux auteurs sans affiliation</b><br>';
echo '<div id=\'cpt3b\'></div>';

if(isset($typDbl) && ($typDbl == "HALCOLLTYP" || $typDbl == "HALTYP")) {//Doublon de type TYP > inutile d'effectuer les recherches
	echo 'Recherche inutile car c\'est une notice doublon';
}else{
	//Début bloc affiliations
	echo '<span><a style="cursor:pointer;" class="text-primary" onclick="afficacherRec(\'3b\', '.$idFic.')";>Calcul des dernières affiliations</a><br>';
	echo '<span id="Rrec-3b-'.$idFic.'" style="display: none;">';
	
	include "./Zip2HAL_constantes.php";

	//Si un auteur n'a aucune affiliation > rechercher dans le référentiel authorstructure pour remonter la dernière affiliation HAL associée à cet auteur
	//Combien d'auteur(s) concerné(s) ?
	$nbAutnoaff = 0;
	$cptNoaff = 0;//Compteur d'affiliations remontées par cette méthode
	for($i = 0; $i < count($halAut); $i++) {
		if($halAut[$i][$cstAN] == "") {$nbAutnoaff++;}
	}

	for($i = 0; $i < count($halAut); $i++) {
		if($halAut[$i][$cstAN] == "") {
			progression($cpt, $nbAutnoaff, 'cpt3b', $iPro, 'auteur');
			$firstNameT = strtolower(wd_remove_accents($halAut[$i][$cstFN]));
			$lastNameT = strtolower(wd_remove_accents($halAut[$i][$cstLN]));
			
			$reqAut = "https://api.archives-ouvertes.fr/search/authorstructure/?firstName_t=".$firstNameT."&lastName_t=".$lastNameT."&producedDateY_i=".$year;
			$reqAut = str_replace(" ", "%20", $reqAut);
			echo '<a target="_blank" href="'.$reqAut.'">URL requête auteur structure HAL</a><br>';
			//echo $reqAut.'<br>';
			$contAut = file_get_contents($reqAut);
			$resAut = json_decode($contAut);
			$orgName = "";
			//var_dump($resAut);
			if(isset($resAut->response->result->org->orgName)) {//Un seul résultat
				if(is_array($resAut->response->result->org->orgName)) {
					$orgName = $resAut->response->result->org->orgName[0];
				}else{
					$orgName = $resAut->response->result->org->orgName;
				}
				$orgName = str_replace(array("[", "]", "&", "="), array("%5B", "%5D", "%26", "%3D"), $orgName);
				//Est-ce une affiliation 'longue' (avec beaucoup de virgules) ou 'courte' ?
				//if(substr_count($orgName, ',') > 2) {$loncou = "longue";}else{$loncou = "courte";}								
				$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=%22".$orgName."%22%20AND%20valid_s:(VALID%20OR%20OLD)&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s desc,docid asc";
				$reqAff = str_replace(" ", "%20", $reqAff);
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a target="_blank" href="'.$reqAff.'">URL requête test validité affiliation trouvée</a><br>';
				//echo $reqAff.'<br>';
				$contAff = file_get_contents($reqAff);
				$resAff = json_decode($contAff);
				$docid = "non";
				if(isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
				if($numFound != 0) {			
					foreach($resAff->response->docs as $affil) {
						if(($affil->valid_s == "VALID" || $affil->valid_s == "OLD") && $docid == "non") {
							$halAff[$iAff]['docid'] = $affil->docid;
							$cptNoaff++;
							$cptAff++;
							$halAff[$iAff]['lsAff'] = $cstlSA.$cptAff."~";
							$halAff[$iAff]['valid'] = $affil->valid_s;
							$halAff[$iAff]['names'] = $affil->name_s;
							if(isset($affil->acronym_s)) {$acronym = " [".$affil->acronym_s."], ";}else{$acronym = ", ";}
							if(isset($affil->country_s)) {$country = ", ".$affil->country_s;}else{$country = "";}
							$halAff[$iAff]['ncplt'] = $affil->docid." ~ ".$affil->name_s.$acronym.$affil->type_s.$country;
							$halAff[$iAff]['firstName'] = $halAut[$i][$cstFN];
							$halAff[$iAff]['lastName'] = $halAut[$i][$cstLN];
							$halAut[$i][$cstAN] .= $cstlSA.$cptAff."~";
							$iAff++;
							$docid = "oui";
							//Pour les affiliations courtes, on ne prend que le premier résultat remonté
							//if($loncou == "courte") {break 2;}
						}
					}
					
					//if($docid == "non") {//pas de docid trouvé avec VALID ou OLD > on teste avec INCOMING
					//	foreach($resAff->response->docs as $affil) {
					//		if($affil->valid_s == "INCOMING"  && $docid == "non") {
					//			$halAff[$iAff]['docid'] = $affil->docid;
					//			$cptNoaff++;
					//			$cptAff++;
					//			$halAff[$iAff]['lsAff'] = "localStruct-Aff".$cptAff;
					//			$halAut[$i][$cstAN] = "localStruct-Aff".$cptAff."~";
					//			$iAff++;
					//			$docid = "oui";
					//		}
					//	}
					//}
					
				}
			}else{//Plusieurs résultats > N'analyser que les 2 premiers
				$org = 0;
				while($org <= 2) {
					if(isset($resAut->response->result->org[$org]->orgName)) {
						if(is_array($resAut->response->result->org[$org]->orgName)) {
							$orgName = $resAut->response->result->org[$org]->orgName[0];
						}else{
							$orgName = $resAut->response->result->org[$org]->orgName;
						}
						$orgName = str_replace(array("[", "]", "&", "="), array("%5B", "%5D", "%26", "%3D"), $orgName);
						//Est-ce une affiliation 'longue' (avec beaucoup de virgules) ou 'courte' ?
						//if(substr_count($orgName, ',') > 2) {$loncou = "longue";}else{$loncou = "courte";}				
						$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=%22".$orgName."%22%20AND%20valid_s:(VALID%20OR%20OLD)&fl=docid,valid_s,name_s,type_s&sort=valid_s desc,docid asc";
						$reqAff = str_replace(" ", "%20", $reqAff);
						echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a target="_blank" href="'.$reqAff.'">URL requête test validité affiliation trouvée</a><br>';
						//echo $reqAff.'<br>';
						$contAff = file_get_contents($reqAff);
						$resAff = json_decode($contAff);
						$docid = "non";
						if(isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
						if($numFound != 0) {			
							foreach($resAff->response->docs as $affil) {
								if(($affil->valid_s == "VALID" || $affil->valid_s == "OLD") && $docid == "non") {
									$halAff[$iAff]['docid'] = $affil->docid;
									$cptNoaff++;
									$cptAff++;
									$halAff[$iAff]['lsAff'] = $cstlSA.$cptAff."~";
									$halAff[$iAff]['valid'] = $affil->valid_s;
									$halAff[$iAff]['names'] = $affil->name_s;
									if(isset($affil->acronym_s)) {$acronym = " [".$affil->acronym_s."], ";}else{$acronym = ", ";}
									if(isset($affil->country_s)) {$country = ", ".$affil->country_s;}else{$country = "";}
									$halAff[$iAff]['ncplt'] = $affil->docid." ~ ".$affil->name_s.$acronym.$affil->type_s.$country;
									$halAff[$iAff]['firstName'] = $halAut[$i][$cstFN];
									$halAff[$iAff]['lastName'] = $halAut[$i][$cstLN];
									$halAut[$i][$cstAN] .= $cstlSA.$cptAff."~";
									$iAff++;
									$docid = "oui";
									//Pour les affiliations courtes, on ne prend que le premier résultat remonté
									//if($loncou == "courte") {break 2;}
								}
							}
							
							//if($docid == "non") {//pas de docid trouvé avec VALID ou OLD > on teste avec INCOMING
							//	foreach($resAff->response->docs as $affil) {
							//		if($affil->valid_s == "INCOMING"  && $docid == "non") {
							//			$halAff[$iAff]['docid'] = $affil->docid;
							//			$cptNoaff++;
							//			$cptAff++;
							//			$halAff[$iAff]['lsAff'] = "localStruct-Aff".$cptAff;
							//			$halAut[$i][$cstAN] = "localStruct-Aff".$cptAff."~";
							//			$iAff++;
							//			$docid = "oui";
							//		}
							//	}
							//}
							
						}
					}
					$org++;
				}
			}
			$cpt++;
		}
	}
	
	echo '</span></span>';//Fin bloc affiliations
	echo $cptNoaff.' affiliation(s) manquante(s) trouvée(s)';
}

echo '<script>';
echo 'document.getElementById(\'cpt3b\').style.display = \'none\';';
echo '</script>';

echo '								</div>';
echo '						</div> <!-- end card-body -->';
echo '				</div>';
echo '		</div>';
echo '</div> <!-- .row -->';
//Fin étape 3b
?>
<?php
//Etape 1 - Parcours des notices à la recherche de doublons potentiels (DOI ou titre exact)		
echo '<div class="row">';
echo '    <div class="col-md-6">';
echo '        <div class="card ribbon-box">';
echo '            <div class="card-body">';
echo '                <div class="ribbon ribbon-success float-right">Étape 1</div>';
echo '                <h5 class="text-success mt-0">Recherche des doublons potentiels</h5>';
echo '                <div class="ribbon-content">';
								
//echo '<b>Etape 1 : recherche des doublons potentiels</b><br>';
echo '<a target="_blank" href="'.$reqAPI.'">URL requête API HAL</a><br>';

if($numFound == 0) {			
	echo 'Aucune notice trouvée dans HAL, <strong>donc, pas de doublon</strong>';
	$typDbl = "";
	$idTEI = "";
}else{
	$cpt = 1;
	$dbl = 0;
	$halId = array();
	$typDbl = "";
	$txtDbl = "";
	$typid = " <strong>et les types sont identiques</strong>";
	$nocol = " mais <strong>pas dans la collection</strong> ";
	
	echo $numFound. ' notice(s) examinée(s)<br>';
	echo '<div id=\'cpt1\'></div>';
	
	foreach($results->response->docs as $entry) {
		progression($cpt, $numFound, 'cpt1', $iPro, 'notice');
		$hId = $entry->halId_s;
		$halId[$hId] = $hId;
		$halId['doublon'][$hId] = "";
		$doi = "";
		$titlePlus = "";
		$doublon = "non";
		
		//Il peut y avoir des titres principaux alternatifs (français/anglais, par exemple)
		for($tit = 0; $tit < count($entry->title_s); $tit++) {
			$titreInit = $entry->title_s[$tit];
			
			//Le titre du fichier HAL sera la clé principale pour rechercher l'article dans HAL, on le simplifie maintenant (minuscules, pas de ponctuation ni d'espaces, etc.)
			$titlePlus = $entry->title_s[$tit];
			//Y-a-t-il un sous-titre ?
			if(isset($entry->subTitle_s[$tit])) {
				$titreInit = $titlePlus;
				$titlePlus .= " : ".$entry->subTitle_s[$tit];
			}
			$encodedTitle = mb_strtolower(normalize($titlePlus));
			
			//On compare les titres normalisés
			$idTEI = "";
			if($enctitTEI == $encodedTitle) {
				$idTEI = $entry->halId_s;
				$docTEI = $entry->docType_s;
				$halId[$encodedTitle] = $hId;
				$doublon = "titre";
			}
		}

		//On compare également les DOI s'ils sont présents
		if(isset($entry->doiId_s)) {$doi = strtolower($hId);}
		if($doiTEI != "" && isset($entry->doiId_s) && $doiTEI == $entry->doiId_s) {
			$idTEI = $entry->halId_s;
			$docTEI = $entry->docType_s;
			$halId[$doi] = $hId;
			if($doublon == "non") {
				$doublon = "DOI";
			}else{
				$doublon .= " et du DOI";
			}
		}
		
		if($doublon != "non") {
			//Doublon trouvé dans HAL > Est-il aussi présent dans la collection et de quel type ?
			$dbl++;
			$halId['doublon'][$hId] .= '&nbsp;<a target="_blank" href="https://hal.archives-ouvertes.fr/'.$halId[$hId].'"><img src=\'./img/doublon.jpg\'></a>&nbsp;';
			$reqDbl = "https://api.archives-ouvertes.fr/search/".$portail."/?fq=collCode_s:%22".$team."%22%20AND%20title_t:%22".$critere."*%22&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
			$reqDbl = str_replace(" ", "%20", $reqDbl);
			$contDbl = file_get_contents($reqDbl);
			$resDbl = json_decode($contDbl);
			$numDbl = 0;
			if(isset($resDbl->response->numFound)) {$numDbl=$resDbl->response->numFound;}
			if($numDbl != 0) {
				foreach($resDbl->response->docs as $entDbl) {
					$doublonDbl = "non";
					if(strpos($entDbl->title_s[0], "[") !== false && strpos($entDbl->title_s[0], "]") !== false) {
						$posi = strpos($entDbl->title_s[0], "[")+1;
						$posf = strpos($entDbl->title_s[0], "]");
						$tradTitleDbl = substr($entDbl->title_s[0], $posi, $posf-$posi);
						$encodedTitleDbl = mb_strtolower(normalize($tradTitleDbl));
					}else{
						//Y-a-t-il un sous-titre ?
						$titlePlusDbl = $entDbl->title_s[0];
						if(isset($entDbl->subTitle_s[0])) {
							$titreInitDbl = $titlePlusDbl;
							$titlePlusDbl .= " : ".$entDbl->subTitle_s[0];
						}
						$encodedTitleDbl = mb_strtolower(normalize($titlePlusDbl));
						
						//On récupère le type de document
						$docTEIDbl = $entDbl->docType_s;
						
						//On compare les titres normalisés
						if($enctitTEI == $encodedTitleDbl) {
							$doublonDbl = "titre";
						}

						//On compare également les DOI s'ils sont présents
						if($doiTEI != "" && isset($entDbl->doiId_s) && $doiTEI == $entDbl->doiId_s) {
							$docTEIDbl = $entDbl->docType_s;
							if($doublonDbl == "non") {
								$doublonDbl = "DOI";
							}else{
								$doublonDbl .= " et du DOI";
							}
						}
						if($doublonDbl != "non") {//Doublon trouvé dans la collection > vérification du type
							$txtDbl = " <strong>et dans la collection ".$team."</strong>";
							if($typTEI == $docTEIDbl) {//Mêmes types de document
								$txtDbl .= $typid;
								$typDbl = "HALCOLLTYP";
							}else{
								$txtDbl .= " <strong>mais les types sont différents</strong>";
								$typDbl = "HALCOLL";
							}
							break 2;//Doublon HALCOLL trouvé > sortie des 2 boucles foreach
						}else{
							if($typTEI == $docTEIDbl) {//Mêmes types de document
								$txtDbl = " mais pas dans la collection ".$team.$typid;
								$typDbl = "HALTYP";
							}else{
								$txtDbl = $nocol."<strong>".$team. " et les types sont différents</strong>";
								$typDbl = "HAL";
							}
						}
					}
				}
			}else{
				if($typTEI == $docTEI) {//Mêmes types de document
					$txtDbl = $nocol."<strong>".$team.$typid."</strong>";
					$typDbl = "HALTYP";
				}else{
					$txtDbl = $nocol."<strong>".$team. " et les types sont différents</strong>";
					$typDbl = "HAL";
				}
			}
			break;
		}
		$cpt++;
	}
	if($dbl == 0) {echo 'Aucune notice trouvée dans HAL, donc, pas de doublon';}//Notice non trouvée > pas de doublon
	if($dbl >= 1) {echo 'La notice est déjà présente dans HAL'.$txtDbl;}//Présence de doublon(s)

	echo '<script>';
	echo 'document.getElementById(\'cpt1\').style.display = \'none\';';
	echo '</script>';
}
echo '								</div>';
echo '						</div> <!-- end card-body -->';
echo '				</div>';
echo '		</div>';
//echo '</div> <!-- .row -->';
//Fin étape 1
?>
<?php
/*
 * Zip2HAL - Importez vos publications dans HAL - Import your publications into HAL
 *
 * Copyright (C) 2023 Olivier Troccaz (olivier.troccaz@cnrs.fr) and Laurent Jonchère (laurent.jonchere@univ-rennes.fr)
 * Released under the terms and conditions of the GNU General Public License (https://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * Etape 3a - Stage 3a
 */
 
//Etape 3a - Recherche des id structure des affiliations

echo '<div class="row">';
echo '    <div class="col-md-6">';
echo '        <div class="card ribbon-box">';
echo '            <div class="card-body">';
echo '                <div class="ribbon ribbon-success float-right">Étape 3a</div>';
echo '                <h5 class="text-success mt-0">Recherche des id structures des affiliations</h5>';
echo '                <div class="ribbon-content">';

$cpt = 1;
$iAff = 0;
$nomAff = array();//Code initial des affiliations (à parir du XML)
$halAff = array();
$anepasTester = array('UMR', 'UMS', 'UPR', 'ERL', 'IFR', 'UR', 'USR', 'USC', 'CIC', 'CIC-P', 'CIC-IT', 'FRE', 'EA', 'INSERM', 'U', 'CHU', 'CNRS', 'INRA', 'CIRAD', 'INRAE', 'IRSTEA', 'CEA', 'AP HP', 'AP-HP', 'France', 'Université de Rennes', 'INSA');
//$affdejaTestee = array();//Tableau des affiliations déjà testées et résultat obtenu pour éviter de refaire des tests


//echo '<b>Etape 3a : recherche des id structures des affiliations</b><br>';
echo '<div id=\'cpt3a-'.$idFic.'\'></div>';

if(isset($typDbl) && ($typDbl == "HALCOLLTYP" || $typDbl == "HALTYP")) {//Doublon de type TYP > inutile d'effectuer les recherches
	echo 'Recherche inutile car c\'est une notice doublon';
}else{
	//Début bloc affiliations
	echo '<span><a style="cursor:pointer;" class="text-primary" onclick="afficacherRec(\'3a\', '.$idFic.')";>Calcul des affiliations</a><br>';
	echo '<span id="Rrec-3a-'.$idFic.'" style="display: none;">';

	include "./Zip2HAL_constantes.php";

	$cptAff = 0;
	
	//Eventuelles rawAffs
	$elts = $xml->getElementsByTagName("author");
	$rawArray = array();
	//Sauvegarde des rawAffs
	foreach($elts as $elt) {
		if ($elt->childNodes->length) {
			foreach($elt->childNodes as $child) {
				if ($child->nodeName == "rawAffs") {
					$rawArray[] = $child->nodeValue;
				}
			}
		}
	}
	//var_dump($rawArray);

	//Affiliations
	$affs = $xml->getElementsByTagName("org");
	foreach($affs as $aff) {
		$nomAff[$iAff]['pays'] = "";
		if($aff) {
			if($aff->hasAttribute($cstXI)) {$nomAff[$iAff][$cstLA] = '#'.$aff->getAttribute($cstXI).'~'; $lsOrg = '#'.$aff->getAttribute($cstXI).'~';}
			if($aff->hasAttribute("type")) {$nomAff[$iAff]['type'] = $aff->getAttribute("type");}
			$cptAff++;
		}
		foreach($aff->childNodes as $elt) {
			//ROR
			if($elt->nodeName == "idno" && $elt->hasAttribute("type") && $elt->getAttribute("type") == "ROR") {
				$nomAff[$iAff]['ror'] = $elt->nodeValue;
			}
			if($elt->nodeName == "orgName") {
				$orgAff = str_replace("Electronic address", "", $elt->nodeValue);
				//Si présence d'un @, il y a alors possibilité de remonter le domaine mail pour l'auteur
				if(strpos($orgAff, "@") !== false) {
					$tabDomel = explode("@", $orgAff);
					$tabOrg = explode(" ", str_replace("  ", " ", $tabDomel[0]));
					$debMel = $tabOrg[count($tabOrg) - 1];
					$domMel = $tabDomel[1];
					//Si le dernier caractère du domaine est un point, le retirer
					if(substr($domMel, -1) == ".") {$domMel = substr($domMel, 0, (strlen($domMel) - 1));}
					$melAut = $debMel."@".$domMel;
					for($i = 0; $i < count($halAut); $i++) {
						if(strpos($halAutinit[$i][$cstAN], $lsOrg) !== false) {
							$halAut[$i]["mailDom"] = $domMel;
							$aut = $i + 1;
							$halAut[$i]["mail"] = $melAut;
						}
					}
					$orgAff = str_replace($melAut, "", $orgAff);
					$orgAff = trim(str_replace(array(".  .", ". ."), "", $orgAff));
					$tabPay = explode(",", $orgAff);
					$payAff = trim($tabPay[count($tabPay) - 1]);
					//Si le dernier caractère du pays est un point, le retirer
					if(substr($payAff, -1) == ".") {$payAff = substr($payAff, 0, (strlen($payAff) - 1));}
					if(array_key_exists($payAff, $countries)) {$nomAff[$iAff]['pays'] = strtoupper($countries[$payAff]);}
					//echo $melAut.' - '.$orgAff.' - '.$payAff.'<br>';
				}else{
					//Si présence de plus de 3 termes séparés par des virgules, le dernier est très certainement le pays
					$tabOrg = explode(",", $orgAff);
					if(count($tabOrg) > 3) {
						$payAff = trim($tabOrg[count($tabOrg) - 1]);
						//Si le dernier caractère du pays est un point, le retirer
						if(substr($payAff, -1) == ".") {$payAff = substr($payAff, 0, (strlen($payAff) - 1));}
						if($nomAff[$iAff]['pays'] == "" && array_key_exists($payAff, $countries)) {$nomAff[$iAff]['pays'] = strtoupper($countries[$payAff]);}
					}
				}
				$nomAff[$iAff]['org'] = $orgAff;
				//Présence de rawAffs ?
				$extRaw = '';
				if (isset($rawArray[$iAff])) {
					//Recherche du terme 'UMR, xxxx' ou 'umr, xxxx'
					if (preg_match('/UMR, [0-9]{4}/', strtoupper($rawArray[$iAff]), $match)) {$rawArray[$iAff] = str_ireplace('UMR, ', 'UMR ', $rawArray[$iAff]);}
					$tabRaw = explode(",", $rawArray[$iAff]);
					foreach ($tabRaw as $raw) {
						//Recherche du terme 'UMR xxxx' ou 'umr xxxx'
						if (preg_match('/UMR [0-9]{4}/', strtoupper($raw), $match)) {$nomAff[$iAff]['org'] .= ', '.$match[0];}
						//Recherche du terme 'UMR CNRS xxxx' ou 'umr cnrs xxxx'
						if (preg_match('/UMR CNRS [0-9]{4}/', strtoupper($raw), $match)) {$nomAff[$iAff]['org'] .= ', '.str_ireplace('CNRS ', '', $match[0]);}
						//Recherche du terme 'UMRxxxx' ou 'umrxxxx'
						if (preg_match('/UMR[0-9]{4}/', strtoupper($raw), $match)) {$nomAff[$iAff]['org'] .= ', '.$match[0];}
						//Recherche du terme 'UPR xxxx' ou 'upr xxxx'
						if (preg_match('/UPR [0-9]{4}/', strtoupper($raw), $match)) {$nomAff[$iAff]['org'] .= ', '.$match[0];}
						//Recherche du terme 'UPRxxxx' ou 'uprxxxx'
						if (preg_match('/UPR[0-9]{4}/', strtoupper($raw), $match)) {$nomAff[$iAff]['org'] .= ', '.$match[0];}
						//Recherche du terme 'UR xxxx' ou 'ur xxxx'
						if (preg_match('/UR [0-9]{4}/', strtoupper($raw), $match)) {$nomAff[$iAff]['org'] .= ', '.$match[0];}
						//Recherche du terme 'URxxxx' ou 'urxxxx'
						if (preg_match('/UR[0-9]{4}/', strtoupper($raw), $match)) {$nomAff[$iAff]['org'] .= ', '.$match[0];}
						//Recherche du terme 'U xxxx' ou 'u xxxx'
						if (preg_match('/U [0-9]{4}/', strtoupper($raw), $match)) {$nomAff[$iAff]['org'] .= ', '.$match[0];}
						//Recherche du terme 'Uxxxx' ou 'uxxxx'
						if (preg_match('/U[0-9]{4}/', strtoupper($raw), $match)) {$nomAff[$iAff]['org'] .= ', '.$match[0];}
					}
				}
			}
			if(isset($nomAff[$iAff]['pays']) && $nomAff[$iAff]['pays'] == "") {
				if($elt->nodeName == "desc") {
					foreach($elt->childNodes as $b) {//Recherche noeud address
						if($b->nodeName == "address") {
							foreach($b->childNodes as $c) {//Recherche noeud country
								if($c->nodeName == "country") {
									if($c->hasAttribute("key")) {$nomAff[$iAff]['pays'] = $c->getAttribute("key");}
								}
							}
						}
					}
				}
			}
		}
		$iAff++;
	}
	//var_dump($nomAff);
	//var_dump($halAut);

	$nbAff = $iAff;
	$iAff = 0;//Servira aussi comme compteur d'id structures des affiliations trouvé(s)

	for($i = 0; $i < count($nomAff); $i++) {
		progression($cpt, $nbAff, 'cpt3a-'.$idFic, $iPro, 'affiliation');
		$code = $nomAff[$i]['org'];
		$type = $nomAff[$i]['type'];
		$pays = $nomAff[$i]['pays'];
		if($pays != "") {$special = "%20AND%20country_s:%22".strtolower($pays)."%22";}else{$special = "";}
		//if(strtolower($pays) != "fr") {$special .= "%20%20AND%20type_s:(institution%20OR%20regroupinstitution%20OR%20regrouplaboratory)";}
		$trouve = 0;//Test pour savoir si la 1ère méthode a permis de trouver un id de structure
		//Si présence d'un terme entre crochets, il faut isoler ce terme et l'ajouter comme recherche prioritaire > ajout au début du tableau
		$crochet = "";
		if(strpos($code, "[") !== false && strpos($code, "]") !== false) {
			$tabCro = explode("[", $code);
			$croTab = explode("]", $tabCro[1]);
			$crochet = $croTab[0];
		}
		$code = str_replace(array("[", "]", "&", "="), array("", "", "", "%3D"), $code);
		
		//Suppression du terme 'Univ'
		$code = str_ireplace(array("Univ ", "Univ. ", "Univ, ", "Univ., "), array("", "", ",", ","), $code);
		
		//Méthode via le ROR
		if (!empty($nomAff[$i]['ror'])) {
			$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=ror_s:%22".$nomAff[$i]['ror']."%22&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s%20desc";
			$reqAff = str_replace(" ", "%20", $reqAff);
			echo $cstHR.$reqAff.'">URL requête affiliations (méthode ROR) HAL</a>';
			$contAff = file_get_contents($reqAff);
			$resAff = json_decode($contAff);
			$numFound = 0;
			if(isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
			if($numFound != 0)  {
				$halAff[$iAff][$cstDI] = $resAff->response->docs[0]->docid;
				$halAff[$iAff][$cstLA] = $nomAff[$i][$cstLA];
				$halAff[$iAff][$cstVA] = $resAff->response->docs[0]->valid_s;
				$halAff[$iAff][$cstNA] = $resAff->response->docs[0]->name_s;
				if(isset($resAff->response->docs[0]->acronym_s)) {$acronym = " [".$resAff->response->docs[0]->acronym_s."], ";}else{$acronym = ", ";}
				if(isset($resAff->response->docs[0]->country_s)) {$country = ", ".$resAff->response->docs[0]->country_s;}else{$country = "";}
				$halAff[$iAff][$cstNC] = $resAff->response->docs[0]->docid." ~ ".$resAff->response->docs[0]->name_s.$acronym.$resAff->response->docs[0]->type_s.$country;
				$halAff[$iAff][$cstFN] = "";
				$halAff[$iAff][$cstLN] = "";
				$halAff[$iAff]['ror'] = "oui";
				$iAff++;
				$trouve++;
			}
			echo ' > '.$numFound.'<br>';
		}
		
		if($trouve == 0) {
			//1ère méthode, sur le référentiel des structures et uniquement sur l'acronyme

			//Si présence d'au moins 3 virgules > test sur chacun des éléments sauf les 2 derniers qui correspondent souvent à la ville et au pays
			//Mais, si pas de virgule ou nombre de virgules < 3, il faut naturellement conserver le dernier élément
			$cptCode = 0;
			$tabCode = explode(",", $code);
			if($crochet != "") {array_unshift($tabCode, $crochet);}
			foreach($tabCode as $test) {
				$test = urlencode(str_replace(" ", "+", trim($test)));
				if(count($tabCode) > 2) {$max = count($tabCode) - 2;}else{$max = count($tabCode);}
				if($cptCode <= $max && !in_array($test, $anepasTester)) {						
					$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=acronym_t:".$test."%20OR%20acronym_sci:".$test."%20AND%20valid_s:(VALID%20OR%20OLD)".$special."&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s%20desc,docid%20asc";
					$reqAff = str_replace(" ", "%20", $reqAff);
					echo $cstHR.$reqAff.'">URL requête affiliations (1ère méthode) HAL</a>';
					//echo $reqAff.'<br>';
					$contAff = file_get_contents($reqAff);
					$resAff = json_decode($contAff);
					$numFound = 0;
					if(isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
					if($numFound != 0) {
						//foreach($resAff->response->docs as $affil) { > Non, on ne prend que la première affiliation trouvée
							$halAff[$iAff][$cstDI] = $resAff->response->docs[0]->docid;
							$halAff[$iAff][$cstLA] = $nomAff[$i][$cstLA];
							$halAff[$iAff][$cstVA] = $resAff->response->docs[0]->valid_s;
							$halAff[$iAff][$cstNA] = $resAff->response->docs[0]->name_s;
							if(isset($resAff->response->docs[0]->acronym_s)) {$acronym = " [".$resAff->response->docs[0]->acronym_s."], ";}else{$acronym = ", ";}
							if(isset($resAff->response->docs[0]->country_s)) {$country = ", ".$resAff->response->docs[0]->country_s;}else{$country = "";}
							$halAff[$iAff][$cstNC] = $resAff->response->docs[0]->docid." ~ ".$resAff->response->docs[0]->name_s.$acronym.$resAff->response->docs[0]->type_s.$country;
							$halAff[$iAff][$cstFN] = "";
							$halAff[$iAff][$cstLN] = "";
							$halAff[$iAff]['ror'] = "non";
							$iAff++;
							$trouve++;
							echo ' > '.$numFound.'<br>';
							break;
						//}
					}
					echo ' > '.$numFound.'<br>';
				}
				$cptCode++;
			}
		}
		
		if($trouve == 0) {
			//2ème méthode > avec le référentiel HAL des structures avec le type d'institution
			
			//Si présence d'au moins 3 virgules > test sur chacun des éléments sauf les 2 derniers qui correspondent souvent à la ville et au pays
			//Mais, si pas de virgule ou nombre de virgules < 3, il faut naturellement conserver le dernier élément
			$cptCode = 0;
			$tabCode = explode(",", $code);
			foreach($tabCode as $test) {
				$test = urlencode(str_replace(" ", "+", trim($test)));
				if(count($tabCode) > 2) {$max = count($tabCode) - 2;}else{$max = count($tabCode);}
				if($cptCode <= $max && !in_array($test, $anepasTester)) {
					$typeSpe = "";
					if($special != "") {//Dans HAL, on signale le plus souvent des institutions étrangères, pas des labos
						//if(strpos($special, "fr") === false) {$typeSpe = "%20AND%20type_s:(institution%20OR%20regroupinstitution%20OR%20regrouplaboratory)";}else{$typeSpe = "%20AND%20type_s:".urlencode($type);}
						$typeSpe = "%20AND%20type_s:".urlencode($type);
					}
					$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=(name_t:".$test."%20OR%20code_t:".$test."%20OR%20acronym_t:".$test.")".$typeSpe."%20AND%20valid_s:(VALID%20OR%20OLD)".$special."&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s desc,docid asc";
					$reqAff = str_replace(" ", "%20", $reqAff);
					echo $cstHR.$reqAff.'">URL requête affiliations (2ème méthode) HAL</a>';
					//echo $reqAff.'<br>';
					$contAff = file_get_contents($reqAff);
					$resAff = json_decode($contAff);
					$numFound = 0;
					if(isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
					if($numFound != 0) {
						//foreach($resAff->response->docs as $affil) { > Non, on ne prend que la première affiliation trouvée
							$halAff[$iAff][$cstDI] = $resAff->response->docs[0]->docid;
							$halAff[$iAff][$cstLA] = $nomAff[$i][$cstLA];
							$halAff[$iAff][$cstVA] = $resAff->response->docs[0]->valid_s;
							$halAff[$iAff][$cstNA] = $resAff->response->docs[0]->name_s;
							if(isset($resAff->response->docs[0]->acronym_s)) {$acronym = " [".$resAff->response->docs[0]->acronym_s."], ";}else{$acronym = ", ";}
							if(isset($resAff->response->docs[0]->country_s)) {$country = ", ".$resAff->response->docs[0]->country_s;}else{$country = "";}
							$halAff[$iAff][$cstNC] = $resAff->response->docs[0]->docid." ~ ".$resAff->response->docs[0]->name_s.$acronym.$resAff->response->docs[0]->type_s.$country;
							$halAff[$iAff][$cstFN] = "";
							$halAff[$iAff][$cstLN] = "";
							$halAff[$iAff]['ror'] = "non";
							$iAff++;
							$trouve++;
							echo ' > '.$numFound.'<br>';
							break;
						//}
						echo ' > '.$numFound.'<br>';
					}else{
						echo ' > '.$numFound.'<br>';
						//3ème méthode > avec le référentiel HAL des structures sans le type d'institution uniquement si country_s = 'fr'
						if($special != "") {//Dans HAL, on signale le plus souvent des institutions étrangères, pas des labos
							if(strpos($special, "fr") !== false) {
								$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=(name_t:".$test."%20OR%20code_t:".$test."%20OR%20acronym_t:".$test.")%20AND%20valid_s:(VALID%20OR%20OLD)".urlencode($special)."&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s desc,docid asc";
								$reqAff = str_replace(" ", "%20", $reqAff);
								echo $cstHR.$reqAff.'">URL requête affiliations (3ème méthode) HAL</a>';
								//echo $reqAff.'<br>';
								$contAff = file_get_contents($reqAff);
								$resAff = json_decode($contAff);
								$numFound = 0;
								if(isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
								if($numFound != 0) {
									//foreach($resAff->response->docs as $affil) { > Non, on ne prend que la première affiliation trouvée
										$halAff[$iAff][$cstDI] = $resAff->response->docs[0]->docid;
										$halAff[$iAff][$cstLA] = $nomAff[$i][$cstLA];
										$halAff[$iAff][$cstVA] = $resAff->response->docs[0]->valid_s;
										$halAff[$iAff][$cstNA] = $resAff->response->docs[0]->name_s;
										if(isset($resAff->response->docs[0]->acronym_s)) {$acronym = " [".$resAff->response->docs[0]->acronym_s."], ";}else{$acronym = ", ";}
										if(isset($resAff->response->docs[0]->country_s)) {$country = ", ".$resAff->response->docs[0]->country_s;}else{$country = "";}
										$halAff[$iAff][$cstNC] = $resAff->response->docs[0]->docid." ~ ".$resAff->response->docs[0]->name_s.$acronym.$resAff->response->docs[0]->type_s.$country;
										$halAff[$iAff][$cstFN] = "";
										$halAff[$iAff][$cstLN] = "";
										$halAff[$iAff]['ror'] = "non";
										$iAff++;
										$trouve++;
										echo ' > '.$numFound.'<br>';
										break;
									//}
								}
								echo ' > '.$numFound.'<br>';
							}
						}
					}
				}
				$cptCode++;
			}
		}
		
		
		//4ème méthode, toujours sur le référentiel des structures mais avec une autre requête
		if($trouve == 0) {
			//Si présence d'au moins 3 virgules > test sur chacun des éléments sauf les 2 derniers qui correspondent souvent à la ville et au pays
			//Mais, si pas de virgule ou nombre de virgules < 3, il faut naturellement conserver le dernier élément
			$code = str_replace(' - ', ',', $code);
			if(strpos($code, ",") !== false) {$cptCode = 1;}else{$cptCode = 0;}
			$tabCode = explode(",", $code);
			foreach($tabCode as $test) {
				$test = str_ireplace(array(" CNRS", " INSERM", " INRAE", " INRIA", " "), array("", "", "", "", "+"), trim($test));
				$test = urlencode($test);
				if(count($tabCode) > 2) {$max = count($tabCode) - 2;}else{$max = count($tabCode);}
				if($cptCode <= $max && !in_array($test, $anepasTester)) {
					//$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=(name_t:%22".$test."%22%20OR%20name_t:(".$test.")%20OR%20code_t:%22".$test."%22%20OR%20acronym_t:%22".$test."%22%20OR%20acronym_sci:%22".$test."%22)%20AND%20type_s:".$type."%20AND%20valid_s:(VALID%20OR%20OLD)&fl=docid,valid_s,name_s,type_s&sort=valid_s%20desc,docid%20asc";
					$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=(name_t:%22".$test."%22%20OR%20name_t:(".$test.")%20OR%20code_t:%22".$test."%22%20OR%20acronym_t:%22".$test."%22%20OR%20acronym_sci:%22".$test."%22)%20AND%20valid_s:(VALID%20OR%20OLD)".$special."&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s%20desc,docid%20asc";
					$reqAff = str_replace(" ", "%20", $reqAff);
					echo $cstHR.$reqAff.'">URL requête affiliations (4ème méthode) HAL</a>';
					//echo $reqAff.'<br>';
					$contAff = file_get_contents($reqAff);
					$resAff = json_decode($contAff);
					$numFound = 0;
					if(isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
					if($numFound != 0) {
						//foreach($resAff->response->docs as $affil) { > Non, on ne prend que la première affiliation trouvée
							$halAff[$iAff][$cstDI] = $resAff->response->docs[0]->docid;
							$halAff[$iAff][$cstLA] = $nomAff[$i][$cstLA];
							$halAff[$iAff][$cstVA] = $resAff->response->docs[0]->valid_s;
							$halAff[$iAff][$cstNA] = $resAff->response->docs[0]->name_s;
							if(isset($resAff->response->docs[0]->acronym_s)) {$acronym = " [".$resAff->response->docs[0]->acronym_s."], ";}else{$acronym = ", ";}
							if(isset($resAff->response->docs[0]->country_s)) {$country = ", ".$resAff->response->docs[0]->country_s;}else{$country = "";}
							$halAff[$iAff][$cstNC] = $resAff->response->docs[0]->docid." ~ ".$resAff->response->docs[0]->name_s.$acronym.$resAff->response->docs[0]->type_s.$country;
							$halAff[$iAff][$cstFN] = "";
							$halAff[$iAff][$cstLN] = "";
							$halAff[$iAff]['ror'] = "non";
							$iAff++;
							$trouve++;
							echo ' > '.$numFound.'<br>';
							break;
						//}
					}
					echo ' > '.$numFound.'<br>';
				}
				$cptCode++;
			}
		}
			
		//5ème méthode, si les 4 précédentes n'ont pas abouti > avec le référentiel HAL des notices
		if($trouve == 0) {
			//On récupère tout d'abord l'année de la publication
			$annee = "";
			$anns = $xml->getElementsByTagName("date");
			foreach($anns as $ann) {
				if($ann->hasAttribute("type") && $ann->getAttribute("type") == "datePub") {$annee = $ann->nodeValue;}
			}
			if($annee != "") {
				for($j = 0; $j < count($halAut); $j++) {
					if($halAut[$j][$cstAN] == $nomAff[$i][$cstLA]) {//On ne s'intéresse qu'aux auteurs concernés par cette référence d'affiliation
						$firstName = $halAut[$j]['firstName'];
						$lastName = $halAut[$j]['lastName'];
						$facetSep = $lastName.' '.$firstName;
						$reqAff = "https://api.archives-ouvertes.fr/search/index/?q=authLastName_sci:%22".urlencode($lastName)."%22%20AND%20authFirstName_sci:%22".urlencode($firstName)."%22&fq=-labStructValid_s:INCOMING%20OR%20(acronym_sci:%22".urlencode($code)."%22%20OR%20structName_sci:%22".urlencode($code)."%22%20OR%20structCode_sci:%22".urlencode($code)."%22)&fl=structPrimaryHasAlphaAuthIdHal_fs,authId_i,authLastName_s,authFirstName_s&sort=abs(sub(producedDateY_i,".$annee."))%20asc";
						$reqAff = str_replace(" ", "%20", $reqAff);
						echo $cstHR.$reqAff.'">URL requête affiliations (5ème méthode) HAL</a>';
						//echo $reqAff.'<br>';
						$contAff = file_get_contents($reqAff);
						$resAff = json_decode($contAff);
						$numFound = 0;
						if(isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
						if($numFound != 0) {
							foreach($resAff->response->docs as $affil) {
								foreach($affil->structPrimaryHasAlphaAuthIdHal_fs as $fSep) {
									if(strpos($fSep, $facetSep) !== false) {
										$fSepTab = explode('_', $fSep);
										$ajout = "oui";
										for($k = 0; $k < count($halAff); $k++) {
											if(intval($fSepTab[2]) == $halAff[$k][$cstDI] && $firstName == $halAff[$k][$cstFN] && $lastName == $halAff[$k][$cstLN]) {$ajout = "non";}
										}
										if($ajout == "oui") {
											//VALID ou OLD ?
											$reqVoO = "https://api.archives-ouvertes.fr/ref/structure/?q=docid:%22".$fSepTab[2]."%22%20AND%20-valid_s:%22INCOMING%22&fl=*&rows=1000&fl=docid,valid_s,name_s,type_s,country_s,acronym_s";
											$reqVoO = str_replace(" ", "%20", $reqVoO);
											$contVoO = file_get_contents($reqVoO);
											$resVoO = json_decode($contVoO);
											$halAff[$iAff][$cstDI] = intval($fSepTab[2]);
											$halAff[$iAff][$cstLA] = $nomAff[$i][$cstLA];
											$halAff[$iAff][$cstVA] = $resVoO->response->docs[0]->valid_s;
											$halAff[$iAff][$cstNA] = $fSepTab[4];
											if(isset($resVoO->response->docs[0]->acronym_s)) {$acronym = " [".$resVoO->response->docs[0]->acronym_s."], ";}else{$acronym = ", ";}
											if(isset($resVoO->response->docs[0]->country_s)) {$country = ", ".$resVoO->response->docs[0]->country_s;}else{$country = "";}
											$halAff[$iAff][$cstNC] = $resVoO->response->docs[0]->docid." ~ ".$resVoO->response->docs[0]->name_s.$acronym.$resVoO->response->docs[0]->type_s.$country;
											$halAff[$iAff][$cstFN] = $firstName;
											$halAff[$iAff][$cstLN] = $lastName;
											$halAff[$iAff]['ror'] = "non";
											$iAff++;
											$trouve++;
											echo ' > '.$numFound.'<br>';
											break 2;
										}
									}
								}
							}
						}
						echo ' > '.$numFound.'<br>';
					}
				}
			}
		}

		if($trouve == 0) {
			//Affiliation sans recherche possible > on réinitialise cette affiliation pour les auteurs concernés
			for($j = 0; $j < count($halAut); $j++) {
				if($halAut[$j][$cstAN] != "" && stripos($halAut[$j][$cstAN], $nomAff[$i][$cstLA]) !== false) {
					$halAut[$j][$cstAN] = str_replace($nomAff[$i][$cstLA], '', $halAut[$j][$cstAN]);
				}
			}
		}
		$cpt++;
	}

	echo '</span></span>';//Fin bloc affiliations
	echo $iAff.' id structures des affiliations trouvé(s)';
}
//var_dump_pre($halAff);
echo '<script>';
echo 'document.getElementById(\'cpt3a-'.$idFic.'\').style.display = \'none\';';
echo '</script>';

echo '								</div>';
echo '						</div> <!-- end card-body -->';
echo '				</div>';
echo '		</div>';
//echo '</div> <!-- .row -->';
//Fin étape 3a
?>
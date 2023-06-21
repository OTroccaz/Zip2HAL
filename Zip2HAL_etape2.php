<?php
/*
 * Zip2HAL - Importez vos publications dans HAL - Import your publications into HAL
 *
 * Copyright (C) 2023 Olivier Troccaz (olivier.troccaz@cnrs.fr) and Laurent Jonchère (laurent.jonchere@univ-rennes.fr)
 * Released under the terms and conditions of the GNU General Public License (https://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * Etape 2 - Stage 2
 */
 
//Etape 2 - Recherche des idHAL des auteurs				

//echo '<div class="row">';
echo '    <div class="col-md-6">';
echo '        <div class="card ribbon-box">';
echo '            <div class="card-body">';
echo '                <div class="ribbon ribbon-success float-right">Étape 2</div>';
echo '                <h5 class="text-success mt-0">Recherche des idHAL et docid des auteurs</h5>';
echo '                <div class="ribbon-content">';

$cpt = 1;
$iAut = 0;
$preAut = array();//Prénoms des auteurs
$nomAut = array();//Noms des auteurs
$affAut = array();//Affiliation des auteurs
$xmlIds = array();//IdHALs trouvés
$xmlIdi = array();//IdHALi trouvés
$melAut = array();//Emails trouvés (domaine)
$adrAut = array();//Emails trouvés (adresse)
$halAut = array();
$iOrcid = array();//ORCID
$iResid = array();//ResearcherID
$halAutinit = array();
$tabIdHAL = array();//Si plusieurs idHAL remontés pour un même auteur

include "./Zip2HAL_constantes.php";

echo '<div id=\'cpt2-'.$idFic.'\'></div>';

if(isset($typDbl) && ($typDbl == "HALCOLLTYP" || $typDbl == "HALTYP")) {//Doublon de type TYP > inutile d'effectuer les recherches
	echo 'Recherche inutile car c\'est une notice doublon';
}else{
	//Début bloc idHal/docid
	echo '<span><a style="cursor:pointer;" class="text-primary" onclick="afficacherRec(\'2\', '.$idFic.')";>Recherche idHAL/docid</a><br>';
	echo '<span id="Rrec-2-'.$idFic.'" style="display: none;">';
	
	//Recherche des noeuds auteur vide pour les supprimer
	$auts = $xml->getElementsByTagName("author");
	foreach($auts as $aut) {
		$prenom = "";
		$nom = "";
		foreach($aut->childNodes as $elt) {
			if($elt->nodeName == "persName") {
				foreach($elt->childNodes as $per) {
					if($per->nodeName == "forename") {
						$prenom = $per->nodeValue;
					}
					if($per->nodeName == "surname") {
						$nom = $per->nodeValue;
					}
				}
			}
		}
		if(trim($prenom) == "" && trim($nom) == "") {
			$aut->parentNode->removeChild($aut);
			$xml->save($nomfic);
		}
	}
	
	$auts = $xml->getElementsByTagName("author");
	foreach($auts as $aut) {
		//Initialisation des variables
		$xmlIds[$iAut] = "";
		$xmlIdi[$iAut] = "";
		$affAut[$iAut] = "";
		$melAut[$iAut] = "";
		$adrAut[$iAut] = "";
		$iOrcid[$iAut] = "";
		$iResid[$iAut] = "";
		$rolAut[$iAut] = "";
		
		//Rôle auteur
		$rolAut[$iAut] = $aut->getAttribute("role");
		
		foreach($aut->childNodes as $elt) {
			//Prénom/Nom
			if($elt->nodeName == "persName") {
				foreach($elt->childNodes as $per) {
					if($per->nodeName == "forename") {
						$preAut[$iAut] = $per->nodeValue;
					}
					if($per->nodeName == "surname") {
						$nomAut[$iAut] = $per->nodeValue;
					}
				}
			}
			//IdHAL
			$notation = "notation";
			if($elt->nodeName == "idno" && $elt->hasAttribute("type") && $elt->getAttribute("type") == "idhal") {
				if($elt->hasAttribute($notation) && $elt->getAttribute($notation) == "string") {$xmlIds[$iAut] = $elt->nodeValue;}
				if($elt->hasAttribute($notation) && $elt->getAttribute($notation) == "numeric") {$xmlIdi[$iAut] = $elt->nodeValue;}
			}
			//Email
			if($elt->nodeName == "email") {
				//Mail réel ou juste le domaine ?
				$melAut[$iAut] = (strpos($elt->nodeValue, '@') !== false) ? str_replace('@', '', strstr($elt->nodeValue, '@')) : $elt->nodeValue;
				$adrAut[$iAut] = $elt->nodeValue;
			}
			//ORCID
			if($elt->nodeName == "idno" && $elt->hasAttribute("type") && $elt->getAttribute("type") == "https://orcid.org/") {
				$iOrcid[$iAut] = $elt->nodeValue;
			}
			//ResearcherID
			if($elt->nodeName == "idno" && $elt->hasAttribute("type") && $elt->getAttribute("type") == "http://www.researcherid.com/rid/") {
				$iResid[$iAut] = $elt->nodeValue;
			}
			//Affiliations
			if($elt->nodeName == "affiliation" && $elt->hasAttribute("ref")) {$affAut[$iAut] .= $elt->getAttribute("ref").'~';}
		}
		$iAut++;
	}
	//var_dump($preAut);
	//var_dump($nomAut);
	//var_dump($melAut);
	//var_dump($affAut);
	
	$nbAut = $iAut;
	$iAut = 0;
	$cptiHi = 0;//Compteur d'idHal_i trouvé(s)
	$cptdoc = 0;//Compteur de docid trouvé(s)
	$docid = "";
	$nbdocid = 0;

	for($i = 0; $i < count($preAut); $i++) {
		progression($cpt, $nbAut, 'cpt2-'.$idFic, $iPro, 'auteur');
		$firstName = $preAut[$i];
		$lastName = $nomAut[$i];
		$affilName = $affAut[$i];
		//Initialisation des variables du tableau
		$halAut[$iAut][$cstFN] = $firstName;
		$halAut[$iAut][$cstLN] = $lastName;
		$halAut[$iAut][$cstAN] = $affilName;
		$halAut[$iAut]['xmlIdi'] = $xmlIdi[$i];
		$halAut[$iAut]['xmlIds'] = $xmlIds[$i];
		$halAut[$iAut][$cstII] = "";
		$halAut[$iAut][$cstIS] = "";
		$halAut[$iAut][$cstMD] = $melAut[$i];
		$halAut[$iAut]['mail'] = $adrAut[$i];
		$halAut[$iAut][$cstDI] = "";
		$halAut[$iAut]['orcid'] = "";
		$halAut[$iAut]['resid'] = "";
		$halAut[$iAut]['rolaut'] = $rolAut[$i];
		$halAut[$iAut]['fullName'] = "";
		$halAut[$iAut]['domMail'] = "";
		
		$firstNameT = strtolower(wd_remove_accents($firstName));
		$lastNameT = strtolower(wd_remove_accents($lastName));
		//Si prénom composé, on ne garde que les initiales et on ajuste le test pour qu'il porte aussi uniquement sur l'initiale du premier prénom (J.-B. Le Cam > (j-b* OR j*))
		$testPre = "";
		if(strpos($firstNameT, "-") !== false) {
			$firstNameT = strtolower(prenomCompInit($firstNameT));
			$testPre = "(".$firstNameT."*%20OR%20".substr($firstNameT, 0, 1)."*)";
			$testPre = str_replace(".", "", $testPre);
		}else{
			$testPre = str_replace(".", "", $firstNameT);
		}
		
		$trouve = 0;//Test pour savoir si une méthode a permis de trouver un idHAL avant de passer à la suivante
		$trvDoc = "non";//Test pour savoir si une méthode a permis de trouver un idHAL avant de passer à la suivante
		
		//Tester l'existence d'un ORCID
		if(isset($iOrcid[$iAut]) && $iOrcid[$iAut] != "") {
			$reqOrc = "https://api.archives-ouvertes.fr/ref/author/?q=orcidId_s:".$iOrcid[$iAut]."%20AND%20valid_s:%22PREFERRED%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s%20desc,docid%20asc";
			$reqOrc = str_replace(" ", "%20", $reqOrc);
			echo '<a target="_blank" href="'.$reqOrc.'">URL requête auteurs HAL (méthode ORCID)</a><br>';
			$contAut = file_get_contents($reqOrc);
			$resAut = json_decode($contAut);
			$numFound = 0;
			if(isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
			$docid = "";
			$nbdocid = 0;
			$iHi = "non";//Test pour savoir si un idHal_i a été trouvé
			if($numFound != 0) {
				//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
				$testMel = "oui";//Ok par défaut
				if($halAut[$iAut][$cstMD] != "") {
					$melXML = $halAut[$iAut][$cstMD];
					$tabMelXML = explode(".", $melXML);
					$testXML = $tabMelXML[count($tabMelXML) - 1];
					if(isset($resAut->response->docs[0]->emailDomain_s[0])) {$melHAL = $resAut->response->docs[0]->emailDomain_s[0];}else{$melHAL = "";}
					$tabMelHAL = explode(".", $melHAL);
					$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
					if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
					//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
				}
				if(isset($resAut->response->docs[0]->idHal_i) && $resAut->response->docs[0]->idHal_i != 0 && strpos($resAut->response->docs[0]->fullName_s, ",") === false) {
					if($testMel == "oui") {
						$halAut[$iAut][$cstFN] = $firstName;
						$halAut[$iAut][$cstLN] = $lastName;
						$halAut[$iAut][$cstAN] = $affilName;
						if(isset($resAut->response->docs[0]->idHal_i)) {$halAut[$iAut][$cstII] = $resAut->response->docs[0]->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
						if(isset($resAut->response->docs[0]->idHal_s)) {$halAut[$iAut][$cstIS] = $resAut->response->docs[0]->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
						if(isset($resAut->response->docs[0]->emailDomain_s[0])) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($resAut->response->docs[0]->emailDomain_s[0], '@'));}else{$halAut[$iAut][$cstMD] = "";}
						if(isset($resAut->response->docs[0]->docid)) {$halAut[$iAut][$cstDI] = $resAut->response->docs[0]->docid; $trvDoc = "oui";}
						$iHi = "oui";
						$cptiHi++;
						$trouve++;
						$halAut[$iAut]['orcid'] = "oui";
					}
				}else{//Pas d'idHal
					if($testMel == "oui") {
						$docid .= $resAut->response->docs[0]->docid;
						if (isset($resAut->response->docs[0]->fullName_s) && !empty($resAut->response->docs[0]->fullName_s)) {$halAut[$iAut]['fullName'] = $resAut->response->docs[0]->fullName_s;}
						if (isset($resAut->response->docs[0]->emailDomain_s[0]) && !empty($resAut->response->docs[0]->emailDomain_s[0])) {$halAut[$iAut]['domMail'] = $resAut->response->docs[0]->emailDomain_s[0];}
						$nbdocid++;
					}
				}
			}
			if($iHi == "non" && $docid != "" && $nbdocid == 1 && $trvDoc == "non") {//Un seul docid trouvé
				$halAut[$iAut][$cstFN] = $firstName;
				$halAut[$iAut][$cstLN] = $lastName;
				$halAut[$iAut][$cstAN] = $affilName;
				$halAut[$iAut][$cstII] = "";
				$halAut[$iAut][$cstIS] = "";
				$halAut[$iAut][$cstMD] = "";
				$halAut[$iAut][$cstDI] = $docid;
				$cptdoc++;
				$trouve++;
				//echo($firstName.' '.$lastName.' : '.$docid);
			}
		}
		
		//Si pas d'ORCID, tester l'existence d'un ResearcherID
		if(isset($iResid[$iAut]) && $iResid[$iAut] != "" && $halAut[$iAut]['orcid'] != "oui") {
			$reqRes = "https://api.archives-ouvertes.fr/ref/author/?q=researcherid_id:".$iResid[$iAut]."%20AND%20valid_s:%22PREFERRED%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s%20desc,docid%20asc";
			$reqRes = str_replace(" ", "%20", $reqRes);
			echo '<a target="_blank" href="'.$reqRes.'">URL requête auteurs HAL (méthode ResearcherID)</a><br>';
			$contAut = file_get_contents($reqRes);
			$resAut = json_decode($contAut);
			$numFound = 0;
			if(isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
			$docid = "";
			$nbdocid = 0;
			$iHi = "non";//Test pour savoir si un idHal_i a été trouvé
			if($numFound != 0) {
				//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
				$testMel = "oui";//Ok par défaut
				if($halAut[$iAut][$cstMD] != "") {
					$melXML = $halAut[$iAut][$cstMD];
					$tabMelXML = explode(".", $melXML);
					$testXML = $tabMelXML[count($tabMelXML) - 1];
					if(isset($resAut->response->docs[0]->emailDomain_s[0])) {$melHAL = $resAut->response->docs[0]->emailDomain_s[0];}else{$melHAL = "";}
					$tabMelHAL = explode(".", $melHAL);
					$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
					if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
					//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
				}
				if(isset($resAut->response->docs[0]->idHal_i) && $resAut->response->docs[0]->idHal_i != 0 && strpos($resAut->response->docs[0]->fullName_s, ",") === false) {
					if($testMel == "oui") {
						$halAut[$iAut][$cstFN] = $firstName;
						$halAut[$iAut][$cstLN] = $lastName;
						$halAut[$iAut][$cstAN] = $affilName;
						if(isset($resAut->response->docs[0]->idHal_i)) {$halAut[$iAut][$cstII] = $resAut->response->docs[0]->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
						if(isset($resAut->response->docs[0]->idHal_s)) {$halAut[$iAut][$cstIS] = $resAut->response->docs[0]->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
						if(isset($resAut->response->docs[0]->emailDomain_s[0])) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($resAut->response->docs[0]->emailDomain_s[0], '@'));}else{$halAut[$iAut][$cstMD] = "";}
						if(isset($resAut->response->docs[0]->docid)) {$halAut[$iAut][$cstDI] = $resAut->response->docs[0]->docid; $trvDoc = "oui";}
						$iHi = "oui";
						$cptiHi++;
						$trouve++;
						$halAut[$iAut]['resid'] = "oui";
					}
				}else{//Pas d'idHal
					if($testMel == "oui") {
						$docid .= $resAut->response->docs[0]->docid;
						if (isset($resAut->response->docs[0]->fullName_s) && !empty($resAut->response->docs[0]->fullName_s)) {$halAut[$iAut]['fullName'] = $resAut->response->docs[0]->fullName_s;}
						if (isset($resAut->response->docs[0]->emailDomain_s[0]) && !empty($resAut->response->docs[0]->emailDomain_s[0])) {$halAut[$iAut]['domMail'] = $resAut->response->docs[0]->emailDomain_s[0];}
						$nbdocid++;
					}
				}
			}
			if($iHi == "non" && $docid != "" && $nbdocid == 1 && $trvDoc == "non") {//Un seul docid trouvé
				$halAut[$iAut][$cstFN] = $firstName;
				$halAut[$iAut][$cstLN] = $lastName;
				$halAut[$iAut][$cstAN] = $affilName;
				$halAut[$iAut][$cstII] = "";
				$halAut[$iAut][$cstIS] = "";
				$halAut[$iAut][$cstMD] = "";
				$halAut[$iAut][$cstDI] = $docid;
				$cptdoc++;
				$trouve++;
				$trvDoc = "oui";
				//echo($firstName.' '.$lastName.' : '.$docid);
			}
		}
		
		//Méthode CSV OCDHAL
		if($trouve == 0) {
			if(strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
				include('../CrosHAL/CrossIDHAL.php');
			}else{
				include("./CrossIDHAL.php");
			}
			$testUniqK1 = strtolower(normalize($lastName.$firstName));// Nom + prénom complets
			$testUniqK2 = strtolower(normalize($lastName.prenomCompInit($firstName)));//Nom complet + initiales du(des) prénom(s)
			foreach($CrossIDHAL as $elt) {
				if($elt["UniqK"] == $testUniqK1 || $elt["UniqK"] == $testUniqK2) {
					//Tester l'existence d'un idORCID
					if(isset($elt["idORCID"]) && $elt["idORCID"] != "") {
						$reqOrc = "https://api.archives-ouvertes.fr/ref/author/?q=orcidId_s:".$elt["idORCID"]."%20AND%20firstName_t:%22".urlencode($firstName)."%22%20AND%20valid_s:%22PREFERRED%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s%20desc,docid%20asc";
						$reqOrc = str_replace(" ", "%20", $reqOrc);
						echo '<a target="_blank" href="'.$reqOrc.'">URL requête auteurs HAL (méthode ORCID à partir du CSV OCDHAL)</a><br>';
						$contAut = file_get_contents($reqOrc);
						$resAut = json_decode($contAut);
						$numFound = 0;
						if(isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
						$docid = "";
						$nbdocid = 0;
						$iHi = "non";//Test pour savoir si un idHal_i a été trouvé
						if($numFound != 0) {
							//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
							$testMel = "oui";//Ok par défaut
							if($halAut[$iAut][$cstMD] != "") {
								$melXML = $halAut[$iAut][$cstMD];
								$tabMelXML = explode(".", $melXML);
								$testXML = $tabMelXML[count($tabMelXML) - 1];
								if(isset($resAut->response->docs[0]->emailDomain_s[0])) {
									$melHAL = $resAut->response->docs[0]->emailDomain_s[0];
								}else{
									if(isset($elt["Domaine"]) && $elt["Domaine"] != "") {
										$melHAL = $elt["Domaine"];
									}else{
										$melHAL = "";
									}
								}
								$tabMelHAL = explode(".", $melHAL);
								$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
								if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
								//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
							}
							if(isset($resAut->response->docs[0]->idHal_i) && $resAut->response->docs[0]->idHal_i != 0 && strpos($resAut->response->docs[0]->fullName_s, ",") === false) {
								if($testMel == "oui") {
									$halAut[$iAut][$cstFN] = $firstName;
									$halAut[$iAut][$cstLN] = $lastName;
									$halAut[$iAut][$cstAN] = $affilName;
									if(isset($resAut->response->docs[0]->idHal_i)) {$halAut[$iAut][$cstII] = $resAut->response->docs[0]->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
									if(isset($resAut->response->docs[0]->idHal_s)) {$halAut[$iAut][$cstIS] = $resAut->response->docs[0]->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
									if(isset($resAut->response->docs[0]->emailDomain_s[0])) {
										$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($resAut->response->docs[0]->emailDomain_s[0], '@'));
									}else{
										if(isset($elt["Domaine"]) && $elt["Domaine"] != "") {
											$halAut[$iAut][$cstMD] = $elt["Domaine"];
										}else{
											$halAut[$iAut][$cstMD] = "";
										}
									}
									if(isset($resAut->response->docs[0]->docid)) {$halAut[$iAut][$cstDI] = $resAut->response->docs[0]->docid; $trvDoc = "oui";}
									$iHi = "oui";
									$cptiHi++;
									$trouve++;
									$halAut[$iAut]['orcid'] = "oui";
								}
							}else{//Pas d'idHal
								if($testMel == "oui") {
									$docid .= $resAut->response->docs[0]->docid;
									if (isset($resAut->response->docs[0]->fullName_s) && !empty($resAut->response->docs[0]->fullName_s)) {$halAut[$iAut]['fullName'] = $resAut->response->docs[0]->fullName_s;}
									if (isset($resAut->response->docs[0]->emailDomain_s[0]) && !empty($resAut->response->docs[0]->emailDomain_s[0])) {$halAut[$iAut]['domMail'] = $resAut->response->docs[0]->emailDomain_s[0];}
									$nbdocid++;
								}
							}
						}
						if($iHi == "non" && $docid != "" && $nbdocid == 1 && $trvDoc == "non") {//Un seul docid trouvé
							$halAut[$iAut][$cstFN] = $firstName;
							$halAut[$iAut][$cstLN] = $lastName;
							$halAut[$iAut][$cstAN] = $affilName;
							$halAut[$iAut][$cstII] = "";
							$halAut[$iAut][$cstIS] = "";
							$halAut[$iAut][$cstMD] = "";
							$halAut[$iAut][$cstDI] = $docid;
							$cptdoc++;
							$trouve++;
							$trvDoc = "oui";
							break;
							
							//echo($firstName.' '.$lastName.' : '.$docid);
						}
					}else{//Pas d'idORCID > Test idResearcherID
						//Tester l'existence d'un idResearcherID
						if(isset($elt["idResearcherID"]) && $elt["idResearcherID"] != "") {
							$reqOrc = "https://api.archives-ouvertes.fr/ref/author/?q=researcherid_id:".$elt["idResearcherID"]."%20AND%20firstName_t:%22".urlencode($firstName)."%22%20AND%20valid_s:%22PREFERRED%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s%20desc,docid%20asc";
							$reqRes = str_replace(" ", "%20", $reqRes);
							echo '<a target="_blank" href="'.$reqRes.'">URL requête auteurs HAL (méthode ResearcherID à partir du CSV OCDHAL)</a><br>';
							$contAut = file_get_contents($reqRes);
							$resAut = json_decode($contAut);
							$numFound = 0;
							if(isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
							$docid = "";
							$nbdocid = 0;
							$iHi = "non";//Test pour savoir si un idHal_i a été trouvé
							if($numFound != 0) {
								//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
								$testMel = "oui";//Ok par défaut
								if($halAut[$iAut][$cstMD] != "") {
									$melXML = $halAut[$iAut][$cstMD];
									$tabMelXML = explode(".", $melXML);
									$testXML = $tabMelXML[count($tabMelXML) - 1];
									if(isset($resAut->response->docs[0]->emailDomain_s[0])) {
										$melHAL = $resAut->response->docs[0]->emailDomain_s[0];
									}else{
										if(isset($elt["Domaine"]) && $elt["Domaine"] != "") {
											$melHAL = $elt["Domaine"];
										}else{
											$melHAL = "";
										}
									}
									$tabMelHAL = explode(".", $melHAL);
									$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
									if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
									//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
								}
								if(isset($resAut->response->docs[0]->idHal_i) && $resAut->response->docs[0]->idHal_i != 0 && strpos($resAut->response->docs[0]->fullName_s, ",") === false) {
									if($testMel == "oui") {
										$halAut[$iAut][$cstFN] = $firstName;
										$halAut[$iAut][$cstLN] = $lastName;
										$halAut[$iAut][$cstAN] = $affilName;
										if(isset($resAut->response->docs[0]->idHal_i)) {$halAut[$iAut][$cstII] = $resAut->response->docs[0]->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
										if(isset($resAut->response->docs[0]->idHal_s)) {$halAut[$iAut][$cstIS] = $resAut->response->docs[0]->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
										if(isset($resAut->response->docs[0]->emailDomain_s[0])) {
											$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($resAut->response->docs[0]->emailDomain_s[0], '@'));
										}else{
											if(isset($elt["Domaine"]) && $elt["Domaine"] != "") {
												$halAut[$iAut][$cstMD] = $elt["Domaine"];
											}else{
												$halAut[$iAut][$cstMD] = "";
											}
										}
										if(isset($resAut->response->docs[0]->docid)) {$halAut[$iAut][$cstDI] = $resAut->response->docs[0]->docid; $trvDoc = "oui";}
										$iHi = "oui";
										$cptiHi++;
										$trouve++;
										$halAut[$iAut]['resid'] = "oui";
									}
								}else{//Pas d'idHal
									if($testMel == "oui") {
										$docid .= $resAut->response->docs[0]->docid;
										if (isset($resAut->response->docs[0]->fullName_s) && !empty($resAut->response->docs[0]->fullName_s)) {$halAut[$iAut]['fullName'] = $resAut->response->docs[0]->fullName_s;}
										if (isset($resAut->response->docs[0]->emailDomain_s[0]) && !empty($resAut->response->docs[0]->emailDomain_s[0])) {$halAut[$iAut]['domMail'] = $resAut->response->docs[0]->emailDomain_s[0];}
										$nbdocid++;
									}
								}
							}
							if($iHi == "non" && $docid != "" && $nbdocid == 1 && $trvDoc == "non") {//Un seul docid trouvé
								$halAut[$iAut][$cstFN] = $firstName;
								$halAut[$iAut][$cstLN] = $lastName;
								$halAut[$iAut][$cstAN] = $affilName;
								$halAut[$iAut][$cstII] = "";
								$halAut[$iAut][$cstIS] = "";
								$halAut[$iAut][$cstMD] = "";
								$halAut[$iAut][$cstDI] = $docid;
								$cptdoc++;
								$trouve++;
								$trvDoc = "oui";
								break;
								
								//echo($firstName.' '.$lastName.' : '.$docid);
							}
						}else{ //Pas d'idORCID, ni idResearcherID > récupération de l'idHAL
							if($elt["UniqK"] == $testUniqK1) {//Si correspondance sur Nom + prénom complets > on se contente de ce qui a été trouvé dans le CSV OCDHAL
								$halAut[$iAut][$cstFN] = $firstName;
								$halAut[$iAut][$cstLN] = $lastName;
								$halAut[$iAut][$cstAN] = $affilName;
								$halAut[$iAut][$cstII] = $elt["idHALnum"];
								$halAut[$iAut][$cstIS] = $elt["idHAL"];
								$halAut[$iAut][$cstMD] = $elt["Domaine"];
								$halAut[$iAut][$cstDI] = "";
								$cptiHi++;
								$trouve++;
								echo 'idHAL '.$elt["idHAL"].' ('.$elt["idHALnum"].') trouvé avec le CSV OCDHAL<br>';
								break;
							}else{//correspondance sur Nom complet + intiale(s) prénom(s) > vérification de la validité de l'idHAL via HAL et récupération des autres informations
								$reqIdH = "https://api.archives-ouvertes.fr/ref/author/?q=idHal_s:%22".$elt["idHAL"]."%22%20AND%20valid_s:%22PREFERRED%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s,firstName_s,lastName_s&sort=valid_s%20desc,docid%20asc";
								$reqIdH = str_replace(" ", "%20", $reqIdH);
								echo '<a target="_blank" href="'.$reqIdH.'">URL requête idHal HAL (méthode CSV OCDHAL)</a><br>';
								$contIdH = file_get_contents($reqIdH);
								$resIdH = json_decode($contIdH);
								$numFound = 0;
								if(isset($resIdH->response->numFound)) {$numFound=$resIdH->response->numFound;}
								$docid = "";
								$nbdocid = 0;
								$iHi = "non";//Test pour savoir si un idHal_i a été trouvé
								
								if($numFound != 0) {				
									foreach($resIdH->response->docs as $idh) {
										//Vérification intiale de la correspondance des nom et prénom(s)
										if($idh->lastName_s == $lastName && $idh->firstName_s == $firstName) {
											//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
											$testMel = "oui";//Ok par défaut
											if($halAut[$iAut][$cstMD] != "") {
												$melXML = $halAut[$iAut][$cstMD];
												$tabMelXML = explode(".", $melXML);
												$testXML = $tabMelXML[count($tabMelXML) - 1];
												if(isset($idh->emailDomain_s[0])) {$melHAL = $idh->emailDomain_s[0];}else{$melHAL = "";}
												$tabMelHAL = explode(".", $melHAL);
												$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
												if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
												//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
											}
											if(isset($idh->idHal_i) && $idh->idHal_i != 0 && $idh->valid_s == "PREFERRED" && strpos($idh->fullName_s, ",") === false) {
												if($testMel == "oui") {
													//echo $firstName.' '.$lastName.' : '.$author->idHal_i.' -> '.$author->idHal_s.' - ';
													$halAut[$iAut][$cstFN] = $firstName;
													$halAut[$iAut][$cstLN] = $lastName;
													$halAut[$iAut][$cstAN] = $affilName;
													if(isset($idh->idHal_i)) {$halAut[$iAut][$cstII] = $idh->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
													if(isset($idh->idHal_s)) {$halAut[$iAut][$cstIS] = $idh->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
													if(isset($idh->emailDomain_s[0])) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($idh->emailDomain_s[0], '@'));}else{$halAut[$iAut][$cstMD] = "";}
													if(isset($idh->docid)) {$halAut[$iAut][$cstDI] = $idh->docid; $trvDoc = "oui";}
													$iHi = "oui";
													$cptiHi++;
													$trouve++;
													if($numFound > 1) {
														$nbCel = count($tabIdHAL);
														$tabIdHAL[$nbCel][$cstFN] = $firstName;
														$tabIdHAL[$nbCel][$cstLN] = $lastName;
														$tabIdHAL[$nbCel]['reqAut'] = $reqAut;
													}
													break;
												}
											}else{//Pas d'idHal
												if($testMel == "oui") {
													$docid .= $idh->docid;
													if (isset($idh->fullName_s) && !empty($idh->fullName_s)) {$halAut[$iAut]['fullName'] = $idh->fullName_s;}
													if (isset($idh->response->docs[0]->emailDomain_s[0]) && !empty($idh->response->docs[0]->emailDomain_s[0])) {$halAut[$iAut]['domMail'] = $idh->response->docs[0]->emailDomain_s[0];}
													$nbdocid++;
												}
											}
											break;
										}
									}
								}
								if($iHi == "non" && $docid != "" && $nbdocid == 1 && $trvDoc == "non") {//Un seul docid trouvé
									$halAut[$iAut][$cstFN] = $firstName;
									$halAut[$iAut][$cstLN] = $lastName;
									$halAut[$iAut][$cstAN] = $affilName;
									$halAut[$iAut][$cstII] = "";
									$halAut[$iAut][$cstIS] = "";
									$halAut[$iAut][$cstMD] = "";
									$halAut[$iAut][$cstDI] = $docid;
									$cptdoc++;
									$trouve++;
									$trvDoc = "oui";
									//echo($firstName.' '.$lastName.' : '.$docid);
								}
							}
						}
					}
				}
			}
		}
		
		if($trouve == 0 && !empty($melAut[$i])) {
			$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=emailDomain_s:%22".$melAut[$i]."%22%20AND%20fullName_sci:(%22".urlencode($firstName)."%20".urlencode($lastName)."%22%20OR%20%22".urlencode(substr($firstName, 0, 1))."%20".urlencode($lastName)."%22)&rows=1000&fl=emailId_s,idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s%20desc,docid%20asc";
			$reqAut = str_replace(" ", "%20", $reqAut);
			echo '<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (méthode intermédiaire)</a><br>';
			//echo $reqAut.'<br>';
			$contAut = file_get_contents($reqAut);
			$resAut = json_decode($contAut);
			$numFound = 0;
			if(isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
			if($numFound != 0) {
				foreach($resAut->response->docs as $author) {
					//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
					$testMel = "oui";//Ok par défaut
					if($halAut[$iAut][$cstMD] != "") {
						$melXML = $halAut[$iAut][$cstMD];
						$tabMelXML = explode(".", $melXML);
						$testXML = $tabMelXML[count($tabMelXML) - 1];
						if(isset($author->emailDomain_s[0])) {$melHAL = $author->emailDomain_s[0];}else{$melHAL = "";}
						$tabMelHAL = explode(".", $melHAL);
						$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
						if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
						//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
					}
					if(isset($author->idHal_i) && $author->idHal_i != 0 && $author->valid_s == "PREFERRED" && strpos($author->fullName_s, ",") === false) {
						if($testMel == "oui") {
							//echo $firstName.' '.$lastName.' : '.$author->idHal_i.' -> '.$author->idHal_s.' - ';
							$halAut[$iAut][$cstFN] = $firstName;
							$halAut[$iAut][$cstLN] = $lastName;
							$halAut[$iAut][$cstAN] = $affilName;
							if(isset($author->idHal_i)) {$halAut[$iAut][$cstII] = $author->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
							if(isset($author->idHal_s)) {$halAut[$iAut][$cstIS] = $author->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
							if(isset($author->emailDomain_s[0])) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($author->emailDomain_s[0], '@'));}else{$halAut[$iAut][$cstMD] = "";}
							if(isset($author->docid)) {$halAut[$iAut][$cstDI] = $author->docid; $trvDoc = "oui";}
							$iHi = "oui";
							$cptiHi++;
							$trouve++;
							break;
						}
					}else{//Pas d'idHAL trouvé > on recherche avec la même méthode mais sans le critère PREFERRED
						if($testMel == "oui" && $trvDoc == "non") {
							$docid .= $author->docid;
							$halAut[$iAut][$cstDI] = $author->docid;
							if (isset($author->fullName_s) && !empty($author->fullName_s)) {$halAut[$iAut]['fullName'] = $author->fullName_s;}
							if (isset($author->emailDomain_s[0]) && !empty($author->emailDomain_s[0])) {$halAut[$iAut]['domMail'] = $author->emailDomain_s[0];}
							$cptdoc++;
							$nbdocid++;
							$trvDoc = "oui";
						}
					}
				}
			}
		}
		
		if($trouve == 0 && strlen($firstName) > 2) {
			$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=fullName_t:%22".urlencode($firstName)."%20".urlencode($lastName)."%22%20AND%20valid_s:%22PREFERRED%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s%20desc,docid%20asc,fullName_s%20asc";
			$reqAut = str_replace(" ", "%20", $reqAut);
			echo '<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (1ère méthode)</a><br>';
			//echo $reqAut.'<br>';
			$contAut = file_get_contents($reqAut);
			$resAut = json_decode($contAut);
			$numFound = 0;
			if(isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
			if($numFound != 0) {
				foreach($resAut->response->docs as $author) {
					//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
					$testMel = "oui";//Ok par défaut
					if($halAut[$iAut][$cstMD] != "") {
						$melXML = $halAut[$iAut][$cstMD];
						$tabMelXML = explode(".", $melXML);
						$testXML = $tabMelXML[count($tabMelXML) - 1];
						if(isset($author->emailDomain_s[0])) {$melHAL = $author->emailDomain_s[0];}else{$melHAL = "";}
						$tabMelHAL = explode(".", $melHAL);
						$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
						if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
						//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
					}
					if(isset($author->idHal_i) && $author->idHal_i != 0 && $author->valid_s == "PREFERRED" && strpos($author->fullName_s, ",") === false) {
						if($testMel == "oui") {
							//echo $firstName.' '.$lastName.' : '.$author->idHal_i.' -> '.$author->idHal_s.' - ';
							$halAut[$iAut][$cstFN] = $firstName;
							$halAut[$iAut][$cstLN] = $lastName;
							$halAut[$iAut][$cstAN] = $affilName;
							if(isset($author->idHal_i)) {$halAut[$iAut][$cstII] = $author->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
							if(isset($author->idHal_s)) {$halAut[$iAut][$cstIS] = $author->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
							if(isset($author->emailDomain_s[0])) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($author->emailDomain_s[0], '@'));}else{$halAut[$iAut][$cstMD] = "";}
							if(isset($author->docid)) {$halAut[$iAut][$cstDI] = $author->docid; $trvDoc = "oui";}
							$iHi = "oui";
							$cptiHi++;
							$trouve++;
							break;
						}
					}else{//Pas d'idHAL trouvé
						if($testMel == "oui" && $trvDoc == "non") {
							$docid .= $author->docid;
							$halAut[$iAut][$cstDI] = $author->docid;
							if (isset($author->fullName_s) && !empty($author->fullName_s)) {$halAut[$iAut]['fullName'] = $author->fullName_s;}
							if (isset($author->emailDomain_s[0]) && !empty($author->emailDomain_s[0])) {$halAut[$iAut]['domMail'] = $author->emailDomain_s[0];}
							$cptdoc++;
							$nbdocid++;
							$trvDoc = "oui";
							if ($author->valid_s == "PREFERRED" && $numFound == 1) {$trouve++;}//Une forme idHAL est forcément associée à une forme "PREFERRED" et il n'est donc pas possible de trouver d'idHAL avec les autres méthodes
						}
					}
				}
			}
		}
		
		if($trouve == 0) {
			$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=fullName_sci:(%22".urlencode($firstNameT)."%20".urlencode($lastNameT)."%22%20OR%20%22".urlencode(substr($firstNameT, 0, 1))."%20".urlencode($lastNameT)."%22)%20AND%20valid_s:%22PREFERRED%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s%20desc,docid%20asc,fullName_s%20asc";
			$reqAut = str_replace(" ", "%20", $reqAut);
			echo '<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (2ème méthode)</a><br>';
			//echo $reqAut.'<br>';
			$contAut = file_get_contents($reqAut);
			$resAut = json_decode($contAut);
			$numFound = 0;
			if(isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
			$docid = "";
			$nbdocid = 0;
			$iHi = "non";//Test pour savoir si un idHal_i a été trouvé

			if($numFound != 0) {				
				foreach($resAut->response->docs as $author) {
					//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
					$testMel = "oui";//Ok par défaut
					if($halAut[$iAut][$cstMD] != "") {
						$melXML = $halAut[$iAut][$cstMD];
						$tabMelXML = explode(".", $melXML);
						$testXML = $tabMelXML[count($tabMelXML) - 1];
						if(isset($author->emailDomain_s[0])) {$melHAL = $author->emailDomain_s[0];}else{$melHAL = "";}
						$tabMelHAL = explode(".", $melHAL);
						$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
						if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
						//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
					}
					if(isset($author->idHal_i) && $author->idHal_i != 0 && $author->valid_s == "PREFERRED" && strpos($author->fullName_s, ",") === false) {
						if($testMel == "oui") {
							//echo $firstName.' '.$lastName.' : '.$author->idHal_i.' -> '.$author->idHal_s.' - ';
							$halAut[$iAut][$cstFN] = $firstName;
							$halAut[$iAut][$cstLN] = $lastName;
							$halAut[$iAut][$cstAN] = $affilName;
							if(isset($author->idHal_i)) {$halAut[$iAut][$cstII] = $author->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
							if(isset($author->idHal_s)) {$halAut[$iAut][$cstIS] = $author->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
							if(isset($author->emailDomain_s[0])) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($author->emailDomain_s[0], '@'));}else{$halAut[$iAut][$cstMD] = "";}
							if(isset($author->docid)) {$halAut[$iAut][$cstDI] = $author->docid; $trvDoc = "oui";}
							$iHi = "oui";
							$cptiHi++;
							$trouve++;
							if($numFound > 1) {
								$nbCel = count($tabIdHAL);
								$tabIdHAL[$nbCel][$cstFN] = $firstName;
								$tabIdHAL[$nbCel][$cstLN] = $lastName;
								$tabIdHAL[$nbCel]['reqAut'] = $reqAut;
							}
							break;
						}
					}else{//Pas d'idHal
						if($testMel == "oui" && $trvDoc == "non") {
							$docid .= $author->docid;
							$halAut[$iAut][$cstDI] = $author->docid;
							if (isset($author->fullName_s) && !empty($author->fullName_s)) {$halAut[$iAut]['fullName'] = $author->fullName_s;}
							if (isset($author->emailDomain_s[0]) && !empty($author->emailDomain_s[0])) {$halAut[$iAut]['domMail'] = $author->emailDomain_s[0];}
							$nbdocid++;
							$cptdoc++;
							$trvDoc = "oui";
							if ($author->valid_s == "PREFERRED" && $numFound == 1) {$trouve++;}//Une forme idHAL est forcément associée à une forme "PREFERRED" et il n'est donc pas possible de trouver d'idHAL avec les autres méthodes
						}
					}
				}
			}
			if($iHi == "non" && $docid != "" && $nbdocid == 1 && $trvDoc == "non") {//Un seul docid trouvé
				$halAut[$iAut][$cstFN] = $firstName;
				$halAut[$iAut][$cstLN] = $lastName;
				$halAut[$iAut][$cstAN] = $affilName;
				$halAut[$iAut][$cstII] = "";
				$halAut[$iAut][$cstIS] = "";
				$halAut[$iAut][$cstMD] = "";
				$halAut[$iAut][$cstDI] = $docid;
				$cptdoc++;
				$trouve++;
				$trvDoc = "oui";
				//echo($firstName.' '.$lastName.' : '.$docid);
			}
		}
		
		if($trouve == 0 && strlen(str_replace(array("-", "."), "", $firstName)) <= 2) {
			$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=fullName_t:(%22".urlencode($firstName)."%20".urlencode($lastName)."%22%20OR%20%22".urlencode(substr($firstName, 0, 1))."%20".urlencode($lastName)."%22)%20AND%20valid_s:%22PREFERRED%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s%20desc,docid%20asc,fullName_s%20asc";
			$reqAut = str_replace(" ", "%20", $reqAut);
			echo '<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (Méthode intermédiaire 2-3)</a><br>';
			//echo $reqAut.'<br>';
			$contAut = file_get_contents($reqAut);
			$resAut = json_decode($contAut);
			$numFound = 0;
			if(isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
			if($numFound != 0) {
				foreach($resAut->response->docs as $author) {
					//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
					$testMel = "oui";//Ok par défaut
					if($halAut[$iAut][$cstMD] != "") {
						$melXML = $halAut[$iAut][$cstMD];
						$tabMelXML = explode(".", $melXML);
						$testXML = $tabMelXML[count($tabMelXML) - 1];
						if(isset($author->emailDomain_s[0])) {$melHAL = $author->emailDomain_s[0];}else{$melHAL = "";}
						$tabMelHAL = explode(".", $melHAL);
						$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
						if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
						//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
					}
					if(isset($author->idHal_i) && $author->idHal_i != 0 && $author->valid_s == "PREFERRED" && strpos($author->fullName_s, ",") === false) {
						if($testMel == "oui") {
							//echo $firstName.' '.$lastName.' : '.$author->idHal_i.' -> '.$author->idHal_s.' - ';
							$halAut[$iAut][$cstFN] = $firstName;
							$halAut[$iAut][$cstLN] = $lastName;
							$halAut[$iAut][$cstAN] = $affilName;
							if(isset($author->idHal_i)) {$halAut[$iAut][$cstII] = $author->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
							if(isset($author->idHal_s)) {$halAut[$iAut][$cstIS] = $author->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
							if(isset($author->emailDomain_s[0])) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($author->emailDomain_s[0], '@'));}else{$halAut[$iAut][$cstMD] = "";}
							if(isset($author->docid)) {$halAut[$iAut][$cstDI] = $author->docid; $trvDoc = "oui";}
							$iHi = "oui";
							$cptiHi++;
							$trouve++;
							if($numFound > 1) {
								$nbCel = count($tabIdHAL);
								$tabIdHAL[$nbCel][$cstFN] = $firstName;
								$tabIdHAL[$nbCel][$cstLN] = $lastName;
								$tabIdHAL[$nbCel]['reqAut'] = $reqAut;
							}
							break;
						}
					}else{//Pas d'idHal
						if($testMel == "oui" && $trvDoc == "non") {
							$docid .= $author->docid;
							$halAut[$iAut][$cstDI] = $author->docid;
							if (isset($author->fullName_s) && !empty($author->fullName_s)) {$halAut[$iAut]['fullName'] = $author->fullName_s;}
							if (isset($author->emailDomain_s[0]) && !empty($author->emailDomain_s[0])) {$halAut[$iAut]['domMail'] = $author->emailDomain_s[0];}
							$nbdocid++;
							$cptdoc++;
							$trvDoc = "oui";
							if ($author->valid_s == "PREFERRED" && $numFound == 1) {$trouve++;}//Une forme idHAL est forcément associée à une forme "PREFERRED" et il n'est donc pas possible de trouver d'idHAL avec les autres méthodes
						}
					}
				}
			}
		}
		
		//$trvDoc = "non";//Test pour savoir si docid trouvé avec méthode 3 > lui donner la priorité car prénom + com complets plus fiables qu'une initiale seule > donc, ignorer méthode 4
		if($trouve == 0 && strlen($firstName) > 2) {
			$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=fullName_t:%22".urlencode($firstName)."%20".urlencode($lastName)."%22%20AND%20valid_s:(%22OLD%22%20OR%20%22INCOMING%22)&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s%20desc,docid%20asc,fullName_s%20asc";
			$reqAut = str_replace(" ", "%20", $reqAut);
			echo '<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (3ème méthode)</a><br>';
			//echo $reqAut.'<br>';
			$contAut = file_get_contents($reqAut);
			$resAut = json_decode($contAut);
			$numFound = 0;
			if(isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
			if($numFound != 0) {
				$old = "non";
				foreach($resAut->response->docs as $author) {
					//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
					$testMel = "oui";//Ok par défaut
					if($halAut[$iAut][$cstMD] != "") {
						$melXML = $halAut[$iAut][$cstMD];
						$tabMelXML = explode(".", $melXML);
						$testXML = $tabMelXML[count($tabMelXML) - 1];
						if(isset($author->emailDomain_s[0])) {$melHAL = $author->emailDomain_s[0];}else{$melHAL = "";}
						$tabMelHAL = explode(".", $melHAL);
						$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
						if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
						//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
					}
					if(isset($author->idHal_i) && $author->idHal_i != 0 && strpos($author->fullName_s, ",") === false) {
						if($testMel == "oui") {
							//On parcours toutes les formes OLD et si plusieurs résultats, stockage dans un tableau à part en vue de prévenir l'utilisateur lors de l'affichage final
							if($author->valid_s == "OLD") {
								if($old == "non") {
									$halAut[$iAut][$cstFN] = $firstName;
									$halAut[$iAut][$cstLN] = $lastName;
									$halAut[$iAut][$cstAN] = $affilName;
									if(isset($author->idHal_i) && $author->idHal_i != 0) {$halAut[$iAut][$cstII] = $author->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
									if(isset($author->idHal_s)) {$halAut[$iAut][$cstIS] = $author->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
									if(isset($author->emailDomain_s[0])) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($author->emailDomain_s[0], '@'));}else{$halAut[$iAut][$cstMD] = "";}
									if(isset($author->docid)) {$halAut[$iAut][$cstDI] = $author->docid; $trvDoc = "oui";}
									$cptiHi++;
									$trouve++;
									$old = "oui";
								}else{
									$nbCel = count($tabIdHAL);
									$tabIdHAL[$nbCel][$cstFN] = $firstName;
									$tabIdHAL[$nbCel][$cstLN] = $lastName;
									$tabIdHAL[$nbCel]['reqAut'] = $reqAut;
									break;
								}
							}else{//Forme INCOMING
								if(strpos($author->fullName_s, ",") === false) {//Pour éviter les pseudo-auteurs du référentiel auteurs 
									$halAut[$iAut][$cstFN] = $firstName;
									$halAut[$iAut][$cstLN] = $lastName;
									$halAut[$iAut][$cstAN] = $affilName;
									if(isset($author->idHal_i) && $author->idHal_i != 0) {$halAut[$iAut][$cstII] = $author->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
									if(isset($author->idHal_s)) {$halAut[$iAut][$cstIS] = $author->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
									if(isset($author->emailDomain_s[0])) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($author->emailDomain_s[0], '@'));}else{$halAut[$iAut][$cstMD] = "";}
									if(isset($author->docid)) {$halAut[$iAut][$cstDI] = $author->docid; $trvDoc = "oui";}
									$cptiHi++;
									$trouve++;
									break;//On ne prend en compte que la 1ère forme INCOMING trouvée
								}
							}
						}
					}else{//Pas d'idHal
						if($testMel == "oui" && $trvDoc == "non") {
							$docid .= $author->docid;
							$halAut[$iAut][$cstDI] = $author->docid;
							if (isset($author->fullName_s) && !empty($author->fullName_s)) {$halAut[$iAut]['fullName'] = $author->fullName_s;}
							if (isset($author->emailDomain_s[0]) && !empty($author->emailDomain_s[0])) {$halAut[$iAut]['domMail'] = $author->emailDomain_s[0];}
							$nbdocid++;
							$cptdoc++;
							$trvDoc = "oui";
							break;//On ne prend en compte que la 1ère forme INCOMING trouvée
						}
					}
				}
			}
		}
		
		if($trouve == 0 && $trvDoc == "non") {
			$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=fullName_sci:(%22".urlencode($firstNameT)."%20".urlencode($lastNameT)."%22%20OR%20%22".urlencode(substr($firstNameT, 0, 1))."%20".urlencode($lastNameT)."%22)%20AND%20valid_s:(%22OLD%22%20OR%20%22INCOMING%22)&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s%20desc,docid%20asc,fullName_s%20asc";
			$reqAut = str_replace(" ", "%20", $reqAut);
			echo '<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (4ème méthode)</a><br>';
			//echo $reqAut.'<br>';
			$contAut = file_get_contents($reqAut);
			$resAut = json_decode($contAut);
			$numFound = 0;
			if(isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
			if($numFound != 0) {
				$old = "non";
				foreach($resAut->response->docs as $author) {
					//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
					$testMel = "oui";//Ok par défaut
					if($halAut[$iAut][$cstMD] != "") {
						$melXML = $halAut[$iAut][$cstMD];
						$tabMelXML = explode(".", $melXML);
						$testXML = $tabMelXML[count($tabMelXML) - 1];
						if(isset($author->emailDomain_s[0])) {$melHAL = $author->emailDomain_s[0];}else{$melHAL = "";}
						$tabMelHAL = explode(".", $melHAL);
						$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
						if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
						//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
					}
					if(isset($author->idHal_i) && $author->idHal_i != 0 && strpos($author->fullName_s, ",") === false) {
						if($testMel == "oui") {
							//On parcours toutes les formes OLD et si plusieurs résultats, stockage dans un tableau à part en vue de prévenir l'utilisateur lors de l'affichage final
							if($author->valid_s == "OLD") {
								if($old == "non") {
									$halAut[$iAut][$cstFN] = $firstName;
									$halAut[$iAut][$cstLN] = $lastName;
									$halAut[$iAut][$cstAN] = $affilName;
									if(isset($author->idHal_i) && $author->idHal_i != 0) {$halAut[$iAut][$cstII] = $author->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
									if(isset($author->idHal_s)) {$halAut[$iAut][$cstIS] = $author->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
									if(isset($author->emailDomain_s[0])) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($author->emailDomain_s[0], '@'));}else{$halAut[$iAut][$cstMD] = "";}
									if(isset($author->docid)) {$halAut[$iAut][$cstDI] = $author->docid; $trvDoc = "oui";}
									$cptiHi++;
									$trouve++;
									$old = "oui";
								}else{
									$nbCel = count($tabIdHAL);
									$tabIdHAL[$nbCel][$cstFN] = $firstName;
									$tabIdHAL[$nbCel][$cstLN] = $lastName;
									$tabIdHAL[$nbCel]['reqAut'] = $reqAut;
									break;
								}
							}else{//Forme INCOMING
								if(strpos($author->fullName_s, ",") === false) {//Pour éviter les pseudo-auteurs du référentiel auteurs 
									$halAut[$iAut][$cstFN] = $firstName;
									$halAut[$iAut][$cstLN] = $lastName;
									$halAut[$iAut][$cstAN] = $affilName;
									if(isset($author->idHal_i) && $author->idHal_i != 0) {$halAut[$iAut][$cstII] = $author->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
									if(isset($author->idHal_s)) {$halAut[$iAut][$cstIS] = $author->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
									if(isset($author->emailDomain_s[0])) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($author->emailDomain_s[0], '@'));}else{$halAut[$iAut][$cstMD] = "";}
									if(isset($author->docid)) {$halAut[$iAut][$cstDI] = $author->docid; $trvDoc = "oui";}
									$cptiHi++;
									$trouve++;
									break;//On ne prend en compte que la 1ère forme INCOMING trouvée
								}
							}
						}
					}else{//Pas d'idHal
						if($testMel == "oui" && $trvDoc == "non") {
							$docid .= $author->docid;
							$halAut[$iAut][$cstDI] = $author->docid;
							if (isset($author->fullName_s) && !empty($author->fullName_s)) {$halAut[$iAut]['fullName'] = $author->fullName_s;}
							if (isset($author->emailDomain_s[0]) && !empty($author->emailDomain_s[0])) {$halAut[$iAut]['domMail'] = $author->emailDomain_s[0];}
							$nbdocid++;
							$cptdoc++;
							$trvDoc = "oui";
							break;//On ne prend en compte que la 1ère forme INCOMING trouvée
						}
					}
				}
			}
		}
		
		$iAut++;
		//echo('<br>');
		$cpt++;
	}

	//var_dump($halAut);
	$halAutinit = $halAut;//Sauvegarde des affiliations et idHal initiaux remontées par OverHAL
	
	echo '</span></span>';//Fin bloc idHAL/docid
	echo $cptiHi. ' idHal et '.$cptdoc.' docid trouvé(s)';
}
//var_dump($halAut);
echo '<script>';
echo 'document.getElementById(\'cpt2-'.$idFic.'\').style.display = \'none\';';
echo '</script>';

echo '								</div>';
echo '						</div> <!-- end card-body -->';
echo '				</div>';
echo '		</div>';
echo '</div> <!-- .row -->';

//Suppression noeuds ORCID pour ne pas les diffuser ensuite via le TEI
$auts = $xml->getElementsByTagName("author");
foreach($auts as $aut) {
	foreach($aut->childNodes as $elt) {
		if($elt->nodeName == "idno" && $elt->hasAttribute("type") && $elt->getAttribute("type") == "https://orcid.org/") {
			$elt->parentNode->removeChild($elt);
			$xml->save($nomfic);
		}
	}
}

//Suppression noeuds ResearcherID pour ne pas les diffuser ensuite via le TEI
$auts = $xml->getElementsByTagName("author");
foreach($auts as $aut) {
	foreach($aut->childNodes as $elt) {
		if($elt->nodeName == "idno" && $elt->hasAttribute("type") && $elt->getAttribute("type") == "http://www.researcherid.com/rid/") {
			$elt->parentNode->removeChild($elt);
			$xml->save($nomfic);
		}
	}
}

//Fin étape 2
?>
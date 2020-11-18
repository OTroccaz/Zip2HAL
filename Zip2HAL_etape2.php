<?php
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
$halAutinit = array();
$tabIdHAL = array();//Si plusieurs idHAL remontés pour un même auteur

include "./Zip2HAL_constantes.php";

echo '<div id=\'cpt2\'></div>';

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
				$melAut[$iAut] = str_replace('@', '', strstr($elt->nodeValue, '@'));
				$adrAut[$iAut] = $elt->nodeValue;
			}
			//ORCID
			if($elt->nodeName == "idno" && $elt->hasAttribute("type") && $elt->getAttribute("type") == "https://orcid.org/") {
				$iOrcid[$iAut] = $elt->nodeValue;
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

	for($i = 0; $i < count($preAut); $i++) {
		progression($cpt, $nbAut, 'cpt2', $iPro, 'auteur');
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
		$halAut[$iAut]['rolaut'] = $rolAut[$i];
		
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
		
		$trouve = 0;//Test pour savoir si une méthode a permis de trouver quelque chose avant de passer à la suivante
		
		//Tester l'existence d'un ORCID
		if(isset($iOrcid[$iAut]) && $iOrcid[$iAut] != "") {
			$reqOrc = "https://api.archives-ouvertes.fr/ref/author/?q=orcid_id:".$iOrcid[$iAut]."%20AND%20valid_s:%22VALID%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s%20desc,docid%20asc";
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
					if(isset($resAut->response->docs[0]->emailDomain_s)) {$melHAL = $resAut->response->docs[0]->emailDomain_s;}else{$melHAL = "";}
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
						if(isset($resAut->response->docs[0]->emailDomain_s)) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($resAut->response->docs[0]->emailDomain_s, '@'));}else{$halAut[$iAut][$cstMD] = "";}
						if(isset($resAut->response->docs[0]->docid)) {$halAut[$iAut][$cstDI] = $resAut->response->docs[0]->docid;}
						$iHi = "oui";
						$cptiHi++;
						$trouve++;
						$halAut[$iAut]['orcid'] = "oui";
					}
				}else{//Pas d'idHal
					if($testMel == "oui") {
						$docid .= $resAut->response->docs[0]->docid;
						$nbdocid++;
					}
				}
			}
			if($iHi == "non" && $docid != "" && $nbdocid == 1) {//Un seul docid trouvé
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
		
		if($trouve == 0) {
			$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=fullName_sci:(%22".$firstNameT."%20".$lastNameT."%22%20OR%20%22".substr($firstNameT, 0, 1)."%20".$lastNameT."%22)%20AND%20valid_s:%22VALID%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s%20desc,docid%20asc";
			$reqAut = str_replace(" ", "%20", $reqAut);
			echo '<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (1ère méthode)</a><br>';
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
						if(isset($author->emailDomain_s)) {$melHAL = $author->emailDomain_s;}else{$melHAL = "";}
						$tabMelHAL = explode(".", $melHAL);
						$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
						if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
						//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
					}
					if(isset($author->idHal_i) && $author->idHal_i != 0 && $author->valid_s == "VALID" && strpos($author->fullName_s, ",") === false) {
						if($testMel == "oui") {
							//echo $firstName.' '.$lastName.' : '.$author->idHal_i.' -> '.$author->idHal_s.' - ';
							$halAut[$iAut][$cstFN] = $firstName;
							$halAut[$iAut][$cstLN] = $lastName;
							$halAut[$iAut][$cstAN] = $affilName;
							if(isset($author->idHal_i)) {$halAut[$iAut][$cstII] = $author->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
							if(isset($author->idHal_s)) {$halAut[$iAut][$cstIS] = $author->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
							if(isset($author->emailDomain_s)) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($author->emailDomain_s, '@'));}else{$halAut[$iAut][$cstMD] = "";}
							if(isset($author->docid)) {$halAut[$iAut][$cstDI] = $author->docid;}
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
							$docid .= $author->docid;
							$nbdocid++;
						}
					}
				}
			}
			if($iHi == "non" && $docid != "" && $nbdocid == 1) {//Un seul docid trouvé
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
		
		if($trouve == 0 && strlen(str_replace(array("-", "."), "", $prenom)) <= 2) {
			$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=fullName_t:%22".$lastName."%22%20AND%20valid_s:%22VALID%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s desc,docid asc";
			$reqAut = str_replace(" ", "%20", $reqAut);
			echo '<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (Méthode intermédiaire 1-2)</a><br>';
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
						if(isset($author->emailDomain_s)) {$melHAL = $author->emailDomain_s;}else{$melHAL = "";}
						$tabMelHAL = explode(".", $melHAL);
						$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
						if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
						//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
					}
					if(isset($author->idHal_i) && $author->idHal_i != 0 && $author->valid_s == "VALID" && strpos($author->fullName_s, ",") === false) {
						if($testMel == "oui") {
							//echo $firstName.' '.$lastName.' : '.$author->idHal_i.' -> '.$author->idHal_s.' - ';
							$halAut[$iAut][$cstFN] = $firstName;
							$halAut[$iAut][$cstLN] = $lastName;
							$halAut[$iAut][$cstAN] = $affilName;
							if(isset($author->idHal_i)) {$halAut[$iAut][$cstII] = $author->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
							if(isset($author->idHal_s)) {$halAut[$iAut][$cstIS] = $author->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
							if(isset($author->emailDomain_s)) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($author->emailDomain_s, '@'));}else{$halAut[$iAut][$cstMD] = "";}
							if(isset($author->docid)) {$halAut[$iAut][$cstDI] = $author->docid;}
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
							$docid .= $author->docid;
							$nbdocid++;
						}
					}
				}
			}
		}
		
		if($trouve == 0) {
			$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=fullName_sci:(%22".$firstNameT."%20".$lastNameT."%22%20OR%20%22".substr($firstNameT, 0, 1)."%20".$lastNameT."%22)%20AND%20valid_s:(%22OLD%22%20OR%20%22INCOMING%22)&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s desc,docid asc";
			$reqAut = str_replace(" ", "%20", $reqAut);
			echo '<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (2ème méthode)</a><br>';
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
						if(isset($author->emailDomain_s)) {$melHAL = $author->emailDomain_s;}else{$melHAL = "";}
						$tabMelHAL = explode(".", $melHAL);
						$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
						if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
						//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
					}
					if($testMel == "oui") {
						//On parcours toutes les formes OLD et si plusieurs résultats, stockage dans un tableau à part en vue de prévenir l'utilisateur lors de l'affichage final
						if($author->valid_s == "OLD") {
							if($old == "non") {
								$halAut[$iAut][$cstFN] = $firstName;
								$halAut[$iAut][$cstLN] = $lastName;
								$halAut[$iAut][$cstAN] = $affilName;
								if(isset($author->idHal_i) && $author->idHal_i != 0) {$halAut[$iAut][$cstII] = $author->idHal_i; $cptiHi++;}else{$halAut[$iAut][$cstII] = "";}
								if(isset($author->idHal_s)) {$halAut[$iAut][$cstIS] = $author->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
								if(isset($author->emailDomain_s)) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($author->emailDomain_s, '@'));}else{$halAut[$iAut][$cstMD] = "";}
								if(isset($author->docid)) {$halAut[$iAut][$cstDI] = $author->docid;}
								$cptdoc++;
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
								if(isset($author->idHal_i) && $author->idHal_i != 0) {$halAut[$iAut][$cstII] = $author->idHal_i; $cptiHi++;}else{$halAut[$iAut][$cstII] = "";}
								if(isset($author->idHal_s)) {$halAut[$iAut][$cstIS] = $author->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
								if(isset($author->emailDomain_s)) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($author->emailDomain_s, '@'));}else{$halAut[$iAut][$cstMD] = "";}
								if(isset($author->docid)) {$halAut[$iAut][$cstDI] = $author->docid;}
								$cptdoc++;
								break;//On ne prend en compte que la 1ère forme INCOMING trouvée
							}
						}
					}
				}
			}
		}
		
		if($trouve == 0 && strlen($prenom) > 2) {
			$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=fullName_t:%22".$firstName."%20".$lastName."%22%20AND%20valid_s:%22VALID%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s,fullName_s&sort=valid_s desc,docid asc";
			$reqAut = str_replace(" ", "%20", $reqAut);
			echo '<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (3ème méthode)</a><br>';
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
						if(isset($author->emailDomain_s)) {$melHAL = $author->emailDomain_s;}else{$melHAL = "";}
						$tabMelHAL = explode(".", $melHAL);
						$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
						if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
						//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
					}
					if(isset($author->idHal_i) && $author->idHal_i != 0 && $author->valid_s == "VALID" && strpos($author->fullName_s, ",") === false) {
						if($testMel == "oui") {
							//echo $firstName.' '.$lastName.' : '.$author->idHal_i.' -> '.$author->idHal_s.' - ';
							$halAut[$iAut][$cstFN] = $firstName;
							$halAut[$iAut][$cstLN] = $lastName;
							$halAut[$iAut][$cstAN] = $affilName;
							if(isset($author->idHal_i)) {$halAut[$iAut][$cstII] = $author->idHal_i;}else{$halAut[$iAut][$cstII] = "";}
							if(isset($author->idHal_s)) {$halAut[$iAut][$cstIS] = $author->idHal_s;}else{$halAut[$iAut][$cstIS] = "";}
							if(isset($author->emailDomain_s)) {$halAut[$iAut][$cstMD] = str_replace('@', '', strstr($author->emailDomain_s, '@'));}else{$halAut[$iAut][$cstMD] = "";}
							if(isset($author->docid)) {$halAut[$iAut][$cstDI] = $author->docid;}
							$iHi = "oui";
							$cptiHi++;
							$trouve++;
							break;
						}
					}else{//Pas d'idHal
						if($testMel == "oui") {
							$docid .= $author->docid;
							$nbdocid++;
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

echo '<script>';
echo 'document.getElementById(\'cpt2\').style.display = \'none\';';
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

//Fin étape 2
?>
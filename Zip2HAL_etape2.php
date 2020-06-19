<?php
//Etape 2 - Recherche des idHAL des auteurs				
echo('<br><br>');
$cpt = 1;
$iAut = 0;
$preAut = array();//Prénoms des auteurs
$nomAut = array();//Noms des auteurs
$affAut = array();//Affiliation des auteurs
$xmlIds = array();//IdHALs trouvés
$xmlIdi = array();//IdHALi trouvés
$melAut = array();//Emails trouvés
$halAut = array();
$tabIdHAL = array();//Si plusieurs idHAL remontés pour un même auteur

echo('<b>Etape 2 : recherche des idHAL et docid des auteurs</b><br>');
echo('<div id=\'cpt2\'></div>');

$auts = $xml->getElementsByTagName("author");
foreach($auts as $aut) {
	//Initialisation des variables
	$xmlIds[$iAut] = "";
	$xmlIdi[$iAut] = "";
	$affAut[$iAut] = "";
	$melAut[$iAut] = "";
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
		if($elt->nodeName == "idno") {
			if($elt->hasAttribute("type") && $elt->getAttribute("type") == "idhal") {
				if($elt->hasAttribute("notation") && $elt->getAttribute("notation") == "string") {$xmlIds[$iAut] = $elt->nodeValue;}
				if($elt->hasAttribute("notation") && $elt->getAttribute("notation") == "numeric") {$xmlIdi[$iAut] = $elt->nodeValue;}
			}
		}
		//Email
		if($elt->nodeName == "email") {
			$melAut[$iAut] = str_replace('@', '', strstr($elt->nodeValue, '@'));
		}
		//Affiliations
		if($elt->nodeName == "affiliation") {
			if($elt->hasAttribute("ref")) {$affAut[$iAut] .= $elt->getAttribute("ref").'~';}
		}
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
	$halAut[$iAut]['firstName'] = $firstName;
	$halAut[$iAut]['lastName'] = $lastName;
	$halAut[$iAut]['affilName'] = $affilName;
	$halAut[$iAut]['xmlIdi'] = $xmlIdi[$i];
	$halAut[$iAut]['xmlIds'] = $xmlIds[$i];
	$halAut[$iAut]['idHali'] = "";
	$halAut[$iAut]['idHals'] = "";
	$halAut[$iAut]['mailDom'] = $melAut[$i];
	$halAut[$iAut]['mail'] = "";
	$halAut[$iAut]['docid'] = "";
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
	if(strlen(str_replace(".", "", $firstNameT)) == 1 || $testPre != "") {//Juste l'initiale du prénom ou prénom composé dont on a gardé que les initiales
		$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=firstName_t:".$testPre."*%20AND%20lastName_t:%22".$lastNameT."%22%20AND%20valid_s:%22VALID%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s&sort=valid_s%20desc,docid%20asc";
	}else{
		$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=firstName_t:(%22".$firstNameT."%22%20OR%20%22".substr($firstNameT, 0, 1)."%22)%20AND%20lastName_t:%22".$lastNameT."%22%20AND%20valid_s:%22VALID%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s";
	}
	$reqAut = str_replace(" ", "%20", $reqAut);
	echo('<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (1ère méthode)</a><br>');
	//echo $reqAut.'<br>';
	$contAut = file_get_contents($reqAut);
	$resAut = json_decode($contAut);
	$numFound = 0;
	if(isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
	$docid = "";
	$nbdocid = 0;
	$iHi = "non";//Test pour savoir si un idHal_i a été trouvé
	$trouve = 0;//Test pour savoir si la 1ère méthode a permis de trouver quelque chose

	if($numFound != 0) {				
		foreach($resAut->response->docs as $author) {
			//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
			$testMel = "oui";//Ok par défaut
			if($halAut[$iAut]['mailDom'] != "") {
				$melXML = $halAut[$iAut]['mailDom'];
				$tabMelXML = explode(".", $melXML);
				$testXML = $tabMelXML[count($tabMelXML) - 1];
				if(isset($author->emailDomain_s)) {$melHAL = $author->emailDomain_s;}else{$melHAL = "";}
				$tabMelHAL = explode(".", $melHAL);
				$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
				if($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
				//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
			}
			if(isset($author->idHal_i) && $author->idHal_i != 0 && $author->valid_s == "VALID") {
				if($testMel == "oui") {
					//echo $firstName.' '.$lastName.' : '.$author->idHal_i.' -> '.$author->idHal_s.' - ';
					$halAut[$iAut]['firstName'] = $firstName;
					$halAut[$iAut]['lastName'] = $lastName;
					$halAut[$iAut]['affilName'] = $affilName;
					if(isset($author->idHal_i)) {$halAut[$iAut]['idHali'] = $author->idHal_i;}else{$halAut[$iAut]['idHali'] = "";}
					if(isset($author->idHal_s)) {$halAut[$iAut]['idHals'] = $author->idHal_s;}else{$halAut[$iAut]['idHals'] = "";}
					if(isset($author->emailDomain_s)) {$halAut[$iAut]['mailDom'] = str_replace('@', '', strstr($author->emailDomain_s, '@'));}else{$halAut[$iAut]['mailDom'] = "";}
					if(isset($author->docid)) {$halAut[$iAut]['docid'] = $author->docid;}
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
	if($iHi == "non" && $docid != "" && $nbdocid == 1) {//Un seul docid trouvé
		$halAut[$iAut]['firstName'] = $firstName;
		$halAut[$iAut]['lastName'] = $lastName;
		$halAut[$iAut]['affilName'] = $affilName;
		$halAut[$iAut]['idHali'] = "";
		$halAut[$iAut]['idHals'] = "";
		$halAut[$iAut]['mailDom'] = "";
		$halAut[$iAut]['docid'] = $docid;
		$cptdoc++;
		$trouve++;
		//echo($firstName.' '.$lastName.' : '.$docid);
	}
	
	if($trouve == 0) {
		if(strlen(str_replace(".", "", $firstNameT))) {//Juste l'initiale du prénom
			$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=firstName_t:".str_replace(".", "", $firstNameT)."* AND lastName_t:%22".$lastNameT."%22%20AND%20valid_s:(%22OLD%22%20OR%20%22INCOMING%22)&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s&sort=valid_s desc,docid asc";
		}else{
			$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=firstName_t:(%22".$firstNameT."%22%20OR%20%22".substr($firstNameT, 0, 1)."%22)%20AND%20lastName_t:%22".$lastNameT."%22%20AND%20valid_s:(%22OLD%22%20OR%20%22INCOMING%22)&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s&sort=valid_s desc,docid asc";
		}
		$reqAut = str_replace(" ", "%20", $reqAut);
		echo('<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (2ème méthode)</a><br>');
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
				if($halAut[$iAut]['mailDom'] != "") {
					$melXML = $halAut[$iAut]['mailDom'];
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
							$halAut[$iAut]['firstName'] = $firstName;
							$halAut[$iAut]['lastName'] = $lastName;
							$halAut[$iAut]['affilName'] = $affilName;
							if(isset($author->idHal_i) && $author->idHal_i != 0) {$halAut[$iAut]['idHali'] = $author->idHal_i; $cptiHi++;}else{$halAut[$iAut]['idHali'] = "";}
							if(isset($author->idHal_s)) {$halAut[$iAut]['idHals'] = $author->idHal_s;}else{$halAut[$iAut]['idHals'] = "";}
							if(isset($author->emailDomain_s)) {$halAut[$iAut]['mailDom'] = str_replace('@', '', strstr($author->emailDomain_s, '@'));}else{$halAut[$iAut]['mailDom'] = "";}
							if(isset($author->docid)) {$halAut[$iAut]['docid'] = $author->docid;}
							$cptdoc++;
							$old = "oui";
						}else{
							$nbCel = count($tabIdHAL);
							$tabIdHAL[$nbCel]['firstName'] = $firstName;
							$tabIdHAL[$nbCel]['lastName'] = $lastName;
							$tabIdHAL[$nbCel]['reqAut'] = $reqAut;
							break;
						}
					}else{//Forme INCOMING
						$halAut[$iAut]['firstName'] = $firstName;
						$halAut[$iAut]['lastName'] = $lastName;
						$halAut[$iAut]['affilName'] = $affilName;
						if(isset($author->idHal_i) && $author->idHal_i != 0) {$halAut[$iAut]['idHali'] = $author->idHal_i; $cptiHi++;}else{$halAut[$iAut]['idHali'] = "";}
						if(isset($author->idHal_s)) {$halAut[$iAut]['idHals'] = $author->idHal_s;}else{$halAut[$iAut]['idHals'] = "";}
						if(isset($author->emailDomain_s)) {$halAut[$iAut]['mailDom'] = str_replace('@', '', strstr($author->emailDomain_s, '@'));}else{$halAut[$iAut]['mailDom'] = "";}
						if(isset($author->docid)) {$halAut[$iAut]['docid'] = $author->docid;}
						$cptdoc++;
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
echo($cptiHi. ' idHal et '.$cptdoc.' docid trouvé(s)');

echo('<script>');
echo('document.getElementById(\'cpt2\').style.display = \'none\';');
echo('</script>');
//Fin étape 2
?>
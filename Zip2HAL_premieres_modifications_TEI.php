<?php
if(isset($typDbl) && ($typDbl == "HALCOLLTYP" || $typDbl == "HALTYP")) {//Doublon de type TYP > inutile d'effectuer les recherches
	//Modifications du TEI inutiles
}else{
	//Définir des constantes au lieu de dupliquer des littéraux
	$cstTA = "tagName";
	$cstTI = "title";
	$cstXL = "xml:lang";
	$cstAU = "author";
	$cstKE = "keywords";
	$cstCC = "classCode";
	$cstNO = "nonodevalue";
	$cstAB = "abstract";
	$cstAM = "amont";
	$cstPE = "persName";
	$cstEM = "email";
	$cstIS = "idHals";
	$cstNT = "notation";
	$cstID = "idhal";
	$cstAF = "affiliation";

	//Premières modifications du TEI avec les résultats précédemment obtenus

	//Métadonnées > Langue > A récupérer dès le début pour pouvoir l'insérer aux titre, résumé et mots-clés
	$tabLang = array_keys($languages);
	$elts = $xml->getElementsByTagName("language");
	foreach($elts as $elt) {
		if($elt->hasAttribute("ident")) {$lang = $elt->nodeValue;}else{$lang = "";}
	}
	//Si 2 langues, prendre la dernière après le ;
	if($lang != "" && strpos($lang, ";") !== false) {
		//$langTab = explode(";", $lang);
		//$lang = $langTab[0];
		$lang = trim(str_replace(";", "", strstr($lang, ";")));
	}
	//Si pas de langue définie dans le XML, on prend l'anglais par défaut
	if($lang == "") {$lang = "English";}

	insertNode($xml, $lang, "langUsage", "", 0, "language", "ident", $languages[$lang], "", "", "iB", $cstTA, "");

	//Ajout de la langue au titre
	$titreOK = "non";
	$elts = $xml->getElementsByTagName($cstTI);
	foreach($elts as $elt) {
		if($elt->hasAttribute($cstXL)) {
			if($titreOK == "non") {//Le titre est parfois présent plusieurs fois
				deleteNode($xml, "analytic", $cstTI, 0, "", "", "", "", "exact");
				$xml->save($nomfic);
				insertNode($xml, $elt->nodeValue, "analytic", $cstAU, 0, $cstTI, $cstXL, $languages[$lang], "", "", "iB", $cstTA, "");
				$xml->save($nomfic);
				$titreOK = "oui";
			}
		}
	}

	//Ajout de la langue aux mots-clés + ajout de 3 mots-clés vides
	$keys = $xml->getElementsByTagName("keywords");
	$ind = 0;
	$tabKey = array();
	$domArray = array();

	//Sauvegarde des mots-clés
	foreach($keys as $key) {
		foreach($key->childNodes as $elt) {
			$tabKey[] = $elt->nodeValue;
			$domArray[] = $elt;
		}
	}
	//Suppression des mots-clés
	foreach($domArray as $node){ 
		$node->parentNode->removeChild($node);
	}
	$xml->save($nomfic);
	//Ajout des mots-clés avec la langue
	foreach($tabKey as $keyw){
		$bimoc = $xml->createElement("term");
		$moc = $xml->createTextNode($keyw);
		$bimoc->setAttribute($cstXL, $languages[$lang]);
		$bimoc->appendChild($moc);
		$key->appendChild($bimoc);																		
		$xml->save($nomfic);
	}
	if(empty($tabKey)) {//Il n'y a pas de mots-clés dans le XML initial > il faut préparer le noeud
		insertNode($xml, $cstNO, "textClass", $cstCC, 0, "keywords", "scheme", $cstAU, "", "", "iB", $cstTA, "");
		$xml->save($nomfic);
		$keys = $xml->getElementsByTagName("keywords");
		foreach($keys as $key) {}
	}				
	//Ajout de 3 mots-clés vides
	$keys = $xml->getElementsByTagName("keywords");
	for($mc = 0; $mc < 3; $mc++) {
		$bimoc = $xml->createElement("term");
		$moc = $xml->createTextNode("");
		$bimoc->setAttribute($cstXL, $languages[$lang]);
		$bimoc->appendChild($moc);
		$keys->item(0)->appendChild($bimoc);																		
		$xml->save($nomfic);
	}

	//Ajout de la langue au résumé
	$elts = $xml->getElementsByTagName($cstAB);
	foreach($elts as $elt) {
		if($elt->hasAttribute($cstXL)) {
			deleteNode($xml, "profileDesc", $cstAB, 0, "", "", "", "", "exact");
			$xml->save($nomfic);
			insertNode($xml, $elt->nodeValue, "profileDesc", "", 0, $cstAB, $cstXL, $languages[$lang], "", "", "iB", $cstTA, "");
			$xml->save($nomfic);
		}
	}


	//Ajout du code collection
	insertNode($xml, "", "seriesStmt", "", 0, "idno", "type", "stamp", "n", $team, "aC", $cstAM, "");
	$xml->save($nomfic);

	//Ajout du domaine
	if($domaine != "") {
		$tabDom = explode(" ~ ", str_replace("’", "'", $domaine));
		insertNode($xml, $tabDom[0], "textClass", $cstCC, 0, $cstCC, "scheme", "halDomain", "n", $tabDom[1], "aC", $cstAM, "");
		$xml->save($nomfic);
	}

	//Ajout des IdHAL et/ou docid et/ou mail
	$auts = $xml->getElementsByTagName($cstAU);
	foreach($auts as $aut) {
		//Initialisation des variables
		$fname = "";//Prénom
		$lname = "";//Nom
		$listIdHAL = "~";//Variable pour assurer l'unicité de l'insertion des IdHAL
		$listdocid = "~";//Variable pour assurer l'unicité de l'insertion des docid
		$listmails = "~";//Variable pour assurer l'unicité de l'insertion des mails
		foreach($aut->childNodes as $elt) {
			//Prénom/Nom
			if($elt->nodeName == $cstPE) {
				foreach($elt->childNodes as $per) {
					if($per->nodeName == "forename") {
						$fname = $per->nodeValue;
					}
					if($per->nodeName == "surname") {
						$lname = $per->nodeValue;
					}
				}
			}
			
			//Ajouts divers
			for($i = 0; $i < count($halAut); $i++) {
				if($halAut[$i]['firstName'] == $fname && $halAut[$i]['lastName'] == $lname) {
					$ou = "iB";
					//Y-a-t-il un mail ?
					if($halAut[$i]['mail'] != "" && strpos($listmails, $halAut[$i]['mail']) === false) {
						$auts = $xml->getElementsByTagName('author')->item($i);
						$bimoc = $xml->createElement($cstEM);
						$moc = $xml->createTextNode($halAut[$i]['mail']);
						$bimoc->appendChild($moc);
						$auts->appendChild($bimoc);
						$listmails .= $halAut[$i]['mail'].'~';
						$ou = "iA";//Le noeud mail doit être juste après persName pour que le TEI soit valide
					}
					//Y-a-t-il un IdHAL ?
					if($halAut[$i][$cstIS] != "" && strpos($listIdHAL, $halAut[$i][$cstIS]) === false) {
						if($ou == "iB") {
							insertNode($xml, $halAut[$i]['idHali'], $cstAU, $cstAF, $i, "idno", "type", $cstID, $cstNT, "numeric", "iB", $cstAM, "");	
						}else{
							insertNode($xml, $halAut[$i]['idHali'], $cstAU, $cstEM, $i, "idno", "type", $cstID, $cstNT, "numeric", "iA", $cstAM, "");	
						}
						insertNode($xml, $halAut[$i][$cstIS], $cstAU, "idno", $i, "idno", "type", $cstID, $cstNT, "string", "iB", $cstAM, "");
						$listIdHAL .= $halAut[$i][$cstIS].'~';
					}
					//Y-a-t-il un docid ?
					if($halAut[$i]['docid'] != "" && strpos($listdocid, $halAut[$i]['docid']) === false) {
						if($ou == "iB") {
							insertNode($xml, $halAut[$i]['docid'], $cstAU, $cstAF, $i, "idno", "type", "halauthorid", "", "", "iB", $cstAM, "");
						}else{
							insertNode($xml, $halAut[$i]['docid'], $cstAU, $cstEM, $i, "idno", "type", "halauthorid", "", "", "iA", $cstAM, "");
						}
						$listdocid .= $halAut[$i]['docid'].'~';
					}
					//Id structures des affiliations
					//Recherche des affiliations remontées globalement sur la base du nom de l'organisme, quel que soit l'auteur mais sous réserve du rattachement de l'auteur à cette affiliation (ex : U1085)
					for($j = 0; $j < count($halAff); $j++) {
						if($halAff[$j]['fname'] == "" && $halAff[$j]['lname'] == "" && (strpos($halAut[$i]['affilName'], $halAff[$j]['lsAff']) !== false)) {
							$lsAff = $halAff[$j]['lsAff'];
							deleteNode($xml, $cstAU, $cstAF, $i, "ref", $lsAff, "", "", "approx");
							//Puis on ajoute l'(les) affiliation(s) trouvée(s)
							$affil = "#struct-".$halAff[$j]['docid'];
							insertNode($xml, $cstNO, $cstAU, $cstPE, $i, $cstAF, "ref", $affil, "", "", "aC", $cstAM, "");
						}
					}
					//Recherche des affiliations remontées pour chaque auteur
					for($j = 0; $j < count($halAff); $j++) {
						if($halAff[$j]['fname'] == $fname && $halAff[$j]['lname'] == $lname) {
							//Au moins une affiliation trouvée > On supprime l'affiliation correspondante du TEI de type '<affiliation ref="#localStruct-Affx"/>' pour cet auteur
							$lsAff = $halAff[$j]['lsAff'];
							deleteNode($xml, $cstAU, $cstAF, $i, "ref", $lsAff, "", "", "approx");
							//Puis on ajoute l'(les) affiliation(s) trouvée(s)
							$affil = "#struct-".$halAff[$j]['docid'];
							insertNode($xml, $cstNO, $cstAU, $cstPE, $i, $cstAF, "ref", $affil, "", "", "aC", $cstAM, "");
						}
					}
					
					$xml->save($nomfic);
				}
			}
			/*
			//Y-a-t-il un docid ?
			for($i = 0; $i < count($halAut); $i++) {
				if($halAut[$i]['firstName'] == $fname && $halAut[$i]['lastName'] == $lname) {
					if($halAut[$i]['docid'] != "" && strpos($listdocid, $halAut[$i]['docid']) === false) {
						insertNode($xml, $halAut[$i]['docid'], $cstAU, $cstAF, $i, "idno", "type", "halauthorid", "", "", "iB");
						$xml->save($nomfic);
						$listdocid .= $halAut[$i]['docid'].'~';
					}
				}
			}
			*/
		}
	}

	//Fin des premières modifications du TEI
}
?>
<?php
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

insertNode($xml, $lang, "langUsage", "", 0, "language", "ident", $languages[$lang], "", "", "iB", "tagName", "");

//Ajout de la langue au titre
$titreOK = "non";
$elts = $xml->getElementsByTagName("title");
foreach($elts as $elt) {
	if($elt->hasAttribute("xml:lang")) {
		if($titreOK == "non") {//Le titre est parfois présent plusieurs fois
			deleteNode($xml, "analytic", "title", 0, "", "", "", "", "exact");
			$xml->save($nomfic);
			insertNode($xml, $elt->nodeValue, "analytic", "author", 0, "title", "xml:lang", $languages[$lang], "", "", "iB", "tagName", "");
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
	$bimoc->setAttribute("xml:lang", $languages[$lang]);
	$bimoc->appendChild($moc);
	$key->appendChild($bimoc);																		
	$xml->save($nomfic);
}
if(!isset($key)) {//Il n'y a pas de mots-clés dans le XML initial > il faut préparer le noeud
	insertNode($xml, "nonodevalue", "textClass", "classCode", 0, "keywords", "scheme", "author", "", "", "iB", "tagName", "");
	$xml->save($nomfic);
	$keys = $xml->getElementsByTagName("keywords");
	foreach($keys as $key) {}
}				
//Ajout de 3 mots-clés vides
$keys = $xml->getElementsByTagName("keywords");
for($mc = 0; $mc < 3; $mc++) {
	$bimoc = $xml->createElement("term");
	$moc = $xml->createTextNode("");
	$bimoc->setAttribute("xml:lang", $languages[$lang]);
	$bimoc->appendChild($moc);
	$keys->item(0)->appendChild($bimoc);																		
	$xml->save($nomfic);
}

//Ajout de la langue au résumé
$elts = $xml->getElementsByTagName("abstract");
foreach($elts as $elt) {
	if($elt->hasAttribute("xml:lang")) {
		deleteNode($xml, "profileDesc", "abstract", 0, "", "", "", "", "exact");
		$xml->save($nomfic);
		insertNode($xml, $elt->nodeValue, "profileDesc", "", 0, "abstract", "xml:lang", $languages[$lang], "", "", "iB", "tagName", "");
		$xml->save($nomfic);
	}
}


//Ajout du code collection
insertNode($xml, "", "seriesStmt", "", 0, "idno", "type", "stamp", "n", $team, "aC", "amont", "");
$xml->save($nomfic);

//Ajout du domaine
if($domaine != "") {
	$tabDom = explode(" ~ ", str_replace("’", "'", $domaine));
	insertNode($xml, $tabDom[0], "textClass", "classCode", 0, "classCode", "scheme", "halDomain", "n", $tabDom[1], "aC", "amont", "");
	$xml->save($nomfic);
}

//Ajout des IdHAL et/ou docid et/ou mail
$auts = $xml->getElementsByTagName("author");
foreach($auts as $aut) {
	//Initialisation des variables
	$fname = "";//Prénom
	$lname = "";//Nom
	$listIdHAL = "~";//Variable pour assurer l'unicité de l'insertion des IdHAL
	$listdocid = "~";//Variable pour assurer l'unicité de l'insertion des docid
	$listmails = "~";//Variable pour assurer l'unicité de l'insertion des mails
	foreach($aut->childNodes as $elt) {
		//Prénom/Nom
		if($elt->nodeName == "persName") {
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
					$bimoc = $xml->createElement("email");
					$moc = $xml->createTextNode($halAut[$i]['mail']);
					$bimoc->appendChild($moc);
					$auts->appendChild($bimoc);
					$listmails .= $halAut[$i]['mail'].'~';
					$ou = "iA";//Le noeud mail doit être juste après persName pour que le TEI soit valide
				}
				//Y-a-t-il un IdHAL ?
				if($halAut[$i]['idHals'] != "" && strpos($listIdHAL, $halAut[$i]['idHals']) === false) {
					if($ou == "iB") {
						insertNode($xml, $halAut[$i]['idHali'], "author", "affiliation", $i, "idno", "type", "idhal", "notation", "numeric", "iB", "amont", "");	
					}else{
						insertNode($xml, $halAut[$i]['idHali'], "author", "email", $i, "idno", "type", "idhal", "notation", "numeric", "iA", "amont", "");	
					}
					insertNode($xml, $halAut[$i]['idHals'], "author", "idno", $i, "idno", "type", "idhal", "notation", "string", "iB", "amont", "");
					$listIdHAL .= $halAut[$i]['idHals'].'~';
				}
				//Y-a-t-il un docid ?
				if($halAut[$i]['docid'] != "" && strpos($listdocid, $halAut[$i]['docid']) === false) {
					if($ou == "iB") {
						insertNode($xml, $halAut[$i]['docid'], "author", "affiliation", $i, "idno", "type", "halauthorid", "", "", "iB", "amont", "");
					}else{
						insertNode($xml, $halAut[$i]['docid'], "author", "email", $i, "idno", "type", "halauthorid", "", "", "iA", "amont", "");
					}
					$listdocid .= $halAut[$i]['docid'].'~';
				}
				//Id structures des affiliations
				//Recherche des affiliations remontées globalement sur la base du nom de l'organisme, quel que soit l'auteur mais sous réserve du rattachement de l'auteur à cette affiliation (ex : U1085)
				for($j = 0; $j < count($halAff); $j++) {
					if($halAff[$j]['fname'] == "" && $halAff[$j]['lname'] == "" && (strpos($halAut[$i]['affilName'], $halAff[$j]['lsAff']) !== false)) {
						$lsAff = $halAff[$j]['lsAff'];
						deleteNode($xml, "author", "affiliation", $i, "ref", $lsAff, "", "", "approx");
						//Puis on ajoute l'(les) affiliation(s) trouvée(s)
						$affil = "#struct-".$halAff[$j]['docid'];
						insertNode($xml, "nonodevalue", "author", "persName", $i, "affiliation", "ref", $affil, "", "", "aC", "amont", "");
					}
				}
				//Recherche des affiliations remontées pour chaque auteur
				for($j = 0; $j < count($halAff); $j++) {
					if($halAff[$j]['fname'] == $fname && $halAff[$j]['lname'] == $lname) {
						//Au moins une affiliation trouvée > On supprime l'affiliation correspondante du TEI de type '<affiliation ref="#localStruct-Affx"/>' pour cet auteur
						$lsAff = $halAff[$j]['lsAff'];
						deleteNode($xml, "author", "affiliation", $i, "ref", $lsAff, "", "", "approx");
						//Puis on ajoute l'(les) affiliation(s) trouvée(s)
						$affil = "#struct-".$halAff[$j]['docid'];
						insertNode($xml, "nonodevalue", "author", "persName", $i, "affiliation", "ref", $affil, "", "", "aC", "amont", "");
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
					insertNode($xml, $halAut[$i]['docid'], "author", "affiliation", $i, "idno", "type", "halauthorid", "", "", "iB");
					$xml->save($nomfic);
					$listdocid .= $halAut[$i]['docid'].'~';
				}
			}
		}
		*/
	}
}

//Fin des premières modifications du TEI

?>
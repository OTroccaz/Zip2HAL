<?php
/*
 * Zip2HAL - Importez vos publications dans HAL - Import your publications into HAL
 *
 * Copyright (C) 2023 Olivier Troccaz (olivier.troccaz@cnrs.fr) and Laurent Jonchère (laurent.jonchere@univ-rennes.fr)
 * Released under the terms and conditions of the GNU General Public License (https://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * Modifications initiales du TEI - Initial changes to the TEI
 */
 
if(isset($typDbl) && ($typDbl == "HALCOLLTYP" || $typDbl == "HALTYP")) {//Doublon de type TYP > inutile d'effectuer les recherches
	//Modifications du TEI inutiles
}else{
	include "./Zip2HAL_constantes.php";

	//Premières modifications du TEI avec les résultats précédemment obtenus
	//var_dump_pre($halAff);
	//Type de document
	$typDoc = "";
	$elts = $xml->getElementsByTagName("classCode");
	foreach($elts as $elt) {
		if($elt->hasAttribute("scheme") && $elt->getAttribute("scheme") == "halTypology") {$typDoc = $elt->getAttribute("n");}
	}

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
		if($elt->hasAttribute($cstXL) && $titreOK == "non") {//Le titre est parfois présent plusieurs fois
			deleteNode($xml, "analytic", $cstTI, 0, "", "", "", "", "exact");
			$xml->save($nomfic);
			insertNode($xml, $elt->nodeValue, "analytic", $cstAU, 0, $cstTI, $cstXL, $languages[$lang], "", "", "iB", $cstTA, "");
			$xml->save($nomfic);
			$titreOK = "oui";
		}
	}

	//Ajout de la langue aux mots-clés + ajout de 3 mots-clés vides
	$keys = $xml->getElementsByTagName($cstKE);
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
		insertNode($xml, $cstNO, "textClass", $cstCC, 0, $cstKE, "scheme", $cstAU, "", "", "iB", $cstTA, "");
		$xml->save($nomfic);
		$keys = $xml->getElementsByTagName($cstKE);
		foreach($keys as $key) {}
	}				
	//Ajout de 3 mots-clés vides
	$keys = $xml->getElementsByTagName($cstKE);
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


	//Ajout du code collection s'il a été renseigné
	if($team != "") {
		insertNode($xml, "", "seriesStmt", "", 0, "idno", "type", "stamp", "n", $team, "aC", $cstAM, "");
		$xml->save($nomfic);
	}

	//Ajout du domaine
	if($domaine != "") {
		$tabDom = explode(" ~ ", str_replace("’", "'", $domaine));
		insertNode($xml, $tabDom[0], "textClass", $cstCC, 0, $cstCC, "scheme", "halDomain", "n", $tabDom[1], "aC", $cstAM, "");
		$xml->save($nomfic);
	}
	
	//Si typedoc COMM ou POSTER
	if($typDoc == "COMM" || $typDoc == "POSTER") {
		//Renseigner les proceedings à oui par défaut s'ils ne sont pas renseignés
		$presProc = "non";
		$elts = $xml->getElementsByTagName("note");
		foreach($elts as $elt) {
			if($elt->hasAttribute("type") && $elt->getAttribute("type") == "proceedings") {
				$presProc = "oui";
			}
		}
		if($presProc == "non") {
			insertNode($xml, "Yes", $cstNS, "", 0, "note", "type", "proceedings", "n", "1", "iB", $cstTN, "");
			$xml->save($nomfic);
		}
		
		//Par défaut, la conférence est considérée comme conférence non invitée
		$presCinv = "non";
		$elts = $xml->getElementsByTagName("note");
		foreach($elts as $elt) {
			if($elt->hasAttribute("type") && $elt->getAttribute("type") == "invited") {
				$presCinv = "oui";
			}
		}
		if($presCinv == "non") {
			insertNode($xml, "No", $cstNS, "", 0, "note", "type", "invited", "n", "0", "iB", $cstTN, "");
			$xml->save($nomfic);
		}
	}
	
	//Si présence d'un ISSN, vérification qu'il comporte bien un tiret et ajout éventuel
	$idns = $xml->getElementsByTagName("idno");
	foreach($idns as $idn) {
		if($idn->hasAttribute("type") && $idn->getAttribute("type") == "issn") {
			if(strpos($idn->nodeValue, "-") === false && $idn->nodeValue != "") {//Pas de tiret et ISSN non nul
				$nodVal = substr($idn->nodeValue, 0, 4)."-".substr($idn->nodeValue, 4, 4);
				deleteNode($xml, $cstMO, "idno", 0, "type", "issn", "", "", $cstEX);
				$xml->save($nomfic);
				insertNode($xml, $nodVal, $cstMO, $cstTI, 0, "idno", "type", "issn", "", "", "iB", $cstTN, "");
				$xml->save($nomfic);
			}
		}
	}

	//Ajout des IdHAL et/ou docid et/ou mail
	$auts = $xml->getElementsByTagName($cstAU);
	foreach($auts as $aut) {
		//Initialisation des variables
		$firstName = "";//Prénom
		$lastName = "";//Nom
		$listIdHAL = "~";//Variable pour assurer l'unicité de l'insertion des IdHAL
		$listdocid = "~";//Variable pour assurer l'unicité de l'insertion des docid
		$listmails = "~";//Variable pour assurer l'unicité de l'insertion des mails
		foreach($aut->childNodes as $elt) {
			//Prénom/Nom
			if($elt->nodeName == $cstPE) {
				foreach($elt->childNodes as $per) {
					if($per->nodeName == "forename") {
						$firstName = $per->nodeValue;
					}
					if($per->nodeName == "surname") {
						$lastName = $per->nodeValue;
					}
				}
			}
			
			//Ajouts divers
			for($i = 0; $i < count($halAut); $i++) {
				if($halAut[$i][$cstFN] == $firstName && $halAut[$i][$cstLN] == $lastName) {
					$ou = "iB";
					//Y-a-t-il un mail ?
					if($halAut[$i]['mail'] != "" && strpos($listmails, $halAut[$i]['mail']) === false) {
						//Sauvegarde et suppression des noeuds 'idno' et affiliation'
						$auts = $xml->getElementsByTagName('author')->item($i);
						$domArray = array();
						foreach($auts->childNodes as $elt) {
							if ($elt->nodeName == 'idno' || $elt->nodeName == 'affiliation') {
								$domArray[] = $elt;
								$elt->parentNode->removeChild($elt);
							}
						}
						$xml->save($nomfic);
						//Suppression du noeud mail pour éviter un doublon
						deleteNode($xml, $cstAU, $cstEM, $i, "", "", "", "", "exact");
						$auts = $xml->getElementsByTagName('author')->item($i);
						$bimoc = $xml->createElement($cstEM);
						$bimoc->setAttribute('type', 'md5');
						$moc = $xml->createTextNode($halAut[$i]['mail']);
						$bimoc->appendChild($moc);
						$auts->appendChild($bimoc);
						$listmails .= $halAut[$i]['mail'].'~';
						$ou = "iA";//Le noeud mail doit être juste après persName pour que le TEI soit valide
						//Rajout des noeuds 'idno' et affiliation' présents initialement
						foreach($domArray as $node){ 
							$auts->appendChild($node);
						}
						$xml->save($nomfic);
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
					if($halAut[$i][$cstDI] != "" && strpos($listdocid, $halAut[$i][$cstDI]) === false) {
						if($ou == "iB") {
							insertNode($xml, $halAut[$i][$cstDI], $cstAU, $cstAF, $i, "idno", "type", "halauthorid", "", "", "iB", $cstAM, "");
						}else{
							insertNode($xml, $halAut[$i][$cstDI], $cstAU, $cstEM, $i, "idno", "type", "halauthorid", "", "", "iA", $cstAM, "");
						}
						$listdocid .= $halAut[$i][$cstDI].'~';
					}
					//Id structures des affiliations
					//Recherche des affiliations remontées globalement sur la base du nom de l'organisme, quel que soit l'auteur mais sous réserve du rattachement de l'auteur à cette affiliation (ex : U1085)
					for($j = 0; $j < count($halAff); $j++) {
						if($halAff[$j][$cstFN] == "" && $halAff[$j][$cstLN] == "" && (strpos($halAut[$i]['affilName'], $halAff[$j][$cstLA]) !== false)) {
							$lsAff = $halAff[$j][$cstLA];
							deleteNode($xml, $cstAU, $cstAF, $i, "ref", $lsAff, "", "", "approx");
							//Puis on ajoute l'(les) affiliation(s) trouvée(s)
							$affil = "#struct-".$halAff[$j][$cstDI];
							insertNode($xml, $cstNO, $cstAU, $cstPE, $i, $cstAF, "ref", $affil, "", "", "aC", $cstAM, "");
							//Enfin, on modifie pour les noeuds enfants de 'listOrg type="structures"'
							$orgs = $xml->getElementsByTagName('org');
							foreach ($orgs as $org) {
								if (strpos($halAff[$j][$cstLA], $org->getAttribute('xml:id')) !== false) {
									$org->setAttribute('xml:id', str_replace('#', '', $affil));
									$org->setAttribute('status', $halAff[$j]['valid']);
								}
							}
						}
					}
					//Recherche des affiliations remontées pour chaque auteur
					for($j = 0; $j < count($halAff); $j++) {
						if($halAff[$j][$cstFN] == $firstName && $halAff[$j][$cstLN] == $lastName) {
							//Au moins une affiliation trouvée > On supprime l'affiliation correspondante du TEI de type '<affiliation ref="#localStruct-Affx"/>' pour cet auteur
							$lsAff = $halAff[$j][$cstLA];
							deleteNode($xml, $cstAU, $cstAF, $i, "ref", $lsAff, "", "", "approx");
							//Puis on ajoute l'(les) affiliation(s) trouvée(s)
							$affil = "#struct-".$halAff[$j][$cstDI];
							insertNode($xml, $cstNO, $cstAU, $cstPE, $i, $cstAF, "ref", $affil, "", "", "aC", $cstAM, "");
							//Enfin, on modifie pour les noeuds enfants de 'listOrg type="structures"'
							$orgs = $xml->getElementsByTagName('org');
							foreach ($orgs as $org) {
								if (strpos($halAff[$j][$cstLA], $org->getAttribute('xml:id')) !== false) {
									$org->setAttribute('xml:id', str_replace('#', '', $affil));
									$org->setAttribute('status', $halAff[$j]['valid']);
								}
							}
						}
					}
					
					$xml->save($nomfic);
				}
			}
			/*
			//Y-a-t-il un docid ?
			for($i = 0; $i < count($halAut); $i++) {
				if($halAut[$i][$cstFN] == $firstName && $halAut[$i][$cstLN] == $lastName) {
					if($halAut[$i][$cstDI] != "" && strpos($listdocid, $halAut[$i][$cstDI]) === false) {
						insertNode($xml, $halAut[$i][$cstDI], $cstAU, $cstAF, $i, "idno", "type", "halauthorid", "", "", "iB");
						$xml->save($nomfic);
						$listdocid .= $halAut[$i][$cstDI].'~';
					}
				}
			}
			*/
		}
	}

	//Fin des premières modifications du TEI
}
?>
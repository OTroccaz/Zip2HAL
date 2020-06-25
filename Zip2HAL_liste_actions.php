<?php
include "./Zip2HAL_nodes.php";
include "./Zip2HAL_codes_pays.php";
include "./Zip2HAL_codes_langues.php";

$cstVal = "valeur";
$cstEX = "exact";
$cstCC = "classCode";
$cstAM = "amont";
$cstXL = "xml:lang";
$cstAN = "analytic";
$cstTI = "title";
$cstAU = "author";
$cstTN = "tagName";
$cstLI = "licence";
$cstNO = "nonodevalue";
$cstDP = "datePub";
$cstIM = "imprint";
$cstDE = "dateEpub";
$cstKE = "keywords";
$cstAB = "abstract";
$cstPD = "profileDesc";

$halID = "";

$action = $_POST["action"];
$valeur = str_replace('"', '\"', $_POST[$cstVal]);
if (isset($_POST["langue"])) {
	$lang = $_POST["langue"];
	$codeLang = $languages[$lang];
}

if ($action != "statistiques") {
	//Chargement du fichier XML
	$nomfic = $_POST["nomfic"];
	$xml = new DOMDocument( "1.0", "UTF-8" );
	$xml->formatOutput = true;
	$xml->preserveWhiteSpace = false;
	$xml->load($nomfic);
	$xml->save($nomfic);
}

//Actions
//Supprimer le TEI
if ($action == "suppression") {
	if(file_exists ($nomfic)) {@unlink($nomfic);}
}

//Domaine
if ($action == "domaine") {
	deleteNode($xml, "textClass", $cstCC, 0, "scheme", "halDomain", "", "", $cstEX);
	$xml->save($nomfic);
	$tabDom = explode(" ~ ", str_replace("’", "'", $valeur));
	insertNode($xml, $tabDom[0], "textClass", $cstCC, 0, $cstCC, "scheme", "halDomain", "n", $tabDom[1], "aC", $cstAM , "");
	$xml->save($nomfic);
}

//Titre
if ($action == "titre") {
	deleteNode($xml, $cstAN, $cstTI, 0, $cstXL, $codeLang, "", "", $cstEX);
	$xml->save($nomfic);
	insertNode($xml, $valeur, $cstAN, $cstAU, 0, $cstTI, $cstXL, $codeLang, "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Titre traduit
if ($action == "titreT") {
	deleteNode($xml, $cstAN, $cstTI, 0, $cstXL, "en", "", "", $cstEX);
	$xml->save($nomfic);
	insertNode($xml, $valeur, $cstAN, $cstAU, 0, $cstTI, $cstXL, "en", "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Notice
if ($action == "notice") {
	$valeur2 = str_replace('"', '\"', $_POST["valeur2"]);
	deleteNode($xml, "edition", "ref", 0, "type", "file", "", "", $cstEX);
	$edt = $xml->getElementsByTagName('edition');
	$bip = $xml->createElement("ref");
	$bip->setAttribute("type", "file");
	$bip->setAttribute("subtype", $valeur2);
	$bip->setAttribute("n", "1");
	$bip->setAttribute("target", $valeur);
	$edt->item(0)->appendChild($bip);
	$xml->save($nomfic);
}

//Licence
if ($action == $cstLI) {
	deleteNode($xml, "availability", $cstLI, 0, "", "", "", "", $cstEX);
	insertNode($xml, $cstNO, "availability", "", 0, $cstLI, "target", $valeur, "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Date de publication
if ($action == $cstDP) {
	insertNode($xml, $valeur, $cstIM, "", 0, "date", "type", $cstDP, "", "", "aC", $cstTN, "");
	$xml->save($nomfic);
}

//Date d'édition
if ($action == $cstDE) {
	deleteNode($xml, $cstIM, "date", 0, "type", $cstDE, "", "", $cstEX);
	insertNode($xml, $valeur, $cstIM, "", 0, "date", "type", $cstDE, "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Langue
if ($action == "language") {
	if(isset($languages[$valeur])) {$language = $languages[$valeur];}else{$language = "";}
	insertNode($xml, $valeur, "langUsage", "", 0, "language", "ident", $language, "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
	
	//Ajout de la langue au titre
	$titreOK = "non";
	$elts = $xml->getElementsByTagName($cstTI);
	foreach($elts as $elt) {
		if ($elt->hasAttribute($cstXL)) {
			if ($titreOK == "non") {//Le titre est parfois présent plusieurs fois
				deleteNode($xml, $cstAN, $cstTI, 0, "", "", "", "", $cstEX);
				$xml->save($nomfic);
				insertNode($xml, $elt->nodeValue, $cstAN, $cstAU, 0, $cstTI, $cstXL, $language, "", "", "iB", $cstTN, "");
			}
		}
	}
	
	//Ajout de la langue aux mots-clés
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
		$bimoc->setAttribute($cstXL, $language);
		$bimoc->appendChild($moc);
		$key->appendChild($bimoc);																		
		$xml->save($nomfic);
	}
	
	//Ajout de la langue au résumé
	$elts = $xml->getElementsByTagName($cstAB);
	foreach($elts as $elt) {
		if ($elt->hasAttribute($cstXL)) {
			deleteNode($xml, $cstPD, $cstAB, 0, "", "", "", "", $cstEX);
			$xml->save($nomfic);
			insertNode($xml, $elt->nodeValue, $cstPD, "", 0, $cstAB, $cstXL, $language, "", "", "iB", $cstTN, "");
			$xml->save($nomfic);
		}
	}
}

//Revue
if ($action == "revue") {
	deleteNode($xml, "monogr", $cstTI, 0, "level", "j", "", "", $cstEX);
	$xml->save($nomfic);
	insertNode($xml, $valeur, "monogr", $cstIM, 0, $cstTI, "level", "j", "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Audience
if ($action == "audience") {
	insertNode($xml, $cstNO, "notesStmt", "", 0, "note", "type", "audience", "n", $valeur, "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Vulgarisation
if ($action == "vulgarisation") {
	deleteNode($xml, "notesStmt", "note", 0, "type", "popular", "", "", $cstEX);
	$xml->save($nomfic);
	if ($valeur == "Yes") {$val = "1";}else{$val = "0";}
	insertNode($xml, $valeur, "notesStmt", "", 0, "note", "type", "popular", "n", $val, "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Comité de lecture
if ($action == "peer") {
	deleteNode($xml, "notesStmt", "note", 0, "type", "peer", "", "", $cstEX);
	$xml->save($nomfic);
	if ($valeur == "Yes") {$val = "1";}else{$val = "0";}
	insertNode($xml, $valeur, "notesStmt", "", 0, "note", "type", "peer", "n", $val, "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Editeur
if ($action == "editeur") {
	deleteNode($xml, $cstIM, "publisher", 0, "", "", "", "", $cstEX);
	$xml->save($nomfic);
	insertNode($xml, $valeur, $cstIM, "biblScope", 0, "publisher", "", "", "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
}

//ISSN
if ($action == "issn") {
	deleteNode($xml, "monogr", "idno", 0, "type", "issn", "", "", $cstEX);
	$xml->save($nomfic);
	insertNode($xml, $valeur, "monogr", $cstTI, 0, "idno", "type", "issn", "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
}

//EISSN
if ($action == "eissn") {
	deleteNode($xml, "monogr", "idno", 0, "type", "eissn", "", "", $cstEX);
	$xml->save($nomfic);
	insertNode($xml, $valeur, "monogr", $cstTI, 0, "idno", "type", "eissn", "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Métadonnées spécifiques aux COMM et POSTER

	////COMM ou POSTER > Titre du volume
	if ($action == "titreV") {
		deleteNode($xml, $cstIM, "biblScope", 0, "unit", "serie", "", "", $cstEX);
		$xml->save($nomfic);
		insertNode($xml, $valeur, $cstIM, "date", 0, "biblScope", "unit", "serie", "", "", "iB", $cstTN, "");
		$xml->save($nomfic);
	}
	
	//COMM ou POSTER > Ville de la conférence
	if ($action == "ville") {
		deleteNode($xml, "meeting", "settlement", 0, "", "", "", "", $cstEX);
		$xml->save($nomfic);
		//Le noeud 'settlement' doit obligatoirement être situé après la date de fin s'il y en a une, sinon après la date de début 
		$ajout = "non";
		$bimoc = $xml->createElement("settlement");
		$moc = $xml->createTextNode($valeur);
		$bimoc->appendChild($moc);
		$elts = $xml->getElementsByTagName("date");
		foreach($elts as $elt) {
			if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "end") {
				insertAfter($bimoc, $elt);
				$xml->save($nomfic);
				$ajout = "oui";
			}
		}
		if ($ajout == "non") {
			foreach($elts as $elt) {
				if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "start") {
					insertAfter($bimoc, $elt);
					$xml->save($nomfic);
					$ajout = "oui";
				}
			}
		}
	}

	//COMM ou POSTER > Date de début de conférence
	if ($action == "startDate") {
		deleteNode($xml, "meeting", "date", 0, "type", "start", "", "", $cstEX);
		$xml->save($nomfic);
		insertNode($xml, $valeur, "meeting", $cstTI, 0, "date", "type", "start", "", "", "iA", $cstTN, "");
		$xml->save($nomfic);
	}

	//COMM ou POSTER > Date de fin de conférence
	if ($action == "endDate") {
		deleteNode($xml, "meeting", "date", 0, "type", "end", "", "", $cstEX);
		$xml->save($nomfic);
		insertNode($xml, $valeur, "meeting", "date", 0, "date", "type", "end", "", "", "iA", $cstTN, "");
		$xml->save($nomfic);
	}

	//COMM ou POSTER > Titre de la conférence
	if ($action == "titreConf") {
		deleteNode($xml, "meeting", $cstTI, 0, "", "", "", "", $cstEX);
		$xml->save($nomfic);
		//$bimoc = $xml->createElement($cstTI);
		//$moc = $xml->createTextNode($valeur);
		//$bimoc->appendChild($moc);
		insertNode($xml, $valeur, "meeting", "date", 0, $cstTI, "", "", "", "", "iB", $cstTN, "");
		$xml->save($nomfic);
	}

	//COMM ou POSTER > Pays de la conférence
	if ($action == "paysConf") {
		$valeur = str_replace("’", "'", $valeur);
		$pays = strtoupper($countries[$valeur]);
		deleteNode($xml, "meeting", "country", 0, "", "", "", "", $cstEX);
		$xml->save($nomfic);
		insertNode($xml, $cstNO, "meeting", "", 0, "country", "key", $pays, "", "", "aC", $cstTN, "");
		$xml->save($nomfic);
	}

	//COMM ou POSTER > ISBN de la conférence
	if ($action == "isbnConf") {
		deleteNode($xml, "monogr", "idno", 0, "type", "isbn", "", "", $cstEX);
		$xml->save($nomfic);
		insertNode($xml, $valeur, "monogr", $cstTI, 0, "idno", "type", "isbn", "", "", "iB", $cstTN, "");
		$xml->save($nomfic);
	}

	//COMM ou POSTER > Proceedings de la conférence
	if ($action == "procConf") {
		deleteNode($xml, "notesStmt", "note", 0, "type", "proceedings", "", "", $cstEX);
		$xml->save($nomfic);
		if ($valeur == "Yes") {$val = "1";}else{$val = "0";}
		insertNode($xml, $valeur, "notesStmt", "", 0, "note", "type", "proceedings", "n", $val, "iB", $cstTN, "");
		$xml->save($nomfic);
	}

	//COMM ou POSTER > Editeur scientifique
	if ($action == "scientificEditor") {
		deleteNode($xml, "monogr", "editor", 0, "", "", "", "", $cstEX);
		$xml->save($nomfic);
		insertNode($xml, $valeur, "monogr", "meeting", 0, "editor", "", "", "", "", "iA", $cstTN, "");
		$xml->save($nomfic);
	}
	
	//COMM ou POSTER > Conférence invitée O/N
	if ($action == "invitConf") {
		deleteNode($xml, "notesStmt", "note", 0, "type", "invited", "", "", $cstEX);
		$xml->save($nomfic);
		if ($valeur == "Yes") {$val = "1";}else{$val = "0";}
		insertNode($xml, $valeur, "notesStmt", "", 0, "note", "type", "invited", "n", $val, "iB", $cstTN, "");
		$xml->save($nomfic);
	}
//Fin métadonnées spécifiques aux COMM et POSTER

//Métadonnées spécifiques aux COUV
	//COUV > Titre de l'ouvrage
	if ($action == "titrOuv") {
		deleteNode($xml, "monogr", $cstTI, 0, "level", "m", "", "", $cstEX);
		$xml->save($nomfic);
		insertNode($xml, $valeur, "monogr", "editor", 0, $cstTI, "level", "m", "", "", "iB", $cstTN, "");
		$xml->save($nomfic);
	}
	
	//COUV > Editeur(s) scientifique(s)
	if ($action == "editOuv") {
		$elts = $xml->getElementsByTagName("monogr");
		$ind = 0;
		$pos = $_POST["pos"];
		foreach($elts as $elt) {
			if ($elt->hasChildNodes()) {
				foreach($elt->childNodes as $item) {
					if ($item->nodeName == "editor") {
						if ($ind != $pos) {
						}else{
							$bimoc = $xml->createElement("editor");
							$moc = $xml->createTextNode($valeur);
							$bimoc->appendChild($moc);
							$elt->replaceChild($bimoc, $item);
							break 2;
						}
						$ind++;
					}
				}
			}
		}
		$xml->save($nomfic);
	}
	
	//COUV > ISBN
	if ($action == "isbnOuv") {
		deleteNode($xml, "monogr", "idno", 0, "type", "isbn", "", "", $cstEX);
		$xml->save($nomfic);
		insertNode($xml, $valeur, "monogr", $cstTI, 0, "idno", "type", "isbn", "", "", "iB", $cstTN, "");
		$xml->save($nomfic);
	}
	
//Fin métadonnées spécifiques aux COUV


//Volume
if ($action == "volume") {
	deleteNode($xml, $cstIM, "biblScope", 0, "unit", "volume", "", "", $cstEX);
	$xml->save($nomfic);
	insertNode($xml, $valeur, $cstIM, "date", 0, "biblScope", "unit", "volume", "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Numéro
if ($action == "issue") {
	deleteNode($xml, $cstIM, "biblScope", 0, "unit", "issue", "", "", $cstEX);
	$xml->save($nomfic);
	insertNode($xml, $valeur, $cstIM, "date", 0, "biblScope", "unit", "issue", "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Pages
if ($action == "pages") {
	deleteNode($xml, $cstIM, "biblScope", 0, "unit", "pp", "", "", $cstEX);
	$xml->save($nomfic);
	insertNode($xml, $valeur, $cstIM, "date", 0, "biblScope", "unit", "pp", "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Financement
if ($action == "financement") {
	deleteNode($xml, "titleStmt", "funder", 0, "", "", "", "", $cstEX);
	$xml->save($nomfic);
	insertNode($xml, $valeur, "titleStmt", "", 0, "funder", "", "", "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Financement ANR
if ($action == "ANR") {
	$tabVal = explode("~", $valeur);
	$docid = $tabVal[0];
	$ref = "projanr-".$docid;
	$label = $tabVal[1];
	$tabLab = explode("[", $label);
	$titre = trim($tabLab[0]);
	$acron = trim(str_replace("]", "", $tabLab[1]));
	$ref_s = trim(str_replace("]", "", $tabLab[2]));
	$annee = $tabVal[2];
	$valid = $tabVal[3];
	
	insertNode($xml, $cstNO, "titleStmt", "", 0, "funder", "ref", "#".$ref, "", "", "aC", $cstTN, "");
	$xml->save($nomfic);
	
	//Y-a-t-il déjà un noeud listOrg pour les projets ?
	$listOrg = "non";
	$orgs = $xml->getElementsByTagName("listOrg");
	foreach($orgs as $org) {
		if ($org->hasAttribute("type") && $org->getAttribute("type") == "projects") {
			$listOrg = "oui";
		}
	}
	if ($listOrg == "non") {
		insertNode($xml, $cstNO, "back", "", 0, "listOrg", "type", "projects", "", "", "aC", $cstTN, "");
		$xml->save($nomfic);
	}
	
	//Positionnement au noeud <listOrg type="projects"> pour ajout des noeuds enfants
	foreach($orgs as $org) {
		if ($org->hasAttribute("type") && $org->getAttribute("type") == "projects") {
			break;
		}
	}
	$bimoc = $xml->createElement("org");
	$moc = $xml->createTextNode("");
	$bimoc->setAttribute("type", "anrProject");
	$bimoc->setAttribute("xml:id", $ref);
	$bimoc->setAttribute("status", $valid);
	$bimoc->appendChild($moc);
	$org->appendChild($bimoc);
	$xml->save($nomfic);
	
	$orgs = $xml->getElementsByTagName("org");
	foreach($orgs as $org) {
		if ($org->hasAttribute("xml:id") && $org->getAttribute("xml:id") == $ref) {
			break;
		}
	}
	$bimoc = $xml->createElement("idno");
	$moc = $xml->createTextNode($ref_s);
	$bimoc->setAttribute("type", "anr");
	$bimoc->appendChild($moc);
	$org->appendChild($bimoc);
	$xml->save($nomfic);
	
	$bimoc = $xml->createElement("orgName");
	$moc = $xml->createTextNode($acron);
	$bimoc->appendChild($moc);
	$org->appendChild($bimoc);
	$xml->save($nomfic);
	
	$bimoc = $xml->createElement("desc");
	$moc = $xml->createTextNode($titre);
	$bimoc->appendChild($moc);
	$org->appendChild($bimoc);
	$xml->save($nomfic);
	
	$bimoc = $xml->createElement("date");
	$moc = $xml->createTextNode($annee);
	$bimoc->setAttribute("type", "start");
	$bimoc->appendChild($moc);
	$org->appendChild($bimoc);
	$xml->save($nomfic);
}

//Financement EUR
if ($action == "EUR") {
	$tabVal = explode("~", $valeur);
	$docid = $tabVal[0];
	$ref = "projeurop-".$docid;
	$ref_s = $tabVal[1];
	$finan = $tabVal[2];
	$calid = $tabVal[3];
	$acron = $tabVal[4];
	$titre = $tabVal[5];
	$anneS = $tabVal[6];
	$anneE = $tabVal[7];
	$valid = $tabVal[8];
	
	insertNode($xml, $cstNO, "titleStmt", "", 0, "funder", "ref", "#".$ref, "", "", "aC", $cstTN, "");
	$xml->save($nomfic);
	
	//Y-a-t-il déjà un noeud listOrg pour les projets ?
	$listOrg = "non";
	$orgs = $xml->getElementsByTagName("listOrg");
	foreach($orgs as $org) {
		if ($org->hasAttribute("type") && $org->getAttribute("type") == "projects") {
			$listOrg = "oui";
		}
	}
	if ($listOrg == "non") {
		insertNode($xml, $cstNO, "back", "", 0, "listOrg", "type", "projects", "", "", "aC", $cstTN, "");
		$xml->save($nomfic);
	}
	
	//Positionnement au noeud <listOrg type="projects"> pour ajout des noeuds enfants
	foreach($orgs as $org) {
		if ($org->hasAttribute("type") && $org->getAttribute("type") == "projects") {
			break;
		}
	}
	$bimoc = $xml->createElement("org");
	$moc = $xml->createTextNode("");
	$bimoc->setAttribute("type", "europeanProject");
	$bimoc->setAttribute("xml:id", $ref);
	$bimoc->setAttribute("status", $valid);
	$bimoc->appendChild($moc);
	$org->appendChild($bimoc);
	$xml->save($nomfic);
	
	$orgs = $xml->getElementsByTagName("org");
	foreach($orgs as $org) {
		if ($org->hasAttribute("xml:id") && $org->getAttribute("xml:id") == $ref) {
			break;
		}
	}
	
	$bimoc = $xml->createElement("idno");
	$moc = $xml->createTextNode($ref_s);
	$bimoc->setAttribute("type", "number");
	$bimoc->appendChild($moc);
	$org->appendChild($bimoc);
	$xml->save($nomfic);
	
	$bimoc = $xml->createElement("idno");
	$moc = $xml->createTextNode($finan);
	$bimoc->setAttribute("type", "program");
	$bimoc->appendChild($moc);
	$org->appendChild($bimoc);
	$xml->save($nomfic);
	
	$bimoc = $xml->createElement("idno");
	$moc = $xml->createTextNode($calid);
	$bimoc->setAttribute("type", "call");
	$bimoc->appendChild($moc);
	$org->appendChild($bimoc);
	$xml->save($nomfic);
	
	$bimoc = $xml->createElement("orgName");
	$moc = $xml->createTextNode($acron);
	$bimoc->appendChild($moc);
	$org->appendChild($bimoc);
	$xml->save($nomfic);
	
	$bimoc = $xml->createElement("desc");
	$moc = $xml->createTextNode($titre);
	$bimoc->appendChild($moc);
	$org->appendChild($bimoc);
	$xml->save($nomfic);
	
	$bimoc = $xml->createElement("date");
	$moc = $xml->createTextNode($anneS);
	$bimoc->setAttribute("type", "start");
	$bimoc->appendChild($moc);
	$org->appendChild($bimoc);
	$xml->save($nomfic);
	
	$bimoc = $xml->createElement("date");
	$moc = $xml->createTextNode($anneE);
	$bimoc->setAttribute("type", "end");
	$bimoc->appendChild($moc);
	$org->appendChild($bimoc);
	$xml->save($nomfic);
}

//Mots-clés
if ($action == "mots-cles") {
	$keys = $xml->getElementsByTagName($cstKE);
	$ind = 0;
	$pos = $_POST["pos"];
	foreach($keys as $key) {
		foreach($key->childNodes as $elt) {
			if ($elt->hasAttribute($cstXL) && ($elt->getAttribute($cstXL) == $codeLang || $elt->getAttribute($cstXL) == "")) {
				if ($ind != $pos) {
				}else{
					$bimoc = $xml->createElement("term");
					$moc = $xml->createTextNode($valeur);
					$bimoc->setAttribute($cstXL, $codeLang);
					$bimoc->appendChild($moc);
					$key->replaceChild($bimoc, $elt);
					break 2;
				}
			}
			$ind++;
		}
	}
	$xml->save($nomfic);
}

//Mots-clés traduits
if ($action == "mots-clesT") {
	$keys = $xml->getElementsByTagName($cstKE);
	$ind = 0;
	$exist = "non";
	$pos = $_POST["pos"];
	foreach($keys as $key) {
		foreach($key->childNodes as $elt) {
			if ($elt->hasAttribute($cstXL) && ($elt->getAttribute($cstXL) == "en")) {
				if ($ind != $pos) {
				}else{
					$bimoc = $xml->createElement("term");
					$moc = $xml->createTextNode($valeur);
					$bimoc->setAttribute($cstXL, "en");
					$bimoc->appendChild($moc);
					$key->replaceChild($bimoc, $elt);
					$exist = "oui";
					break 2;
				}
			}
			$ind++;
		}
	}
	$xml->save($nomfic);
	if ($exist == "non") {
		insertNode($xml, $valeur, $cstKE, "", 0, "term", $cstXL, "en", "", "", "aC", $cstTN, "");
		$xml->save($nomfic);
	}
}

//Ajout de mots-clés
if ($action == "ajout-mots-cles") {
	$keys = $xml->getElementsByTagName($cstKE);
	$ind = 0;
	$exist = "non";
	$pos = $_POST["pos"];
	foreach($keys as $key) {
		foreach($key->childNodes as $elt) {
			if ($elt->hasAttribute($cstXL) && ($elt->getAttribute($cstXL) == $codeLang)) {
				if ($ind != $pos) {
				}else{
					$bimoc = $xml->createElement("term");
					$moc = $xml->createTextNode($valeur);
					$bimoc->setAttribute($cstXL, $codeLang);
					$bimoc->appendChild($moc);
					$key->replaceChild($bimoc, $elt);
					$exist = "oui";
					break 2;
				}
			}
			$ind++;
		}
	}
	$xml->save($nomfic);
	if ($exist == "non") {
		foreach($keys as $key) {
			foreach($key->childNodes as $elt) {
				if ($elt->hasAttribute($cstXL) && ($elt->getAttribute($cstXL) == $codeLang)) {
					if ($ind != $pos) {
					}else{
						$bimoc = $xml->createElement("term");
						$moc = $xml->createTextNode($valeur);
						$bimoc->setAttribute($cstXL, $codeLang);
						$bimoc->appendChild($moc);
						$key->appendChild($bimoc);
						break 2;
					}
				}
				$ind++;
			}
		}
	}
	$xml->save($nomfic);
}

//Résumé
if ($action == $cstAB) {
	deleteNode($xml, $cstPD, $cstAB, 0, $cstXL, $codeLang, "", "", $cstEX);
	$xml->save($nomfic);
	insertNode($xml, $valeur, $cstPD, "", 0, $cstAB, $cstXL, $codeLang, "", "", "iB", $cstTN, "");
	$xml->save($nomfic);
}

//Résumé traduit
if ($action == "abstractT") {
	deleteNode($xml, $cstPD, $cstAB, 0, $cstXL, "en", "", "", $cstEX);
	$xml->save($nomfic);
	//insertNode($xml, $valeur, $cstPD, "", 0, $cstAB, $cstXL, "en", "", "", "iB", $cstTN, "");
	$elts = $xml->getElementsByTagName($cstPD);
	$bimoc = $xml->createElement($cstAB);
	$moc = $xml->createTextNode($valeur);
	$bimoc->setAttribute($cstXL, "en");
	$bimoc->appendChild($moc);
	$elts->item(0)->appendChild($bimoc);	
	$xml->save($nomfic);
}

//Supprimer un auteur
if ($action == "supprimerAuteur") {
	$i = $_POST["pos"];
	$tabVal = explode(' ~ ', $valeur);
	$firstName = $tabVal[0];
	$lastName = $tabVal[1];
	$cpt = 0;

	$auts = $xml->getElementsByTagName($cstAU);
	foreach($auts as $aut) {
		if ($aut->hasChildNodes()) {
			foreach($aut->childNodes as $item) {
				if ($item->hasChildNodes()) {
					foreach($item->childNodes as $nompre) {
						if ($nompre->nodeName == "forename") {$prenom = $nompre->nodeValue;}
					}
					foreach($item->childNodes as $nompre) {
						if ($nompre->nodeName == "surname") {$nom = $nompre->nodeValue;}
					}
					if ($prenom == $firstName && $nom == $lastName) {
						$qui = $xml->getElementsByTagName($cstAU)[$cpt];
						$qui->parentNode->removeChild($qui);
						break 2;
					}
				}
			}
		}
		$cpt++;
	}
	$xml->save($nomfic);
}

//Ajouter un idHAL
if ($action == "ajouterIdHAL") {
	$i = $_POST["pos"];
	if ($valeur == "") {//C'est en fait une suppression
		deleteNode($xml, $cstAU, "idno", $i, "type", "idhal", "notation", "string", $cstEX);
		deleteNode($xml, $cstAU, "idno", $i, "type", "idhal", "notation", "numeric", $cstEX);
		deleteNode($xml, $cstAU, "idno", $i, "type", "halauthorid", "", "", $cstEX);
		$xml->save($nomfic);
	}else{
		$tabVal = explode('(', $valeur);
		deleteNode($xml, $cstAU, "idno", $i, "type", "idhal", "notation", "string", $cstEX);
		deleteNode($xml, $cstAU, "idno", $i, "type", "idhal", "notation", "numeric", $cstEX);
		$xml->save($nomfic);
		//insertNode($xml, trim($tabVal[0]), $cstAU, "affiliation", $i, "idno", "type", "idhal", "notation", "string", "iB", $cstAM , "");
		//insertNode($xml, trim(str_replace(')', '', $tabVal[1])), $cstAU, "affiliation", $i, "idno", "type", "idhal", "notation", "numeric", "iB", $cstAM , "");
		insertNode($xml, trim(str_replace(')', '', $tabVal[1])), $cstAU, "persName", $i, "idno", "type", "idhal", "notation", "numeric", "iA", $cstAM , "");
		insertNode($xml, trim($tabVal[0]), $cstAU, "persName", $i, "idno", "type", "idhal", "notation", "string", "iA", $cstAM , "");
		$xml->save($nomfic);
	}
}

//Supprimer un idHAL
if ($action == "supprimerIdHAL") {
	$i = $_POST["pos"];
	deleteNode($xml, $cstAU, "idno", $i, "type", "idhal", "notation", "string", $cstEX);
	deleteNode($xml, $cstAU, "idno", $i, "type", "idhal", "notation", "numeric", $cstEX);
	deleteNode($xml, $cstAU, "idno", $i, "type", "halauthorid", "", "", $cstEX);
	$xml->save($nomfic);
}

//Ajouter une affiliation
if ($action == "ajouterAffil") {
	$i = $_POST["pos"];
	$tabVal = explode('~', $valeur);
	$affil = "#struct-".trim($tabVal[0]);
	insertNode($xml, $cstNO, $cstAU, "persName", $i, "affiliation", "ref", $affil, "", "", "aC", $cstAM , "");
	$xml->save($nomfic);
}

//Supprimer une affiliation
if ($action == "supprimerAffil") {
	$i = $_POST["pos"];
	$affil = "#struct-".$valeur;
	deleteNode($xml, $cstAU, "affiliation", $i, "ref", $affil, "", "", $cstEX);
	$xml->save($nomfic);
}

//Ajouter un auteur
if ($action == "ajouterAuteur") {
	$tabVal = explode(' ', $valeur);
	$i = $_POST["pos"];
	//deleteNode($xml, $cstAU, $cstAU, $i, "role", "aut", "", "", $cstEX);
	$cpt = 0;//Boucle pour retrouver $pos
	$elts = $xml->getElementsByTagName($cstAU);		
	foreach ($elts as $elt) {
		if ($cpt != $i) {
		}else{
			$elt->parentNode->removeChild($elt);
			//$elts->removeChild($elt);
			break;
		}
		$cpt++;
	}
	$xml->save($nomfic);
	
	$aut = $xml->getElementsByTagName('analytic')->item(0);
	$biaut = $xml->createElement($cstAU);
	$biaut->setAttribute("role", "aut");
	$aut->appendChild($biaut);
	$xml->save($nomfic);

	$aut = $xml->getElementsByTagName('author')->item($i);
	$biaut = $xml->createElement("persName");
	$aut->appendChild($biaut);
	$xml->save($nomfic);

	$aut = $xml->getElementsByTagName('persName')->item($i);
	$biaut = $xml->createElement("forename");
	$biaut->setAttribute("type", "first");
	$cTn = $xml->createTextNode($tabVal[0]);
	$biaut->appendChild($cTn);
	$aut->appendChild($biaut);
	$xml->save($nomfic);
	
	$biaut = $xml->createElement("surname");
	$cTn = $xml->createTextNode($tabVal[1]);
	$biaut->appendChild($cTn);
	$aut->appendChild($biaut);
	$xml->save($nomfic);
	
}

if ($action == "statistiques") {
	$idTEI = $_POST["idTEI"];
	$typDoc = $_POST["typDoc"];
	$titreNot = $_POST["titreNot"];
	$datePub = $_POST[$cstDP];
	$Fnm = "./Zip2HAL_actions.php";
	include $Fnm;
	array_multisort($ACTIONS_LISTE);

	$tabAct = explode("~", $action);
	foreach ($tabAct as $act) {
		if ($act != "") {
			$ajout = count($ACTIONS_LISTE);
			$ACTIONS_LISTE[$ajout]["quand"] = time();
			$ACTIONS_LISTE[$ajout][$cstVal] = $valeur.".xml";
			$ACTIONS_LISTE[$ajout]["titre"] = $titreNot;
			$ACTIONS_LISTE[$ajout]["type"] = $typDoc;
			$ACTIONS_LISTE[$ajout]["annee"] = $datePub;
			$ACTIONS_LISTE[$ajout]["idHAL"] = $idTEI;
		}
	}
	$total = count($ACTIONS_LISTE);

	$inF = fopen($Fnm,"w");
	fseek($inF, 0);
	$chaine = "";
	$chaine .= '<?php'.chr(13);
	$chaine .= '$ACTIONS_LISTE = array('.chr(13);
	fwrite($inF,$chaine);
	foreach($ACTIONS_LISTE AS $i => $valeur) {
		$chaine = $i.' => array(';
		$chaine .= '"quand"=>"'.$ACTIONS_LISTE[$i]["quand"].'", ';
		$chaine .= '"valeur"=>"'.$ACTIONS_LISTE[$i][$cstVal].'", ';
		$chaine .= '"titre"=>"'.$ACTIONS_LISTE[$i]["titre"].'", ';
		$chaine .= '"type"=>"'.$ACTIONS_LISTE[$i]["type"].'", ';
		$chaine .= '"annee"=>"'.$ACTIONS_LISTE[$i]["annee"].'", ';
		$chaine .= '"idHAL"=>"'.$ACTIONS_LISTE[$i]["idHAL"].'")';
		if ($i != $total-1) {$chaine .= ',';}
		$chaine .= chr(13);
		//session 1 day test
		//$hier = time() - 86400;
		//session 7 days test
		//$hier = time() - 604800;
		//if ($ACTIONS_LISTE[$i]["quand"] > $hier) {
			fwrite($inF,$chaine);
		//}else{
			//$i -= 1;
		//}
	}
	$chaine = ');'.chr(13);
	$chaine .= '?>';
	fwrite($inF,$chaine);
	fclose($inF);
	array_multisort($ACTIONS_LISTE);
}

?>
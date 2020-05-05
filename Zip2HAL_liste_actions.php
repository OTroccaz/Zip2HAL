<?php
include "./Zip2HAL_nodes.php";
include "./Zip2HAL_codes_pays.php";
$halID = "";
$nomfic = $_POST["nomfic"];
$action = $_POST["action"];
$valeur = str_replace('"', '\"', $_POST["valeur"]);
if (isset($_POST["langue"])) {
	$lang = $_POST["langue"];
	$lang = $countries[$lang];
}

//Chargement du fichier XML
$xml = new DOMDocument( "1.0", "UTF-8" );
$xml->formatOutput = true;
$xml->preserveWhiteSpace = false;
$xml->load($nomfic);
$xml->save($nomfic);

//Actions
//Titre
if ($action == "titre") {
	insertNode($xml, $valeur, "analytic", "", 0, "title", "xml:lang", $lang, "", "", "iB", "tagName", "");
	$xml->save($nomfic);
}

//Notice
if ($action == "notice") {
	$valeur2 = str_replace('"', '\"', $_POST["valeur2"]);
	deleteNode($xml, "edition", "ref", 0, "type", "file", "", "", "exact");
	$edt = $xml->getElementsByTagName('edition');
	$bip = $xml->createElement("ref");
	$bip->setAttribute("type", "file");
	$bip->setAttribute("subtype", $valeur2);
	$bip->setAttribute("n", "1");
	$bip->setAttribute("target", $valeur);
	$edt->item(0)->appendChild($bip);
	$xml->save($nomfic);
}

//Date de publication
if ($action == "datePub") {
	insertNode($xml, $valeur, "imprint", "", 0, "date", "type", "datePub", "", "", "iB", "tagName", "");
	$xml->save($nomfic);
}

//Langue
if ($action == "language") {
	$lang = $countries[$valeur];
	insertNode($xml, $valeur, "langUsage", "", 0, "language", "ident", $lang, "", "", "iB", "tagName", "");
	$xml->save($nomfic);
}

//Revue
if ($action == "revue") {
	deleteNode($xml, "monogr", "title", 0, "level", "j", "", "", "exact");
	$xml->save($nomfic);
	insertNode($xml, $valeur, "monogr", "imprint", 0, "title", "level", "j", "", "", "iB", "tagName", "");
	$xml->save($nomfic);
}

//Audience
if ($action == "audience") {
	insertNode($xml, "nonodevalue", "notesStmt", "", 0, "note", "type", "audience", "n", $valeur, "iB", "tagName", "");
	$xml->save($nomfic);
}

//Vulgarisation
if ($action == "vulgarisation") {
	deleteNode($xml, "notesStmt", "note", 0, "type", "popular", "", "", "exact");
	$xml->save($nomfic);
	if ($valeur == "Yes") {$val = "1";}else{$val = "0";}
	insertNode($xml, $valeur, "notesStmt", "", 0, "note", "type", "popular", "n", $val, "iB", "tagName", "");
	$xml->save($nomfic);
}

//Comité de lecture
if ($action == "peer") {
	deleteNode($xml, "notesStmt", "note", 0, "type", "peer", "", "", "exact");
	$xml->save($nomfic);
	if ($valeur == "Yes") {$val = "1";}else{$val = "0";}
	insertNode($xml, $valeur, "notesStmt", "", 0, "note", "type", "peer", "n", $val, "iB", "tagName", "");
	$xml->save($nomfic);
}

//Editeur
if ($action == "editeur") {
	deleteNode($xml, "imprint", "publisher", 0, "", "", "", "", "exact");
	$xml->save($nomfic);
	insertNode($xml, $valeur, "imprint", "biblScope", 0, "publisher", "", "", "", "", "iB", "tagName", "");
	$xml->save($nomfic);
}

//ISSN
if ($action == "issn") {
	deleteNode($xml, "monogr", "idno", 0, "type", "issn", "", "", "exact");
	$xml->save($nomfic);
	insertNode($xml, $valeur, "monogr", "title", 0, "idno", "type", "issn", "", "", "iB", "tagName", "");
	$xml->save($nomfic);
}

//EISSN
if ($action == "eissn") {
	deleteNode($xml, "monogr", "idno", 0, "type", "eissn", "", "", "exact");
	$xml->save($nomfic);
	insertNode($xml, $valeur, "monogr", "title", 0, "idno", "type", "eissn", "", "", "iB", "tagName", "");
	$xml->save($nomfic);
}

//Volume
if ($action == "volume") {
	deleteNode($xml, "imprint", "biblScope", 0, "unit", "volume", "", "", "exact");
	$xml->save($nomfic);
	insertNode($xml, $valeur, "imprint", "date", 0, "biblScope", "unit", "volume", "", "", "iB", "tagName", "");
	$xml->save($nomfic);
}

//Numéro
if ($action == "issue") {
	deleteNode($xml, "imprint", "biblScope", 0, "unit", "issue", "", "", "exact");
	$xml->save($nomfic);
	insertNode($xml, $valeur, "imprint", "date", 0, "biblScope", "unit", "issue", "", "", "iB", "tagName", "");
	$xml->save($nomfic);
}

//Pages
if ($action == "pages") {
	deleteNode($xml, "imprint", "biblScope", 0, "unit", "pp", "", "", "exact");
	$xml->save($nomfic);
	insertNode($xml, $valeur, "imprint", "date", 0, "biblScope", "unit", "pp", "", "", "iB", "tagName", "");
	$xml->save($nomfic);
}

//Financement
if ($action == "financement") {
	deleteNode($xml, "titleStmt", "funder", 0, "", "", "", "", "exact");
	$xml->save($nomfic);
	insertNode($xml, $valeur, "titleStmt", "", 0, "funder", "", "", "", "", "iB", "tagName", "");
	$xml->save($nomfic);
}

//Mots-clés
if ($action == "mots-cles") {
	$lang = $countries[$_POST["langue"]];
	$pos = $_POST["pos"];
	insertNode($xml, $valeur, "keywords", "", $pos, "term", "xml:lang", $lang, "", "", "aC", "tagName", "");
	$xml->save($nomfic);
}
//Ajout de mots-clés
if ($action == "ajout-mots-cles") {
	$lang = $countries[$_POST["langue"]];
	$keyw = $xml->getElementsByTagName('keywords')->item(0);
	$bimoc = $xml->createElement("term");
	$moc = $xml->createTextNode($valeur);
	$bimoc->setAttribute("xml:lang", $lang);
	$bimoc->appendChild($moc);
	$keyw->appendChild($bimoc);
	$xml->save($nomfic);
}

if ($action == "abstract") {
	$lang = $countries[$_POST["langue"]];
	deleteNode($xml, "profileDesc", "abstract", 0, "xml:lang", $lang, "", "", "exact");
	$xml->save($nomfic);
	insertNode($xml, $valeur, "profileDesc", "", 0, "abstract", "xml:lang", $lang, "", "", "iB", "tagName", "");
	$xml->save($nomfic);
}

//Ajouter un idHAL
if ($action == "ajouterIdHAL") {
	$i = $_POST["pos"];
	$tabVal = explode('(', $valeur);
	deleteNode($xml, "author", "idno", $i, "type", "idhal", "notation", "string", "exact");
	deleteNode($xml, "author", "idno", $i, "type", "idhal", "notation", "numeric", "exact");
	$xml->save($nomfic);
	//insertNode($xml, trim($tabVal[0]), "author", "affiliation", $i, "idno", "type", "idhal", "notation", "string", "iB", "amont", "");
	//insertNode($xml, trim(str_replace(')', '', $tabVal[1])), "author", "affiliation", $i, "idno", "type", "idhal", "notation", "numeric", "iB", "amont", "");
	insertNode($xml, trim(str_replace(')', '', $tabVal[1])), "author", "persName", $i, "idno", "type", "idhal", "notation", "numeric", "iA", "amont", "");
	insertNode($xml, trim($tabVal[0]), "author", "persName", $i, "idno", "type", "idhal", "notation", "string", "iA", "amont", "");
	$xml->save($nomfic);
}

//Supprimer un idHAL
if ($action == "supprimerIdHAL") {
	$i = $_POST["pos"];
	deleteNode($xml, "author", "idno", $i, "type", "idhal", "notation", "string", "exact");
	deleteNode($xml, "author", "idno", $i, "type", "idhal", "notation", "numeric", "exact");
	$xml->save($nomfic);
}

//Ajouter une affiliation
if ($action == "ajouterAffil") {
	$i = $_POST["pos"];
	$tabVal = explode('~ ', $valeur);
	$affil = "#struct-".str_replace(array('(', ')'), '', $tabVal[1]);
	insertNode($xml, "nonodevalue", "author", "persName", $i, "affiliation", "ref", $affil, "", "", "aC", "amont", "");
	$xml->save($nomfic);
}

//Supprimer une affiliation
if ($action == "supprimerAffil") {
	$i = $_POST["pos"];
	$affil = "#struct-".$valeur;
	deleteNode($xml, "author", "affiliation", $i, "ref", $affil, "", "", "exact");
	$xml->save($nomfic);
}

//Ajouter un auteur
if ($action == "ajouterAuteur") {
	$tabVal = explode(' ', $valeur);
	$i = $_POST["pos"];
	//deleteNode($xml, "author", "author", $i, "role", "aut", "", "", "exact");
	$cpt = 0;//Boucle pour retrouver $pos
	$elts = $xml->getElementsByTagName("author");		
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
	$biaut = $xml->createElement("author");
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
	$Fnm = "./Zip2HAL_actions.php";
	include $Fnm;
	array_multisort($ACTIONS_LISTE);

	$tabAct = explode("~", $action);
	foreach ($tabAct as $act) {
		if ($act != "") {
			$ajout = count($ACTIONS_LISTE);
			$ACTIONS_LISTE[$ajout]["action"] = $act;
			$ACTIONS_LISTE[$ajout]["valeur"] = $valeur;
			$ACTIONS_LISTE[$ajout]["quand"] = time();
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
		$chaine .= '"action"=>"'.$ACTIONS_LISTE[$i]["action"].'", ';
		$chaine .= '"valeur"=>"'.str_replace('"', '\"', $ACTIONS_LISTE[$i]["valeur"]).'", ';
		$chaine .= '"quand"=>"'.$ACTIONS_LISTE[$i]["quand"].'")';
		if ($i != $total-1) {$chaine .= ',';}
		$chaine .= chr(13);
		//session 1 day test
		//$hier = time() - 86400;
		//session 7 days test
		$hier = time() - 604800;
		if ($ACTIONS_LISTE[$i]["quand"] > $hier) {
			fwrite($inF,$chaine);
		}else{
			$i -= 1;
		}
	}
	$chaine = ');'.chr(13);
	$chaine .= '?>';
	fwrite($inF,$chaine);
	fclose($inF);
	array_multisort($ACTIONS_LISTE);
}

?>
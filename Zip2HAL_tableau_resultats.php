<?php
/*
 * Zip2HAL - Importez vos publications dans HAL - Import your publications into HAL
 *
 * Copyright (C) 2023 Olivier Troccaz (olivier.troccaz@cnrs.fr) and Laurent Jonchère (laurent.jonchere@univ-rennes.fr)
 * Released under the terms and conditions of the GNU General Public License (https://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * Tableau synthétique  des résultats obtenus - Summary table of results obtained
 */
 
include "./Zip2HAL_constantes.php";

if(isset($typDbl) && ($typDbl == "HALCOLLTYP" || $typDbl == "HALTYP")) {//Doublon de type TYP > inutile d'afficher l'avertissement
}else{
	echo '<div class="alert alert-primary mt-3" role="alert">	<strong>Attention : merci de vérifier <u>très attentivement</u> les affiliations et idHAL des auteurs calculés par l\'algorithme (ci-dessous) : les résultats peuvent contenir des erreurs.</strong><br></div>';
}

//Tableau des résultats

echo '<b>Résultats et éventuelle validation finale du TEI pour importation dans HAL</b> <i>(Si la langue doit être modifiée, ceci doit être réalisé en amont de toute autre modification)</i><br>';
//echo 'Fichier '.$nomfic.'<br>';
//echo '<br>';
//echo '';
if(isset($typDbl) && ($typDbl == "HALCOLLTYP" || $typDbl == "HALTYP")) {//Doublon de type TYP > tableau non responsive
	echo '<table class=\'table table-striped table-sm table-bordered table-hover small\'>';
}else{
	echo '<table class=\'table table-striped table-sm table-bordered table-hover table-responsive small\'>';
}
echo '<thead class=\'thead-dark\'>';
echo '<tr>';
//echo '<th class=\'text-center\'><b>ID</b></td>';
//Doublon ?
if(isset($typDbl) && $typDbl != "") {
	echo '<th class=\'text-center\'><b>Doublon</b></td>';
}
echo '<th class=\'text-center\'><b>Supprimer</b></td>';
echo '<th class=\'text-center\'><b>Type de document</b></td>';
echo '<th class=\'text-center\'><b>Métadonnées</b></td>';
echo '<th class=\'text-center\'><b>Liens</b></td>';
echo '<th class=\'text-center col-md-3\'><b>Auteurs* / affiliations</b></td>';
echo '<th class=\'text-center\'><b>Validation du TEI modifié</td>';
echo '<th class=\'text-center\'><b>Importer dans HAL</b></td>';
echo '</tr>';
echo '<tbody>';

$cpt = 1;

echo '<tr class=\'text-center\'>';

//Numérotation > id
//echo '<td>'.$cpt.'</td>';

//Doublon ?
if(isset($typDbl) && $typDbl != "") {
	echo '<td><a target=\'_blank\' href=\'https://hal.archives-ouvertes.fr/'.$idTEI.'\'><img alt=\'HAL\' src=\'./img/HAL.jpg\'></a></td>';
}

//Supprimer le TEI
echo '<td><span id=\'suppression'.'-'.$idFic.'\'><a class="btn btn-primary" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'suppression\', valeur : \''.$idFic.'\',}); majokSuppr(\'suppression'.'-'.$idFic.'\');"><i class="mdi mdi-delete text-white"></i></a></span></td>';

//Type de document
$typDoc = "";
//$nomficZip = $_SERVER['REQUEST_URI'];
$elts = $xml->getElementsByTagName("classCode");
foreach($elts as $elt) {
	if($elt->hasAttribute("scheme") && $elt->getAttribute("scheme") == "halTypology") {$typDoc = $elt->getAttribute("n");}
}
$temps = str_replace(array('./XML/Zip2HAL_TEI_OverHAL_', '.zip'), '', $nomficZip);
echo '<td>';
//echo '<select class="custom-select" id="typdoc" name="typdoc" style="width: 100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', nomficHal : \''.str_replace($dir."/", "./XML/HAL/", $nomfic).'\', nomficZip : \''.$nomficZip.'\', temps : \''.$temps.'\', action: \'typedoc\', valeur: $(this).val()}); let myForm = document.querySelector(\'form\'); let submitButton = myForm.querySelector(\'#soumis\'); myForm.requestSubmit(submitButton);">';
echo '<select class="custom-select" id="typdoc" name="typdoc" style="width: 100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'typedoc\', valeur: $(this).val(), init: \''.$typDoc.'\'}); afftypedoc($(this).val(), '.$idFic.');">';
$sel = ($typDoc == 'ART' || $typDoc == 'PATENT') ? 'selected="selected"' : '';
echo '<option value="ART~|~Article dans une revue" '.$sel.'>ART</option>';
$sel = ($typDoc == 'COMM') ? 'selected="selected"' : '';
echo '<option value="COMM~|~Communication dans un congrès" '.$sel.'>COMM</option>';
$sel = ($typDoc == 'POSTER') ? 'selected="selected"' : '';
echo '<option value="POSTER~|~Poster de conférence" '.$sel.'>POSTER</option>';
$sel = ($typDoc == 'COUV') ? 'selected="selected"' : '';
echo '<option value="COUV~|~Chapitre dtroliaposouvrage" '.$sel.'>COUV</option>';
echo '</select>';
echo '</td>';

if(isset($typDbl) && ($typDbl == "HALCOLLTYP" || $typDbl == "HALTYP")) {//Doublon de type TYP > inutile d'afficher les métadonnées
	echo $cstSP;
}else{
	$tabMetaMQ[$nomfic] = array();//Tableau regroupant les métadonnées obligatoires manquantes
	//Métadonnées
	echo '<td style=\'text-align: left;\'><span id=\'metadonnees-'.$idFic.'\'>';
	
	//Métadonnées > Langue
	echo '<p class="form-inline">Langue* :&nbsp;<input id="language-'.$idFic.'" name="language-'.$idFic.'" value="'.$lang.'" class="autoLang form-control" style="height: 18px; width:150px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'language\', valeur: $(this).val(), language: $(this).val()}); afficacherLang($(this).val(), '.$idFic.');"></p>';
	
	if($lang == "") {$tabMetaMQ[$nomfic][] = "la langue";}
	
	//Domaine
	$domOK = "non";
	$elts = $xml->getElementsByTagName("classCode");
	foreach($elts as $elt) {
		if($domOK == "non") {
			echo '<p class="form-inline">Domaine :&nbsp;<input type="text" id="domaine-'.$idFic.'" name="domaine-'.$idFic.'" value="'.$domaine.'" class="form-control" style="height: 18px; width:400px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'domaine\', valeur: $(this).val()});"></p>';
			$domOK = "oui";
		}
	}
	echo 'Pour modifier le domaine, si vous connaissez une partie du code, utilisez le champ ci-dessous puis validez avec le bouton vert, autrement, l\'arborescence dynamique ci-après. :';
	echo '<span id="choixdom-'.$idFic.'">';
	echo '<br>';
	echo '<p class="form-inline"><input type="text" id ="inputdom-'.$idFic.'" name="inputdom-'.$idFic.'" class="autoDO form-control" style="margin-left: 30px; height: 18px; width:300px">';
	echo '&nbsp;<b>+</b>&nbsp;<a style="cursor:pointer;" onclick=\'document.getElementById("domaine-'.$idFic.'").value=$("#inputdom-'.$idFic.'").val(); $.post("Zip2HAL_liste_actions.php", {nomfic : "'.$nomfic.'", action: "domaine", valeur: $("#inputdom-'.$idFic.'").val()});\'><span class="btn btn-success p-0"><i class="mdi mdi-check-outline"></i></span></a></p>';

	$codI = "";
	$cpt = 1;
	$reqAPI = "https://api.archives-ouvertes.fr/ref/domain/?q=*:*&fl=code_s,fr_domain_s&rows=500&sort=code_s%20ASC";
	$contents = file_get_contents($reqAPI);
	$results = json_decode($contents);
	foreach($results->response->docs as $entry) {
		$code = $entry->code_s;
		$tabCode = explode(".", $code);
		$codF = $tabCode[0];
		if($codI != $codF) {//Nouveau groupe de disciplines
			if($cpt != 1) {echo '</span>';}
			$domF = str_replace("'", "’", $entry->fr_domain_s);
			echo '<span style=\'margin-left: 30px;\' id=\'cod-'.$cpt.'-'.$idFic.'\'><a style=\'cursor:pointer;\' onclick=\'afficacher('.$cpt.', '.$idFic.');\';><font style=\'color: #FE6D02;\'><b>>&nbsp;</b></font></a></span>';
			echo '<span><a style=\'cursor:pointer;\' onclick=\'document.getElementById("domaine-'.$idFic.'").value="'.$domF.' ~ '.$code.'"; $.post("Zip2HAL_liste_actions.php", {nomfic : "'.$nomfic.'", action: "domaine", valeur: "'.$domF.' ~ '.$code.'"});\'>'.$domF.'</a></span><br>';
			$codI = $codF;
			echo '<span id=\'dom-'.$cpt.'-'.$idFic.'\' style=\'display:none;\'>';
			$cpt++;
		}else{//Liste des différentes sous-matières de la discipline
			$sMat = str_replace($domF.'/', '', str_replace("'", "’", $entry->fr_domain_s));
			$sMatVal = str_replace("'", "’", $entry->fr_domain_s);
			//$sMatTab = explode("/", $entry->fr_domain_s);
			//$num = count($sMatTab) - 1;
			//$sMatVal = $sMatTab[$num];
			$code = $entry->code_s;
			echo '<span style=\'margin-left: 60px;\'><a style=\'cursor:pointer;\' onclick=\'document.getElementById("domaine-'.$idFic.'").value="'.$sMatVal.' ~ '.$code.'"; $.post("Zip2HAL_liste_actions.php", {nomfic : "'.$nomfic.'", action: "domaine", valeur: "'.$sMatVal.' ~ '.$code.'"});\'>'.$sMat.'</a></span><br>';
		}
	}
	echo '</span><br>';
	
	//Métadonnées > DOI
	echo '<p class="form-inline">DOI :&nbsp;<input type="text" id="doi-'.$idFic.'" name="doi-'.$idFic.'" value="'.$doiTEI.'" class="form-control" style="height: 18px; width:300px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'doi\', valeur: $(this).val()});"></p>';
	
	//Métadonnées > Partage de dépôt
	$nomfic2 = str_replace(array('./XML/', '.xml'), '', $nomfic);
	echo '<p class="form-inline">Partager ce dépôt avec :&nbsp;<a href="#" data-toggle="tooltip" data-html="true" title="" data-original-title="<strong>Renseignez le login</strong><br>Plusieurs valeurs possibles, séparées par un point-virgule : login1;login2;etc."><i class="mdi mdi-account-question text-info mdi-18px"></i></a>&nbsp;<input type="text" id="partDep-'.$idFic.'" name="partDep-'.$idFic.'" value="'.$partDep.'" class="form-control" style="height: 18px; width:300px;" onkeyup="majpartDep(\''.$nomfic2.'\', \''.$idFic.'\', $(this).val());"></p>';
	
	//Métadonnées > Titre
	$titreNot = "";
	$elts = $xml->getElementsByTagName($cstTI);
	foreach($elts as $elt) {
		if($elt->hasAttribute($cstXL) && ($elt->getAttribute($cstXL) == $languages[$lang] || $elt->getAttribute($cstXL) == "")) {
			$titreNot = str_replace(array("<i>", "</i>", "[", "]"), "", $elt->nodeValue);
			$testMeta = "ok";
		}
	}
	echo 'Titre* :&nbsp;<textarea id="titre-'.$idFic.'" name="titre-'.$idFic.'" class="textarea form-control" style="width: 600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'titre\', valeur: $(this).val(), langue : \''.$lang.'\'});">'.$titreNot.'</textarea><br>';
	
	if($titreNot == "") {$tabMetaMQ[$nomfic][] = "le titre";}
	
	
	//Métadonnées > Titre traduit en anglais
	if($lang != $cstEN) {$affT = "block;";}else{$affT = "none;";}
		
	$titreT = "";
	//Le titre traduit est-il déjà présent ?
	$elts = $xml->getElementsByTagName($cstTI);
	foreach($elts as $elt) {
		if($elt->hasAttribute($cstXL) && ($elt->getAttribute($cstXL) == "en")) {$titreT = $elt->nodeValue;}
	}
	echo '<span id="lantitreT-'.$idFic.'" style="display:'.$affT.'">Titre traduit :&nbsp;<textarea id="titreT-'.$idFic.'" name="titreT-'.$idFic.'" class="textarea form-control" style="width: 600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'titreT\', valeur: $(this).val(), langue : \'English\'});">'.$titreT.'</textarea><br></span>';
		
	
	//Métadonnées > Notice
	$target = "";
	$elts = $xml->getElementsByTagName("ref");
	foreach($elts as $elt) {
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "file") {
			if($elt->hasAttribute("target")) {$target = $elt->getAttribute("target");}
		}
	}
	if($target != "") {//N'afficher les métadonnées de la notice uniquement s'il y en a une
		echo '<p class="form-inline">Texte intégral :&nbsp;<input type="text" id="notice-'.$idFic.'" name="notice-'.$idFic.'" value="'.$target.'" class="form-control" style="height: 18px; width:350px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'notice\', valeur: $(this).val(), valeur2: $(\'#subtype-'.$idFic.'\').val()});">';
		if($target != "") {echo '&nbsp;-&nbsp;<a target="_blank" href="'.$target.'">Lien</a></p>';}
		//Subtype
		echo '<p class="form-inline">Type de dépôt :&nbsp;<select id="subtype-'.$idFic.'" name="subtype-'.$idFic.'" class="form-control" style="height: 18px; padding: 0px; width:150px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', nomfic : \''.$nomfic.'\', action: \'notice\', valeur: $(\'#notice-'.$idFic.'\').val(), valeur2: $(this).val()});">';
		if($elt->getAttribute($cstSU) == "author") {$txt = $cstSE;}else{$txt = "";}
		echo '<option '.$txt.' value="author">author</option>';
		if($elt->getAttribute($cstSU) == "greenPublisher") {$txt = $cstSE;}else{$txt = "";}
		echo '<option '.$txt.' value="greenPublisher">greenPublisher</option>';
		if($elt->getAttribute($cstSU) == "publisherPaid") {$txt = $cstSE;}else{$txt = "";}
		echo '<option '.$txt.' value="publisherPaid">publisherPaid</option>';
		if($elt->getAttribute($cstSU) == "noaction") {$txt = $cstSE;}else{$txt = "";}
		echo '<option '.$txt.' value="noaction">noaction</option>';
		echo '</select></p>';
		//Licence
		$licence = "";
		$elts = $xml->getElementsByTagName("licence");
		foreach($elts as $elt) {
			if($elt->hasAttribute("target")) {
				$licence = $elt->getAttribute("target");
			}
		}
		echo '<p class="form-inline">Licence :&nbsp;<input type="text-'.$idFic.'" id="licence" name="licence-'.$idFic.'" value="'.$licence.'" class="form-control" style="height: 18px; width:400px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'licence\', valeur: $(this).val()});">';
	}
	
	//Métadonnées > Date de publication
	$datePub = "";
	$elts = $xml->getElementsByTagName("date");
	foreach($elts as $elt) {
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "datePub") {
			$datePub = $elt->nodeValue;
			$testMeta = "ok";
		}
	}
	echo '<p class="form-inline">Date de publication* :&nbsp;<input type="text" id="datePub-'.$idFic.'" name="datePub-'.$idFic.'" value="'.$datePub.'" class="form-control" style="height: 18px; width:200px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'datePub\', valeur: $(this).val()});"></p>';
	
	if($datePub == "") {$tabMetaMQ[$nomfic][] = "l\'année de publication";}
	
	//Métadonnées > Date d'édition
	$dateEpub = "";
	$elts = $xml->getElementsByTagName("date");
	foreach($elts as $elt) {
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "dateEpub") {
			$dateEpub = $elt->nodeValue;
		}
	}
	echo '<p class="form-inline">Date de mise en ligne :&nbsp;<input type="text" id="dateEpub-'.$idFic.'" name="dateEpub-'.$idFic.'" value="'.$dateEpub.'" class="form-control" style="height: 18px; width:200px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'dateEpub\', valeur: $(this).val()});"></p>';

	//Métadonnées > Audience, vulgarisation et comité de lecture
	$testMetaA = "";
	$testMetaV = "";
	$testMetaC = "";
	$elts = $xml->getElementsByTagName("note");
	foreach($elts as $elt) {
		//Audience
		if (isset($testMetaA) && $testMetaA != "ok") {$testMetaA = "";}
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "audience") {
			echo '<p class="form-inline">Audience* :&nbsp;';
			echo '<select id="audience-'.$idFic.'" name="audience" class="form-control" style="height: 18px; padding: 0px; width:150px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'audience\', valeur: $(this).val()});">';
			$valAud = $elt->getAttribute("n");
			if($valAud == "") {$txt = $cstSE;}else{$txt = "";}
			echo '<option '.$txt.' value="">Inconnue</option>';
			if($valAud == 2) {$txt = $cstSE; $testMetaA = "ok";}else{$txt = "";}
			echo '<option '.$txt.' value="2">Internationale</option>';
			if($valAud == 3) {$txt = $cstSE; $testMetaA = "ok";}else{$txt = "";}
			echo '<option '.$txt.' value="3">Nationale</option>';
			echo '</select></p>';
		}
		
		//Vulgarisation
		$txtVO = "";
		$txtVN = "";
		if (isset($testMetaV) && $testMetaV != "ok") {$testMetaV = "";}
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "popular") {
			if($elt->nodeValue == "Yes") {$txtVO = $cstCH; $txtVN = ""; $testMetaV = "ok";}
			if($elt->nodeValue == "No") {$txtVO = ""; $txtVN = $cstCH; $testMetaV = "ok";}
			echo '<p class="form-inline">Vulgarisation* :&nbsp;';
			echo '<input type="radio" '.$txtVO.' id="popular-'.$idFic.'" name="popular-'.$idFic.'" value="Yes" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'vulgarisation\', valeur: $(this).val()});">&nbsp;Oui';
			echo $cst5sp;
			echo '<input type="radio" '.$txtVN.' id="popular-'.$idFic.'" name="popular-'.$idFic.'" value="No" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'vulgarisation\', valeur: $(this).val()});">&nbsp;Non';
			echo '</p>';
		}
		
		//Comité de lecture
		$txtCO = "";
		$txtCN = "";
		if (isset($testMetaC) && $testMetaC != "ok") {$testMetaC = "";}
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "peer") {
			if($elt->nodeValue == "Yes") {$txtCO = $cstCH; $txtCN = ""; $testMetaC = "ok";}
			if($elt->nodeValue == "No") {$txtCO = ""; $txtCN = $cstCH; $testMetaC = "ok";}
			echo '<p class="form-inline">Comité de lecture* :&nbsp;';
			echo '<input type="radio" '.$txtCO.' id="peer-'.$idFic.'" name="peer-'.$idFic.'" value="Yes" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'peer\', valeur: $(this).val()});">&nbsp;Oui';
			echo $cst5sp;
			echo '<input type="radio" '.$txtCN.' id="peer-'.$idFic.'" name="peer-'.$idFic.'" value="No" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'peer\', valeur: $(this).val()});">&nbsp;Non';
			echo '</p>';
		}
	}

	if($testMetaA == "") {$tabMetaMQ[$nomfic][] = "l\'audience";}
	if($testMetaV == "") {$tabMetaMQ[$nomfic][] = "la vulgarisation";}
	if($testMetaC == "") {$tabMetaMQ[$nomfic][] = "le comité de lecture";}
	
	//Métadonnées spécifiques aux ART
	$affArt = ($typDoc == "ART") ? 'block' : 'none';
	echo '<span id="Art-'.$idFic.'" style="display:'.$affArt.'">';
	
		//Métadonnées > Revue
		//if($typDoc == "ART") {
			$nomRevue = "";
			$elts = $xml->getElementsByTagName($cstTI);
			foreach($elts as $elt) {
				if($elt->hasAttribute($cstLE) && $elt->getAttribute($cstLE) == "j") {
					$nomRevue = $elt->nodeValue;
					$testMeta = "ok";
				}
			}
			echo '<p class="form-inline">Nom de la revue* :&nbsp;<input type="text" id="revue-'.$idFic.'" name="revue-'.$idFic.'" value="'.$nomRevue.'" class="form-control" style="height: 18px; width:600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'revue\', valeur: $(this).val()});"></p>';
			
			if($nomRevue == "") {$tabMetaMQ[$nomfic][] = "le titre de la revue";}
		//}
		
		//Métadonnées > Editeur
		$editeur = "non";
		$elts = $xml->getElementsByTagName("publisher");
		foreach($elts as $elt) {
			echo '<p class="form-inline">Editeur :&nbsp;<input type="text" id="publisher-'.$idFic.'" name="publisher-'.$idFic.'" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'editeur\', valeur: $(this).val()});"></p>';
			$editeur = "oui";
		}
		if ($editeur == "non") {
			echo '<p class="form-inline">Editeur :&nbsp;<input type="text" id="publisher-'.$idFic.'" name="publisher-'.$idFic.'" value="" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'editeur\', valeur: $(this).val()});"></p>';
		}
	echo '</span>';
	//Fin métadonnées spécifiques aux ART
	
	//Métadonnées > ISSN et EISSN
	$issn = "";
	$eissn = "";
	$elts = $xml->getElementsByTagName("idno");
	foreach($elts as $elt) {
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "issn") {$issn = $elt->nodeValue;}
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "eissn") {$eissn = $elt->nodeValue;}
	}
	echo '<p class="form-inline">ISSN :&nbsp;<input type="text" id="issn-'.$idFic.'" name="issn-'.$idFic.'" value="'.$issn.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'issn\', valeur: $(this).val()});"></p>';
	echo '<p class="form-inline">EISSN :&nbsp;<input type="text" id="eissn-'.$idFic.'" name="eissn-'.$idFic.'" value="'.$eissn.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'eissn\', valeur: $(this).val()});"></p>';
	
	//Métadonnées spécifiques aux COMM et POSTER
	$affComPos = ($typDoc == "COMM" || $typDoc == "POSTER") ? 'block' : 'none';
	echo '<span id="ComPos-'.$idFic.'" style="display:'.$affComPos.'">';
	//if($typDoc == "COMM" || $typDoc == "POSTER") {
		
		//Métadonnées > Nom de la revue, ville, dates, titre, pays, ISBN, actes, éditeur scientifique et conférence invitée
		$titreV = "";
		$settlement = "";
		$startDate = "";
		$endDate = "";
		$titreConf = "";
		$affPays = "";
		$paysConf = "";
		$isbnConf = "";
		$procConf = "";
		$editConf = "";
		$inviConf = "";
		
		//Métadonnées > Titre du volume
		$elts = $xml->getElementsByTagName($cstTI);
		foreach($elts as $elt) {
			foreach($elts as $elt) {
				if($elt->hasAttribute($cstLE) && ($elt->getAttribute($cstLE) == "j")) {
					$titreV = $elt->nodeValue;
					//Déplacement du noeud depuis <title> vers <imprint>
					deleteNode($xml, $cstMO, $cstTI, 0, $cstLE, "j", "", "", "exact");
					$xml->save($nomfic);
					insertNode($xml, $elt->nodeValue, "imprint", "date", 0, "biblScope", "unit", "serie", "", "", "iB", "tagName", "");
					$xml->save($nomfic);
				}
			}
		}
		echo 'Titre du volume :&nbsp;<textarea id="titreV-'.$idFic.'" name="titreV-'.$idFic.'" class="textarea form-control" style="width: 600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'titreV\', valeur: $(this).val(), langue : \''.$lang.'\'});">'.$titreV.'</textarea><br>';
		
		$elts = $xml->getElementsByTagName("meeting");
		foreach($elts as $elt) {
			if($elt->hasChildNodes()) {
				foreach($elt->childNodes as $item) {
					//Ville de la conférence
					if($item->nodeName == "settlement") {$settlement = $item->nodeValue;}
					//Date de début de conférence
					if($item->nodeName == "date" && $item->hasAttribute("type") && $item->getAttribute("type") == "start") {$startDate = $item->nodeValue;}
					//Date de fin de conférence
					if($item->nodeName == "date" && $item->hasAttribute("type") && $item->getAttribute("type") == "end") {$endDate = $item->nodeValue;}
					//Titre de la conférence
					if($item->nodeName == $cstTI) {$titreConf = $item->nodeValue;}
					//Pays de la conférence
					$affPays = "";
					if($item->nodeName == "country" && $item->hasAttribute("key")) {
						$paysConf = $item->getAttribute("key");
						$valPays = array_values($countries);
						$keyPays = array_keys($countries);
						for($i = 0; $i < count($countries); $i++) {
							if(strtolower($paysConf) == $valPays[$i]) {$affPays = $keyPays[$i];}
						}
					}
				}
			}
		}
		
		if($settlement == "" && ($typDoc == "COMM" || $typDoc == "POSTER")) {$tabMetaMQ[$nomfic][] = "la ville de la conférence";}
		if($startDate == "" && ($typDoc == "COMM" || $typDoc == "POSTER")) {$tabMetaMQ[$nomfic][] = "la date de début de la conférence";}
		if($paysConf == "" && ($typDoc == "COMM" || $typDoc == "POSTER")) {$tabMetaMQ[$nomfic][] = "le pays de la conférence";}
		if($titreConf == "" && ($typDoc == "COMM" || $typDoc == "POSTER")) {$tabMetaMQ[$nomfic][] = "le titre de la conférence";}
		
		$txtPO = "";
		$txtPN = "";
		$elts = $xml->getElementsByTagName("idno");
		foreach($elts as $elt) {
			//ISBN
			if($elt->hasAttribute("type") && $elt->getAttribute("type") == "isbn") {$isbnConf = $elt->nodeValue;}
		}
		$elts = $xml->getElementsByTagName($cstMO);
		foreach($elts as $elt) {
			if($elt->hasChildNodes()) {
				foreach($elt->childNodes as $item) {
					//Editeur scientifique
					if($item->nodeName == "editor") {$editConf = $item->nodeValue;}
				}
			}
		}
		$elts = $xml->getElementsByTagName("note");
		foreach($elts as $elt) {
			//Conférence invitée O/N
			//Par défaut, la conférence est considérée comme conférence non invitée
			$inviConf = "No";
			if($elt->hasAttribute("type") && $elt->getAttribute("type") == "invited") {
				if($elt->nodeValue == 0) {$inviConf = "No";}
				if($elt->nodeValue == 1) {$inviConf = "Yes";}
			}
			//Proceedings O/N
			//Par défaut, les proceedings sont renseignés comme présents
			$txtPO = $cstCH;
			$txtPN = "";
			if($elt->hasAttribute("type") && $elt->getAttribute("type") == "proceedings") {
				if($elt->nodeValue == "Yes") {$txtPO = $cstCH; $txtPN = "";}
				if($elt->nodeValue == "No") {$txtPO = ""; $txtPN = $cstCH;}
			}
		}
		
		if($inviConf == "" && ($typDoc == "COMM" || $typDoc == "POSTER")) {$tabMetaMQ[$nomfic][] = "le caractère conférence invitée (O/N)";}
		
		//Ville de la conférence
		echo '<p class="form-inline">Ville* :&nbsp;<input type="text" id="settlement-'.$idFic.'" name="settlement-'.$idFic.'" value="'.$settlement.'" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ville\', valeur: $(this).val()});"></p>';
		//Date de début de conférence
		echo '<p class="form-inline">Date de début de conférence* :&nbsp;<input type="text" id="startDate-'.$idFic.'" name="startDate-'.$idFic.'" value="'.$startDate.'" class="form-control" style="height: 18px; width:140px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'startDate\', valeur: $(this).val()});"></p>';
		//Date de fin de conférence
		echo '<p class="form-inline">Date de fin de conférence :&nbsp;<input type="text" id="endDate-'.$idFic.'" name="endDate-'.$idFic.'" value="'.$endDate.'" class="form-control" style="height: 18px; width:140px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'endDate\', valeur: $(this).val()});"></p>';
		//Titre de la conférence
		echo 'Titre de la conférence* :&nbsp;<textarea id="titreConf-'.$idFic.'" name="titreConf-'.$idFic.'" class="textarea form-control" style="width: 600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'titreConf\', valeur: $(this).val()});">'.$titreConf.'</textarea><br>';
		//Pays de la conférence
		echo '<p class="form-inline">Pays* :&nbsp;<input type="text" id="paysConf-'.$idFic.'" name="paysConf-'.$idFic.'" value="'.$affPays.'" class="autoPays form-control" style="height: 18px; width:200px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'paysConf\', valeur: $(this).val()});"></p>';
		//ISBN de la conférence
		echo '<p class="form-inline">ISBN :&nbsp;<input type="text" id="isbnConf-'.$idFic.'" name="isbnConf-'.$idFic.'" value="'.$isbnConf.'" class="form-control" style="height: 18px; width:200px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'isbnConf\', valeur: $(this).val()});"></p>';
		//Proceedings de la conférence
		echo '<p class="form-inline">Proceedings :&nbsp;';
		echo '<input type="radio" '.$txtPO.' id="procConf-'.$idFic.'" name="procConf-'.$idFic.'" value="Yes" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'procConf\', valeur: $(this).val()});"> Oui';
		echo $cst5sp;
		echo '<input type="radio" '.$txtPN.' id="procConf-'.$idFic.'" name="procConf-'.$idFic.'" value="No" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'procConf\', valeur: $(this).val()});"> Non';
		echo '</p>';
		//Editeur scientifique
		echo '<p class="form-inline">Editeur scientifique :&nbsp;<input type="text" id="scientificEditor-'.$idFic.'" name="scientificEditor-'.$idFic.'" value="'.$editConf.'" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'scientificEditor\', valeur: $(this).val()});"></p>';
		//Conférence invitée O/N
		$txtCO = "";
		$txtCN = "";
		if($inviConf == "Yes") {$txtCO = $cstCH; $txtCN = "";}
		if($inviConf == "No") {$txtCO = ""; $txtCN = $cstCH;}
			echo '<p class="form-inline">Conférence invitée* :&nbsp;';
			echo '<input type="radio" '.$txtCO.' id="invitConf-'.$idFic.'" name="invitConf-'.$idFic.'" value="Yes" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'invitConf\', valeur: $(this).val()});"> Oui';
			echo $cst5sp;
			echo '<input type="radio" '.$txtCN.' id="invitConf-'.$idFic.'" name="invitConf-'.$idFic.'" value="No" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'invitConf\', valeur: $(this).val()});"> Non';
			echo '</p>';
	//}
	echo '</span>';
	//Fin métadonnées spécifiques aux COMM et POSTER
	
	
	//Métadonnées spécifiques aux COUV
	$affCouv = ($typDoc == "COUV") ? 'block' : 'none';
	echo '<span id="Couv-'.$idFic.'" style="display:'.$affCouv.'">';
	//if($typDoc == "COUV") {
		
		//Métadonnées > Titre de l'ouvrage, éditeur(s) scientifique(s) et ISBN
		$titrOuv = "";
		$editOuv = array();
		$isbnOuv = "";
		
		$elts = $xml->getElementsByTagName($cstMO);
		foreach($elts as $elt) {
			if($elt->hasChildNodes()) {
				foreach($elt->childNodes as $item) {
					//Titre de l'ouvrage
					if($item->nodeName == $cstTI && $item->hasAttribute($cstLE) && $item->getAttribute($cstLE) == "m") {$titrOuv = $item->nodeValue;}
					//Editeur scientifique
					if($item->nodeName == "editor") {$editOuv[] = $item->nodeValue;}
					//ISBN
					if($item->nodeName == "idno" && $item->hasAttribute("type") && $item->getAttribute("type") == "isbn") {$isbnOuv = $item->nodeValue;}
				}
			}
		}
		
		if($titrOuv == "" && $typDoc == "COUV") {$tabMetaMQ[$nomfic][] = "le tite de l\'ouvrage";}
		
		//Titre de l'ouvrage
		echo '<p class="form-inline">Titre de l\'ouvrage* :<br><input type="text" id="titrOuv-'.$idFic.'" name="titrOuv-'.$idFic.'" value="'.$titrOuv.'" class="form-control" style="height: 18px; width:600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'titrOuv\', valeur: $(this).val()});"></p>';
		//Editeur(s) scientifique(s)
		if (!empty($editOuv)) {
			$ed = 0;
			foreach($editOuv as $edit) {
				echo '<p class="form-inline">Editeur scientifique :&nbsp;<input type="text" id="editOuv-'.$idFic.'-'.$ed.'" name="editOuv-'.$idFic.'-'.$ed.'" value="'.$edit.'" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'editOuv\', pos: '.$ed.', valeur: $(this).val()});"></p>';
				$ed++;
			}
		}else{
			echo '<p class="form-inline">Editeur scientifique :&nbsp;<input type="text" id="editOuv-'.$idFic.'-0" name="editOuv-'.$idFic.'-0" value="" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'editOuv\', pos: 0, valeur: $(this).val()});"></p>';
			echo '<p class="form-inline">Editeur scientifique :&nbsp;<input type="text" id="editOuv-'.$idFic.'-1" name="editOuv-'.$idFic.'-1" value="" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'editOuv\', pos: 1, valeur: $(this).val()});"></p>';
			echo '<p class="form-inline">Editeur scientifique :&nbsp;<input type="text" id="editOuv-'.$idFic.'-2" name="editOuv-'.$idFic.'-2" value="" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'editOuv\', pos: 2, valeur: $(this).val()});"></p>';
		}
		//ISBN
		echo '<p class="form-inline">ISBN :&nbsp;<input type="text" id="isbnOuv-'.$idFic.'" name="isbnOuv-'.$idFic.'" value="'.$isbnOuv.'" class="form-control" style="height: 18px; width:200px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'isbnOuv\', valeur: $(this).val()});"></p>';					
	//}
	echo '</span>';
	//Fin métadonnées spécifiques aux COUV
	
	//Métadonnées > Volume, numéro et pages
	$elts = $xml->getElementsByTagName("biblScope");
	$volume = "";
	$numero = "";
	$pages = "";
	foreach($elts as $elt) {
		if($elt->hasAttribute("unit") && $elt->getAttribute("unit") == "volume") {$volume = $elt->nodeValue;}
		if($elt->hasAttribute("unit") && $elt->getAttribute("unit") == "issue") {$numero = $elt->nodeValue;}
		if($elt->hasAttribute("unit") && $elt->getAttribute("unit") == "pp") {$pages = $elt->nodeValue;}
	}
	echo '<p class="form-inline">Volume :&nbsp;<input type="text" id="volume-'.$idFic.'" name="volume-'.$idFic.'" value="'.$volume.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'volume\', valeur: $(this).val()});"></p>';
	echo '<p class="form-inline">Numéro :&nbsp;<input type="text" id="issue-'.$idFic.'" name="issue-'.$idFic.'" value="'.$numero.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'issue\', valeur: $(this).val()});"></p>';
	echo '<p class="form-inline">Pages :&nbsp;<input type="text" id="pp-'.$idFic.'" name="pp-'.$idFic.'" value="'.$pages.'" class="form-control" style="height: 18px; width:150px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'pages\', valeur: $(this).val()});";"></p>';
	
	//Métadonnées > Financement
	$funder = "non";
	$elts = $xml->getElementsByTagName("funder");
	foreach($elts as $elt) {
		echo 'Financement :&nbsp;<textarea id="funder-'.$idFic.'" name="funder-'.$idFic.'" class="textarea form-control" style="width: 600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'financement\', valeur: $(this).val()});">'.$elt->nodeValue.'</textarea><br>';
		$funder = "oui";
	}
	if ($funder == "non") {
		echo 'Financement :&nbsp;<textarea id="funder-'.$idFic.'" name="funder-'.$idFic.'" class="textarea form-control" style="width: 600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'financement\', valeur: $(this).val()});"></textarea><br>';
	}
	
	//Métadonnées > Financement ANR
	echo 'Indiquez le ou les projets ANR liés à ce travail :<br>';
	for ($iANR=1; $iANR < 4; $iANR++) {
		echo '<input type="text" id="ANR'.$iANR.'-'.$idFic.'" name="ANR'.$iANR.'-'.$idFic.'" class="autoANR form-control" style="height: 18px; width: 600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ANR\', valeur: $(this).val()});">';
	}
	echo '<br>';
	
	//Métadonnées > Financement EUR
	echo 'Indiquez le ou les projets EU liés à ce travail :<br>';
	for ($iEUR=1; $iEUR < 4; $iEUR++) {
		echo '<input type="text" id="EUR'.$iEUR.'-'.$idFic.'" name="EUR'.$iEUR.'-'.$idFic.'" class="autoEUR form-control" style="height: 18px; width: 600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'EUR\', valeur: $(this).val()});">';
	}
	echo '<br>';
	
	//Métadonnées > Mots-clés
	echo 'Mots-clés :';
	$keys = $xml->getElementsByTagName("keywords");
	$ind = 0;
	foreach($keys as $key) {
		foreach($key->childNodes as $elt) {
			if($elt->hasAttribute($cstXL) && ($elt->getAttribute($cstXL) == $languages[$lang] || $elt->getAttribute($cstXL) == "")) {
				echo '<input type="text" id="mots-cles'.$ind.'-'.$idFic.'" name="mots-cles'.$ind.'-'.$idFic.'" value="'.str_replace("'", "\'", $elt->nodeValue).'" class="form-control" style="height: 18px; width: 600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'mots-cles\', pos: '.$ind.', valeur: $(this).val(), langue: \''.$lang.'\'});">';
			}
			$ind++;
		}
	}
	$nbMC = $ind - 1;
	
	echo '<br>';
	//Métadonnées > Mots-clés > Ajout par liste de mots-clés séparés par des points-virgules
	echo 'Ajout de mots-clés dans la langue de la notice : vous pouvez renseigner ici une liste de plusieurs mots-clés séparés par des points-virgules.';
	echo '<textarea id="mots-cles-liste-'.$idFic.'" name="mots-cles-liste'.$idFic.'" class="textarea form-control" style="width: 600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'mots-cles-liste\', pos: '.$ind.', valeur: $(this).val(), langue: \''.$lang.'\'});"></textarea>';
	
	//Métadonnées > Mots-clés traduits en anglais
	if($lang != $cstEN) {$affMC = "block";}else{$affMC = "none";}
	
	echo '<span id="lanMCT-'.$idFic.'" style="display:'.$affMC.'"><br>Mots-clés traduits :';
	$tabMC = array();
	$keys = $xml->getElementsByTagName("keywords");
	foreach($keys as $key) {
		foreach($key->childNodes as $elt) {
			if($elt->hasAttribute($cstXL) && ($elt->getAttribute($cstXL) == "en")) {$tabMC = $elt->nodeValue;}
		}
	}
	for($mc = 0; $mc <= $nbMC; $mc++) {
		if(isset($tabMC[$mc])) {$mcT = $tabMC[$mc];}else{$mcT = "";}
		echo '<input type="text" id="mots-clesT'.$ind.'-'.$idFic.'" name="mots-clesT'.$ind.'-'.$idFic.'" value="'.str_replace("'", "\'", $mcT).'" class="form-control" style="height: 18px; width: 600px;" onfocusout="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'mots-clesT\', pos: '.$ind.', valeur: $(this).val(), langue: \'English\'});">';
		$ind++;						
	}
	echo '</span>';
	
	/*
	//Ajouter des mots-clés
	echo '<br>';
	echo 'Ajouter des mots-clés :';
	for($dni = $ind; $dni < $ind + 5; $dni++) {
		echo '<input type="text" id="mots-cles'.$dni.'-'.$idFic.'" name="mots-cles'.$dni.'-'.$idFic.'" value="" class="form-control" style="height: 18px; width: 280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajout-mots-cles\', pos: '.$dni.', valeur: $(this).val(), langue: $(\'#language-'.$idFic.'\').val()});">';
	}
	*/
	echo '<br>';
			
	//Métadonnées > Résumé
	$resume = "";
	$elts = $xml->getElementsByTagName("abstract");
	foreach($elts as $elt) {
		if($elt->hasAttribute($cstXL)) {$resume = str_replace(array("<i>", "</i>"), "", $elt->nodeValue);}
	}
	echo 'Résumé :&nbsp;<textarea id="abstract-'.$idFic.'" name="abstract-'.$idFic.'" class="textarea form-control" style="width: 600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'abstract\', valeur: $(this).val(), langue: \''.$lang.'\'});">'.$resume.'</textarea><br>';
	
	//Métadonnées > Résumé traduit en anglais
	if($lang != $cstEN) {$affR = "block";}else{$affR = "none";}
	$resumeT = "";
	//Le résumé traduit est-il déjà présent ?
	$elts = $xml->getElementsByTagName("abstract");
	foreach($elts as $elt) {
		if($elt->hasAttribute($cstXL) && ($elt->getAttribute($cstXL) == "en")) {$resumeT = $elt->nodeValue;}
	}
	echo '<span id="lanresumeT-'.$idFic.'" style="display:'.$affR.'">Résumé traduit:&nbsp;<textarea id="abstractT-'.$idFic.'" name="abstractT-'.$idFic.'" class="textarea form-control" style="width: 600px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'abstractT\', valeur: $(this).val(), langue: \'English\'});">'.$resumeT.'</textarea><br></span>';
	
	echo '</span></td>';
	//Fin des métadonnées
}

//Liens
echo '<td>';
	//DOI
	if(isset($doiTEI) && $doiTEI != "") {echo '<a target=\'_blank\' rel=\'noopener noreferrer\' href=\'https://doi.org/'.$doiTEI.'\'><img alt=\'DOI\' src=\'./img/doi.jpg\'></a>&nbsp;';}

	//PMID
	if(isset($pmiTEI) && $pmiTEI != "") {echo '<br><a target=\'_blank\' rel=\'noopener noreferrer\' href=\'https://pubmed.ncbi.nlm.nih.gov/'.$pmiTEI.'\'><img alt=\'PMID\' src=\'./img/pubmed.png\'></a>&nbsp;';}
	
	//CrossRef
	if(isset($doiTEI) && $doiTEI != "") {echo '<br><a target=\'_blank\' rel=\'noopener noreferrer\' href=\'https://api.crossref.org/v1/works/http:/dx.doi.org/'.$doiTEI.'\'><img alt=\'CrossRef\' src=\'./img/CR.jpg\'></a>&nbsp;';}
echo '</td>';

if(isset($typDbl) && ($typDbl == "HALCOLLTYP" || $typDbl == "HALTYP")) {//Doublon de type TYP > inutile d'afficher les affiliations, la validation du TEI et la possibilité d'import dans HAL
	echo $cstSP;
	echo $cstSP;
	echo $cstSP;
}else{
	if(empty($halAut)) {$tabMetaMQ[$nomfic][] = "les auteurs";}
	//Auteurs / affiliations
	echo '<td style=\'text-align: left;\'><span id=\'affiliations-'.$idFic.'\'>';
	//$i = compteur auteur / $j = compteur affiliation
	for($i = 0; $i < count($halAut); $i++) {
		echo '<span id="PN-aut'.$i.'-'.$idFic.'"><b>'.$halAut[$i][$cstFN].' '.$halAut[$i][$cstLN].'</b></span>';
		
		//Possibilité de supprimer l'auteur
		echo '&nbsp;<span id="Vu-aut'.$i.'-'.$idFic.'"><a style="cursor:pointer;" data-toggle="tooltip" data-html="true" title="<strong>Supprimer l\'auteur ?</strong>" data-original-title="" onclick="event.preventDefault(); afficherPopupConfirmation(\'Êtes-vous sûr de vouloir supprimer cet auteur ?\', \''.$nomfic.'\', '.$i.', \''.$halAut[$i][$cstFN].' ~ '.$halAut[$i][$cstLN].'\', \'aut'.$i.'-'.$idFic.'\');"><i class="mdi mdi-trash-can-outline mdi-18px text-primary"></i></a></span>';
		
		//Début span suppression auteur
		echo '&nbsp;<span id="Sup-aut'.$i.'-'.$idFic.'">';
		
		//Afficher icône mail
		if($halAut[$i]['rolaut'] == "crp") {//Si auteur correspondant
			echo '&nbsp;<span id="Crp-aut'.$i.'-'.$idFic.'"><a href="#" data-toggle="tooltip" data-html="true" title="<strong>Auteur correspondant</strong>" data-original-title=""><i class="mdi mdi-email-outline text-info mdi-18px"></i></a></span>';
		}else{
			if($halAut[$i]['mail'] != "") {//Si email remonté
				echo '&nbsp;<span id="Crp-aut'.$i.'-'.$idFic.'"><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'designerCRP\', pos: '.$i.', valeur: \'crp\'}); majokCRP(\''.$i.'-'.$idFic.'\');"><i class="mdi mdi-email-outline text-gray-700 mdi-18px" title="Désigner comme auteur correspondant ?"></i></a></span>';
			}
		}
		
		echo '<br>';
		
		//Docid
		if (!empty($halAut[$i]['fullName'])) {$qui = "&nbsp;(".$halAut[$i]['fullName'].")";}else{$qui = "";}
		echo 'Ajouter un docid :&nbsp;<span class="form-inline"><input type="text" id="ajoutDocid'.$i.'-'.$idFic.'" name="ajoutDocid'.$i.'-'.$idFic.'" value="'.$halAut[$i][$cstDI].$qui.'" class="form-control" style="height: 18px; width:250px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterDocid\', pos: '.$i.', valeur: $(this).val()}); majokIdHALSuppr(\'ajoutIdh'.$i.'-'.$idFic.'\');">';
		echo '&nbsp;<span id="Vu'.$halAut[$i][$cstDI].'-'.$idFic.'"><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'supprimerDocid\', pos: '.$i.', valeur: \'\'}); majokDocidSuppr(\'ajoutDocid'.$i.'-'.$idFic.'\');"><i class=\'mdi mdi-trash-can-outline mdi-18px text-primary\'></i></a>';
		echo '</span></span>';
		//Domaine du mail remonté par la requête
		echo '<span class="form-inline"><input type="text" id="domMail'.$i.'-'.$idFic.'" name="domMail'.$i.'-'.$idFic.'" value="'.$halAut[$i]['domMail'].'" class="form-control" style="height: 18px; width:250px;"></span>';
		
		if($halAut[$i]['mailDom'] != "") {echo ' (@'.$halAut[$i]['mailDom'].')<br>';}
		//echo '<br>';
		if($halAut[$i][$cstXS] != "") {
			echo '<span id="Txt'.$halAut[$i][$cstXS].'-'.$idFic.'">Supprimer l\'idHAL '.$halAut[$i][$cstXS].'</span> <span id="Vu'.$halAut[$i][$cstXS].'-'.$idFic.'"><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'supprimerIdHAL\', pos: '.$i.', valeur: \''.$halAut[$i][$cstXS].'\'}); majokIdHAL(\''.$halAut[$i][$cstXS].'-'.$idFic.'\');"><i class=\'mdi mdi-trash-can-outline mdi-18px text-primary\'></i></a></span><br>';
		}
		
		if($halAut[$i][$cstIS] != "") {
			//echo 'Remonter le bon auteur du référentiel auteurs <a class=info><img src=\'./img/pdi.jpg\'><span>L\'idHAL n\'est pas ajouté automatiquement car c\'est juste une suggestion que vous devrez valider en l\'ajoutant dans le champ ci-dessous prévu à cet effet.</span></a> :<br><input type="text" id="ajoutidHAL'.$i.'" value="'.$halAutinit[$i][$cstIS].'" name="ajoutidHAL'.$i.'" class="form-control" style="height: 18px; width:200px;">';
			$idHAL = $halAut[$i][$cstIS].' ('.$halAut[$i]['idHali'].')';
		}else{
			$idHAL = "";
		}
		
		//Fond jaune fluo si idHal proposé
		if ($idHAL != "") {$fondidH = "#faed27";}else{$fondidH = "#ffffff;";}
		
		echo 'Ajouter un idHAL :&nbsp;<span class="form-inline"><input type="text" id="ajoutIdh'.$i.'-'.$idFic.'" name="ajoutIdh'.$i.'-'.$idFic.'" value="'.$idHAL.'" class="autoID form-control" style="height: 18px; width:250px; background-color:'.$fondidH.';" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterIdHAL\', pos: '.$i.', valeur: $(this).val()});">';
		echo '&nbsp;<span id="Vu'.$halAut[$i][$cstIS].'-'.$idFic.'"><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'supprimerIdHAL\', pos: '.$i.', valeur: \'\'}); majokIdHALSuppr(\'ajoutIdh'.$i.'-'.$idFic.'\');"><i class=\'mdi mdi-trash-can-outline mdi-18px text-primary\'></i></a>';
		if ($halAut[$i]['orcid'] == "oui") {
			echo '<a href="#" data-toggle="tooltip" data-html="true" title="<strong>IdHAL vérifié par ORCID</strong>" data-original-title=""><i class="mdi mdi-check-bold text-success mdi-18px"></i></a>';
		}else{
			if ($halAut[$i]['resid'] == "oui") {
				echo '<a href="#" data-toggle="tooltip" data-html="true" title="<strong>IdHAL vérifié par ResearcherID</strong>" data-original-title=""><i class="mdi mdi-check-bold text-success mdi-18px"></i></a>';
			}else{
				if ($idHAL != "") {
					echo '<a href="#" data-toggle="tooltip" data-html="true" title="<strong>Est-ce le bon idHAL ?</strong><br>Merci de vérifier." data-original-title=""><i class="mdi mdi-exclamation-thick text-info mdi-18px"></i></a>';
				}
			}
		}
		echo '</span></span><br>';

		echo '<a target="_blank" href="https://aurehal.archives-ouvertes.fr/person/browse?critere='.$halAut[$i][$cstFN].'+'.$halAut[$i][$cstLN].'">Consulter le référentiel auteur</a><br>';
		
		//Lors de l'étape 2 (méthodes 1, 1-2 et 2), d'autres idHAL ont-ils été trouvés via la requête ?
		for($id = 0; $id < count($tabIdHAL); $id++) {
			if(isset($tabIdHAL[$id][$cstFN]) && $tabIdHAL[$id][$cstFN] == $halAut[$i][$cstFN] && isset($tabIdHAL[$id][$cstLN]) && $tabIdHAL[$id][$cstLN] == $halAut[$i][$cstLN]) {
				$reqAut = $tabIdHAL[$id]['reqAut'];
				echo '<a target="_blank" href="'.$reqAut.'"><span class="text-info">D\'autres idHAL ont été trouvés</span></a><br>';
			}
		}
		
		//Affiliations remontées par OverHAL
		echo '<i><font style=\'color: #999999;\'>Affiliation(s) remontée(s) par OverHAL:<br>';
		for($j = 0; $j < count($nomAff); $j++) {
			if($halAutinit[$i][$cstAN] != "" && stripos($halAutinit[$i][$cstAN], $nomAff[$j][$cstLA]) !== false) {
				echo '<span id="aut'.$i.'-nomAff'.$j.'">'.$nomAff[$j]['org'];
				//echo '&nbsp;<i class=\'mdi mdi-trash-can-outline mdi-18px text-primary\'></i></span><br>';
				echo '</span><br>';
			}
		}
		echo '</font></i>';
		$ajtAff = "~";//Pour éviter d'afficher 2 fois des affiliations > méthode 1 / méthode 2 > avec ou sans prénom/nom
		$ajtAffDD = "~";//Drag and drop > Pour éviter de prendre en compte 2 fois des affiliations > méthode 1 / méthode 2 > avec ou sans prénom/nom
		for($j = 0; $j < count($halAff); $j++) {
			if($halAut[$i][$cstAN] != "" && stripos($halAut[$i][$cstAN], $halAff[$j][$cstLA]) !== false && strpos($ajtAff, $halAff[$j][$cstNA]) === false && (($halAut[$i][$cstFN] == $halAff[$j][$cstFN] && $halAut[$i][$cstLN] == $halAff[$j][$cstLN]) || ($halAff[$j][$cstFN] == "" && $halAff[$j][$cstLN] == ""))) {
				if($halAff[$j]['valid'] == "VALID") {$txtcolor = '#697683';}
				if($halAff[$j]['valid'] == "OLD") {$txtcolor = '#ff6600';}
				$ajtAff .= $halAff[$j][$cstNA]."~";
				$halAffVal = str_replace('"', '', $halAff[$j]['ncplt']);
				$halAffVal = str_replace("'", "’", $halAffVal);
				//Test pour vérifier d'éventuelles "scories" dans les affiliations remontées > ref="#struct- (texte au lieu d'un id)"
				$tstTab = explode("~", $halAff[$j]['ncplt']);
				if(!is_int(intval(trim($tstTab[0])))) {
					echo '<div id="warning-alert-modal-'.$idFic.'" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">';
					echo '    <div class="modal-dialog modal-md modal-center">';
					echo '        <div class="modal-content">';
					echo '            <div class="modal-body p-4">';
					echo '                <div class="text-center">';
					echo '                    <i class="dripicons-warning h1 text-warning"></i>';
					echo '                    <h4 class="mt-2">Avertissement</h4>';
					echo '										<p class="mt-3">TEI non valide du fait de l\'affiliation '.$halAff[$j]['ncplt'].'<br>';
					echo '										Vérifiez les champs affiliation du TEI et supprimez les champs erronés du formulaire à l\'aide de la poubelle (même si le champ en question est vide';
					echo '										</p>';
					echo '                    <button type="button" class="btn btn-warning my-2" data-dismiss="modal">Continuer</button>';
					echo '                </div>';
					echo '            </div>';
					echo '        </div><!-- /.modal-content -->';
					echo '    </div><!-- /.modal-dialog -->';
					echo '</div><!-- /.modal -->';
					
					echo '<script type="text/javascript">';
					echo '	(function($) {';
					echo '			"use strict";';
					echo '			$("#warning-alert-modal-'.$idFic.'").modal(';
					echo '					{"show": true, "backdrop": "static"}';
					echo '							)';
					echo '	})(window.jQuery)';
					echo '</script>';
				}
				echo '<span id="aut'.$i.$cstHA.$j.'-'.$idFic.'" draggable="true"><strong><font style=\'color:&nbsp;'.$txtcolor.';\'>'.$halAffVal.'</font></strong></span>';
				echo '&nbsp;<span id="Vu-aut'.$i.$cstHA.$j.'-'.$idFic.'"><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'supprimerAffil\', pos: '.$i.', valeur: \''.$halAff[$j][$cstDI].'\'}); majokAffil(\'aut'.$i.$cstHA.$j.'-'.$idFic.'\', \''.$halAffVal.'\');"><i class=\'mdi mdi-trash-can-outline mdi-18px text-primary\'></i></a></span>';
				if (isset($halAff[$j]['ror']) && $halAff[$j]['ror'] == "oui") {
					echo '<a href="#" data-toggle="tooltip" data-html="true" title="<strong>Affiliation vérifiée par ROR</strong>" data-original-title=""><i class="mdi mdi-check-bold text-success mdi-18px"></i></a>';
				}
				echo '<br>';
			}
		}
		
		//Drag and drop
		for($j = 0; $j < count($halAff); $j++) {
			if($halAut[$i][$cstAN] != "" && stripos($halAut[$i][$cstAN], $halAff[$j][$cstLA]) !== false && strpos($ajtAffDD, $halAff[$j][$cstNA]) === false && (($halAut[$i][$cstFN] == $halAff[$j][$cstFN] && $halAut[$i][$cstLN] == $halAff[$j][$cstLN]) || ($halAff[$j][$cstFN] == "" && $halAff[$j][$cstLN] == ""))) {
				$ajtAffDD .= $halAff[$j][$cstNA]."~";
				echo '<script type="text/javascript">';
				echo '	document.querySelector(\'[id="aut'.$i.$cstHA.$j.'-'.$idFic.'"]\').addEventListener(\'dragstart\', function(e){';
				echo '			e.dataTransfer.setData(\'text\', e.target.innerText);';
				echo '	});';
				echo '</script>';
			}
		}
		
		echo 'Ajouter des affiliations :&nbsp;<br>';
		
		for($dni = $j; $dni < $j + 5; $dni++) {						
			echo '<span class="form-inline"><input type="text" draggable="true" id="aut'.$i.$cstAA.$dni.'-'.$idFic.'" name="aut'.$i.$cstAA.$dni.'-'.$idFic.'" value="" class="autoAF form-control" style="height: 18px; width: 250px;" onclick="this.setSelectionRange(0, this.value.length);" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterAffil\', pos: '.$i.', valeur: $(this).val()});">';
			echo '&nbsp;<span id="Vu-aut'.$i.$cstAA.$dni.'-'.$idFic.'"><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'supprimerAffil\', pos: '.$i.', valeur: $(\'#aut'.$i.$cstAA.$dni.'-'.$idFic.'\').val().split(\'~\')[0].trim()}); majokAffilAjout(\'aut'.$i.$cstAA.$dni.'-'.$idFic.'\');"><i class=\'mdi mdi-trash-can-outline mdi-18px text-primary\'></i></a></span></span>';
			
			//Drag and drop
			echo '<script type="text/javascript">';
			echo '	document.querySelector(\'[id="aut'.$i.$cstAA.$dni.'-'.$idFic.'"]\').addEventListener(\'dragstart\', function(e){';
			echo '			e.dataTransfer.setData(\'text\', e.target.value);';
			echo '	});';
			echo '	var input = document.getElementById("aut'.$i.$cstAA.$dni.'-'.$idFic.'");';
			echo '	input.addEventListener(\'drop\', function (event) {';
			echo '		event.preventDefault();';
			echo '		var textData = event.dataTransfer.getData(\'text\');'; // Récupérer ce qui est déplacé
			echo '		event.target.value = textData;'; // Changer le contenu avec ce qui est déplacé
			echo '		$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterAffil\', pos: '.$i.', valeur: textData});';
			echo '	});';
			echo '</script>';
		}
		//echo '<input type="text" id="ajoutAff'.$i.'" name="ajoutAff'.$i.'" class="autoAF form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterAffil\', pos: '.$i.', valeur: $(this).val()});">';
		echo '</font>';
		
		echo '</span><br>';//Fin span suppression auteur
	}
	
	echo '<br>';
	echo '<b>Ajouter un auteur <i>(Prénom Nom)</i> :&nbsp;</b><input type="text" id="ajoutAuteur-'.$idFic.'" name="ajoutAuteur" class="autoAuteurs form-control" style="height: 18px; width:280px;" onfocusout="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterAuteur\', pos: '.$i.', valeur: $(this).val()});">';
	echo '</span></td>';
	
	//Vérification si des métadonnées sont manquantes
	//var_dump($tabMetaMQ[$nomfic]);
	$maj = "non";
	$message = "";
	if(empty($tabMetaMQ[$nomfic])) {
		$maj = "oui";
	}
	
	//Avant validation du TEI, il faut supprimer toutes les affiliations locales qui peuvent persister (<affiliation ref="#localStruct-Affx"/>
	$domArray = array();
	$affs = $xml->getElementsByTagName($cstAF);
	foreach($affs as $aff) {
		if ($aff->hasAttribute("ref") && strpos($aff->getAttribute("ref"), "localStruct") !== false) {//Affiliation locale
			//Enregistrement de l'affiliation locale
			$domArray[] = $aff;
		}
	}
	//Suppression des affiliations locales
	foreach($domArray as $node){ 
		$node->parentNode->removeChild($node);
	}
	$xml->save($nomfic);
	
	//Validation du TEI
	if($maj == "oui") {
		echo '<td><span id=\'validerTEI-'.$idFic.'\'>';
		//echo '<div id=\'cpt4-'.$idFic.'\'>Validation en cours ...</div>';
		//echo '<script>afficherPopupAttente();</script>';
		
		/*
		echo '<div id="info-alert-modal-'.$idFic.'" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">';
		echo '    <div class="modal-dialog modal-md modal-center">';
		echo '        <div class="modal-content">';
		echo '            <div class="modal-body p-4">';
		echo '                <div class="text-center">';
		echo '                    <i class="dripicons-information h1 text-info"></i>';
		echo '                    <h4 class="mt-2">Veuillez patienter</h4>';
		echo '										<p class="mt-3">Validation du TEI en cours ...</p>';
		//echo '                    <button type="button" class="btn btn-info my-2" data-dismiss="modal">Continuer</button>';
		echo '                </div>';
		echo '            </div>';
		echo '        </div><!-- /.modal-content -->';
		echo '    </div><!-- /.modal-dialog -->';
		echo '</div><!-- /.modal -->';

		echo '<script type="text/javascript">';
		echo '	(function($) {';
		echo '			"use strict";';
		echo '			$("#info-alert-modal-'.$idFic.'").modal(';
		echo '					{"show": true, "backdrop": "static"}';
		echo '							)';
		echo '	})(window.jQuery)';
		echo '</script>';

		ob_flush();
		flush();
		ob_flush();
		flush();
		sleep(1);
		*/
		
		$maj = "non";
		$tst = new DOMDocument();
		$tst->load($nomfic);
		if(!$tst->schemaValidate('./aofr.xsd')) {
			/*
			echo '<script>';
			echo 'document.getElementById(\'cpt4-'.$idFic.'\').style.display = \'none\';';
			echo 'effacerPopup();';
			echo '</script>';
			*/

			/*
			echo '<script type="text/javascript">';
			echo '	(function($) {';
			echo '			"use strict";';
			echo '			$("#info-alert-modal-'.$idFic.'").modal("hide")';
			echo '	})(window.jQuery)';
			//echo 'document.getElementById(\'cpt4-'.$idFic.'\').style.display = \'none\';';
			echo '</script>';
			*/

			echo '<a target=\'_blank\' href=\'https://www.freeformatter.com/xml-validator-xsd.html#\'><i class=\'mdi mdi-trash-can-outline mdi-18px text-primary\'></i></a><br>';
			echo '<a target=\'_blank\' href=\''.$nomfic.'\'>Lien TEI</a><br>';
			print '<b>TEI invalide !</b>';
			libxml_display_errors();
		}else{
			/*
			echo '<script>';
			echo 'document.getElementById(\'cpt4-'.$idFic.'\').style.display = \'none\';';
			echo 'effacerPopup();';
			echo '</script>';
			*/

			/*
			echo '<script type="text/javascript">';
			echo '	(function($) {';
			echo '			"use strict";';
			echo '			$("#info-alert-modal-'.$idFic.'").modal("hide")';
			echo '	})(window.jQuery)';
			//echo 'document.getElementById(\'cpt4-'.$idFic.'\').style.display = \'none\';';
			echo '</script>';
			*/

			echo '<a target=\'_blank\' href=\'https://www.freeformatter.com/xml-validator-xsd.html#\'><i class="mdi mdi-check-bold text-success mdi-18px"></i></a><br>';
			echo '<a target=\'_blank\' href=\''.$nomfic.'\'>Lien TEI</a><br>';			
			$maj = "oui";
		}
		echo '</span></td>';
	}else{
		$titreNotS = str_replace("'", "\'", $titreNot);
		$titreNotS = htmlspecialchars(str_replace("'", "\'", $titreNotS));//les apostrophes sont parfois directement encodées en &#039;
		$titreNotS = str_replace('"', '\"', $titreNotS);
		$titreNotS = str_replace(" ", "%20", $titreNotS);
		$idNomfic = str_replace(array(".xml", "./XML/"), "", $nomfic);
		$lienMAJ = "./Zip2HAL_Modif.php?action=MAJ&Id=".$idNomfic."&portail=".$racine."&partDep=".$partDep;

		echo '<td><center><span id=\'majcont\'></span><span id=\'validerTEI-'.$idFic.'\'>Après avoir complété les champs manquants, cliquez sur l\'icône ci-dessous afin de vérifier la validité du TEI pour pouvoir ensuite l\'importer dans HAL.<br><a style="cursor:pointer;" onclick="schemaVal('.$idFic.'); afficherPopupAttente(); goto(\'Zip2HAL_schema_validate.php?idFic='.$idFic.'&nomfic='.$nomfic.'&idNomfic='.$idNomfic.'&idTEI='.$idTEI.'&typDoc='.$typDoc.'&datePub='.$datePub.'&portail='.$racine.'&login='.$HAL_USER.'&team='.$team.'&titreNot='.$titreNotS.'&partDep='.$partDep.'\');"><i class="mdi-progress-upload mdi mdi-24px text-primary"></i></a></span></center></td>';
	}
	
	//Importer dans HAL
	if($maj == "oui") {
		$idNomfic = str_replace(array(".xml", "./XML/"), "", $nomfic);
		$lienMAJ = "./Zip2HAL_Modif.php?action=MAJ&Id=".$idNomfic."&portail=".$racine."&partDep=".$partDep;
		//$lienMAJ = "https://ecobio.univ-rennes1.fr";//Pour test
		include "./Zip2HAL_actions.php";
		$titreNotS = str_replace("'", "\'", $titreNot);
		$titreNotS = str_replace('"', '\"', $titreNotS);
		//echo '<td><span id=\'importerHAL-'.$idFic.'\'><center><span id=\''.$idNomfic.'-'.$idFic.'\'><a target=\'_blank\' href=\''.$lienMAJ.'\' onclick="$.post(\'Zip2HAL_liste_actions.php\', { idNomfic : \''.$idNomfic.'\', action: \'statistiques\', valeur: \''.$idNomfic.'\', idTEI: \''.$idTEI.'\', typDoc: \''.$typDoc.'\', titreNot: \''.$titreNotS.'\', datePub: \''.$datePub.'\'}); majokVu(\''.$idNomfic.'-'.$idFic.'\');"><img alt=\'MAJ\' src=\'./img/MAJ.png\'></a></span></center></span></td>';
		echo '<td><span id=\'importerHAL-'.$idFic.'\'><center><span id=\''.$idNomfic.'-'.$idFic.'\'><a target=\'_blank\' href=\''.$lienMAJ.'\' onclick="$.post(\'Zip2HAL_liste_actions.php\', { idNomfic : \''.$idNomfic.'\', action: \'statistiques\', valeur: \''.$idNomfic.'\', idTEI: \''.$idTEI.'\', typDoc: \''.$typDoc.'\', titreNot: \''.$titreNotS.'\', datePub: \''.$datePub.'\', login: \''.$HAL_USER.'\', team: \''.$team.'\'});"><i class="mdi-progress-upload mdi mdi-24px"></i></a></span></center></span></td>';
	}else{
		echo '<td><span id=\'importerHAL-'.$idFic.'\'><center><i class="mdi-progress-upload mdi mdi-24px text-gray"></i></center></span></td>';
	}
}

echo '</tr>';
echo '</tbody>';
echo '</table>';
//Fin du tableau des résultats
?>

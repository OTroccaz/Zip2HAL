<?php
//Tableau des résultats
echo('<br><br>');

echo('<b>Tableau des résultats et éventuelle validation finale du TEI pour importation dans HAL</b> <i>(Si la langue doit être modifiée, ceci doit être réalisé en amont de toute autre modification)</i><br>');
//echo('Fichier '.$nomfic.'<br>');
echo('<table class=\'table table-striped table-bordered table-hover;\'>');
echo('<tr>');
echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>ID</b></td>');
echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Doublon</b></td>');
echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Supprimer</b></td>');
echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Type de document</b></td>');
echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Métadonnées</b></td>');
echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>DOI</b></td>');
echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Auteurs* / affiliations</b></td>');
echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Validation du TEI modifié</td>');
echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Importer dans HAL</b></td>');
echo('</tr>');

$cpt = 1;

echo('<tr style=\'text-align: center;\'>');

//Numérotation > id
echo('<td>'.$cpt.'</td>');

//Doublon ?
if(isset($typDbl) && $typDbl != "") {
	echo('<td><a target=\'_blank\' href=\'https://hal.archives-ouvertes.fr/'.$idTEI.'\'><img alt=\'HAL\' src=\'./img/HAL.jpg\'></a></td>');
}else{
	echo('<td>&nbsp;</td>');
}

//Supprimer le TEI
echo('<td><span id=\'suppression'.'-'.$idFic.'\'><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'suppression\', valeur : \''.$idFic.'\',}); majokSuppr(\'suppression'.'-'.$idFic.'\');"><img alt=\'Supprimer le TEI\' src=\'./img/supprimer.jpg\'></a></span>');

//Type de document
$typDoc = "";
$elts = $xml->getElementsByTagName("classCode");
foreach($elts as $elt) {
	if($elt->hasAttribute("scheme") && $elt->getAttribute("scheme") == "halTypology") {$typDoc = $elt->getAttribute("n");}
}
echo('<td>'.$typDoc.'</td>');

if(isset($typDbl) && $typDbl == "HALCOLLTYP") {//Doublon de type HAL et COLL > inutile d'afficher les métadonnées
	echo('<td>&nbsp;</td>');
}else{
	$tabMetaMQ[$nomfic] = array();//Tableau regroupant les métadonnées obligatoires manquantes
	//Métadonnées
	echo('<td style=\'text-align: left;\'><span id=\'metadonnees-'.$idFic.'\'>');
	
	//Métadonnées > Langue
	echo('<p class="form-inline">Langue* : <input id="language-'.$idFic.'" name="language-'.$idFic.'" value="'.$lang.'"class="autoLang form-control" style="height: 18px; width:150px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'language\', valeur: $(this).val(), language: $(this).val()}); afficacherLang($(this).val(), '.$idFic.');"></p>');
	
	if($lang == "") {$tabMetaMQ[$nomfic][] = "la langue";}
	
	//Domaine
	$domOK = "non";
	$elts = $xml->getElementsByTagName("classCode");
	foreach($elts as $elt) {
		if($domOK == "non") {
			echo('<p class="form-inline">Domaine : <input type="text" id="domaine-'.$idFic.'" name="domaine-'.$idFic.'" value="'.$domaine.'" class="form-control" style="height: 18px; width:400px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'domaine\', valeur: $(this).val()});"></p>');
			$domOK = "oui";
		}
	}
	echo('Pour modifier le domaine, utilisez le champ ou l\'arborescence dynamique ci-dessous :');
	echo('<span id="choixdom-'.$idFic.'">');
	echo('<br>');
	echo('<p class="form-inline"><input type="text" id ="inputdom-'.$idFic.'" name="inputdom-'.$idFic.'" class="autoDO form-control" style="margin-left: 30px; height: 18px; width:300px">');
	echo('&nbsp;<b>+</b>&nbsp;<a style="cursor:pointer;" onclick=\'document.getElementById("domaine-'.$idFic.'").value=$("#inputdom-'.$idFic.'").val(); $.post("Zip2HAL_liste_actions.php", {nomfic : "'.$nomfic.'", action: "domaine", valeur: $("#inputdom-'.$idFic.'").val()});\'><img width=\'12px\' alt=\'Valider le domaine\' src=\'./img/done.png\'></a></p>');

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
			if($cpt != 1) {echo('</span>');}
			$domF = str_replace("'", "’", $entry->fr_domain_s);
			echo('<span style=\'margin-left: 30px;\' id=\'cod-'.$cpt.'-'.$idFic.'\'><a style=\'cursor:pointer;\' onclick=\'afficacher('.$cpt.', '.$idFic.');\';><font style=\'color: #FE6D02;\'><b>>&nbsp;</b></font></a></span>');
			echo('<span><a style=\'cursor:pointer;\' onclick=\'document.getElementById("domaine-'.$idFic.'").value="'.$domF.' ~ '.$code.'"; $.post("Zip2HAL_liste_actions.php", {nomfic : "'.$nomfic.'", action: "domaine", valeur: "'.$domF.' ~ '.$code.'"});\'>'.$domF.'</a></span><br>');
			$codI = $codF;
			echo('<span id=\'dom-'.$cpt.'-'.$idFic.'\' style=\'display:none;\'>');
			$cpt++;
		}else{//Liste des différentes sous-matières de la discipline
			$sMat = str_replace($domF.'/', '', str_replace("'", "’", $entry->fr_domain_s));
			$sMatVal = str_replace("'", "’", $entry->fr_domain_s);
			//$sMatTab = explode("/", $entry->fr_domain_s);
			//$num = count($sMatTab) - 1;
			//$sMatVal = $sMatTab[$num];
			$code = $entry->code_s;
			echo('<span style=\'margin-left: 60px;\'><a style=\'cursor:pointer;\' onclick=\'document.getElementById("domaine-'.$idFic.'").value="'.$sMatVal.' ~ '.$code.'"; $.post("Zip2HAL_liste_actions.php", {nomfic : "'.$nomfic.'", action: "domaine", valeur: "'.$sMatVal.' ~ '.$code.'"});\'>'.$sMat.'</a></span><br>');
		}
	}
	echo('</span><br>');
	
	//Métadonnées > Titre
	$titreNot = "";
	$elts = $xml->getElementsByTagName("title");
	foreach($elts as $elt) {
		if($elt->hasAttribute("xml:lang") && ($elt->getAttribute("xml:lang") == $languages[$lang] || $elt->getAttribute("xml:lang") == "")) {
			$titreNot = $elt->nodeValue;
			$testMeta = "ok";
		}
	}
	echo('Titre* : <textarea id="titre-'.$idFic.'" name="titre-'.$idFic.'" class="textarea form-control" style="width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'titre\', valeur: $(this).val(), langue : \''.$lang.'\'});">'.$titreNot.'</textarea><br>');
	
	if($titreNot == "") {$tabMetaMQ[$nomfic][] = "le titre";}
	
	
	//Métadonnées > Titre traduit en anglais
	if($lang != "English") {$affT = "block";}else{$affT = "none";}
		
	$titreT = "";
	//Le titre traduit est-il déjà présent ?
	$elts = $xml->getElementsByTagName("title");
	foreach($elts as $elt) {
		if($elt->hasAttribute("xml:lang") && ($elt->getAttribute("xml:lang") == "en")) {$titreT = $elt->nodeValue;}
	}
	echo('<span id="lantitreT-'.$idFic.'" style="display:'.$affT.'">Titre traduit : <textarea id="titreT-'.$idFic.'" name="titreT-'.$idFic.'" class="textarea form-control" style="width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'titreT\', valeur: $(this).val(), langue : \'English\'});">'.$titreT.'</textarea><br></span>');
		
	
	//Métadonnées > Notice
	$target = "";
	$elts = $xml->getElementsByTagName("ref");
	foreach($elts as $elt) {
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "file") {
			if($elt->hasAttribute("target")) {$target = $elt->getAttribute("target");}
		}
	}
	if($target != "") {//N'afficher les métadonnées de la notice uniquement s'il y en a une
		echo('<p class="form-inline">Texte intégral : <input type="text" id="notice-'.$idFic.'" name="notice-'.$idFic.'" value="'.$target.'" class="form-control" style="height: 18px; width:350px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'notice\', valeur: $(this).val(), valeur2: $(\'#subtype\').val()});">');
		if($target != "") {echo(' - <a target="_blank" href="'.$target.'">Lien</a></p>');}
		//Subtype
		echo('<p class="form-inline">Type de dépôt : <select id="subtype-'.$idFic.'" name="subtype-'.$idFic.'" class="form-control" style="height: 18px; padding: 0px; width:150px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'notice\', valeur: $(\'#notice\').val(), valeur2: $(this).val()});">');
		if($elt->getAttribute("subtype") == "author") {$txt = "selected";}else{$txt = "";}
		echo('<option '.$txt.' value="author">author</option>');
		if($elt->getAttribute("subtype") == "greenPublisher") {$txt = "selected";}else{$txt = "";}
		echo('<option '.$txt.' value="greenPublisher">greenPublisher</option>');
		if($elt->getAttribute("subtype") == "publisherPaid") {$txt = "selected";}else{$txt = "";}
		echo('<option '.$txt.' value="publisherPaid">publisherPaid</option>');
		if($elt->getAttribute("subtype") == "noaction") {$txt = "selected";}else{$txt = "";}
		echo('<option '.$txt.' value="noaction">noaction</option>');
		echo('</select></p>');
		//Licence
		$licence = "";
		$elts = $xml->getElementsByTagName("licence");
		foreach($elts as $elt) {
			if($elt->hasAttribute("target")) {
				$licence = $elt->getAttribute("target");
			}
		}
		echo('<p class="form-inline">Licence : <input type="text-'.$idFic.'" id="licence" name="licence-'.$idFic.'" value="'.$licence.'" class="form-control" style="height: 18px; width:400px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'licence\', valeur: $(this).val()});">');
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
	echo('<p class="form-inline">Date de publication* : <input type="text" id="datePub-'.$idFic.'" name="datePub-'.$idFic.'" value="'.$datePub.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'datePub\', valeur: $(this).val()});"></p>');
	
	if($datePub == "") {$tabMetaMQ[$nomfic][] = "l\'année de publication";}
	
	//Métadonnées > Date d'édition
	$dateEpub = "";
	$elts = $xml->getElementsByTagName("date");
	foreach($elts as $elt) {
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "dateEpub") {
			$dateEpub = $elt->nodeValue;
		}
	}
	echo('<p class="form-inline">Date d\'édition : <input type="text" id="dateEpub-'.$idFic.'" name="dateEpub-'.$idFic.'" value="'.$dateEpub.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'dateEpub\', valeur: $(this).val()});"></p>');

	//Métadonnées > Revue
	if($typDoc == "ART") {
		$nomRevue = "";
		$elts = $xml->getElementsByTagName("title");
		foreach($elts as $elt) {
			if($elt->hasAttribute("level") && $elt->getAttribute("level") == "j") {
				$nomRevue = $elt->nodeValue;
				$testMeta = "ok";
			}
		}
		echo('<p class="form-inline">Nom de la revue* :<br> <input type="text" id="revue-'.$idFic.'" name="revue-'.$idFic.'" value="'.$nomRevue.'" class="form-control" style="height: 18px; width:500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'revue\', valeur: $(this).val()});"></p>');
		
		if($nomRevue == "") {$tabMetaMQ[$nomfic][] = "le titre de la revue";}
	}
	
	//Métadonnées > Audience, vulgarisation et comité de lecture
	$elts = $xml->getElementsByTagName("note");
	foreach($elts as $elt) {
		//Audience
		if (isset($testMetaA) && $testMetaA != "ok") {$testMetaA = "";}
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "audience") {
			echo('<p class="form-inline">Audience* : ');
			echo('<select id="audience-'.$idFic.'" name="audience" class="form-control" style="height: 18px; padding: 0px; width:150px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'audience\', valeur: $(this).val()});">>');
			$valAud = $elt->getAttribute("n");
			if($valAud == 1) {$txt = "selected"; $testMetaA = "ok";}else{$txt = "";}
			echo('<option '.$txt.' value="1">Internationale</option>');
			if($valAud == 2) {$txt = "selected"; $testMetaA = "ok";}else{$txt = "";}
			echo('<option '.$txt.' value="2">Nationale</option>');
			if($valAud == 3) {$txt = "selected";}else{$txt = "";}
			echo('<option '.$txt.' value="3">Non renseignée</option>');
			echo('</select></p>');
		}
		
		//Vulgarisation
		$txtVO = "";
		$txtVN = "";
		if (isset($testMetaV) && $testMetaV != "ok") {$testMetaV = "";}
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "popular") {
			if($elt->nodeValue == "Yes") {$txtVO = "checked"; $txtVN = ""; $testMetaV = "ok";}
			if($elt->nodeValue == "No") {$txtVO = ""; $txtVN = "checked"; $testMetaV = "ok";}
			echo('<p class="form-inline">Vulgarisation* : ');
			echo('<input type="radio" '.$txtVO.' id="popular-'.$idFic.'" name="popular-'.$idFic.'" value="Yes" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'vulgarisation\', valeur: $(this).val()});"> Oui');
			echo('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
			echo('<input type="radio" '.$txtVN.' id="popular-'.$idFic.'" name="popular-'.$idFic.'" value="No" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'vulgarisation\', valeur: $(this).val()});"> Non');
			echo('</p>');
		}
		
		//Comité de lecture
		$txtCO = "";
		$txtCN = "";
		if (isset($testMetaC) && $testMetaC != "ok") {$testMetaC = "";}
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "peer") {
			if($elt->nodeValue == "Yes") {$txtCO = "checked"; $txtCN = ""; $testMetaC = "ok";}
			if($elt->nodeValue == "No") {$txtCO = ""; $txtCN = "checked"; $testMetaC = "ok";}
			echo('<p class="form-inline">Comité de lecture* : ');
			echo('<input type="radio" '.$txtCO.' id="peer-'.$idFic.'" name="peer-'.$idFic.'" value="Yes" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'peer\', valeur: $(this).val()});"> Oui');
			echo('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
			echo('<input type="radio" '.$txtCN.' id="peer-'.$idFic.'" name="peer-'.$idFic.'" value="No" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'peer\', valeur: $(this).val()});"> Non');
			echo('</p>');
		}
	}

	if($testMetaA == "") {$tabMetaMQ[$nomfic][] = "l\'audience";}
	if($testMetaV == "") {$tabMetaMQ[$nomfic][] = "la vulgarisation";}
	if($testMetaC == "") {$tabMetaMQ[$nomfic][] = "le comité de lecture";}
	
	//Métadonnées > Editeur
	$editeur = "non";
	$elts = $xml->getElementsByTagName("publisher");
	foreach($elts as $elt) {
		echo('<p class="form-inline">Editeur : <input type="text" id="publisher-'.$idFic.'" name="publisher-'.$idFic.'" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'editeur\', valeur: $(this).val()});"></p>');
		$editeur = "oui";
	}
	if ($editeur == "non") {
		echo('<p class="form-inline">Editeur : <input type="text" id="publisher-'.$idFic.'" name="publisher-'.$idFic.'" value="" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'editeur\', valeur: $(this).val()});"></p>');
	}
	
	//Métadonnées > ISSN et EISSN
	$issn = "";
	$eissn = "";
	$elts = $xml->getElementsByTagName("idno");
	foreach($elts as $elt) {
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "issn") {$issn = $elt->nodeValue;}
		if($elt->hasAttribute("type") && $elt->getAttribute("type") == "eissn") {$eissn = $elt->nodeValue;}
	}
	echo('<p class="form-inline">ISSN : <input type="text" id="issn-'.$idFic.'" name="issn-'.$idFic.'" value="'.$issn.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'issn\', valeur: $(this).val()});"></p>');
	echo('<p class="form-inline">EISSN : <input type="text" id="eissn-'.$idFic.'" name="eissn-'.$idFic.'" value="'.$eissn.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'eissn\', valeur: $(this).val()});"></p>');
	
	//Métadonnées spécifiques aux COMM et POSTER
	if($typDoc == "COMM" || $typDoc == "POSTER") {
		
		//Métadonnées > Nom de la revue, ville, dates, titre, pays, ISBN, actes, éditeur scientifique et conférence invitée
		$titreV = "";
		$settlement = "";
		$startDate = "";
		$endDate = "";
		$titreConf = "";
		$paysConf = "";
		$isbnConf = "";
		$procConf = "";
		$editConf = "";
		$inviConf = "";
		
		//Métadonnées > Titre du volume
		$elts = $xml->getElementsByTagName("title");
		foreach($elts as $elt) {
			foreach($elts as $elt) {
				if($elt->hasAttribute("level") && ($elt->getAttribute("level") == "j")) {
					$titreV = $elt->nodeValue;
					//Déplacement du noeud depuis <title> vers <imprint>
					deleteNode($xml, "monogr", "title", 0, "level", "j", "", "", "exact");
					$xml->save($nomfic);
					insertNode($xml, $elt->nodeValue, "imprint", "date", 0, "biblScope", "unit", "serie", "", "", "iB", "tagName", "");
					$xml->save($nomfic);
				}
			}
		}
		echo('Titre du volume : <textarea id="titreV-'.$idFic.'" name="titreV-'.$idFic.'" class="textarea form-control" style="width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'titreV\', valeur: $(this).val(), langue : \''.$lang.'\'});">'.$titreV.'</textarea><br>');
		
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
					if($item->nodeName == "title") {$titreConf = $item->nodeValue;}
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
		
		if($settlement == "") {$tabMetaMQ[$nomfic][] = "la ville de la conférence";}
		if($paysConf == "") {$tabMetaMQ[$nomfic][] = "le pays de la conférence";}
		if($startDate == "") {$tabMetaMQ[$nomfic][] = "la date de début de la conférence";}
		
		$txtPO = "";
		$txtPN = "";
		$elts = $xml->getElementsByTagName("idno");
		foreach($elts as $elt) {
			//ISBN
			if($elt->hasAttribute("type") && $elt->getAttribute("type") == "isbn") {$isbnConf = $elt->nodeValue;}
			//Proceedings
			if($elt->hasAttribute("type") && $elt->getAttribute("type") == "proceedings") {
				if($elt->nodeValue == "Yes") {$txtPO = "checked"; $txtPN = "";}
				if($elt->nodeValue == "No") {$txtPO = ""; $txtPN = "checked";}
			}
		}
		$elts = $xml->getElementsByTagName("monogr");
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
			if($elt->hasAttribute("type") && $elt->getAttribute("type") == "invited") {
				if($elt->nodeValue == 0) {$inviConf = "No";}
				if($elt->nodeValue == 1) {$inviConf = "Yes";}
			}
		}
		
		if($inviConf == "") {$tabMetaMQ[$nomfic][] = "le caractère conférence invitée (O/N)";}
		
		//Ville de la conférence
		echo('<p class="form-inline">Ville* : <input type="text" id="settlement-'.$idFic.'" name="settlement-'.$idFic.'" value="'.$settlement.'" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ville\', valeur: $(this).val()});"></p>');
		//Date de début de conférence
		echo('<p class="form-inline">Date de début de conférence* : <input type="text" id="startDate-'.$idFic.'" name="startDate-'.$idFic.'" value="'.$startDate.'" class="form-control" style="height: 18px; width:140px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'startDate\', valeur: $(this).val()});"></p>');
		//Date de fin de conférence
		echo('<p class="form-inline">Date de fin de conférence : <input type="text" id="endDate-'.$idFic.'" name="endDate-'.$idFic.'" value="'.$endDate.'" class="form-control" style="height: 18px; width:140px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'endDate\', valeur: $(this).val()});"></p>');
		//Titre de la conférence
		echo('Titre de la conférence* : <textarea id="titreConf-'.$idFic.'" name="titreConf-'.$idFic.'" class="textarea form-control" style="width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'titreConf\', valeur: $(this).val()});">'.$titreConf.'</textarea><br>');
		//Pays de la conférence
		echo('<p class="form-inline">Pays* : <input type="text" id="paysConf-'.$idFic.'" name="paysConf-'.$idFic.'" value="'.$affPays.'" class="autoPays form-control" style="height: 18px; width:200px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'paysConf\', valeur: $(this).val()});"></p>');
		//ISBN de la conférence
		echo('<p class="form-inline">ISBN : <input type="text" id="isbnConf-'.$idFic.'" name="isbnConf-'.$idFic.'" value="'.$isbnConf.'" class="form-control" style="height: 18px; width:200px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'isbnConf\', valeur: $(this).val()});"></p>');
		//Proceedings de la conférence
		echo('<p class="form-inline">Proceedings : ');
		echo('<input type="radio" '.$txtPO.' id="procConf-'.$idFic.'" name="procConf-'.$idFic.'" value="Yes" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'procConf\', valeur: $(this).val()});"> Oui');
		echo('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		echo('<input type="radio" '.$txtPN.' id="procConf-'.$idFic.'" name="procConf-'.$idFic.'" value="No" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'procConf\', valeur: $(this).val()});"> Non');
		echo('</p>');
		//Editeur scientifique
		echo('<p class="form-inline">Editeur scientifique : <input type="text" id="scientificEditor-'.$idFic.'" name="scientificEditor-'.$idFic.'" value="'.$editConf.'" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'scientificEditor\', valeur: $(this).val()});"></p>');
		//Conférence invitée O/N
		$txtCO = "";
		$txtCN = "";
		if($inviConf == "Yes") {$txtCO = "checked"; $txtCN = "";}
		if($inviConf == "No") {$txtCO = ""; $txtCN = "checked";}
			echo('<p class="form-inline">Conférence invitée* : ');
			echo('<input type="radio" '.$txtCO.' id="invitConf-'.$idFic.'" name="invitConf-'.$idFic.'" value="Yes" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'invitConf\', valeur: $(this).val()});"> Oui');
			echo('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
			echo('<input type="radio" '.$txtCN.' id="invitConf-'.$idFic.'" name="invitConf-'.$idFic.'" value="No" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'invitConf\', valeur: $(this).val()});"> Non');
			echo('</p>');
	}
	//Fin métadonnées spécifiques aux COMM et POSTER
	
	
	//Métadonnées spécifiques aux COUV
	if($typDoc == "COUV") {
		
		//Métadonnées > Titre de l'ouvrage, éditeur(s) scientifique(s) et ISBN
		$titrOuv = "";
		$editOuv = array();
		$isbnOuv = "";
		
		$elts = $xml->getElementsByTagName("monogr");
		foreach($elts as $elt) {
			if($elt->hasChildNodes()) {
				foreach($elt->childNodes as $item) {
					//Titre de l'ouvrage
					if($item->nodeName == "title" && $item->hasAttribute("level") && $item->getAttribute("level") == "m") {$titrOuv = $item->nodeValue;}
					//Editeur scientifique
					if($item->nodeName == "editor") {$editOuv[] = $item->nodeValue;}
					//ISBN
					if($item->nodeName == "idno" && $item->hasAttribute("type") && $item->getAttribute("type") == "isbn") {$isbnOuv = $item->nodeValue;}
				}
			}
		}
		
		if($titrOuv == "") {$tabMetaMQ[$nomfic][] = "le tite de l\'ouvrage";}
		
		//Titre de l'ouvrage
		echo('<p class="form-inline">Titre de l\'ouvrage* : <input type="text" id="titrOuv-'.$idFic.'" name="titrOuv-'.$idFic.'" value="'.$titrOuv.'" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'titrOuv\', valeur: $(this).val()});"></p>');
		//Editeur(s) scientifique(s)
		$ed = 0;
		foreach($editOuv as $edit) {
			echo('<p class="form-inline">Editeur scientifique : <input type="text" id="editOuv-'.$idFic.'-'.$ed.'" name="editOuv-'.$idFic.'-'.$ed.'" value="'.$edit.'" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'editOuv\', pos: '.$ed.', valeur: $(this).val()});"></p>');
			$ed++;
		}
		//ISBN
		echo('<p class="form-inline">ISBN : <input type="text" id="isbnOuv-'.$idFic.'" name="isbnOuv-'.$idFic.'" value="'.$isbnOuv.'" class="form-control" style="height: 18px; width:200px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'isbnOuv\', valeur: $(this).val()});"></p>');					
	}
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
	echo('<p class="form-inline">Volume : <input type="text" id="volume-'.$idFic.'" name="volume-'.$idFic.'" value="'.$volume.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'volume\', valeur: $(this).val()});"></p>');
	echo('<p class="form-inline">Numéro : <input type="text" id="issue-'.$idFic.'" name="issue-'.$idFic.'" value="'.$numero.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'issue\', valeur: $(this).val()});"></p>');
	echo('<p class="form-inline">Pages : <input type="text" id="pp-'.$idFic.'" name="pp-'.$idFic.'" value="'.$pages.'" class="form-control" style="height: 18px; width:150px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'pages\', valeur: $(this).val()});";"></p>');
	
	//Métadonnées > Financement
	$funder = "non";
	$elts = $xml->getElementsByTagName("funder");
	foreach($elts as $elt) {
		echo('Financement : <textarea id="funder-'.$idFic.'" name="funder-'.$idFic.'" class="textarea form-control" style="width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'financement\', valeur: $(this).val()});">'.$elt->nodeValue.'</textarea><br>');
		$funder = "oui";
	}
	if ($funder == "non") {
		echo('Financement : <textarea id="funder-'.$idFic.'" name="funder-'.$idFic.'" class="textarea form-control" style="width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'financement\', valeur: $(this).val()});"></textarea><br>');
	}
	
	//Métadonnées > Financement ANR
	echo('Indiquez le ou les projets ANR liés à ce travail :<br>');
	for ($iANR=1; $iANR < 4; $iANR++) {
		echo('<input type="text" id="ANR'.$iANR.'-'.$idFic.'" name="ANR'.$iANR.'-'.$idFic.'" class="autoANR form-control" style="height: 18px; width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ANR\', valeur: $(this).val()});">');
	}
	echo('<br>');
	
	//Métadonnées > Financement EUR
	echo('Indiquez le ou les projets EU liés à ce travail :<br>');
	for ($iEUR=1; $iEUR < 4; $iEUR++) {
		echo('<input type="text" id="EUR'.$iEUR.'-'.$idFic.'" name="EUR'.$iEUR.'-'.$idFic.'" class="autoEUR form-control" style="height: 18px; width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'EUR\', valeur: $(this).val()});">');
	}
	echo('<br>');
	
	//Métadonnées > Mots-clés
	echo('Mots-clés :');
	$keys = $xml->getElementsByTagName("keywords");
	$ind = 0;
	foreach($keys as $key) {
		foreach($key->childNodes as $elt) {
			if($elt->hasAttribute("xml:lang") && ($elt->getAttribute("xml:lang") == $languages[$lang] || $elt->getAttribute("xml:lang") == "")) {
				echo('<input type="text" id="mots-cles'.$ind.'-'.$idFic.'" name="mots-cles'.$ind.'-'.$idFic.'" value="'.str_replace("'", "\'", $elt->nodeValue).'" class="form-control" style="height: 18px; width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'mots-cles\', pos: '.$ind.', valeur: $(this).val(), langue: \''.$lang.'\'});">');
			}
			$ind++;
		}
	}
	$nbMC = $ind - 1;
	
	//Métadonnées > Mots-clés traduits en anglais
	if($lang != "English") {$affMC = "block";}else{$affMC = "none";}
	
	echo('<span id="lanMCT-'.$idFic.'" style="display:'.$affMC.'"><br>Mots-clés traduits :');
	$tabMC = array();
	$keys = $xml->getElementsByTagName("keywords");
	foreach($keys as $key) {
		foreach($key->childNodes as $elt) {
			if($elt->hasAttribute("xml:lang") && ($elt->getAttribute("xml:lang") == "en")) {$tabMC = $elt->nodeValue;}
		}
	}
	for($mc = 0; $mc <= $nbMC; $mc++) {
		if(isset($tabMC[$mc])) {$mcT = $tabMC[$mc];}else{$mcT = "";}
		echo('<input type="text" id="mots-clesT'.$ind.'-'.$idFic.'" name="mots-clesT'.$ind.'-'.$idFic.'" value="'.str_replace("'", "\'", $mcT).'" class="form-control" style="height: 18px; width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'mots-clesT\', pos: '.$ind.', valeur: $(this).val(), langue: \'English\'});">');
		$ind++;						
	}
	echo('</span>');
	
	/*
	//Ajouter des mots-clés
	echo('<br>');
	echo('Ajouter des mots-clés :');
	for($dni = $ind; $dni < $ind + 5; $dni++) {
		echo('<input type="text" id="mots-cles'.$dni.'-'.$idFic.'" name="mots-cles'.$dni.'-'.$idFic.'" value="" class="form-control" style="height: 18px; width: 280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajout-mots-cles\', pos: '.$dni.', valeur: $(this).val(), langue: $(\'#language-'.$idFic.'\').val()});">');
	}
	*/
	echo('<br>');
			
	//Métadonnées > Résumé
	$resume = "";
	$elts = $xml->getElementsByTagName("abstract");
	foreach($elts as $elt) {
		if($elt->hasAttribute("xml:lang")) {$resume = $elt->nodeValue;}
	}
	echo('Résumé : <textarea id="abstract-'.$idFic.'" name="abstract-'.$idFic.'" class="textarea form-control" style="width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'abstract\', valeur: $(this).val(), langue: \''.$lang.'\'});">'.$resume.'</textarea><br>');
	
	//Métadonnées > Résumé traduit en anglais
	if($lang != "English") {$affR = "block";}else{$affR = "none";}
	$resumeT = "";
	//Le résumé traduit est-il déjà présent ?
	$elts = $xml->getElementsByTagName("abstract");
	foreach($elts as $elt) {
		if($elt->hasAttribute("xml:lang") && ($elt->getAttribute("xml:lang") == "en")) {$resumeT = $elt->nodeValue;}
	}
	echo('<span id="lanresumeT-'.$idFic.'" style="display:'.$affR.'">Résumé traduit: <textarea id="abstractT-'.$idFic.'" name="abstractT-'.$idFic.'" class="textarea form-control" style="width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'abstractT\', valeur: $(this).val(), langue: \'English\'});">'.$resumeT.'</textarea><br></span>');
	
	echo('</span></td>');
	//Fin des métadonnées
}

//DOI
if(isset($doiTEI)) {echo('<td><a target=\'_blank\' href=\'https://doi.org/'.$doiTEI.'\'><img alt=\'DOI\' src=\'./img/doi.jpg\'></a>');}else{echo('<td>&nbsp;</td>');}

if(isset($typDbl) && $typDbl == "HALCOLLTYP") {//Doublon de type HAL et COLL > inutile d'afficher les affiliations, la validation du TEI et la possibilité d'import dans HAL
	echo('<td>&nbsp;</td>');
	echo('<td>&nbsp;</td>');
	echo('<td>&nbsp;</td>');
}else{
	if(empty($halAut)) {$tabMetaMQ[$nomfic][] = "les auteurs";}
	//Auteurs / affiliations
	echo('<td style=\'text-align: left;\'><span id=\'affiliations-'.$idFic.'\'>');
	//$i = compteur auteur / $j = compteur affiliation
	for($i = 0; $i < count($halAut); $i++) {
		echo('<span id="PN-aut'.$i.'-'.$idFic.'"><b>'.$halAut[$i]['firstName'].' '.$halAut[$i]['lastName'].'</b></span>');
		
		//Possibilité de supprimer l'auteur
		echo('&nbsp;<span id="Vu-aut'.$i.'-'.$idFic.'"><a style="cursor:pointer;" onclick="event.preventDefault(); afficherPopupConfirmation(\'Êtes-vous sûr de vouloir supprimer cet auteur ?\', \''.$nomfic.'\', '.$i.', \''.$halAut[$i]['firstName'].' ~ '.$halAut[$i]['lastName'].'\', \'aut'.$i.'-'.$idFic.'\');"><img width=\'12px\' alt=\'Suppression auteur\' src=\'./img/supprimer.jpg\'></a></span>');
		
		//Début span suppression auteur
		echo('&nbsp;<span id="Sup-aut'.$i.'-'.$idFic.'">');
		
		if($halAut[$i]['mailDom'] != "") {echo(' (@'.$halAut[$i]['mailDom'].')');}
		echo('<br>');
		if($halAut[$i]['xmlIds'] != "") {
			echo('<span id="Txt'.$halAut[$i]['xmlIds'].'-'.$idFic.'">Supprimer l\'idHAL '.$halAut[$i]['xmlIds'].'</span> <span id="Vu'.$halAut[$i]['xmlIds'].'-'.$idFic.'"><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'supprimerIdHAL\', pos: '.$i.', valeur: \''.$halAut[$i]['xmlIds'].'\'}); majokIdHAL(\''.$halAut[$i]['xmlIds'].'-'.$idFic.'\');"><img width=\'12px\' alt=\'Supprimer l\'idHAL\' src=\'./img/supprimer.jpg\'></a></span><br>');
		}
		//Si pas d'idHAL et si id auteur existe, afficher l'id
		if($halAut[$i]['idHals'] == "" && $halAut[$i]['docid'] != "") {
			echo('id '.$halAut[$i]['docid'].'<br>');
		}
		if($halAut[$i]['idHals'] != "") {
			//echo('Remonter le bon auteur du référentiel auteurs <a class=info><img src=\'./img/pdi.jpg\'><span>L\'idHAL n\'est pas ajouté automatiquement car c\'est juste une suggestion que vous devrez valider en l\'ajoutant dans le champ ci-dessous prévu à cet effet.</span></a> :<br><input type="text" id="ajoutidHAL'.$i.'" value="'.$halAutinit[$i]['idHals'].'" name="ajoutidHAL'.$i.'" class="form-control" style="height: 18px; width:200px;">');
			$idHAL = $halAut[$i]['idHals'].' ('.$halAut[$i]['idHali'].')';
		}else{
			$idHAL = "";
		}
		
		echo('Ajouter un idHAL : <span class="form-inline"><input type="text" id="ajoutIdh'.$i.'-'.$idFic.'" name="ajoutIdh'.$i.'-'.$idFic.'" value="'.$idHAL.'" class="autoID form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterIdHAL\', pos: '.$i.', valeur: $(this).val()});">');
		echo('&nbsp;<span id="Vu'.$halAut[$i]['idHals'].'-'.$idFic.'"><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'supprimerIdHAL\', pos: '.$i.', valeur: \'\'}); majokIdHALSuppr(\'ajoutIdh'.$i.'-'.$idFic.'\');"><img width=\'12px\' alt=\'Supprimer l\'idHAL\' src=\'./img/supprimer.jpg\'></a></span></span><br>');
		echo('<a target="_blank" href="https://aurehal.archives-ouvertes.fr/author/browse?critere='.$halAut[$i]['firstName'].'+'.$halAut[$i]['lastName'].'">Consulter le référentiel auteur</a><br>');
		
		//Lors de l'étape 2 (2ème méthode), d'autres idHAL ont-ils été trouvés via la requête ?
		for($id = 0; $id < count($tabIdHAL); $id++) {
			if(isset($tabIdHAL[$id]['firstName']) && $tabIdHAL[$id]['firstName'] == $halAut[$i]['firstName'] && isset($tabIdHAL[$id]['lastName']) && $tabIdHAL[$id]['lastName'] == $halAut[$i]['lastName']) {
				$reqAut = $tabIdHAL[$id]['reqAut'];
				echo('<a target="_blank" href="'.$reqAut.'"><font color=\'red\'>D\'autres idHAL ont été trouvés</font></a><br>');
			}
		}
		
		//Début bloc affiliations
		echo '<span><a style="cursor:pointer;" onclick="afficacherAff('.$i.','.$idFic.')";>Affiliations</a><br>';
		echo '<span id="Raff-'.$i.'-'.$idFic.'" style="display: none;">';
		
		//Affiliations remontées par OverHAL
		echo('<i><font style=\'color: #999999;\'>Affiliation(s) remontée(s) par OverHAL:<br>');
		for($j = 0; $j < count($nomAff); $j++) {
			if($halAutinit[$i]['affilName'] != "" && stripos($halAutinit[$i]['affilName'], $nomAff[$j]['lsAff']) !== false) {
				echo('<span id="aut'.$i.'-nomAff'.$j.'">'.$nomAff[$j]['org']);
				//echo('&nbsp;<img width=\'12px\' alt=\'Supprimer l\'affiliation\' src=\'./img/supprimer.jpg\'></span><br>');
				echo('</span><br>');
			}
		}
		echo('</font></i>');
		$ajtAff = "~";//Pour éviter d'afficher 2 fois des affiliations > méthode 1 / méthode 2 > avec ou sans prénom/nom
		$ajtAffDD = "~";//Drag and drop > Pour éviter de prendre en compte 2 fois des affiliations > méthode 1 / méthode 2 > avec ou sans prénom/nom
		for($j = 0; $j < count($halAff); $j++) {
			if($halAut[$i]['affilName'] != "" && stripos($halAut[$i]['affilName'], $halAff[$j]['lsAff']) !== false && strpos($ajtAff, $halAff[$j]['names']) === false && (($halAut[$i]['firstName'] == $halAff[$j]['fname'] && $halAut[$i]['lastName'] == $halAff[$j]['lname']) || ($halAff[$j]['fname'] == "" && $halAff[$j]['lname'] == ""))) {
				if($halAff[$j]['valid'] == "VALID") {$txtcolor = '#339966';}
				if($halAff[$j]['valid'] == "OLD") {$txtcolor = '#ff6600';}
				$ajtAff .= $halAff[$j]['names']."~";
				echo('<span id="aut'.$i.'-halAff'.$j.'-'.$idFic.'" draggable="true"><font style=\'color: '.$txtcolor.';\'>'.$halAff[$j]['ncplt'].'</font></span>');
				echo('&nbsp;<span id="Vu-aut'.$i.'-halAff'.$j.'-'.$idFic.'"><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'supprimerAffil\', pos: '.$i.', valeur: \''.$halAff[$j]['docid'].'\'}); majokAffil(\'aut'.$i.'-halAff'.$j.'-'.$idFic.'\', \''.str_replace("'", "\'", $halAff[$j]['ncplt']).'\');"><img width=\'12px\' alt=\'Supprimer l\'affiliation\' src=\'./img/supprimer.jpg\'></a></span><br>');						
			}
		}
		
		//Drag and drop
		for($j = 0; $j < count($halAff); $j++) {
			if($halAut[$i]['affilName'] != "" && stripos($halAut[$i]['affilName'], $halAff[$j]['lsAff']) !== false && strpos($ajtAffDD, $halAff[$j]['names']) === false && (($halAut[$i]['firstName'] == $halAff[$j]['fname'] && $halAut[$i]['lastName'] == $halAff[$j]['lname']) || ($halAff[$j]['fname'] == "" && $halAff[$j]['lname'] == ""))) {
				$ajtAffDD .= $halAff[$j]['names']."~";
				echo('<script type="text/javascript">');
				echo('	document.querySelector(\'[id="aut'.$i.'-halAff'.$j.'-'.$idFic.'"]\').addEventListener(\'dragstart\', function(e){');
				echo('			e.dataTransfer.setData(\'text\', e.target.innerText);');
				echo('	});');
				echo('</script>');
			}
		}
		
		echo('Ajouter des affiliations : <br>');
		
		for($dni = $j; $dni < $j + 5; $dni++) {						
			echo('<span class="form-inline"><input type="text" draggable="true" id="aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'" name="aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'" value="" class="autoAF form-control" style="height: 18px; width: 280px;" onclick="this.setSelectionRange(0, this.value.length);" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterAffil\', pos: '.$i.', valeur: $(this).val()});">');
			echo('&nbsp;<span id="Vu-aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'"><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'supprimerAffil\', pos: '.$i.', valeur: $(\'#aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'\').val().split(\'~\')[0].trim()}); majokAffilAjout(\'aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'\');"><img width=\'12px\' alt=\'Supprimer l\'affiliation\' src=\'./img/supprimer.jpg\'></a></span></span><br>');
			
			//Drag and drop
			echo('<script type="text/javascript">');
			echo('	document.querySelector(\'[id="aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'"]\').addEventListener(\'dragstart\', function(e){');
			echo('			e.dataTransfer.setData(\'text\', e.target.value);');
			echo('	});');
			echo('	var input = document.getElementById("aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'");');
			echo('	input.addEventListener(\'drop\', function (event) {');
			echo('		event.preventDefault();');
			echo('		var textData = event.dataTransfer.getData(\'text\');'); // Récupérer ce qui est déplacé
			echo('		event.target.value = textData;'); // Changer le contenu avec ce qui est déplacé
			echo('		$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterAffil\', pos: '.$i.', valeur: textData});');
			echo('	});');
			echo('</script>');
		}
		//echo('<input type="text" id="ajoutAff'.$i.'" name="ajoutAff'.$i.'" class="autoAF form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterAffil\', pos: '.$i.', valeur: $(this).val()});">');
		echo('</font>');
		
		echo('</span>');//Fin span suppression auteur
		
		echo '</span></span><br>';//Fin bloc affiliations
	}
	
	echo('<br>');
	echo('<b>Ajouter un auteur <i>(Prénom Nom)</i> : </b><input type="text" id="ajoutAuteur-'.$idFic.'" name="ajoutAuteur" class="form-control" style="height: 18px; width:280px;" onfocusout="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterAuteur\', pos: '.$i.', valeur: $(this).val()});">');
	echo('</span></td>');
	
	//Vérification si des métadonnées sont manquantes
	//var_dump($tabMetaMQ);
	$maj = "non";
	$message = "";
	if(empty($tabMetaMQ[$nomfic])) {
		$maj = "oui";
	}
	
	//Validation du TEI
	if($maj == "oui") {
		echo('<td><span id=\'validerTEI-'.$idFic.'\'>');
		echo('<div id=\'cpt4-'.$idFic.'\'>Validation en cours ...</div>');
		echo('<script>afficherPopupAttente();</script>');
		ob_flush();
		flush();
		ob_flush();
		flush(); 
		$maj = "non";
		$tst = new DOMDocument();
		$tst->load($nomfic);
		if(!$tst->schemaValidate('./aofr.xsd')) {
			echo('<script>');
			echo('document.getElementById(\'cpt4-'.$idFic.'\').style.display = \'none\';');
			echo('effacerPopup();');
			echo('</script>');
			echo('<a target=\'_blank\' href=\'https://www.freeformatter.com/xml-validator-xsd.html#\'><img alt=\'TEI non valide AOFR\' src=\'./img/supprimer.jpg\'></a><br>');
			echo('<a target=\'_blank\' href=\''.$nomfic.'\'>Lien TEI</a><br>');
			print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
			libxml_display_errors();
		}else{
			echo('<script>');
			echo('document.getElementById(\'cpt4-'.$idFic.'\').style.display = \'none\';');
			echo('effacerPopup();');
			echo('</script>');
			echo('<a target=\'_blank\' href=\'https://www.freeformatter.com/xml-validator-xsd.html#\'><img alt=\'TEI validé AOFR\' src=\'./img/done.png\'></a><br>');
			echo('<a target=\'_blank\' href=\''.$nomfic.'\'>Lien TEI</a><br>');			
			$maj = "oui";
		}
		echo('</span></td>');
	}else{
		$titreNotS = str_replace("'", "\'", $titreNot);
		$idNomfic = str_replace(array(".xml", "./XML/"), "", $nomfic);
		$lienMAJ = "./Zip2HALModif.php?action=MAJ&Id=".$idNomfic."&portail=".$racine;
		echo('<td><center><span id=\'validerTEI-'.$idFic.'\'>Après avoir complété les champs manquants, cliquez sur l\'icône ci-dessous afin de vérifier la validité du TEI pour pouvoir ensuite l\'importer dans HAL.<br><a style="cursor:pointer;" onclick="schemaVal('.$idFic.'); afficherPopupAttente(); goto(\'Zip2HAL_schema_validate.php?idFic='.$idFic.'&nomfic='.$nomfic.'&idNomfic='.$idNomfic.'&idTEI='.$idTEI.'&typDoc='.$typDoc.'&titreNot='.$titreNotS.'&datePub='.$datePub.'&portail='.$racine.'\');"><img alt=\'Vérifier la validité du TEI\' src=\'./img/question.jpg\'></a></span></center></td>');
	}
	
	//Importer dans HAL
	if($maj == "oui") {
		$idNomfic = str_replace(array(".xml", "./XML/"), "", $nomfic);
		$lienMAJ = "./Zip2HALModif.php?action=MAJ&Id=".$idNomfic."&portail=".$racine;
		//$lienMAJ = "https://ecobio.univ-rennes1.fr";//Pour test
		include "./Zip2HAL_actions.php";
		$titreNotS = str_replace("'", "\'", $titreNot);
		echo('<td><span id=\'importerHAL-'.$idFic.'\'><center><span id=\''.$idNomfic.'-'.$idFic.'\'><a target=\'_blank\' href=\''.$lienMAJ.'\' onclick="$.post(\'Zip2HAL_liste_actions.php\', { idNomfic : \''.$idNomfic.'\', action: \'statistiques\', valeur: \''.$idNomfic.'\', idTEI: \''.$idTEI.'\', typDoc: \''.$typDoc.'\', titreNot: \''.$titreNotS.'\', datePub: \''.$datePub.'\'}); majokVu(\''.$idNomfic.'-'.$idFic.'\');"><img alt=\'MAJ\' src=\'./img/MAJ.png\'></a></span></center></span></td>');
	}else{
		echo('<td><span id=\'importerHAL-'.$idFic.'\'><center><img alt=\'MAJ\' src=\'./img/MAJImpossible.png\'></center></span></td>');
	}
}

echo('</tr>');
echo('<table>');
//Fin du tableau des résultats

?>
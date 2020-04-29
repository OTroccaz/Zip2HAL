<?php
function deleteNode($xml, $amont, $aval, $pos, $typAtt1, $valAtt1, $rech) {
	/*
	$aval = noeud à supprimer
	$rech = 'approx' ou 'exact' > méthode de recherche pour l'attribut
	*/
	$cpt = 0;//Boucle pour retrouver $pos
	$elts = $xml->getElementsByTagName($amont);		
	foreach ($elts as $elt) {
		if ($cpt != $pos) {
		}else{
			if ($elt->hasChildNodes()) {
				foreach($elt->childNodes as $item) {
					//echo('<script>console.log("'.$amont.' : '.$valAtt1.'");</script>');
					if ($item->nodeName == $aval) {
						if ($typAtt1 != "") {
							if ($item->hasAttribute($typAtt1)) {$att1 = $item->getAttribute($typAtt1);}
							if ($rech == "approx") {
								if (strpos($valAtt1, $att1) !== false) {
									$elt->removeChild($item);
									break 2;
								}
							}else{
								if ($valAtt1 == $att1) {
									$elt->removeChild($item);
									break 2;
								}
							}
						}else{
							$elt->removeChild($item);
							break 2;
						}
					}
				}
			}
		}
		$cpt++;
	}
}

function insertNode($xml, $dueon, $amont, $aval, $pos, $tagName, $typAtt1, $valAtt1, $typAtt2, $valAtt2, $methode, $crit, $comp) {
	/*
	$methode = iB (insertBefore) ou aC (appendChild)
	Attribuer à $dueon la chaîne 'nonodevalue' si aucune valeur n'est nécessaire au noeud
	$pos = si besoin de se positionner à un endroit précis dans une liste de noeuds
	$crit = critère déterminant s'il faut parcourir sur $tagName ou sur $amont
		-> si recherche sur tagName, c'est pour une mise à jour
		-> si recherche sur amont, c'est pour vérifier l'existence + ajout éventuel
	Si recherche amont et $comp != "" > le noeud existe et il faut remplacer la valeur de l'attribut
	*/
  $noeud = "";
  $dueon = htmlspecialchars($dueon);
	//echo('<script>console.log("'.$amont.' : '.$valAtt1.'");</script>');
  //si noeud présent
	$cpt = 0;//Boucle pour retrouver $pos
	if ($crit == "amont") {
		$elts = $xml->getElementsByTagName($amont);		
		foreach ($elts as $elt) {
			if ($cpt != $pos) {
			}else{
				if ($elt->hasChildNodes()) {
					foreach($elt->childNodes as $item) {
						if (get_class($item) != "DOMText") {
							if ($item->hasAttribute($typAtt1)) {$att1 = $item->getAttribute($typAtt1);}else{$att1 = "";}
							if ($item->hasAttribute($typAtt2)) {$att2 = $item->getAttribute($typAtt2);}else{$att2 = "";}
							if ($comp == "") {//L'appel à la fonction sert juste à vérifier l'existence du noeud
								if ($att1 == $valAtt1 && $att2 == $valAtt2) {//Noeud avec attributs déjà présent
									$noeud = "ok";
									break 2;
								}
							}else{//L'appel à la fonction sert à remplacer la valeur de l'attribut d'un noeud existant > Test uniquement sur le 1er attribut pour l'instant
								if ($item->hasAttribute($typAtt1) && strpos($comp, $att1) !== false) {
									$item->setAttribute($typAtt1, $valAtt1);
									$noeud = "ok";
									break 2;
								}
							}
						}else{//Pas de noeud enfant ?
							$bip = $xml->createElement($tagName);
							if ($typAtt1 != "" && $valAtt1 != "") {$bip->setAttribute($typAtt1, $valAtt1);}
							if ($valAtt2 != "") {$bip->setAttribute($typAtt2, $valAtt2);}
							if ($dueon != "nonodevalue") {$cTn = $xml->createTextNode($dueon);}
							if ($dueon != "nonodevalue") {$bip->appendChild($cTn);}
							$biblStr = $xml->getElementsByTagName($amont)->item(0);						
							$biblStr->appendChild($bip);
							break;
						}
					}
				}
			}
			$cpt++;
		}
	}else{
		$elts = $xml->getElementsByTagName($tagName);
		foreach ($elts as $elt) {
			if ($cpt != $pos) {
			}else{
				if ($elt->hasAttribute($typAtt1)) {
					$quoi = $elt->getAttribute($typAtt1);
					if ($amont != "langUsage" && $tagName != "abstract") {
						if ($quoi == $valAtt1) {
							if ($dueon != "nonodevalue") {$elt->nodeValue = $dueon;}
							if ($elt->hasAttribute("subtype")) {$elt->removeAttribute("subtype");}//suppression inPress
							if ($valAtt2 != "") {$elt->setAttribute($typAtt2, $valAtt2);}
							$noeud = "ok";
						}
					}else{
						if ($dueon != "nonodevalue") {$elt->nodeValue = $dueon;}
						$elt->setAttribute($typAtt1, $valAtt1);
						if ($elt->hasAttribute("subtype")) {$elt->removeAttribute("subtype");}//suppression inPress
						if ($valAtt2 != "") {$elt->setAttribute($typAtt2, $valAtt2);}
						$noeud = "ok";
					}
				}
			}
			$cpt++;
		}
	}
	//echo('<script>console.log("Noeud : '.$noeud.'");</script>');
  //si noeud absent > recherche du noeud amont pour insérer les nouvelles données au bon emplacement
  if ($noeud == "" && $dueon != "") {
		$cpt = 0;//Boucle pour retrouver $pos
    $bibl = $xml->getElementsByTagName($amont);
    foreach($bibl as $elt) {
			if ($cpt != $pos) {
			}else{
				if ($elt->hasChildNodes()) {
					foreach($elt->childNodes as $item) {
						$name = $item->nodeName;
						//Si pas de valeur $aval définie, insertion en item(0)
						if ($aval == "") {
							$bip = $xml->createElement($tagName);
							if ($typAtt1 != "" && $valAtt1 != "") {$bip->setAttribute($typAtt1, $valAtt1);}
							if ($valAtt2 != "") {$bip->setAttribute($typAtt2, $valAtt2);}
							if ($dueon != "nonodevalue") {$cTn = $xml->createTextNode($dueon);}
							if ($dueon != "nonodevalue") {$bip->appendChild($cTn);}
							$biblStr = $xml->getElementsByTagName($amont)->item(0);						
							$biblStr->appendChild($bip);
							break 2;
						}else{
							if ($name == $aval) {//insertion nvx noeuds
								$bip = $xml->createElement($tagName);
								if ($dueon != "nonodevalue") {$cTn = $xml->createTextNode($dueon);}
								if ($typAtt1 != "" && $valAtt1 != "") {$bip->setAttribute($typAtt1, $valAtt1);}
								if ($valAtt2 != "") {$bip->setAttribute($typAtt2, $valAtt2);}
								if ($dueon != "nonodevalue") {$bip->appendChild($cTn);}
								$biblStr = $xml->getElementsByTagName($amont)->item($pos);
								//echo('<script>console.log("'.var_dump($biblStr).'");</script>');
								if ($methode == "iB") {//insertBefore
									$biblStr->insertBefore($bip, $item);
								}else{
									$biblStr->appendChild($bip);
								}
								break 2;
							}
							//echo('<script>console.log("'.var_dump($item).'");</script>');
							/*
							if ($item->hasChildNodes()) {
								$childs = $item->childNodes;
								echo('<script>console.log("'.var_dump($childs).'");</script>');
								foreach($childs as $i) {
									$name = $i->parentNode->nodeName;
									echo('<script>console.log("'.$name.'");</script>');
									if ($name == $aval) {//insertion nvx noeuds
										$bip = $xml->createElement($tagName);
										if ($dueon != "nonodevalue") {$cTn = $xml->createTextNode($dueon);}
										if ($typAtt1 != "" && $valAtt1 != "") {$bip->setAttribute($typAtt1, $valAtt1);}
										if ($valAtt2 != "") {$bip->setAttribute($typAtt2, $valAtt2);}
										if ($dueon != "nonodevalue") {$bip->appendChild($cTn);}
										$biblStr = $xml->getElementsByTagName($amont)->item($pos);
										echo('<script>console.log("'.var_dump($biblStr).'");</script>');
										if ($methode == "iB") {//insertBefore
											$biblStr->insertBefore($bip, $i->parentNode);
										}else{
											$biblStr->appendChild($bip);
										}
										break 3;
									}
								}
							}
							*/
						}
					}
				}else{
					//Pas de noeud enfant, insertion directe
					$bip = $xml->createElement($tagName);
					if ($typAtt1 != "" && $valAtt1 != "") {$bip->setAttribute($typAtt1, $valAtt1);}
					if ($valAtt2 != "") {$bip->setAttribute($typAtt2, $valAtt2);}
					if ($dueon != "nonodevalue") {$cTn = $xml->createTextNode($dueon);}
					if ($dueon != "nonodevalue") {$bip->appendChild($cTn);}
					$biblStr = $xml->getElementsByTagName($amont)->item(0);						
					$biblStr->appendChild($bip);
					break;
				}
			}
			$cpt++;
    }
  }
}
?>
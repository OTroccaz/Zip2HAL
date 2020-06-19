<?php
//Etape 3b - Recherche la dernière affiliation associée aux auteurs sans affiliation
echo('<br><br>');
$cpt = 1;
$year = date('Y', time());

echo('<b>Etape 3b : recherche de la dernière affiliation associée avec HAL aux auteurs sans affiliation</b><br>');
echo('<div id=\'cpt3b\'></div>');
//Si un auteur n'a aucune affiliation > rechercher dans le référentiel authorstructure pour remonter la dernière affiliation HAL associée à cet auteur
//Combien d'auteur(s) concerné(s) ?
$nbAutnoaff = 0;
$cptNoaff = 0;//Compteur d'affiliations remontées par cette méthode
for($i = 0; $i < count($halAut); $i++) {
	if($halAut[$i]['affilName'] == "") {$nbAutnoaff++;}
}
	
for($i = 0; $i < count($halAut); $i++) {
	if($halAut[$i]['affilName'] == "") {
		progression($cpt, $nbAutnoaff, 'cpt3b', $iPro, 'auteur');
		$firstNameT = strtolower(wd_remove_accents($halAut[$i]['firstName']));
		$lastNameT = strtolower(wd_remove_accents($halAut[$i]['lastName']));
		
		$reqAut = "https://api.archives-ouvertes.fr/search/authorstructure/?firstName_t=".$firstNameT."&lastName_t=".$lastNameT."&producedDateY_i=".$year;
		$reqAut = str_replace(" ", "%20", $reqAut);
		echo('<a target="_blank" href="'.$reqAut.'">URL requête auteur structure HAL</a><br>');
		//echo $reqAut.'<br>';
		$contAut = file_get_contents($reqAut);
		$resAut = json_decode($contAut);
		$orgName = "";
		//var_dump($resAut);
		if(isset($resAut->response->result->org->orgName)) {//Un seul résultat
			if(is_array($resAut->response->result->org->orgName)) {
				$orgName = $resAut->response->result->org->orgName[0];
			}else{
				$orgName = $resAut->response->result->org->orgName;
			}
			$orgName = str_replace(array("[", "]", "&", "="), array("%5B", "%5D", "%26", "%3D"), $orgName);
			//Est-ce une affiliation 'longue' (avec beaucoup de virgules) ou 'courte' ?
			//if(substr_count($orgName, ',') > 2) {$loncou = "longue";}else{$loncou = "courte";}								
			$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=%22".$orgName."%22%20AND%20valid_s:(VALID%20OR%20OLD)&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s desc,docid asc";
			$reqAff = str_replace(" ", "%20", $reqAff);
			echo('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a target="_blank" href="'.$reqAff.'">URL requête test validité affiliation trouvée</a><br>');
			//echo $reqAff.'<br>';
			$contAff = file_get_contents($reqAff);
			$resAff = json_decode($contAff);
			$docid = "non";
			if(isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
			if($numFound != 0) {			
				foreach($resAff->response->docs as $affil) {
					if(($affil->valid_s == "VALID" || $affil->valid_s == "OLD") && $docid == "non") {
						$halAff[$iAff]['docid'] = $affil->docid;
						$cptNoaff++;
						$cptAff++;
						$halAff[$iAff]['lsAff'] = "#localStruct-Aff".$cptAff."~";
						$halAff[$iAff]['valid'] = $affil->valid_s;
						$halAff[$iAff]['names'] = $affil->name_s;
						if(isset($affil->acronym_s)) {$acronym = " [".$affil->acronym_s."], ";}else{$acronym = ", ";}
						if(isset($affil->country_s)) {$country = ", ".$affil->country_s;}else{$country = "";}
						$halAff[$iAff]['ncplt'] = $affil->docid." ~ ".$affil->name_s.$acronym.$affil->type_s.$country;
						$halAff[$iAff]['fname'] = $halAut[$i]['firstName'];
						$halAff[$iAff]['lname'] = $halAut[$i]['lastName'];
						$halAut[$i]['affilName'] .= "#localStruct-Aff".$cptAff."~";
						$iAff++;
						$docid = "oui";
						//Pour les affiliations courtes, on ne prend que le premier résultat remonté
						//if($loncou == "courte") {break 2;}
					}
				}
				
				//if($docid == "non") {//pas de docid trouvé avec VALID ou OLD > on teste avec INCOMING
				//	foreach($resAff->response->docs as $affil) {
				//		if($affil->valid_s == "INCOMING"  && $docid == "non") {
				//			$halAff[$iAff]['docid'] = $affil->docid;
				//			$cptNoaff++;
				//			$cptAff++;
				//			$halAff[$iAff]['lsAff'] = "localStruct-Aff".$cptAff;
				//			$halAut[$i]['affilName'] = "localStruct-Aff".$cptAff."~";
				//			$iAff++;
				//			$docid = "oui";
				//		}
				//	}
				//}
				
			}
		}else{//Plusieurs résultats > N'analyser que les 2 premiers
			$org = 0;
			while($org <= 2) {
				if(isset($resAut->response->result->org[$org]->orgName)) {
					if(is_array($resAut->response->result->org[$org]->orgName)) {
						$orgName = $resAut->response->result->org[$org]->orgName[0];
					}else{
						$orgName = $resAut->response->result->org[$org]->orgName;
					}
					$orgName = str_replace(array("[", "]", "&", "="), array("%5B", "%5D", "%26", "%3D"), $orgName);
					//Est-ce une affiliation 'longue' (avec beaucoup de virgules) ou 'courte' ?
					//if(substr_count($orgName, ',') > 2) {$loncou = "longue";}else{$loncou = "courte";}				
					$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=%22".$orgName."%22%20AND%20valid_s:(VALID%20OR%20OLD)&fl=docid,valid_s,name_s,type_s&sort=valid_s desc,docid asc";
					$reqAff = str_replace(" ", "%20", $reqAff);
					echo('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a target="_blank" href="'.$reqAff.'">URL requête test validité affiliation trouvée</a><br>');
					//echo $reqAff.'<br>';
					$contAff = file_get_contents($reqAff);
					$resAff = json_decode($contAff);
					$docid = "non";
					if(isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
					if($numFound != 0) {			
						foreach($resAff->response->docs as $affil) {
							if(($affil->valid_s == "VALID" || $affil->valid_s == "OLD") && $docid == "non") {
								$halAff[$iAff]['docid'] = $affil->docid;
								$cptNoaff++;
								$cptAff++;
								$halAff[$iAff]['lsAff'] = "#localStruct-Aff".$cptAff."~";
								$halAff[$iAff]['valid'] = $affil->valid_s;
								$halAff[$iAff]['names'] = $affil->name_s;
								if(isset($affil->acronym_s)) {$acronym = " [".$affil->acronym_s."], ";}else{$acronym = ", ";}
								if(isset($affil->country_s)) {$country = ", ".$affil->country_s;}else{$country = "";}
								$halAff[$iAff]['ncplt'] = $affil->docid." ~ ".$affil->name_s.$acronym.$affil->type_s.$country;
								$halAff[$iAff]['fname'] = $halAut[$i]['firstName'];
								$halAff[$iAff]['lname'] = $halAut[$i]['lastName'];
								$halAut[$i]['affilName'] .= "#localStruct-Aff".$cptAff."~";
								$iAff++;
								$docid = "oui";
								//Pour les affiliations courtes, on ne prend que le premier résultat remonté
								//if($loncou == "courte") {break 2;}
							}
						}
						
						//if($docid == "non") {//pas de docid trouvé avec VALID ou OLD > on teste avec INCOMING
						//	foreach($resAff->response->docs as $affil) {
						//		if($affil->valid_s == "INCOMING"  && $docid == "non") {
						//			$halAff[$iAff]['docid'] = $affil->docid;
						//			$cptNoaff++;
						//			$cptAff++;
						//			$halAff[$iAff]['lsAff'] = "localStruct-Aff".$cptAff;
						//			$halAut[$i]['affilName'] = "localStruct-Aff".$cptAff."~";
						//			$iAff++;
						//			$docid = "oui";
						//		}
						//	}
						//}
						
					}
				}
				$org++;
			}
		}
		$cpt++;
	}
}
 
echo($cptNoaff.' affiliation(s) manquante(s) trouvée(s)');

echo('<script>');
echo('document.getElementById(\'cpt3b\').style.display = \'none\';');
echo('</script>');
//Fin étape 3b
?>
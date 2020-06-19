<?php
//Etape 3c - Recherche id auteur grâce à l'affiliation éventuellement trouvée
echo('<br><br>');
$cpt = 1;
$cptId = 0;
$year = date('Y', time());

echo('<b>Etape 3c : recherche des docid auteur grâce aux affiliations éventuellement trouvées</b><br>');
echo('<div id=\'cpt3c\'></div>');

for($i = 0; $i < count($halAut); $i++) {
	progression($cpt, count($halAut), 'cpt3c', $iPro, 'auteur');
	if($halAut[$i]['docid'] == "") {//Pas d'id auteur
		for($j = 0; $j < count($halAff); $j++) {
			//if($halAff[$j]['fname'] == $halAut[$i]['firstName'] && $halAff[$j]['lname'] == $halAut[$i]['lastName']) {
			if(strpos($halAut[$i]['affilName'], $halAff[$j]['lsAff']) !== false) {
				$affil = $halAff[$j]['names'];
				$afill = str_replace("&", "%24", $affil);
				$reqId = "https://api.archives-ouvertes.fr/search/index/?q=authLastName_sci:%22".$halAut[$i]['lastName']."%22%20AND%20authFirstName_sci:%22".$halAut[$i]['firstName']."%22&fq=(structAcronym_sci:%22".$affil."%22%20OR%20structName_sci:%22".$affil."%22%20OR%20structCode_sci:%22".$affil."%22)&fl=authIdLastNameFirstName_fs&sort=abs(sub(producedDateY_i,".$year."))%20asc";
				$reqId = str_replace(" ", "%20", $reqId);
				echo('<a target="_blank" href="'.$reqId.'">URL requête docid HAL</a><br>');
				//echo $reqId.'<br>';
				$contId = file_get_contents($reqId);
				$resId = json_decode($contId);
				$numFound = 0;
				if(isset($resId->response->numFound)) {$numFound = $resId->response->numFound;}
				if($numFound != 0) {
					$tests = $resId->response->docs[0]->authIdLastNameFirstName_fs;
					foreach($tests as $test) {
						if((strpos($test, ($halAut[$i]['firstName'])) !== false || strpos($test, (substr($halAut[$i]['firstName'], 0, 1))) !== false) && strpos($test, $halAut[$i]['lastName']) !== false) {
							$testTab = explode('_FacetSep_', $test);
							$halAut[$i]['docid'] = $testTab[0];
							$cptId++;
							break 2;
						}
					}
				}
			}
		}
	}
	$cpt++;
}

echo($cptId.' docid auteur trouvé(s)');

echo('<script>');
echo('document.getElementById(\'cpt3c\').style.display = \'none\';');
echo('</script>');
//Fin étape 3c
?>
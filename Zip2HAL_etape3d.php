<?php
//Etape 3d - Recherche idHAL auteur grâce aux docid auteur trouvés précédemment

//echo '<div class="row">';
echo '    <div class="col-md-6">';
echo '        <div class="card ribbon-box">';
echo '            <div class="card-body">';
echo '                <div class="ribbon ribbon-success float-right">Étape 3d</div>';
echo '                <h5 class="text-success mt-0">Recherche des idHAL auteur grâce aux docid auteur trouvés précédemment</h5>';
echo '                <div class="ribbon-content">';

$cpt = 1;
$cptId = 0;

//echo '<b>Etape 3d : recherche des idHAL auteur grâce aux docid auteur trouvés précédemment</b><br>';
echo '<div id=\'cpt3d\'></div>';

if(isset($typDbl) && ($typDbl == "HALCOLLTYP" || $typDbl == "HALTYP")) {//Doublon de type TYP > inutile d'effectuer les recherches
	echo 'Recherche inutile car c\'est une notice doublon';
}else{
	//Début bloc idHAL
	echo '<span><a style="cursor:pointer;" class="text-primary" onclick="afficacherRec(\'3d\', '.$idFic.')";>Recherche des idHAL</a><br>';
	echo '<span id="Rrec-3d-'.$idFic.'" style="display: none;">';
	
	for($i = 0; $i < count($halAut); $i++) {
		progression($cpt, count($halAut), 'cpt3d', $iPro, 'auteur');
		if($halAut[$i]['docid'] != "" && $halAut[$i]['idHals'] == "") {//L'auteur a bien un docid mais pas d'idHAL
			$reqId = "https://api.archives-ouvertes.fr/ref/author/?q=docid:".$halAut[$i]['docid']."%20AND%20valid_s:(VALID%20OR%20OLD)&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s";
			$reqId = str_replace(" ", "%20", $reqId);
			echo '<a target="_blank" href="'.$reqId.'">URL requête idHAL auteur</a><br>';
			$contId = file_get_contents($reqId);
			$resId = json_decode($contId);
			$numFound = 0;
			if(isset($resId->response->numFound)) {$numFound = $resId->response->numFound;}
			if($numFound != 0) {
				$halAut[$i]['idHali'] = $resId->response->docs[0]->idHal_i;
				$halAut[$i]['idHals'] = $resId->response->docs[0]->idHal_s;
				if(isset($resId->response->docs[0]->emailDomain_s)) {$halAut[$i]['mailDom'] = str_replace('@', '', strstr($resId->response->docs[0]->emailDomain_s, '@'));}
				$cptId++;
				break;
			}
		}
		$cpt++;
	}

	echo '</span></span>';//Fin bloc idHAL
	echo $cptId.' idHAL auteur trouvé(s)';
}

echo '<script>';
echo 'document.getElementById(\'cpt3d\').style.display = \'none\';';
echo '</script>';
//Fin étape 3d

echo '								</div>';
echo '						</div> <!-- end card-body -->';
echo '				</div>';
echo '		</div>';
echo '</div> <!-- .row -->';
?>
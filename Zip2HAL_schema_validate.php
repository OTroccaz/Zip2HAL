<?php
$nomfic = htmlspecialchars($_GET["nomfic"]);
$idFic = htmlspecialchars($_GET["idFic"]);
$idNomfic = htmlspecialchars($_GET["idNomfic"]);
$idTEI = htmlspecialchars($_GET["idTEI"]);
$typDoc = htmlspecialchars($_GET["typDoc"]);
$titreNotS = htmlspecialchars(str_replace("%20", " ", $_GET["titreNot"]));
$datePub = htmlspecialchars($_GET["datePub"]);
$portail = htmlspecialchars($_GET["portail"]);
$login = htmlspecialchars($_GET["login"]);
$team = htmlspecialchars($_GET["team"]);
$lienMAJ = "./Zip2HALModif.php?action=MAJ&Id=".$idNomfic;

//Correction/bug Ukraine/UA
$xml = new DOMDocument( "1.0", "UTF-8" );
$xml->formatOutput = true;
$xml->preserveWhiteSpace = false;
$xml->load($nomfic);
$xml->save($nomfic);
$elts = $xml->getElementsByTagName("country");
foreach($elts as $elt) {
	if($elt->hasAttribute("key") && $elt->getAttribute("key") == "UK") {
		$elt->removeAttribute("key");
		$xml->save($nomfic);
		$elt->setAttribute("key", "UA");
		$xml->save($nomfic);
	}
}

$tst = new DOMDocument();
$tst->load($nomfic);
if(!$tst->schemaValidate('./aofr.xsd')) {
	echo '<script>effacerPopup();</script>';
	echo '<script>document.getElementById("validerTEI-'.$idFic.'").innerHTML = "<img alt=\'TEI non valide AOFR\' src=\'./img/supprimer.jpg\'>";</script>';
	echo '<script>document.getElementById("content").innerHTML = "";</script>';
}else{
	//$contenu = '<center><span id=\''.$idNomfic.'-'.$idFic.'\'><a target=\'_blank\' href=\''.$lienMAJ.'\' onclick=\"$.post(\'Zip2HAL_liste_actions.php\', {idNomfic : \''.$idNomfic.'\', action: \'statistiques\', valeur: \''.$idNomfic.'\', idTEI: \''.$idTEI.'\', typDoc: \''.$typDoc.'\', titreNot: \''.$titreNotS.'\', datePub: \''.$datePub.'\', portail: \''.$racine.'\'}); majokVu(\''.$idNomfic.'-'.$idFic.'\');\"><img alt=\'MAJ\' src=\'./img/MAJ.png\'></a></span></center>';
	$contenu = '<center><span id=\''.$idNomfic.'-'.$idFic.'\'><a target=\'_blank\' href=\''.$lienMAJ.'\' onclick=\"$.post(\'Zip2HAL_liste_actions.php\', {idNomfic : \''.$idNomfic.'\', action: \'statistiques\', valeur: \''.$idNomfic.'\', idTEI: \''.$idTEI.'\', typDoc: \''.$typDoc.'\', titreNot: \''.$titreNotS.'\', datePub: \''.$datePub.'\', portail: \''.$portail.'\', login: \''.$login.'\', team: \''.$team.'\'});\"><img alt=\'MAJ\' src=\'./img/MAJ.png\'></a></span></center>';
	echo '<script>effacerPopup();</script>';
	echo '<script>document.getElementById("validerTEI-'.$idFic.'").innerHTML = "<img alt=\'TEI validé AOFR\' src=\'./img/done.png\'>";</script>';
	echo '<script>document.getElementById("importerHAL-'.$idFic.'").innerHTML = "'.$contenu.'";</script>';
	echo '<script>document.getElementById("content").innerHTML = "";</script>';
	
}

?>
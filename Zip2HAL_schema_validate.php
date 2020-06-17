<?php
$nomfic = $_GET["nomfic"];
$idFic = $_GET["idFic"];
$idNomfic = $_GET["idNomfic"];
$lienMAJ = "./Zip2HALModif.php?action=MAJ&Id=".$idNomfic;

$tst = new DOMDocument();
$tst->load($nomfic);
if(!$tst->schemaValidate('./aofr.xsd')) {
	echo('<script>effacerPopup();</script>');
	echo('<script>document.getElementById("validerTEI-'.$idFic.'").innerHTML = "<img alt=\'TEI non valide AOFR\' src=\'./img/supprimer.jpg\'>";</script>');
	echo('<script>document.getElementById("content").innerHTML = "";</script>');
}else{
	$contenu = '<center><span id=\''.$idNomfic.'-'.$idFic.'\'><a target=\'_blank\' href=\''.$lienMAJ.'\' onclick=\"$.post(\'Zip2HAL_liste_actions.php\', {idNomfic : \''.$idNomfic.'\', action: \'statistiques\', valeur: \''.$idNomfic.'\'}); majokVu(\''.$idNomfic.'-'.$idFic.'\');\"><img alt=\'MAJ\' src=\'./img/MAJ.png\'></a></span></center>';
	echo('<script>effacerPopup();</script>');
	echo('<script>document.getElementById("validerTEI-'.$idFic.'").innerHTML = "<img alt=\'TEI validé AOFR\' src=\'./img/done.png\'>";</script>');
	echo('<script>document.getElementById("importerHAL-'.$idFic.'").innerHTML = "'.$contenu.'";</script>');
	echo('<script>document.getElementById("content").innerHTML = "";</script>');
	
}

?>
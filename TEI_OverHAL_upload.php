<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "https://www.w3.org/TR/html4/loose.dtd">
<?php
header('Content-type: text/html; charset=UTF-8');

if (isset($_GET['css']) && ($_GET['css'] != ""))
{
  $css = $_GET['css'];
}else{
  $css = "https://ecobio.univ-rennes1.fr/HAL_SCD.css";
}
?>

<html lang="fr">
<head>
  <title>Zip2HAL</title>
  <meta name="Description" content="Zip2HAL">
  <link href="bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $css;?>" type="text/css">
  <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <script type="text/javascript" language="Javascript" src="./Zip2HAL.js"></script>
  <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
  <link rel="icon" type="type/ico" href="favicon.ico">
  <link rel="stylesheet" href="./Zip2HAL.css">
</head>
<body>

<noscript>
<div class=' red center' id='noscript'><strong>ATTENTION !!! JavaScript est désactivé ou non pris en charge par votre navigateur : cette procédure ne fonctionnera pas correctement.</strong><br>
<strong>Pour modifier cette option, voir <a target='_blank' rel='noopener noreferrer' href='https://www.libellules.ch/browser_javascript_activ.php'>ce lien</a>.</strong></div><br>
</noscript>

<table class="table100" aria-describedby="Entêtes">
<tr>
<th scope="col" style="text-align: left;"><img alt="Zip2HAL" title="Zip2HAL" width="250px" src="./img/logo_Zip2hal.png"></td>
<th scope="col" style="text-align: right;"><img alt="Université de Rennes 1" title="Université de Rennes 1" width="150px" src="./img/logo_UR1_gris_petit.jpg"></td>
</tr>
</table>
<hr style="color: #467666; height: 1px; border-width: 1px; border-top-color: #467666; border-style: inset;">

<?php
$location = "Location: "."TEI_OverHAL.php";
$erreur = "";
$qui = "TEI_OverHAL";
$xml = "./XML/";
$hal = "/HAL/";

if (isset($_FILES[$qui]['name']) && $_FILES[$qui]['name'] != "") //File has been submitted
{
	if ($_FILES[$qui]['error'])
	{
		switch ($_FILES[$qui]['error'])
		{
			 case 1: // UPLOAD_ERR_INI_SIZE
				 if($erreur == "") {$erreur = "?erreur=1";}
				 break;
			 case 2: // UPLOAD_ERR_FORM_SIZE
				  if($erreur == "") {$erreur = "?erreur=2";}
				 break;
			 case 3: // UPLOAD_ERR_PARTIAL
				  if($erreur == "") {$erreur = "?erreur=3";}
				 break;
			 //case 4: // UPLOAD_ERR_NO_FILE
				 //if($erreur == "") {$erreur = "?erreur=4";}
				 //break;
			 default:
				  if($erreur == "") {$erreur = "?erreur=0";}
				 break;
		}
	}
	$extension = strrchr($_FILES[$qui]['name'], '.');
	if ($extension != ".zip") {
		 if($erreur == "") {$erreur = "?erreur=5";}
	}
	$temps = time();
	mkdir($xml.$temps);
	$nomfic = "./XML/TEI_OverHAL_".$temps.".zip";
	move_uploaded_file($_FILES[$qui]['tmp_name'], $nomfic);
	$zip = new ZipArchive;
	if ($zip->open($nomfic) === TRUE) {
		$zip->extractTo('./XML/'.$temps);
		$zip->close();
	}else{
		 if($erreur == "") {$erreur = "?erreur=8";}
	}
	
	//Déplacer les fichier sous HAL
	if (is_dir($xml.$temps.$hal)) {
		if ($dh = opendir($xml.$temps.$hal)) {
			while (($file = readdir($dh)) !== false) {
				if($file != '.' && $file != '..') {
					copy($xml.$temps.$hal.$file, $xml.$temps."/".$file );
					unlink($xml.$temps.$hal.$file);
				}
			}
			closedir($dh);
			rmdir($xml.$temps.$hal);
		}
	}
	
	//Vérification que l'archive ne contient bien que des fichiers xml
	$repertoire = opendir($xml.$temps);
	while(false !== ($fichier = readdir($repertoire))) {
		$chemin = $xml.$temps."/".$fichier;
		$infos = pathinfo($chemin);
		if ($fichier != "." && $fichier != ".." && !is_dir($fichier) && $infos['extension'] != "xml") {
			//Extension non xml > suppression dossier créé et archive zip copiée
			$repertoire = opendir($xml.$temps);
			while(false !== ($fichier = readdir($repertoire))) {
				$chemin = $xml.$temps."/".$fichier;
				if ($fichier != "." && $fichier != ".." && !is_dir($fichier)) {
					unlink($chemin);
				}
			}
			rmdir($xml.$temps);
			unlink($nomfic);
			 if($erreur == "") {$erreur = "?erreur=9";}
		}
	}
  closedir($repertoire);
	if($erreur == "") {
		Header("Location: "."Zip2HAL.php?nomficZip=".$nomfic);
	}else{
		Header("Location: "."TEI_Overhal.php".$erreur);
	}
}
?>

<?php
include('./bas.php');
?>
</body>
</html>
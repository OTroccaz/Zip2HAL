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
<b>Pour modifier cette option, voir <a target='_blank' rel='noopener noreferrer' href='https://www.libellules.ch/browser_javascript_activ.php'>ce lien</a>.</b></div><br>
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
if (isset($_FILES['TEI_OverHAL']['name']) && $_FILES['TEI_OverHAL']['name'] != "") //File has been submitted
{
	if ($_FILES['TEI_OverHAL']['error'])
	{
		switch ($_FILES['TEI_OverHAL']['error'])
		{
			 case 1: // UPLOAD_ERR_INI_SIZE
			 Header($location."?erreur=1");
			 break;
			 case 2: // UPLOAD_ERR_FORM_SIZE
			 Header($location."?erreur=2");
			 break;
			 case 3: // UPLOAD_ERR_PARTIAL
			 Header($location."?erreur=3");
			 break;
			 //case 4: // UPLOAD_ERR_NO_FILE
			 //Header("Location: "."OverHAL.php?erreur=4");
			 //break;
		}
	}
	$extension = strrchr($_FILES['TEI_OverHAL']['name'], '.');
	if ($extension != ".zip") {
		Header($location."?erreur=5");
	}
	$temps = time();
	mkdir("./XML/".$temps);
	$nomfic = "./XML/TEI_OverHAL_".$temps.".zip";
	move_uploaded_file($_FILES['TEI_OverHAL']['tmp_name'], $nomfic);
	$zip = new ZipArchive;
	if ($zip->open($nomfic) === TRUE) {
		$zip->extractTo('./XML/'.$temps);
		$zip->close();
	}else{
		Header($location."?erreur=8");
	}
	
	//Déplacer les fichier sous HAL
	if (is_dir("./XML/".$temps."/HAL/")) {
		if ($dh = opendir("./XML/".$temps."/HAL/")) {
			while (($file = readdir($dh)) !== false) {
				if($file != '.' && $file != '..') {
					copy("./XML/".$temps."/HAL/".$file, "./XML/".$temps."/".$file );
					unlink("./XML/".$temps."/HAL/".$file);
				}
			}
			closedir($dh);
			rmdir("./XML/".$temps."/HAL/");
		}
	}
	
	//Vérification que l'archive ne contient bien que des fichiers xml
	$repertoire = opendir("./XML/".$temps);
	while(false !== ($fichier = readdir($repertoire))) {
		$chemin = "./XML/".$temps."/".$fichier;
		$infos = pathinfo($chemin);
		if ($fichier != "." && $fichier != ".." && !is_dir($fichier) && $infos['extension'] != "xml") {
			//Extension non xml > suppression dossier créé et archive zip copiée
			$repertoire = opendir("./XML/".$temps);
			while(false !== ($fichier = readdir($repertoire))) {
				$chemin = "./XML/".$temps."/".$fichier;
				if ($fichier != "." && $fichier != ".." && !is_dir($fichier)) {
					unlink($chemin);
				}
			}
			rmdir("./XML/".$temps);
			unlink($nomfic);
			Header($location."?erreur=9");
		}
	}
  closedir($repertoire);

	Header("Location: "."Zip2HAL.php?nomficZip=".$nomfic);
}
?>

<?php
include('./bas.php');
?>
</body>
</html>
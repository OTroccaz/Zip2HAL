<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "https://www.w3.org/TR/html4/loose.dtd">
<?php
$loc = "Location: ";
$locT = $loc."Zip2HAL_TEI_OverHAL.php";
$erreur = "";
$qui = "TEI_OverHAL";
$xml = "./XML/";
$hal = "/HAL/";

if(isset($_FILES[$qui]['name']) && $_FILES[$qui]['name'] != "") //File has been submitted
{
	if($_FILES[$qui]['error'])
	{
		switch($_FILES[$qui]['error'])
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
			 default:
				  if($erreur == "") {$erreur = "?erreur=0";}
				 break;
		}
	}
	$extension = strrchr($_FILES[$qui]['name'], '.');
	if($extension != ".zip" && $erreur == "") {$erreur = "?erreur=5";}
	
	$temps = time();
	mkdir($xml.$temps);
	$nomfic = "./XML/Zip2HAL_TEI_OverHAL_".$temps.".zip";
	move_uploaded_file($_FILES[$qui]['tmp_name'], $nomfic);
	$zip = new ZipArchive;
	if($zip->open($nomfic) === TRUE) {
		$zip->extractTo('./XML/'.$temps);
		$zip->close();
	}else{
		 if($erreur == "") {$erreur = "?erreur=8";}
	}
	
	//Déplacer les fichier sous HAL
	if(is_dir($xml.$temps.$hal) && $dh = opendir($xml.$temps.$hal)) {
		while(($file = readdir($dh)) !== false) {
			if($file != '.' && $file != '..') {
				copy($xml.$temps.$hal.$file, $xml.$temps."/".$file );
				unlink($xml.$temps.$hal.$file);
			}
		}
		closedir($dh);
		rmdir($xml.$temps.$hal);
	}
	
	//Vérification que l'archive ne contient bien que des fichiers xml
	$repertoire = opendir($xml.$temps);
	while(false !==($fichier = readdir($repertoire))) {
		$chemin = $xml.$temps."/".$fichier;
		$infos = pathinfo($chemin);
		if($fichier != "." && $fichier != ".." && !is_dir($fichier) && $infos['extension'] != "xml") {
			//Extension non xml > suppression dossier créé et archive zip copiée
			$repertoire = opendir($xml.$temps);
			while(false !==($fichier = readdir($repertoire))) {
				$chemin = $xml.$temps."/".$fichier;
				if($fichier != "." && $fichier != ".." && !is_dir($fichier)) {
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
		Header($loc."Zip2HAL.php?nomficZip=".$nomfic);
	}else{
		Header($loc."Zip2HAL_TEI_Overhal.php".$erreur);
	}
}else{
	Header($loc."Zip2HAL_TEI_Overhal.php?erreur=4");
}
?>
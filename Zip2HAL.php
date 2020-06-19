<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "https://www.w3.org/TR/html4/loose.dtd">
<?php
//authentification CAS ou autre ?
if(strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false || strpos($_SERVER['HTTP_HOST'], 'ecobio') !== false) {
  include('./_connexion.php');
  $HAL_USER = $user;
  $HAL_PASSWD = $pass;
}else{
  require_once('./CAS_connect.php');
}

//Avant tout, vérification de l'étape de chargement du fichier zip des TEI xml OverHAL
if(isset($_GET['nomficZip'])) {
	$nomficZip = $_GET['nomficZip'];
}else{
	header('Location: '.'TEI_OverHAL.php?erreur=6');
}

//Si le fichier a été supprimé
if(!isset($nomficZip) || !file_exists($nomficZip)) {
	header('Location: '.'TEI_OverHAL.php?erreur=7');
}

header('Content-type: text/html; charset=UTF-8');

register_shutdown_function(function() {
    $error = error_get_last();

    if($error['type'] === E_ERROR && strpos($error['message'], 'Maximum execution time of') === 0) {
        echo "<br><b><font color='red'>Le script a été arrêté car son temps d'exécution dépasse la limite maximale autorisée.</font></b><br>";
    }
});

if(isset($_GET['css']) && ($_GET['css'] != ""))
{
  $css = $_GET['css'];
}else{
  $css = "https://ecobio.univ-rennes1.fr/HAL_SCD.css";
}

include "./Zip2HAL_nodes.php";
include "./Zip2HAL_codes_pays.php";
include "./Zip2HAL_codes_langues.php";

include "./Zip2HAL_fonctions.php";

suppression("./XML", 3600);//Suppression des fichiers et dossiers du dossier XML créés il y a plus d'une heure

include("./normalize.php");
include("./URLport_coll.php");
include('./DOMValidator.php');
?>

<html lang="fr">
<head>
  <title>Zip2HAL</title>
  <meta name="Description" content="Zip2HAL">
  <link href="bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $css;?>" type="text/css">
  <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css" type="text/css">
  <link rel="icon" type="type/ico" href="favicon.ico">
	<script type="text/javascript" language="Javascript" src="./Zip2HAL.js"></script>
  <link rel="stylesheet" href="./Zip2HAL.css">
</head>
<body>
<div id="top"></div>
<noscript>
<div align='center' id='noscript'><font color='red'><b>ATTENTION !!! JavaScript est désactivé ou non pris en charge par votre navigateur : cette procédure ne fonctionnera pas correctement.</b></font><br>
<b>Pour modifier cette option, voir <a target='_blank' href='https://www.libellules.ch/browser_javascript_activ.php'>ce lien</a>.</b></div><br>
</noscript>

<div id='content'></div>

<table width="100%">
<tr>
<td style="text-align: left;"><img alt="Zip2HAL" title="Zip2HAL" width="250px" src="./img/logo_Zip2hal.png"></td>
<td style="text-align: right;"><img alt="Université de Rennes 1" title="Université de Rennes 1" width="150px" src="./img/logo_UR1_gris_petit.jpg"></td>
</tr>
</table>
<hr style="color: #467666; height: 1px; border-width: 1px; border-top-color: #467666; border-style: inset;">

<p>Zip2HAL permet ...</p>

<br>
<?php
//echo time();
echo('<br>');
echo('<div id="haut"><a href="#top">Haut de page</a></div>');

$team = "";//Code collection HAL
$racine = "";//Portail de dépôt
$domaine = "";//Domaine disciplinaire

if(isset($_POST["soumis"])) {
	$team = htmlspecialchars($_POST["team"]);
	if($team == "Entrez le code de votre collection") {//Code collection non renseigné > on en met un par défaut
		$team = "ECOBIO";
	}
	$racine = htmlspecialchars($_POST["racine"]);
	if(isset($_POST["domaine"])) {$domaine = htmlspecialchars($_POST["domaine"]);}
}
?>

<form method="POST" accept-charset="utf-8" name="zip2hal" action="Zip2HAL.php?nomficZip=<?php echo($nomficZip); ?>">

<?php
if($racine == "") {$racine = "https://hal-univ-rennes1.archives-ouvertes.fr/";}
?>

<p class="form-inline"><label for="racine">Portail de dépôt :</label>

<select id="racine" class="form-control" size="1" name="racine" style="padding: 3px; width: 350px;">
<?php
$tabcoll = array_keys($collport);
for ($i=0; $i < count($tabcoll); $i++) {
	if($racine == $tabcoll[$i]) {$txt = "selected";}else{$txt = "";}
	echo('<option '.$txt.' value="'.$tabcoll[$i].'">'.$tabcoll[$i].'</option>');
}
?>
</select>
</p>

<p class="form-inline"><b><label for="team">Code collection HAL</label></b> <a class=info onclick='return false' href="#">(qu’est-ce que c’est ?)<span>Code visible dans l’URL d’une collection.
Exemple : IPR-MOL est le code de la collection https://hal.archives-ouvertes.fr/<b>IPR-PMOL</b> de l’équipe Physique moléculaire
de l’unité IPR UMR CNRS 6251</span></a> :
<?php
$team1 = "";
$team2 = "";
if(isset($team) && $team != "") {
	$team1 = $team;
	$team2 = $team;
}else{
	$team1 = "Entrez le code de votre collection";
	$team2 = "";
}
?>
<input type="text" id ="team" name="team" class="form-control" style="height: 25px; width:300px" value="<?php echo $team1;?>" onClick="this.value='<?php echo $team2;?>';" >&nbsp;<a target="_blank" href="https://hal-univ-rennes1.archives-ouvertes.fr/page/codes-collections">Trouver le code de mon équipe / labo</a><br>

<p class="form-inline"><b><label for="domaine">Domaine disciplinaire : </label></b>
<?php
if($domaine == "") {
	if(isset($_POST["soumis"])) {
		echo('-');
	}else{
		echo('<span id="domaine" style="display:none;">');
		echo('</span>');
		echo('<span id="choixdom">');
		echo('&nbsp;si vous connaissez une partie du code, utilisez le champ ci-dessous puis validez avec le bouton vert, autrement, l\'arborescence dynamique ci-après.');
		echo('<br>');
		echo('<input type="text" id ="inputdom" name="inputdom" class="autoDO form-control" style="margin-left: 30px; height: 18px; width:300px">');
		echo('&nbsp;<b>+</b>&nbsp;<a style="cursor:pointer;" onclick="choixdom($(\'#inputdom\').val(),\'\');"><img width=\'12px\' alt=\'Valider le domaine\' src=\'./img/done.png\'></a>');
		echo('<br>');

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
				echo('<span style=\'margin-left: 30px;\' id=\'cod-'.$cpt.'-0\'><a style=\'cursor:pointer;\' onclick=\'afficacher('.$cpt.','.'0'.')\';><font style=\'color: #FE6D02;\'><b>>&nbsp;</b></font></a></span>');
				echo('<span><a style=\'cursor:pointer;\' onclick=\'choixdom("'.$domF.'","'.$code.'");\'>'.$domF.'</a></span><br>');
				$codI = $codF;
				echo('<span id=\'dom-'.$cpt.'-0\' style=\'display:none;\'>');
				$cpt++;
			}else{//Liste des différentes sous-matières de la discipline
				$sMat = str_replace($domF.'/', '', str_replace("'", "’", $entry->fr_domain_s));
				$sMatVal = str_replace("'", "’", $entry->fr_domain_s);
				//$sMatTab = explode("/", $entry->fr_domain_s);
				//$num = count($sMatTab) - 1;
				//$sMatVal = $sMatTab[$num];
				echo('<span style=\'margin-left: 60px;\'><a style=\'cursor:pointer;\' onclick=\'choixdom("'.$sMatVal.'","'.$code.'");\'>'.$sMat.'</a></span><br>');
			}
		}
		echo('</span>');
	}
}else{
	echo($domaine);
}
?>
</p>

<p class="form-inline"><b><label for="teioverhal">Fichier ZIP TEI OverHAL : </label></b>
<?php
if(file_exists($nomficZip)) {
	echo($nomficZip);
}

//Nouvelle soumission d'archive
echo('<p class="form-inline"><a href="./TEI_OverHAL.php">Nouvelle soumission d\'archive</a></p>');
?>

</p>

<br>
<input type="submit" class="btn btn-md btn-primary" value="Valider" name="soumis">
</form>
<br>

<?php

if(isset($_POST["soumis"])) {
	$dir = str_replace(array("TEI_OverHAL_", ".zip"), "", $nomficZip);
	$tabFic = scandir($dir);
	$idFic = 1;
	foreach($tabFic as $nomfic) {
		if(substr($nomfic, -2, 2) !== '..' && substr($nomfic, -1, 1) !== '.') {		
			$nomfic = $dir."/".$nomfic; 

			//Chargement du fichier XML
			$xml = new DOMDocument( "1.0", "UTF-8" );
			$xml->formatOutput = true;
			$xml->preserveWhiteSpace = false;
			$xml->load($nomfic);
			$xml->save($nomfic);
			
			//Récupération du titre, du DOI et du type de document de la notice TEI
			$titTEI = "";
			$doiTEI = "";
			$typTEI = "";
			$tits = $xml->getElementsByTagName("title");
			foreach($tits as $tit) {
				if($tit->hasAttribute("xml:lang")) {$titTEI = $tit->nodeValue;}
			}
			$idns = $xml->getElementsByTagName("idno");
			foreach($idns as $idn) {
				if($idn->hasAttribute("type") && $idn->getAttribute("type") == 'doi') {$doiTEI = $idn->nodeValue;}
			}
			$typs = $xml->getElementsByTagName("classCode");
			foreach($typs as $typ) {
				if($typ->hasAttribute("scheme") && $typ->getAttribute("scheme") == 'halTypology') {$typTEI = $typ->getAttribute("n");}
			}
			$enctitTEI = normalize(utf8_encode(mb_strtolower(utf8_decode($titTEI))));
			//echo '<br>'.$doiTEI. ' > '.$titTEI;
			
			//Récupération du premier mot du titre pour limiter la recherche API
			$tabTit = explode(' ', $titTEI);
				
			$portail = $collport[$racine];
			//$reqAPI = "https://api.archives-ouvertes.fr/search/?fq=producedDateY_i:2016&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
			//$reqAPI = "https://api.archives-ouvertes.fr/search/".$portail."/?fq=*:*&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
			//$reqAPI = "https://api.archives-ouvertes.fr/search/".$portail."/?fq=producedDateY_i:2016&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
			
			//Récupération de l'année de publication
			$anns = $xml->getElementsByTagName("date");
			$datePub = "";
			foreach($anns as $ann) {
				if($ann->hasAttribute("type") && $ann->getAttribute("type") == "datePub") {
					$datePub = $ann->nodeValue;
					$datePub1 = $datePub - 1;
					$datePub2 = $datePub + 1;
				}
			}
			if($datePub != "") {$special = "%20AND%20producedDateY_i:(".$datePub1."%20OR%20".$datePub."%20OR%20".$datePub2.")";}else{$special = "";}
			
			$reqAPI = "https://api.archives-ouvertes.fr/search/?fq=title_t:%22".strtolower($tabTit[0])."*%22".$special."&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
			$contents = file_get_contents($reqAPI);
			$results = json_decode($contents);
			$numFound = 0;
			if(isset($results->response->numFound)) {$numFound=$results->response->numFound;}
			
			echo('<br><b><font style=\'color:#fe6d02; font-size:14px;\'>Traitement du fichier '.str_replace($dir."/", "", $nomfic).'</font></b><br>');
			
			include('./Zip2HAL_etape1.php');
			
			if(isset($typDbl) && $typDbl != "HALCOLLTYP") {//Pas un doublon de type HAL et COLL
				
				include('./Zip2HAL_etape2.php');
				include('./Zip2HAL_etape3a.php');
				include('./Zip2HAL_etape3b.php');
				include('./Zip2HAL_etape3c.php');
				include('./Zip2HAL_etape3d.php');

				//var_dump($halAut);
				//var_dump($halAff);

				/*
				echo('<br><br>');
				echo('Tableau initial obtenu pour les idHAL des auteurs ($halAutinit) :');
				var_dump($halAutinit);
				echo('<br><br>');
				echo('Tableau final obtenu pour les idHAL des auteurs ($halAut) :');
				var_dump($halAut);
				echo('<br><br>');
				echo('Tableau des noms des affiliations ($nomAff) :');
				var_dump($nomAff);
				echo('<br><br>');
				echo('Tableau obtenu pour les id structure des affiliations ($halAff) :');
				var_dump($halAff);
				*/


			}
			
			include('./Zip2HAL_premieres_modifications_TEI.php');
			
			include('./Zip2HAL_tableau_resultats.php');
			
			//TODO stats à mettre en place
			
		}
		$idFic++;
	}
	//Vérification si des métadonnées sont manquantes
	//var_dump($tabMetaMQ);
	$tabKey = array_keys($tabMetaMQ);
	$message = "";
	$arrayMQ = "non";
	if(!empty($tabMetaMQ)) {
		foreach($tabKey as $key) {
			if(!empty($tabMetaMQ[$key])) {
				$message .= "Fichier ".str_replace($dir."/", "", $key)." :<br>";
				foreach($tabMetaMQ[$key] as $elt) {
					$message .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;La métadonnée concernant ".$elt." est manquante.<br>";
					$arrayMQ = "oui";
				}
				$message .= "<br>";
			}
			
		}
	}
	if($arrayMQ == "oui") {echo('<script>afficherPopupAvertissement("'.$message.'");</script>');}
}
?>

<!--Ajustement automatique des textarea-->
<script type="text/javascript" language="Javascript" src="./autoresize.jquery.js"></script>
<script type="text/javascript">
	$('textarea').autoResize();
</script>

<?php
echo('<br><br>');
include('./bas.php');
?>
</body>
</html>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "https://www.w3.org/TR/html4/loose.dtd">
<?php
//Avant tout, vérification de l'étape de chargement du fichier zip des TEI xml OverHAL
if (isset($_GET['nomficZip'])) {
	$nomficZip = $_GET['nomficZip'];
}else{
	header('Location: '.'TEI_OverHAL.php?erreur=6');
}

//Si le fichier a été supprimé
if (!isset($nomficZip) || !file_exists($nomficZip)) {
	header('Location: '.'TEI_OverHAL.php?erreur=7');
}

header('Content-type: text/html; charset=UTF-8');

register_shutdown_function(function() {
    $error = error_get_last();

    if ($error['type'] === E_ERROR && strpos($error['message'], 'Maximum execution time of') === 0) {
        echo "<br><b><font color='red'>Le script a été arrêté car son temps d'exécution dépasse la limite maximale autorisée.</font></b><br>";
    }
});

if (isset($_GET['css']) && ($_GET['css'] != ""))
{
  $css = $_GET['css'];
}else{
  $css = "https://ecobio.univ-rennes1.fr/HAL_SCD.css";
}

include "./Zip2HAL_nodes.php";
include "./Zip2HAL_codes_pays.php";

function progression($indice, $iMax, $id, &$iPro, $quoi) {
	$iPro = $indice;
	echo('<script>');
  echo('var txt = \'Traitement '.$quoi.' '.$indice.' sur '.$iMax.'<br>\';');
	echo('document.getElementById(\''.$id.'\').innerHTML = txt');
	echo('</script>');
	ob_flush();
	flush();
	ob_flush();
	flush();
}

//Suppresion des accents
function wd_remove_accents($str, $charset='utf-8')
{
    $str = htmlentities($str, ENT_NOQUOTES, $charset);

    $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

    return $str;
}

//Nettoyage des dossiers de création de fichiers
function suppression($dir, $age) {
	
	$handle = opendir($dir);
	while($elem = readdir($handle)) {//ce while vide tous les répertoires et sous répertoires
		$ageElem = time() - filemtime($dir.'/'.$elem);
		if ($ageElem > $age) {
			if(is_dir($dir.'/'.$elem) && substr($elem, -2, 2) !== '..' && substr($elem, -1, 1) !== '.') {//si c'est un répertoire
				suppression($dir.'/'.$elem, $age);
			}else{
				if(substr($elem, -2, 2) !== '..' && substr($elem, -1, 1) !== '.')	{
					unlink($dir.'/'.$elem);
				}
			}
		}			
	}
	
	$handle = opendir($dir);
	while($elem = readdir($handle)) {//ce while efface tous les dossiers
		$ageElem = time() - filemtime($dir.'/'.$elem);
		if ($ageElem > $age) {
			if(is_dir($dir.'/'.$elem) && substr($elem, -2, 2) !== '..' && substr($elem, -1, 1) !== '.') {//si c'est un repertoire
				suppression($dir.'/'.$elem, $age);
				rmdir($dir.'/'.$elem);
			}    
		}
	}
}
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
  <script type="text/javascript" language="Javascript" src="./Zip2HAL.js"></script>
  <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css" type="text/css">
  <link rel="icon" type="type/ico" href="favicon.ico">
  <link rel="stylesheet" href="./Zip2HAL.css">
</head>
<body>

<noscript>
<div align='center' id='noscript'><font color='red'><b>ATTENTION !!! JavaScript est désactivé ou non pris en charge par votre navigateur : cette procédure ne fonctionnera pas correctement.</b></font><br>
<b>Pour modifier cette option, voir <a target='_blank' href='https://www.libellules.ch/browser_javascript_activ.php'>ce lien</a>.</b></div><br>
</noscript>

<!--Autocomplete idHAL-->
<script type="text/javascript">
$(function() {
    
	//autocomplete
	$(".autoID").autocomplete({
			source: "AC_ID.php",
			minLength: 1
	});                

});
</script>

<!--Autocomplete affiliations-->
<script type="text/javascript">
$(function() {
    
	//autocomplete
	$(".autoAF").autocomplete({
			source: "AC_AF.php",
			minLength: 1,
			open: function (event, ui) {
				$('.ui-autocomplete > li').css("background-color", function() {
						return $(this).text().indexOf('VALID') > -1 ? '#dff0d8' : ($(this).text().indexOf('INCOMING') > -1 ? '#fcf8e3' : '#f2dede');
				});
				$('.ui-menu-item > div').css("color", function() {
						return $(this).text().indexOf('VALID') > -1 ? '#698d53' : ($(this).text().indexOf('INCOMING') > -1 ? '#cfac72' : '#b96f7b');
				});
			}	
	})

});
</script>

<style type="text/css">
	.ui-autocomplete {
			font-family: 'Corbel', sans-serif;
			font-size:12px;       
	} 
</style>

<!--Autocomplete domaine-->
<script type="text/javascript">
$(function() {
    
	//autocomplete
	$(".autoDO").autocomplete({
			source: "AC_DO.php",
			minLength: 1
	});                

});
</script>

<!--Autocomplete financements ANR-->
<script type="text/javascript">
$(function() {
    
    //autocomplete
    $(".autoANR").autocomplete({
        source: "AC_ANR.php",
        minLength: 1
    });                

});
</script>

<!--Autocomplete financements EUR-->
<script type="text/javascript">
$(function() {
    
    //autocomplete
    $(".autoEUR").autocomplete({
        source: "AC_EUR.php",
        minLength: 1
    });                

});
</script>

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
/*
//authentification CAS ou autre ?
if (strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false || strpos($_SERVER['HTTP_HOST'], 'ecobio') !== false) {
  include('./_connexion.php');
  $HAL_USER = $user;
  $HAL_PASSWD = $pass;
}else{
  require_once('./CAS_connect.php');
  
  session_start();
  $HAL_USER = phpCAS::getUser();
  $_SESSION['HAL_USER'] = $HAL_USER;
  $HAL_PASSWD = "";
  if (isset($_POST['password']) && $_POST['password'] != "") {$_SESSION['HAL_PASSWD'] = htmlspecialchars($_POST['password']);}

  if (isset($_SESSION['HAL_PASSWD']) && $_SESSION['HAL_PASSWD'] != "") {
    $HAL_PASSWD = $_SESSION['HAL_PASSWD'];
  }else{
    include('./Zip2HALForm.php');
    die();
  }
}
*/
//echo time();
echo ('<br>');

$team = "";//Code collection HAL
$racine = "";//Portail de dépôt
$domaine = "";//Domaine disciplinaire

if (isset($_POST["soumis"])) {
	$team = htmlspecialchars($_POST["team"]);
	if ($team == "Entrez le code de votre collection") {//Code collection non renseigné > on en met un par défaut
		$team = "ECOBIO";
	}
	$racine = htmlspecialchars($_POST["racine"]);
	if (isset($_POST["domaine"])) {$domaine = htmlspecialchars($_POST["domaine"]);}
}
?>

<form method="POST" accept-charset="utf-8" name="zip2hal" action="Zip2HAL.php?nomficZip=<?php echo($nomficZip); ?>">

<?php
if ($racine == "") {$racine = "https://hal-univ-rennes1.archives-ouvertes.fr/";}
?>

<p class="form-inline"><label for="racine">Portail de dépôt :</label>

<select id="racine" class="form-control" size="1" name="racine" style="padding: 3px; width: 350px;">
<?php
$tabcoll = array_keys($collport);
for ($i=0; $i < count($tabcoll); $i++) {
	if ($racine == $tabcoll[$i]) {$txt = "selected";}else{$txt = "";}
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
if (isset($team) && $team != "") {
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
if ($domaine == "") {
	if (isset($_POST["soumis"])) {
		echo ('-');
	}else{
		echo ('<span id="domaine" style="display:none;">');
		echo ('</span>');
		echo ('<span id="choixdom">');
		echo ('&nbsp;si vous connaissez une partie du code, utilisez le champ ci-dessous puis validez avec le bouton vert, autrement, l\'arborescence dynamique ci-après.');
		echo ('<br>');
		echo ('<input type="text" id ="inputdom" name="inputdom" class="autoDO form-control" style="margin-left: 30px; height: 18px; width:300px">');
		echo ('&nbsp;<b>+</b>&nbsp;<a style="cursor:pointer;" onclick="choixdom($(\'#inputdom\').val(),\'\');"><img width=\'12px\' alt=\'Valider le domaine\' src=\'./img/done.png\'></a>');
		echo ('<br>');

		$codI = "";
		$cpt = 1;
		$reqAPI = "https://api.archives-ouvertes.fr/ref/domain/?q=*:*&fl=code_s,fr_domain_s&rows=500&sort=code_s%20ASC";
		$contents = file_get_contents($reqAPI);
		$results = json_decode($contents);
		foreach($results->response->docs as $entry) {
			$code = $entry->code_s;
			$tabCode = explode(".", $code);
			$codF = $tabCode[0];
			if ($codI != $codF) {//Nouveau groupe de disciplines
				if ($cpt != 1) {echo ('</span>');}
				$domF = str_replace("'", "’", $entry->fr_domain_s);
				echo ('<span style=\'margin-left: 30px;\' id=\'cod-'.$cpt.'-0\'><a style=\'cursor:pointer;\' onclick=\'afficacher('.$cpt.','.'0'.')\';><font style=\'color: #FE6D02;\'><b>>&nbsp;</b></font></a></span>');
				echo ('<span><a style=\'cursor:pointer;\' onclick=\'choixdom("'.$domF.'","'.$code.'");\'>'.$domF.'</a></span><br>');
				$codI = $codF;
				echo ('<span id=\'dom-'.$cpt.'-0\' style=\'display:none;\'>');
				$cpt++;
			}else{//Liste des différentes sous-matières de la discipline
				$sMat = str_replace($domF.'/', '', str_replace("'", "’", $entry->fr_domain_s));
				$sMatVal = str_replace("'", "’", $entry->fr_domain_s);
				//$sMatTab = explode("/", $entry->fr_domain_s);
				//$num = count($sMatTab) - 1;
				//$sMatVal = $sMatTab[$num];
				echo ('<span style=\'margin-left: 60px;\'><a style=\'cursor:pointer;\' onclick=\'choixdom("'.$sMatVal.'","'.$code.'");\'>'.$sMat.'</a></span><br>');
			}
		}
		echo ('</span>');
	}
}else{
	echo ($domaine);
}
?>
</p>

<p class="form-inline"><b><label for="teioverhal">Fichier ZIP TEI OverHAL : </label></b>
<?php
if (file_exists($nomficZip)) {
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

if (isset($_POST["soumis"])) {
	$dir = str_replace(array("TEI_OverHAL_", ".zip"), "", $nomficZip);
	$tabFic = scandir($dir);
	$idFic = 1;
	foreach($tabFic as $nomfic) {
		if (substr($nomfic, -2, 2) !== '..' && substr($nomfic, -1, 1) !== '.') {		
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
				if ($tit->hasAttribute("xml:lang")) {$titTEI = $tit->nodeValue;}
			}
			$idns = $xml->getElementsByTagName("idno");
			foreach($idns as $idn) {
				if ($idn->hasAttribute("type") && $idn->getAttribute("type") == 'doi') {$doiTEI = $idn->nodeValue;}
			}
			$typs = $xml->getElementsByTagName("classCode");
			foreach($typs as $typ) {
				if ($typ->hasAttribute("scheme") && $typ->getAttribute("scheme") == 'halTypology') {$typTEI = $typ->getAttribute("n");}
			}
			$enctitTEI = normalize(utf8_encode(mb_strtolower(utf8_decode($titTEI))));
			//echo '<br>'.$doiTEI. ' > '.$titTEI;
			
			//Récupération du premier mot du titre pour limiter la recherche API
			$tabTit = explode(' ', $titTEI);
				
			$portail = $collport[$racine];
			//$reqAPI = "https://api.archives-ouvertes.fr/search/?fq=producedDateY_i:2016&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
			//$reqAPI = "https://api.archives-ouvertes.fr/search/".$portail."/?fq=*:*&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
			//$reqAPI = "https://api.archives-ouvertes.fr/search/".$portail."/?fq=producedDateY_i:2016&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
			$reqAPI = "https://api.archives-ouvertes.fr/search/".$portail."/?fq=title_t:%22".strtolower($tabTit[0])."*%22&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
			$contents = file_get_contents($reqAPI);
			$results = json_decode($contents);
			$numFound = 0;
			if (isset($results->response->numFound)) {$numFound=$results->response->numFound;}
			
			echo('<br><b><font style=\'color:#fe6d02; font-size:14px;\'>Traitement du fichier '.str_replace($dir."/", "", $nomfic).'</font></b><br>');
			
			echo('<b>Etape 1 : recherche des doublons potentiels</b><br>');
			echo('<a target="_blank" href="'.$reqAPI.'">URL requête API HAL</a><br>');
			
			//Etape 1 - Parcours des notices à la recherche de doublons potentiels (DOI ou titre exact)		
			if ($numFound == 0) {			
				echo('Aucune notice trouvée dans HAL, donc, pas de doublon');
				$typDbl = "";
			}else{
				//echo('<br><br>');
				//echo($numFound. " notice(s) trouvée(s)");
				//echo('<br><br>');
				$cpt = 1;
				$dbl = 0;
				$halId = array();
				$typDbl = "";
				$txtDbl = "";
				
				echo($numFound. ' notice(s) examinée(s)<br>');
				echo('<div id=\'cpt1\'></div>');
				
				foreach($results->response->docs as $entry) {
					progression($cpt, $numFound, 'cpt1', $iPro, 'notice');
					$hId = $entry->halId_s;
					$halId['doublon'][$hId] = "";
					$doi = "";
					$titlePlus = "";
					$titreInit = $entry->title_s[0];
					$doublon = "non";
					
					//Le titre du fichier HAL sera la clé principale pour rechercher l'article dans HAL, on le simplifie maintenant (minuscules, pas de ponctuation ni d'espaces, etc.)
					//Le titre intègre-t-il une traduction avec [] ?
					if(strpos($entry->title_s[0], "[") !== false && strpos($entry->title_s[0], "]") !== false)
					{
						$posi = strpos($entry->title_s[0], "[")+1;
						$posf = strpos($entry->title_s[0], "]");
						$tradTitle = substr($entry->title_s[0], $posi, $posf-$posi);
						$encodedTitle = normalize(utf8_encode(mb_strtolower($tradTitle)));
					}else{
						//Y-a-t-il un sous-titre ?
						$titlePlus = $entry->title_s[0];
						if (isset($entry->subTitle_s[0])) {
							$titreInit = $titlePlus;
							$titlePlus .= " : ".$entry->subTitle_s[0];
						}
						$encodedTitle = normalize(utf8_encode(mb_strtolower(utf8_decode($titlePlus))));
					}
					
					//On compare les titres normalisés
					if ($enctitTEI == $encodedTitle) {
						$idTEI = $entry->halId_s;
						$docTEI = $entry->docType_s;
						$halId[$encodedTitle] = $hId;
						$doublon = "titre";
					}

					//On compare également les DOI s'ils sont présents
					if (isset($entry->doiId_s)) {$doi = strtolower($hId);}
					if ($doiTEI != "" && isset($entry->doiId_s) && $doiTEI == $entry->doiId_s) {
						$idTEI = $entry->halId_s;
						$docTEI = $entry->docType_s;
						$halId[$doi] = $hId;
						if ($doublon == "non") {
							$doublon = "DOI";
						}else{
							$doublon .= " et du DOI";
						}
					}
							
					if ($doublon != "non") {
						//Doublon trouvé dans HAL > Est-il aussi présent dans la collection et de quel type ?
						$dbl++;
						$halId['doublon'][$hId] .= '&nbsp;<a target="_blank" href="https://hal.archives-ouvertes.fr/'.$halId[$hId].'"><img src=\'./img/doublon.jpg\'></a>&nbsp;';
						$reqDbl = "https://api.archives-ouvertes.fr/search/".$portail."/?fq=collCode_s:%22".$team."%22%20AND%20title_t:%22".strtolower($tabTit[0])."*%22&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
						//echo $reqDbl.'<br>';
						$contDbl = file_get_contents($reqDbl);
						$resDbl = json_decode($contDbl);
						$numDbl = 0;
						if (isset($results->response->numFound)) {$numDbl=$results->response->numFound;}
						//echo $resDbl.'<br>';
						foreach($resDbl->response->docs as $entDbl) {
							$doublonDbl = "non";
							if(strpos($entDbl->title_s[0], "[") !== false && strpos($entDbl->title_s[0], "]") !== false) {
								$posi = strpos($entDbl->title_s[0], "[")+1;
								$posf = strpos($entDbl->title_s[0], "]");
								$tradTitleDbl = substr($entDbl->title_s[0], $posi, $posf-$posi);
								$encodedTitleDbl = normalize(utf8_encode(mb_strtolower($tradTitleDbl)));
							}else{
								//Y-a-t-il un sous-titre ?
								$titlePlusDbl = $entDbl->title_s[0];
								if (isset($entDbl->subTitle_s[0])) {
									$titreInitDbl = $titlePlusDbl;
									$titlePlusDbl .= " : ".$entDbl->subTitle_s[0];
								}
								$encodedTitleDbl = normalize(utf8_encode(mb_strtolower(utf8_decode($titlePlusDbl))));
								
								//On récupère le type de document
								$docTEIDbl = $entDbl->docType_s;
								
								//On compare les titres normalisés
								if ($enctitTEI == $encodedTitleDbl) {
									$doublonDbl = "titre";
								}

								//On compare également les DOI s'ils sont présents
								if ($doiTEI != "" && isset($entDbl->doiId_s) && $doiTEI == $entDbl->doiId_s) {
									$docTEIDbl = $entDbl->docType_s;
									if ($doublonDbl == "non") {
										$doublonDbl = "DOI";
									}else{
										$doublonDbl .= " et du DOI";
									}
								}
								
								if ($doublonDbl != "non") {//Doublon trouvé dans la collection > vérification du type
									$txtDbl = " et dans la collection ".$team;
									if ($typTEI == $docTEIDbl) {//Mêmes types de document
										$txtDbl .= " et les types sont identiques";
										$typDbl = "HALCOLLTYP";
									}else{
										$txtDbl .= " mais les types sont différents";
										$typDbl = "HALCOLL";
									}
									//echo $typTEI.' - '.$docTEIDbl.'<br>';
									break 2;//Doublon HALCOLL trouvé > sortie des 2 boucles foreach
								}else{
									if ($typTEI == $docTEIDbl) {//Mêmes types de document
										$txtDbl = " mais pas dans la collection ".$team. " et les types sont identiques";
										$typDbl = "HALTYP";
									}else{
										$txtDbl = " mais pas dans la collection ".$team. " et les types sont différents";
										$typDbl = "HAL";
									}
								}
							}
						}
					}
					$cpt++;
				}
				if ($dbl == 0) {echo('Aucune notice trouvée dans HAL, donc, pas de doublon');}//Notice non trouvée > pas de doublon
				if ($dbl >= 1) {echo('La notice est déjà présente dans HAL'.$txtDbl);}//Présence de doublon(s)

				echo('<script>');
				echo('document.getElementById(\'cpt1\').style.display = \'none\';');
				echo('</script>');
			}
			//Fin étape 1
			
			if (isset($typDbl) && $typDbl != "HALCOLLTYP") {//Pas un doublon de type HAL et COLL
				//Etape 2 - Recherche des idHAL des auteurs				
				echo('<br><br>');
				$cpt = 1;
				$iAut = 0;
				$preAut = array();//Prénoms des auteurs
				$nomAut = array();//Noms des auteurs
				$affAut = array();//Affiliation des auteurs
				$xmlIds = array();//IdHALs trouvés
				$xmlIdi = array();//IdHALi trouvés
				$melAut = array();//Emails trouvés
				$halAut = array();
				$tabIdHAL = array();//Si plusieurs idHAL remontés pour un même auteur
				
				echo('<b>Etape 2 : recherche des idHAL et docid des auteurs</b><br>');
				echo('<div id=\'cpt2\'></div>');
				
				$auts = $xml->getElementsByTagName("author");
				foreach($auts as $aut) {
					//Initialisation des variables
					$xmlIds[$iAut] = "";
					$xmlIdi[$iAut] = "";
					$affAut[$iAut] = "";
					$melAut[$iAut] = "";
					foreach($aut->childNodes as $elt) {
						//Prénom/Nom
						if ($elt->nodeName == "persName") {
							foreach($elt->childNodes as $per) {
								if ($per->nodeName == "forename") {
									$preAut[$iAut] = $per->nodeValue;
								}
								if ($per->nodeName == "surname") {
									$nomAut[$iAut] = $per->nodeValue;
								}
							}
						}
						//IdHAL
						if ($elt->nodeName == "idno") {
							if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "idhal") {
								if ($elt->hasAttribute("notation") && $elt->getAttribute("notation") == "string") {$xmlIds[$iAut] = $elt->nodeValue;}
								if ($elt->hasAttribute("notation") && $elt->getAttribute("notation") == "numeric") {$xmlIdi[$iAut] = $elt->nodeValue;}
							}
						}
						//Email
						if ($elt->nodeName == "email") {
							$melAut[$iAut] = str_replace('@', '', strstr($elt->nodeValue, '@'));
						}
						//Affiliations
						if ($elt->nodeName == "affiliation") {
							if ($elt->hasAttribute("ref")) {$affAut[$iAut] .= $elt->getAttribute("ref").'~';}
						}
					}
					$iAut++;
				}
				//var_dump($preAut);
				//var_dump($nomAut);
				//var_dump($melAut);
				//var_dump($affAut);
				
				$nbAut = $iAut;
				$iAut = 0;
				$cptiHi = 0;//Compteur d'idHal_i trouvé(s)
				$cptdoc = 0;//Compteur de docid trouvé(s)
				
				for($i = 0; $i < count($preAut); $i++) {
					progression($cpt, $nbAut, 'cpt2', $iPro, 'auteur');
					$firstName = $preAut[$i];
					$lastName = $nomAut[$i];
					$affilName = $affAut[$i];
					//Initialisation des variables du tableau
					$halAut[$iAut]['firstName'] = $firstName;
					$halAut[$iAut]['lastName'] = $lastName;
					$halAut[$iAut]['affilName'] = $affilName;
					$halAut[$iAut]['xmlIdi'] = $xmlIdi[$i];
					$halAut[$iAut]['xmlIds'] = $xmlIds[$i];
					$halAut[$iAut]['idHali'] = "";
					$halAut[$iAut]['idHals'] = "";
					$halAut[$iAut]['mailDom'] = $melAut[$i];
					$halAut[$iAut]['docid'] = "";
					$firstNameT = strtolower(wd_remove_accents($firstName));
					$lastNameT = strtolower(wd_remove_accents($lastName));
					$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=firstName_t:(%22".$firstNameT."%22%20OR%20%22".substr($firstNameT, 0, 1)."%22)%20AND%20lastName_t:%22".$lastNameT."%22%20AND%20valid_s:%22VALID%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s";
					$reqAut = str_replace(" ", "%20", $reqAut);
					echo('<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (1ère méthode)</a><br>');
					//echo $reqAut.'<br>';
					$contAut = file_get_contents($reqAut);
					$resAut = json_decode($contAut);
					$numFound = 0;
					if (isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
					$docid = "";
					$nbdocid = 0;
					$iHi = "non";//Test pour savoir si un idHal_i a été trouvé
					$trouve = 0;//Test pour savoir si la 1ère méthode a permis de trouver quelque chose
			
					if ($numFound != 0) {				
						foreach($resAut->response->docs as $author) {
							//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
							$testMel = "oui";//Ok par défaut
							if ($halAut[$iAut]['mailDom'] != "") {
								$melXML = $halAut[$iAut]['mailDom'];
								$tabMelXML = explode(".", $melXML);
								$testXML = $tabMelXML[count($tabMelXML) - 1];
								if (isset($author->emailDomain_s)) {$melHAL = $author->emailDomain_s;}else{$melHAL = "";}
								$tabMelHAL = explode(".", $melHAL);
								$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
								if ($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
								//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
							}
							if (isset($author->idHal_i) && $author->idHal_i != 0 && $author->valid_s == "VALID") {
								if ($testMel == "oui") {
									//echo $firstName.' '.$lastName.' : '.$author->idHal_i.' -> '.$author->idHal_s.' - ';
									$halAut[$iAut]['firstName'] = $firstName;
									$halAut[$iAut]['lastName'] = $lastName;
									$halAut[$iAut]['affilName'] = $affilName;
									if (isset($author->idHal_i)) {$halAut[$iAut]['idHali'] = $author->idHal_i;}else{$halAut[$iAut]['idHali'] = "";}
									if (isset($author->idHal_s)) {$halAut[$iAut]['idHals'] = $author->idHal_s;}else{$halAut[$iAut]['idHals'] = "";}
									if (isset($author->emailDomain_s)) {$halAut[$iAut]['mailDom'] = str_replace('@', '', strstr($author->emailDomain_s, '@'));}else{$halAut[$iAut]['mailDom'] = "";}
									if (isset($author->docid)) {$halAut[$iAut]['docid'] = $author->docid;}
									$iHi = "oui";
									$cptiHi++;
									$trouve++;
									break;
								}
							}else{//Pas d'idHal
								if ($testMel == "oui") {
									$docid .= $author->docid;
									$nbdocid++;
								}
							}
						}
					}
					if($iHi == "non" && $docid != "" && $nbdocid == 1) {//Un seul docid trouvé
						$halAut[$iAut]['firstName'] = $firstName;
						$halAut[$iAut]['lastName'] = $lastName;
						$halAut[$iAut]['affilName'] = $affilName;
						$halAut[$iAut]['idHali'] = "";
						$halAut[$iAut]['idHals'] = "";
						$halAut[$iAut]['mailDom'] = "";
						$halAut[$iAut]['docid'] = $docid;
						$cptdoc++;
						$trouve++;
						//echo($firstName.' '.$lastName.' : '.$docid);
					}
					
					if ($trouve == 0) {
						$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=firstName_t:(%22".$firstNameT."%22%20OR%20%22".substr($firstNameT, 0, 1)."%22)%20AND%20lastName_t:%22".$lastNameT."%22%20AND%20valid_s:(%22OLD%22%20OR%20%22INCOMING%22)&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s&sort=valid_s desc,docid asc";
						$reqAut = str_replace(" ", "%20", $reqAut);
						echo('<a target="_blank" href="'.$reqAut.'">URL requête auteurs HAL (2ème méthode)</a><br>');
						//echo $reqAut.'<br>';
						$contAut = file_get_contents($reqAut);
						$resAut = json_decode($contAut);
						$numFound = 0;
						if (isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
						if ($numFound != 0) {
							$old = "non";
							foreach($resAut->response->docs as $author) {
								//Test sur le domaine des adresses mail s'il y en déjà une dans le XML
								$testMel = "oui";//Ok par défaut
								if ($halAut[$iAut]['mailDom'] != "") {
									$melXML = $halAut[$iAut]['mailDom'];
									$tabMelXML = explode(".", $melXML);
									$testXML = $tabMelXML[count($tabMelXML) - 1];
									if (isset($author->emailDomain_s)) {$melHAL = $author->emailDomain_s;}else{$melHAL = "";}
									$tabMelHAL = explode(".", $melHAL);
									$testHAL = $tabMelHAL[count($tabMelHAL) - 1];
									if ($testXML != $testHAL) {$testMel = "non";}//Les domaines/pays des mails sont différents > ne pas remonter l'idHAL, ni le docid si pas d'idHAL
									//echo $testXML.' - '.$testHAL.' > '.$testMel.'<br>';
								}
								if ($testMel == "oui") {
									//On parcours toutes les formes OLD et si plusieurs résultats, stockage dans un tableau à part en vue de prévenir l'utilisateur lors de l'affichage final
									if ($author->valid_s == "OLD") {
										if ($old == "non") {
											$halAut[$iAut]['firstName'] = $firstName;
											$halAut[$iAut]['lastName'] = $lastName;
											$halAut[$iAut]['affilName'] = $affilName;
											if (isset($author->idHal_i) && $author->idHal_i != 0) {$halAut[$iAut]['idHali'] = $author->idHal_i; $cptiHi++;}else{$halAut[$iAut]['idHali'] = "";}
											if (isset($author->idHal_s)) {$halAut[$iAut]['idHals'] = $author->idHal_s;}else{$halAut[$iAut]['idHals'] = "";}
											if (isset($author->emailDomain_s)) {$halAut[$iAut]['mailDom'] = str_replace('@', '', strstr($author->emailDomain_s, '@'));}else{$halAut[$iAut]['mailDom'] = "";}
											if (isset($author->docid)) {$halAut[$iAut]['docid'] = $author->docid;}
											$cptdoc++;
											$old = "oui";
										}else{
											$nbCel = count($tabIdHAL);
											$tabIdHAL[$nbCel]['firstName'] = $firstName;
											$tabIdHAL[$nbCel]['lastName'] = $lastName;
											$tabIdHAL[$nbCel]['reqAut'] = $reqAut;
											break;
										}
									}else{//Forme INCOMING
										$halAut[$iAut]['firstName'] = $firstName;
										$halAut[$iAut]['lastName'] = $lastName;
										$halAut[$iAut]['affilName'] = $affilName;
										if (isset($author->idHal_i) && $author->idHal_i != 0) {$halAut[$iAut]['idHali'] = $author->idHal_i; $cptiHi++;}else{$halAut[$iAut]['idHali'] = "";}
										if (isset($author->idHal_s)) {$halAut[$iAut]['idHals'] = $author->idHal_s;}else{$halAut[$iAut]['idHals'] = "";}
										if (isset($author->emailDomain_s)) {$halAut[$iAut]['mailDom'] = str_replace('@', '', strstr($author->emailDomain_s, '@'));}else{$halAut[$iAut]['mailDom'] = "";}
										if (isset($author->docid)) {$halAut[$iAut]['docid'] = $author->docid;}
										$cptdoc++;
										break;//On ne prend en compte que la 1ère forme INCOMING trouvée
									}
								}
							}
						}
					}
					
					$iAut++;
					//echo ('<br>');
					$cpt++;
				}
				
				//var_dump($halAut);
				$halAutinit = $halAut;//Sauvegarde des affiliations et idHal initiaux remontées par OverHAL
				echo($cptiHi. ' idHal et '.$cptdoc.' docid trouvé(s)');
				
				echo('<script>');
				echo('document.getElementById(\'cpt2\').style.display = \'none\';');
				echo('</script>');
				//Fin étape 2
				

				
				//Etape 3a - Recherche des id structure des affiliations
				echo('<br><br>');
				$cpt = 1;
				$iAff = 0;
				$nomAff = array();//Code initial des affiliations (à parir du XML)
				$halAff = array();
				$anepasTester = array('UMR', 'UMS', 'UPR', 'ERL', 'IFR', 'UR', 'USR', 'USC', 'CIC', 'CIC-P', 'CIC-IT', 'FRE', 'EA', 'INSERM', 'U', 'CHU', 'CNRS', 'INRA', 'CIRAD', 'INRAE', 'IRSTEA', 'CEA');
				//$affdejaTestee = array();//Tableau des affiliations déjà testées et résultat obtenu pour éviter de refaire des tests

				
				echo('<b>Etape 3a : recherche des id structures des affiliations</b><br>');
				echo('<div id=\'cpt3a\'></div>');
				
				$cptAff = 0;
				
				//Affiliations
				$affs = $xml->getElementsByTagName("org");
				foreach($affs as $aff) {
					if ($aff) {
						if ($aff->hasAttribute("xml:id")) {$nomAff[$iAff]['lsAff'] = '#'.$aff->getAttribute("xml:id").'~';}
						if ($aff->hasAttribute("type")) {$nomAff[$iAff]['type'] = $aff->getAttribute("type");}
						$cptAff++;
					}
					foreach($aff->childNodes as $elt) {
						if ($elt->nodeName == "orgName") {
							$nomAff[$iAff]['org'] = $elt->nodeValue;
						}
					}
					$iAff++;
				}
				//var_dump($nomAff);
				
				$nbAff = $iAff;
				$iAff = 0;//Servira aussi comme compteur d'id structures des affiliations trouvé(s)
				
				for($i = 0; $i < count($nomAff); $i++) {
					progression($cpt, $nbAff, 'cpt3a', $iPro, 'affiliation');
					$code = $nomAff[$i]['org'];
					$type = $nomAff[$i]['type'];
					$trouve = 0;//Test pour savoir si la 1ère méthode a permis de trouver un id de structure
					//Si présence d'un terme entre crochets, il faut isoler ce terme et l'ajouter comme recherche prioritaire > ajout au début du tableau
					$crochet = "";
					if (strpos($code, "[") !== false && strpos($code, "]") !== false) {
						$tabCro = explode("[", $code);
						$croTab = explode("]", $tabCro[1]);
						$crochet = $croTab[0];
					}
					$code = str_replace(array("[", "]", "&", "="), array("", "", "", "%3D"), $code);
					
					//Suppression du terme 'Univ'
					$code = str_ireplace(array("Univ ", "Univ. ", "Univ, ", "Univ., "), array("", "", ",", ","), $code);
					
					//1ère méthode, sur le référentiel des structures et uniquement sur l'acronyme

					//Si présence de virgules > test sur chacun des éléments sauf le dernier qui correspond au pays
					//Mais, si pas de virgule, il faut naturellement conserver le dernier élément > $cptCode = 0 ou 1
					if (strpos($code, ",") !== false) {$cptCode = 1;}else{$cptCode = 0;}
					$tabCode = explode(",", $code);
					if ($crochet != "") {array_unshift($tabCode, $crochet);}
					foreach($tabCode as $test) {
						$test = str_replace(" ", "+", trim($test));
						if ($cptCode < count($tabCode) && !in_array($test, $anepasTester)) {
							$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=acronym_t:".$test."%20OR%20acronym_sci:".$test."%20AND%20valid_s:(VALID%20OR%20OLD)&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s%20desc,docid%20asc";
							$reqAff = str_replace(" ", "%20", $reqAff);
							echo('<a target="_blank" href="'.$reqAff.'">URL requête affiliations (1ère méthode) HAL</a><br>');
							//echo $reqAff.'<br>';
							$contAff = file_get_contents($reqAff);
							$resAff = json_decode($contAff);
							if (isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
							if ($numFound != 0) {			
								//foreach($resAff->response->docs as $affil) { > Non, on ne prend que la première affiliation trouvée
									$halAff[$iAff]['docid'] = $resAff->response->docs[0]->docid;
									$halAff[$iAff]['lsAff'] = $nomAff[$i]['lsAff'];
									$halAff[$iAff]['valid'] = $resAff->response->docs[0]->valid_s;
									$halAff[$iAff]['names'] = $resAff->response->docs[0]->name_s;
									if (isset($resAff->response->docs[0]->acronym_s)) {$acronym = " [".$resAff->response->docs[0]->acronym_s."], ";}else{$acronym = ", ";}
									if (isset($resAff->response->docs[0]->country_s)) {$country = ", ".$resAff->response->docs[0]->country_s;}else{$country = "";}
									$halAff[$iAff]['ncplt'] = $resAff->response->docs[0]->docid." ~ ".$resAff->response->docs[0]->name_s.$acronym.$resAff->response->docs[0]->type_s.$country;
									$halAff[$iAff]['fname'] = "";
									$halAff[$iAff]['lname'] = "";
									$iAff++;
									$trouve++;
									break;
								//}
							}
						}
						$cptCode++;
					}
					
					if ($trouve == 0) {
						//2ème méthode > avec le référentiel HAL des structures
						
						//Si présence de virgules > test sur chacun des éléments sauf le dernier qui correspond au pays
						//Mais, si pas de virgule, il faut naturellement conserver le dernier élément > $cptCode = 0 ou 1
						if (strpos($code, ",") !== false) {$cptCode = 1;}else{$cptCode = 0;}
						$tabCode = explode(",", $code);
						foreach($tabCode as $test) {
							$test = str_replace(" ", "+", trim($test));
							if ($cptCode < count($tabCode) && !in_array($test, $anepasTester)) {
								$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=(name_t:".$test."%20OR%20code_t:".$test."%20OR%20acronym_t:".$test.")%20AND%20type_s:".$type."%20AND%20valid_s:(VALID%20OR%20OLD)&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s desc,docid asc";
								$reqAff = str_replace(" ", "%20", $reqAff);
								echo('<a target="_blank" href="'.$reqAff.'">URL requête affiliations (2ème méthode) HAL</a><br>');
								//echo $reqAff.'<br>';
								$contAff = file_get_contents($reqAff);
								$resAff = json_decode($contAff);
								if (isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
								if ($numFound != 0) {			
									//foreach($resAff->response->docs as $affil) { > Non, on ne prend que la première affiliation trouvée
										$halAff[$iAff]['docid'] = $resAff->response->docs[0]->docid;
										$halAff[$iAff]['lsAff'] = $nomAff[$i]['lsAff'];
										$halAff[$iAff]['valid'] = $resAff->response->docs[0]->valid_s;
										$halAff[$iAff]['names'] = $resAff->response->docs[0]->name_s;
										if (isset($resAff->response->docs[0]->acronym_s)) {$acronym = " [".$resAff->response->docs[0]->acronym_s."], ";}else{$acronym = ", ";}
										if (isset($resAff->response->docs[0]->country_s)) {$country = ", ".$resAff->response->docs[0]->country_s;}else{$country = "";}
										$halAff[$iAff]['ncplt'] = $resAff->response->docs[0]->docid." ~ ".$resAff->response->docs[0]->name_s.$acronym.$resAff->response->docs[0]->type_s.$country;
										$halAff[$iAff]['fname'] = "";
										$halAff[$iAff]['lname'] = "";
										$iAff++;
										$trouve++;
										break;
									//}
								}
							}
							$cptCode++;
						}
					}
					
					//3ème méthode, toujours sur le référentiel des structures mais avec une autre requête
					if ($trouve == 0) {
						//Si présence de virgules > test sur chacun des éléments sauf le dernier qui correspond au pays
						//Mais, si pas de virgule, il faut naturellement conserver le dernier élément > $cptCode = 0 ou 1
						if (strpos($code, ",") !== false) {$cptCode = 1;}else{$cptCode = 0;}
						$tabCode = explode(",", $code);
						foreach($tabCode as $test) {
							$test = str_replace(" ", "+", trim($test));
							if ($cptCode < count($tabCode) && !in_array($test, $anepasTester)) {
								//$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=(name_t:%22".$test."%22%20OR%20name_t:(".$test.")%20OR%20code_t:%22".$test."%22%20OR%20acronym_t:%22".$test."%22%20OR%20acronym_sci:%22".$test."%22)%20AND%20type_s:".$type."%20AND%20valid_s:(VALID%20OR%20OLD)&fl=docid,valid_s,name_s,type_s&sort=valid_s%20desc,docid%20asc";
								$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=(name_t:%22".$test."%22%20OR%20name_t:(".$test.")%20OR%20code_t:%22".$test."%22%20OR%20acronym_t:%22".$test."%22%20OR%20acronym_sci:%22".$test."%22)%20AND%20valid_s:(VALID%20OR%20OLD)&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s%20desc,docid%20asc";
								$reqAff = str_replace(" ", "%20", $reqAff);
								echo('<a target="_blank" href="'.$reqAff.'">URL requête affiliations (3ème méthode) HAL</a><br>');
								//echo $reqAff.'<br>';
								$contAff = file_get_contents($reqAff);
								$resAff = json_decode($contAff);
								if (isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
								if ($numFound != 0) {			
									//foreach($resAff->response->docs as $affil) { > Non, on ne prend que la première affiliation trouvée
										$halAff[$iAff]['docid'] = $resAff->response->docs[0]->docid;
										$halAff[$iAff]['lsAff'] = $nomAff[$i]['lsAff'];
										$halAff[$iAff]['valid'] = $resAff->response->docs[0]->valid_s;
										$halAff[$iAff]['names'] = $resAff->response->docs[0]->name_s;
										if (isset($resAff->response->docs[0]->acronym_s)) {$acronym = " [".$resAff->response->docs[0]->acronym_s."], ";}else{$acronym = ", ";}
										if (isset($resAff->response->docs[0]->country_s)) {$country = ", ".$resAff->response->docs[0]->country_s;}else{$country = "";}
										$halAff[$iAff]['ncplt'] = $resAff->response->docs[0]->docid." ~ ".$resAff->response->docs[0]->name_s.$acronym.$resAff->response->docs[0]->type_s.$country;
										$halAff[$iAff]['fname'] = "";
										$halAff[$iAff]['lname'] = "";
										$iAff++;
										$trouve++;
										break;
									//}
								}
							}
							$cptCode++;
						}
					}
						
					//4ème méthode, si les 3 précédentes n'ont pas abouti > avec le référentiel HAL des notices
					if ($trouve == 0) {
						//On récupère tout d'abord l'année de la publication
						$annee = "";
						$anns = $xml->getElementsByTagName("date");
						foreach($anns as $ann) {
							if ($ann->hasAttribute("type") && $ann->getAttribute("type") == "datePub") {$annee = $ann->nodeValue;}
						}
						if ($annee != "") {
							for($j = 0; $j < count($halAut); $j++) {
								if ($halAut[$j]['affilName'] == $nomAff[$i]['lsAff']) {//On ne s'intéresse qu'aux auteurs concernés par cette référence d'affiliation
									$firstName = $halAut[$j]['firstName'];
									$lastName = $halAut[$j]['lastName'];
									$facetSep = $lastName.' '.$firstName;
									$reqAff = "https://api.archives-ouvertes.fr/search/index/?q=authLastName_sci:%22".$lastName."%22%20AND%20authFirstName_sci:%22".$firstName."%22&fq=-labStructValid_s:INCOMING%20OR%20(structAcronym_sci:%22".$code."%22%20OR%20structName_sci:%22u1085%22%20OR%20structCode_sci:%22".$code."%22)&fl=structPrimaryHasAlphaAuthIdHal_fs,authId_i,authLastName_s,authFirstName_s&sort=abs(sub(producedDateY_i,".$annee."))%20asc";
									$reqAff = str_replace(" ", "%20", $reqAff);
									echo('<a target="_blank" href="'.$reqAff.'">URL requête affiliations (4ème méthode) HAL</a><br>');
									//echo $reqAff.'<br>';
									$contAff = file_get_contents($reqAff);
									$resAff = json_decode($contAff);
									if (isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
									if ($numFound != 0) {
										foreach($resAff->response->docs as $affil) {
											foreach($affil->structPrimaryHasAlphaAuthIdHal_fs as $fSep) {
												if (strpos($fSep, $facetSep) !== false) {
													$fSepTab = explode('_', $fSep);
													$ajout = "oui";
													for($k = 0; $k < count($halAff); $k++) {
														if (intval($fSepTab[2]) == $halAff[$k]['docid'] && $firstName == $halAff[$k]['fname'] && $lastName == $halAff[$k]['lname']) {$ajout = "non";}
													}
													if ($ajout == "oui") {
														//VALID ou OLD ?
														$reqVoO = "https://api.archives-ouvertes.fr/ref/structure/?q=docid:%22".$fSepTab[2]."%22%20AND%20-valid_s:%22INCOMING%22&fl=*&rows=1000&fl=docid,valid_s,name_s,type_s,country_s,acronym_s";
														$reqVoO = str_replace(" ", "%20", $reqVoO);
														$contVoO = file_get_contents($reqVoO);
														$resVoO = json_decode($contVoO);
														$halAff[$iAff]['docid'] = intval($fSepTab[2]);
														$halAff[$iAff]['lsAff'] = $nomAff[$i]['lsAff'];
														$halAff[$iAff]['valid'] = $resVoO->response->docs[0]->valid_s;
														$halAff[$iAff]['names'] = $fSepTab[4];
														if (isset($resVoO->response->docs[0]->acronym_s)) {$acronym = " [".$resVoO->response->docs[0]->acronym_s."], ";}else{$acronym = ", ";}
														if (isset($resVoO->response->docs[0]->country_s)) {$country = ", ".$resVoO->response->docs[0]->country_s;}else{$country = "";}
														$halAff[$iAff]['ncplt'] = $resVoO->response->docs[0]->docid." ~ ".$resVoO->response->docs[0]->name_s.$acronym.$resVoO->response->docs[0]->type_s.$country;
														$halAff[$iAff]['fname'] = $firstName;
														$halAff[$iAff]['lname'] = $lastName;
														$iAff++;
														$trouve++;
														break 2;
													}
												}
											}
										}
									}
								}
							}
						}
					}

					if ($trouve == 0) {
						//Affiliation sans recherche possible > on réinitialise cette affiliation pour les auteurs concernés
						for($j = 0; $j < count($halAut); $j++) {
							if ($halAut[$j]['affilName'] != "" && stripos($halAut[$j]['affilName'], $nomAff[$i]['lsAff']) !== false) {
								$halAut[$j]['affilName'] = str_replace($nomAff[$i]['lsAff'], '', $halAut[$j]['affilName']);
							}
						}
					}
					$cpt++;
				}

				echo($iAff.' id structures des affiliations trouvé(s)');

				echo('<script>');
				echo('document.getElementById(\'cpt3a\').style.display = \'none\';');
				echo('</script>');
				//Fin étape 3a
				
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
					if ($halAut[$i]['affilName'] == "") {$nbAutnoaff++;}
				}
					
				for($i = 0; $i < count($halAut); $i++) {
					if ($halAut[$i]['affilName'] == "") {
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
						if (isset($resAut->response->result->org->orgName)) {//Un seul résultat
							if (is_array($resAut->response->result->org->orgName)) {
								$orgName = $resAut->response->result->org->orgName[0];
							}else{
								$orgName = $resAut->response->result->org->orgName;
							}
							$orgName = str_replace(array("[", "]", "&", "="), array("%5B", "%5D", "%26", "%3D"), $orgName);
							//Est-ce une affiliation 'longue' (avec beaucoup de virgules) ou 'courte' ?
							//if (substr_count($orgName, ',') > 2) {$loncou = "longue";}else{$loncou = "courte";}								
							$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=%22".$orgName."%22%20AND%20valid_s:(VALID%20OR%20OLD)&fl=docid,valid_s,name_s,type_s,country_s,acronym_s&sort=valid_s desc,docid asc";
							$reqAff = str_replace(" ", "%20", $reqAff);
							echo('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a target="_blank" href="'.$reqAff.'">URL requête test validité affiliation trouvée</a><br>');
							//echo $reqAff.'<br>';
							$contAff = file_get_contents($reqAff);
							$resAff = json_decode($contAff);
							$docid = "non";
							if (isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
							if ($numFound != 0) {			
								foreach($resAff->response->docs as $affil) {
									if (($affil->valid_s == "VALID" || $affil->valid_s == "OLD") && $docid == "non") {
										$halAff[$iAff]['docid'] = $affil->docid;
										$cptNoaff++;
										$cptAff++;
										$halAff[$iAff]['lsAff'] = "#localStruct-Aff".$cptAff."~";
										$halAff[$iAff]['valid'] = $affil->valid_s;
										$halAff[$iAff]['names'] = $affil->name_s;
										if (isset($affil->acronym_s)) {$acronym = " [".$affil->acronym_s."], ";}else{$acronym = ", ";}
										if (isset($affil->country_s)) {$country = ", ".$affil->country_s;}else{$country = "";}
										$halAff[$iAff]['ncplt'] = $affil->docid." ~ ".$affil->name_s.$acronym.$affil->type_s.$country;
										$halAff[$iAff]['fname'] = $halAut[$i]['firstName'];
										$halAff[$iAff]['lname'] = $halAut[$i]['lastName'];
										$halAut[$i]['affilName'] .= "#localStruct-Aff".$cptAff."~";
										$iAff++;
										$docid = "oui";
										//Pour les affiliations courtes, on ne prend que le premier résultat remonté
										//if ($loncou == "courte") {break 2;}
									}
								}
								
								//if ($docid == "non") {//pas de docid trouvé avec VALID ou OLD > on teste avec INCOMING
								//	foreach($resAff->response->docs as $affil) {
								//		if ($affil->valid_s == "INCOMING"  && $docid == "non") {
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
								if (isset($resAut->response->result->org[$org]->orgName)) {
									if (is_array($resAut->response->result->org[$org]->orgName)) {
										$orgName = $resAut->response->result->org[$org]->orgName[0];
									}else{
										$orgName = $resAut->response->result->org[$org]->orgName;
									}
									$orgName = str_replace(array("[", "]", "&", "="), array("%5B", "%5D", "%26", "%3D"), $orgName);
									//Est-ce une affiliation 'longue' (avec beaucoup de virgules) ou 'courte' ?
									//if (substr_count($orgName, ',') > 2) {$loncou = "longue";}else{$loncou = "courte";}				
									$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=%22".$orgName."%22%20AND%20valid_s:(VALID%20OR%20OLD)&fl=docid,valid_s,name_s,type_s&sort=valid_s desc,docid asc";
									$reqAff = str_replace(" ", "%20", $reqAff);
									echo('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a target="_blank" href="'.$reqAff.'">URL requête test validité affiliation trouvée</a><br>');
									//echo $reqAff.'<br>';
									$contAff = file_get_contents($reqAff);
									$resAff = json_decode($contAff);
									$docid = "non";
									if (isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
									if ($numFound != 0) {			
										foreach($resAff->response->docs as $affil) {
											if (($affil->valid_s == "VALID" || $affil->valid_s == "OLD") && $docid == "non") {
												$halAff[$iAff]['docid'] = $affil->docid;
												$cptNoaff++;
												$cptAff++;
												$halAff[$iAff]['lsAff'] = "#localStruct-Aff".$cptAff."~";
												$halAff[$iAff]['valid'] = $affil->valid_s;
												$halAff[$iAff]['names'] = $affil->name_s;
												if (isset($affil->acronym_s)) {$acronym = " [".$affil->acronym_s."], ";}else{$acronym = ", ";}
												if (isset($affil->country_s)) {$country = ", ".$affil->country_s;}else{$country = "";}
												$halAff[$iAff]['ncplt'] = $affil->docid." ~ ".$affil->name_s.$acronym.$affil->type_s.$country;
												$halAff[$iAff]['fname'] = $halAut[$i]['firstName'];
												$halAff[$iAff]['lname'] = $halAut[$i]['lastName'];
												$halAut[$i]['affilName'] .= "#localStruct-Aff".$cptAff."~";
												$iAff++;
												$docid = "oui";
												//Pour les affiliations courtes, on ne prend que le premier résultat remonté
												//if ($loncou == "courte") {break 2;}
											}
										}
										
										//if ($docid == "non") {//pas de docid trouvé avec VALID ou OLD > on teste avec INCOMING
										//	foreach($resAff->response->docs as $affil) {
										//		if ($affil->valid_s == "INCOMING"  && $docid == "non") {
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
				
				//Etape 3c - Recherche id auteur grâce à l'affiliation éventuellement trouvée
				echo('<br><br>');
				$cpt = 1;
				$cptId = 0;
				$year = date('Y', time());
				
				echo('<b>Etape 3c : recherche des docid auteur grâce aux affiliations éventuellement trouvées</b><br>');
				echo('<div id=\'cpt3c\'></div>');
				
				for($i = 0; $i < count($halAut); $i++) {
					progression($cpt, count($halAut), 'cpt3c', $iPro, 'auteur');
					if ($halAut[$i]['docid'] == "") {//Pas d'id auteur
						for($j = 0; $j < count($halAff); $j++) {
							//if ($halAff[$j]['fname'] == $halAut[$i]['firstName'] && $halAff[$j]['lname'] == $halAut[$i]['lastName']) {
							if (strpos($halAut[$i]['affilName'], $halAff[$j]['lsAff']) !== false) {
								$affil = $halAff[$j]['names'];
								$afill = str_replace("&", "%24", $affil);
								$reqId = "https://api.archives-ouvertes.fr/search/index/?q=authLastName_sci:%22".$halAut[$i]['lastName']."%22%20AND%20authFirstName_sci:%22".$halAut[$i]['firstName']."%22&fq=(structAcronym_sci:%22".$affil."%22%20OR%20structName_sci:%22".$affil."%22%20OR%20structCode_sci:%22".$affil."%22)&fl=authIdLastNameFirstName_fs&sort=abs(sub(producedDateY_i,".$year."))%20asc";
								$reqId = str_replace(" ", "%20", $reqId);
								echo('<a target="_blank" href="'.$reqId.'">URL requête docid HAL</a><br>');
								//echo $reqId.'<br>';
								$contId = file_get_contents($reqId);
								$resId = json_decode($contId);
								$numFound = 0;
								if (isset($resId->response->numFound)) {$numFound = $resId->response->numFound;}
								if ($numFound != 0) {
									$tests = $resId->response->docs[0]->authIdLastNameFirstName_fs;
									foreach($tests as $test) {
										if ((strpos($test, ($halAut[$i]['firstName'])) !== false || strpos($test, (substr($halAut[$i]['firstName'], 0, 1))) !== false) && strpos($test, $halAut[$i]['lastName']) !== false) {
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
				
				//Etape 3d - Recherche idHAL auteur grâce aux docid auteur trouvés précédemment
				echo('<br><br>');
				$cpt = 1;
				$cptId = 0;
				
				echo('<b>Etape 3d : recherche des idHAL auteur grâce aux docid auteur trouvés précédemment</b><br>');
				echo('<div id=\'cpt3d\'></div>');
				
				for($i = 0; $i < count($halAut); $i++) {
					progression($cpt, count($halAut), 'cpt3d', $iPro, 'auteur');
					if ($halAut[$i]['docid'] != "" && $halAut[$i]['idHals'] == "") {//L'auteur a bien un docid mais pas d'idHAL
						$reqId = "https://api.archives-ouvertes.fr/ref/author/?q=docid:".$halAut[$i]['docid']."%20AND%20valid_s:(VALID%20OR%20OLD)&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s";
						$reqId = str_replace(" ", "%20", $reqId);
						echo('<a target="_blank" href="'.$reqId.'">URL requête idHAL auteur</a><br>');
						$contId = file_get_contents($reqId);
						$resId = json_decode($contId);
						$numFound = 0;
						if (isset($resId->response->numFound)) {$numFound = $resId->response->numFound;}
						if ($numFound != 0) {
							$halAut[$i]['idHali'] = $resId->response->docs[0]->idHal_i;
							$halAut[$i]['idHals'] = $resId->response->docs[0]->idHal_s;
							if (isset($resId->response->docs[0]->emailDomain_s)) {$halAut[$i]['mailDom'] = str_replace('@', '', strstr($resId->response->docs[0]->emailDomain_s, '@'));}
							$cptId++;
							break;
						}
					}
					$cpt++;
				}
				
				echo($cptId.' idHAL auteur trouvé(s)');
				
				echo('<script>');
				echo('document.getElementById(\'cpt3d\').style.display = \'none\';');
				echo('</script>');
				//Fin étape 3d
				
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


				//Premières modifications du TEI avec les résultats précédemment obtenus
			
				//Ajout du code collection
				insertNode($xml, "", "seriesStmt", "", 0, "idno", "type", "stamp", "n", $team, "aC", "amont", "");
				$xml->save($nomfic);
				
				//Ajout du domaine
				if ($domaine != "") {
					$tabDom = explode(" ~ ", str_replace("’", "'", $domaine));
					insertNode($xml, $tabDom[0], "textClass", "classCode", 0, "classCode", "scheme", "halDomain", "n", $tabDom[1], "aC", "amont", "");
					$xml->save($nomfic);
				}

				//Ajout des IdHAL et/ou docid
				$auts = $xml->getElementsByTagName("author");
				foreach($auts as $aut) {
					//Initialisation des variables
					$fname = "";//Prénom
					$lname = "";//Nom
					$listIdHAL = "~";//Variable pour assurer l'univité de l'insertion des IdHAL
					$listdocid = "~";//Variable pour assurer l'univité de l'insertion des docid
					foreach($aut->childNodes as $elt) {
						//Prénom/Nom
						if ($elt->nodeName == "persName") {
							foreach($elt->childNodes as $per) {
								if ($per->nodeName == "forename") {
									$fname = $per->nodeValue;
								}
								if ($per->nodeName == "surname") {
									$lname = $per->nodeValue;
								}
							}
						}
						
						//Ajouts divers
						for($i = 0; $i < count($halAut); $i++) {
							if ($halAut[$i]['firstName'] == $fname && $halAut[$i]['lastName'] == $lname) {
								//Y-a-t-il un IdHAL ?
								if ($halAut[$i]['idHals'] != "" && strpos($listIdHAL, $halAut[$i]['idHals']) === false) {
									insertNode($xml, $halAut[$i]['idHali'], "author", "affiliation", $i, "idno", "type", "idhal", "notation", "numeric", "iB", "amont", "");	
									insertNode($xml, $halAut[$i]['idHals'], "author", "idno", $i, "idno", "type", "idhal", "notation", "string", "iB", "amont", "");
									$listIdHAL .= $halAut[$i]['idHals'].'~';
								}
								//Y-a-t-il un docid ?
								if ($halAut[$i]['docid'] != "" && strpos($listdocid, $halAut[$i]['docid']) === false) {
									insertNode($xml, $halAut[$i]['docid'], "author", "affiliation", $i, "idno", "type", "halauthorid", "", "", "iB", "amont", "");
									$listdocid .= $halAut[$i]['docid'].'~';
								}
								//Id structures des affiliations
								//Recherche des affiliations remontées globalement sur la base du nom de l'organisme, quel que soit l'auteur mais sous réserve du rattachement de l'auteur à cette affiliation (ex : U1085)
								for($j = 0; $j < count($halAff); $j++) {
									if ($halAff[$j]['fname'] == "" && $halAff[$j]['lname'] == "" && (strpos($halAut[$i]['affilName'], $halAff[$j]['lsAff']) !== false)) {
										$lsAff = $halAff[$j]['lsAff'];
										deleteNode($xml, "author", "affiliation", $i, "ref", $lsAff, "", "", "approx");
										//Puis on ajoute l'(les) affiliation(s) trouvée(s)
										$affil = "#struct-".$halAff[$j]['docid'];
										insertNode($xml, "nonodevalue", "author", "persName", $i, "affiliation", "ref", $affil, "", "", "aC", "amont", "");
									}
								}
								//Recherche des affiliations remontées pour chaque auteur
								for($j = 0; $j < count($halAff); $j++) {
									if ($halAff[$j]['fname'] == $fname && $halAff[$j]['lname'] == $lname) {
										//Au moins une affiliation trouvée > On supprime l'affiliation correspondante du TEI de type '<affiliation ref="#localStruct-Affx"/>' pour cet auteur
										$lsAff = $halAff[$j]['lsAff'];
										deleteNode($xml, "author", "affiliation", $i, "ref", $lsAff, "", "", "approx");
										//Puis on ajoute l'(les) affiliation(s) trouvée(s)
										$affil = "#struct-".$halAff[$j]['docid'];
										insertNode($xml, "nonodevalue", "author", "persName", $i, "affiliation", "ref", $affil, "", "", "aC", "amont", "");
									}
								}
								
								$xml->save($nomfic);
							}
						}
						/*
						//Y-a-t-il un docid ?
						for($i = 0; $i < count($halAut); $i++) {
							if ($halAut[$i]['firstName'] == $fname && $halAut[$i]['lastName'] == $lname) {
								if ($halAut[$i]['docid'] != "" && strpos($listdocid, $halAut[$i]['docid']) === false) {
									insertNode($xml, $halAut[$i]['docid'], "author", "affiliation", $i, "idno", "type", "halauthorid", "", "", "iB");
									$xml->save($nomfic);
									$listdocid .= $halAut[$i]['docid'].'~';
								}
							}
						}
						*/
					}
				}

				//Fin des premières modifications du TEI
			}
			
			
			
			//Tableau des résultats
			echo('<br><br>');
			
			echo('<b>Tableau des résultats et éventuelle validation finale du TEI pour importation dans HAL</b><br>');
			//echo('Fichier '.$nomfic.'<br>');
			echo('<table class=\'table table-striped table-bordered table-hover;\'>');
			echo('<tr>');
			echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>ID</b></td>');
			echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Doublon</b></td>');
			echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Supprimer</b></td>');
			echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Type de document</b></td>');
			echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Métadonnées</b></td>');
			echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>DOI</b></td>');
			echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Auteurs / affiliations</b></td>');
			echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Validation du TEI modifié</td>');
			echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Importer dans HAL</b></td>');
			echo('</tr>');
			
			$cpt = 1;
			
			echo('<tr style=\'text-align: center;\'>');
			
			//Numérotation > id
			echo('<td>'.$cpt.'</td>');
			
			//Doublon ?
			if (isset($typDbl) && $typDbl != "") {
				echo('<td><a target=\'_blank\' href=\'https://hal.archives-ouvertes.fr/'.$idTEI.'\'><img alt=\'HAL\' src=\'./img/HAL.jpg\'></a></td>');
			}else{
				echo('<td>&nbsp;</td>');
			}
			
			//Supprimer le TEI
			echo('<td><span id=\'suppression'.'-'.$idFic.'\'><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'suppression\', valeur : \''.$idFic.'\',}); majokSuppr(\'suppression'.'-'.$idFic.'\');"><img alt=\'Supprimer le TEI\' src=\'./img/supprimer.jpg\'></a></span>');
			
			//Type de document
			$typDoc = "";
			$elts = $xml->getElementsByTagName("classCode");
			foreach($elts as $elt) {
				if ($elt->hasAttribute("scheme") && $elt->getAttribute("scheme") == "halTypology") {$typDoc = $elt->getAttribute("n");}
			}
			echo('<td>'.$typDoc.'</td>');
			
			if (isset($typDbl) && $typDbl == "HALCOLLTYP") {//Doublon de type HAL et COLL > inutile d'afficher les métadonnées
				echo('<td>&nbsp;</td>');
			}else{
				//Métadonnées
				echo('<td style=\'text-align: left;\'><span id=\'metadonnees-'.$idFic.'\'>');
				
				//Domaine
				$domOK = "non";
				$elts = $xml->getElementsByTagName("classCode");
				foreach($elts as $elt) {
					if ($domOK == "non") {
						echo('<p class="form-inline">Domaine : <input type="text" id="domaine-'.$idFic.'" name="domaine-'.$idFic.'" value="'.$domaine.'" class="form-control" style="height: 18px; width:400px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'domaine\', valeur: $(this).val()});"></p>');
						$domOK = "oui";
					}
				}
				echo('Pour modifier le domaine, utilisez le champ ou l\'arborescence dynamique ci-dessous :');
				echo ('<span id="choixdom-'.$idFic.'">');
				echo ('<br>');
				echo ('<p class="form-inline"><input type="text" id ="inputdom-'.$idFic.'" name="inputdom-'.$idFic.'" class="autoDO form-control" style="margin-left: 30px; height: 18px; width:300px">');
				echo ('&nbsp;<b>+</b>&nbsp;<a style="cursor:pointer;" onclick=\'document.getElementById("domaine-'.$idFic.'").value=$("#inputdom-'.$idFic.'").val(); $.post("Zip2HAL_liste_actions.php", {nomfic : "'.$nomfic.'", action: "domaine", valeur: $("#inputdom-'.$idFic.'").val()});\'><img width=\'12px\' alt=\'Valider le domaine\' src=\'./img/done.png\'></a></p>');

				$codI = "";
				$cpt = 1;
				$reqAPI = "https://api.archives-ouvertes.fr/ref/domain/?q=*:*&fl=code_s,fr_domain_s&rows=500&sort=code_s%20ASC";
				$contents = file_get_contents($reqAPI);
				$results = json_decode($contents);
				foreach($results->response->docs as $entry) {
					$code = $entry->code_s;
					$tabCode = explode(".", $code);
					$codF = $tabCode[0];
					if ($codI != $codF) {//Nouveau groupe de disciplines
						if ($cpt != 1) {echo ('</span>');}
						$domF = str_replace("'", "’", $entry->fr_domain_s);
						echo ('<span style=\'margin-left: 30px;\' id=\'cod-'.$cpt.'-'.$idFic.'\'><a style=\'cursor:pointer;\' onclick=\'afficacher('.$cpt.', '.$idFic.');\';><font style=\'color: #FE6D02;\'><b>>&nbsp;</b></font></a></span>');
						echo ('<span><a style=\'cursor:pointer;\' onclick=\'document.getElementById("domaine-'.$idFic.'").value="'.$domF.' ~ '.$code.'"; $.post("Zip2HAL_liste_actions.php", {nomfic : "'.$nomfic.'", action: "domaine", valeur: "'.$domF.' ~ '.$code.'"});\'>'.$domF.'</a></span><br>');
						$codI = $codF;
						echo ('<span id=\'dom-'.$cpt.'-'.$idFic.'\' style=\'display:none;\'>');
						$cpt++;
					}else{//Liste des différentes sous-matières de la discipline
						$sMat = str_replace($domF.'/', '', str_replace("'", "’", $entry->fr_domain_s));
						$sMatVal = str_replace("'", "’", $entry->fr_domain_s);
						//$sMatTab = explode("/", $entry->fr_domain_s);
						//$num = count($sMatTab) - 1;
						//$sMatVal = $sMatTab[$num];
						$code = $entry->code_s;
						echo ('<span style=\'margin-left: 60px;\'><a style=\'cursor:pointer;\' onclick=\'document.getElementById("domaine-'.$idFic.'").value="'.$sMatVal.' ~ '.$code.'"; $.post("Zip2HAL_liste_actions.php", {nomfic : "'.$nomfic.'", action: "domaine", valeur: "'.$sMatVal.' ~ '.$code.'"});\'>'.$sMat.'</a></span><br>');
					}
				}
				echo ('</span><br>');
				
				//Métadonnées > Titre
				$titreOK = "non";
				$elts = $xml->getElementsByTagName("language");
				foreach($elts as $elt) {
					if ($elt->hasAttribute("ident")) {$lang = $elt->nodeValue;}else{$lang = "";}
				}
				$elts = $xml->getElementsByTagName("title");
				foreach($elts as $elt) {
					if ($elt->hasAttribute("xml:lang")) {
						if ($titreOK == "non") {//Le titre est parfois présent plusieurs fois
							echo('Titre : <textarea id="titre-'.$idFic.'" name="titre-'.$idFic.'" class="textarea form-control" style="width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'titre\', valeur: $(this).val(), langue : \''.$lang.'\'});";>'.str_replace("'", "\'", $elt->nodeValue).'</textarea><br>');
							$titreOK = "oui";
						};
						}
				}
				//Métadonnées > Notice
				$target = "";
				$elts = $xml->getElementsByTagName("ref");
				foreach($elts as $elt) {
					if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "file") {
						if ($elt->hasAttribute("target")) {$target = $elt->getAttribute("target");}
					}
				}
				if ($target != "") {//N'afficher les métadonnées de la notice uniquement s'il y en a une
					echo('<p class="form-inline">Texte intégral : <input type="text" id="notice-'.$idFic.'" name="notice-'.$idFic.'" value="'.$target.'" class="form-control" style="height: 18px; width:350px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'notice\', valeur: $(this).val(), valeur2: $(\'#subtype\').val()});";>');
					if ($target != "") {echo(' - <a target="_blank" href="'.$target.'">Lien</a></p>');}
					//Subtype
					echo('<p class="form-inline">Type de dépôt : <select id="subtype-'.$idFic.'" name="subtype-'.$idFic.'" class="form-control" style="height: 18px; padding: 0px; width:150px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'notice\', valeur: $(\'#notice\').val(), valeur2: $(this).val()});">');
					if ($elt->getAttribute("subtype") == "author") {$txt = "selected";}else{$txt = "";}
					echo('<option '.$txt.' value="author">author</option>');
					if ($elt->getAttribute("subtype") == "greenPublisher") {$txt = "selected";}else{$txt = "";}
					echo('<option '.$txt.' value="greenPublisher">greenPublisher</option>');
					if ($elt->getAttribute("subtype") == "publisherPaid") {$txt = "selected";}else{$txt = "";}
					echo('<option '.$txt.' value="publisherPaid">publisherPaid</option>');
					if ($elt->getAttribute("subtype") == "noaction") {$txt = "selected";}else{$txt = "";}
					echo('<option '.$txt.' value="noaction">noaction</option>');
					echo('</select></p>');
					//Licence
					$licence = "";
					$elts = $xml->getElementsByTagName("licence");
					foreach($elts as $elt) {
						if ($elt->hasAttribute("target")) {
							$licence = $elt->getAttribute("target");
						}
					}
					echo('<p class="form-inline">Licence : <input type="text-'.$idFic.'" id="licence" name="licence-'.$idFic.'" value="'.$licence.'" class="form-control" style="height: 18px; width:400px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'licence\', valeur: $(this).val()});";>');
				}
				
				//Métadonnées > Date de publication
				$elts = $xml->getElementsByTagName("date");
				foreach($elts as $elt) {
					if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "datePub") {echo('<p class="form-inline">Date de publication : <input type="text" id="datePub-'.$idFic.'" name="datePub-'.$idFic.'" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'datePub\', valeur: $(this).val()});";></p>');}
				}
				
				//Métadonnées > Date d'édition
				$elts = $xml->getElementsByTagName("date");
				foreach($elts as $elt) {
					if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "dateEpub") {echo('<p class="form-inline">Date d\'édition : <input type="text" id="dateEpub-'.$idFic.'" name="dateEpub-'.$idFic.'" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'dateEpub\', valeur: $(this).val()});";></p>');}
				}
				
				//Métadonnées > Langue
				$tabLang = array_keys($countries);
				$elts = $xml->getElementsByTagName("language");
				foreach($elts as $elt) {
					if ($elt->hasAttribute("ident")) {$lang = $elt->nodeValue;}else{$lang = "";}
				}
				echo('<p class="form-inline">Langue : ');
				echo('<select id="language-'.$idFic.'" name="language" class="form-control" style="height: 18px; padding: 0px; width:150px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'language\', valeur: $(this).val()});";>>');
				for($i = 0; $i < count($countries); $i++) {
					if ($lang == $tabLang[$i]) {$txt = "selected";}else{$txt = "";}
					echo('<option '.$txt.' value="'.$tabLang[$i].'">'.$tabLang[$i].'</option>');
				}
				echo('</select></p>');

				//Métadonnées > Revue
				$elts = $xml->getElementsByTagName("title");
				foreach($elts as $elt) {
					if ($elt->hasAttribute("level")) {echo('<p class="form-inline">Nom de la revue :<br> <input type="text" id="revue-'.$idFic.'" name="revue-'.$idFic.'" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'revue\', valeur: $(this).val()});";></p>');}
				}
				
				//Métadonnées > Audience, vulgarisation et comité de lecture
				$elts = $xml->getElementsByTagName("note");
				foreach($elts as $elt) {
					//Audience
					if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "audience") {
						echo('<p class="form-inline">Audience : ');
						echo('<select id="audience-'.$idFic.'" name="audience" class="form-control" style="height: 18px; padding: 0px; width:150px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'audience\', valeur: $(this).val()});";>>');
						$valAud = $elt->getAttribute("n");
						if ($valAud == 1) {$txt = "selected";}else{$txt = "";}
						echo('<option '.$txt.' value="1">Internationale</option>');
						if ($valAud == 2) {$txt = "selected";}else{$txt = "";}
						echo('<option '.$txt.' value="2">Nationale</option>');
						if ($valAud == 3) {$txt = "selected";}else{$txt = "";}
						echo('<option '.$txt.' value="3">Non renseignée</option>');
						echo('</select></p>');
					}
					//Vulgarisation
					if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "popular") {
						if ($elt->nodeValue == "Yes") {$txtO = "checked"; $txtN = "";}else{$txtO = ""; $txtN = "checked";}
						echo('<p class="form-inline">Vulgarisation : ');
						echo('<input type="radio" '.$txtO.' id="popular-'.$idFic.'" name="popular-'.$idFic.'" value="Yes" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'vulgarisation\', valeur: $(this).val()});";> Oui');
						echo('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
						echo('<input type="radio" '.$txtN.' id="popular-'.$idFic.'" name="popular-'.$idFic.'" value="No" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'vulgarisation\', valeur: $(this).val()});";> Non');
						echo('</p>');
					}
					//Comité de lecture
					if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "peer") {
						if ($elt->nodeValue == "Yes") {$txtO = "checked"; $txtN = "";}else{$txtO = ""; $txtN = "checked";}
						echo('<p class="form-inline">Comité de lecture : ');
						echo('<input type="radio" '.$txtO.' id="peer-'.$idFic.'" name="peer-'.$idFic.'" value="Yes" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'peer\', valeur: $(this).val()});";> Oui');
						echo('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
						echo('<input type="radio" '.$txtN.' id="peer-'.$idFic.'" name="peer-'.$idFic.'" value="No" class="form-control" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'peer\', valeur: $(this).val()});";> Non');
						echo('</p>');
					}
				}
				
				//Métadonnées > Editeur
				$elts = $xml->getElementsByTagName("publisher");
				foreach($elts as $elt) {
					echo('<p class="form-inline">Editeur : <input type="text" id="publisher-'.$idFic.'" name="publisher-'.$idFic.'" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'editeur\', valeur: $(this).val()});";></p>');
				}
				
				//Métadonnées > ISSN et EISSN
				$elts = $xml->getElementsByTagName("idno");
				foreach($elts as $elt) {
					if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "issn") {echo('<p class="form-inline">ISSN : <input type="text" id="issn-'.$idFic.'" name="issn-'.$idFic.'" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'issn\', valeur: $(this).val()});";></p>');}
					if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "eissn") {echo('<p class="form-inline">EISSN : <input type="text" id="eissn-'.$idFic.'" name="eissn-'.$idFic.'" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'eissn\', valeur: $(this).val()});";></p>');}
				}
				
				//Métadonnées > Volume, numéro et pages
				$elts = $xml->getElementsByTagName("biblScope");
				foreach($elts as $elt) {
					if ($elt->hasAttribute("unit") && $elt->getAttribute("unit") == "volume") {echo('<p class="form-inline">Volume : <input type="text" id="volume-'.$idFic.'" name="volume-'.$idFic.'" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'volume\', valeur: $(this).val()});";></p>');}
					if ($elt->hasAttribute("unit") && $elt->getAttribute("unit") == "issue") {echo('<p class="form-inline">Numéro : <input type="text" id="issue-'.$idFic.'" name="issue-'.$idFic.'" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:100px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'issue\', valeur: $(this).val()});";></p>');}
					if ($elt->hasAttribute("unit") && $elt->getAttribute("unit") == "pp") {echo('<p class="form-inline">Pages : <input type="text" id="pp-'.$idFic.'" name="pp-'.$idFic.'" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:150px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'pages\', valeur: $(this).val()});";"></p>');}
				}
				
				//Métadonnées > Financement
				$elts = $xml->getElementsByTagName("funder");
				foreach($elts as $elt) {
					echo('Financement : <textarea id="funder-'.$idFic.'" name="funder-'.$idFic.'" class="textarea form-control" style="width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'financement\', valeur: $(this).val()});";>'.str_replace("'", "\'", $elt->nodeValue).'</textarea><br>');
				}
				
				//Métadonnées > Financement ANR
				echo ('Indiquez le ou les projets ANR liés à ce travail :<br>');
				for ($iANR=1; $iANR < 4; $iANR++) {
					echo('<input type="text" id="ANR'.$iANR.'-'.$idFic.'" name="ANR'.$iANR.'-'.$idFic.'" class="autoANR form-control" style="height: 18px; width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ANR\', valeur: $(this).val()});";>');
				}
				echo ('<br>');
				
				//Métadonnées > Financement EUR
				echo ('Indiquez le ou les projets EU liés à ce travail :<br>');
				for ($iEUR=1; $iEUR < 4; $iEUR++) {
					echo('<input type="text" id="EUR'.$iEUR.'-'.$idFic.'" name="EUR'.$iEUR.'-'.$idFic.'" class="autoEUR form-control" style="height: 18px; width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'EUR\', valeur: $(this).val()});";>');
				}
				echo ('<br>');
				
				//Métadonnées > Mots-clés
				echo('Mots-clés :');
				$keys = $xml->getElementsByTagName("keywords");
				$ind = 0;
				foreach($keys as $key) {
					foreach($key->childNodes as $elt) {
						echo('<input type="text" id="mots-cles'.$ind.'-'.$idFic.'" name="mots-cles'.$ind.'-'.$idFic.'" value="'.str_replace("'", "\'", $elt->nodeValue).'" class="form-control" style="height: 18px; width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'mots-cles\', pos: '.$ind.', valeur: $(this).val(), langue: $(\'#language\').val()});";>');
						$ind++;
					}
				}
				//Ajouter des mots-clés
				echo('<br>');
				echo('Ajouter des mots-clés :');
				for($dni = $ind; $dni < $ind + 5; $dni++) {
					echo('<input type="text" id="mots-cles'.$dni.'-'.$idFic.'" name="mots-cles'.$dni.'-'.$idFic.'" value="" class="form-control" style="height: 18px; width: 280px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajout-mots-cles\', pos: '.$dni.', valeur: $(this).val(), langue: $(\'#language\').val()});";>');
				}
				echo('<br>');
						
				//Métadonnées > Résumé
				$elts = $xml->getElementsByTagName("abstract");
				foreach($elts as $elt) {
					if ($elt->hasAttribute("xml:lang")) {echo('Résumé : <textarea id="abstract-'.$idFic.'" name="abstract-'.$idFic.'" class="textarea form-control" style="width: 500px;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'abstract\', valeur: $(this).val(), langue: $(\'#language\').val()});";>'.str_replace("'", "\'", $elt->nodeValue).'</textarea><br>');}
				}
				
				echo('</span></td>');
				//Fin des métadonnées
			}
			
			//DOI
			if (isset($doiTEI)) {echo('<td><a target=\'_blank\' href=\'https://doi.org/'.$doiTEI.'\'><img alt=\'DOI\' src=\'./img/doi.jpg\'></a>');}else{echo('<td>&nbsp;</td>');}
			
			if (isset($typDbl) && $typDbl == "HALCOLLTYP") {//Doublon de type HAL et COLL > inutile d'afficher les affiliations, la validation du TEI et la possibilité d'import dans HAL
				echo('<td>&nbsp;</td>');
				echo('<td>&nbsp;</td>');
				echo('<td>&nbsp;</td>');
			}else{
				//Auteurs / affiliations
				echo('<td style=\'text-align: left;\'><span id=\'affiliations-'.$idFic.'\'>');
				//$i = compteur auteur / $j = compteur affiliation
				for($i = 0; $i < count($halAut); $i++) {
					echo('<span id="PN-aut'.$i.'-'.$idFic.'"><b>'.$halAut[$i]['firstName'].' '.$halAut[$i]['lastName'].'</b></span>');
					
					//Possibilité de supprimer l'auteur
					echo('&nbsp;<span id="Vu-aut'.$i.'-'.$idFic.'"><a style="cursor:pointer;" onclick="if (confirm(\'Etes-vous sûr de vouloir supprimer cet auteur ?\')) { $.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'supprimerAuteur\', pos: '.$i.', valeur: \''.$halAut[$i]['firstName'].' ~ '.$halAut[$i]['lastName'].'\'}); majokAuteur(\'aut'.$i.'-'.$idFic.'\', \''.str_replace("'", "\'", $halAut[$i]['firstName'].' '.$halAut[$i]['lastName']).'\');}"><img width=\'12px\' alt=\'Supprimer l\'auteur\' src=\'./img/supprimer.jpg\'></a></span>');
					
					//Début span suppression auteur
					echo('&nbsp;<span id="Sup-aut'.$i.'-'.$idFic.'">');
					
					if ($halAut[$i]['mailDom'] != "") {echo(' (@'.$halAut[$i]['mailDom'].')');}
					echo('<br>');
					if ($halAut[$i]['xmlIds'] != "") {
						echo('<span id="Txt'.$halAut[$i]['xmlIds'].'-'.$idFic.'">Supprimer l\'idHAL '.$halAut[$i]['xmlIds'].'</span> <span id="Vu'.$halAut[$i]['xmlIds'].'-'.$idFic.'"><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'supprimerIdHAL\', pos: '.$i.', valeur: \''.$halAut[$i]['xmlIds'].'\'}); majokIdHAL(\''.$halAut[$i]['xmlIds'].'-'.$idFic.'\');";><img width=\'12px\' alt=\'Supprimer l\'idHAL\' src=\'./img/supprimer.jpg\'></a></span><br>');
					}
					//Si pas d'idHAL et si id auteur existe, afficher l'id
					if ($halAut[$i]['idHals'] == "" && $halAut[$i]['docid'] != "") {
						echo('id '.$halAut[$i]['docid'].'<br>');
					}
					if ($halAut[$i]['idHals'] != "") {
						//echo('Remonter le bon auteur du référentiel auteurs <a class=info><img src=\'./img/pdi.jpg\'><span>L\'idHAL n\'est pas ajouté automatiquement car c\'est juste une suggestion que vous devrez valider en l\'ajoutant dans le champ ci-dessous prévu à cet effet.</span></a> :<br><input type="text" id="ajoutidHAL'.$i.'" value="'.$halAutinit[$i]['idHals'].'" name="ajoutidHAL'.$i.'" class="form-control" style="height: 18px; width:200px; align:center;">');
						$idHAL = $halAut[$i]['idHals'].' ('.$halAut[$i]['idHali'].')';
					}else{
						$idHAL = "";
					}
					
					echo('Ajouter un idHAL : <input type="text" id="ajoutIdh'.$i.'-'.$idFic.'" name="ajoutIdh'.$i.'-'.$idFic.'" value="'.$idHAL.'" class="autoID form-control" style="height: 18px; width:280px; align:center;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterIdHAL\', pos: '.$i.', valeur: $(this).val()});";>');
					echo('<a target="_blank" href="https://aurehal.archives-ouvertes.fr/author/browse?critere='.$halAut[$i]['firstName'].'+'.$halAut[$i]['lastName'].'">Consulter le référentiel auteur</a><br>');
					
					//Lors de l'étape 2 (2ème méthode), d'autres idHAL ont-ils été trouvés via la requête ?
					for($id = 0; $id < count($tabIdHAL); $id++) {
						if (isset($tabIdHAL[$id]['firstName']) && $tabIdHAL[$id]['firstName'] == $halAut[$i]['firstName'] && isset($tabIdHAL[$id]['lastName']) && $tabIdHAL[$id]['lastName'] == $halAut[$i]['lastName']) {
							$reqAut = $tabIdHAL[$id]['reqAut'];
							echo('<a target="_blank" href="'.$reqAut.'"><font color=\'red\'>D\'autres idHAL ont été trouvés</font></a><br>');
						}
					}
					
					//Affiliations remontées par OverHAL
					echo('<i><font style=\'color: #999999;\'>Affiliation(s) remontée(s) par OverHAL:<br>');
					for($j = 0; $j < count($nomAff); $j++) {
						if ($halAutinit[$i]['affilName'] != "" && stripos($halAutinit[$i]['affilName'], $nomAff[$j]['lsAff']) !== false) {
							echo('<span id="aut'.$i.'-nomAff'.$j.'">'.$nomAff[$j]['org']);
							//echo('&nbsp;<img width=\'12px\' alt=\'Supprimer l\'affiliation\' src=\'./img/supprimer.jpg\'></span><br>');
							echo('</span><br>');
						}
					}
					echo('</font></i>');
					$ajtAff = "~";//Pour éviter d'afficher 2 fois des affiliations > méthode 1 / méthode 2 > avec ou sans prénom/nom
					$ajtAffDD = "~";//Drag and drop > Pour éviter de prendre en compte 2 fois des affiliations > méthode 1 / méthode 2 > avec ou sans prénom/nom
					for($j = 0; $j < count($halAff); $j++) {
						if ($halAut[$i]['affilName'] != "" && stripos($halAut[$i]['affilName'], $halAff[$j]['lsAff']) !== false && strpos($ajtAff, $halAff[$j]['names']) === false && (($halAut[$i]['firstName'] == $halAff[$j]['fname'] && $halAut[$i]['lastName'] == $halAff[$j]['lname']) || ($halAff[$j]['fname'] == "" && $halAff[$j]['lname'] == ""))) {
							if ($halAff[$j]['valid'] == "VALID") {$txtcolor = '#339966';}
							if ($halAff[$j]['valid'] == "OLD") {$txtcolor = '#ff6600';}
							$ajtAff .= $halAff[$j]['names']."~";
							echo('<span id="aut'.$i.'-halAff'.$j.'-'.$idFic.'" draggable="true"><font style=\'color: '.$txtcolor.';\'>'.$halAff[$j]['ncplt'].'</font></span>');
							echo('&nbsp;<span id="Vu-aut'.$i.'-halAff'.$j.'-'.$idFic.'"><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'supprimerAffil\', pos: '.$i.', valeur: \''.$halAff[$j]['docid'].'\'}); majokAffil(\'aut'.$i.'-halAff'.$j.'-'.$idFic.'\', \''.str_replace("'", "\'", $halAff[$j]['ncplt']).'\');"><img width=\'12px\' alt=\'Supprimer l\'affiliation\' src=\'./img/supprimer.jpg\'></a></span><br>');						
						}
					}
					
					//Drag and drop
					for($j = 0; $j < count($halAff); $j++) {
						if ($halAut[$i]['affilName'] != "" && stripos($halAut[$i]['affilName'], $halAff[$j]['lsAff']) !== false && strpos($ajtAffDD, $halAff[$j]['names']) === false && (($halAut[$i]['firstName'] == $halAff[$j]['fname'] && $halAut[$i]['lastName'] == $halAff[$j]['lname']) || ($halAff[$j]['fname'] == "" && $halAff[$j]['lname'] == ""))) {
							$ajtAffDD .= $halAff[$j]['names']."~";
							echo('<script type="text/javascript">');
							echo('	document.querySelector(\'[id="aut'.$i.'-halAff'.$j.'-'.$idFic.'"]\').addEventListener(\'dragstart\', function(e){');
							echo('			e.dataTransfer.setData(\'text\', e.target.innerText);');
							echo('	});');
							echo('</script>');
						}
					}
					
					echo('Ajouter des affiliations : <br>');
					
					for($dni = $j; $dni < $j + 5; $dni++) {						
						echo('<span class="form-inline"><input type="text" draggable="true" id="aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'" name="aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'" value="" class="autoAF form-control" style="height: 18px; width: 280px;" onclick="this.setSelectionRange(0, this.value.length);" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterAffil\', pos: '.$i.', valeur: $(this).val()});">');
						echo('&nbsp;<span id="Vu-aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'"><a style="cursor:pointer;" onclick="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'supprimerAffil\', pos: '.$i.', valeur: $(\'#aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'\').val().split(\'~\')[0].trim()}); majokAffilAjout(\'aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'\');"><img width=\'12px\' alt=\'Supprimer l\'affiliation\' src=\'./img/supprimer.jpg\'></a></span></span><br>');
						
						//Drag and drop
						echo('<script type="text/javascript">');
						echo('	document.querySelector(\'[id="aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'"]\').addEventListener(\'dragstart\', function(e){');
						echo('			e.dataTransfer.setData(\'text\', e.target.value);');
						echo('	});');
						echo('	var input = document.getElementById("aut'.$i.'-ajoutAff'.$dni.'-'.$idFic.'");');
						echo('	input.addEventListener(\'drop\', function (event) {');
						echo('		event.preventDefault();');
						echo('		var textData = event.dataTransfer.getData(\'text\');'); // Récupérer ce qui est déplacé
						echo('		event.target.value = textData;'); // Changer le contenu avec ce qui est déplacé
						echo('		$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterAffil\', pos: '.$i.', valeur: textData});');
						echo('	});');
						echo('</script>');
					}
					//echo('<input type="text" id="ajoutAff'.$i.'" name="ajoutAff'.$i.'" class="autoAF form-control" style="height: 18px; width:280px; align:center;" onchange="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterAffil\', pos: '.$i.', valeur: $(this).val()});";>');
					echo('</font><br>');
					
					echo ('</span>');//Fin span suppression auteur
				}
				echo('<br>');
				echo('<b>Ajouter un auteur <i>(Prénom Nom)</i> : </b><input type="text" id="ajoutAuteur-'.$idFic.'" name="ajoutAuteur" class="form-control" style="height: 18px; width:280px; align:center;" onfocusout="$.post(\'Zip2HAL_liste_actions.php\', {nomfic : \''.$nomfic.'\', action: \'ajouterAuteur\', pos: '.$i.', valeur: $(this).val()});";>');
				echo('</span></td>');
				
				//Validation du TEI
				echo('<td><span id=\'validerTEI-'.$idFic.'\'>');
				echo('<div id=\'cpt4-'.$idFic.'\'>Validation en cours ...</div>');
				ob_flush();
				flush();
				ob_flush();
				flush(); 
				$maj = "non";
				$tst = new DOMDocument();
				$tst->load($nomfic);
				if (!$tst->schemaValidate('./aofr.xsd')) {
					echo('<script>');
					echo('document.getElementById(\'cpt4\').style.display = \'none\';');
					echo('</script>');
					echo('<a target=\'_blank\' href=\'https://www.freeformatter.com/xml-validator-xsd.html#\'><img alt=\'TEI non valide AOFR\' src=\'./img/supprimer.jpg\'></a><br>');
					echo('<a target=\'_blank\' href=\''.$nomfic.'\'>Lien TEI</a><br>');
					print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
					libxml_display_errors();
				}else{
					echo('<script>');
					echo('document.getElementById(\'cpt4-'.$idFic.'\').style.display = \'none\';');
					echo('</script>');
					echo('<a target=\'_blank\' href=\'https://www.freeformatter.com/xml-validator-xsd.html#\'><img alt=\'TEI validé AOFR\' src=\'./img/done.png\'></a><br>');
					echo('<a target=\'_blank\' href=\''.$nomfic.'\'>Lien TEI</a><br>');			
					$maj = "oui";
				}
				echo('<span></td>');
				
				//Importer dans HAL
				if ($maj == "oui") {
					$idNomfic = str_replace(array(".xml", "./XML/"), "", $nomfic);
					$lienMAJ = "./Zip2HALModif.php?action=MAJ&Id=".$idNomfic;
					//$lienMAJ = "https://ecobio.univ-rennes1.fr";//Pour test
					include "./Zip2HAL_actions.php";
					echo('<td><span id=\'importerHAL-'.$idFic.'\'><center><span id=\''.$idNomfic.'-'.$idFic.'\'><a target=\'_blank\' href=\''.$lienMAJ.'\' onclick="$.post(\'Zip2HAL_liste_actions.php\', { idNomfic : \''.$idNomfic.'\', action: \'statistiques\', valeur: \''.$idNomfic.'\'}); majokVu(\''.$idNomfic.'-'.$idFic.'\');"><img alt=\'MAJ\' src=\'./img/MAJ.png\'></a></span></center></td>');
				}else{
					echo('<td><center><img alt=\'MAJ\' src=\'./img/MAJImpossible.png\'></center></span></td>');
				}
			}
			
			echo('</tr>');
			echo('<table>');
			//Fin du tableau des résultats
			
			//TODO stats à mettre en place
			
		}
		$idFic++;
	}
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
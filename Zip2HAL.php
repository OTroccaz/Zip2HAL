<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "https://www.w3.org/TR/html4/loose.dtd">
<?php
//Avant tout, vérification de l'étape de chargement du fichier TEI OverHAL xml
if (isset($_GET['nomfic'])) {
	$nomfic = $_GET['nomfic'];
}else{
	header('Location: '.'TEI_OverHAL.php?erreur=6');
}

//Si le fichier a été supprimé
if (!isset($nomfic) || !file_exists($nomfic)) {
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

function objectToArray($object) {
  if (!is_object( $object) && !is_array($object)) {
    return $object;
  }
  if (is_object($object)) {
    $object = get_object_vars($object);
  }
  return array_map('objectToArray', $object);
}

function askCurl($url, &$arrayCurl) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_USERAGENT, 'SCD (https://halur1.univ-rennes1.fr)');
  curl_setopt($ch, CURLOPT_USERAGENT, 'PROXY (https://siproxy.univ-rennes1.fr)');
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $json = curl_exec($ch);
  curl_close($ch);
  
  $memory = intval(ini_get('memory_limit')) * 1024 * 1024;
  $limite = strlen($json)*1;
  if ($limite > $memory) {
    die ('<b><font color="red">Désolé ! La collection et/ou la période choisie génère(nt) trop de résultats pour être traités correctement.</font></b>');
  }else{
    $parsed_json = json_decode($json, true);
    $arrayCurl = objectToArray($parsed_json);
  }
}

function insertNode($xml, $dueon, $amont, $aval, $tagName, $typAtt1, $valAtt1, $typAtt2, $valAtt2, $methode) {//$methode = iB (insertBefore) ou aC (appendChild)
  $noeud = "";
  $dueon = htmlspecialchars($dueon);
  //si noeud présent
  $elts = $xml->getElementsByTagName($tagName);
  foreach ($elts as $elt) {
    if ($elt->hasAttribute($typAtt1)) {
      $quoi = $elt->getAttribute($typAtt1);
      if ($amont != "langUsage" && $tagName != "abstract") {
        if ($quoi == $valAtt1) {
          $elt->nodeValue = $dueon;
          if ($elt->hasAttribute("subtype")) {$elt->removeAttribute("subtype");}//suppression inPress
          if ($valAtt2 != "") {$elt->setAttribute($typAtt2, $valAtt2);}
          $noeud = "ok";
        }
      }else{
        $elt->nodeValue = $dueon;
        $elt->setAttribute($typAtt1, $valAtt1);
        if ($elt->hasAttribute("subtype")) {$elt->removeAttribute("subtype");}//suppression inPress
        if ($valAtt2 != "") {$elt->setAttribute($typAtt2, $valAtt2);}
        $noeud = "ok";
      }
    }
  }
	
  //si noeud absent > recherche du noeud amont pour insérer les nouvelles données au bon emplacement
  if ($noeud == "" && $dueon != "") {
    $bibl = $xml->getElementsByTagName($amont);
    foreach ($bibl as $elt) {
      foreach($elt->childNodes as $item) { 
        if ($item->hasChildNodes()) {
          $childs = $item->childNodes;
          foreach($childs as $i) {
            $name = $i->parentNode->nodeName;
            if ($name == $aval) {//insertion nvx noeuds
              $bip = $xml->createElement($tagName);
              $cTn = $xml->createTextNode($dueon);
              if ($typAtt1 != "" && $valAtt1 != "") {$bip->setAttribute($typAtt1, $valAtt1);}
              if ($valAtt2 != "") {$bip->setAttribute($typAtt2, $valAtt2);}
              $bip->appendChild($cTn);
              $biblStr = $xml->getElementsByTagName($amont)->item(0);
              if ($methode == "iB") {//insertBefore
                $biblStr->insertBefore($bip, $i->parentNode);
              }else{
                $biblStr->appendChild($bip);
              }
              break 2;
            }
          }
        }
      }
    }
  }
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
function suppression($dossier, $age) {
  $repertoire = opendir($dossier);
    while(false !== ($fichier = readdir($repertoire)))
    {
      $chemin = $dossier."/".$fichier;
      $infos = pathinfo($chemin);
      $age_fichier = time() - filemtime($chemin);
      if ($fichier != "." && $fichier != ".." && !is_dir($fichier) && $age_fichier > $age)
      {
      unlink($chemin);
      //echo $chemin." - ".date ("F d Y H:i:s.", filemtime($chemin))."<br>";
      }
    }
  closedir($repertoire);
}
suppression("./XML", 3600);//Suppression des fichiers du dossier XML créés il y a plus d'une heure

include("./normalize.php");
include("./URLport_coll.php");
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
    $(".autoAC").autocomplete({
        source: "AC_AF.php",
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
    include('./CrosHALForm.php');
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
	$racine = htmlspecialchars($_POST["racine"]);
	$domaine = htmlspecialchars($_POST["domaine"]);
}
?>

<form method="POST" accept-charset="utf-8" name="zip2hal" action="Zip2HAL.php?nomfic=<?php echo($nomfic); ?>">

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
<select id="domaine" class="form-control" size="1" name="domaine" style="padding: 3px; width: 350px;">
<?php
$reqAPI = "https://api.archives-ouvertes.fr/ref/domain/?q=*:*&fl=code_s,fr_domain_s&rows=500&sort=fr_domain_s%20ASC";
$contents = file_get_contents($reqAPI);
$results = json_decode($contents);
foreach($results->response->docs as $entry) {
	$code = $entry->code_s;
	$label = $entry->fr_domain_s;
	if ($domaine == $code) {$txt = "selected";}else{$txt = "";}
	echo('<option '.$txt.' value="'.$code.'">'.$label.'</option>');	
}
?>
</select>
</p>

<p class="form-inline"><b><label for="teioverhal">Fichier TEI OverHAL : </label></b>
<?php
if (file_exists($nomfic)) {
	echo($nomfic);
}
?>

</p>

<br>
<input type="submit" class="btn btn-md btn-primary" value="Valider" name="soumis">
</form>
<br>

<?php

if (isset($_POST["soumis"])) {
	//Chargement du fichier XML
	$xml = new DOMDocument( "1.0", "UTF-8" );
	$xml->formatOutput = true;
	$xml->preserveWhiteSpace = false;
	$xml->load($nomfic);
	$xml->saveXML();
	
	//Récupération du titre et du DOI de la notice TEI
	$titTEI = "";
	$doiTEI = "";
	$tits = $xml->getElementsByTagName("title");
	foreach($tits as $tit) {
		if ($tit->hasAttribute("xml:lang")) {$titTEI = $tit->nodeValue;}
	}
	$idns = $xml->getElementsByTagName("idno");
	foreach($idns as $idn) {
		if ($idn->hasAttribute("type") && $idn->getAttribute("type") == 'doi') {$doiTEI = $idn->nodeValue;}
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
	
	if ($numFound == 0) {
		echo('Aucune notice trouvée');
	}else{
		//echo('<br><br>');
		//echo($numFound. " notice(s) trouvée(s)");
		//echo('<br><br>');
		
		//Etape 1 - Parcours des notices à la recherche de doublons potentiels (DOI ou titre exact)
		$cpt = 1;
		$dbl = 0;
		$halId = array();
		
		echo('<b>Etape 1 : recherche des doublons potentiels</b><br>');
		echo('<a target="_blank" href="'.$reqAPI.'">URL requête API HAL</a><br>');
		echo($numFound. ' notice(s) examinée(s) : ');
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
				$dbl++;
				//echo('Doublon trouvé sur la base du '.$doublon.' pour <a target="_blank" href="https://hal.archives-ouvertes.fr/'.$hId.'">'.$hId.'</a> et <a target="_blank" href="https://hal.archives-ouvertes.fr/'.$halId[$hId].'">'.$halId[$hId].'</a><br>');
				$halId['doublon'][$hId] .= '&nbsp;<a target="_blank" href="https://hal.archives-ouvertes.fr/'.$halId[$hId].'"><img src=\'./img/doublon.jpg\'></a>&nbsp;';
			}
			$cpt++;
		}
		if ($dbl == 0) {echo('aucune notice trouvée dans HAL, donc, pas de doublon');}//Notice non trouvée > pas de doublon
		if ($dbl >= 1) {echo('la notice est déjà présente dans HAL');}//Présence de doublon(s)

		echo('<script>');
		echo('document.getElementById(\'cpt1\').style.display = \'none\';');
		echo('</script>');
		//Fin étape 1
		
		
		//Etape 2 - Recherche des idHAL des auteurs				
		echo('<br><br>');
		$cpt = 1;
		$iAut = 0;
		$preAut = array();//Prénoms des auteurs
		$nomAut = array();//Noms des auteurs
		$affAut = array();//Affiliation des auteurs
		$xmlIds = array();//IdHALs trouvés
		$xmlIdi = array();//IdHALi trouvés
		$halAut = array();
		
		echo('<b>Etape 2 : recherche des idHAL des auteurs</b><br>');
		echo('<div id=\'cpt2\'></div>');
		
		$auts = $xml->getElementsByTagName("author");
		foreach($auts as $aut) {
			//Initialisation des variables
			$xmlIds[$iAut] = "";
			$xmlIdi[$iAut] = "";
			$affAut[$iAut] = "";
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
				//Affiliations
				if ($elt->nodeName == "affiliation") {
					if ($elt->hasAttribute("ref")) {$affAut[$iAut] .= $elt->getAttribute("ref").'~';}
				}
			}
			$iAut++;
		}
		//var_dump($preAut);
		//var_dump($nomAut);
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
			$halAut[$iAut]['mailDom'] = "";
			$halAut[$iAut]['docid'] = "";
			$firstNameT = strtolower(wd_remove_accents($firstName));
			$lastNameT = strtolower(wd_remove_accents($lastName));
			$reqAut = "https://api.archives-ouvertes.fr/ref/author/?q=firstName_t:%22".$firstNameT."%22%20AND%20lastName_t:%22".$lastNameT."%22&rows=1000&fl=idHal_i,idHal_s,docid,valid_s,emailDomain_s";
			$reqAut = str_replace(" ", "%20", $reqAut);
			//echo $reqAut.'<br>';
			$contAut = file_get_contents($reqAut);
			$resAut = json_decode($contAut);
			$numFound = 0;
			if (isset($resAut->response->numFound)) {$numFound=$resAut->response->numFound;}
			$docid = "";
			$nbdocid = 0;
			$iHi = "non";//Test pour savoir si un idHal_i a été trouvé
	
			if ($numFound != 0) {				
				foreach($resAut->response->docs as $author) {
					if (isset($author->idHal_i) && $author->idHal_i != 0 && $author->valid_s == "VALID") {
						//echo $firstName.' '.$lastName.' : '.$author->idHal_i.' -> '.$author->idHal_s.' - ';
						$halAut[$iAut]['firstName'] = $firstName;
						$halAut[$iAut]['lastName'] = $lastName;
						$halAut[$iAut]['affilName'] = $affilName;
						if (isset($author->idHal_i)) {$halAut[$iAut]['idHali'] = $author->idHal_i;}else{$halAut[$iAut]['idHali'] = "";}
						if (isset($author->idHal_s)) {$halAut[$iAut]['idHals'] = $author->idHal_s;}else{$halAut[$iAut]['idHals'] = "";}
						if (isset($author->emailDomain_s)) {$halAut[$iAut]['mailDom'] = $author->emailDomain_s;}else{$halAut[$iAut]['mailDom'] = "";}
						$halAut[$iAut]['docid'] = "";
						$iHi = "oui";
						$cptiHi++;
						break;
					}else{//Pas d'idHal
						$docid .= $author->docid;
						$nbdocid++;
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
				//echo($firstName.' '.$lastName.' : '.$docid);
			}
			$iAut++;
			//echo ('<br>');
			$cpt++;
		}
		//var_dump($halAut);
		$halAutinit = $halAut;//Sauvegarde des affiliations et idHal intiaux remontées par OverHAL
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
		$aTester = array('UMR', 'UMS', 'UPR', 'ERL', 'IFR', 'UR', 'USR', 'USC', 'CIC', 'CIC-P', 'CIC-IT', 'FRE', 'EA', 'INSERM', 'U');

		
		echo('<b>Etape 3a : recherche des id structures des affiliations</b><br>');
		echo('<div id=\'cpt3a\'></div>');
		
		$cptAff = 0;
		
		//Affiliations
		$affs = $xml->getElementsByTagName("org");
		foreach($affs as $aff) {
			if ($aff && $aff->hasAttribute("xml:id")) {$nomAff[$iAff]['lsAff'] = '#'.$aff->getAttribute("xml:id").'~'; $cptAff++;}
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
			$test = "non";//Test pour savoir si le code commence par un des éléments du tableau aTester
			foreach($aTester as $elt) {
				if (stripos($code, $elt) !== false) {
					if ($elt == "U" && strlen($code) != 5) {break;}
					if (($elt == "UR" || $elt == "EA" || $elt == "IFR") && strlen($code) != 6) {break;}
					if ($elt == "UMR" && strlen($code) > 7) {break;}
					if (($elt == "UMS" || $elt == "UPR" || $elt == "ERL" || $elt == "USR" || $elt == "USC" || $elt == "FRE" || $elt == "CIC") && strlen($code) != 7) {break;}
					if ($elt == "CIC-P" && strlen($code) != 9) {break;}
					if ($elt == "CIC-IT" && strlen($code) != 10) {break;}
					$test = "oui";
					break;
				}			
			}
			if ($test == "oui") {
				
				//1ère méthode > avec le référentiel HAL des structures
				$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=%22".$code."%22%20AND%20-valid_s:%22INCOMING%22&fl=*&rows=1000&fl=docid,valid_s,name_s";
				$reqAff = str_replace(" ", "%20", $reqAff);
				//echo $reqAff.'<br>';
				$contAff = file_get_contents($reqAff);
				$resAff = json_decode($contAff);
				if (isset($resAff->response->numFound)) {$numFound=$resAff->response->numFound;}
				if ($numFound != 0) {			
					foreach($resAff->response->docs as $affil) {
						$halAff[$iAff]['docid'] = $affil->docid;
						$halAff[$iAff]['lsAff'] = $nomAff[$i]['lsAff'];
						$halAff[$iAff]['valid'] = $affil->valid_s;
						$halAff[$iAff]['names'] = $affil->name_s;
						$halAff[$iAff]['fname'] = "";
						$halAff[$iAff]['lname'] = "";
						$iAff++;
					}
				}
				
				//2ème méthode > avec le référentiel HAL des notices
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
												$reqVoO = "https://api.archives-ouvertes.fr/ref/structure/?q=docid:%22".$fSepTab[2]."%22%20AND%20-valid_s:%22INCOMING%22&fl=*&rows=1000&fl=docid,valid_s,name_s";
												$reqVoO = str_replace(" ", "%20", $reqVoO);
												$contVoO = file_get_contents($reqVoO);
												$resVoO = json_decode($contVoO);
												$halAff[$iAff]['docid'] = intval($fSepTab[2]);
												$halAff[$iAff]['lsAff'] = $nomAff[$i]['lsAff'];
												$halAff[$iAff]['valid'] = $resVoO->response->docs[0]->valid_s;
												$halAff[$iAff]['names'] = $fSepTab[4];
												$halAff[$iAff]['fname'] = $firstName;
												$halAff[$iAff]['lname'] = $lastName;
												$iAff++;
											}
										}
									}
								}
							}
						}
					}
				}
			}else{
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
				
				$reqAut = "https://api.archives-ouvertes.fr/search/authorstructure/?firstName_t=".$firstNameT."&lastName_t=".$lastNameT;
				$reqAut = str_replace(" ", "%20", $reqAut);
				//echo $reqAut.'<br>';
				$contAut = file_get_contents($reqAut);
				$resAut = json_decode($contAut);
				$orgName = "";
				if (isset($resAut->response->result->org[0]->orgName)) {$orgName = $resAut->response->result->org[0]->orgName;}
				if ($orgName != "") {//Une affiliation a été trouvée
					$reqAff = "https://api.archives-ouvertes.fr/ref/structure/?q=%22".$orgName."%22&fl=*&rows=1000&fl=idocid,valid_s,name_s";
					$reqAff = str_replace(" ", "%20", $reqAff);
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
								$halAff[$iAff]['fname'] = $halAut[$i]['firstName'];
								$halAff[$iAff]['lname'] = $halAut[$i]['lastName'];
								$halAut[$i]['affilName'] = "#localStruct-Aff".$cptAff."~";
								$iAff++;
								$docid = "oui";
							}
						}
						/*
						if ($docid == "non") {//pas de docid trouvé avec VALID ou OLD > on teste avec INCOMING
							foreach($resAff->response->docs as $affil) {
								if ($affil->valid_s == "INCOMING"  && $docid == "non") {
									$halAff[$iAff]['docid'] = $affil->docid;
									$cptNoaff++;
									$cptAff++;
									$halAff[$iAff]['lsAff'] = "localStruct-Aff".$cptAff;
									$halAut[$i]['affilName'] = "localStruct-Aff".$cptAff."~";
									$iAff++;
									$docid = "oui";
								}
							}
						}
						*/
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
		
		
		/*
		//Actions
		foreach($results->response->docs as $entry) {
			$hId = $entry->halId_s;
			$lienMAJ = "";
			$lienMAJgrp = "";
			$actsMAJ = "";
			$actsMAJgrp = "";
			$actMaj = "ok";
			$raisons = "";
			$tei = $entry->label_xml;
			$tei = str_replace(array('<p>', '</p>'), '', $tei);
			$tei = str_replace('<p part="N">HAL API platform', '<p part="N">HAL API platform</p>', $tei);
			$teiRes = '<?xml version="1.0" encoding="UTF-8"?>'.$tei;
			$Fnm = "./XML/".$hId.".xml";
			$xml = new DOMDocument( "1.0", "UTF-8" );
			$xml->formatOutput = true;
			$xml->preserveWhiteSpace = false;
			$colact = "ok";
			if (@$xml->loadXML($teiRes) !== false) {//tester validité teiRes
				$xml->loadXML($teiRes);
			}else{
				$colact = "pasok";
			}
			
			//suppression noeud <teiHeader>
			$elts = $xml->documentElement;
			if (is_object($elts->getElementsByTagName("teiHeader")->item(0))) {
				$elt = $elts->getElementsByTagName("teiHeader")->item(0);
				$newXml = $elts->removeChild($elt);
			}
			
			//suppression éventuel attribut 'corresp' pour le noeud <idno type="stamp" n="xxx" corresp="yyy">
			if (is_object($xml->getElementsByTagName("idno"))) {
				$elts = $xml->getElementsByTagName("idno");
				$nbelt = $elts->length;
				for ($pos = $nbelt; --$pos >= 0;) {
					$elt = $elts->item($pos);
					if ($elt && $elt->hasAttribute("type")) {
						$quoi = $elt->getAttribute("type");
						if ($quoi == "stamp") {
							if ($elt->hasAttribute("corresp")) {$elt->removeAttribute("corresp");}
							//$xml->save($nomfic);
						}
					}
				}
			}
			
			//suppression éventuel noeud <listBibl type="references">
			if (is_object($xml->getElementsByTagName("listBibl"))) {
				$elts = $xml->getElementsByTagName("listBibl");
				foreach($elts as $elt) {
					if ($elt->hasAttribute("type")) {
						$quoi = $elt->getAttribute("type");
						if ($quoi == "references") {
							$parent = $elt->parentNode; 
							$newXml = $parent->removeChild($elt);
						}
					}
				}
			}
			
			//Si absent, ajout du code collection
			
			$xml->save($Fnm);
		}
		//Fin actions
		*/
		
		
		//Tableau des résultats
		echo('<br><br>');
		
		echo('<b>Tableau des résultats</b><br>');
		echo('<table class=\'table table-striped table-bordered table-hover;\'>');
		echo('<tr>');
		echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>ID</b></td>');
		echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Doublon</b></td>');
		echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Supprimer</b></td>');
		echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Type de document</b></td>');
		echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Métadonnées</b></td>');
		echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>DOI</b></td>');
		echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Auteurs / affiliations</b></td>');
		echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Valider le TEI modifié</td>');
		echo('<td style=\'text-align: center; background-color: #eeeeee; color: #999999;\'><b>Importer dans HAL</b></td>');
		echo('</tr>');
		
		$cpt = 1;
		
		echo('<tr style=\'text-align: center;\'>');
		//Numérotation > id
		echo('<td>'.$cpt.'</td>');
		//Doublon ?
		echo('<td><a target=\'_blank\' href=\'https://hal.archives-ouvertes.fr/'.$idTEI.'\'><img alt=\'HAL\' src=\'./img/HAL.jpg\'></a>');
		//Supprimer toute la notice
		echo('<td><img alt=\'Supprimer la notice\' src=\'./img/supprimer.jpg\'>');
		echo('<td>'.$docTEI.'</td>');
		//Métadonnées
		echo('<td style=\'text-align: left;\'>');
		//Métadonnées > Titre
		$elts = $xml->getElementsByTagName("title");
		foreach($elts as $elt) {
			if ($elt->hasAttribute("xml:lang")) {echo('Titre : <textarea id="titre" name="titre" class="textarea form-control" style="width: 500px;">'.str_replace("'", "\'", $elt->nodeValue).'</textarea><br>');}
		}
		//Métadonnées > Notice
		$elts = $xml->getElementsByTagName("ref");
		foreach($elts as $elt) {
			if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "file") {
				if ($elt->hasAttribute("target")) {echo('<p class="form-inline">Notice : <input type="text" id=notice" name="notice" value="'.$elt->getAttribute("target").'" class="form-control" style="height: 18px; width:400px;"> - <a target="_blank" href="'.$elt->getAttribute("target").'">Lien</a></p>');}
			}
		}
		//Métadonnées > Date de publication
		$elts = $xml->getElementsByTagName("date");
		foreach($elts as $elt) {
			if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "datePub") {echo('<p class="form-inline">Date de publication : <input type="text" id="datePub" name="datePub" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:100px;"></p>');}
		}
		//Métadonnées > Langue
		$elts = $xml->getElementsByTagName("language");
		foreach($elts as $elt) {
			if ($elt->hasAttribute("ident")) {echo('<p class="form-inline">Langue : <input type="text" id="language" name="language" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:150px;"></p>');}
		}
		//Métadonnées > Revue
		$elts = $xml->getElementsByTagName("title");
		foreach($elts as $elt) {
			if ($elt->hasAttribute("level")) {echo('<p class="form-inline">Nom de la revue : <input type="text" id="revue" name="revue" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:150px;"></p>');}
		}
		//Métadonnées > Audience, vulgarisation et comité de lecture
		$elts = $xml->getElementsByTagName("note");
		foreach($elts as $elt) {
			if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "audience") {
				$audience = '';
				switch($elt->getAttribute("n")) {
					case 1 :
						$audience = 'Internationale';
						break;
					case 2 :
						$audience = 'Nationale';
						break;
					case 3 :
						$audience = 'Non renseignée';
						break;
				}
				echo('<p class="form-inline">Audience : <input type="text" id="audience" name="audience" value="'.$audience.'" class="form-control" style="height: 18px; width:200px;"></p>');
			}
			if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "popular") {echo('<p class="form-inline">Vulgarisation : <input type="text" id="popular" name="popular" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:100px;"></p>');}
			if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "peer") {echo('<p class="form-inline">Comité de lecture : <input type="text" id="peer" name="peer" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:100px;"></p>');}
		}
		//Métadonnées > Editeur
		$elts = $xml->getElementsByTagName("publisher");
		foreach($elts as $elt) {
			echo('<p class="form-inline">Editeur : <input type="text" id="publisher" name="publisher" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:300px;"></p>');
		}
		//Métadonnées > ISSN et EISSN
		$elts = $xml->getElementsByTagName("idno");
		foreach($elts as $elt) {
			if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "issn") {echo('<p class="form-inline">ISSN : <input type="text" id="issn" name="issn" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:100px;"></p>');}
			if ($elt->hasAttribute("type") && $elt->getAttribute("type") == "eissn") {echo('<p class="form-inline">EISSN : <input type="text" id="eissn" name="eissn" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:100px;"></p>');}
		}
		//Métadonnées > Volume, numéro et pages
		$elts = $xml->getElementsByTagName("biblScope");
		foreach($elts as $elt) {
			if ($elt->hasAttribute("unit") && $elt->getAttribute("unit") == "volume") {echo('<p class="form-inline">Volume : <input type="text" id="volume" name="volume" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:100px;"></p>');}
			if ($elt->hasAttribute("unit") && $elt->getAttribute("unit") == "issue") {echo('<p class="form-inline">Numéro : <input type="text" id="issue" name="issue" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:100px;"></p>');}
			if ($elt->hasAttribute("unit") && $elt->getAttribute("unit") == "pp") {echo('<p class="form-inline">Pages : <input type="text" id="pp" name="pp" value="'.$elt->nodeValue.'" class="form-control" style="height: 18px; width:150px;"></p>');}
		}
		//Métadonnées > Financement
		$elts = $xml->getElementsByTagName("funder");
		foreach($elts as $elt) {
			echo('Financement : <textarea id="funder" name="funder" class="textarea form-control" style="width: 500px;">'.str_replace("'", "\'", $elt->nodeValue).'</textarea><br>');
		}
		//Métadonnées > Mots-clés
		$motscles = '';
		$keys = $xml->getElementsByTagName("keywords");
		foreach($keys as $key) {
			foreach($key->childNodes as $elt) {
				$motscles .= $elt->nodeValue.', ';
			}
		}
		$motscles = substr($motscles, 0, (strlen($motscles) - 2));
		echo('Mots-clés : <textarea id="funder" name="funder" class="textarea form-control" style="width: 500px;">'.str_replace("'", "\'", $motscles).'</textarea><br>');
		//Métadonnées > Résumé
		$elts = $xml->getElementsByTagName("abstract");
		foreach($elts as $elt) {
			if ($elt->hasAttribute("xml:lang")) {echo('Résumé : <textarea id="abstract" name="abstract" class="textarea form-control" style="width: 500px;">'.str_replace("'", "\'", $elt->nodeValue).'</textarea><br>');}
		}
		
		echo('</td>');
		//DOI
		if (isset($doiTEI)) {echo('<td><a target=\'_blank\' href=\'https://doi.org/'.$doiTEI.'\'><img alt=\'DOI\' src=\'./img/doi.jpg\'></a>');}else{echo('<td>&nbsp;</td>');}
		//Auteurs / affiliations
		echo('<td style=\'text-align: left;\'>');
		for($i = 0; $i < count($halAut); $i++) {
			echo('<b>'.$halAutinit[$i]['firstName'].' '.$halAutinit[$i]['lastName'].'</b>');
			if ($halAutinit[$i]['mailDom'] != "") {echo(' (@'.$halAutinit[$i]['mailDom'].')');}
			echo('<br>');
			if ($halAutinit[$i]['xmlIds'] != "") {
				echo('Supprimer l\'idHAL '.$halAutinit[$i]['xmlIds'].' <img width=\'12px\' alt=\'Supprimer l\'idHAL\' src=\'./img/supprimer.jpg\'><br>');
			}
			if ($halAutinit[$i]['idHals'] != "") {
				echo('Remonter le bon auteur du référentiel auteurs :<br><form><input type="text" id="ajoutidHAL" value="'.$halAutinit[$i]['idHals'].'" name="ajoutidHAL'.$i.'" class="form-control" style="height: 18px; width:200px; align:center;"></form>');
			}
			echo('<form>Ajouter un idHAL : <input type="text" id="ajoutIdh'.$i.'" name="ajoutIdh'.$i.'" class="autoID form-control" style="height: 18px; width:300px; align:center;"></form>');
			echo('<i><font style=\'color: #999999;\'>Affiliation(s) remontée(s) par OverHAL:<br>');
			for($j = 0; $j < count($nomAff); $j++) {
				if ($halAutinit[$i]['affilName'] != "" && stripos($halAutinit[$i]['affilName'], $nomAff[$j]['lsAff']) !== false) {
					echo($nomAff[$j]['org']);
					echo('&nbsp;<img width=\'12px\' alt=\'Supprimer l\'affiliation\' src=\'./img/supprimer.jpg\'><br>');
				}
			}
			echo('</font></i>');
			$ajtAff = "~";//Pour éviter d'afficher 2 fois des affiliations > méthode 1 / méthode 2 > avec ou sans prénom/nom
			for($j = 0; $j < count($halAff); $j++) {
				if ($halAut[$i]['affilName'] != "" && stripos($halAut[$i]['affilName'], $halAff[$j]['lsAff']) !== false && strpos($ajtAff, $halAff[$j]['names']) === false && (($halAut[$i]['firstName'] == $halAff[$j]['fname'] && $halAut[$i]['lastName'] == $halAff[$j]['lname']) || ($halAff[$j]['fname'] == "" && $halAff[$j]['lname'] == ""))) {
					if ($halAff[$j]['valid'] == "VALID") {$txtcolor = '#339966';}
					if ($halAff[$j]['valid'] == "OLD") {$txtcolor = '#ff6600';}
					$ajtAff .= $halAff[$j]['names']."~";
					echo('<font style=\'color: '.$txtcolor.';\'>'.$halAff[$j]['names'].'</font>');
					echo('&nbsp;<img width=\'12px\' alt=\'Supprimer l\'affiliation\' src=\'./img/supprimer.jpg\'><br>');
				}
			}
			echo('<form>Ajouter une affiliation : <input type="text" id="ajoutAff'.$i.'" name="ajoutAff'.$i.'" class="autoAF form-control" style="height: 18px; width:300px; align:center;"></form>');
			echo('</font><br>');
		}
		echo('<br>');
		echo('<form><b>Ajouter un auteur : </b><input type="text" id="ajoutAuteur" name="ajoutAuteur" class="form-control" style="height: 15px; width:200px; align:center;"></form>');
		echo('</td>');
		echo('<td><img alt=\'Valider le TEI modifié\' src=\'./img/done.png\'>');
		echo('<td><img alt=\'Importer dans HAL\' src=\'./img/MAJ.png\'>');
		echo('</tr>');
		echo('<table>');
		//Fin du tableau des résultats
		
		//TODO stats à mettre en place
		
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
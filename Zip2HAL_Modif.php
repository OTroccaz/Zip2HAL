<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "https://www.w3.org/TR/html4/loose.dtd">
<?php
/*
 * Zip2HAL - Importez vos publications dans HAL - Import your publications into HAL
 *
 * Copyright (C) 2023 Olivier Troccaz (olivier.troccaz@cnrs.fr) and Laurent Jonchère (laurent.jonchere@univ-rennes.fr)
 * Released under the terms and conditions of the GNU General Public License (https://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * Modification du TEI - Modification of the TEI
 */
 
header('Content-type: text/html; charset=UTF-8');
?>

<?php
include "./Zip2HAL_constantes.php";

$passw = 'password';
$h_passw = 'HAL_PASSWD';

//require_once('./CAS_connect.php')//authentification CAS ou autre ?
if (strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
  include('./_connexion.php');
  $HAL_USER = $user;
  $HAL_PASSWD = $pass;
}else{
  require_once('./CAS_connect.php');
  
  if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
  $HAL_USER = phpCAS::getUser();
  $_SESSION['HAL_USER'] = $HAL_USER;
  $HAL_PASSWD = "";
  if (isset($_POST[$passw ]) && $_POST[$passw ] != "") {$_SESSION[$h_passw] = htmlspecialchars($_POST[$passw ]);}

  if (isset($_SESSION[$h_passw]) && $_SESSION[$h_passw] != "") {
    $HAL_PASSWD = $_SESSION[$h_passw];
  }else{
    include('./Zip2HAL_Form.php');
    die();
  }
}
?>
<html lang="fr">
<body>
<?php
if (isset($_GET['Id']) && ($_GET['Id'] != ""))
{
  $idNomfic = $_GET['Id'];
}else{
  if (isset($_GET['DOI']) && ($_GET['DOI'] != ""))
  {
    $doi = $_GET['DOI'];
  }else{
    header('Location: Zip2HAL.php');
    exit;
  }
}

if (isset($_GET[$cstPO]) && ($_GET[$cstPO] != ""))
{
	$portail = $_GET[$cstPO];
}

$obo = "";
if (isset($_GET['partDep']) && ($_GET['partDep'] != ""))
{
	$partDep = $_GET['partDep'];
	$tabDep = explode(';', $partDep);
	//On-Behalf-Of: login|jonchere;login|otroccaz
	foreach($tabDep as $elt) {
		$obo .= "login|".$elt.";";
	}
	$obo = substr($obo, 0, -1);
}

//Pour visualiser résultat preprod > https://univ-rennes1.halpreprod.archives-ouvertes.fr/halid

/*
$url = "https://api-preprod.archives-ouvertes.fr/sword/hal/";
$urlStamp = "https://api-preprod.archives-ouvertes.fr/";
*/
$url = "https://api.archives-ouvertes.fr/sword/hal/";
$urlStamp = "https://api.archives-ouvertes.fr/";

$nomfic = "./XML/".$idNomfic.".xml";
$nomficFin = "./XML/".$idNomfic."-Fin.xml";
copy($nomfic, $nomficFin);
  
//suppression éventuel noeud <listBibl type="references">
$xml = new DOMDocument( "1.0", "UTF-8" );
$xml->formatOutput = true;
$xml->preserveWhiteSpace = false;
$xml->load($nomfic);

$gpElts = $xml->documentElement;
$elts = $xml->getElementsByTagName("listBibl");

foreach($elts as $elt) {
  if ($elt->hasAttribute("type")) {
    $quoi = $elt->getAttribute("type");
    if ($quoi == "references") {
      $parent = $elt->parentNode; 
      $newXml = $parent->removeChild($elt);
      $xml->save($nomficFin);
    }
  }
}

//vérification validité des collections renseignées
$eltASup = array();
$elts = $xml->getElementsByTagName("idno");
foreach($elts as $elt) {
  if ($elt->hasAttribute("type")) {
    $quoi = $elt->getAttribute("type");
    if ($quoi == "stamp") {
      $coll = $elt->getAttribute("n");
      $contents = file_get_contents($urlStamp.'search/?q=collCode_s:"'.$coll.'"');
      $contents = mb_convert_encoding($contents, 'UTF-8', 'ISO-8859-1');
      $results = json_decode($contents);
      $numFound = $results->response->numFound;
      //echo $coll." - ".$numFound.'<br>';
      if ($numFound == 0) {
        $eltASup[] = $elt;
      }
    }
  }
}
foreach($eltASup as $elt) {
  $elt->parentNode->removeChild($elt);
}
$xml->save($nomficFin);

$xmlContenu = $xml->saveXML();//Nécessaire pour le mime-type soit bien considéré comme du text/xml

$ENDPOINTS_RESPONDER["TIMOUT"] = 20;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
if (isset ($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")	{
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");
}else{
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
}
curl_setopt($ch, CURLOPT_VERBOSE, 1);
$headers=array();
$headers[] = "Packaging: http://purl.org/net/sword-types/AOfr";
$headers[] = "Content-Type: text/xml";
//Métadonnées > Notice
$elts = $xml->getElementsByTagName("ref");
foreach($elts as $elt) {
	if($elt->hasAttribute("type") && $elt->getAttribute("type") == "file" && $elt->hasAttribute("target")) {
		//$headers[] = "X-Allow-Completion : false";
	}
}
//$headers[] = "Authorization: Basic";
if (isset($doi)) {
  $headers[] = "X-Allow-Completion[".$doi."]";
}
//Si partage du dépôt
if ($obo != "") {
		$headers[] = "On-Behalf-Of: ".$obo;
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_USERPWD, ''.$HAL_USER.':'.$HAL_PASSWD.'');
curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlContenu);
//var_dump($headers);
$return = curl_exec($ch);
//print_r($return);

$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Code retour http : ".$httpcode."<br>";
if(!$return)
{
  if ($httpcode == 401) {
    $errStr="Problème d'authentification, mot de passe incorrect.\n";
  }else{
    $errStr="Problème avec l'API sword, contactez le support technique (erreur http=$httpcode)";
  }
  //exit ("ERREUR : ".$art->getCle()." : ".$errStr);
  exit ("ERREUR : ".$errStr);
}

try {
  $entry = new SimpleXMLElement($return);
  $entry->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
  $entry->registerXPathNamespace('sword', 'http://purl.org/net/sword/terms');
  $entry->registerXPathNamespace('hal', 'http://hal.archives-ouvertes.fr/');
	//var_dump($entry);
  if (in_array($httpcode, array(200, 201, 202))) {
    $id = $entry->id;
    $pwdRes=$entry->xpath('hal:password');
    if (!empty($pwdRes) && is_array($pwdRes) && !empty($pwdRes[0][0]))
    {
      $pss=$pwdRes[0][0];
    }
    else {
      $pss='Unknown';
    }
    $link="unknown";
    $linkAttribute=$entry->link->attributes();
    if (isset($linkAttribute) && @count($linkAttribute) > 0) {
      if (!empty($linkAttribute) && isset($linkAttribute['href']) && !empty($linkAttribute['href'])) {
        $link = "<a target='_blank' href='".$linkAttribute['href']."'>prod</a>";
        $linkpreprod = "<a target='_blank' href='https://univ-rennes1.halpreprod.archives-ouvertes.fr/'>preprod</a>";
      }
    }
    //exit ("<b>OK, modification effectuée :</b> id=>$id,passwd=>$pss,link=> $link ou $linkpreprod \n");
    //exit ("<b>OK, modification effectuée :</b> id=>$id,passwd=>$pss,link=> $link \n");
		
		//Récupération du halid pour stockage dans le fichier de statistiques
		$halId = str_replace(array("https://hal.archives-ouvertes.fr/", "v1"), "", $linkAttribute['href']);
		$Fnm = "./Zip2HAL_actions.php";
		include $Fnm;
		$inF = fopen($Fnm,"w");
		fseek($inF, 0);
		$chaine = "";
		$chaine .= '<?php'.chr(13);
		$chaine .= '$ACTIONS_LISTE = array('.chr(13);
		fwrite($inF,$chaine);
		foreach($ACTIONS_LISTE AS $i => $valeur) {
			$chaine = $i.' => array(';
			$chaine .= '"quand"=>"'.$ACTIONS_LISTE[$i]["quand"].'", ';
			$chaine .= '"team"=>"'.$ACTIONS_LISTE[$i]["team"].'", ';
			$chaine .= '"'.$cstVal.'"=>"'.$ACTIONS_LISTE[$i][$cstVal].'", ';
			$chaine .= '"titre"=>"'.$ACTIONS_LISTE[$i]["titre"].'", ';
			$chaine .= '"type"=>"'.$ACTIONS_LISTE[$i]["type"].'", ';
			$chaine .= '"annee"=>"'.$ACTIONS_LISTE[$i]["annee"].'", ';
			$chaine .= '"login"=>"'.$ACTIONS_LISTE[$i]["login"].'", ';
			if ($ACTIONS_LISTE[$i][$cstVal] == $idNomfic.".xml") {
				$chaine .= '"'.$cstID.'"=>"'.$halId.'")';
			}else{
				$chaine .= '"'.$cstID.'"=>"'.$ACTIONS_LISTE[$i][$cstID].'")';
			}
			//if ($i != $total-1) {$chaine .= ',';}
			$chaine .= chr(13);
			//session 6 mois test
			$hier = time() - 15552000;
			if ($ACTIONS_LISTE[$i]["quand"] > $hier) {
				fwrite($inF,$chaine);
			}else{
				$i -= 1;
			}
		}
		$chaine = ');'.chr(13);
		$chaine .= '?>';
		fwrite($inF,$chaine);
		fclose($inF);
		array_multisort($ACTIONS_LISTE, SORT_DESC);
		
		header("Location: ".$linkAttribute['href']);
  } else {
    //var_dump($return);
    $err = $entry->xpath('/sword:error/sword:verboseDescription');
    $summaries = $entry->xpath('/atom:summary/sword:error');
    //var_dump($summaries[0]);
    exit ("ERREUR : Pb sword : ".$err[0][0]."\n");
  }
} catch (Exception $e) {
  return ("ERREUR : Erreur Web service  : ".$e->getMessage()."\n");
}

curl_close($ch);

?>
</body>
</html>

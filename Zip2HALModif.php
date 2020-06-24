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
  <link rel="stylesheet" href="<?php echo $css;?>" type="text/css">
  <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="icon" type="type/ico" href="HAL_favicon.ico">
  <link rel="stylesheet" href="./Zip2HAL.css">
</head>
<body>
<table class="table100" aria-describedby="Entêtes">
<tr>
<th scope="col" style="text-align: left;"><img alt="Zip2HAL" title="Zip2HAL" width="250px" src="./img/logo_Zip2hal.png"></td>
<th scope="col" style="text-align: right;"><img alt="Université de Rennes 1" title="Université de Rennes 1" width="150px" src="./img/logo_UR1_gris_petit.jpg"></td>
</tr>
</table>
<br><br>
<div style="width: 60%; margin-left: auto ;margin-right: auto ;">
<?php
if (strpos(phpversion(), "7") !== false) {//PHP7 > Possibilité d'utiliser la classe CURLFile
	function curlFile($file, $idNomfic) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		return new CURLFile($file, finfo_file($finfo, $file), substr(strrchr($idNomfic, "/"), 1).".xml");
	}
}

$passw = 'password';
$h_passw = 'HAL_PASSWD';

//require_once('./CAS_connect.php')//authentification CAS ou autre ?
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
  if (isset($_POST[$passw ]) && $_POST[$passw ] != "") {$_SESSION[$h_passw] = htmlspecialchars($_POST[$passw ]);}

  if (isset($_SESSION[$h_passw]) && $_SESSION[$h_passw] != "") {
    $HAL_PASSWD = $_SESSION[$h_passw];
  }else{
    include('./Zip2HALForm.php');
    die();
  }
}

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
      $contents = utf8_encode($contents);
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

/*
//Création d'une archive zip pour obtenir un mime-type application/zip acceptable par HAL
$zip = new ZipArchive();
$Fname = "./XML/".$idNomfic.".zip";
if ($zip->open($Fname, ZipArchive::CREATE)!==TRUE) {
	die("Impossible d'ouvrir le fichier ".$Fname);
}
$zip->addFile($nomficFin, substr(strrchr($idNomfic, "/"), 1).".xml");
//echo "Nombre de fichiers : " . $zip->numFiles . "\n";
//echo "Statut :" . $zip->status . "\n";
$zip->close();
*/

$ENDPOINTS_RESPONDER["TIMOUT"] = 20;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
//$attach = substr(strrchr($idNomfic, "/"), 1).".xml";
$headers=array();
$headers[] = "Packaging: http://purl.org/net/sword-types/AOfr";
$headers[] = "Content-type: application/xml";
//$headers[] = "Content-Disposition: attachment; filename=\"".$attach."\"";
//$headers[] = "Authorization: Basic";
if (isset($doi)) {
  $headers[] = "X-Allow-Completion[".$doi."]";
}
curl_setopt($ch, CURLOPT_USERPWD, ''.$HAL_USER.':'.$HAL_PASSWD.'');
//var_dump($headers);
//var_dump(curlFile($nomficFin, $idNomfic));
//curl_setopt($ch, CURLOPT_POSTFIELDS, array('file' => curlFile($Fname, $idNomfic)));
if (strpos(phpversion(), "7") !== false) {//PHP7
	curl_setopt($ch, CURLOPT_POSTFIELDS, array('file' => curlFile($nomficFin, $idNomfic)));
}else{//PHP5
	//curl_setopt($ch, CURLOPT_SAFE_UPLOAD, 1);
	$data = array(
    'uploaded_file' => '@'.realpath($nomficFin).';type=application/xml;filename='.substr(strrchr($idNomfic, "/"), 1).".xml",
	);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

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
</div>
</body>
</html>

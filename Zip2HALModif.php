<?php
header('Content-type: text/html; charset=UTF-8');

if (isset($_GET['css']) && ($_GET['css'] != ""))
{
  $css = $_GET['css'];
}else{
  $css = "https://ecobio.univ-rennes1.fr/HAL_SCD.css";
}
?>
<html>
<head>
  <title>CrosHAL</title>
  <meta name="Description" content="CrosHAL">
  <link rel="stylesheet" href="<?php echo($css);?>" type="text/css">
  <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="icon" type="type/ico" href="HAL_favicon.ico">
  <link rel="stylesheet" href="./CrosHAL.css">
</head>
<body>
<table width="100%">
<tr>
<td style="text-align: left;"><img alt="ExtrHAL" title="ExtrHAL" width="250px" src="./img/logo_Croshal.png"></td>
<td style="text-align: right;"><img alt="Université de Rennes 1" title="Université de Rennes 1" width="150px" src="./img/logo_UR1_gris_petit.jpg"></td>
</tr>
</table>
<br><br>
<div style="width: 60%; margin-left: auto ;margin-right: auto ;">
<?php
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
  if (isset($_POST['password']) && $_POST['password'] != "") {$_SESSION['HAL_PASSWD'] = htmlspecialchars($_POST['password']);}

  if (isset($_SESSION['HAL_PASSWD']) && $_SESSION['HAL_PASSWD'] != "") {
    $HAL_PASSWD = $_SESSION['HAL_PASSWD'];
  }else{
    include('./Zip2HALForm.php');
    die();
  }
}

if (isset($_GET['Id']) && ($_GET['Id'] != ""))
{
  //$halid = "hal-01179051";
  $halid = $_GET['Id'];
}else{
  if (isset($_GET['DOI']) && ($_GET['DOI'] != ""))
  {
    //$halid = "hal-01179051";
    $doi = $_GET['DOI'];
  }else{
    header('Location: CrosHAL.php');
    exit;
  }
}

//Pour visualiser résultat preprod > https://univ-rennes1.halpreprod.archives-ouvertes.fr/halid

/*
$url = "https://api-preprod.archives-ouvertes.fr/sword/";
$urlStamp = "https://api-preprod.archives-ouvertes.fr/";
*/
$url = "https://api.archives-ouvertes.fr/sword/";
$urlStamp = "https://api.archives-ouvertes.fr/";

/*
if ($_GET['action'] == "MAJ") {
  $nomfic = "./XML/".$halid.".xml";
  $nomficFin = "./XML/".$halid."-Fin.xml";
  copy($nomfic, $nomficFin);
}
if ($_GET['action'] == "PDF") {
  $nomfic = "./XML/".$halid."_PDF.xml";
  $nomficFin = "./XML/".$halid."_PDF-Fin.xml";
  copy($nomfic, $nomficFin);
}
*/

$nomfic = "./XML/".$halid.".xml";
$nomficFin = "./XML/".$halid."-Fin.xml";
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

$fp = fopen($nomficFin, "r");
$ENDPOINTS_RESPONDER["TIMOUT"] = 20;

$ch = curl_init($url.$halid);
curl_setopt($ch, CURLOPT_PUT, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
$headers=array();
$headers[] = "Packaging: http://purl.org/net/sword-types/AOfr";
$headers[] = "Content-Type: text/xml";
//$headers[] = "Authorization: Basic";
if (isset($doi)) {
  $headers[] = "X-Allow-Completion[".$doi."]";
}
curl_setopt($ch, CURLOPT_USERPWD, ''.$HAL_USER.':'.$HAL_PASSWD.'');
//var_dump($headers);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_INFILE, $fp);
curl_setopt($ch, CURLOPT_INFILESIZE, filesize($nomficFin));
curl_setopt($ch, CURLOPT_UPLOAD, TRUE);

$return = curl_exec($ch);
//var_dump($return);

$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Code retour http : ".$httpcode."<br>";
if($return == FALSE)
{
  if ($httpcode == 401) {
    $errStr="Problème d'authentification, mot de passe incorrect.\n";
  }else{
    $errStr="Problème avec l'API sword, contactez le support technique (erreur http=$httpcode)";
  }
  //exit ("ERREUR : ".$art->getCle()." : ".$errStr);
  exit ("ERREUR : ".$errStr);;
}
try {
  $entry = new SimpleXMLElement($return);
  $entry->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
  $entry->registerXPathNamespace('sword', 'http://purl.org/net/sword/terms');
  $entry->registerXPathNamespace('hal', 'http://hal.archives-ouvertes.fr/');
  if (in_array($httpcode, array(200, 201, 202))) {
    $id = $entry->id;
    $passwdRes=$entry->xpath('hal:password');
    if (!empty($passwdRes) and is_array($passwdRes) and !empty($passwdRes[0][0]))
    {
      $passwd=$passwdRes[0][0];
    }
    else {
      $passwd='Unknown';
    }
    $link="unknown";
    $linkAttribute=$entry->link->attributes();
    if (isset($linkAttribute) && @count($linkAttribute) > 0) {
      if (!empty($linkAttribute) && isset($linkAttribute['href']) && !empty($linkAttribute['href'])) {
        $link = "<a target='_blank' href='".$linkAttribute['href']."'>prod</a>";
        $linkpreprod = "<a target='_blank' href='https://univ-rennes1.halpreprod.archives-ouvertes.fr/".$halid."'>preprod</a>";
      }
    }
    //exit ("<b>OK, modification effectuée :</b> id=>$id,passwd=>$passwd,link=> $link ou $linkpreprod \n");
    //exit ("<b>OK, modification effectuée :</b> id=>$id,passwd=>$passwd,link=> $link \n");
		if (isset($_GET['etp']) && ($_GET['etp'] == 1))
		{
			echo('<script type="text/javascript">');
			echo('setTimeout(window.close,1000);');
			echo('</script>');
		} else {
			header("Location: ".$linkAttribute['href']);
		}
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

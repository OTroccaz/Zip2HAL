<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "https://www.w3.org/TR/html4/loose.dtd">
<?php
header('Content-type: text/html; charset=UTF-8');

if (isset($_GET['css']) && ($_GET['css'] != ""))
{
  $css = $_GET['css'];
}else{
  $css = "https://ecobio.univ-rennes1.fr/HAL_SCD.css";
}

include("./Zip2HAL_actions.php");
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

<noscript>
<div align='center' id='noscript'><font color='red'><b>ATTENTION !!! JavaScript est désactivé ou non pris en charge par votre navigateur : cette procédure ne fonctionnera pas correctement.</b></font><br>
<b>Pour modifier cette option, voir <a target='_blank' rel='noopener noreferrer' href='https://www.libellules.ch/browser_javascript_activ.php'>ce lien</a>.</b></div><br>
</noscript>

<div id='content'></div>

<table width="100%">
<tr>
<td style="text-align: left;"><img alt="Zip2HAL" title="Zip2HAL" width="250px" src="./img/logo_Zip2hal.png"></td>
<td style="text-align: right;"><img alt="Université de Rennes 1" title="Université de Rennes 1" width="150px" src="./img/logo_UR1_gris_petit.jpg"></td>
</tr>
</table>
<hr style="color: #467666; height: 1px; border-width: 1px; border-top-color: #467666; border-style: inset;">

Zip2HAL statistiques

<table class='table table-striped table-bordered table-hover;'>
<tr>
<td style='text-align: center; background-color: #eeeeee; color: #999999;'><b>Date du dépôt</b></td>
<td style='text-align: center; background-color: #eeeeee; color: #999999;'><b>Nom du fichier XML</b></td>
<td style='text-align: center; background-color: #eeeeee; color: #999999;'><b>Titre</b></td>
<td style='text-align: center; background-color: #eeeeee; color: #999999;'><b>Type</b></td>
<td style='text-align: center; background-color: #eeeeee; color: #999999;'><b>Année</b></td>
<td style='text-align: center; background-color: #eeeeee; color: #999999;'><b>IdHAL</b></td>
<td style='text-align: center; background-color: #eeeeee; color: #999999;'><b>Lien HAL</b></td>
</tr>

<?php
foreach($ACTIONS_LISTE as $act) {
	echo('<tr>');
	echo('<td>'.date("d/m/Y", $act["quand"]).'</td>');
	echo('<td>'.$act["valeur"].'</td>');
	echo('<td>'.$act["titre"].'</td>');
	echo('<td>'.$act["type"].'</td>');
	echo('<td>'.$act["annee"].'</td>');
	echo('<td>'.$act["idHAL"].'</td>');
	if($act["idHAL"] != "") {
		echo('<td><a target="_blank" href="https://hal.archives-ouvertes.fr/'.$act["idHAL"].'">Lien HAL</a></td>');
	}else{
		echo('<td>&nbsp;</td>');
	}
	echo('</tr>');
}
?>

</table>

<?php
include('./bas.php');
?>
</body>
</html>
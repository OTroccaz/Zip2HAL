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

//Définir des constantes au lieu de dupliquer des littéraux
$cstID = "idhal";
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
<div align='center' id='noscript'class='red'><strong>ATTENTION !!! JavaScript est désactivé ou non pris en charge par votre navigateur : cette procédure ne fonctionnera pas correctement.</strong><br>
<strong>Pour modifier cette option, voir <a target='_blank' rel='noopener noreferrer' href='https://www.libellules.ch/browser_javascript_activ.php'>ce lien</a>.</strong></div><br>
</noscript>

<div id='content'></div>

<table class="table100" aria-describedby="Entêtes">
<tr>
<th scope="col" style="text-align: left;"><img alt="Zip2HAL" title="Zip2HAL" width="250px" src="./img/logo_Zip2hal.png"></td>
<th scope="col" style="text-align: right;"><img alt="Université de Rennes 1" title="Université de Rennes 1" width="150px" src="./img/logo_UR1_gris_petit.jpg"></td>
</tr>
</table>
<hr style="color: #467666; height: 1px; border-width: 1px; border-top-color: #467666; border-style: inset;">

Statistiques Zip2HAL
<br>
<br>
<table class='table table-striped table-bordered table-hover;' aria-describedby='Statistiques Zip2HAL'>
<tr>
<th scope='col' style='text-align: center; background-color: #eeeeee; color: #999999;'><strong>Date du dépôt</strong></td>
<th scope='col' style='text-align: center; background-color: #eeeeee; color: #999999;'><strong>Nom du fichier XML</strong></td>
<th scope='col' style='text-align: center; background-color: #eeeeee; color: #999999;'><strong>Titre</strong></td>
<th scope='col' style='text-align: center; background-color: #eeeeee; color: #999999;'><strong>Type</strong></td>
<th scope='col' style='text-align: center; background-color: #eeeeee; color: #999999;'><strong>Année</strong></td>
<th scope='col' style='text-align: center; background-color: #eeeeee; color: #999999;'><strong>IdHAL</strong></td>
<th scope='col' style='text-align: center; background-color: #eeeeee; color: #999999;'><strong>Lien HAL</strong></td>
</tr>

<?php
foreach($ACTIONS_LISTE as $act) {
	echo '<tr>';
	echo '<td>'.date("d/m/Y", $act["quand"]).'</td>';
	echo '<td>'.$act["valeur"].'</td>';
	echo '<td>'.$act["titre"].'</td>';
	echo '<td>'.$act["type"].'</td>';
	echo '<td>'.$act["annee"].'</td>';
	echo '<td>'.$act[$cstID].'</td>';
	if($act[$cstID] != "") {
		echo '<td><a target="_blank" href="https://hal.archives-ouvertes.fr/'.$act[$cstID].'">Lien HAL</a></td>';
	}else{
		echo '<td>&nbsp;</td>';
	}
	echo '</tr>';
}
?>

</table>

<?php
include('./bas.php');
?>
</body>
</html>
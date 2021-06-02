<!DOCTYPE html>
<?php
//authentification CAS ou autre ?
if(strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false || strpos($_SERVER['HTTP_HOST'], 'ecobio') !== false) {
  include('./_connexion.php');
  $HAL_USER = $user;
  $HAL_PASSWD = $pass;
}else{
  require_once('./CAS_connect.php');
	$HAL_USER = phpCAS::getUser();
	$HAL_QUOI = "Zip2HAL";
	if($HAL_USER != "jonchere" && $HAL_USER != "otroccaz") {include('./Stats_listes_HALUR1.php');}
}

//Avant tout, vérification de l'étape de chargement du fichier zip des TEI xml OverHAL
if(isset($_GET['nomficZip'])) {
	$nomficZip = $_GET['nomficZip'];
}else{
	header('Location: '.'Zip2HAL_TEI_OverHAL.php?erreur=6');
}

//Si le fichier a été supprimé
if(!isset($nomficZip) || !file_exists($nomficZip)) {
	header('Location: '.'Zip2HAL_TEI_OverHAL.php?erreur=7');
}

//Si le dossier lié à l'archive a plus d'un jour, on supprime tout et on relance
if(isset($nomficZip)) {
	$dosarc = str_replace(array("Zip2HAL_TEI_OverHAL_", ".zip"), "", $nomficZip)."/";
	$ageElem = time() - filemtime($dosarc);
	if($ageElem > 86400) {//Si dossier de plus d'un jour
		$handle = opendir($dosarc);//Suppression des fichiers du dossier
		while($elem = readdir($handle)) {
			if(is_dir($dosarc.$elem) || substr($elem, -2, 2) === '..' || substr($elem, -1, 1) === '.') {
			}else{
				unlink($dosarc.$elem);
			}
		}
		rmdir($dosarc);//Suppression du dossier
		unlink($nomficZip);//Suppression de l'archive
		header('Location: '.'Zip2HAL_TEI_OverHAL.php?erreur=7');
	}
}

register_shutdown_function(function() {
    $error = error_get_last();

    if($error['type'] === E_ERROR && strpos($error['message'], 'Maximum execution time of') === 0) {
        echo "<br><strong><font color='red'>Le script a été arrêté car son temps d'exécution dépasse la limite maximale autorisée.</font></strong><br>";
    }
});
include "./Zip2HAL_nodes.php";
include "./Zip2HAL_codes_pays.php";
include "./Zip2HAL_codes_langues.php";

include "./Zip2HAL_fonctions.php";

suppression("./XML", 86400);//Suppression des fichiers et dossiers du dossier XML créés il y a plus d'un jour

include("./Glob_normalize.php");
include("./Zip2HAL_URLport_coll.php");
include('./Zip2HAL_DOMValidator.php');

$brk = '<br><br>';
?>

<html lang="fr">
<head>
	<meta charset="utf-8" />
	<title>Zip2HAL - HAL - UR1</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta content="Zip2HAL permet de déposer dans HAL un lot de publications au format TEI HAL (fichier généré par OverHAL)" name="description" />
	<meta content="Coderthemes + Lizuka + OTroccaz + LJonchere" name="author" />
	<!-- App favicon -->
	<link rel="shortcut icon" href="favicon.ico">

	<!-- App css -->
	<link href="./assets/css/icons.min.css" rel="stylesheet" type="text/css" />
	<link href="./assets/css/app-hal-ur1.min.css" rel="stylesheet" type="text/css" id="light-style" />
	<!-- <link href="./assets/css/app-creative-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" /> -->
	
	<!-- third party js -->
	<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="./Zip2HAL.js"></script>
	<!-- third party js end -->
	
	<!-- third party css -->
	<!-- <link href="./assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" /> -->
	<link rel="stylesheet" href="./Zip2HAL.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css" type="text/css">
	<!-- third party css end -->
	
	<!-- bundle -->
	<script src="./assets/js/vendor.min.js"></script>
	<script src="./assets/js/app.min.js"></script>

	<!-- third party js -->
	<!-- <script src="./assets/js/vendor/Chart.bundle.min.js"></script> -->
	<!-- third party js ends -->
	<script src="./assets/js/pages/hal-ur1.chartjs.js"></script>
		
</head>
		
<body class="loading" data-layout="topnav">

<noscript>
<div class='text-primary' id='noscript'><strong>ATTENTION !!! JavaScript est désactivé ou non pris en charge par votre navigateur : cette procédure ne fonctionnera pas correctement.</strong><br>
<strong>Pour modifier cette option, voir <a target='_blank' rel='noopener noreferrer' href='http://www.libellules.ch/browser_javascript_activ.php'>ce lien</a>.</strong></div><br>
</noscript>

<?php
$team = "";//Code collection HAL
$racine = "";//Portail de dépôt
$partDep = "";//Partage de dépôt = Déposer pour un autre compte
$domaine = "";//Domaine disciplinaire
$soumis = "soumis";

if(isset($_POST[$soumis])) {
	$team = htmlspecialchars($_POST["team"]);
	if($team == "Entrez le code de votre collection") {//Code collection non renseigné > on en met un par défaut > pas obligatoire apparemment
		//$team = "ECOBIO";
		$team = "";
	}
	$racine = htmlspecialchars($_POST["racine"]);
	$partDep = htmlspecialchars($_POST["partDep"]);
	if(isset($_POST["domaine"])) {$domaine = htmlspecialchars($_POST["domaine"]);}
}

if(isset($nomficZip)) {
	echo '<form method="POST" accept-charset="utf-8" name="zip2hal" action="Zip2HAL.php?nomficZip='.$nomficZip.'">';
}else{
	echo '<form method="POST" accept-charset="utf-8" name="zip2hal" action="Zip2HAL.php">';
}

if($racine == "") {$racine = "https://hal-univ-rennes1.archives-ouvertes.fr/";}
?>

        <!-- Begin page -->
        <div class="wrapper">

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">
								
								<?php
								include "./Glob_haut.php";
								?>
								
								<!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb bg-light-lighten p-2">
                                                <li class="breadcrumb-item"><a href="index.php"><i class="uil-home-alt"></i> Accueil HALUR1</a></li>
                                                <li class="breadcrumb-item active" aria-current="page">Zip2<span class="font-weight-bold">HAL</span></li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <h4 class="page-title">Importez vos publications dans HAL</h4>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-xl-8 col-lg-6 d-flex">
                                <!-- project card -->
                                <div class="card d-block w-100 shadow-lg">
                                    <div class="card-body">
                                        
                                        <!-- project title-->
                                        <h2 class="h1 mt-0">
                                            <i class="mdi mdi mdi-folder-zip-outline text-primary"></i>
                                            <span class="font-weight-light">Zip2</span><span class="text-primary">HAL</span>
                                        </h2>
                                        <h5 class="badge badge-primary badge-pill">Présentation</h5>
																				
																				<img src="./img/nasa-galaxy.jpg" alt="Accueil Zip2HAL" class="img-fluid"><br>
																				<p class="font-italic">Photo : Galaxy by Nasa on Unsplash (détail)</p>

                                        <p class=" mb-2 text-justify">
                                           Zip2HAL permet de déposer dans HAL un lot de publications au format TEI HAL (fichier généré par OverHAL). Ce script a été créé par Olivier Troccaz (conception-développement) et Laurent Jonchère (conception).
                                        </p>
																				
																				<p class="mb-4">
                                            Contacts : <a target='_blank' rel='noopener noreferrer' href="https://openaccess.univ-rennes1.fr/interlocuteurs/laurent-jonchere">Laurent Jonchère</a> (Université de Rennes 1) / <a target='_blank' rel='noopener noreferrer' href="https://openaccess.univ-rennes1.fr/interlocuteurs/olivier-troccaz">Olivier Troccaz</a> (CNRS CReAAH/OSUR).
                                        </p>

                                    </div> <!-- end card-body-->
                                    
                                </div> <!-- end card-->

                            </div> <!-- end col -->
                            <div class="col-lg-6 col-xl-4 d-flex">
                                <div class="card shadow-lg w-100">
                                    <div class="card-body">
                                        <h5 class="badge badge-primary badge-pill">Mode d'emploi</h5>
																				<div class=" mb-2">
																						<ul class="list-group">
																								<li class="list-group-item">
																										<a target="_blank" rel="noopener noreferrer" href="https://halur1.univ-rennes1.fr/Zip2HAL_Tutoriel.pdf"><i class="mdi mdi-file-pdf-box-outline mr-1"></i> Tutoriel</a>
																								</li>
                                            </ul> 
                                        </div>
                                    </div>
                                </div>
                                <!-- end card-->
                            </div>
                        </div>
                        <!-- end row -->

                        <div class="row">
                            <div class="col-12 d-flex">
                                <!-- project card -->
                                <div class="card w-100 d-block shadow-lg">
                                    <div class="card-body">
                                        
                                        <h5 class="badge badge-primary badge-pill">Paramétrage</h5>
																				
																				<div class="form-group row mb-2">
																						<label class="col-12 col-md-2 col-form-label font-weight-bold" for="racine">Portail de dépôt :</label>

																						<div class="col-12 col-md-4">
																								<?php
																								$colPort = array();
																								$urlPort = "https://api.archives-ouvertes.fr/ref/instance";
																								$contents = file_get_contents($urlPort);
																								$results = json_decode($contents);
																								foreach($results->response->docs as $entry) {
																									//$colPort[$entry->url."/"] = $entry->name;
																									$colPort[] = $entry->url."/";
																								}
																								array_multisort($colPort, SORT_ASC);
																								?>
																								<select id="racine" class="custom-select" name="racine">
																								<?php
																								for ($i=0; $i < count($colPort); $i++) {
																									if($racine == $colPort[$i]) {$txt = "selected";}else{$txt = "";}
																									echo '<option '.$txt.' value="'.$colPort[$i].'">'.$colPort[$i].'</option>';
																								}
																								?>
																								</select>
																						</div>
																				</div> <!-- .form-group -->
																				
																				<div class="form-group row mb-2">
																				
																						<label for="partDep" class="col-12 col-md-2 col-form-label font-weight-bold">
																						Partager ce dépôt avec :
																						</label>
																						
																						<div class="col-12 col-md-5">
																								<div class="input-group">
																										<div class="input-group-prepend">
																												<button type="button" tabindex="0" class="btn btn-info" data-html="true" data-toggle="popover" data-trigger="focus" title="" data-content='Renseignez le login - Plusieurs valeurs possibles, séparées par un point-virgule : login1;login2;etc.' data-original-title="">
																												<i class="mdi mdi-comment-question text-white"></i>
																												</button>
																										</div>
																										<input type="text" id="partDep" name="partDep" class="form-control"  value="<?php echo $partDep;?>">
																								</div>
																						</div>
																				
																				</div> <!-- .form-group -->
																				
																				<div class="form-group row mb-2">
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
																						<label for="team" class="col-12 col-md-2 col-form-label font-weight-bold">
																						Code collection HAL :
																						</label>

																						<div class="col-12 col-md-5">
																								<div class="input-group">
																										<div class="input-group-prepend">
																												<button type="button" tabindex="0" class="btn btn-info" data-html="true" data-toggle="popover" data-trigger="focus" title="" data-content='Code visible dans l’URL d’une collection.
																						Exemple : IPR-MOL est le code de la collection http://hal.archives-ouvertes.fr/ <span class="font-weight-bold">IPR-PMOL</span> de l’équipe Physique moléculaire de l’unité IPR UMR CNRS 6251' data-original-title="">
																												<i class="mdi mdi-comment-question text-white"></i>
																												</button>
																										</div>
																										<input type="text" id="team" name="team" class="form-control"  value="<?php echo $team1;?>" onClick="this.value='<?php echo $team2;?>';">
																								<a class="ml-2 small" target="_blank" rel="noopener noreferrer" href="https://hal-univ-rennes1.archives-ouvertes.fr/page/codes-collections">Trouver le code<br>de mon équipe / labo</a>
																								</div>
																						</div>
																				</div> <!-- .form-group -->
																				
																				<div class="form-group row mb-2">
																						<span class="col-12 col-md-2 col-form-label font-weight-bold pt-0">Domaine disciplinaire : </span>
																						<?php
																						if($domaine == "") {
																							if(isset($_POST[$soumis])) {
																								echo '-';
																							}else{
																								$endSpan = '</span>';
																								echo '<span id="domaine" class="d-none;">'.$endSpan;
																								echo '<div id="choixdom" class="col-10">';
																								//echo '&nbsp;si vous connaissez une partie du code, utilisez le champ ci-dessous puis validez avec le bouton vert, autrement, l\'arborescence dynamique ci-après.';
																								echo '	<div class="form-group row mb-2">';
																								echo '		<div class="col-12 col-md-6">';
																								echo '			<input type="text" id="inputdom" name="inputdom" class="autoDO form-control ui-autocomplete-input" autocomplete="off">';
																								echo '		</div>';
																								echo '		<div class="col-12 col-md-2">';
																								echo '			<span class="btn btn-success" onclick="choixdom($(\'#inputdom\').val(),\'\');"><i class="mdi mdi-check-outline"></i></span>';
																								echo '		</div>';
																								echo '	<br>';
																								echo '	</div>';
																								echo '	<div class="form-group row mb-2">';
																								echo '		<div class="col-12 small text-primary">';
																								echo '		Si vous connaissez une partie du code, utilisez le champ ci-dessus puis validez avec le bouton vert, autrement, l\'arborescence dynamique ci-après.';
																								echo '		</div>';
																								echo '	</div>';

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
																										if($cpt != 1) {echo $endSpan;}
																										$domF = str_replace("'", "’", $entry->fr_domain_s);
																										echo '<span style=\'margin-left: 30px;\' id=\'cod-'.$cpt.'-0\'><a style=\'cursor:pointer;\' onclick=\'afficacher('.$cpt.','.'0'.');\'><span style=\'color: #FE6D02;\'><strong>>&nbsp;</strong></span></a></span>';
																										echo '<span><a style=\'cursor:pointer;\' onclick=\'choixdom("'.$domF.'","'.$code.'");\'>'.$domF.'</a></span><br>';
																										$codI = $codF;
																										echo '<span id=\'dom-'.$cpt.'-0\' style=\'display:none;\'>';
																										$cpt++;
																									}else{//Liste des différentes sous-matières de la discipline
																										$sMat = str_replace($domF.'/', '', str_replace("'", "’", $entry->fr_domain_s));
																										$sMatVal = str_replace("'", "’", $entry->fr_domain_s);
																										echo '<span style=\'margin-left: 60px;\'><a style=\'cursor:pointer;\' onclick=\'choixdom("'.$sMatVal.'","'.$code.'");\'>'.$sMat.'</a></span><br>';
																									}
																								}
																								echo $endSpan.'</div>';
																							}
																						}else{
																							echo $domaine;
																						}
																						?>
																						
																				</div> <!-- .form-group -->
																				
																				<div class="form-group row mb-2">
																						<div class="col-12">
																								<strong><span>Fichier ZIP TEI OverHAL :&nbsp;</span></strong>
																								<?php
																								if(isset($nomficZip) && file_exists($nomficZip)) {
																									echo $nomficZip;
																								}
																								?>
																						</div>
																				</div> <!-- .form-group -->
																				
																				<div class="form-group row mb-2">
																						<div class="col-12">
																								<?php
																								//Nouvelle soumission d'archive
																								echo '<a href="./Zip2HAL_TEI_OverHAL.php">Nouvelle soumission d\'archive</a>';
																								?>
																						</div>
																				</div> <!-- .form-group -->
																				
																				<div class="form-group row mt-4">
																						<div class="col-12 justify-content-center d-flex">
																								<input type="submit" class="btn btn-md btn-primary btn-lg" value="Valider" name="soumis">
																						</div>
																				</div> <!-- .form-group -->

                                    </div> <!-- end card-body-->
                                    
                                </div> <!-- end card-->

                            </div> <!-- end col -->
                        
												</div> <!-- end row -->
												
												<?php
												if(isset($_POST[$soumis])) {
													$dir = str_replace(array("Zip2HAL_TEI_OverHAL_", ".zip"), "", $nomficZip);
													$tabFic = scandir($dir);
													$idFic = 1;
													foreach($tabFic as $nomfic) {
														if(substr($nomfic, -2, 2) !== '..' && substr($nomfic, -1, 1) !== '.') {	
															echo ' <div class="row">';
															echo '  <div class="col-12 d-flex">';
															echo '      <div class="card shadow-lg w-100">';
															echo '          <div class="card-body">';
													
															$nomfic = $dir."/".$nomfic;

															//Chargement du fichier XML
															$xml = new DOMDocument( "1.0", "UTF-8" );
															$xml->formatOutput = true;
															$xml->preserveWhiteSpace = false;
															$xml->load($nomfic);
															$xml->save($nomfic);
															
															//Récupération du titre, du DOI, éventuellement du PMID et du type de document de la notice TEI
															$titTEI = "";
															$doiTEI = "";
															$pmiTEI = "";
															$typTEI = "";
															$tits = $xml->getElementsByTagName("title");
															foreach($tits as $tit) {
																if($tit->hasAttribute("xml:lang")) {$titTEI = $tit->nodeValue;}
															}
															$idns = $xml->getElementsByTagName("idno");
															foreach($idns as $idn) {
																if($idn->hasAttribute("type") && $idn->getAttribute("type") == 'doi') {$doiTEI = $idn->nodeValue;}
																if($idn->hasAttribute("type") && $idn->getAttribute("type") == 'pubmed') {$pmiTEI = $idn->nodeValue;}
															}
															$typs = $xml->getElementsByTagName("classCode");
															foreach($typs as $typ) {
																if($typ->hasAttribute("scheme") && $typ->getAttribute("scheme") == 'halTypology') {$typTEI = $typ->getAttribute("n");}
															}
															$enctitTEI = mb_strtolower(normalize($titTEI));
															
															//Récupération du premier mot du titre pour limiter la recherche API
															$tabTit = explode(' ', $titTEI);
																
															$portail = $collport[$racine];
															
															//Récupération de l'année de publication
															$anns = $xml->getElementsByTagName("date");
															$datePub = "";
															foreach($anns as $ann) {
																if($ann->hasAttribute("type") && $ann->getAttribute("type") == "datePub") {
																	$datePub = substr($ann->nodeValue, 0, 4);
																	$datePub1 = $datePub - 1;
																	$datePub2 = $datePub + 1;
																}
															}
															if($datePub != "") {$special = "%20AND%20(producedDateY_i:(".$datePub1."%20OR%20".$datePub."%20OR%20".$datePub2.")%20OR%20inPress_bool:true)";}else{$special = "";}
															
															//Quand on a que le titre et pas le DOI, il faut trouver une correspondance exacte sur tout le titre pour considérer qu'il s'agit d'un doublon
															if($doiTEI != "") {
																$critere = strtolower($tabTit[0]."%20".$tabTit[1]."%20".$tabTit[2]);
																$critere = str_replace('%20:', '', $critere);
																$reqAPI = "https://api.archives-ouvertes.fr/search/?fq=title_t:%22".$critere."*%22".$special."&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
															}else{
																$critere = $titTEI;
																$reqAPI = "https://api.archives-ouvertes.fr/search/?fq=title_t:%22".$titTEI."*%22".$special."&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
																$reqAPI = str_replace(" ", "%20", $reqAPI);
															}
															$contents = file_get_contents($reqAPI);
															$results = json_decode($contents);
															$numFound = 0;
															if(isset($results->response->numFound)) {$numFound = $results->response->numFound;}
															
															//Interrogation du référentiel CRAC
															//Quand on a que le titre et pas le DOI, il faut trouver une correspondance exacte sur tout le titre pour considérer qu'il s'agit d'un doublon
															if($doiTEI != "") {
																$reqAPIC = "https://api.archives-ouvertes.fr/crac/hal/?fq=title_t:%22".$critere."*%22".$special."&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
															}else{
																$reqAPIC = "https://api.archives-ouvertes.fr/crac/hal/?fq=title_t:%22".$titTEI."*%22".$special."&rows=10000&fl=halId_s,doiId_s,title_s,subTitle_s,docType_s";
																$reqAPIC = str_replace(" ", "%20", $reqAPIC);
															}
															$contentsC = file_get_contents($reqAPIC);
															$resultsC = json_decode($contentsC);
															$numFoundC = 0;
															if(isset($resultsC->response->numFound)) {$numFoundC = $resultsC->response->numFound;}
															
															echo '<div class="alert alert-secondary mt-3" role="alert">';
															echo '	<strong>Traitement du fichier</strong> '.str_replace($dir."/", "", $nomfic).'</strong><br>';
															echo '</div>';
															
															include('./Zip2HAL_etape1.php');
															
															if(isset($typDbl) && $typDbl != "HALCOLLTYP") {//Pas un doublon de type HAL et COLL
																
																include('./Zip2HAL_etape2.php');
																include('./Zip2HAL_etape3a.php');
																include('./Zip2HAL_etape3b.php');
																include('./Zip2HAL_etape3c.php');
																include('./Zip2HAL_etape3d.php');

																echo $brk.'<span style="display: none;"';
																echo 'Tableau initial obtenu pour les idHAL des auteurs ($halAutinit) :';
																var_dump($halAutinit);
																echo $brk;
																echo 'Tableau final obtenu pour les idHAL des auteurs ($halAut) :';
																var_dump($halAut);
																echo $brk;
																echo 'Tableau des noms des affiliations ($nomAff) :';
																var_dump($nomAff);
																echo $brk;
																echo 'Tableau obtenu pour les id structure des affiliations ($halAff) :';
																var_dump($halAff);
																echo '</span>';


															}
															
															include('./Zip2HAL_premieres_modifications_TEI.php');
															
															include('./Zip2HAL_tableau_resultats.php');
															
															//Vérification si des métadonnées sont manquantes
															if(isset($tabMetaMQ)) {
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
																if($arrayMQ == "oui") {
																	/*
																	echo '<script src="https://code.jquery.com/jquery-3.5.1.js"></script>';
																	echo '<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>';
																	$message = str_replace("\'", "\'", $message);
																	echo '<script>afficherPopupAvertissement("'.$message.'");</script>';
																	*/

																	echo '<div id="warning-alert-modal-'.$idFic.'" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">';
																	echo '    <div class="modal-dialog modal-md modal-center">';
																	echo '        <div class="modal-content">';
																	echo '            <div class="modal-body p-4">';
																	echo '                <div class="text-center">';
																	echo '                    <i class="dripicons-warning h1 text-warning"></i>';
																	echo '                    <h4 class="mt-2">Avertissement</h4>';
																	echo '										<p class="mt-3">'.str_replace("\'", "'", $message).'</p>';
																	echo '                    <button type="button" class="btn btn-warning my-2" data-dismiss="modal">Continuer</button>';
																	echo '                </div>';
																	echo '            </div>';
																	echo '        </div><!-- /.modal-content -->';
																	echo '    </div><!-- /.modal-dialog -->';
																	echo '</div><!-- /.modal -->';
																	
																	echo '<script type="text/javascript">';
																	echo '	(function($) {';
																	echo '			"use strict";';
																	echo '			$("#warning-alert-modal-'.$idFic.'").modal(';
																	echo '					{"show": true, "backdrop": "static"}';
																	echo '							)';
																	echo '	})(window.jQuery)';
																	echo '</script>';
																}
															}
															
															echo '						</div>';
															echo '        </div> <!-- end card-->';
															echo '    </div> <!-- .col -->';
															echo '</div> <!-- .row -->';
														}
														$idFic++;
													}
												}
												?>

                    </div>
                    <!-- container -->

                </div>
                <!-- content -->

								<!--Ajustement automatique des textarea-->
								<script src="./Zip2HAL_autoresize.jquery.js"></script>
								<script>
									$('textarea').autoResize();
								</script>

								<?php
								echo $brk;
								include('./Glob_bas.php');
								?>
								
								</div>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->


        </div>
				
				</form>
				
				<button id="scrollBackToTop" class="btn btn-primary"><i class="mdi mdi-24px text-white mdi-chevron-double-up"></i></button>
        <!-- END wrapper -->

        <!-- bundle -->
        <!-- <script src="./assets/js/vendor.min.js"></script> -->
        <script src="./assets/js/app.min.js"></script>

        <!-- third party js -->
        <!-- <script src="./assets/js/vendor/Chart.bundle.min.js"></script> -->
        <!-- third party js ends -->
        <script src="./assets/js/pages/hal-ur1.chartjs.js"></script>
				
				<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
				<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
				<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css" type="text/css">
				
				<script>
            (function($) {
                'use strict';
                $(document).scroll(function() {
                  var y = $(this).scrollTop();
                  if (y > 200) {
                    $('#scrollBackToTop').fadeIn();
                  } else {
                    $('#scrollBackToTop').fadeOut();
                  }
                });
                $('#scrollBackToTop').each(function(){
                    $(this).click(function(){ 
                        $('html,body').animate({ scrollTop: 0 }, 'slow');
                        return false; 
                    });
                });
            })(window.jQuery)
        </script>

    </body>
</html>
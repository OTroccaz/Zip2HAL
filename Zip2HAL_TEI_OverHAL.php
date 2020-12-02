<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "https://www.w3.org/TR/html4/loose.dtd">

<html lang="fr">
<head>
	<meta charset="utf-8" />
	<title>Zip2HAL - HAL - UR1</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta content="Zip2HAL permet de déposer dans HAL un lot de publications au format TEI HAL (fichier généré par OverHAL)" name="description" />
	<meta content="Coderthemes + Lizuka + OTroccaz + LJonchere" name="author" />
	<!-- App favicon -->
	<link rel="shortcut icon" href="favicon.ico">

	<!-- third party css -->
	<!-- <link href="./assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" /> -->
	<link rel="stylesheet" href="./Zip2HAL.css">
	<!-- third party css end -->

	<!-- App css -->
	<link href="./assets/css/icons.min.css" rel="stylesheet" type="text/css" />
	<link href="./assets/css/app-hal-ur1.min.css" rel="stylesheet" type="text/css" id="light-style" />
	<!-- <link href="./assets/css/app-creative-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" /> -->
	
	<!-- third party js -->
	<!-- <script type="text/javascript" language="Javascript" src="Zip2HAL.js"></script> -->
	<!-- third party js end -->

</head>
		
<body class="loading" data-layout="topnav">

<noscript>
<div class='text-primary' id='noscript'><strong>ATTENTION !!! JavaScript est désactivé ou non pris en charge par votre navigateur : cette procédure ne fonctionnera pas correctement.</strong><br>
<strong>Pour modifier cette option, voir <a target='_blank' rel='noopener noreferrer' href='http://www.libellules.ch/browser_javascript_activ.php'>ce lien</a>.</strong></div><br>
</noscript>

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
                                            Contacts : <a target='_blank' rel='noopener noreferrer' href="https://openaccess.univ-rennes1.fr/interlocuteurs/laurent-jonchere">Laurent Jonchère</a> (Université de Rennes 1) / <a target='_blank' rel='noopener noreferrer' href="https://ecobio.univ-rennes1.fr/personnel.php?qui=Olivier_Troccaz">Olivier Troccaz</a> (CNRS ECOBIO/OSUR).
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

																				<?php
																				if(isset($_GET["erreur"]))
																				{
																					echo '<div id="warning-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">';
																					echo '    <div class="modal-dialog modal-md modal-center">';
																					echo '        <div class="modal-content">';
																					echo '            <div class="modal-body p-4">';
																					echo '                <div class="text-center">';
																					echo '                    <i class="dripicons-warning h1 text-warning"></i>';
																					echo '                    <h4 class="mt-2">Avertissement</h4>';
																					
																					$erreur = $_GET["erreur"];
																					if($erreur == 0) {echo '                    <p class="mt-3">Erreur inconnue !</p></script>';}
																					if($erreur == 1) {echo '                    <p class="mt-3">Le fichier dépasse la limite autorisée par le serveur(fichier php.ini) !</p></script>';}
																					if($erreur == 2) {echo '                    <p class="mt-3">Le fichier dépasse la limite autorisée dans le formulaire HTML !</p></script>';}
																					if($erreur == 3) {echo '                    <p class="mt-3">L\'envoi du fichier a été interrompu pendant le transfert !</p></script>';}
																					if($erreur == 4) {echo '                    <p class="mt-3">Aucun fichier soumis !</p></script>';}
																					if($erreur == 5) {echo '                    <p class="mt-3">Mauvaise extension de fichier !</p></script>';}
																					if($erreur == 6) {echo '                    <p class="mt-3">Vous devez au préalable fournir votre extraction TEI d\'OverHAL !</p></script>';}
																					if($erreur == 7) {echo '                    <p class="mt-3">Le répertoire de dépôt de fichier est automatiquement nettoyé chaque jour et votre fichier ZIP des extractions TEI d\'OverHAL n\'existe plus : vous devez procéder de nouveau à son chargement !  </p>';}
																					if($erreur == 8) {echo '                    <p class="mt-3">Archive ZIP incorrecte !</p></script>';}
																					if($erreur == 9) {echo '                    <p class="mt-3">Au moins un des fichiers de votre archive ZIP n\'a pas l\'extension XML !</p></script>';}
																					
																					echo '                    <button type="button" class="btn btn-warning my-2" data-dismiss="modal">Continuer</button>';
																					echo '                </div>';
																					echo '            </div>';
																					echo '        </div><!-- /.modal-content -->';
																					echo '    </div><!-- /.modal-dialog -->';
																					echo '</div><!-- /.modal -->';
																				}
																				?>
																				
																				<br><br>
																				La première étape consiste à fournir votre extraction TEI d'OverHAL :
																				<br><br>
																				
																				<form enctype="multipart/form-data" action="Zip2HAL_TEI_OverHAL_upload.php" method="post" accept-charset="UTF-8">
    
																						<div class="form-group row mb-1">
																								 <label for="TEI_OverHAL" class="col-12 col-md-4 col-form-label font-weight-bold pt-0">Fichier source des extractions TEI OverHAL (zip) :</label>
																								 <div class="col-12 col-md-4">
																										<input class="form-control" id="TEI_OverHAL" name="TEI_OverHAL" type="file">
																								</div>
																								<div class="col-12 col-md-4">
																										<input type="submit" class="btn btn-md btn-primary" value="Envoyer">
																								</div>
																								
																						</div>

																				</form>
																				
																		</div> <!-- end card-->
																		
																</div> <!-- end card-->
                                
                            </div> <!-- .col -->
                            
                        </div> <!-- .row -->
                       
                    </div>
                    <!-- container -->

                </div> <!-- content -->
								
								<?php
								include('./Glob_bas.php');
								?>
								
						</div> <!-- content -->
						
				</div> <!-- END wrapper -->
				
				<!-- bundle -->
        <script src="./assets/js/vendor.min.js"></script>
        <script src="./assets/js/app.min.js"></script>

        <!-- third party js -->
        <script src="./assets/js/vendor/Chart.bundle.min.js"></script>
        <!-- third party js ends -->
        <script src="./assets/js/pages/hal-ur1.chartjs.js"></script>

				<script type="text/javascript">
						(function($) {
								'use strict';
								$('#warning-alert-modal').modal(
										{'show': true, 'backdrop': 'static'}    
										
												)
						})(window.jQuery)
				</script>

</body>
</html>
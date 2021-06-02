<!DOCTYPE html>
<?php
header('Content-type: text/html; charset=UTF-8');
require_once('./CAS_connect.php');
$action = $_GET['action'];
$id = $_GET['Id'];
$form = "Zip2HAL_Modif.php?action=".$action."&amp;Id=".$id;
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
	<script type="text/javascript" language="Javascript" src="./Zip2HAL.js"></script>
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
	<script src="./assets/js/vendor/Chart.bundle.min.js"></script>
	<!-- third party js ends -->
	<script src="./assets/js/pages/hal-ur1.chartjs.js"></script>
		
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
                                            Contacts : <a target='_blank' rel='noopener noreferrer' href="https://openaccess.univ-rennes1.fr/interlocuteurs/laurent-jonchere">Laurent Jonchère</a> (Université de Rennes 1) / <a target='_blank' rel='noopener noreferrer' href="https://ecobio.univ-rennes1.fr/personnel.php?qui=Olivier_Troccaz">Olivier Troccaz</a> (CNRS CReAAH/OSUR).
                                        </p>

                                    </div> <!-- end card-body-->
                                    
                                </div> <!-- end card-->

                            </div> <!-- end col -->
                            <div class="col-lg-6 col-xl-4 d-flex">
                                <div class="card shadow-lg w-100">
                                    <div class="card-body">
                                        <h5 class="badge badge-primary badge-pill">Mode d'emploi</h5>
																				<p class=" mb-2">
																						<ul class="list-group">
																								<li class="list-group-item">
																										<a target="_blank" rel="noopener noreferrer" href="https://halur1.univ-rennes1.fr/Zip2HAL_Tutoriel.pdf"><i class="mdi mdi-file-pdf-box-outline mr-1"></i> Tutoriel</a>
																								</li>
                                            </ul> 
                                        </p>
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
																				
																				Bonjour <strong><?php echo phpCAS::getUser();?></strong>,<br>
																				La procédure de modification Zip2HAL des notices n'étant pas pour l'instant complètement liée à l'authentification CAS du CCSD, nous avons besoin que vous resaisissiez le mot de passe de votre compte administrateur HAL.
																				<form action="<?php echo $form; ?>" method="post">
																				<input type ="password" name="password">
																				<input type="submit" value="Envoyer">
																				</form>
																		</div> <!-- end card-body-->
																
														</div> <!-- end card-->

												</div> <!-- end col -->
										</div>
										<!-- end row -->
										
								 </div> <!-- container -->

                </div>
                <!-- content -->
								
								<?php
								include "./Glob_bas.php";
								?>

						</div>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->


        </div>
				
				<button id="scrollBackToTop" class="btn btn-primary"><i class="mdi mdi-24px text-white mdi-chevron-double-up"></i></button>
        <!-- END wrapper -->
				
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
                    
                        );
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

<?php$collport = array("https://tel.archives-ouvertes.fr/" => "tel","https://hal.archives-ouvertes.fr/" => "hal","https://archivesic.ccsd.cnrs.fr/" => "archivesic","https://jeannicod.ccsd.cnrs.fr/" => "jeannicod","https://edutice.archives-ouvertes.fr/" => "tematice","https://memsic.ccsd.cnrs.fr/" => "memsic","http://hal.in2p3.fr/" => "democrite","https://hal.inria.fr/" => "inria","https://halshs.archives-ouvertes.fr/" => "halshs","https://artxiker.ccsd.cnrs.fr/" => "artxiker","https://www.hal.inserm.fr/" => "inserm","https://hal-ujm.archives-ouvertes.fr/" => "ujm","https://hal-ens-lyon.archives-ouvertes.fr/" => "ens-lyon","https://hal-lirmm.ccsd.cnrs.fr/" => "lirmm","https://cel.archives-ouvertes.fr/" => "cel","https://hal-emse.ccsd.cnrs.fr/" => "emse","https://hal.ird.fr/" => "ird","http://hal.cirad.fr/" => "cirad","https://hal-pasteur.archives-ouvertes.fr/" => "pasteur","https://hal-obspm.ccsd.cnrs.fr/" => "obspm","https://hal-bioemco.ccsd.cnrs.fr/" => "bioemco","https://hal-sde.archives-ouvertes.fr/" => "haledd","https://hal-ineris.archives-ouvertes.fr/" => "ineris","https://hal-cea.archives-ouvertes.fr/" => "cea","https://hal-insu.archives-ouvertes.fr/" => "insu","https://hal-ssa.archives-ouvertes.fr/" => "ssa","https://hal-irsn.archives-ouvertes.fr/" => "irsn","https://hal-meteofrance.archives-ouvertes.fr/" => "meteo","https://hal.univ-cotedazur.fr/" => "univ-cotedazur","https://hal-descartes.archives-ouvertes.fr/" => "descartes","https://hal-paris1.archives-ouvertes.fr/" => "paris1","https://hal-mnhn.archives-ouvertes.fr/" => "mnhn","https://hal-supelec.archives-ouvertes.fr/" => "supelec","https://hal-hprints.archives-ouvertes.fr/" => "hprints","https://hal-sfo.ccsd.cnrs.fr/" => "sfo","https://hal.univ-brest.fr/" => "univ-brest","https://dumas.ccsd.cnrs.fr/" => "dumas","https://hal-unilim.archives-ouvertes.fr/" => "unilim","https://hal-anses.archives-ouvertes.fr/" => "afssa","https://hal-univ-lyon3.archives-ouvertes.fr/" => "univ-lyon3","http://hal.grenoble-em.com/" => "grenoble-em","https://hal-univ-artois.archives-ouvertes.fr/" => "univ-artois","https://hal-polytechnique.archives-ouvertes.fr/" => "polytechnique","http://hal.univ-nantes.fr/" => "univ-nantes","https://hal-imt.archives-ouvertes.fr/" => "institut-telecom","http://hal.univ-smb.fr/" => "univ-savoie","https://hal-confremo.archives-ouvertes.fr/" => "confremo","https://hal-mines-paristech.archives-ouvertes.fr/" => "ensmp","https://hal-riip.archives-ouvertes.fr/" => "riip","https://hal-ens.archives-ouvertes.fr/" => "ens","https://hal-univ-paris13.archives-ouvertes.fr/" => "univ-paris13","https://medihal.archives-ouvertes.fr/" => "medihal","https://hal-inrap.archives-ouvertes.fr/" => "inrap","https://hal-hec.archives-ouvertes.fr/" => "hec","https://hal-sciencespo.archives-ouvertes.fr/" => "sciencespo","https://hal-univ-avignon.archives-ouvertes.fr/" => "univ-avignon","https://hal-univ-bourgogne.archives-ouvertes.fr/" => "univ-bourgogne","https://hal-ensta-bretagne.archives-ouvertes.fr/" => "ensieta","https://hal-brgm.archives-ouvertes.fr/" => "brgm","https://hal-iogs.archives-ouvertes.fr/" => "iogs","https://pastel.archives-ouvertes.fr/" => "pastel","https://hal-agroparistech.archives-ouvertes.fr/" => "agroparistech","https://hal-enpc.archives-ouvertes.fr/" => "enpc","https://hal.sorbonne-universite.fr/" => "sorbonne-universite","https://hal-hcl.archives-ouvertes.fr/" => "hcl","https://telearn.archives-ouvertes.fr/" => "telearn","https://hal-ecp.archives-ouvertes.fr/" => "ecp","https://hal-essec.archives-ouvertes.fr/" => "essec","https://hal-pjse.archives-ouvertes.fr/" => "pjse","https://hal-espci.archives-ouvertes.fr/" => "espci","https://hal-ensta-paris.archives-ouvertes.fr//" => "ensta","https://hal-rbs.archives-ouvertes.fr/" => "rbs","https://hal-ephe.archives-ouvertes.fr/" => "ephe","https://hal-upec-upem.archives-ouvertes.fr/" => "univ-mlv","https://hal-enscp.archives-ouvertes.fr/" => "enscp","https://hal-univ-tlse2.archives-ouvertes.fr/" => "univ-tlse2","https://hal-univ-tlse3.archives-ouvertes.fr/" => "ups-tlse","https://hal-em-normandie.archives-ouvertes.fr/" => "em-normandie","https://hal.univ-antilles.fr/" => "uag","https://hal-univ-diderot.archives-ouvertes.fr/" => "univ-diderot","https://hal-ifp.archives-ouvertes.fr/" => "ifp","https://hal-univ-corse.archives-ouvertes.fr/" => "univ-corse","https://hal-genes.archives-ouvertes.fr/" => "genes","https://hal-agrocampus-ouest.archives-ouvertes.fr/" => "agrocampus-ouest","https://hal-univ-paris8.archives-ouvertes.fr/" => "univ-paris8","https://hal-bnf.archives-ouvertes.fr/" => "bnf","https://hal-audencia.archives-ouvertes.fr/" => "audencia","https://hal-cstb.archives-ouvertes.fr/" => "cstb","https://hal-univ-rennes1.archives-ouvertes.fr/" => "univ-rennes1","https://hal-auf.archives-ouvertes.fr/" => "afrique","https://hal.univ-lille3.fr/" => "univ-lille3","https://hal-enac.archives-ouvertes.fr/" => "enac","https://hal-icp.archives-ouvertes.fr/" => "icp","http://hal.univ-grenoble-alpes.fr/" => "saga","https://hal-univ-tln.archives-ouvertes.fr/" => "univ-tln","https://hal-mines-nantes.archives-ouvertes.fr/" => "emn","https://hal-univ-tours.archives-ouvertes.fr/" => "univ-tours","https://hal-pse.archives-ouvertes.fr/" => "pse","https://hal.uca.fr/" => "clermont-univ","https://hal-onera.archives-ouvertes.fr/" => "onera","https://hal-mines-albi.archives-ouvertes.fr/" => "ema","https://hal-amu.archives-ouvertes.fr/" => "amu","https://hal-rennes-sb.archives-ouvertes.fr/" => "esc-rennes","https://hal-insa-rennes.archives-ouvertes.fr/" => "insa-rennes","https://hal-univ-ubs.archives-ouvertes.fr/" => "univ-bsud","https://hal-univ-fcomte.archives-ouvertes.fr/" => "univ-fcomte","https://hal-neoma-bs.archives-ouvertes.fr/" => "neoma","https://hal-usj.archives-ouvertes.fr/" => "usj","https://hal-centralesupelec.archives-ouvertes.fr/" => "centralesupelec","https://hal.univ-reunion.fr/" => "univ-reunion","https://hal-univ-perp.archives-ouvertes.fr/" => "univ-perp","https://hal-normandie-univ.archives-ouvertes.fr/" => "normandie-univ","https://hal.uvsq.fr/" => "uvsq","https://hal-univ-paris-dauphine.archives-ouvertes.fr/" => "univ-paris-dauphine","https://hal-univ-paris3.archives-ouvertes.fr/" => "univ-paris3","https://hal.univ-lorraine.fr/" => "univ-lorraine","https://hal-univ-orleans.archives-ouvertes.fr/" => "univ-orleans","https://hal-lara.archives-ouvertes.fr/" => "lara","https://hal-inalco.archives-ouvertes.fr/" => "inalco","https://hal.laas.fr/" => "laas","https://hal.campus-aar.fr/" => "campusaar","https://hal-insep.archives-ouvertes.fr/" => "insep","https://hal-univ-paris10.archives-ouvertes.fr/" => "univ-paris10","https://hal-univ-evry.archives-ouvertes.fr/" => "univ-evry","https://hal.univ-guyane.fr/" => "univ-guyane","https://hal-montpellier-supagro.archives-ouvertes.fr/" => "montpellier-supagro","https://aurehal.archives-ouvertes.fr//" => "aurehal","https://hal.univ-rennes2.fr/" => "univ-rennes2","https://hal-univ-lemans.archives-ouvertes.fr/" => "univ-lemans","https://hal-udl.archives-ouvertes.fr/" => "udl","https://hal.ehesp.fr/" => "ehesp","https://hal-hceres.archives-ouvertes.fr/" => "hceres","https://hal.umontpellier.fr/" => "univ-montpellier","https://hal-univ-rochelle.archives-ouvertes.fr/" => "univ-rochelle","https://hal-univ-paris-lumieres.archives-ouvertes.fr/" => "univ-paris-lumieres","https://hal-univ-poitiers.archives-ouvertes.fr/" => "univ-poitiers","https://hal.insa-toulouse.fr/" => "insa-toulouse","https://hal-inshea.archives-ouvertes.fr/" => "inshea","https://hal.univ-reims.fr/" => "urca","https://hal-edf.archives-ouvertes.fr/" => "edf","https://hal-agrosup-dijon.archives-ouvertes.fr/" => "agrosup-dijon","https://hal-upf.archives-ouvertes.fr/" => "upf","https://hal-imt-atlantique.archives-ouvertes.fr/" => "imt-atlantique","https://hal-univ-lyon1.archives-ouvertes.fr/" => "univ-lyon1","https://hal-univ-pau.archives-ouvertes.fr/" => "univ-pau-pays-adour","https://hal-rnmsh.archives-ouvertes.fr/" => "rnmsh","https://hal-uphf.archives-ouvertes.fr/" => "uphf","https://hal-u-paris-seine.archives-ouvertes.fr/" => "u-paris-seine","https://hal.univ-lille.fr/" => "univ-lille","https://hal.em-lyon.com/" => "em-lyon","https://hal.utc.fr/" => "utc","https://hal.univ-lyon2.fr/" => "univ-lyon2","https://hal-cnam.archives-ouvertes.fr/" => "cnam","https://hal-vetagro-sup.archives-ouvertes.fr/" => "vetagro-sup","https://hal.telecom-paristech.fr/" => "telecom-paristech","https://hal-cnrs.archives-ouvertes.fr/" => "cnrs","https://hal.univ-angers.fr/" => "univ-angers","https://hal-enssib.archives-ouvertes.fr/" => "enssib","https://hal-ciheam.iamm.fr/" => "ciheam-iamm","https://hal-utt.archives-ouvertes.fr/" => "utt","https://hal-enc.archives-ouvertes.fr/" => "enc","https://hal-u-bordeaux-montaigne.archives-ouvertes.fr/" => "u-bordeaux-montaigne","https://hal-enva.archives-ouvertes.fr/" => "enva","https://hal.u-pec.fr//" => "u-pec","https://hal-chu-clermontferrand.archives-ouvertes.fr/" => "chu-clermontferrand","https://hal-unc.archives-ouvertes.fr/" => "unc","https://hal.mines-ales.fr/" => "mines-ales","https://hal-ip-paris.archives-ouvertes.fr/" => "ip-paris","https://hal.inrae.fr/" => "inrae","https://hal-univ-paris.archives-ouvertes.fr/" => "univ-paris",);?>
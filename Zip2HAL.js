function majokCRP(crpAut) {
  document.getElementById("Crp-aut"+crpAut).innerHTML = "<a href=\"#\" data-toggle=\"tooltip\" data-html=\"true\" title=\"Auteur correspondant\" data-original-title=\"\"><i class=\"mdi mdi-email-outline text-info mdi-18px\"></i></a>";
}

function majokIdHAL(halID) {
  document.getElementById("Vu"+halID).innerHTML = "<i class='mdi mdi-delete-outline mdi-18px text-grey'></i>";
	document.getElementById("Txt"+halID).innerHTML = "<s>Supprimer l'idHAL "+halID+"</s>";
}

function majokIdHALSuppr(halID) {
  document.getElementById(halID).value = "";
}


function majokAffil(affilPos, affilName) {
  document.getElementById("Vu-"+affilPos).innerHTML = "<i class='mdi mdi-delete-outline mdi-18px text-grey'></i>";
	document.getElementById(affilPos).innerHTML = "<s>"+affilName+"</s>";
}

function majokAffilAjout(affilPos) {
	document.getElementById(affilPos).value = "";
}

function majokVu(idNomfic) {
  document.getElementById(idNomfic).innerHTML = "<img src='./img/MAJOK.png'>";
}

function majokSuppr(suppression) {
	var idNomfiv = suppression.replace("suppression", "");
  document.getElementById(suppression).innerHTML = "<img src='./img/supprimer_ok.jpg'>";
	document.getElementById("metadonnees"+idNomfiv).innerHTML = "";
	document.getElementById("affiliations"+idNomfiv).innerHTML = "";
	document.getElementById("validerTEI"+idNomfiv).innerHTML = "";
	document.getElementById("importerHAL"+idNomfiv).innerHTML = "";
}

function majokAuteur(auteurPos, auteurName) {
  document.getElementById("Vu-"+auteurPos).innerHTML = "<i class='mdi mdi-delete-outline mdi-18px text-grey'></i><br><br>";
	document.getElementById("Sup-"+auteurPos).style.display = "none";
	document.getElementById("PN-"+auteurPos).innerHTML = "<s>"+auteurName+"</s>";
}

function afficacherLang(lang, idFic) {
	if(lang == "English") {
		document.getElementById("lantitreT-"+idFic).style.display = "none";
		document.getElementById("lanMCT-"+idFic).style.display = "none";
		document.getElementById("lanresumeT-"+idFic).style.display = "none";
	}else{
		document.getElementById("lantitreT-"+idFic).style.display = "block";
		document.getElementById("lanMCT-"+idFic).style.display = "block";
		document.getElementById("lanresumeT-"+idFic).style.display = "block";
	}
}

function afficacher(id,idFic) {
	for(var i = 1; i < 14; i++) {
		if (i != id) {
			document.getElementById("dom-"+i+"-"+idFic).style.display = "none";
			document.getElementById("cod-"+i+"-"+idFic).innerHTML = "<a style='cursor:pointer;' onclick='afficacher("+i+","+idFic+")';><font style='color: #FE6D02;'><b>>&nbsp;</b></font></a>";
		}
	}
	if (document.getElementById("dom-"+id+"-"+idFic).style.display == "block") {
		document.getElementById("dom-"+id+"-"+idFic).style.display = "none";
		document.getElementById("cod-"+id+"-"+idFic).innerHTML = "<a style='cursor:pointer;' onclick='afficacher("+id+","+idFic+")';><font style='color: #FE6D02;'><b>>&nbsp;</b></font></a>";
	}else{
		document.getElementById("dom-"+id+"-"+idFic).style.display = "block";
		document.getElementById("cod-"+id+"-"+idFic).innerHTML = "<a style='cursor:pointer;' onclick='afficacher("+id+","+idFic+")';><font style='color: #FE6D02;'><b>v&nbsp;</b></font></a>";
	}
}

function afficacherRec(qui, idFic) {
	if (document.getElementById("Rrec-"+qui+"-"+idFic).style.display == "block") {
		document.getElementById("Rrec-"+qui+"-"+idFic).style.display = "none";
	}else{
		document.getElementById("Rrec-"+qui+"-"+idFic).style.display = "block";
	}
}

function choixdom(dom, code) {
	if (dom.indexOf(" ~ ") == -1) {
		document.getElementById("domaine").innerHTML = dom + ' ~ ' + code + '<br><input type="hidden" name="domaine" value="'+dom+' ~ '+code+'">';
	}else{
		var tab = dom.split(" ~ ");
		document.getElementById("domaine").innerHTML = tab[0] + ' ~ ' + tab[1] + '<br><input type="hidden" name="domaine" value="'+dom+' ~ '+code+'">';
	}
	document.getElementById("domaine").style.width = "900px";
	document.getElementById("domaine").style.marginLeft = "30px";
	document.getElementById("domaine").className = "d-block";
	document.getElementById("choixdom").className = "d-none";
}

function schemaVal(idFic) {
	document.getElementById("validerTEI-"+idFic).innerHTML = "Validation en cours ...";
}

//Popup JQuery d'avertissement
function afficherPopupAvertissement(message) {
    // crée la division qui sera convertie en popup 
    $('body').append('<div id="popupavertissement" title="Avertissement"></div>');
    $("#popupavertissement").html(message);

    // transforme la division en popup
    var popup = $("#popupavertissement").dialog({
        autoOpen: true,
				closeOnEscape: false,
				open: function(event, ui) {
						$(".ui-dialog-titlebar-close", $(this).parent()).hide();
				},
				modal: true,
        width: 600,
        dialogClass: 'dialogstyleperso',
				hide: "fade",
        buttons: [
            {
								text: "OK",
                class: 'ui-state-warning',
                click: function () {
                    $(this).dialog("close");
                    $('#popupavertissement').remove();
                }
            }
        ]
    });
    $("#popupavertissement").prev().addClass('ui-state-warning');
    return popup;
}

//Popup travail en cours
function afficherPopupAttente(titre='Veuillez patienter', message='Validation du TEI en cours ...') {
    // crée la division qui sera convertie en popup
    $('body').append('<div id="popupattente" title="' + titre + '"></div>');
    $("#popupattente").html(message);

    // transforme la division en popup
    var popup = $("#popupattente").dialog({
        autoOpen: true,
				closeOnEscape: false,
				open: function(event, ui) {
						$(".ui-dialog-titlebar-close", $(this).parent()).hide();
				},
				modal: true,				
        width: 400,
        dialogClass: 'dialogstyleperso',
        hide: "fade"
    });
    $("#popupattente").prev().addClass('ui-state-information');
    return popup;
}

function effacerPopup(popup) {
    $(popup).dialog("close");
    $('#popupattente').remove();
}


//Popup JQuery de confirmation
function afficherPopupConfirmation(question, Cnomfic, Cpos, Cprenomnom, Cauteur) {
    // crée la division qui sera convertie en popup
    $('body').append('<div id="popupconfirmation" title="Confirmation"></div>');
    $("#popupconfirmation").html(question, Cnomfic, Cpos, Cprenomnom, Cauteur);

    // transforme la division en popup
    var popup = $("#popupconfirmation").dialog({
        autoOpen: true,
				closeOnEscape: false,
				open: function(event, ui) {
						$(".ui-dialog-titlebar-close", $(this).parent()).hide();
				},
				modal: true,
        width: 400,
        dialogClass: 'dialogstyleperso',
        hide: "fade",
        buttons: [
            {
                text: "Oui",
                class: "ui-state-question",
                click: function () {
                    $(this).dialog("close");
                    $("#popupconfirmation").remove();
										$.post("Zip2HAL_liste_actions.php", {nomfic : Cnomfic, action: 'supprimerAuteur', pos: Cpos, valeur: Cprenomnom});
										majokAuteur(Cauteur, Cprenomnom);
                }
            },
            {
                text: "Non",
                class: "ui-state-question",
                click: function () {
                    $(this).dialog("close");
                    $("#popupconfirmation").remove();
                }
            }
        ]
    });
    $("#popupconfirmation").prev().addClass('ui-state-question');
    return popup;
}

function goto(Page) {
	$("#majcont").load(Page);
}

//Autocomplete domaine
$(function() {
		
	//autocomplete
	$(".autoDO").autocomplete({
			source: "Zip2HAL_AC_DO.php",
			minLength: 1
	});                

});

//Autocomplete idHAL
$(function() {
		
	//autocomplete
	$(".autoID").autocomplete({
			source: "Zip2HAL_AC_ID.php",
			minLength: 1
	});                

});

//Autocomplete affiliations
$(function() {
		
	//autocomplete
	$(".autoAF").autocomplete({
			source: "Zip2HAL_AC_AF.php",
			minLength: 1,
			open: function (event, ui) {
				$('.ui-autocomplete > li').css("background-color", function() {
						return $(this).text().indexOf('VALID') > -1 ? '#dff0d8' : ($(this).text().indexOf('INCOMING') > -1 ? '#fcf8e3' : '#f2dede');
				});
				$('.ui-menu-item > div').css("color", function() {
						return $(this).text().indexOf('VALID') > -1 ? '#698d53' : ($(this).text().indexOf('INCOMING') > -1 ? '#cfac72' : '#b96f7b');
				});
			}	
	})

});

//Autocomplete financements ANR
$(function() {
		
		//autocomplete
		$(".autoANR").autocomplete({
				source: "Zip2HAL_AC_ANR.php",
				minLength: 1
		});                

});

//Autocomplete financements EUR
$(function() {
		
		//autocomplete
		$(".autoEUR").autocomplete({
				source: "Zip2HAL_AC_EUR.php",
				minLength: 1
		});                

});

//Autocomplete pays
$(function() {
		
		//autocomplete
		$(".autoPays").autocomplete({
				source: "Zip2HAL_AC_Pays.php",
				minLength: 1
		});                

});

//Autocomplete langues
$(function() {
		
		//autocomplete
		$(".autoLang").autocomplete({
				source: "Zip2HAL_AC_Langues.php",
				minLength: 1
		});                

});


function majokIdHAL(halID) {
  document.getElementById("Vu"+halID).innerHTML = "<img width='12px' src='./img/supprimer_ok.jpg'>";
	document.getElementById("Txt"+halID).innerHTML = "<s>Supprimer l'idHAL "+halID+"</s>";
}

function majokIdHALSuppr(halID) {
  document.getElementById(halID).value = "";
}


function majokAffil(affilPos, affilName) {
  document.getElementById("Vu-"+affilPos).innerHTML = "<img width='12px' src='./img/supprimer_ok.jpg'>";
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
  document.getElementById("Vu-"+auteurPos).innerHTML = "<img width='12px' src='./img/supprimer_ok.jpg'><br><br>";
	document.getElementById("Sup-"+auteurPos).style.display = "none";
	document.getElementById("PN-"+auteurPos).innerHTML = "<s>"+auteurName+"</s>";
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

function choixdom(dom, code) {
	if (dom.indexOf(" ~ ") == -1) {
		document.getElementById("domaine").innerHTML = dom + ' ~ ' + code + '<br><input type="hidden" name="domaine" value="'+dom+' ~ '+code+'">';
	}else{
		var tab = dom.split(" ~ ");
		document.getElementById("domaine").innerHTML = tab[0] + ' ~ ' + tab[1] + '<br><input type="hidden" name="domaine" value="'+dom+' ~ '+code+'">';
	}
	document.getElementById("domaine").style.width = "900px";
	document.getElementById("domaine").style.marginLeft = "30px";
	document.getElementById("domaine").style.display = "block";
	document.getElementById("choixdom").style.display = "none";
}

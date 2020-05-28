function majokIdHAL(halID) {
  document.getElementById("Vu"+halID).innerHTML = "<img width='12px' src='./img/supprimer_ok.jpg'>";
	document.getElementById("Txt"+halID).innerHTML = "<s>Supprimer l'idHAL "+halID+"</s>";
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
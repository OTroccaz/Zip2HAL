function majokIdHAL(halID) {
  document.getElementById("Vu"+halID).innerHTML = "<img width='12px' src='./img/supprimer_ok.jpg'>";
	document.getElementById("Txt"+halID).innerHTML = "<s>Supprimer l'idHAL "+halID+"</s>";
}

function majokAffil(affilPos, affilName) {
  document.getElementById("Vu-"+affilPos).innerHTML = "<img width='12px' src='./img/supprimer_ok.jpg'>";
	document.getElementById(affilPos).innerHTML = "<s>"+affilName+"</s>";
}

function majokVu(idNomfic) {
  document.getElementById(idNomfic).innerHTML = "<img src='./img/MAJOK.png'>";
}
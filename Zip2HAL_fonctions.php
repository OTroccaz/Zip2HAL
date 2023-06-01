<?php
/*
 * Zip2HAL - Importez vos publications dans HAL - Import your publications into HAL
 *
 * Copyright (C) 2023 Olivier Troccaz (olivier.troccaz@cnrs.fr) and Laurent Jonchère (laurent.jonchere@univ-rennes.fr)
 * Released under the terms and conditions of the GNU General Public License (https://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * Fonctions utilisées - Functions used
 */
 
function progression($indice, $iMax, $id, &$iPro, $quoi) {
	$iPro = $indice;
	echo '<script>';
  echo 'var txt = \'Traitement '.$quoi.' '.$indice.' sur '.$iMax.'<br>\';';
	echo 'document.getElementById(\''.$id.'\').innerHTML = txt';
	echo '</script>';
	ob_flush();
	flush();
	ob_flush();
	flush();
}

function mb_ucwords($str) {
  $str = mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
  return ($str);
}

function prenomCompInit($prenom) {
  $prenom = str_replace("  ", " ",$prenom);
  if(strpos(trim($prenom),"-") !== false) {//Le prénom comporte un tiret
    $postiret = mb_strpos(trim($prenom),'-', 0, 'UTF-8');
    if($postiret != 1) {
      $prenomg = trim(mb_substr($prenom,0,($postiret-1),'UTF-8'));
    }else{
      $prenomg = trim(mb_substr($prenom,0,1,'UTF-8'));
    }
    $prenomd = trim(mb_substr($prenom,($postiret+1),strlen($prenom),'UTF-8'));
    $autg = mb_substr($prenomg,0,1,'UTF-8');
    $autd = mb_substr($prenomd,0,1,'UTF-8');
    $prenom = mb_ucwords($autg).".-".mb_ucwords($autd).".";
  }else{
    if(strpos(trim($prenom)," ") !== false) {//plusieurs prénoms
      $tabprenom = explode(" ", trim($prenom));
      $p = 0;
      $prenom = "";
      while (isset($tabprenom[$p])) {
        if($p == 0) {
          $prenom .= mb_ucwords(mb_substr($tabprenom[$p], 0, 1, 'UTF-8')).".";
        }else{
          $prenom .= " ".mb_ucwords(mb_substr($tabprenom[$p], 0, 1, 'UTF-8')).".";
        }
        $p++;
      }
    }else{
      $prenom = mb_ucwords(mb_substr($prenom, 0, 1, 'UTF-8')).".";
    }
  }
  return $prenom;
}

//Suppresion des accents
function wd_remove_accents($str, $charset='utf-8')
{
    $str = htmlentities($str, ENT_NOQUOTES, $charset);

    $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

    return $str;
}

//Nettoyage des dossiers de création de fichiers
function suppression($dir, $age) {
	
	$handle = opendir($dir);
	while($elem = readdir($handle)) {//ce while vide tous les répertoires et sous répertoires
		$ageElem = time() - filemtime($dir.'/'.$elem);
		if($ageElem > $age) {
			if(is_dir($dir.'/'.$elem) && substr($elem, -2, 2) !== '..' && substr($elem, -1, 1) !== '.') {//si c'est un répertoire
				suppression($dir.'/'.$elem, $age);
			}else{
				if(substr($elem, -2, 2) !== '..' && substr($elem, -1, 1) !== '.')	{
					unlink($dir.'/'.$elem);
				}
			}
		}			
	}
	
	$handle = opendir($dir);
	while($elem = readdir($handle)) {//ce while efface tous les dossiers
		$ageElem = time() - filemtime($dir.'/'.$elem);
		if($ageElem > $age) {
			if(is_dir($dir.'/'.$elem) && substr($elem, -2, 2) !== '..' && substr($elem, -1, 1) !== '.') {//si c'est un repertoire
				suppression($dir.'/'.$elem, $age);
				rmdir($dir.'/'.$elem);
			}    
		}
	}
}

?>
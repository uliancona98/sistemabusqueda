<?php
include("../src/lib/LIB_http.php");
include("../src/lib/LIB_parse.php");
include("../src/lib/LIB_resolve_addresses.php");
include("../src/lib/LIB_http_codes.php");
include("../src/LanguageDetection/Language.php");
include("../src/inflector/inflector.php");
use ICanBoogie\Inflector;

function generate($link, $lastM) {
    //$page_base = get_base_page_address($link);
    $strHTML = '';
    $downloaded_page = http_get_withheader($link, '');
    if ($downloaded_page['ERROR'] == '') {
        $content_type=$downloaded_page['STATUS']['content_type'];
		$strStatus=$downloaded_page['STATUS'];
        $code=$strStatus["http_code"];
        if (($code!=200 && $code!=206) || strpos(strtolower($content_type),"text")===false) {
            return array('Error'=>'La página respondió con un estatus diferente a 200 0 206');
        }else{
            if ($code ==200 || $code ==206){ //&& strpos(strtolower($content_type),"text")===true) {
                $content = $downloaded_page['FILE'];
                $lastModified = getLastModified($content);
                if($lastM) {
                    if ($lastM == $lastModified) {
                        return null;
                    }
                }
                $title = return_between($downloaded_page['FILE'], "<title>", "</title>", EXCL);
                $strHTML = return_between($downloaded_page['FILE'], "<body>", "</body>", EXCL);//$downloaded_page['FILE'];
                $strHTML = getStrHTML($strHTML, $link);
                $language = getLanguage($strHTML);
                if($language != 'es') {
                    return array('Error'=>'La página no está escrita en español.');
                } 
                $strHTML = removeStopWords($strHTML, $language);
                $strHTML = singularizeStr($strHTML, $language);
                return array(
                    'Title' => $title,
                    'Content' => $strHTML,
                    'LastModified' => $lastModified
                );
            }
        }
    }
    return array('Error'=>$downloaded_page['ERROR']);
}

function getStrHTML($content, $url) {       
    $strHTML = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );
    $strHTML = remove($strHTML,"<script","</script>");//strip JavaScript
    $strHTML = remove($strHTML,"<img","</img>");
    $strHTML = remove($strHTML,"<style","</style>");
    $strHTML = remove($strHTML,"<head","</head>");
    $strHTML = strip_tags($strHTML);//Remove html tags
    $strHTML = strtolower_utf8($strHTML);
    $strHTML = cleanPunctuationMarks($strHTML);
    $strHTML = trim(preg_replace('/\s\s+/', ' ', $strHTML)); //eliminamos exceso de espacios en blanco
    return $strHTML;
}

function getUrls($target) {
    $urls = array();
    $downloaded_page = http_get($target, '');
    if ($downloaded_page['ERROR'] == '') {
        $link_array = parse_array($downloaded_page['FILE'], $beg_tag="<a", $close_tag=">");
        for($xx=0; $xx<count($link_array); $xx++)
        {
            
            $link = get_attribute($tag=$link_array[$xx], $attribute="href");
            $resolved_link_address = resolve_address($link, "");
            $downloaded_link = http_get($resolved_link_address, $target);
            if($downloaded_link['STATUS']['http_code'] == 200) {
                $urls[] = $downloaded_link['STATUS']['url'];
            }
            //$urls[] = $link;
        }
    }
    return $urls;
}

function strtolower_utf8($string){ 
    $convert_to = array( 
      "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", 
      "v", "w", "x", "y", "z", "á", "é", "í",
      "ñ", "ó",  "ú"
    ); 
    $convert_from = array( 
      "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", 
      "V", "W", "X", "Y", "Z", "Á", "É", "Í", 
      "Ñ", "Ó",  "Ù", "Ú"
    ); 
  
    return str_replace($convert_from, $convert_to, $string); 
  }

function cleanPunctuationMarks($str) {
    $convert_to = array( 
        "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", ""
    ); 
    $convert_from = array( 
    '"', ':', '.', ',', ';', '¿', '?', '(', ')', '{', '}', '¡', '!', '...', '<', '>', '[', ']', "'"
    ); 

    return str_replace($convert_from, $convert_to, $str); 
}

function getLanguage($str) {
    $ld = new LanguageDetection\Language();
 
    return $ld->detect($str)->whitelist('en', 'fr', 'nb', 'pt', 'es', 'tr')->bestResults();
}

function removeStopWords($str, $language) {
    $path = '../src/stop-words/';

    $commonWords = array();
    $dir = opendir($path);
    while ($elemento = readdir($dir)){
        if( $elemento != "." && $elemento != ".."){
            if( !is_dir($path.$elemento) ){
                $ext = explode("_",$elemento);
                $ext = explode(".",end($ext));
                $ext = $ext[0];
                if ($ext == $language) {
                    $fp = fopen($path.$elemento, 'r');
                    while (!feof($fp)) {
                        $linea = fgets($fp);
                        $commonWords[] = preg_replace("/[\r\n|\n|\r]+/", "", $linea);
                    }
                    fclose($fp);
                }
            }
        }
    }
    $arrText = explode(' ', trim($str));
    $diff = array_diff($arrText, $commonWords);
    $str = implode(' ', $diff);
    //return preg_replace('/\b('.implode('|',$commonWords).')\b/','',$str);
    return $str;
}

function singularizeStr($str, $language) {
    $inflector = Inflector::get($language);
    $txtArr = explode(" ",$str);
    $newTxtArr = array();
    foreach($txtArr as $word) {
        if(!empty($word)) {
            $newTxtArr[] = $inflector->singularize($word); 
        }
    }    
    return implode(" ", $newTxtArr);
}

function getLastModified($content) {
    $date = split_string($content, "ETag", BEFORE, EXCL);
    $date = return_between($date, 'Last-Modified:', 'GMT', EXCL);
    $date = date('Y-m-d H:i:s', strtotime($date));
    if($date == '01-01-1970 01:00:00') {
        return null;
    }
    return $date;
}

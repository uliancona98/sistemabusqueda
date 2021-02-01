<?php
use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;
error_reporting(E_ALL);
ini_set('display_errors', true);

if (file_exists('config.php')) {
    require('config.php');
}
require $config['autoload'] ?? __DIR__.'/../vendor/autoload.php';
$adapter = new Curl();
$eventDispatcher = new EventDispatcher();
//-------------------
require("lexer.php");
$facetMenu = array();
$sugerencias = array();
$correciones = array();
$terminosConsultaExpandida = array();
$terminosConsulta = array();
$results = array();
/*echo '<script>';
echo 'console.log('.'"ajaja"' .')';
echo '</script>';*/
$correctoAnalizadorSintactico = false;

$q = '';
$client = new Solarium\Client($adapter, $eventDispatcher, $config);


if(isset($_GET['query'])) {
    echo '<script>';
    echo 'console.log('.'"ajfaja"' .')';
    echo '</script>';
    //echo getSugestions();
    return;
} 
if(isset($_GET['search'])){
    // echo getCorrections();
    // echo getCategories();
    echo '<script>';
    echo 'console.log('. json_encode( $_GET['search'] ) .')';
    echo '</script>';
    $res = getResults();
    $cat = getCategories();
    $cor = getCorrections();
    echo json_encode(['results' => $res, 'categories'=>$cat, 'corrections'=>$cor]);
    // return;
}

/*if(isset($_GET['query'])){
    $arrayTokens = checarTokens($_GET['query']);
    
    if($correctoAnalizadorSintactico){
        $queryExpandida = expansionConsulta($arrayTokens);
        //var_dump($terminosConsultaExpandida);
        //Expansión de la consulta con datamuse
        busqueda($queryExpandida);
        echo "<pre>";
        print_r($results);
        echo "</pre>";

        echo "<pre>";
        print_r($facetMenu);
        echo "</pre>";
        
        echo "<pre>";
        print_r($sugerencias);
        echo "</pre>";

        echo "<pre>";
        print_r($correciones);
        echo "</pre>";
    }else{
        echo "Error en tu consulta";
    }

}
if (isset($_GET['idFaceta'])) {
    $categoria = 1;
    busquedaFaceta($_GET['idFaceta']);
}*/



function getResults() {
    global $results;
   /*$results = [
       [
           'id'=>1, 
           'title'=>"Canzando ls...", 
           'url'=> 'http:..',
           'category'=> ['cat2', 'cat1'],
           'snippet' => 'cazando gangas mex',
           'score' => 1.86972342,
       ],
       [
           'id'=>2, 
           'title'=>"terk9sdpo ls...", 
           'url'=> 'http:..',
           'category'=> ['cat4', 'cat1'],
           'snippet' => 'cazando gangas mex',
           'score' => 1.36972342,
       ],
       [
           'id'=>4, 
           'title'=>"como alsdd ls...", 
           'url'=> 'http:..',
           'category'=> ['cat8', 'cat1'],
           'snippet' => 'termina gangas mex',
           'score' => 1.16972342,
       ],
        ];*/
    return json_encode($results); 
}
/*
function getSuggestions(){
    global $sugerencias;
    echo json_encode($sugerencias); 
}
function getCorrecciones(){
    global $correciones;
    echo json_encode($correciones); 
}
function getCategorias(){
    global $facetMenu;
    echo json_encode($facetMenu); 
}
function getResultados(){
    global $results;
    echo json_encode($results); 
}
*/
function getCategories() {
    //$categories = [['cat2', 2], ['cat4', 2], ['cot', 0], ['catt', 0]];
    global $facetMenu;
    return json_encode($facetMenu);
}

function getCorrections() {
    global $correciones;
    //$corrections = ['alex', 'carlos', 'andrea'];
    return json_encode($correciones);
}

function getSugestions() {
    global $sugerencias;
    //$sugestions = ['a  lex', 'carlos', 'andrea', '1234', '234', '345'];
    $arr = array_values(array_filter($sugerencias, 'filter'));
    return json_encode($arr);
}

function filter($val) {
    
    $query = $_GET['query'];
    $len = strlen($query); 
    return (substr($val, 0, $len) === $query); 
}

// $results = (Object)[
//         (object)['fields'=>[
//             'id'=> 79,
//             'url' => ['www.https...'],
//             'title' => 'mejora vir',
//             'category' => ['Cat3'],
//             'content' => ['contenido'],
//             '_version_' => 1690187199826362368,
//             'score' => 0.84473014
//         ]
//     ],
//         (object)['fields'=>[
//             'id'=> 79,
//             'url' => ['www.https...'],
//             'title' => 'mejora vir',
//             'category' => ['Cat3'],
//             'content' => ['contenido'],
//             '_version_' => 1690187199826362368,
//             'score' => 0.84473014
//         ]
//     ],
//         // (object)[1, 23,4],
//         // new ParentClass(),
//     ];


function strpos_recursive($haystack, $needle, $offset = 0, &$results = array()) {               
    $offset = strpos($haystack, $needle, $offset);
    if($offset === false) {
        return $results;           
    } else {
        $results[] = $offset;
        return strpos_recursive($haystack, $needle, ($offset + 1), $results);
    }
}
//Funcion que checa que los tokens esten correctos
function checarTokens($consulta){
    global $correctoAnalizadorSintactico;
    $array_tokns=array();
    global $terminosConsultaExpandida;
    $_delimitadores = ' '; 
    $token = strtok($consulta, $_delimitadores);
    $array_delimitador = array();
    while ($token !== false){
       $array_delimitador[]=$token;
       $token = strtok(' ');
    }
    //var_dump($array_delimitador);
    $array_tokens= array();
    foreach ($array_delimitador as $i => $value) {
        if (str_contains($value, '(')) {
            $found = strpos_recursive($value, '(');
            if($found) {
                //echo("FOUND");
                foreach($found as $pos) {
                    if($pos==0){
                        echo $value;
                        $expl = explode('(', $value);
                        //var_dump($expl);
                        if(strlen($value)==1){
                            $array_tokens[] = "(";
                        }elseif(count($expl)>1){
                            $array_tokens[] = "(";
                            $array_tokens[] = $expl[1];
                            $terminosConsultaExpandida[]=$expl[1];
                        }
                    }elseif($pos==(strlen($value)-1)){
                        $expl = explode('(', $value);
                        if(strlen($value)==1){
                            $array_tokens[] = "(";
                        }elseif(count($expl)>1){
                            $array_tokens[] = $expl[0];
                            $terminosConsultaExpandida[]=$expl[0];
                            $array_tokens[] = "(";
                        }

                    }else{
                        $expl = explode('(', $value);
                        if(strlen($value)==1){
                            $array_tokens[] = "(";
                        }elseif(count($expl)>1){
                            $array_tokens[] = $expl[0];
                            $terminosConsultaExpandida[]=$expl[0];
                            $array_tokens[] = "(";
                            $array_tokens[] = $expl[1];
                            $terminosConsultaExpandida[]=$expl[1];
                        }
                    }
                }   
            }
        }elseif(str_contains($value, ')')){
            $found = strpos_recursive($value, ')');
            if($found) {
                foreach($found as $pos) {
                    if($pos==0){
                        $expl = explode(')', $value);
                        if(strlen($value)==1){
                            $array_tokens[] = ")";
                        }elseif(count($expl)>1){
                            $array_tokens[] = ")";
                            $array_tokens[] = $expl[1];
                            $terminosConsultaExpandida[]=$expl[1];
                        }


                    }elseif($pos==(strlen($value)-1)){
                        $expl = explode(')', $value);
                        if(strlen($value)==1){
                            $array_tokens[] = ")";
                        }elseif(count($expl)>1){
                            $array_tokens[] = $expl[0];
                            $terminosConsultaExpandida[]=$expl[0];
                            $array_tokens[] = ")";
                        }

                    }else{
                        $expl = explode(')', $value);
                        if(strlen($value)==1){
                            $array_tokens[] = ")";
                        }elseif(count($expl)>1){
                            $array_tokens[] = $expl[0];
                            $terminosConsultaExpandida[]=$expl[0];
                            $array_tokens[] = ")";
                            $array_tokens[] = $expl[1];
                            $terminosConsultaExpandida[]=$expl[1];
                        }
                    }
                }   
            } else {
                echo " not found in";
            }
            
        }else{
            $array_tokens[] = $value;
        }

    }
    
    $lexer = new Lexer($array_tokens);
    $terminos = array();
    if($lexer->getCorrectoSintactico()==true){
        echo("correct");
        $correctoAnalizadorSintactico = true;
        $array_tokns=$lexer->getTokensArray();    
    }else{
        $correctoAnalizadorSintactico = false;
        echo "Sintaxis errónea";
    }
    return $array_tokns;
}

//Hace expansion de consulta
function expansionConsulta($arrayTokens){
    
    global $terminosConsultaExpandida;
    global $terminosConsulta;
    $query="";
    echo "<pre>";
    print_r($arrayTokens);
    echo "</pre>";
    foreach($arrayTokens as $key => $value){
        if($value['token']=='WORD'){
            //echo ($value['lexema']);
            //foreach($terminosConsultaExpandida as $termino){
                $urlDatamuse = "https://api.datamuse.com/words?ml=".$value['lexema']."&v=es&max=3";
                $jsonDatamuse = file_get_contents($urlDatamuse);
                $datosDatamuse = json_decode($jsonDatamuse,true);
                //echo $urlDatamuse; 
    
                $elementosArray=count($datosDatamuse);
                $contador =0;
                $terminosConsulta[]=$value['lexema'];
                $query = $query."(".$value['lexema'];
                $fin = 0;
                foreach($datosDatamuse as $key => $datos){
                    //$consultaExpandida[] = "and";  //¿Le concateno ands? Segun yo no importa, terminos seguidos son por default and en las dos api
                    
                    if(strcasecmp($datos["word"], $value['lexema']) != 0){//son diferentes insensitivo
                        if($contador == 0){
                            if($contador == ($elementosArray-1)){
                                $query = $query." OR ".$datos["word"]."";
                                $fin == 1;
                            }else{
                                $query = $query." OR ".$datos["word"]."";
                            }
                        }elseif($contador == ($elementosArray-1)){
                            $query = $query." OR ".$datos["word"]."";
                            $fin == 1;
                        }else{
                            $query = $query." OR ".$datos["word"]."";
                        }
                        $terminosConsultaExpandida[]= $datos["word"];
                    }else{
                        if($contador == ($elementosArray-1)){
                            $fin == 1;
                        }
                    }
                    $contador++;
                }
                if($fin==0){
                    $query = $query.")";

                }
                /*echo "<pre>";
                print_r($datosDatamuse);
                echo "</pre>";*/
            //}


        }else{
            if($key==0){
                $query = $query.$value['lexema']."";
            }
            else{
                $query = $query.$value['lexema']."";

            }
        }

    }
    echo ($query);
    return $query;
}

//Llama a la busqueda de Solr y luego a sugerencias y luego correciones
function busqueda($queryExpandida){
    global $terminosConsulta, $client,$q;
    busquedaSolr($queryExpandida);


    foreach($terminosConsulta as $key => $valor){
        $q = $q." ".$valor;
    }
    echo $q;
    /*echo "<pre>";
    print_r($terminosConsulta);
    echo "</pre>";
    echo "<pre>";
    print_r($terminosConsultaExpandida);
    echo "</pre>";*/
    echo "Correciones";
    //Correciones
    spellChecker();
    //Fin correciones

    echo "Sugerencias";
    suggestions($q);
}
//Hace la busca en Solr
function busquedaSolr($q){
    // create a client instance
    global $facetMenu;
    global $client;
    global $results;

    // get a select query instance

    // get the facetset component
    $query = $client->createSelect();
    $query->setQuery($q);
    $facetSet = $query->getFacetSet();
    // create a facet field instance and set options
    $facetSet->createFacetField('cat')->setField('category'); //faceta

    //Highlight
    $query->getHighlighting()->setRequireFieldMatch(true);
    $hl = $query->getHighlighting();


    $hl->setFields('title, content', "category");
    $hl->setSimplePrefix('<b>');
    $hl->setSimplePostfix('</b>');
    //Highlight
    $resultset = $client->select($query);
    $highlighting = $resultset->getHighlighting();
    echo '<b>Query:</b> '.$query->getQuery().'<hr/>';

    // display the total number of documents found by Solr
    echo 'Se han encontrado: '.$resultset->getNumFound().' resultados';

    // display facet counts
    echo '<hr/>Facet counts for field "category":<br/>';
    $facet = $resultset->getFacetSet()->getFacet('cat');
    $i=0;
    foreach ($facet as $value => $count) {
        $facetMenu[] = array($value, $count);
        echo '<a href="" id="'.$value.'" onclick="removeday(event)"> '.$value.' [' . $count . ']</a><br/>';
        $i++;
    }
    echo "<pre>";
    print_r($facetMenu);
    echo "</pre>";
   // var_dump($resultset);
    foreach ($resultset as $document) {
        echo '</table><br/><b>Highlighting results:</b><br/>';
        $highlightedDoc = $highlighting->getResult($document->id);
        if ($highlightedDoc) {
            $highlightString = '';
            foreach ($highlightedDoc as $field => $highlight) {
                $highl = implode(' (...) ', $highlight) . '<br/>';
                $highlightString=$highlightString.$highl;
                
                //echo implode(' (...) ', $highlight) . '<br/>';
            }
            if(strlen($highlightString)>0){
                echo '<hr/><table>';
                echo '<tr><th>' . 'Id: ' . '</th><td>' . $document->id . '</td></tr>';
                if (is_array($document->url)) {
                    $url = implode(', ', $document->url);
                }
                echo '<tr><th>' . 'URL: ' . '</th><td><a href="' . $value . '">'.$value.'</a></td></tr>';
                echo '<tr><th>' . 'Title: ' . '</th><td>' . $document->title . '</td></tr>';
                if (is_array($document->category)) {
                    $value = implode(', ', $document->category);
                }
                echo '<tr><th>' . 'Categories: ' . '</th><td>' . $value . '</td></tr>';
                echo '<tr><th>' . 'Score: ' . '</th><td>' . $document->score . '</td></tr>';

                //}
                echo '</table>';
                
                $results[]=array(
                    "id" =>$document->id,
                    "title" => $document->title,
                    "url" => $url,
                    "category" =>$document->category,
                    "snippet" => $highlightString,
                    "score" => $document->score,
                    );
            }

        }
    }
}

function spellChecker(){
    global $correciones, $client, $terminosConsulta;
    // get a select query instance
    foreach($terminosConsulta as $key => $value){
        $query = $client->createSelect()
        // Unfortunately the /select handler of the techproducts example doesn't contain a spellchecker anymore.
        // Therefore we have to use the /browse handler and turn of velocity by forcing json as response writer.
        ->setHandler('browse')
        ->setResponseWriter(\Solarium\Core\Query\AbstractQuery::WT_JSON)
        // Normally we would use 'spellcheck.q'. But the /browse handler checks 'q'.
        ->setQuery($value)
        ->setRows(0);

        // add spellcheck settings
        $spellcheck = $query->getSpellcheck()
            ->setCount(10)
            ->setBuild(true)
            ->setCollate(true)
            ->setExtendedResults(true)
            ->setCollateExtendedResults(true)
            ->setDictionary('default');

        // this executes the query and returns the result
        $resultset = $client->select($query);
        $spellcheckResult = $resultset->getSpellcheck();
        $collations = $spellcheckResult->getCollations();
        /*echo '<h1>Collations</h1>';
        echo "<pre>";
        echo "</pre>";*/
        
        foreach ($collations as $collation) {
            /*echo 'Query: '.$collation->getQuery().'<br/>';
            echo 'Hits: '.$collation->getHits().'<br/>';*/
            echo 'Correciones:<br/>';
            foreach ($collation->getCorrections() as $input => $correction) {
                $correciones[] = $input . ' => ' . $correction;
                echo $input . ' => ' . $correction .'<br/>';
            }
            echo '<hr/>';
    }
    }

}

function suggestions($q){
    global $sugerencias, $client;
    $query = $client->createSuggester();
    $query->setQuery($q);
    $array = array("mySuggester","mySuggester2");
    $query->setDictionary($array);

    $query->setBuild(true);
    $query->setCount(10);
    
    // this executes the query and returns the result
    $resultset = $client->suggester($query);
    

    // displa  y results for each term
    foreach ($resultset as $dictionary => $terms) {
        echo '<h3>' . $dictionary . '</h3>';
        foreach ($terms as $term => $termResult) {
            echo '<h4>' . $term . '</h4>';
            echo 'NumFound: '.$termResult->getNumFound().'<br/>';
            foreach ($termResult as $result) {
                $sugerencias[] = $result['term'];
                echo '- '.$result['term'].'<br/>';
            }
        }
    
        echo '<hr/>';
    }
}
function busquedaFaceta($cat){
    global $q;
    $queryFaceta = $q. " AND category:".$cat;
    var_dump($queryFaceta);
    busquedaSolr($queryFaceta);
    //$arrayTokens = checarTokens();
    //$queryExpandida = expansionConsulta($arrayTokens);
    //Expansión de la consulta con datamuse

    //busquedaSimple($);
}


?>
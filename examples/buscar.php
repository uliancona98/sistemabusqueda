<?php
use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;
error_reporting(E_ALL);
ini_set('display_errors', true);
require("lexer.php");
//$client = new Solarium\Client($adapter, $eventDispatcher, $config);
$queryExpandida ="";
$facetMenu = array();
$sugerencias = array();
$correciones = array();
$terminosConsultaExpandida = array();
$terminosConsulta = array();
$results = array();
$correctoAnalizadorSintactico = false;
$q = '';
//var_dump($client);
if (file_exists('config.php')) {
    require('config.php');
}
require $config['autoload'] ?? __DIR__.'/../vendor/autoload.php';

$pagesCount = 1;

if (! (isset($_GET['pageNumber']))) {
    //$pageNumber = 1;
    //$pagesCount = 1;


} else {
    //$pageNumber = $_GET['pageNumber'];
}
$perPageCount = 5;

if ((isset($_GET['id']))) {
    $adapter = new Curl();
    $eventDispatcher = new EventDispatcher();
    $client = new Solarium\Client($adapter, $eventDispatcher, $config);
    //var_dump($_GET['id']);
    $nombreCategoria = $_GET['id'];


    //echo($queryCat);

    busquedaSolrFaceta($nombreCategoria);

}

if ((isset($_GET['keyword']))) {
    global $sugerencias;
    $adapter = new Curl();
    $eventDispatcher = new EventDispatcher();
    $client = new Solarium\Client($adapter, $eventDispatcher, $config);

    suggestions($_GET['keyword']);
    $result = $sugerencias;
    if(!empty($result)) {
        ?>
        <ul id="sugerencias-list">
        <?php
        foreach($result as $suggest) {
        ?>
        <li onClick="selectSugest('<?php echo $suggest; ?>');"><?php echo $suggest; ?></li>
        <?php 
        } ?>
        </ul>
    <?php 
    }

}
if (! (isset($_GET['pageNumber']))) {
    $pageNumber = 1;
    //$pagesCount = 1;


} else {
    $pageNumber = $_GET['pageNumber'];
}
if ((isset($_GET['query']))) {
    $adapter = new Curl();
    $eventDispatcher = new EventDispatcher();
    $client = new Solarium\Client($adapter, $eventDispatcher, $config);


//$sql = "SELECT * FROM tbl_staff  WHERE 1";

/*if ($result = mysqli_query($conn, $sql)) {
    $rowCount = mysqli_num_rows($result);
    mysqli_free_result($result);
}*/
    $arrayTokens = checarTokens($_GET['query']);
    
    if($correctoAnalizadorSintactico){
        $queryExpandida = expansionConsulta($arrayTokens);
        //var_dump($terminosConsultaExpandida);
        //Expansión de la consulta con datamuse
        //busqueda($queryExpandida);
        /*echo "<pre>";
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
        echo "</pre>";*/
        busquedaSolr($queryExpandida);
        spellChecker();

        $rowCount = count($results);//mysqli_num_rows($result)

        $pagesCount = ceil($rowCount / $perPageCount);
    
        $lowerLimit = ($pageNumber - 1) * $perPageCount;
    }else{
        //echo "<p>Error en tu consulta</p>";
    }


}


?>
    <div class="centre">
        
        <div class="results" id="results">
            <div class="resultSection" id="resultSection">

    <?php 
    foreach ($results as $data) {
    ?>
            <div class="resultOption">
            <p align="left">Id: <?php echo $data['id'] ?></p>
            <p align="left">Título: <?php echo $data['title'] ?></p>
            <p align="left">Categorías: <?php echo $data['category'] ?></p>
            <p align="left">
                <a href="URL: <?php echo $data['url'] ?>"> <?php echo $data['url'] ?>
                </a>
            </p>
            <p align="left">Snippet: <?php echo $data['snippet'] ?></p>
            </div>
    <?php
    }
    ?>
            </div>
        </div>
    </div>



<!--<div style="height: 30px;"></div>
<table width="50%" align="center">
    <tr>

        <td valign="top" align="left"></td>


        <td valign="top" align="center">
 
	<?php
  /*  if ((isset($_GET['query']))) {

    
	for ($i = 1; $i <= $pagesCount; $i ++) {
    if ($i == $pageNumber) {
        ?>
	      <a href="javascript:void(0);" class="current"><?php echo $i ?></a>
<?php
    } else {
        ?>
	      <a href="javascript:void(0);" class="pages"
            onclick="showRecords('<?php echo $perPageCount;  ?>', '<?php echo $i; ?>');"><?php echo $i ?></a>
<?php
    } // endIf
} // endFor
    }*/ //endif
?>
</td>
        <td align="right" valign="top">
         Page 
         <?php 
        if ((isset($_GET['query']))) {

        


         echo $pageNumber; 
         ?> of 
         <?php echo $pagesCount; 
        }
         ?>
	</td>
    </tr>
</table>-->

<?php
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
                }
                
            }else{
                $array_tokens[] = $value;
            }

        }
        
        $lexer = new Lexer($array_tokens);
        $terminos = array();
        if($lexer->getCorrectoSintactico()==true){
            $correctoAnalizadorSintactico = true;
            $array_tokns=$lexer->getTokensArray();    
        }else{
            $correctoAnalizadorSintactico = false;
            echo'<script type="text/javascript">
            alert("Sintaxis de consulta errónea, corrija");
            </script>';
        }
        return $array_tokns;
    }

    //Hace expansion de consulta
    function expansionConsulta($arrayTokens){
        
        global $terminosConsultaExpandida;
        global $terminosConsulta;
        $query="";
        /*echo "<pre>";
        print_r($arrayTokens);
        echo "</pre>";*/
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
        //echo "<p>".$query."</p>";
        return $query;
    }

    //Llama a la busqueda de Solr y luego a sugerencias y luego correciones
    function busqueda($queryExpandida){
        global $terminosConsulta,$q;
        busquedaSolr($queryExpandida);
        foreach($terminosConsulta as $key => $valor){
            $q = $q." ".$valor;
        }
        spellChecker();
        //suggestions($q);
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
        $query->getHighlighting();
        $hl = $query->getHighlighting();


        $hl->setFields('title, content', "category");
        $hl->setSimplePrefix('<b>');
        $hl->setSimplePostfix('</b>');
        //Highlight
        $resultset = $client->select($query);
        $highlighting = $resultset->getHighlighting();
        //echo '<p><b>Query:</b> '.$query->getQuery().'<p/>';

        // display the total number of documents found by Solr
        //echo '<p>Se han encontrado: '.$resultset->getNumFound().' resultados</p>';

        // display facet counts
        //echo '<hr/>Facet counts for field "category":<br/>';
        $facet = $resultset->getFacetSet()->getFacet('cat');
        $i=0;
        ?>
            <div class="side-left">
                <div class="categories">
                    <h3 class="categoriesTitle">Categorías</h3>
                    <div id="categoryItems" class="categoryItems">
        <?php
        foreach ($facet as $value => $count) {
            $facetMenu[] = array($value, $count);
        ?>
                        <label onclick="aver(this.id)" class="category" id="
                        <?php
                        echo $q.'-'.$value;?>">
                        <?php echo $value.' ['.$count.']' ?>
                        </label>  

        <?php
            $i++;
        }
        ?>
                    </div>
                </div>
            </div>            
<?php
        
        foreach ($resultset as $document) {
            $highlightedDoc = $highlighting->getResult($document->id);
            if ($highlightedDoc) {
                $highlightString = '';
                foreach ($highlightedDoc as $field => $highlight) {
                    $highl = implode(' (...) ', $highlight) . '<br/>';
                    $highlightString=$highlightString.$highl;
                }
                if (is_array($document->url)) {
                    $url = implode(', ', $document->url);
                }
                if (is_array($document->category)) {
                    $categories = implode(', ', $document->category);
                }
                
                $results[]=array(
                    "id" =>$document->id,
                    "title" => $document->title,
                    "url" => $url,
                    "category" =>$categories,
                    "snippet" => $highlightString,
                    "score" => $document->score,
                    );
            }
        }
    }
    function busquedaSolrFaceta($q){
        // create a client instance
        global $facetMenu;
        global $client;
        global $results;
        $querySeparado = array();
        $querySeparado = explode("-", $q);
        // get a select query instance
        /*echo'<script type="text/javascript">
        alert("Sintaxis dsrónea'+'$q'+', corrisssja");
        </script>';*/
        echo "<script>console.log('Debug Objects: " . $q . "' );</script>";
        echo "<script>console.log('Debug Objects: " . $querySeparado[1] . "' );</script>";

        // get the facetset component
        $query = $client->createSelect();
        $query->setQuery($querySeparado[0]);
        $facetSet = $query->getFacetSet();
        // create a facet field instance and set options
        $facetSet->createFacetField('cat')->setField('category'); //faceta

        //Highlight
        $query->getHighlighting();
        $hl = $query->getHighlighting();

        $hl->setFields('title, content', "category");
        $hl->setSimplePrefix('<b>');
        $hl->setSimplePostfix('</b>');
        //Highlight

        $resultset = $client->select($query);
        $highlighting = $resultset->getHighlighting();
        //echo '<p><b>Query:</b> '.$query->getQuery().'<p/>';

        // display the total number of documents found by Solr
        //echo '<p>Se han encontrado: '.$resultset->getNumFound().' resultados</p>';

        // display facet counts
        //echo '<hr/>Facet counts for field "category":<br/>';
        $facet = $resultset->getFacetSet()->getFacet('cat');
        $i=0;
        ?>
            <div class="side-left">
                <div class="categories">
                    <h3 class="categoriesTitle">Categorías</h3>
                    <div id="categoryItems" class="categoryItems">
        <?php
        foreach ($facet as $value => $count) {
            $facetMenu[] = array($value, $count);
        ?>
                        <label onclick="aver(this.id)" class="category" id="
                        <?php
                        echo $q.'-'.$value;?>">
                        <?php echo $value.' ['.$count.']' ?>
                        </label>  

        <?php
            $i++;
        }
        ?>
                    </div>
                </div>
            </div>            
<?php
                //echo "<script>console.log('Debug Objects:', " . $resultset . " );</script>";
        foreach ($resultset as $document) {

            $highlightedDoc = $highlighting->getResult($document->id);
            if ($highlightedDoc) {
                $highlightString = '';
                foreach ($highlightedDoc as $field => $highlight) {
                    $highl = implode(' (...) ', $highlight) . '<br/>';
                    $highlightString=$highlightString.$highl;
                }
                if (is_array($document->url)) {
                    $url = implode(', ', $document->url);
                }
                if (is_array($document->category)) {
                    $categories = implode(', ', $document->category);
                }
                $categoriasDoc = $document->category;
                //var_dump($catN);

                foreach($categoriasDoc as $catN){
                    if (strcmp($catN, $querySeparado[1]) == 0) {


                        $results[]=array(
                            "id" =>$document->id,
                            "title" => $document->title,
                            "url" => $url,
                            "category" =>$categories,
                            "snippet" => $highlightString,
                            "score" => $document->score,
                            );
                            break;
                    }
                }
            
            }
        }
    }
    function spellChecker(){
        global $correciones, $client, $terminosConsulta;
        //var_dump($client);
        // get a select query instance
        ?>
        <div class="side-right">
            <div class="corrections">
                <h3 class="correctionsTitle">Correcciones</h3>
                <div id="correctionItems" class="correctionItems">
                    <!-- <label class="correction">opcion3</label>
                    <label class="correction">opcion3</label>
                    <label class="correction">opcion3</label> -->
        <?php

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
            foreach ($collations as $collation) {
                /*echo 'Query: '.$collation->getQuery().'<br/>';
                echo 'Hits: '.$collation->getHits().'<br/>';*/
                //echo 'Correciones:<br/>';
                foreach ($collation->getCorrections() as $input => $correction) {
                    $correciones[] = ($input . ' => ' . $correction);
                    //echo $input . ' => ' . $correction .'<br/>';
                    ?>
                    <label class="correction"><?php 
                    echo($input . ' => ' . $correction);
                    ?>
                    </label>
                    <?php
                }
                //echo '<hr/>';
            }
        }?>
            </div>
                </div>
                    </div>
        <?php
        

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
            //echo '<h3>' . $dictionary . '</h3>';
            foreach ($terms as $term => $termResult) {
                //echo '<h4>' . $term . '</h4>';
                //echo 'NumFound: '.$termResult->getNumFound().'<br/>';
                foreach ($termResult as $result) {
                    $sugerencias[] = $result['term'];
                    //echo '- '.$result['term'].'<br/>';
                }
            }
        
            //echo '<hr/>';
        }
    }
    function busquedaFaceta($cat){
        global $q;
        $queryFaceta = $q. " AND category:".$cat;
        //var_dump($queryFaceta);
        busquedaSolr($queryFaceta);
        //$arrayTokens = checarTokens();
        //$queryExpandida = expansionConsulta($arrayTokens);
        //Expansión de la consulta con datamuse

        //busquedaSimple($);
    }

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

    ?>
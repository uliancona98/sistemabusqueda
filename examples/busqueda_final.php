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
?>
<html>
    <head>
        <title>Sistema de busqueda Solr</title>
    </head>
    <body>
        <article>
        <h1>Ingresa una busqueda</h1>
        <p>Para que tu busqueda sea exitosa tiene que empezar con un '(' y terminar
             con ')' y cada palabra y caracter debe estar separada por espacio.</p>
        <p> - (galletas AND calabaza)<br>
            - (galletas OR calabaza) NOT (donas NOT pera)<br>
            - (galletas papas OR calabaza) AND (donas OR pera)<br>
            - (carne leche)<br>
            Se acepta también la consulta de una frase sin parentesis ejemplo: <br> 
            -carne AND leche <br>
            -carne leche huevo <br>
            -CARNE AND leche NOT huevo
        </p>
        <form action="" method="GET">
            <label for="query"></label>
            <input type="text" name="query" id="">
            <button type="submit" name="button">Enviar</button>
        </form>
        
        </article><br>
    </body>
</html>
<?php
    //htmlFooter();
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

    require("lexer.php");
    $facetMenu = array();
    $sugerencias = array();
    $correciones = array();
    $terminosConsultaExpandida = array();
    $terminosConsulta = array();
    $results = array();
    $correctoAnalizadorSintactico = false;

    $q = '';
    $client = new Solarium\Client($adapter, $eventDispatcher, $config);
    
    if(isset($_GET['query'])){
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
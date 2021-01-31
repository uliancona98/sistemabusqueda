<?php

class Lexer{

    protected $_lineas;
    protected $_numero;
    protected $_token;
    private $_tokens = array();
    private $_correctoSintactico;
    protected $_afd = array(
        0 => array(false, false, 1, false, false),
        1 => array(2, false, false, false, false),
        2 => array(2, 3, false, 4, false),
        3 => array(2, false, false, false, false),
        4 => array(false, 5, false, false, true),
        5 => array(false, false, 1, false, false)

    );

function getTokensArray(){
    return  $this->_tokens;
} 

function getCorrectoSintactico(){
    return $this->_correctoSintactico;
}
    protected $_tokenList = array(
        //Reservadas
        "OR"  => "OPERADOR OR",
        "AND"  => "OPERADOR AND",
        "NOT" => "OPERADOR NOT",
        "(" => "APERTURA",
        ")" => "CIERRE"
    );

    protected $_delimitadores = ' "'; // Los delimitadores son ESPACIO y COMILLA_DOBLE
    
    function __construct($linea){
        //$this->_lineas   = preg_split("/(\r\n|\n|\r)/", trim($txt));
 
        /*foreach($this->_lineas as $numero => $linea){
            $this->_numero = $numero;
            $this->lexico($linea);
        }*/
        $this->_numero = 0;
        $this->lexico($linea);
        //$this->printTokens();
    }

    function lexico($linea){
        //$tokens_line = new StringTokenizer($linea, $this->_delimitadores);
        foreach ($linea as $pos => $tok) {
            $this->_token = $tok;
            $this->_tokens[] =  $this->returnTokenItem();
        }
        $this->_correctoSintactico = $this->analizadorSintactico();
    }

    function analizadorSintactico(){
        $i = 0; $estado = 0;
        $transiciones = count($this->_tokens);
        $tokn_input_list =$this->_tokens;
        for ( $id_tokn = 0; $id_tokn<=$transiciones;  $id_tokn++) {
            if($i==$transiciones) $entrada = 4; // COLUMNA DE ACEPTACION
            else{
                $lexema = $tokn_input_list[$id_tokn]["lexema"];
                if($this->esAndOrNot($lexema))           $entrada = 1; // Letra
                elseif($this->esApertura($lexema))    $entrada = 2; // Digito
                elseif($this->esCierre($lexema))    $entrada = 3; // Digito
                elseif($this->esPalabraClave($lexema))    $entrada = 0; // Digito
                else return false;
            }
            $estado = $this->_afd[$estado][$entrada];
            if($estado === false || $estado === true) return $estado;
            $i++;
        }
    }


    function esApertura($c=null){
        $result1 = strcasecmp("(", $c);
        if($result1==0){
            return true;
        }
        return false; 
    }
    function esCierre($c=null){
        $result1 = strcasecmp(")", $c);
        if($result1==0){
            return true;
        }
        return false; 
    }
    function esAndOrNot($c=null){
        $result1 = strcasecmp("AND", $c);
        $result2= strcasecmp("OR", $c);
        $result3= strcasecmp("NOT", $c);

        if($result1==0 || $result2==0 || $result3==0){
            return true;
        }
        return false;
    }

    function esNot($c=null){
        $result1 = strcasecmp("NOT", $c);
        if($result1 ==0){
            return true;
        }else{
            return false;
        }
    }

    function esPalabraClave($c=null){
        return true;
    }

    function buscarExpresion($c=null){
        if($c==null) $c = $this->_token;
        foreach($this->_tokenList as $exp => $name){
           $result = strcasecmp($exp, $c);
           if($result == 0){
            return $name;
           }else{
               if (preg_match("/\w/", $c)) {
                   return "WORD";
               }else{
                   return false;
               }

           } 
        }
        //return false;
    }

    function returnTokenItem($v=false){
        if($v==false) $v = $this->_tokenList;
        else $token =  $v;
        if(is_array($v)) $token = $this->buscarExpresion();
        return array(
            'lexema' => $this->_token,
            'token' => $token,
            'linea' => $this->_numero+1
        ); 
    }

    function printTokens(){
        echo "
        <table class='lexer'>
            <thead>
                <tr>
                    <th>NRO</th>
                    <th>LEXEMA</th>
                    <th>TIPO</th>
                    <th>LINEA</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($this->_tokens as $num => $item) {
            echo "<tr><td>".($num+1)."</td>";
            foreach ($item as $valor){
                if($valor == "ERROR") $valor = "<b>".$valor."</b>";
                echo "<td>".$valor."</td>";
            }
            echo "</tr>";
        }


        echo "</tbody>
        </table>";
    }

    function printTokenList(){
        echo "
        <table class='lexer2'>
            <thead>
                <tr>
                    <th>LEXEMA</th>
                    <th>TIPO</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($this->fjhb  as $lexema => $tipo) {
            echo "<tr><td>".$lexema."</td><td>".$tipo."</td></tr>";
        }


        echo "</tbody>
        </table>";
    }

}
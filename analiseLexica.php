<?php
#Leitura do arquivo de entrada em txt;
$file_lines = file('entrada.txt');
$fonte = '';
foreach ($file_lines as $line) {
    $fonte = $line;
}

$arrFonte = explode(' ',$fonte);    #Divite o texto de entrada pelos espaços;
$arrTokens = [];
$arrSimbolos = [];
$arrErros = [];
$terminador = false;    #Indentifica a ausência do caractere terminador;

foreach($arrFonte as $f){
    if(strstr($f,';')){ #Se um caractere terminador existir, ele não estará separado por um espaço;
        $faux = explode(';',$f);
        $token = categorizar($faux[0], $fonte, $arrSimbolos, $arrErros);
        if($token != null){
            array_push($arrTokens, $token);   
        }
        #A entrada tem um terminador;
        $terminador = true;

        $token = categorizar(';', $fonte, $arrSimbolos, $arrErros);
        if($token != null){
            array_push($arrTokens, $token);   
        }

        /*  O trecho comentado abaixo é para parar o compilador em caso de erro, assim o programa lê todo o texto
        mas identifica os erros no final;*/

        /*if(sizeof($arrErros)>0){
            break;
        }*/
        break;
    }else{
        $token = categorizar($f, $fonte, $arrSimbolos, $arrErros);
        if($token != null){
            array_push($arrTokens, $token); 
        }
        /*if(sizeof($arrErros)>0){
            break;
        }   */     
    }
}

#Se terminou a leitura do arquivo e ainda não foi encontrado terminador, um erro é lançado;
if($terminador == false){
    array_push($arrErros, ['Caractere Terminador não encontrado.'=>';', 'posicao'=>null]);
}

#Apenas para visualização;
/*$arrFinal = ["tabelaTokens"=>$arrTokens, "tabelaSimbolos"=>$arrSimbolos, "tabelaErros"=>$arrErros];
echo json_encode($arrFinal);*/


#Gera os arquivos JSON;
$fp = fopen('tokens.json', 'w');
fwrite($fp, json_encode($arrTokens));
fclose($fp);

$fp = fopen('simbolos.json', 'w');
fwrite($fp, json_encode($arrSimbolos));
fclose($fp);

$fp = fopen('erro.json', 'w');
fwrite($fp, json_encode($arrErros));
fclose($fp);


/** FUNÇÕES **/

#Função Categorizar faz as analises e categoriza os elementos do texto de entrada.
#Ela retorna um objeto da class Token, criado abaixo, ou uma mensagem de erro no array de erros;
function categorizar($str, $fonte, &$arrSimbolos, &$arrErros){
    $arrReservadas = ['while','do'];    #Conjunto de palavras reservadas;
    $arrOperadores = ['<','=','+'];     #Conjunto de Operadores;
    $arrIdentificadores = ['i','j'];    #Conjunto de Identificadores;

    $t = new Token();
    if(in_array($str, $arrReservadas)){
        $t->token = $str;
        $t->identificacao = 'Palavra Reservada';
        $t->tamanho = strlen($str);
        $pos = strpos($fonte, $str);
        $t->posicao = [0,$pos];
        return $t;
    }elseif(in_array($str, $arrOperadores)){
        $t->token = $str;
        $t->identificacao = 'Operador';
        $t->tamanho = strlen($str);
        $pos = strpos($fonte, $str);
        $t->posicao = [0,$pos];
        return $t;
    }elseif(in_array($str, $arrIdentificadores)){
        $t->token = $str;
        $id = tabelaSimbolos($str, $arrSimbolos);
        $t->identificacao = ['Identificador',$id];
        $t->tamanho = strlen($str);
        $pos = strpos($fonte, $str);
        $t->posicao = [0,$pos];
        return $t;
    }elseif(is_numeric($str)){
        $t->token = $str;
        $id = tabelaSimbolos($str, $arrSimbolos);
        $t->identificacao = ['Constante',$id];
        $t->tamanho = strlen($str);
        $pos = strpos($fonte, $str);
        $t->posicao = [0,$pos];
        return $t;
    }elseif($str == ";"){
        $t->token = $str;
        $t->identificacao = 'Terminador';
        $t->tamanho = strlen($str);
        $pos = strpos($fonte, $str);
        $t->posicao = [0,$pos];
        return $t;
    }elseif($str != " "){   #Se o token não for identificado, nem um espaço em branco;
        array_push($arrErros, ['Caractere invalido'=>$str, 'posicao'=>strpos($fonte, $str)]);
    }
}

#Função para gerar a tabela de simbolos;
#Por se tratar de um array, o índice do simbolo é sua posição no array;
function tabelaSimbolos($simbolo, &$arrSimbolos){
    $id = in_array($simbolo,$arrSimbolos);
    if($id == false){
        array_push($arrSimbolos, $simbolo);
        return array_search($simbolo,$arrSimbolos);
    }else{
        return $id;
    }
}



class Token{
    public $token;
    public $identificacao;
    public $tamanho;
    public $posicao = [];
}


?>
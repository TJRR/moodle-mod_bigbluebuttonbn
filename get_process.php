<?php

function get_cod_tj(){
    $INTERVALO_HORAS_PERMITE_VALIDAR_AUTENTICACAO = 2;
    $ddMM = "";
    $senha = "";
    $codigoSistema = "6745231";
    $dataAtualSuperior = time() + ($INTERVALO_HORAS_PERMITE_VALIDAR_AUTENTICACAO * 60 * 60);
    @$dataBase = mktime(23,59,59,date('m'),date('d'),date('Y'));
    $dataHoraAtual = time();
    @$ddMM = date('Ymd', $dataHoraAtual);
    if($dataHoraAtual>$dataBase){
      $ddMM = date('Ymd', $dataAtualSuperior);
    }
    return md5($codigoSistema.$ddMM);
  }

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');

$id = required_param('id', PARAM_INT); // Course Module ID, or

$params = array('id' => $id);

$course = $DB->get_record('course', $params, '*', MUST_EXIST);

require_login($course);

if(isset($_POST['nrprocesso'])){

  $url = 'https://projudi.tjrr.jus.br/projudi/webservices/consultaProcessualWebService';

  @$result=curl_exec($ch);

  $ch = curl_init();
  //curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/projudi.cer");
  $body = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:impl="http://impl.consulta.webservice.projudi.gov/">
     <soapenv:Header/>
     <soapenv:Body>
        <impl:consultarProcesso>
           <!--Optional:-->
           <impl:numeroUnicoProcesso>'.str_replace('-','',str_replace('.','',$_POST['nrprocesso'])).'</impl:numeroUnicoProcesso>
           <!--Optional:-->
           <impl:sistemaTribunal>FUT</impl:sistemaTribunal>
           <!--Optional:-->
           <impl:systemPass>'.get_cod_tj().'</impl:systemPass>
        </impl:consultarProcesso>
     </soapenv:Body>
  </soapenv:Envelope>';

  $defaults = array(
  CURLOPT_URL => $url,
  CURLOPT_POST => true,
  CURLOPT_RETURNTRANSFER => 1,
  CURLOPT_SSL_VERIFYPEER => true,
  CURLOPT_POSTFIELDS => $body,
  CURLOPT_HTTPHEADER => array('Content-Type: text/xml; charset=utf-8')
  );
  @$fp = fopen("./curl.log", "w");
  @curl_setopt($ch, CURLOPT_STDERR, $fp);
  curl_setopt($ch, CURLOPT_VERBOSE, true);
  curl_setopt_array($ch, $defaults);
  $result = curl_exec($ch);
  curl_close($ch);

  echo $result;
}

?>

<?php

function get_cod_tj(){
    $INTERVALO_HORAS_PERMITE_VALIDAR_AUTENTICACAO = 2;
    $ddMM = "";
    $senha = "";
    $codigoSistema = "6745231";
    $dataAtualSuperior = time() + ($INTERVALO_HORAS_PERMITE_VALIDAR_AUTENTICACAO * 60 * 60);
    $dataBase = mktime(23,59,59,date('m'),date('d'),date('Y'));
    $dataHoraAtual = time();
    $ddMM = date('Ymd', $dataHoraAtual);
    if($dataHoraAtual>$dataBase){
      $ddMM = date('Ymd', $dataAtualSuperior);
    }
    return md5($codigoSistema.$ddMM);
  }

$url = 'https://projudi.tjrr.jus.br/projudi/webservices/consultaProcessualWebService';

@$result=curl_exec($ch);

$ch = curl_init();
curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/projudi.cer");
$body = '<?xml version="1.0" ?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Body>
    <ns2:consultarAudienciaProcesso xmlns:ns3="http://impl.processo.webservice.projudi.gov/" xmlns:ns2="http://impl.consulta.webservice.projudi.gov/">
      <ns2:numeroUnicoProcesso>'.$_GET['nrprocesso'].'</ns2:numeroUnicoProcesso>
      <ns2:sistemaTribunal>FUT</ns2:sistemaTribunal>
      <ns2:systemPass>'.get_cod_tj().'</ns2:systemPass>
    </ns2:consultarAudienciaProcesso>
  </S:Body>
</S:Envelope>';

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

?>

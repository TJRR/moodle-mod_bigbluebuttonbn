<?php

$url = 'http://projudi-mni.tjrr.jus.br/projudi/webservices/consultaProcessualWebService';

@$result=curl_exec($ch);

$ch = curl_init();
curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/projudi.cer");
$body = '<?xml version="1.0" ?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Body>
    <ns2:consultarAudienciaProcesso xmlns:ns3="http://impl.processo.webservice.projudi.gov/" xmlns:ns2="http://impl.consulta.webservice.projudi.gov/">
      <ns2:numeroUnicoProcesso>'.$_GET['nrprocesso'].'</ns2:numeroUnicoProcesso>
      <ns2:sistemaTribunal>FUT</ns2:sistemaTribunal>
      <ns2:systemPass>281ca63549322564c88ce42dbda16a48</ns2:systemPass>
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
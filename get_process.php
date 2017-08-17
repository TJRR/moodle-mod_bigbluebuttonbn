<?php

$url = 'https://projudi.tjrr.jus.br:443/projudi/webservices/consultaProcessualWebService';

@$result=curl_exec($ch);

$ch = curl_init();
curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/certificado_projudi.cer");
$body = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:impl="http://impl.consulta.webservice.projudi.gov/">
   <soapenv:Header/>
   <soapenv:Body>
      <impl:consultarProcesso>
         <!--Optional:-->
         <impl:numeroUnicoProcesso>'.$_GET['nrprocesso'].'</impl:numeroUnicoProcesso>
         <!--Optional:-->
         <impl:sistemaTribunal>FUT</impl:sistemaTribunal>
         <!--Optional:-->
         <impl:systemPass>3bd3d2e6d73513cee07cdbd39ce00cf5</impl:systemPass>
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

?>

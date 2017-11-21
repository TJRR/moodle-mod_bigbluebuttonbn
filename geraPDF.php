<?php
 include("mpdf60/mpdf.php");
 require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
 global $DB;

 $sql = 'SELECT * FROM {bigbluebuttonbn_a_record} WHERE id = ?';
 $aud_gravada = $DB->get_record_sql($sql, array($_GET['id']));

 $sql2 = 'SELECT * FROM {bigbluebuttonbn} WHERE id = ?';
 $processo = $DB->get_record_sql($sql2, array($aud_gravada->id_bbb));

 $sql3 = 'SELECT * FROM {bigbluebuttonbn_partes} WHERE id_bbb = ? and oab=0';
 $partes = $DB->get_records_sql($sql3, array($aud_gravada->id_bbb));

 if($processo->segredojustica == ''){
   $sigilo = "Não";
 }else{
   $sigilo = "Sim";
 }

 $ano = date('Y',$aud_gravada->expectedate/1000);
 $data = date('d/m/Y',$aud_gravada->expectedate/1000);
 $hora = date('h:i',$aud_gravada->expectedate/1000);

 $partes_text = "";
 foreach ($partes as $parte) {
   if($partes_text!=""){
     $partes_text .= " e ";
   }
   $partes_text .= $parte->name;
 }

 $html = "
 <fieldset>
 <h1>AUDIÊNCIA POR VIDEOCONFERÊNCIA</h1>
 <p class='center sub-titulo'>
 Processo: ".$aud_gravada->nrprocesso."
 <br>Nível de sigilo: ".$sigilo."
 </p>
 <p>Aos ".$data.", às ".$hora.", na ".$aud_gravada->placeidtribunal.", as partes ".$partes_text." participaram da audiência por videoconferência que encotra-se disponível em ".$aud_gravada->link." .</p>
 <p>".$aud_gravada->hearingidtribunal."</p>
 </fieldset>
 ";
 $diretorio = '../../../../moodledata/PDF/';
 $mpdf=new mPDF();
 $mpdf->SetDisplayMode('fullpage');
 $css = file_get_contents("css/estilo.css");
 $mpdf->WriteHTML($css,1);
 $mpdf->WriteHTML($html);

 //arrumando o diretorio para salvar
 if(!is_dir($diretorio)){
    mkdir ($diretorio, 0777 ); // criar o diretorio
 }
 $diretorio = $diretorio.$ano;
 if(!is_dir($diretorio)){
    mkdir ($diretorio, 0777 ); // criar o diretorio
 }
 $diretorio = $diretorio."/".$aud_gravada->placeidtribunal;
 if(!is_dir($diretorio)){
    mkdir ($diretorio, 0777 ); // criar o diretorio
 }

 $mpdf->Output($diretorio.'/'.$aud_gravada->recordid.'.pdf','F');
 //endereçamento no servidor
 //$mpdf->Output('../../../PDF/pdf_tj_teste.pdf','F');

 exit;

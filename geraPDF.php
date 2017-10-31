<?php
 include("mpdf60/mpdf.php");

 $html = "
 <fieldset>
 <h1>Teste</h1>
 <p class='center sub-titulo'>
 Nº <strong>908987987098098</strong> -
 Tipo <strong>Conciliação</strong>
 </p>
 <p>Olá</p>
 </fieldset>
 ";

 $mpdf=new mPDF();
 $mpdf->SetDisplayMode('fullpage');
 $css = file_get_contents("css/estilo.css");
 $mpdf->WriteHTML($css,1);
 $mpdf->WriteHTML($html);
 $mpdf->Output('../../../PDF/pdf_tj_teste.pdf','F');

 exit;

<?php
/**
 * View a BigBlueButton room
 *
 * @package   mod_bigbluebuttonbn
 * @author    Alan Velasques Santos  (alanvelasques [at] gmail [dt] com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');

//Esta recebendo BBBSESSION e COURSE
global $CFG,$DB,$COURSE,$PAGE,$USER;

$bbbendpoint = bigbluebuttonbn_get_cfg_server_url();
$bbbshared_secret = bigbluebuttonbn_get_cfg_shared_secret();

$sql = "SELECT * FROM {bigbluebuttonbn} bbb where bbb.meetingid = '".$_GET['meetingId']."'";

$bbb = $DB->get_record_sql($sql);

// SQL para buscar o magistrado responsável
$sql = "SELECT c.id AS id, roleid, c.fullname, u.username, u.firstname, u.lastname, u.email ".
        "FROM {role_assignments} ra, {user} u, {course} c, {context} cxt ".
        "WHERE ra.userid = u.id ".
        "AND ra.contextid = cxt.id ".
        "AND cxt.contextlevel =50 ".
        "AND cxt.instanceid = c.id ".
        "AND c.id = ? ".
        "AND roleid = 14";
//O Roleid de magistrado no TJ é 14
$magistrado_search = $DB->get_records_sql($sql, array($bbb->course));
//Ainda não sei bem qual dos nomes pegar... mas é algum destes (fullname, firstname + lastname)
if($magistrado_search){
  foreach ($magistrado_search as $key) {
    $nome_magistrado = $key->firstname.' '.$key->lastname;
    break;
  }
}else{
  $nome_magistrado = "[[Nome do Magistrado]]";
}

if (isset($bbb->record) && $bbb->record) {

    $meetingID = '';
    $results = bigbluebuttonbn_getRecordedMeetings($bbb->course, $bbb->id);

    if ($results) {
        //Eliminates duplicates
        $mIDs = array();
        foreach ($results as $result) {
            $mIDs[$result->meetingid] = $result->meetingid;
        }
        //Generates the meetingID string
        foreach ($mIDs as $mID) {
            if (strlen($meetingID) > 0) {
                $meetingID .= ',';
            }
            $meetingID .= $mID;
        }
    }

    // Get actual recordings
    if ($meetingID != '') {
        $recordings = bigbluebuttonbn_getRecordingsArray($meetingID, $bbbendpoint, $bbbshared_secret);
    } else {
        $recordings = Array();
    }
    // Get recording links
    $recordings_imported = bigbluebuttonbn_getRecordingsImportedArray($bbb->course, $bbb->id);
    // Merge the recordings
    $recordings = array_merge($recordings, $recordings_imported);

    $segredo = 0;
    foreach($recordings as $record){
      $sql = 'SELECT * FROM {bigbluebuttonbn_a_record} WHERE guid = ?';
      $aud_gravada = $DB->get_record_sql($sql, array($record['recordID']));

      //Pegando dados do processo para colocar na audiencia
      $sql = 'SELECT * FROM {bigbluebuttonbn} WHERE id = ?';
      $processo = $DB->get_record_sql($sql, array($bbb->id));
      if($processo->segredojustica == ''){
        $segredo = 0;
      }else{
        $segredo = 1;
      }

      if(!$aud_gravada){
        //Montando o campo descrição
        $partes = $DB->get_records('bigbluebuttonbn_partes', array('id_bbb'=>$bbb->id,'oab'=>'0'));

        $date_ = new DateTime();
        $date_->setTimestamp($record['startTime']/1000);

        $ano = date('Y',$record['startTime']/1000);
        $hora = date_format($date_->sub(new DateInterval('PT2H')), 'H');
        $minuto = date_format($date_->sub(new DateInterval('PT2H')), 'i');
        setlocale(LC_TIME, 'pt_BR.utf-8');
        date_default_timezone_set('America/Sao_Paulo');
        $data = strftime('%d de %B de %Y',$record['startTime']/1000);

        if($processo->segredojustica == ''){
          $sigilo = "Público";
        }else{
          $sigilo = "Sim";
        }

        $partes_text = "";
        foreach ($partes as $parte) {
          if($partes_text!=""){
            $partes_text .= " e ";
          }
          $partes_text .= $parte->name;
        }
        $url_gravacao = $CFG->wwwroot.'/mod/bigbluebuttonbn/playback.php?id='.$_GET['id'].'&recordID='.$record['recordID'].'&meetingID='.$record['meetingID'];
        $descricao = "<p>Aos ".$data.", às ".$hora."h".$minuto."min, na Sala de Audiências da ".$course->fullname.", presentes o Juiz ".$nome_magistrado." e as partes: ".$partes_text.".
        Aberta a audiência referente ao processo acima identificado, o Juiz esclareceu às partes que o depoimento será registrado através de gravação de áudio e vídeo digital que será acostado aos autos
        e ficará disponível no endereço eletrônico: <a href='".$url_gravacao."'>".$url_gravacao." .</a></p>
        <p>Foram tomados os depoimentos, ouvido o Ministério Público e a Defesa.</p>";
        $year = date("Y", $record['startTime']/1000);
        $aud = new stdClass();
        $aud->id_bbb=$bbb->id;
        $aud->placeidtribunal=$course->fullname;
        $aud->hearingidtribunal=$record['meta_bbb-recording-description'];
        $aud->guid=$record['recordID'];
        $aud->nrprocesso=$record['meetingName'];
        $aud->expectedate=$processo->openingtime;//$record['startTime'];
        $aud->publishdate=$record['endTime'];
        $aud->basefilepath="172.16.1.62/VC/".$year."/".$course->shortname."/";
        $aud->size=$record['size'];
        $aud->hash=md5($record['recordID']);
        $aud->duration=$record['playbacks']['presentation']['length'];
        $aud->files='[{"FileName":"VC/'.$year.'/'.$course->shortname.'/'.$record['recordID'].'.pdf"'.','.'"Size":'.$aud->size.','.'"Hash":"'.$aud->hash.'",'.'"Duration":'.$aud->duration.'}]';
        $aud->meetingid=$record['meetingID'];
        $aud->link=$record['playbacks']['presentation']['url'];
        $aud->name=$record['meta_bbb-recording-name'];
        $aud->description=$descricao;//$record['meta_bbb-recording-description'];
        $aud->tags=$record['meta_bbb-recording-tags'];
        $aud->id_course=$_GET['id'];
        $aud->timecreated = strtotime(date("Y-m-d H:i:s"));
        $aud_id = $DB->insert_record('bigbluebuttonbn_a_record', $aud);
        gera_pdf($aud_id);
      }else{
        $aud_gravada->link=$record['playbacks']['presentation']['url'];
        $DB->update_record('bigbluebuttonbn_a_record', $aud_gravada, false);
        if($aud_gravada->id_course==''||$aud_gravada->id_course==0){
          $aud_gravada->id_course=$_GET['id'];
          $aud_gravada->name=$record['meta_bbb-recording-name'];
          $aud_gravada->description=$record['meta_bbb-recording-description'];
          $aud_gravada->tags=$record['meta_bbb-recording-tags'];
          $DB->update_record('bigbluebuttonbn_a_record', $aud_gravada, false);
        }
        gera_pdf($aud_gravada->id);
      }
    }
}

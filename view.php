<?php
/**
 * View a BigBlueButton room
 *
 * @package   mod_bigbluebuttonbn
 * @author    Fred Dixon  (ffdixon [at] blindsidenetworks [dt] com)
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 * @copyright 2010-2015 Blindside Networks Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/Mobile_Detect.php');

$id = required_param('id', PARAM_INT); // Course Module ID, or
$b  = optional_param('n', 0, PARAM_INT); // bigbluebuttonbn instance ID
$group  = optional_param('group', 0, PARAM_INT); // group instance ID

if ($id) {
    $cm = get_coursemodule_from_id('bigbluebuttonbn', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $bigbluebuttonbn = $DB->get_record('bigbluebuttonbn', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($b) {
    $bigbluebuttonbn = $DB->get_record('bigbluebuttonbn', array('id' => $b), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $bigbluebuttonbn->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('bigbluebuttonbn', $bigbluebuttonbn->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('view_error_url_missing_parameters', 'bigbluebuttonbn'));
}

require_login($course, true, $cm);

$version_major = bigbluebuttonbn_get_moodle_version_major();
if ($version_major < '2013111800') {
    //This is valid before v2.6
    $module = $DB->get_record('modules', array('name' => 'bigbluebuttonbn'));
    $module_version = $module->version;
} else {
    //This is valid after v2.6
    $module_version = get_config('mod_bigbluebuttonbn', 'version');
}
$context = bigbluebuttonbn_get_context_module($cm->id);


bigbluebuttonbn_event_log(BIGBLUEBUTTON_EVENT_ACTIVITY_VIEWED, $bigbluebuttonbn, $context, $cm);

////////////////////////////////////////////////
/////  BigBlueButton Session Setup Starts  /////
////////////////////////////////////////////////
// BigBluebuttonBN activity data
$bbbsession['bigbluebuttonbn'] = $bigbluebuttonbn;

// User data
$bbbsession['username'] = fullname($USER);
$bbbsession['userID'] = $USER->id;
if (isguestuser()) {
    $bbbsession['roles'] = bigbluebuttonbn_get_guest_role();
} else {
    $bbbsession['roles'] = bigbluebuttonbn_get_user_roles($context, $USER->id);
}

// User roles
if ($bigbluebuttonbn->participants == null || $bigbluebuttonbn->participants == "" || $bigbluebuttonbn->participants == "[]") {
    //The room that is being used comes from a previous version
    $bbbsession['moderator'] = has_capability('mod/bigbluebuttonbn:moderate', $context);
} else {
    $bbbsession['moderator'] = bigbluebuttonbn_is_moderator($bbbsession['userID'], $bbbsession['roles'], $bigbluebuttonbn->participants);
}
$bbbsession['administrator'] = has_capability('moodle/category:manage', $context);
$bbbsession['managerecordings'] = ($bbbsession['administrator'] || has_capability('mod/bigbluebuttonbn:managerecordings', $context));

// BigBlueButton server data
$bbbsession['endpoint'] = bigbluebuttonbn_get_cfg_server_url();
$bbbsession['shared_secret'] = bigbluebuttonbn_get_cfg_shared_secret();

// Server data
$bbbsession['modPW'] = $bigbluebuttonbn->moderatorpass;
$bbbsession['viewerPW'] = $bigbluebuttonbn->viewerpass;

// Database info related to the activity
$bbbsession['meetingdescription'] = $bigbluebuttonbn->intro;
$bbbsession['welcome'] = $bigbluebuttonbn->welcome;
if (!isset($bbbsession['welcome']) || $bbbsession['welcome'] == '') {
    $bbbsession['welcome'] = get_string('mod_form_field_welcome_default', 'bigbluebuttonbn');
}

$bbbsession['userlimit'] = bigbluebuttonbn_get_cfg_userlimit_editable() ? intval($bigbluebuttonbn->userlimit) : intval(bigbluebuttonbn_get_cfg_userlimit_default());
$bbbsession['voicebridge'] = ($bigbluebuttonbn->voicebridge > 0) ? 70000 + $bigbluebuttonbn->voicebridge : $bigbluebuttonbn->voicebridge;
$bbbsession['wait'] = $bigbluebuttonbn->wait;
$bbbsession['record'] = $bigbluebuttonbn->record;
if ($bigbluebuttonbn->record) {
    $bbbsession['welcome'] .= '<br><br>' . get_string('bbbrecordwarning', 'bigbluebuttonbn');
}
//Foi colocado um de forma fixa para desativar as taggins manualmente...
//para reativar basta colocar //$bigbluebuttonbn->tagging; no lugar do 0
$bbbsession['tagging'] = 0;

$bbbsession['openingtime'] = $bigbluebuttonbn->openingtime;
$bbbsession['closingtime'] = $bigbluebuttonbn->closingtime;

// Additional info related to the course
$bbbsession['course'] = $course;
$bbbsession['coursename'] = $course->fullname;
$bbbsession['cm'] = $cm;
$bbbsession['context'] = $context;

// Metadata (origin)
$bbbsession['origin'] = "Moodle";
$bbbsession['originVersion'] = $CFG->release;
$parsedUrl = parse_url($CFG->wwwroot);
$bbbsession['originServerName'] = $parsedUrl['host'];
$bbbsession['originServerUrl'] = $CFG->wwwroot;
$bbbsession['originServerCommonName'] = '';
$bbbsession['originTag'] = 'moodle-mod_bigbluebuttonbn (' . $module_version . ')';

// Mobile Detection
$bbbsession['detectmobile'] = $CFG->bigbluebuttonbn_detect_mobile;
$bbbsession['ismobilesession'] = bigbluebutton_is_device_for_mobile_client();
////////////////////////////////////////////////
/////   BigBlueButton Session Setup Ends   /////
////////////////////////////////////////////////

// Validates if the BigBlueButton server is running
$serverVersion = bigbluebuttonbn_getServerVersion($bbbsession['endpoint']);
if (!isset($serverVersion)) { //Server is not working
    if ($bbbsession['administrator']) {
            print_error('view_error_unable_join', 'bigbluebuttonbn', $CFG->wwwroot . '/admin/settings.php?section=modsettingbigbluebuttonbn');
    } else if ($bbbsession['moderator']) {
            print_error('view_error_unable_join_teacher', 'bigbluebuttonbn', $CFG->wwwroot . '/course/view.php?id=' . $bigbluebuttonbn->course);
    } else {
            print_error('view_error_unable_join_student', 'bigbluebuttonbn', $CFG->wwwroot . '/course/view.php?id=' . $bigbluebuttonbn->course);
    }
    } else {
    $xml = bigbluebuttonbn_wrap_xml_load_file(bigbluebuttonbn_getMeetingsURL($bbbsession['endpoint'], $bbbsession['shared_secret']));
    if (!isset($xml) || !isset($xml->returncode) || $xml->returncode == 'FAILED') { // The shared secret is wrong
        if ($bbbsession['administrator']) {
                    print_error('view_error_unable_join', 'bigbluebuttonbn', $CFG->wwwroot . '/admin/settings.php?section=modsettingbigbluebuttonbn');
        } else if ($bbbsession['moderator']) {
                    print_error('view_error_unable_join_teacher', 'bigbluebuttonbn', $CFG->wwwroot . '/course/view.php?id=' . $bigbluebuttonbn->course);
        } else {
                    print_error('view_error_unable_join_student', 'bigbluebuttonbn', $CFG->wwwroot . '/course/view.php?id=' . $bigbluebuttonbn->course);
        }
    }
}

// Mark viewed by user (if required)
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Print the page header
$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/mod/bigbluebuttonbn/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($bigbluebuttonbn->name));
$PAGE->set_cacheable(false);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

// Validate if the user is in a role allowed to join
if (!has_capability('moodle/category:manage', $context) && !has_capability('mod/bigbluebuttonbn:join', $context)) {
    echo $OUTPUT->header();
    if (isguestuser()) {
        echo $OUTPUT->confirm('<p>' . get_string('view_noguests', 'bigbluebuttonbn') . '</p>' . get_string('liketologin'),
            get_login_url(), $CFG->wwwroot . '/course/view.php?id=' . $course->id);
    } else {
        echo $OUTPUT->confirm('<p>' . get_string('view_nojoin', 'bigbluebuttonbn') . '</p>' . get_string('liketologin'),
            get_login_url(), $CFG->wwwroot . '/course/view.php?id=' . $course->id);
    }

    echo $OUTPUT->footer();
    exit;
}

// Operation URLs
$bbbsession['courseURL'] = $CFG->wwwroot . '/course/view.php?id=' . $bigbluebuttonbn->course;
$bbbsession['logoutURL'] = $CFG->wwwroot . '/mod/bigbluebuttonbn/bbb_view.php?action=logout&id=' . $id . '&bn=' . $bbbsession['bigbluebuttonbn']->id;
$bbbsession['recordingReadyURL'] = $CFG->wwwroot . '/mod/bigbluebuttonbn/bbb_broker.php?action=recording_ready';
$bbbsession['joinURL'] = $CFG->wwwroot . '/mod/bigbluebuttonbn/bbb_view.php?action=join&id=' . $id . '&bigbluebuttonbn=' . $bbbsession['bigbluebuttonbn']->id;

// Output starts here
echo $OUTPUT->header();

/// find out current groups mode
$groupmode = groups_get_activity_groupmode($bbbsession['cm']);
if ($groupmode == NOGROUPS) {  //No groups mode
    $bbbsession['meetingid'] = $bbbsession['bigbluebuttonbn']->meetingid . '-' . $bbbsession['course']->id . '-' . $bbbsession['bigbluebuttonbn']->id;
    $bbbsession['meetingname'] = $bbbsession['bigbluebuttonbn']->name;

} else {                                        // Separate or visible groups mode
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    echo '<br><div class="alert alert-warning">' . get_string('view_groups_selection_warning', 'bigbluebuttonbn') . '</div>';
    echo $OUTPUT->box_end();

    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/bigbluebuttonbn/view.php?id=' . $bbbsession['cm']->id);
    if ($groupmode == SEPARATEGROUPS && sizeof($groups) > 0) {
        $groups = groups_get_activity_allowed_groups($bbbsession['cm']);
        $current_group = current($groups);
        $bbbsession['group'] = $current_group->id;
    } else {
        $groups = groups_get_all_groups($bbbsession['course']->id);
        $bbbsession['group'] = groups_get_activity_group($bbbsession['cm'], true);
    }

    $bbbsession['meetingid'] = $bbbsession['bigbluebuttonbn']->meetingid . '-' . $bbbsession['course']->id . '-' . $bbbsession['bigbluebuttonbn']->id . '[' . $bbbsession['group'] . ']';
    if ($bbbsession['group'] > 0) {
        $group_name = groups_get_group_name($bbbsession['group']);
    } else {
        $group_name = get_string('allparticipants');
    }
    $bbbsession['meetingname'] = $bbbsession['bigbluebuttonbn']->name . ' (' . $group_name . ')';
}

// Metadata (context)
$bbbsession['contextActivityName'] = $bbbsession['meetingname'];
$bbbsession['contextActivityDescription'] = bigbluebuttonbn_html2text($bbbsession['meetingdescription'], 64);
$bbbsession['contextActivityTags'] = "";
$bbbsession['contextActivityLitigation'] = "";

$bigbluebuttonbn_activity = 'open';
$now = time();
if (!empty($bigbluebuttonbn->openingtime) && $now < $bigbluebuttonbn->openingtime) {
    //ACTIVITY HAS NOT BEEN OPENED
    $bigbluebuttonbn_activity = 'not_started';

} else if (!empty($bigbluebuttonbn->closingtime) && $now > $bigbluebuttonbn->closingtime) {
    //ACTIVITY HAS BEEN CLOSED
    $bigbluebuttonbn_activity = 'ended';
    $bbbsession['presentation'] = bigbluebuttonbn_get_presentation_array($context, $bigbluebuttonbn->presentation);

} else {
    //ACTIVITY OPEN
    $bbbsession['presentation'] = bigbluebuttonbn_get_presentation_array($bbbsession['context'], $bigbluebuttonbn->presentation, $bigbluebuttonbn->id);
}

// Initialize session variable used across views
$SESSION->bigbluebuttonbn_bbbsession = $bbbsession;

bigbluebuttonbn_view($bbbsession, $bigbluebuttonbn_activity, $course);

//JavaScript variables
$waitformoderator_ping_interval = bigbluebuttonbn_get_cfg_waitformoderator_ping_interval();
$jsVars = array(
    'activity' => $bigbluebuttonbn_activity,
    'meetingid' => $bbbsession['meetingid'],
    'bigbluebuttonbnid' => $bbbsession['bigbluebuttonbn']->id,
    'ping_interval' => ($waitformoderator_ping_interval > 0 ? $waitformoderator_ping_interval * 1000 : 15000),
    'userlimit' => $bbbsession['userlimit'],
    'locales' => bigbluebuttonbn_get_locales_for_ui(),
    'opening' => ($bbbsession['openingtime']) ? get_string('mod_form_field_openingtime', 'bigbluebuttonbn') . ': ' . userdate($bbbsession['openingtime']) : '',
    'closing' => ($bbbsession['closingtime']) ? get_string('mod_form_field_closingtime', 'bigbluebuttonbn') . ': ' . userdate($bbbsession['closingtime']) : ''
);

$PAGE->requires->data_for_js('bigbluebuttonbn', $jsVars);

$jsmodule = array(
    'name'     => 'mod_bigbluebuttonbn',
    'fullpath' => '/mod/bigbluebuttonbn/module.js',
    'requires' => array('datasource-get', 'datasource-jsonschema', 'datasource-polling'),
);
$PAGE->requires->js_init_call('M.mod_bigbluebuttonbn.view_init', array(), false, $jsmodule);


// Finish the page
echo $OUTPUT->footer();

function bigbluebuttonbn_view($bbbsession, $activity, $course) {
    global $CFG, $DB, $OUTPUT;

    echo $OUTPUT->heading($bbbsession['meetingname'], 3);
    echo $OUTPUT->heading($bbbsession['meetingdescription'], 5);

    echo $OUTPUT->box_start('generalbox boxaligncenter', 'bigbluebuttonbn_view_message_box');
    echo '<br><span id="status_bar"></span><br>';
    echo '<span id="control_panel"></span>';
    echo $OUTPUT->box_end();

    echo $OUTPUT->box_start('generalbox boxaligncenter', 'bigbluebuttonbn_view_action_button_box');
    echo '<br><br><span id="join_button"></span>&nbsp;<span id="end_button"></span>' . "\n";
    echo $OUTPUT->box_end();
    // Show mobile client options if mobile is detected
    if ($bbbsession['ismobilesession'])
    {
        echo $OUTPUT->box_start('generalbox boxaligncenter', 'bigbluebuttonbn_view_action_button_box');
        include 'mobile_apps.php';
        echo $OUTPUT->box_end();
    }

    if ($activity == 'not_started') {
        // Do nothing
    } else {
        if ($activity == 'ended') {
            bigbluebuttonbn_view_ended($bbbsession);
        } else {
            bigbluebuttonbn_view_joining($bbbsession);
        }

        bigbluebuttonbn_view_recordings($bbbsession, $course);
    }
}

function bigbluebuttonbn_view_joining($bbbsession) {

    if ($bbbsession['tagging'] && ($bbbsession['administrator'] || $bbbsession['moderator'])) {
        echo '' .
            '<div id="panelContent" class="hidden">' . "\n" .
            '  <div class="yui3-widget-bd">' . "\n" .
            '    <form>' . "\n" .
            '      <fieldset>' . "\n" .
            '        <input type="hidden" name="join" id="meeting_join_url" value="">' . "\n" .
            '        <input type="hidden" name="message" id="meeting_message" value="">' . "\n" .
            '        <div>' . "\n" .
            '          <label for="name">' . get_string('view_recording_name', 'bigbluebuttonbn') . '</label><br/>' . "\n" .
            '          <input type="text" name="name" id="recording_name" placeholder="">' . "\n" .
            '        </div><br>' . "\n" .
            '        <div>' . "\n" .
            '          <label for="description">' . get_string('view_recording_description', 'bigbluebuttonbn') . '</label><br/>' . "\n" .
            '          <input type="text" name="description" id="recording_description" value="" placeholder="">' . "\n" .
            '        </div><br>' . "\n" .
            '        <div>' . "\n" .
            '          <label for="tags">' . get_string('view_recording_tags', 'bigbluebuttonbn') . '</label><br/>' . "\n" .
            '          <input type="text" name="tags" id="recording_tags" value="" placeholder="">' . "\n" .
            '        </div><br>' . "\n" .
            '        <div>' . "\n" .
            '          <label for="litigation">' . get_string('view_recording_litigation', 'bigbluebuttonbn') . '</label><br/>' . "\n" .
            '          <input type="text" name="litigation" id="recording_litigation" value="" placeholder="" required>' . "\n" .
            '        </div>' . "\n" .
            '      </fieldset>' . "\n" .
            '    </form>' . "\n" .
            '  </div>' . "\n" .
            '</div>';
    }
}

function bigbluebuttonbn_view_ended($bbbsession) {
    global $OUTPUT;

    if (!is_null($bbbsession['presentation']['url'])) {
        $attributes = array('title' => $bbbsession['presentation']['name']);
        $icon = new pix_icon($bbbsession['presentation']['icon'], $bbbsession['presentation']['mimetype_description']);

        echo '<h4>' . get_string('view_section_title_presentation', 'bigbluebuttonbn') . '</h4>' .
                '' . $OUTPUT->action_icon($bbbsession['presentation']['url'], $icon, null, array(), false) . '' .
                '' . $OUTPUT->action_link($bbbsession['presentation']['url'], $bbbsession['presentation']['name'], null, $attributes) . '<br><br>';
    }
}

function bigbluebuttonbn_view_recordings($bbbsession, $course) {
    global $CFG,$DB,$COURSE;

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
    $magistrado_search = $DB->get_records_sql($sql, array($COURSE->id));
    //Ainda não sei bem qual dos nomes pegar... mas é algum destes (fullname, firstname + lastname)
    if($magistrado_search){
      foreach ($magistrado_search as $key) {
        $nome_magistrado = $key->firstname.' '.$key->lastname;
        break;
      }
    }else{
      $nome_magistrado = "[[Nome do Magistrado]]";
    }
    if (isset($bbbsession['record']) && $bbbsession['record']) {
        $output = html_writer::tag('h4', get_string('view_section_title_recordings', 'bigbluebuttonbn'));

        $meetingID = '';
        $results = bigbluebuttonbn_getRecordedMeetings($bbbsession['course']->id, $bbbsession['bigbluebuttonbn']->id);

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
            $recordings = bigbluebuttonbn_getRecordingsArray($meetingID, $bbbsession['endpoint'], $bbbsession['shared_secret']);
        } else {
            $recordings = Array();
        }
        // Get recording links
        $recordings_imported = bigbluebuttonbn_getRecordingsImportedArray($bbbsession['course']->id, $bbbsession['bigbluebuttonbn']->id);
        // Merge the recordings
        $recordings = array_merge($recordings, $recordings_imported);

        foreach($recordings as $record){
          $sql = 'SELECT * FROM {bigbluebuttonbn_a_record} WHERE guid = ?';
          $aud_gravada = $DB->get_record_sql($sql, array($record['recordID']));
          if(!$aud_gravada){
            //Montando o campo descrição
            $sql = 'SELECT * FROM {bigbluebuttonbn} WHERE id = ?';
            $processo = $DB->get_record_sql($sql, array($bbbsession['bigbluebuttonbn']->id));

            $partes = $DB->get_records('bigbluebuttonbn_partes', array('id_bbb'=>$bbbsession['bigbluebuttonbn']->id,'oab'=>'0'));

            $date_ = new DateTime();
            $date_->setTimestamp($record['startTime']/1000);

            $ano = date('Y',$record['startTime']/1000);
            $hora = date_format($date_->sub(new DateInterval('PT2H')), 'H');
            $minuto = date_format($date_->sub(new DateInterval('PT2H')), 'i');
            setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
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

            $descricao = "<p>Aos ".$data.", às ".$hora."h".$minuto."min, na Sala de Audiências da ".$course->fullname.", presentes o Juiz ".$nome_magistrado." e as partes: ".$partes_text.".
            Aberta a audiência referente ao processo acima identificado, o Juiz esclareceu às partes que o depoimento será registrado através de gravação de áudio e vídeo digital que será acostado aos autos
            e ficará disponível no endereço eletrônico: <a href='".$record['playbacks']['presentation']['url']."'>".$record['playbacks']['presentation']['url']." .</a></p>
            <p>Foram tomados os depoimentos, ouvido o Ministério Público e a Defesa.</p>";
            $year = date("Y", $record['startTime']/1000);
            $aud = new stdClass();
            $aud->id_bbb=$bbbsession['bigbluebuttonbn']->id;
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


        $output .= '<br>
        <table class="generaltable">
          <thead>
            <tr>
              <th class="header c1" style="text-align:left;" scope="col">'.get_string('view_recording_name', 'bigbluebuttonbn').'</th>
              <th class="header c2" style="text-align:left;" scope="col">Termo de Audiência</th>
              <th class="header c3" style="text-align:left;" scope="col">Date</th>
              <th class="header c4" style="text-align:center;" scope="col">Duration</th>
              <th class="header c5 lastcol" style="text-align:left;" scope="col">Toolbar</th>
            </tr>
          </thead>
          <tbody>';
        foreach($recordings as $record){
          if($record['published']=='true'){
            $sql = 'SELECT * FROM {bigbluebuttonbn_a_record} WHERE guid = ?';
            $aud_gravada = $DB->get_record_sql($sql, array($record['recordID']));
            $aud_gravada->publishdate = $aud_gravada->publishdate/1000;
            $date_ = new DateTime();
            $date_->setTimestamp($aud_gravada->publishdate);
            if($aud_gravada){
              $output .= '<tr>
                <td class="cell c1" style=" text-align:left;">'.$aud_gravada->name.'</td>
                <td class="cell c2" style=" text-align:left;">'.$aud_gravada->description.'</td>
                <td class="cell c3" style=" text-align:left;">'.date_format($date_->sub(new DateInterval('PT4H')), 'd/m/Y H:i').'</td>
                <td class="cell c4" style=" text-align:left;">'.$aud_gravada->duration.'</td>
                <td class="cell c5 lastcol" style="text-align:left; width:10%">
                  <a href="'.$CFG->wwwroot.'/mod/bigbluebuttonbn/playback.php?id='.$_GET['id'].'&recordID='.$aud_gravada->guid.'&meetingID='.$aud_gravada->meetingid.'" data-links="0" class="action-icon" target="_blank"><img alt="Audiência" class="smallicon" title="Audiência" src="'.$CFG->wwwroot.'/pix/e/insert_edit_video.png"></a><br>
                  <a onclick=\'if(confirm("Você tem certeza que deseja ocultar esta audiência?")){M.mod_bigbluebuttonbn.broker_manageRecording("unpublish", "'.$aud_gravada->guid.'", "'.$aud_gravada->meetingid.'");}else{return false;}\' data-links="0" class="action-icon" href="#"><img alt="Hide" class="smallicon" title="Hide" src="'.$CFG->wwwroot.'/theme/image.php/clean/core/1513160402/t/hide"></a><br>
                  <a onclick=\'if(confirm("Você tem certeza que deseja excluir esta audiência?")){M.mod_bigbluebuttonbn.broker_manageRecording("delete", "'.$aud_gravada->guid.'", "'.$aud_gravada->meetingid.'");}else{return false;}\' data-links="0" class="action-icon" href="#"><img alt="Delete" class="smallicon" title="Delete" src="'.$CFG->wwwroot.'/theme/image.php/clean/core/1513160402/t/delete"></a><br>
                  <a href="'.$CFG->wwwroot.'/mod/bigbluebuttonbn/edit_record_data.php?id='.$aud_gravada->guid.'" data-links="0" class="action-icon"><img alt="Edit" class="smallicon" title="Edit" src="'.$CFG->wwwroot.'/theme/image.php/clean/core/1513160402/t/edit"></a></td>
              </tr>';
            }
          }
        }
        $output .= '  </tbody>
        </table>';

        echo $output;
    }
}

function bigbluebutton_is_device_for_mobile_client(){
    $detect = new Mobile_Detect;
    return $detect->isAndroidOS() || $detect->isiOS();
}

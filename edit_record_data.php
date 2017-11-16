<?php
/**
 * Edit Record data.
 *
 * @package   mod_bigbluebuttonbn
 * @author    Luiz Henrique Longhi Rossi
 * @copyright 2017 Cognitiva Brasil Tecnologias Educacionais
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once ($CFG->libdir.'/formslib.php');

//Setando o curso como o curso base para pedir login
$course = $DB->get_record('course', array('id' => '1'), '*', MUST_EXIST);
require_login($course, true);

/// Print the header
$PAGE->set_url('/mod/bigbluebuttonbn/rooms_form.php');
$PAGE->set_title(get_string('modulename', 'bigbluebuttonbn'));
$PAGE->set_cacheable(false);
$PAGE->set_pagelayout('incourse');

// $PAGE->navbar->add(get_string('modulename', 'bigbluebuttonbn'), "index.php?id=$course->id");

class simplehtml_form extends moodleform {

    function definition() {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('text', 'nome','nome'); // Add elements to your form
         $mform->setType('nome', PARAM_RAW);
        $mform->addElement('editor', 'descrição','descrição'); // Add elements to your form
        $mform->setType('descrição', PARAM_RAW);

        $mform->addElement('text', 'etiqueta','etiqueta'); // Add elements to your form
        $mform->setType('etiqueta', PARAM_RAW);        

        $this->add_action_buttons();

    }
}

$mform = new simplehtml_form();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {

  $record = new stdClass();
  $record->name = $fromform->room;
  $record->userid = $USER->id;
  $record->timecreated = time();
  $lastinsertid = $DB->insert_record('bigbluebuttonbn_rooms', $record, false);

  $log = new stdClass();
  $log->courseid = 1;
  $log->bigbluebuttonbnid = 1;
  $log->userid = $USER->id;
  $log->timecreated = time();
  $log->meetingid = '';
  $log->log = substr("Sala ".$lastinsertid." - ".$fromform->room." incluída",0,30);
  $log->meta = '';
  $log_insert = $DB->insert_record('bigbluebuttonbn_logs', $log, false);
}

echo $OUTPUT->header();

echo $OUTPUT->heading('Editar processo: ');

// //Apresenta todas as salas
// $table = 'bigbluebuttonbn_rooms';
// $select = "";
// $records = $DB->get_records_select($table, $select);

// echo "<ul>";
// foreach ($records as $row) {
//   echo "<li>".$row->name." <sup><a href='".$CFG->wwwroot."/mod/bigbluebuttonbn/delete_room.php?id=".$row->id."' onclick='if(!confirm(\"Deseja realmente deletar a sala?\")) return false;'>x</a></sup></li>";
//   //print_r($row);
// }
// echo "</ul>";

// echo "<br><br>";

$mform->display();

echo $OUTPUT->footer();

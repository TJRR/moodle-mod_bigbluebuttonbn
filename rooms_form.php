<?php
/**
 * Insert Physical rooms to bigbluebuttonbn.
 *
 * @package   mod_bigbluebuttonbn
 * @author    Alan Velasques Santos
 * @copyright 2010-2015 Blindside Networks Inc.
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
//$PAGE->set_heading('Webconference');
$PAGE->set_cacheable(false);
$PAGE->set_pagelayout('incourse');

//$navigation = build_navigation(array('name' => get_string('modulename', 'bigbluebuttonbn'), 'link' => '', 'type' => 'activity'));
//$PAGE->navbar->add(get_string('modulename', 'bigbluebuttonbn'), "index.php?id=$course->id");

class simplehtml_form extends moodleform {

    function definition() {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('text', 'room', get_string('mod_form_field_addroom', 'bigbluebuttonbn')); // Add elements to your form
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
  $log->log = "Sala ".$lastinsertid." - ".$fromform->room." incluída";
  $log->meta = '';
  $log_insert = $DB->insert_record('bigbluebuttonbn_logs', $log, false);
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('index_heading', 'bigbluebuttonbn'));

//Apresenta todas as salas
$table = 'bigbluebuttonbn_rooms';
$select = "";
$records = $DB->get_records_select($table, $select);

echo "<ul>";
foreach ($records as $row) {
  echo "<li>".$row->name." <sup><a href='".$CFG->wwwroot."/mod/bigbluebuttonbn/delete_room.php?id=".$row->id."' onclick='if(!confirm(\"Deseja realmente deletar a sala?\")) return false;'>x</a></sup></li>";
  //print_r($row);
}
echo "</ul>";

echo "<br><br>";

$mform->display();

echo $OUTPUT->footer();

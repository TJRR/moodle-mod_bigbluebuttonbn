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
$PAGE->set_url('/mod/bigbluebuttonbn/edit_record_data.php');
$PAGE->set_title(get_string('modulename', 'bigbluebuttonbn'));
$PAGE->set_cacheable(false);
$PAGE->set_pagelayout('incourse');

// $PAGE->navbar->add(get_string('modulename', 'bigbluebuttonbn'), "index.php?id=$course->id");

class simplehtml_form extends moodleform {

    function definition() {
        global $CFG, $DB;

        $sql = 'SELECT * FROM {bigbluebuttonbn_a_record} WHERE guid = ?';
        $records = $DB->get_record_sql($sql, array($_GET['id']));

        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('hidden', 'id','id'); // Add elements to your form
        $mform->setType('id', PARAM_RAW);
        $mform->setDefault('id',$records->id);
        $mform->addElement('hidden', 'id_course','id_course'); // Add elements to your form
        $mform->setType('id_course', PARAM_RAW);
        $mform->setDefault('id_course',$records->id_course);

        $mform->addElement('text', 'name','nome'); // Add elements to your form
        $mform->setType('name', PARAM_RAW);
        $mform->setDefault('name',$records->name);

        $mform->addElement('editor', 'description','descrição'); // Add elements to your form
        $mform->setType('description', PARAM_RAW);
        $mform->setDefault('description',array('text'=>$records->description));

        $mform->addElement('text', 'tag','etiqueta'); // Add elements to your form
        $mform->setType('tag', PARAM_RAW);
        $mform->setDefault('tag',$records->tags);

        $this->add_action_buttons();

    }
}

$mform = new simplehtml_form();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {

  $records = new stdClass();
  $records->id = $fromform->id;
  $records->name = $fromform->name;
  $records->description = $fromform->description['text'];
  $records->tags = $fromform->tag;
  $DB->update_record('bigbluebuttonbn_a_record', $records, false);
  redirect($CFG->wwwroot.'/mod/bigbluebuttonbn/view.php?id='.$fromform->id_course);
}

echo $OUTPUT->header();

echo $OUTPUT->heading('Editar audiência: ');

$mform->display();

echo $OUTPUT->footer();

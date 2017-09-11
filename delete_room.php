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
$PAGE->set_url('/mod/bigbluebuttonbn/delete_room.php');
$PAGE->set_title(get_string('modulename', 'bigbluebuttonbn'));
//$PAGE->set_heading('Webconference');
$PAGE->set_cacheable(false);
$PAGE->set_pagelayout('incourse');


//Apresenta todas as salas
$table = 'bigbluebuttonbn_rooms';
$records = $DB->delete_records($table, ['id'=>$_GET['id']]);

echo '<meta http-equiv="refresh" content="0;url='.$CFG->wwwroot.'/mod/bigbluebuttonbn/rooms_form.php" />';

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

$sql = "SELECT cm.id, bbb.guid, bbb.meetingid FROM {course_modules} cm, {modules} m, {bigbluebuttonbn_a_record} bbb where m.name = 'bigbluebuttonbn' and m.id = cm.module and cm.instance = bbb.id_bbb and bbb.guid = '".$_GET['meetingId']."'";

$result = $DB->get_record_sql($sql);

echo "<meta http-equiv='refresh' content='0; url=\"".$CFG->wwwroot."/mod/bigbluebuttonbn/playback.php?id=".$result->id."&recordID=".$result->guid."&meetingID=".$result->meetingid."\"'>";

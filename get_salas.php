<?php
/**
 * Local Proxy view for Big Blue Button local security, need additional configuration to
 * work properly.
 *
 * @package   mod_bigbluebuttonbn
 * @author    Alan Velasques Santos
 * @copyright 2017 Cognitiva Brasil Tecnologias Educacionais
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

global $DB;

$sql = 'SELECT * FROM {bigbluebuttonbn_r_reserved} WHERE openingtime = ? and id_physical_room = ?';
$sala = $DB->get_record_sql($sql, array($_GET['date'],$_GET['id']));
if($sala){
  echo 1;
}else{
  echo 0;
}

?>

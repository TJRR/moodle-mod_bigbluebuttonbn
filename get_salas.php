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

$sql = 'SELECT * FROM {bigbluebuttonbn_r_reserved} WHERE ((openingtime BETWEEN ? and ?) or (closingtime BETWEEN ? and ?) or (? BETWEEN openingtime and closingtime) or (? BETWEEN openingtime and closingtime)) and id_physical_room = ?';

if(isset($_GET['id_bbb'])){
  $sql .= ' and id_bbb <> ?';
  $sala = $DB->get_records_sql($sql, array($_GET['date_ini'],$_GET['date_fim'],$_GET['date_ini'],$_GET['date_fim'],$_GET['date_ini'],$_GET['date_fim'],$_GET['id'],$_GET['id_bbb']));
}else{
  $sala = $DB->get_records_sql($sql, array($_GET['date_ini'],$_GET['date_fim'],$_GET['date_ini'],$_GET['date_fim'],$_GET['date_ini'],$_GET['date_fim'],$_GET['id']));
}
if($sala){
  echo 1;
}else{
  echo 0;
}

?>

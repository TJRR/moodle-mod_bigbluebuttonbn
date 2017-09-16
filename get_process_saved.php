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

$sql = 'SELECT * FROM {bigbluebuttonbn} WHERE name = ?';
$processo = $_GET['nrprocesso'];
if(!(strpos($processo, '-')!==false)){
  $processo = substr($processo,0,7).'-'.substr($processo,7,2).'.'.substr($processo,9,4).'.'.substr($processo,13,1).'.'.substr($processo,14,2).'.'.substr($processo,16);
}
@$processo = $DB->get_record_sql($sql, array($processo));
if($processo){
  echo 1;
}else{
  echo 0;
}

?>

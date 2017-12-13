<?php

/**
 * Upgrade logic.
 *
 * @package   mod_bigbluebuttonbn
 * @author    Fred Dixon  (ffdixon [at] blindsidenetworks [dt] com)
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 * @copyright 2010-2015 Blindside Networks Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

function xmldb_bigbluebuttonbn_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;
    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    $result = true;

    if ($result && $oldversion < 2012040200) {
        // Define field intro to be droped from bigbluebuttonbn
        $table = new xmldb_table('bigbluebuttonbn');
        $field = new xmldb_field('intro', XMLDB_TYPE_TEXT, 'medium', null, null, null, null,'name');

        // Drop field intro
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field, $continue=true, $feedback=true);
        }

        // Define field introformat to be droped from bigbluebuttonbn
        $table = new xmldb_table('bigbluebuttonbn');
        $field = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'intro');

        // Drop field introformat
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field, $continue=true, $feedback=true);
        }

        // Once we reach this point, we can store the new version and consider the module
        // upgraded to the version 2012040200 so the next time this block is skipped
        upgrade_mod_savepoint(true, 2012040200, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2012062705) {

        // Define table bigbluebuttonbn_log to be created
        $table = new xmldb_table('bigbluebuttonbn_log');

        // Adding fields to table bigbluebuttonbn_log
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('meetingid', XMLDB_TYPE_CHAR, '256', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('bigbluebuttonbnid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('record', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('event', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table bigbluebuttonbn_log
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for bigbluebuttonbn_log
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // bigbluebuttonbn savepoint reached
        upgrade_mod_savepoint(true, 2012062705, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2012100100) {

        $table = new xmldb_table('bigbluebuttonbn');
        $field = new xmldb_field('welcome');
        $field->set_attributes(XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, null, null, 'type');

        $dbman->change_field_type($table, $field, $continue=true, $feedback=true);

        upgrade_mod_savepoint(true, 2012100100, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2014050100) {

        $table = new xmldb_table('bigbluebuttonbn');
        $field = new xmldb_field('allmoderators');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }

        upgrade_mod_savepoint(true, 2014050100, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2014070420) {

        $table = new xmldb_table('bigbluebuttonbn');
        $field = new xmldb_field('participants', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }

        upgrade_mod_savepoint(true, 2014070420, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2014101004) {

        $table = new xmldb_table('bigbluebuttonbn');
        $field = new xmldb_field('participants');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null);

        $dbman->change_field_type($table, $field, $continue=true, $feedback=true);

        upgrade_mod_savepoint(true, 2014101004, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2015063000) {
        // Update the bigbluebuttonbn table
        $table = new xmldb_table('bigbluebuttonbn');
        //// Drop field timeduration
        $field = new xmldb_field('timeduration');
        if( $dbman->field_exists($table, $field) ) {
            $dbman->drop_field($table, $field, $continue=true, $feedback=true);
        }
        //// Drop field allmoderators
        $field = new xmldb_field('allmoderators');
        if( $dbman->field_exists($table, $field) ) {
            $dbman->drop_field($table, $field, $continue=true, $feedback=true);
        }
        //// Add field intro
        $field = new xmldb_field('intro');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'name');
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }
        //// Add field introformat
        $field = new xmldb_field('introformat');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 1, 'intro');
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }
        //// Add field tagging
        $field = new xmldb_field('tagging');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'record');
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }
        //// Add field presentation
        $field = new xmldb_field('presentation');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'timemodified');
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }
        //// Add field type
        $field = new xmldb_field('type');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'course');
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }
        //// Rename field timeavailable
        $field = new xmldb_field('timeavailable');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        if( $dbman->field_exists($table, $field) ) {
            $dbman->rename_field($table, $field, 'openingtime', $continue=true, $feedback=true);
        }
        //// Rename field timedue
        $field = new xmldb_field('timedue');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        if( $dbman->field_exists($table, $field) ) {
            $dbman->rename_field($table, $field, 'closingtime', $continue=true, $feedback=true);
        }
        //// Add field timecreated
        $field = new xmldb_field('timecreated');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'closingtime');
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }
        //// Add field userlimit
        $field = new xmldb_field('userlimit');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }

        // Update the bigbluebuttonbn_log table
        $table = new xmldb_table('bigbluebuttonbn_log');
        //// Add field userid
        $field = new xmldb_field('userid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'bigbluebuttonbnid');
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }
        //// Add field meta
        $field = new xmldb_field('meta');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'event');
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }
        //// Drop field recording
        $field = new xmldb_field('record');
        if( $dbman->field_exists($table, $field) ) {
            //// Migrate data in field recording to new format in meta
            $meta = new \stdClass();

            // Record => true.
            $meta->record = true;
            $DB->set_field('bigbluebuttonbn_log', 'meta', json_encode($meta), array('event' => 'Create', 'record' => 1));

            // Record => false.
            $meta->record = false;
            $DB->set_field('bigbluebuttonbn_log', 'meta', json_encode($meta), array('event' => 'Create', 'record' => 0));

            // Drop field recording
            $dbman->drop_field($table, $field, $continue=true, $feedback=true);
        }

        upgrade_mod_savepoint(true, 2015063000, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2015080605) {
        // Update the bigbluebuttonbn table
        $table = new xmldb_table('bigbluebuttonbn');
        //// Drop field description
        $field = new xmldb_field('description');
        if( $dbman->field_exists($table, $field) ) {
            $dbman->drop_field($table, $field, $continue=true, $feedback=true);
        }
        //// Change welcome, allow null
        $field = new xmldb_field('welcome');
        $field->set_attributes(XMLDB_TYPE_TEXT, null, null, null, null, null, null, null, 'type');
        if( $dbman->field_exists($table, $field) ) {
            $dbman->change_field_notnull($table, $field, $continue=true, $feedback=true);
        }

        // Update the bigbluebuttonbn_log table
        $table = new xmldb_table('bigbluebuttonbn_log');
        //// Change welcome, allow null
        $field = new xmldb_field('userid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null, 'bigbluebuttonbnid');
        if( $dbman->field_exists($table, $field) ) {
            $dbman->change_field_notnull($table, $field, $continue=true, $feedback=true);
        }

        upgrade_mod_savepoint(true, 2015080605, 'bigbluebuttonbn');
    }

    if ( $result && $oldversion < 2016011305 ) {
        // Update the bigbluebuttonbn table
        $table = new xmldb_table('bigbluebuttonbn');

        // Define field type to be droped from bigbluebuttonbn
        $field = new xmldb_field('type');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'course');
        if ( $dbman->field_exists($table, $field) ) {
            $dbman->drop_field($table, $field, $continue=true, $feedback=true);
        }

        // Define field 'event' to be renamed
        $field = new xmldb_field('event');
        $field->set_attributes(XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null);

        // Make sure bigbluebuttonbn_logs table exists
        $table = new xmldb_table('bigbluebuttonbn_log');
        // Conditionally rename the table
        if ($dbman->table_exists($table)) {
            // Update the bigbluebuttonbn_log table
            if ( $dbman->field_exists($table, $field) ) {
                $dbman->rename_field($table, $field, 'log', $continue=true, $feedback=true);
            }
            // Rename bigbluebuttonbn_log table to bigbluebuttonbn_logs
            $dbman->rename_table($table, 'bigbluebuttonbn_logs', $continue=true, $feedback=true);
        } else {
            // It was already renamed, select it only
            $table = new xmldb_table('bigbluebuttonbn_logs');
            // Update the bigbluebuttonbn_logs table
            if ( $dbman->field_exists($table, $field) ) {
                $dbman->rename_field($table, $field, 'log', $continue=true, $feedback=true);
            }
        }

        upgrade_mod_savepoint(true, 2016011305, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2016051910) {
        // Update the bigbluebuttonbn table
        $table = new xmldb_table('bigbluebuttonbn');
        //// Drop field newwindow
        $field = new xmldb_field('newwindow');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field, $continue=true, $feedback=true);
        }

        upgrade_mod_savepoint(true, 2016051910, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2017072100) {

      // Define table bigbluebuttonbn_proc to be created
      $table = new xmldb_table('bigbluebuttonbn_proc');

      // Adding fields to table bigbluebuttonbn_proc
      $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
      $table->add_field('type', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
      $table->add_field('userid',XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'bigbluebuttonbnid');
      $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

      // Adding keys to table bigbluebuttonbn_proc
      $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

      // Conditionally launch create table for bigbluebuttonbn_proc
      if (!$dbman->table_exists($table)) {
          $dbman->create_table($table);
      }

      // Define table bigbluebuttonbn_rooms to be created
      $table = new xmldb_table('bigbluebuttonbn_rooms');

      // Adding fields to table bigbluebuttonbn_rooms
      $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
      $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
      $table->add_field('userid',XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'bigbluebuttonbnid');
      $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

      // Adding keys to table bigbluebuttonbn_rooms
      $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

      // Conditionally launch create table for bigbluebuttonbn_rooms
      if (!$dbman->table_exists($table)) {
          $dbman->create_table($table);
      }

      upgrade_mod_savepoint(true, 2017072100, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2017081301) {

      // Define table bigbluebuttonbn_proc to be created
      $table = new xmldb_table('bigbluebuttonbn_r_reserved');

      // Adding fields to table bigbluebuttonbn_proc
      $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
      $table->add_field('id_physical_room', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null);
      $table->add_field('id_bbb', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null);
      $table->add_field('openingtime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null);
      $table->add_field('closingtime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null);
      $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

      // Adding keys to table bigbluebuttonbn_proc
      $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

      // Conditionally launch create table for bigbluebuttonbn_r_reserved
      if (!$dbman->table_exists($table)) {
          $dbman->create_table($table);
      }

      upgrade_mod_savepoint(true, 2017081301, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2017081400) {
        // Update the bigbluebuttonbn table
        $table = new xmldb_table('bigbluebuttonbn');

        //// Add field meta
        $field = new xmldb_field('tipoaudiencia');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }

        upgrade_mod_savepoint(true, 2017081400, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2017081600) {
        // Update the bigbluebuttonbn table
        $table = new xmldb_table('bigbluebuttonbn');

        //// Add field meta
        $field = new xmldb_field('nrprocesso');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }

        upgrade_mod_savepoint(true, 2017081600, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2017081700) {
        // Update the bigbluebuttonbn table
        $table = new xmldb_table('bigbluebuttonbn');

        //// Add field segredojustica
        $field = new xmldb_field('segredojustica');
        $field->set_attributes(XMLDB_TYPE_CHAR, '45', null, null, null, null);
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }

        //// Add field assuntoprincipal
        $field = new xmldb_field('assuntoprincipal');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }

        // Define table bigbluebuttonbn_proc to be created
        $table = new xmldb_table('bigbluebuttonbn_partes');

        // Adding fields to table bigbluebuttonbn_proc
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('id_parte', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null);
        $table->add_field('id_bbb', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('oab', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // Adding keys to table bigbluebuttonbn_proc
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for bigbluebuttonbn_r_reserved
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_mod_savepoint(true, 2017081700, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2017102700) {
        // Update the bigbluebuttonbn table
        $table = new xmldb_table('bigbluebuttonbn');

        //// Add field meta
        $field = new xmldb_field('process_type');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, 0);
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }

        upgrade_mod_savepoint(true, 2017102700, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2017111400) {

      // Define table bigbluebuttonbn_proc to be created
      $table = new xmldb_table('bigbluebuttonbn_a_record');

      // Adding fields to table bigbluebuttonbn_proc
      $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
      $table->add_field('id_bbb', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null);
      $table->add_field('placeidtribunal', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
      $table->add_field('hearingidtribunal', XMLDB_TYPE_TEXT, null, null, null, null, null,null);
      $table->add_field('cast', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
      $table->add_field('nrprocesso', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
      $table->add_field('expectedate', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null);
      $table->add_field('publishdate', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null);
      $table->add_field('basefilepath', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
      $table->add_field('files', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
      $table->add_field('size', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null);
      $table->add_field('hash', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
      $table->add_field('duration', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null);
      $table->add_field('meetingid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
      $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

      // Adding keys to table bigbluebuttonbn_proc
      $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

      // Conditionally launch create table for bigbluebuttonbn_r_reserved
      if (!$dbman->table_exists($table)) {
          $dbman->create_table($table);
      }

      upgrade_mod_savepoint(true, 2017111400, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2017111600) {
        // Update the bigbluebuttonbn table
        $table = new xmldb_table('bigbluebuttonbn_a_record');

        //// Add field meta
        $field = new xmldb_field('link');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }

        upgrade_mod_savepoint(true, 2017111600, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2017112100) {

        // Update the bigbluebuttonbn_a_record table
        $table = new xmldb_table('bigbluebuttonbn_a_record');

        // Define field 'cast' to be renamed
        $field = new xmldb_field('cast');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Conditionally rename the table
        if ($dbman->table_exists($table)) {
            // Update the bigbluebuttonbn_a_record table
            if ( $dbman->field_exists($table, $field) ) {
                $dbman->rename_field($table, $field, 'recordid', $continue=true, $feedback=true);
            }
        }

        upgrade_mod_savepoint(true, 2017112100, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2017112300) {

        // Update the bigbluebuttonbn_a_record table
        $table = new xmldb_table('bigbluebuttonbn_a_record');

        // Define field 'cast' to be renamed
        $field = new xmldb_field('recordid');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Conditionally rename the table
        if ($dbman->table_exists($table)) {
            // Update the bigbluebuttonbn_a_record table
            if ( $dbman->field_exists($table, $field) ) {
                $dbman->rename_field($table, $field, 'guid', $continue=true, $feedback=true);
            }
        }

        upgrade_mod_savepoint(true, 2017112300, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2017121300) {
        // Update the bigbluebuttonbn table
        $table = new xmldb_table('bigbluebuttonbn_a_record');

        //// Add field name
        $field = new xmldb_field('name');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }

        //// Add field description
        $field = new xmldb_field('description');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }

        //// Add field tags
        $field = new xmldb_field('tags');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }

        upgrade_mod_savepoint(true, 2017121300, 'bigbluebuttonbn');
    }

    if ($result && $oldversion < 2017121301) {
        // Update the bigbluebuttonbn table
        $table = new xmldb_table('bigbluebuttonbn_a_record');

        //// Add field name
        $field = new xmldb_field('id_course');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        if( !$dbman->field_exists($table, $field) ) {
            $dbman->add_field($table, $field, $continue=true, $feedback=true);
        }

        upgrade_mod_savepoint(true, 2017121301, 'bigbluebuttonbn');
    }
    return $result;
}

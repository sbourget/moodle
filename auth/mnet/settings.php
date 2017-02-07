<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Admin settings and defaults.
 *
 * @package    auth_mnet
 * @copyright  2017 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/lib/outputlib.php');

    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_mnet/pluginname', '',
            new lang_string('auth_mnetdescription', 'auth_mnet')));

    // Generate warning if MNET is disabled.
    if (empty($CFG->mnet_dispatcher_mode) || $CFG->mnet_dispatcher_mode !== 'strict') {
        $settings->add(new admin_setting_heading('auth_mnet/disabled', '',
                new lang_string('mnetdisabled','mnet')));
    }

    // RPC Timeout.
    $settings->add(new admin_setting_configtext('auth_mnet/rpc_negotiation_timeout',
            get_string('rpc_negotiation_timeout', 'auth_mnet'),
            get_string('auth_mnet_rpc_negotiation_timeout', 'auth_mnet'), '30', PARAM_INT));

    // Generate full list of ID and service providers.
    $query = "
       SELECT
           h.id,
           h.name as hostname,
           h.wwwroot,
           h2idp.publish as idppublish,
           h2idp.subscribe as idpsubscribe,
           idp.name as idpname,
           h2sp.publish as sppublish,
           h2sp.subscribe as spsubscribe,
           sp.name as spname
       FROM
           {mnet_host} h
       LEFT JOIN
           {mnet_host2service} h2idp
       ON
          (h.id = h2idp.hostid AND
          (h2idp.publish = 1 OR
           h2idp.subscribe = 1))
       INNER JOIN
           {mnet_service} idp
       ON
          (h2idp.serviceid = idp.id AND
           idp.name = 'sso_idp')
       LEFT JOIN
           {mnet_host2service} h2sp
       ON
          (h.id = h2sp.hostid AND
          (h2sp.publish = 1 OR
           h2sp.subscribe = 1))
       INNER JOIN
           {mnet_service} sp
       ON
          (h2sp.serviceid = sp.id AND
           sp.name = 'sso_sp')
       WHERE
          ((h2idp.publish = 1 AND h2sp.subscribe = 1) OR
          (h2sp.publish = 1 AND h2idp.subscribe = 1)) AND
           h.id != ?
       ORDER BY
           h.name ASC";

    $id_providers = array();
    $service_providers = array();
    if ($resultset = $DB->get_records_sql($query, array($CFG->mnet_localhost_id))) {
        foreach($resultset as $hostservice) {
            if(!empty($hostservice->idppublish) && !empty($hostservice->spsubscribe)) {
                $service_providers[]= array('id' => $hostservice->id,
                    'name' => $hostservice->hostname,
                    'wwwroot' => $hostservice->wwwroot);
            }
            if(!empty($hostservice->idpsubscribe) && !empty($hostservice->sppublish)) {
                $id_providers[]= array('id' => $hostservice->id,
                    'name' => $hostservice->hostname,
                    'wwwroot' => $hostservice->wwwroot);
            }
        }
    }

    // ID Providers.
    $table = html_writer::start_tag('table', array('class' => 'generaltable'));

    $count = 0;
    foreach($id_providers as $host) {
        $table .= html_writer::start_tag('tr');
        $table .= html_writer::start_tag('td');
        $table .= $host['name'];
        $table .= html_writer::end_tag('td');
        $table .= html_writer::start_tag('td');
        $table .= $host['wwwroot'];
        $table .= html_writer::end_tag('td');
        $table .= html_writer::end_tag('tr');
        $count++;
    }
        $table .= html_writer::end_tag('table');

    if ($count > 0) {
        $settings->add(new admin_setting_heading('auth_mnet/idproviders', '',
                new lang_string('auth_mnet_roamin', 'auth_mnet') . $table));
    }

    // Service Providers.
    unset($table);
    $table = html_writer::start_tag('table', array('class' => 'generaltable'));
    $count = 0;
    foreach($service_providers as $host) {
        $table .= html_writer::start_tag('tr');
        $table .= html_writer::start_tag('td');
        $table .= $host['name'];
        $table .= html_writer::end_tag('td');
        $table .= html_writer::start_tag('td');
        $table .= $host['wwwroot'];
        $table .= html_writer::end_tag('td');
        $table .= html_writer::end_tag('tr');
        $count++;
    }
        $table .= html_writer::end_tag('table');
    if ($count > 0) {
        $settings->add(new admin_setting_heading('auth_mnet/serviceproviders', '',
                new lang_string('auth_mnet_roamout', 'auth_mnet') . $table));
    }
}

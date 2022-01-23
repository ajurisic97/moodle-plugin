<?php

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('reportsestireport', get_string('pluginname', 
        'report_sestireport'), "$CFG->wwwroot/report/sestireport/index.php",'report/sestireport:view'));

// no report settings
$settings = null;

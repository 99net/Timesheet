<?php

define('TIMESHEET_VERSION', '1.0.0');

if (!defined("PLUGIN_TIMESHEET_DIR")) {
    define("PLUGIN_TIMESHEET_DIR", GLPI_ROOT . "/plugins/timesheet");
}

function plugin_init_timesheet()
{
    global $PLUGIN_HOOKS, $LANG;
    $PLUGIN_HOOKS['csrf_compliant']['timesheet'] = true;

    $plugin = new Plugin();
    if ($plugin->isActivated('timesheet')) {

        $PLUGIN_HOOKS['menu_toadd']['timesheet'] = [
            'helpdesk' => 'PluginTimesheetHelpdesk'
        ];

        Plugin::registerClass('PluginTimesheetProfile', [
            'addtabon' => ['Profile']
        ]);

        if (Session::haveRight('plugin_timesheet_timesheet', READ | CREATE | DELETE | PURGE)) {
            Plugin::registerClass('PluginTimesheetTimesheet', [
                'addtabon' => ['Project']
            ]);
        }

        $PLUGIN_HOOKS['item_update']['timesheet'] = [
            'TicketTask' => 'plugin_timesheet_tickettask_update'
        ];

        $PLUGIN_HOOKS['item_add']['timesheet'] = [
            'TicketTask' => 'plugin_timesheet_tickettask_update'
        ];

        $PLUGIN_HOOKS['use_massive_action']['timesheet'] = 1;
    }
}

function plugin_version_timesheet()
{
    global $DB, $LANG;

    return array(
        'name' => __('Timesheet for GLPI'),
        'version' => TIMESHEET_VERSION,
        'author' => '<a href="https://99net.pl">99NET</a>',
        'license' => 'GPLv2+',
        'homepage' => 'https://99net.pl',
        'minGlpiVersion' => '9.4.5'
    );
}

function plugin_timesheet_check_prerequisites()
{
    if (GLPI_VERSION >= '9.4.5')
        return true;
    else
        echo "GLPI version not compatible need 9.4.5";
}


function plugin_timesheet_check_config($verbose = false)
{
    if ($verbose)
        echo 'Installed / not configured';

    return true;
}

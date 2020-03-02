<?php

include("../../../inc/includes.php");

$plugin = new Plugin();
if (!$plugin->isInstalled('timesheet') || !$plugin->isActivated('timesheet')) {
    Html::displayNotFoundError();
}

Session::checkRight("plugin_timesheet_timesheet", READ | DELETE);

Html::header(__('Timesheets'), $_SERVER['PHP_SELF'], 'helpdesk', 'plugintimesheethelpdesk', 'timesheet');

Search::show('PluginTimesheetTimesheet');
Html::footer();

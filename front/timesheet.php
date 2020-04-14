<?php

include("../../../inc/includes.php");

$plugin = new Plugin();
if (!$plugin->isInstalled('timesheet') || !$plugin->isActivated('timesheet')) {
    Html::displayNotFoundError();
}

Session::checkRight("plugin_timesheet_timesheet", READ | DELETE);

Html::header(__('Timesheets'), $_SERVER['PHP_SELF'], 'helpdesk', 'plugintimesheethelpdesk', 'timesheet');

$itemtype = 'PluginTimesheetTimesheet';

$params = Search::manageParams($itemtype, $_GET);
echo "<div class='search_page'>";
Search::showGenericSearch($itemtype, $params);

if (!isset($params['itemtype'])) {
    $params['sort'] = 30;
    $params['order'] = 'DESC';
}

if ($params['as_map'] == 1) {
    Search::showMap($itemtype, $params);
} else {
    Search::showList($itemtype, $params);
}
echo "</div>";

Html::footer();

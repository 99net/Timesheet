<?php

include ('../../../inc/includes.php');

echo '<script type="text/javascript" src="/lib/tiny_mce/lib/tinymce.min.js?v=9.4.5"></script>';

Html::header(__('Timesheets'), $_SERVER['PHP_SELF'], 'helpdesk', 'plugintimesheethelpdesk', 'timesheet');

Session::checkRight("plugin_timesheet_timesheet", READ);
if(isset($_GET['id']) && $_GET['id'] != -1) {
    Session::checkRight('plugin_timesheet_timesheet', CREATE);
}

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
    $_GET["withtemplate"] = "";
}



//Session::checkRight('plugin_timesheet_timesheet', CREATE);

$ts = new PluginTimesheetTimesheet();

if (isset($_POST["add"])) {
    $ts->check(-1, CREATE, $_POST);
    if ($newID = $ts->add($_POST)) {

        $ts->getFromDB($newID);
        $content = html_entity_decode(
            $ts->getField('content'),
            ENT_NOQUOTES | ENT_HTML5
        );

        $DB->update(
            'glpi_plugin_timesheet_timesheets',
            ['content' => $content],
            ['id' => $newID]
        );


        $ts->redirectToList();
    }
    Html::back();

} else if (isset($_POST["delete"])) {
    $ts->check($_POST["id"], DELETE);
    $ts->delete($_POST);
    $ts->redirectToList();

} else if (isset($_POST["restore"])) {
    $ts->check($_POST["id"], DELETE);

    $ts->restore($_POST);
    $ts->redirectToList();

} else if (isset($_POST["purge"])) {
    $ts->check($_POST["id"], PURGE);

    $ts->delete($_POST, 1);
    $ts->redirectToList();

} else if (isset($_POST["update"])) {
    $ts->check($_POST["id"], UPDATE);

    $ts->update($_POST);
    Html::back();

} else {
    Html::header(PluginTimesheetTimesheet::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "helpdesk", "plugintimesheethelpdesk");
    $ts->display(['id' => $_GET["id"],
        'withtemplate' => $_GET["withtemplate"]]);
    Html::footer();
}
/*

if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
   Html::header("Timesheet", $_SERVER['PHP_SELF'], "helpdesk", "plugintimesheethelpdesk", "");
} else {
   Html::helpHeader("Timesheet", $_SERVER['PHP_SELF']);
}

$id = null;

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    Session::checkRight('plugin_timesheet_timesheet', EDIT);
}

Session::checkRight('plugin_timesheet_timesheet', CREATE);

$timesheet = new PluginTimesheetTimesheet();
$timesheet->display($_GET);


*/

Html::footer();

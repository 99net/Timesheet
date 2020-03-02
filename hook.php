<?php

function plugin_timesheet_tickettask_update(TicketTask $item)
{
    global $DB;

    // if (in_array('state', $item->updates)) { // tylko jeśli byłą zmiana

    if ($item->getField('state') == 2) {

        $Ticket = new Ticket();
        $Ticket->getFromDB($item->getField('tickets_id'));

        $stmt = $DB->prepare("INSERT INTO glpi_plugin_timesheet_timesheets (tickettasks_id, entities_id, users_id_tech, actiontime, date_creation, date_mod, task_date_creation, content)
						VALUES (?,?,?,?,now(),now(),?,?) on duplicate key update `tickettasks_id`=values(`tickettasks_id`), entities_id=values(entities_id), users_id_tech=values(users_id_tech), actiontime=values(actiontime), date_mod=now(), task_date_creation=values(task_date_creation), content=values(content)");
        $stmt->bind_param('iiiiss',
            $item->getField('id'),
            $Ticket->getField('entities_id'),
            $item->getField('users_id_tech'),
            $item->getField('actiontime'),
            $item->getField('date_creation'),
            //strip_tags(
            html_entity_decode(
                $item->getField('content'),
                ENT_NOQUOTES | ENT_HTML5
            )
        //)
        );

        $stmt->execute();
    }
    //}
}

/*
 *
function plugin_timesheet_MassiveActions($type)
{
    return [];
    $actions = [];
    $myclass = 'PluginTimesheetTimesheet';

    switch ($type) {
        case 'PluginTimesheetTimesheet' :
            if (Session::haveRight('plugin_timesheet_timesheet', CREATE)) {
                $actions[$myclass . MassiveAction::CLASS_ACTION_SEPARATOR . 'attach'] = __("Attach to Project", 'timesheet');
                $actions[$myclass . MassiveAction::CLASS_ACTION_SEPARATOR . 'detach'] = __("Detach from Project", 'timesheet');
            }
            if (Session::haveRight('plugin_timesheet_timesheet', DELETE | PURGE)) {
                $actions[$myclass . MassiveAction::CLASS_ACTION_SEPARATOR . 'destroy'] = __("Delete");
            }
            break;
    }
    return $actions;
}
*/


function plugin_timesheet_addParamFordynamicReport($itemtype)
{
    if ($itemtype == 'PluginTimesheetTimesheet') {
        return ['no_formatted_actiontime' => 1];
    }
    return false;
}

function plugin_timesheet_install()
{
    global $DB, $LANG;

    ProfileRight::addProfileRights(['plugin_timesheet_timesheet']);

    if (!$DB->tableExists('glpi_plugin_timesheet_timesheets')) {
        $query = "CREATE TABLE `glpi_plugin_timesheet_timesheets` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `tickettasks_id` int(11) NULL,
                  `entities_id` int(11) NULL,
                  `users_id_tech` int(11) NOT NULL,
                  `actiontime` int(11) NOT NULL,
                  `task_date_creation` DATETIME NOT NULL,
                  `projects_id` int(11) NULL,
                  `content` TEXT,
                  `date_mod` DATETIME NOT NULL,
                  `date_creation` DATETIME NOT NULL,
                  PRIMARY KEY  (`id`),
                  UNIQUE INDEX `glpi_plugin_timesheet_timesheets_ticketasks_id_unique` (`tickettasks_id`)
               ) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $DB->queryOrDie($query, $DB->error());
    }

    $i = 0;
    $self = new PluginTimesheetTimesheet();
    $tabs = $self->rawSearchOptions();
    foreach ($tabs as $tab) {
        if (isset($tab['id'])) {
            $i++;
            $DB->insert(
                'glpi_displaypreferences',
                [
                    'itemtype' => 'PluginTimesheetTimesheet',
                    'num' => $tab['id'],
                    'rank' => $i,
                    'users_id' => 0
                ]
            );
        }
    }

    return true;
}

function plugin_timesheet_uninstall()
{
    global $DB, $LANG;

    ProfileRight::deleteProfileRights(['plugin_timesheet_timesheet']);

    $DB->delete('glpi_displaypreferences', [
        'itemtype' => 'PluginTimesheetTimesheet'
    ]);

    if ($DB->tableExists("glpi_plugin_timesheet_timesheets")) {
        $query = "DROP TABLE `glpi_plugin_timesheet_timesheets`";
        $DB->queryOrDie($query, $DB->error());
    }
}

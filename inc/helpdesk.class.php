<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginTimesheetHelpdesk extends CommonDBTM
{
    public static $rightname = 'plugin_timesheet_helpdesk';

    // Should return the localized name of the type
    static function getTypeName($nb = 0)
    {
        return _n('Timesheet', 'Timesheets', $nb);
    }

    static function getMenuContent()
    {
        $menu = parent::getMenuContent();
        $menu['title'] = 'Timesheets';
        if (Session::haveRight('plugin_timesheet_timesheet', CREATE)) {
            $menu['links']['add'] = '/plugins/timesheet/front/timesheet.form.php?id=-1';
        }
        $menu['links'][__('Add Project', 'timesheet')] = '/front/project.form.php?id=-1&withtemplate=2';
        $menu['page'] = '/plugins/timesheet/front/timesheet.php';
        return $menu;
    }
}

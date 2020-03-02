<?php

class PluginTimesheetTimesheet extends CommonDBTM
{

    public static $rightname = 'plugin_timesheet_timesheet';

    static public function getTypeName($nb = 0)
    {
        return _n('Timesheet', 'Timesheets', $nb);
    }

    function showForm($ID, $options = [])
    {

        $canedit = $this->can(self::$rightname, UPDATE);

        $rand_time = mt_rand();
        $rand_user = mt_rand();
        $rand_text = mt_rand();

        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        $ts = new PluginTimesheetTimesheet();
        $ts->getFromDB($this->fields['id']);
        echo "<tr class='tab_bg_1'>";
        echo "<th>" . __('User') . "</th>";
        echo "<td class='fa-label'>";
        $params = ['name' => "users_id_tech",
            'value' => (($ID > -1)
                ? $this->fields["users_id_tech"]
                : Session::getLoginUserID()),
            'right' => "own_ticket",
            'rand' => $rand_user,
            //'entity' => $item->fields["entities_id"],
            'width' => ''];


        User::dropdown($params);


        echo "</td>";
        echo "</tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<th>" . __('Duration') . "</th>";
        echo "<td>";
        $toadd = [];
        for ($i = 9; $i <= 100; $i++) {
            $toadd[] = $i * HOUR_TIMESTAMP;
        }

        Dropdown::showTimeStamp("actiontime", ['min' => 0,
            'max' => 8 * HOUR_TIMESTAMP,
            'value' => $this->fields["actiontime"],
            'rand' => $rand_time,
            'addfirstminutes' => true,
            'inhours' => true,
            'toadd' => $toadd,
            'width' => '']);
        echo "</td></tr>\n";


        echo "<tr class='tab_bg_1'>";
        echo "<th>" . __("Date") . "</th>";
        echo "<td>";
        Html::showDateField("task_date_creation", ['value' => $this->fields["task_date_creation"],
            'timestep' => 1,
            'maybeempty' => false]);
        echo "</td";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>" . __("Description") . "</th>";
        echo "<td>";

        $content_id = "content$rand_text";
        $cols = 100;
        $rows = 10;

        Html::textarea(['name' => 'content',
            'value' => $this->fields["content"],
            'rand' => $rand_text,
            'editor_id' => $content_id,
            'enable_fileupload' => false,
            'enable_richtext' => true,
            'cols' => $cols,
            'rows' => $rows]);

        echo "</td>";
        echo "</tr>";

        $this->showFormButtons($options);
    }

    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
            'id' => 'common',
            'name' => __('Timesheets')
        ];

        $tab[] = [
            'id' => '1',
            'table' => self::getTable(),
            'field' => 'id',
            'name' => __('ID'),
        ];

        $tab[] = [
            'id' => '2',
            'table' => self::getTable(),
            'field' => 'tickettasks_id',
            'name' => __('Task'),
            'datatype' => 'specific'
        ];

        $tab[] = [
            'id' => 3,
            'table' => 'glpi_entities',
            'field' => 'completename',
            'linkfield' => 'entities_id',
            'datatype' => 'itemlink',
            'name' => __('Entity'),
            'joinparams' => [
                'beforejoin' => [
                    'table' => 'glpi_tickets',
                    'joinparams' => [
                        'beforejoin' => [
                            'table' => 'glpi_tickettasks',
                            'joinparams' => [
                                'beforejoin' => [
                                    'table' => self::getTable(),
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];


        $tab[] = [
            'id' => '4',
            'table' => 'glpi_users',
            'field' => 'user_fullname',
            'linkfield' => 'users_id_tech',
            'datatype' => 'itemlink',
            'name' => __('User'),
            'computation' => "ifnull(CONCAT(TABLE.`firstname`, ' ', TABLE.`realname`), TABLE.`name`)",
            'joinparams' => [
                'beforejoin' => [
                    'table' => self::getTable(),
                    'joinparams' => [
                        'jointype' => 'empty'
                    ]
                ]
            ]
        ];

        $tab[] = [
            'id' => '5',
            'table' => self::getTable(),
            'field' => 'actiontime',
            'name' => __('Duration'),
            'datatype' => 'specific',
        ];

        $tab[] = [
            'id' => '30',
            'table' => self::getTable(),
            'field' => 'task_date_creation',
            'name' => __('Date'),
            'datatype' => 'date'
        ];

        $tab[] = [
            'id' => '35',
            'table' => 'glpi_projects',
            'linkfield' => 'projects_id',
            'field' => 'name',
            'name' => __('Project'),
            'datatype' => 'itemlink',
            'joinparams' => [
                'beforejoin' => [
                    'table' => self::getTable(),
                    'joinparams' => [
                        'jointype' => 'empty'
                    ]
                ]
            ]
        ];

        $tab[] = [
            'id' => '40',
            'table' => self::getTable(),
            'field' => 'content',
            'name' => __('Comment'),
            'datatype' => 'html'
        ];

        return $tab;
    }

    static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }

        switch ($field) {
            case 'actiontime':
                if (
                    isset($_GET['no_formatted_actiontime'])
                    && isset($_GET['display_type'])
                    && in_array($_GET['display_type'], [
                        Search::CSV_OUTPUT,
                        Search::CSV_OUTPUT * Search::GLOBAL_SEARCH,
                        Search::SYLK_OUTPUT,
                        Search::SYLK_OUTPUT * Search::GLOBAL_SEARCH
                    ])
                )
                    return intval($values[$field] / 60);

                return Html::timestampToString($values[$field], false, false);
            case 'tickettasks_id':
                $tickettasks_id = $values[$field];
                $Task = new TicketTask();
                $Task->getFromDB($tickettasks_id);
                $Ticket = new Ticket();
                $Ticket->getFromDB($Task->getField('tickets_id'));
                return sprintf('[%s #%07d] %s', 'GLPI Zgłoszenie', $Task->getField('tickets_id'), $Ticket->getField('name'));

        }

        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    function getSpecificMassiveActions($checkitem = null)
    {
        $actions = [];
        $myclass = 'PluginTimesheetTimesheet';

        if (Session::haveRight('plugin_timesheet_timesheet', CREATE)) {
            $actions[$myclass . MassiveAction::CLASS_ACTION_SEPARATOR . 'attach'] = __("Attach to Project", 'timesheet');
            $actions[$myclass . MassiveAction::CLASS_ACTION_SEPARATOR . 'detach'] = __("Detach from Project", 'timesheet');
        }
        if (Session::haveRight('plugin_timesheet_timesheet', DELETE | PURGE)) {
            $actions[$myclass . MassiveAction::CLASS_ACTION_SEPARATOR . 'destroy'] = __("Delete");
        }

        return $actions;
    }

    static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        switch ($ma->getAction()) {
            case 'attach':
                echo '<div>';
                Project::dropdown([
                    'name' => 'projects_id',
                ]);
                echo '</div><div>';
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                echo '</div>';
                return true;
        }

        return parent::showMassiveActionsSubForm($ma);
    }

    static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    )
    {
        global $DB;
        switch ($ma->getAction()) {
            case 'attach' :
                if ($item->getType() == 'PluginTimesheetTimesheet') {
                    $input = $ma->getInput();
                    if (!empty($input['projects_id'])) {
                        foreach ($ids as $id) {
                            if ($item->update([
                                'id' => $id,
                                'projects_id' => $input['projects_id']
                            ])) {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                            }
                        }
                    }
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }
                return;
            case 'detach' :
                if ($item->getType() == 'PluginTimesheetTimesheet') {
                    foreach ($ids as $id) {
                        if ($item->update([
                            'id' => $id,
                            'projects_id' => 0
                        ])) {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        }
                    }
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }
                return;
            case 'destroy' :
                if ($item->getType() == 'PluginTimesheetTimesheet') {
                    foreach ($ids as $id) {
                        $item->getFromDB($id);
                        if ($item->canDeleteItem() && Session::haveRight(self::$rightname, DELETE | PURGE)) {
                            $item->deleteFromDB();
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        }
                    }
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item::getType()) {
            case Project::getType():
                return __('Timesheets', 'timesheet');
                break;
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item::getType()) {
            case Project::getType():
                self::displayTabContentForProject($item);
                break;
        }
        return true;
    }

    static function displayTabContentForProject(CommonGLPI $item)
    {
        global $DB;

        if (isset($_GET["start"])) {
            $start = intval($_GET["start"]);
        } else {
            $start = 0;
        }

        if (isset($_GET['order']) && !empty($_GET['order'])) {
            $order = $_GET['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($_GET['sort']) && !empty($_GET['sort'])) {
            $sort = $_GET['sort'];
        } else {
            $sort = 'id';
        }

        $dbu = new DbUtils();

        $ID = $item->getID();
        $iterator = $DB->request([
            'SELECT' => [
                'glpi_plugin_timesheet_timesheets.id',
                'glpi_plugin_timesheet_timesheets.tickettasks_id',
                'glpi_tickets.id as ticket_id',
                'glpi_tickets.name as ticket_name',
                'glpi_plugin_timesheet_timesheets.projects_id',
                'glpi_plugin_timesheet_timesheets.actiontime',
                'glpi_plugin_timesheet_timesheets.users_id_tech',
                'glpi_plugin_timesheet_timesheets.content',
                'glpi_plugin_timesheet_timesheets.task_date_creation',
                'glpi_entities.completename',
                'glpi_entities.id as entity_id',
                new \QueryExpression('ifnull(concat(`glpi_users`.`firstname`," ", `glpi_users`.`realname`), `glpi_users`.`name`) as `fullname`'),
            ],
            'FROM' => 'glpi_plugin_timesheet_timesheets',
            'WHERE' => [
                'projects_id' => $ID,
            ] + $dbu->getEntitiesRestrictCriteria("glpi_tickets"),
            'LEFT JOIN' => [
                'glpi_users' => [
                    'FKEY' => [
                        'glpi_users' => 'id',
                        'glpi_plugin_timesheet_timesheets' => 'users_id_tech'
                    ]
                ],
                'glpi_tickettasks' => [
                    'FKEY' => [
                        'glpi_tickettasks' => 'id',
                        'glpi_plugin_timesheet_timesheets' => 'tickettasks_id'
                    ]
                ],
                'glpi_tickets' => [
                    'FKEY' => [
                        'glpi_tickets' => 'id',
                        'glpi_tickettasks' => 'tickets_id'
                    ]
                ],
                'glpi_entities' => [
                    'FKEY' => [
                        'glpi_entities' => 'id',
                        'glpi_tickets' => 'entities_id'
                    ]
                ]
            ],
            'ORDER' => "$sort $order",
            'START' => $start,
            'LIMIT' => $_SESSION['glpilist_limit']
        ]);

        $numrows = count($iterator);

        if ($numrows) {
            $rand = mt_rand();

            echo "<div class='center'>";
            $canadd = Session::haveRight('plugin_timesheet_timesheet', CREATE);
            $candelete = Session::haveRight('plugin_timesheet_timesheet', DELETE | PURGE);

            $timesheet = new self();

            if ($canadd || $candelete) {

                $actions = $timesheet->getSpecificMassiveActions();
                unset($actions['PluginTimesheetTimesheet' . MassiveAction::CLASS_ACTION_SEPARATOR . 'attach']);

                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = [
                    'item' => $timesheet,
                    'num_displayed' => min($_SESSION['glpilist_limit'], $numrows),
                    'container' => 'mass' . __CLASS__ . $rand,
                    'specific_actions' => $actions
                ];

                Html::showMassiveActions($massiveactionparams);
            }
            echo "<table class='tab_cadre_fixehov'>";

            $header = '<tr>';


            if ($canadd || $candelete) {
                $header .= "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
            }

            $header .= "<th" . ($sort == "id" ? " class='order_$order'" : '') . "><a href='javascript:reloadTab(\"sort=id&amp;order=" . (($order == "ASC") ? "DESC" : "ASC") . "&amp;start=0\");'>" . __('ID') . "</a></th>";
            $header .= "<th" . ($sort == "tickettasks_id" ? " class='order_$order'" : '') . "><a href='javascript:reloadTab(\"sort=tickettasks_id&amp;order=" . (($order == "ASC") ? "DESC" : "ASC") . "&amp;start=0\");'>" . __('Task') . "</a></th>";
            $header .= "<th" . ($sort == "completename" ? " class='order_$order'" : '') . "><a href='javascript:reloadTab(\"sort=completename&amp;order=" . (($order == "ASC") ? "DESC" : "ASC") . "&amp;start=0\");'>" . __('Entity') . "</a></th>";
            $header .= "<th" . ($sort == "actiontime" ? " class='order_$order'" : '') . "><a href='javascript:reloadTab(\"sort=actiontime&amp;order=" . (($order == "ASC") ? "DESC" : "ASC") . "&amp;start=0\");'>" . __('Duration') . "</a></th>";
            $header .= "<th" . ($sort == "task_date_creation" ? " class='order_$order'" : '') . "><a href='javascript:reloadTab(\"sort=task_date_creation&amp;order=" . (($order == "ASC") ? "DESC" : "ASC") . "&amp;start=0\");'>" . __('Date') . "</a></th>";
            $header .= "<th" . ($sort == "fullname" ? " class='order_$order'" : '') . "><a href='javascript:reloadTab(\"sort=fullname&amp;order=" . (($order == "ASC") ? "DESC" : "ASC") . "&amp;start=0\");'>" . __('User') . "</a></th>";
            $header .= "<th" . ($sort == "content" ? " class='order_$order'" : '') . "><a href='javascript:reloadTab(\"sort=content&amp;order=" . (($order == "ASC") ? "DESC" : "ASC") . "&amp;start=0\");'>" . __('Comment') . "</a></th>";
            $header .= "</tr>";
            echo $header;


            foreach ($iterator as $item) {

                echo "<tr class='tab_bg_2'>";

                if ($canadd || $candelete) {
                    echo "<td width='10'>";
                    Html::showMassiveActionCheckBox('PluginTimesheetTimesheet', $item['id']);
                    echo "</td>";
                }

                $date = new \DateTime($item['task_date_creation']);

                echo "<td style='text-align:right'>" . $item['id'] . "</td>" .
                    "<td style='text-align:left'>" . (empty($item['tickettasks_id']) ? '' : sprintf('[%s #%07d] %s', 'GLPI Zgłoszenie', $item['ticket_id'], $item['ticket_name'])) . "</td>" .
                    "<td style='text-align:left'><a href=\"/front/entity.form.php?id=" . $item['entity_id'] . "\">" . $item['completename'] . "</a></td>" .
                    "<td style='text-align:right'>" . Html::timestampToString($item['actiontime'], false, false) . "</td>" .
                    "<td style='text-align:right'>" . $date->format('Y-m-d') . "</td>" .
                    "<td style='text-align:center'><a href=\"/front/users.form.php?id=" . $item['users_id_tech'] . "\">" . $item['fullname'] . "</a></td>" .
                    "<td>" . $item['content'] . "</td>" .
                    "<td></td>";
                echo "</tr>";
            }

            echo $header;
            echo "</table>";

            $massiveactionparams['ontop'] = false;
            if ($canadd || $candelete) {
                Html::showMassiveActions($massiveactionparams);
            }

            echo "</div>";
            Html::printAjaxPager(PluginTimesheetTimesheet::getTypeName(1), $start, $numrows);

        }


    }

}

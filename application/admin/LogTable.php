<?php

namespace TooMuchNiche\application\admin;

defined('\ABSPATH') || exit;

use TooMuchNiche\application\models\LogModel;
use TooMuchNiche\application\components\logger\Logger;
use TooMuchNiche\application\Plugin;

use function TooMuchNiche\prn;

/**
 * LogTable class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class LogTable extends MyListTable
{

    const per_page = 30;

    function get_columns()
    {
        return
            array(
                //'log_level' => LogModel::model()->getAttributeLabel('log_level'),
                'log_time' => LogModel::model()->getAttributeLabel('log_time'),
                'message' => LogModel::model()->getAttributeLabel('message'),
            );
    }

    function column_message($item)
    {
        $m = $item['message'];
        return \wp_kses_post($m);
    }

    function column_log_time($item)
    {
        return $this->view_column_date($item, 'log_time');
    }

    function column_log_level($item)
    {

        if ($item['log_level'] == Logger::LEVEL_ERROR)
            $class = 'error';
        elseif ($item['log_level'] == Logger::LEVEL_WARNING)
            $class = 'warning';
        elseif ($item['log_level'] == Logger::LEVEL_INFO)
            $class = 'info';
        elseif ($item['log_level'] == Logger::LEVEL_DEBUG)
            $class = 'debug';
        else
            $class = '';

        return '<mark class="' . \esc_attr($class) . '">' . ucfirst(Logger::getLevel($item['log_level'])) . '</mark>';
    }

    function get_sortable_columns()
    {
        return array();
    }

    function get_bulk_actions()
    {
        return array();
    }

    protected function getWhereFilters()
    {
        return array();
    }

    protected function extra_tablenav($which)
    {
        return array();
    }

    public function display()
    {
        $singular = $this->_args['singular'];

        $this->display_tablenav('top');

        $this->screen->render_screen_reader_content('heading_list');
?>
        <table class="table wp-list-table ">

            <tbody id="the-list" <?php
                                    if ($singular)
                                    {
                                        echo " data-wp-lists='list:$singular'";
                                    }
                                    ?>>
                <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

        </table>
<?php
        $this->display_tablenav('bottom');
    }
}

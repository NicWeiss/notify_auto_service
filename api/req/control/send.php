<?php

/**
 * class acceptor
 * отвечает за управление списком получателей
 */

namespace control;

use generic\BaseController;
use lib\email;
use lib\telegram;
use model\cron_model as model;

class send extends BaseController
{
    private static $workday_list = ['1', '2', '3', '4', '5'];
    private static $weekend_list = ['6', '7'];
    private static $notify_pool = [];

    public static function run()
    {
        $first_day = '01';
        $last_day = gmdate("t");
        $current_day = gmdate("d");
        $day_of_week = gmdate("w");
        $day_of_week = $day_of_week == '0' ? '7' : $day_of_week;

        $current_time = gmdate('H:i');
        $current_date = gmdate('m.d.Y');

        self::find_by_type('everyday', $current_time);
        self::find_once($current_date, $current_time);
        self::find_day_of_week($current_time, $day_of_week);
        self::find_every_month($current_date, $current_time);
        self::find_every_year($current_date, $current_time);

        if ($first_day == $current_day) {
            self::find_by_type('first_month_day', $current_time);
        }
        if ($last_day == $current_day) {
            self::find_by_type('last_month_day', $current_time);
        }
        if (in_array($day_of_week, self::$workday_list)) {
            self::find_by_type('workday', $current_time);
        }
        if (in_array($day_of_week, self::$weekend_list)) {
            self::find_by_type('weekend', $current_time);
        }

        self::send_notify();

        std_log("\n ------------------------- Sending is done! -------------------------- \n");
    }

    private static function find_once($date, $time)
    {
        $notify_list = model::get_once_notifies($date, $time);
        self::process_notify($notify_list);
    }

    private static function find_by_type($type, $time)
    {
        $notify_list = model::get_types_notifies($type, $time);
        self::process_notify($notify_list);
    }

    private static function find_day_of_week($time, $day_of_week)
    {
        $notify_list = model::get_day_of_week_notifies($time, $day_of_week);
        self::process_notify($notify_list);
    }

    private static function find_every_month($date, $time)
    {
        $notify_list = model::get_every_month_notify($date, $time);
        self::process_notify($notify_list);
    }

    private static function find_every_year($date, $time)
    {
        $notify_list = model::get_every_year_notify($date, $time);
        self::process_notify($notify_list);
    }

    private static function process_notify($notify_list)
    {
        foreach ($notify_list as $notify) {
            if (!$notify['acceptorsList']) {
                std_error_log("У уведомления " . $notify['id'] . " : " . $notify['name'] . " нет получателей");
            }
            foreach ($notify['acceptorsList'] as $acceptor) {
                array_push(self::$notify_pool, ['notify' => $notify, 'acceptor' => $acceptor]);
            }
        }
    }

    private static function send_notify()
    {
        foreach (self::$notify_pool as $item) {
            $account = $item['acceptor']['account'];
            $title = $item['notify']['name'];
            $text = $item['notify']['text'] ? $item['notify']['text'] : ' ';
            $type = $item['acceptor']['type'];


            std_log($item['notify']['id'] . ":" . $type . " -> " . $account . " \n");

            if (!$type) {
                std_log("У получателя " . $item['acceptor']['name'] . " : " . $item['acceptor']['account'] . " нет типа");
            }

            if ($type == 'email') {
                email::send([
                    'to' => $account,
                    'title' => $title,
                    'text' => $text
                ]);
            }
            if ($type == "tg") {
                telegram::send([
                    'to' => $account,
                    'title' => $title,
                    'text' => $text
                ]);
            }
        }
    }
}

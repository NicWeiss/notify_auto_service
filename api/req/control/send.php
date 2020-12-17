<?php

/**
 * class acceptor
 * отвечает за управление списком получателей
 */

namespace control;

use generic\component;
use lib\email;
use lib\telegram;
use model\notify_model as nf;

class send extends component
{
    private static $workday_list = ['1', '2', '3', '4', '5'];
    private static $weekend_list = ['6', '7'];
    private static $notify_pool = [];

    public static function run()
    {
        $first_day = '01';
        $last_day = date("t");
        $current_day = date("d");
        $day_of_week = date("w");
        $day_of_week = $day_of_week == '0' ? '7' : $day_of_week;

        self::process_notify('once');
        self::process_notify('everyday');
        self::process_notify('day_of_week', $day_of_week);

        if ($first_day == $current_day) {
            self::process_notify('first_month_day');
        }
        if ($last_day == $current_day) {
            self::process_notify('last_month_day');
        }
        if (in_array($day_of_week, self::$workday_list)) {
            self::process_notify('workday');
        }
        if (in_array($day_of_week, self::$weekend_list)) {
            self::process_notify('weekend');
        }

        self::send_notify();
    }

    private static function process_notify($type, $day_of_week = null)
    {
        $notify_list = nf::get_notify_for_cron($type, $day_of_week);

        foreach ($notify_list as $notify) {
            date_default_timezone_set('Etc/GMT' . $notify['time_zone_offset']);

            # получаем дату и время сервера с учётом временной зоны
            $current_time = date('H:i');
            $current_date = date('d-m-Y');

            # получаем дату и  время нотификаций с учётом временной зоны
            $notify_time = null;
            $notify_date = null;
            if ($notify['date']) {
                $notify_date = date('d-m-Y', substr($notify['date'], 0, -3));
            }
            $notify_time = date('H:i', substr($notify['time'], 0, -3));

            if ($current_time == $notify_time) {

                if ($notify_date != $current_date && $notify_date != null) {
                    continue;
                }
                if (!$notify['acceptorsList']) {
                    std_error_log("У уведомления " . $notify['id'] . " : " . $notify['name'] . " нет получателей");
                }
                foreach ($notify['acceptorsList'] as $acceptor) {
                    array_push(self::$notify_pool, ['notify' => $notify, 'acceptor' => $acceptor]);
                }
            } else {
                std_log('!!! times not match! : EXIT');
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

            if ($title) {
            }
            if (!$type) {
                std_error_log("У получателя " . $item['acceptor']['name'] . " : " . $item['acceptor']['account'] . " нет типа");
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

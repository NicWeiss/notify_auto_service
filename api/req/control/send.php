<?php
/**
 * class acceptor
 * отвечает за управление списком получателей
 */

namespace control;

use generic\component;
use lib\email;
use lib\request;
use model\notify_model as nf;

class send extends component
{
    public static function run()
    {
        //получить текушие время и даты
        //внутри цикла анализировать на соответствиедаты и времени
        //так-же анализировать на периодичность

        $notify_list = nf::get_notify_for_cron();
        foreach ($notify_list as $notify) {
            foreach ($notify['acceptorsList'] as $acceptor) {
                if ($acceptor['type'] == 'email') {
                    email::send([
                        'to' => $acceptor['account'],
                        'title' =>$notify['name'],
                        'text' => $notify['text']
                    ]);
                }
                if ($acceptor['type'] == "tg"){
                    print_r('tg');
                }
            }
        }
    }

}

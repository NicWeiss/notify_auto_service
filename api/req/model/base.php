<?php

namespace model;

use lib\dba as dba;

use function PHPSTORM_META\type;

final class base
{

    //----------------------------------------------------------------------------------- получение информации о пользователе
	public static function get_user($u_id){

		$sql="SELECT iu.u_name, iu.u_st, idep.d_name, iud.ud_department_id,iu.u_cell  ,iu.u_surname, idep.d_id,  iu.mo_card, pt.p_name, pt.p_id FROM `i_users` iu
		left join `i_post` pt on pt.p_id = iu.u_post
		left join `i_user_departments` iud on iud.ud_user_id = iu.u_id
		left join `i_departments` idep on idep.d_id = iud.ud_department_id 
		 WHERE `u_id`='".$u_id."'";

        dba::query($sql);
		$info = dba::fetch_assoc();
		return $info;
	}

    /** Получение новостей */
    public static function get_news($params)
    {
        //Prepare additional requests
        $page_by_limit = $params['page']*PAGE_POSTS_LIMIT . ' , ' . PAGE_POSTS_LIMIT;

        $additional_type = strlen($params['type'])>0 ? " and `a_is_general`='".$params['type']. "' " : '';
        $additional_filter = strlen($params['filter'])>0 ? " and ia.a_title LIKE '%" . $params['filter'] . "%'" : '';
        if(strlen($params['view'])>0){
            if ($params['view'] == '0') {
                $additional_view = " and (ia.a_expiretime > '" . time() . "' or ia.a_expiretime = '0') ";
            }
            if ($params['view'] == '2') {
                $additional_view = " and ia.a_expiretime != '0' AND ia.a_expiretime < '" . time() . "' ";
            }
        }
        $additional_trash = " and ia.a_is_disabled = '0' ";
        if(strlen($params['trash'])>0){
            if ($params['trash'] == '1') {
                $additional_trash = " and ia.a_is_disabled = '1' ";
            }
        }

        $sql = "SELECT iug.ug_group_id FROM `i_users` iu
	            left join i_user_groups iug on iug.ug_user_id=iu.u_id
	            WHERE iu.u_id='" . $params['user_id'] . "'";
        dba::query($sql);
        $tmp = dba::fetch_assoc_all();
        $additional_req = "";
        $date = date_create();

        $i = 0;
        foreach ($tmp as $row) {
            $additional_req = $additional_req . " iatg.atg_group_id = '" . $tmp[$i]['ug_group_id'] . "' ";
            $i++;
            if (count($tmp) != $i) {
                $additional_req = $additional_req . "OR";
            }
        }
        if ($additional_req) {
            $additional_req = "AND ({$additional_req})";
        }

        //Generate and Query SQL
        $sql = "SELECT * FROM `i_announces` ia
	            left join i_announces_to_groups iatg on iatg.atg_announce_id=ia.a_id
	            WHERE `a_update_ts`< '" . date_timestamp_get($date) . "' " . $additional_req . $additional_type . $additional_view . $additional_trash . $additional_filter .
            " and `a_need_visa` = '0' and `a_published` = '1' group by ia.a_id ORDER BY `a_update_ts` DESC limit " . $page_by_limit;
        dba::query($sql);
        $tmp = dba::fetch_assoc_all();
        
        //Prepare FOR REST
        $i=0;
        foreach($tmp as $key){
            $tmp[$i]['id'] = $tmp[$i]['a_id'];
            $i++;
        }

        return $tmp;
    }

    /** Получение пользовательских прав доступа */
    public static function get_rights($id)
    {
        $sql = "SELECT * FROM i_user_rights  WHERE `ur_user_id`='" . $id . "'";
        dba::query($sql);
        $tmp = dba::fetch_assoc();
        return $tmp;
    }

    /** Получение пользовательских настроек отображения */
    public static function get_view($id)
    {
        $sql = "SELECT * FROM i_announces_settings  WHERE `s_user_id`='" . $id . "' ";
        dba::query($sql);
        $tmp = dba::fetch_assoc();
        if (!$tmp) {
            $sql = "INSERT INTO `i_announces_settings`( `s_user_id`, `s_view_type`,`s_trash`) VALUES ('" . $id . "',0,0)";
            dba::query($sql);
            dba::fetch_assoc();
        }
        return $tmp;
    }
}

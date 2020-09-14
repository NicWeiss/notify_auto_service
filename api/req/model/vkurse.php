<?php
/**
 * user entity library
*
* @author Nikitin Artyom <Ixornic@gmail.com>
* @copyright Copyright (c) 2017, Nikitin Artyom
* @package model
*/

namespace model;
use generic\model as model;
use lib\dba as dba;
use bbcode;

/**
 * standard checking
 */
if (!defined('_INNER'))
	die;

class vkurse extends model{

	/**
	 * db instance
	 * @var \lib\dba\generic
	 */
 	public static $db = null;
 	public static $nsp_db = null;


public static function get_next_news_id(){

$sql="SELECT * FROM `i_announces` ORDER BY `a_id` DESC LIMIT 1";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc();
	return $tmp['a_id']+1;
}

//-----------------------------------------------------------------------------------получение списка департаментов и их кодовые номера

public static function get_dep_array(){
		$sql="SELECT * FROM `i_departments` ORDER BY `d_name` ASC";
		self::$db->query($sql);
		$arr = self::$db -> fetch_assoc_all();
		$all=count($arr);
		$col=0;
		$output=array();
        		for($i=0; $i<$all; $i++){
        			$sql="SELECT iu.u_id FROM `i_users` iu
        			left join `i_user_departments` iud on iud.ud_user_id=iu.u_id
        			left join `i_user_rights` ur on ur.ur_user_id=iu.u_id
        			where iud.ud_department_id=".$arr[$col]['d_id']." and ur.ur_can_add_edit_remove = 1 and iu.u_is_disabled=0
        			";
					self::$db->query($sql);
					$dep = self::$db -> fetch_assoc_all();
					if (count($dep)>0) {
						array_push($output,$arr[$col]);
						//$output[$col]=$arr[$col];
					}
						$col++;
        		}
        		//std_debug($col);
		return $output;
	}


//-----------------------------------------------------------------------------------Получение информации о должности сотрудника по его ID

public static function get_user_post($u_id){
		$sql="SELECT  pt.p_name, pt.p_id FROM `i_users` iu
		left join `i_post` pt on pt.p_id = iu.u_post
		 WHERE `u_id`='".$u_id."'";

		self::$db->query($sql);
		$array = self::$db -> fetch_assoc();
		return $array;
}


//-----------------------------------------------------------------------------------Подпись о принятии решения о одобрении или отказе

public static function set_approve($u_id, $id, $status, $time){
$sql="UPDATE `i_announces` SET `a_who_approve`='".$u_id."', `a_published`='".$status."', `a_update_ts`= '".$time."'
		WHERE `a_id` = '".$id."'";
		self::$db->query($sql);
}


//-----------------------------------------------------------------------------------Возвращает статус закладки для пользователя

public static function bookmarks_state($u_id,$a_id){
$sql="SELECT * FROM `i_announces_bookmarks`
		 WHERE b_u_id='".$u_id."' and b_a_id='".$a_id."'";
		self::$db->query($sql);
		$tmp = self::$db -> fetch_assoc_all();
		if(count($tmp)>0){
			$active=$tmp[0]['b_active'];
		}else{
			$active=0;
		}
		return $active;
}

//----------------------------------------------------------------------------------- получение информации о пользователе
	public static function getALLuserinfo($u_id){

		$sql="SELECT iu.u_name, iu.u_st, idep.d_name, iud.ud_department_id,iu.u_cell  ,iu.u_surname, idep.d_id,  iu.mo_card, pt.p_name, pt.p_id FROM `i_users` iu
		left join `i_post` pt on pt.p_id = iu.u_post
		left join `i_user_departments` iud on iud.ud_user_id = iu.u_id
		left join `i_departments` idep on idep.d_id = iud.ud_department_id 
		 WHERE `u_id`='".$u_id."'";

		self::$db->query($sql);
		$info = self::$db -> fetch_assoc();
		return $info;
	}
//-----------------------------------------------------------------------------------Установка или удаление закладки

public static function change_bookmark($u_id, $a_id, $active){
	$sql="SELECT * FROM `i_announces_bookmarks`
		 WHERE b_u_id='".$u_id."' and b_a_id='".$a_id."'";
		self::$db->query($sql);
		$tmp = self::$db -> fetch_assoc_all();
//std_debug($sql);
	if($active==0){	
		if (count($tmp)>0){
			$sql="DELETE FROM `i_announces_bookmarks` WHERE b_u_id='".$u_id."' and b_a_id='".$a_id."'";
			self::$db->query($sql);
		}
	}else{
			$sql="DELETE FROM `i_announces_bookmarks` WHERE b_u_id='".$u_id."' and b_a_id='".$a_id."'";
			self::$db->query($sql);
			$sql="INSERT INTO `i_announces_bookmarks`(`b_u_id`, `b_a_id`, `b_active`) VALUES ('".$u_id."','".$a_id."','".$active."')";
			self::$db->query($sql);
	}	


}

//----------------------------------------------------------------------------------- Комментирование приказа

public static function set_comment($n_id, $u_id, $u_name, $u_surname, $comment){
/*$sql="INSERT INTO `i_announces_comments`(`c_aid`, `c_uid`, `c_name`, `c_surname`, `c_time`, `c_comment`) 
	VALUES ('".$n_id."','".$u_id."','".$u_name."','".$u_surname."',NOW(),' ".$comment." ')";
		self::$db->query($sql);*/
	self::$db->query_insert('i_announces_comments',[
		'c_aid' => $n_id,
		'c_uid' => $u_id,
		'c_name' => $u_name,
		'c_surname' => $u_surname,
		'c_time' => 'NOW()',
		'c_comment' => $comment
	],['c_time']);
}

//----------------------------------------------------------------------------------- Удвление комментария

public static function del_comment($c_id, $c_who_del){
$sql="UPDATE `i_announces_comments` SET `c_del`='1',`c_who_del`='".$c_who_del."', `c_del_time`=NOW() 
		WHERE `c_id` = '".$c_id."'";
		self::$db->query($sql);
}

//----------------------------------------------------------------------------------- Восстановление комментария

public static function restore_comment($c_id){
$sql="UPDATE `i_announces_comments` SET `c_del`='0',`c_who_del`=' ', `c_del_time`=' '
		WHERE `c_id` = '".$c_id."'";
		self::$db->query($sql);
}

//-----------------------------------------------------------------------------------Загрузка крмментариев к новости
public static function get_comment($id){
		$sql="SELECT * FROM `i_announces_comments` 
		WHERE `c_aid`='".$id."'";
		self::$db->query($sql);
		$arr = self::$db -> fetch_assoc_all();
		return $arr;
}

//-----------------------------------------------------------------------------------передача приказа

public static function transfer_news($u_id, $a_id, $current_time, $iniciator){
		$sql="UPDATE `i_announces` SET `a_author`='".$u_id."',`a_need_visa`='1', `a_update_author`='".$iniciator."' , `a_update_ts`= '".$current_time."', `a_is_disabled`='0',`a_is_periodic`='0' 
		WHERE `a_id` = '".$a_id."'";
		self::$db->query($sql);
		$arr = self::$db -> fetch_assoc();
	}

//-----------------------------------------------------------------------------------получение списка пользователей входящих в выбранный департамент


public static function get_dep_user_array($d_id){
		$sql="SELECT iu.u_name, iu.u_id, pt.p_name, iu.u_surname FROM `i_user_departments` iud
		left join `i_users` iu on iu.u_id = iud.ud_user_id
		left join `i_user_rights` ur on ur.ur_user_id = iu.u_id
		left join `i_post` pt on pt.p_id = iu.u_post
		 WHERE `ud_department_id`='".$d_id."' and ur.ur_can_add_edit_remove=1 and iu.u_is_disabled = 0 ORDER BY `u_surname` ASC";
		 //std_debug($sql);
		self::$db->query($sql);
		$arr = self::$db -> fetch_assoc_all();
		//std_debug($arr);
		return $arr;
	}


//-----------------------------------------------------------------------------------получение названия выбранного департамента


public static function get_depname($d_id){
		$sql="SELECT * FROM `i_departments`
		 WHERE `d_id`='".$d_id."'";
		self::$db->query($sql);
		$arr = self::$db -> fetch_assoc();
		return $arr;
	}

  //-------------------------------------------------------------------------------- Колличество доступных новостей
public static function get_news_count($id,$type_req,$view_req,$trash_req,$search_req){

	$sql="SELECT iug.ug_group_id FROM `i_users` iu
	left join i_user_groups iug on iug.ug_user_id=iu.u_id
	 WHERE iu.u_id='".$id."'";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();
	$add_req="";
	$date = date_create();

	$i=0;
	foreach($tmp as $row){
		$add_req=$add_req." iatg.atg_group_id = '".$tmp[$i]['ug_group_id']."' " ;
		$i++; 
		if (count($tmp) != $i){$add_req=$add_req."OR";}
        }
     if ($add_req){
     	$add_req="and (".$add_req.")";
     }
     if (strlen($view_req)<6 ){$view_req="";}
	$sql="SELECT ia.a_id FROM `i_announces` ia
	left join i_announces_to_groups iatg on iatg.atg_announce_id=ia.a_id
	WHERE `a_update_ts`<".date_timestamp_get($date)." and ".$type_req.$view_req.$trash_req." ".$add_req." " .$search_req. "
	and `a_need_visa` = '0' and `a_published` = '1'
	 group by ia.a_id ORDER BY `a_update_ts` DESC";
	//std_debug($sql);
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();
	return count($tmp);
}


  
  //-------------------------------------------------------------------------------- Получение новостей
public static function get_news($id,$limit,$type_req,$view_req,$trash_req,$search_req){

	$sql="SELECT iug.ug_group_id FROM `i_users` iu
	left join i_user_groups iug on iug.ug_user_id=iu.u_id
	 WHERE iu.u_id='".$id."'";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();
	$add_req="";
	$date = date_create();

	$i=0;
	foreach($tmp as $row){
		$add_req=$add_req." iatg.atg_group_id = '".$tmp[$i]['ug_group_id']."' " ;
		$i++; 
		if (count($tmp) != $i){$add_req=$add_req."OR";}
        }
    if($add_req){
    	$add_req = "AND ({$add_req})";
    }
	$sql="SELECT * FROM `i_announces` ia
	left join i_announces_to_groups iatg on iatg.atg_announce_id=ia.a_id
	WHERE `a_update_ts`<".date_timestamp_get($date)." and ".$type_req.$view_req.$trash_req." ".$add_req." ".$search_req. "
	and `a_need_visa` = '0' and `a_published` = '1'
	 group by ia.a_id ORDER BY `a_update_ts` DESC ".$limit;
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();
	return $tmp;
}


  //-------------------------------------------------------------------------------- Получение закладок
public static function get_bookmarks($u_id){

	//local constant's
	$type_req="(ia.a_is_general='0' or ia.a_is_general='1' or ia.a_is_general='2' or ia.a_is_general='3') and";
	$view_req="(ia.a_expiretime > '".time()."' or ia.a_expiretime = '0') and ";
	$trash_req="ia.a_is_disabled = '0'";

	//sql query
	$sql="SELECT iug.ug_group_id FROM `i_users` iu
	left join i_user_groups iug on iug.ug_user_id=iu.u_id
	 WHERE iu.u_id='".$u_id."'";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();
	$add_req="";

	$i=0;
	foreach($tmp as $row){
		$add_req=$add_req." iatg.atg_group_id = '".$tmp[$i]['ug_group_id']."' " ;
		$i++; 
		if (count($tmp) != $i){$add_req=$add_req."OR";}
        }
    if($add_req){
    	$add_req = "AND ({$add_req})";
    }
	$sql="SELECT * FROM `i_announces` ia
	left join i_announces_to_groups iatg on iatg.atg_announce_id=ia.a_id
	left join i_announces_bookmarks iab on iab.b_a_id=ia.a_id
	WHERE ".$type_req.$view_req.$trash_req." ".$add_req." 
	and `a_need_visa` = '0' and `a_published` = '1' and iab.b_active = '1' and iab.b_u_id='".$u_id."'
	 group by ia.a_id ORDER BY `a_update_ts` DESC ";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();
	//std_debug($sql);
	return $tmp;
}


//------------------------------------------------------------------------------------ Проверяю, прочитал ли я полученные новости
public static function get_reeaded_news($id,$news){
$i=0;
		$add_req="";
	foreach($news as $row){
		$add_req=$add_req." iav.v_announce_id = '".$news[$i]['a_id']."' " ;
		$i++; 
		if (count($news) != $i){$add_req=$add_req."OR";}
        }
        //std_debug($news);
        if($add_req){
        	$add_req = "AND (".$add_req.")";
        }
	$sql="SELECT iav.v_announce_id, iav.v_user_id, iav.v_ts FROM `i_announces` ia
	left join i_announce_views iav on iav.v_announce_id=ia.a_id
	WHERE iav.v_user_id = '".$id."' ".$add_req;
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();
	return $tmp;
}

//
public static function reeaded_newsfor_notify($id,$news){
$i=0;
		$add_req="";
	foreach($news as $row){
		$add_req=$add_req." iav.v_announce_id = '".$news[$i]['a_id']."' " ;
		$i++; 
		if (count($news) != $i){$add_req=$add_req."OR";}
        }
        //std_debug($news);
        if($add_req){
        	$add_req = "AND (".$add_req.")";
        }
$sql="SELECT iav.v_announce_id FROM `i_announces` ia
	left join i_announce_views iav on iav.v_announce_id=ia.a_id
	WHERE iav.v_user_id = '".$id."' ".$add_req."group by `a_id`";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all("v_announce_id");
	return $tmp;

	}

//---------------------------------------------------------------------------------- Получаю инфу выбранной новости
public static function get_news_text($id){
	$sql="SELECT * FROM `i_announces` ia
	left join i_users iu on iu.u_id=ia.a_author
	 WHERE `a_id`='".$id."'";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc();
	return $tmp;
}

//---------------------------------------------------------------------------------- Получаю настройки
public static function get_view($id){
	$sql="SELECT * FROM i_announces_settings  WHERE `s_user_id`='".$id."' ";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc();
	if (!$tmp){
	$sql="INSERT INTO `i_announces_settings`( `s_user_id`, `s_view_type`,`s_trash`) VALUES ('".$id."',0,0)";
	self::$db->query($sql);
	self::$db -> fetch_assoc();
	}
	return $tmp;
	}
//---------------------------------------------------------------------------------- Получаю права
public static function get_rights($id){
	$sql="SELECT * FROM i_user_rights  WHERE `ur_user_id`='".$id."'";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc();
	return $tmp;
	}

//---------------------------------------------------------------------------------- Получаю только свои новости
public static function only_my_news($id,$add_req,$search_req){
$sql="SELECT * FROM i_announces ia  WHERE (`a_author`='".$id."' OR `a_update_author`='".$id."') ".$search_req."  group by `a_id` ORDER by a_update_ts DESC ".$add_req;
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();
		return $tmp;
}

//---------------------------------------------------------------------------------- Получаю не опубликованные новости
public static function news_for_approve($add_req){
$sql="SELECT * FROM i_announces ia  WHERE `a_published` = '0' and `a_is_disabled`='0' and `a_need_visa` = '0'  group by `a_id` ORDER by a_update_ts DESC ".$add_req;
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();
		return $tmp;
}

//---------------------------------------------------------------------------------- Получаю только свои новости
public static function only_delete_personal_news($add_req,$search_req){
$sql="SELECT * FROM i_announces ia  
left join i_users iu on iu.u_id = ia.a_author
WHERE iu.u_is_disabled = '1'  ".$search_req."  group by `a_id` ORDER by a_update_ts DESC ".$add_req;
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();
		return $tmp;
}

//---------------------------------------------------------------------------------- Получаю новость из архива
public static function get_one_archive_item($id){
$sql="SELECT * FROM i_announces_archive  WHERE `iaa_id`='".$id."'";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc();
		return $tmp;
}
//---------------------------------------------------------------------------------- Получаю архив новости
public static function get_archive($id){
	$sql="SELECT * FROM i_announces_archive  WHERE `iaa_news_id`='".$id."' ORDER by `iaa_id` DESC";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();

	  $Month_r = array(                                                                                       //забиваем массив для вывода дат на русском
            "01" => "января", 
            "02" => "февраля", 
            "03" => "марта", 
            "04" => "апреля", 
            "05" => "мая", 
            "06" => "июня", 
            "07" => "июля", 
            "08" => "августа", 
            "09" => "сентября", 
            "10" => "октября", 
            "11" => "ноября", 
            "12" => "декабря"
        );  
	$i=0;
	$uid=0;
	foreach($tmp as $row){
		if($tmp[$i]['iaa_update_author']){
			$uid=$tmp[$i]['iaa_update_author'];
		}else{
			$uid=$tmp[$i]['iaa_author'];
		}
		$sql="SELECT * FROM i_users  WHERE `u_id`='".$uid."'";
		self::$db->query($sql);
		$user = self::$db -> fetch_assoc();
		$str = $tmp[$i]['iaa_text'];
		$tmp[$i]['name'] = $user['u_name'];
		$tmp[$i]['surname'] = $user['u_surname'];
		if(strlen ($str) > 150){
			$tst=substr($str, 0, 150).'...';
			$bb = new bbcode(substr($str, 0, 150).'...');
			$tmp[$i]['preview'] = $bb -> get_html();
		}else{
			$bb = new bbcode($tmp[$i]['iaa_text']);
			$tmp[$i]['preview'] = $bb -> get_html();
		}
        //std_debug($tst);
		$tmp_date= $tmp[$i]['iaa_update_ts'];
		$tmp[$i]['data_update_ru'] = date('d '.$Month_r[date('m',$tmp_date)].'   Y  H:i:s',$tmp_date);;
		$i++; 
        }
	return $tmp;
	}


//---------------------------------------------------------------------------------- Устанавливаю настройки view
public static function set_view($id,$view){
	$sql="UPDATE i_announces_settings SET `s_view_type` = ".$view."  WHERE `s_user_id`='".$id."'";
	self::$db->query($sql);
	self::$db -> fetch_assoc();
	}
//---------------------------------------------------------------------------------- Устанавливаю настройки periodic
public static function set_periodic($id,$periodic){
	//std_debug($periodic);
	$sql="UPDATE i_announces_settings SET `s_periodic` = ".$periodic."  WHERE `s_user_id`='".$id."'";
	self::$db->query($sql);
	self::$db -> fetch_assoc();
	}

//---------------------------------------------------------------------------------- Устанавливаю настройки trash
public static function set_trash($id,$trash){
	$sql="UPDATE i_announces_settings SET `s_trash` = ".$trash."  WHERE `s_user_id`='".$id."'";
	self::$db->query($sql);
	self::$db -> fetch_assoc();
	}


//---------------------------------------------------------------------------------- инфорация о пользователе
public static function get_user_info($id){
	$sql="SELECT iu.u_name,  iu.u_surname, iu.u_is_disabled FROM `i_users` iu
	 WHERE `u_id`='".$id."'";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc();
	return $tmp;
}


//---------------------------------------------------------------------------------- Помечаем номер автора новости
public static function get_creator_id($id){
	$sql="SELECT ia.a_author FROM `i_announces` ia
	 WHERE `a_id`='".$id."'";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc();
	return $tmp["a_author"];
}


//---------------------------------------------------------------------------------- Устанавливаем или снимаем флаг для переработки новости
public static function  set_visa($news, $visa,$iniciator,$current_time){
$sql="UPDATE i_announces SET `a_need_visa`= '".$visa."', `a_who_review`='".$iniciator."', `a_update_ts`='".$current_time."' where `a_id` = '".$news."'";
	self::$db->query($sql);
	self::$db -> fetch_assoc();
}

//---------------------------------------------------------------------------------- Получаем новости которые требуется проверить и изменить перед публикацией
public static function only_need_visa_news($add_req,$search_req){
$sql="SELECT * FROM i_announces ia  
WHERE ia.a_need_visa= '1'  ".$search_req."  group by `a_id` ORDER by a_update_ts DESC ".$add_req;
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();
		return $tmp;

}
//---------------------------------------------------------------------------------- Устанавливаем нового автора новости
public static function  set_new_author($author,$news){
	$sql="UPDATE i_announces SET `a_author`= '".$author."' where `a_id` = '".$news."'";
	self::$db->query($sql);
	self::$db -> fetch_assoc();
}

//---------------------------------------------------------------------------------- Помечаем новость прочитанной
public static function set_reeaded($news_id,$user_id){
	$ts=time();
	$sql="INSERT INTO `i_announce_views`(`v_announce_id`, `v_user_id`, `v_ts`) VALUES ('".$news_id."','".$user_id."','".$ts."')";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc();
}

//---------------------------------------------------------------------------------- Удаляем или восстанавливаем новость
public static function set_disabled($id,$news_id,$news_state){
	$ts=time();
	$sql="UPDATE i_announces SET `a_is_disabled`= '".$news_state."', `a_update_ts`= '".time()."', `a_update_author`='".$id."' where `a_id` = '".$news_id."'";
	self::$db->query($sql);
	self::$db -> fetch_assoc();
}

//---------------------------------------------------------------------------------- Получаем подразделение пользователя
public static function get_instruction($id){
	$sql="SELECT instruction FROM `i_users` iu
	left join i_instructions ii on iu.u_post=ii.p_id
	 WHERE `u_id`='".$id."'";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc();
	//std_debug($tmp);
	return $tmp['instruction'];
}


//----------------------------------------------------------------------------------- Список пользователей обязанных к прочтению
public static function get_user_must_view($news_id){
	$sql="SELECT iu.u_id, iu.u_surname, iu.u_name, iug.ug_group_id, ig.g_group_style, ig.g_name FROM `i_announces_to_groups` iatg 
	left join i_user_groups iug on iug.ug_group_id=iatg.atg_group_id
	left join i_users iu on iu.u_id=iug.ug_user_id 
	left join i_groups ig on ig.g_id=iug.ug_group_id
	where atg_announce_id = ?
	and not iu.u_is_disabled = 1
	and iu.u_id not in (
	select v_user_id from i_announce_views
    where v_announce_id = ?
	)

	";
	//group by iu.u_id ORDER by iug.ug_group_id
	self::$db->query($sql,$news_id,$news_id);
	$tmp = self::$db -> fetch_assoc_all();

	return $tmp;
}

//----------------------------------------------------------------------------------- Список пользователей просмотревших данную новость
public static function get_user_view($news_id){
	$sql="SELECT iu.u_id, iu.u_surname, iu.u_name, iug.ug_group_id, ig.g_group_style, ig.g_name  FROM `i_announce_views` iav
	left join i_users iu on iu.u_id=iav.v_user_id
	left join i_user_groups iug on iug.ug_user_id=iu.u_id
	left join i_groups ig on ig.g_id=iug.ug_group_id
	 WHERE `v_announce_id`= ?
	 and not iu.u_is_disabled = 1

	 ";

	 //group by iu.u_id ORDER by iug.ug_group_id
	self::$db->query($sql,$news_id);
	$tmp = self::$db -> fetch_assoc_all();
	//std_debug($tmp);
	return $tmp;
}

//-------------------------------------------------------------------------------------- Получение списка групп
public static function get_group_list(){
	$sql="SELECT * FROM `i_groups`";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();
	//std_debug($tmp);
	return $tmp;
}

//-------------------------------------------------------------------------------------- Обратная связь
public static function send_feedback($id,$text){
	$sql="INSERT INTO `feedback`(`f_uid`, `f_name`, `f_surname`, `f_resource`, `f_feed`, `f_date`) VALUES ('".$id['u_id']."','".$id['u_name']."','".$id['u_surname']."','bvk','".$text."',NOW())";
	self::$nsp_db->query($sql);
	$tmp = self::$nsp_db -> fetch_assoc();
}


//-------------------------------------------------------------------------------------- Поиск и обновление периодических новостей
public static function update_ts(){
	$sql="SELECT * FROM `i_announces` WHERE `a_is_periodic`='1' and `a_is_disabled`='0'";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();

	$this_day=date("d");
	$this_month=date("m");
	$date = date_create();
	$i=0;
	foreach($tmp as $i=>$row){
		if ($this_month != date("m",$tmp[$i]['a_update_ts'])){
			if ($this_day == date("d",$tmp[$i]['a_timestamp'])){
				$sql="UPDATE `i_announces` SET `a_published`='1', `a_update_ts`= '".date_timestamp_get($date)."',`a_update_author`='2707' WHERE `a_id`='".$tmp[$i]['a_id']."'";
				self::$db->query($sql);
				self::$db -> fetch_assoc();
				self::del_rel_announce_views($tmp[$i]['a_id']);
				}
			}
		$i++; 
    }
    //print_r("DAS_ENDE");

}

public static function random_ts(){
	$sql="SELECT * FROM `i_announces` WHERE `a_is_periodic`='1'";
	self::$db->query($sql);
	$tmp = self::$db -> fetch_assoc_all();

	$this_day=date("d");
	$this_month=date("m");
	$date = date_create();

	$i=0;
	foreach($tmp as $i=>$row){
		$time = rand(1535750148,1538327805); //1 сен 2018 - 30 сен 2018
		//if ($this_month != date("m",$tmp[$i]['a_update_ts'])){
			//if ($this_day == date("d",$tmp[$i]['a_timestamp'])){
				$sql="UPDATE `i_announces` SET `a_published`='1', `a_update_ts`= '".$time."',`a_update_author`='2707' WHERE `a_id`='".$tmp[$i]['a_id']."'";
				self::$db->query($sql);
				self::$db -> fetch_assoc();
				self::del_rel_announce_views($tmp[$i]['a_id']);
				//}
			//}
		$i++; 
    }
    print_r("DAS_ENDE");
}


//-------------------------------------------------------------------------------------- Добавление в архив

 public static function add_archive($data){
        return self::$db->query_insert('i_announces_archive',$data);
    }
//-------------------------------------------------------------------------------------- Обновление новости

 public static function update_announce($data,$id){
        return self::$db->query_update('i_announces','a_id',$id,$data);
    }

//-------------------------------------------------------------------------------------- Удаление записей обновляемой новости

 public static function del_rel_announces_to_groups($id){
        self::$db->query("DELETE FROM `i_announces_to_groups` WHERE `atg_announce_id`='".$id."'");
    }
 public static function del_rel_announce_views($id){
        self::$db->query("DELETE FROM `i_announce_views` WHERE `v_announce_id`='".$id."'");
        self::$db->query("DELETE FROM `i_announces_comments` WHERE `c_aid`='".$id."'");
    }

////////////////////WHAAAAAAAAAAAAAAT?
  public static function get_worktime($year,$month){
		$sql="SELECT * FROM `worktime` WHERE `month`='".$month."' and `year`='".$year."'";
		self::$nsp_db->query($sql);
		$tmp = self::$nsp_db -> fetch_assoc();
        return $tmp;
    }



//--------------------------------------------------- Krab part

    /**
     * get types announces
     * @return array - Types announces
     */
    public static function get_types_announce()
    {
        $sql = "SELECT * FROM info.i_announces_types";
        self::$db->query($sql);

        return self::$db->fetch_assoc_all();
    }


    /**
     * added announce
     * @param $data - array
     * content data:
     *       a_timestamp - date of placement (timestamp)
     *       a_title - article title (text)
     *       a_text - article content (text)
     *       a_author - id user (int)
     *       a_importance - is importance (1 | 0)
     *       a_published - is published (1 | 0), default 1
     *       a_update_ts - date update article (timestamp)
     *       a_expiretime - expire time article (timestamp)
     *       a_is_general - type article (0 - organizational,
     *                                    1 - depression and premiums,
     *                                    2 - events and promotions,
     *                                    3 - discounts or guests)
     *       a_is_periodic - periodic article (1 | 0)
     * @return bool - is added
     */
    public static function add_announce($data){
        return self::$db->query_insert('i_announces',$data);
    }

    /**
     * relations announce
     * @param $id - $id_announce
     * @param $data - array groups
     * @return bool - is added
     */
    public static function add_rel_announces_to_groups($id,$data){

        $sql = /** @lang text */
            "INSERT INTO info.i_announces_to_groups(atg_announce_id, atg_group_id) VALUES ";

        foreach ($data as $datum => $index){
            if ($datum != count($data) - 1 ){
                $sql.= "($id, $index),";
            }
            else{
                $sql.= "($id, $index)";
            }


        }
//std_debug($sql);
        return self::$db->query($sql);
    }


    /**
     * get article by id
     * @param $id
     * @return bool|array - is data article
     */
    public static function get_announce($id){
        $sql = "SELECT 
                    ai.a_id as id,
                    ai.a_title as title,
                    ai.a_text as text,
                    ai.a_author as author,
                    ai.a_importance as importance,
                    ai.a_expiretime as expiretime,
                    ai.a_is_periodic as periodic,
                    ai.a_is_general as a_is_general
                FROM info.i_announces ai WHERE ai.a_id = {$id} ORDER BY ai.a_update_ts LIMIT 1";
        self::$db->query($sql);
        $data = self::$db->fetch_assoc();

        if (!$data){
            return false;
        }
        return $data;
    }

    /**
     * group announces by id
     * @param $id
     * @return array|bool - is data group
     */
    public static function get_group_announces($id){
        $sql = "SELECT 
                    iatg.atg_group_id 
                FROM info.i_announces_to_groups iatg WHERE iatg.atg_announce_id = {$id}";

        self::$db->query($sql);

        $data = self::$db->fetch_assoc_all();

        if (!$data){
            return false;
        }
        return $data;
    }


////////////////////////////////Блок обновления данных самим пользователем///////////////////////////
    //////////////////////////
    //////////////////////////
    //////////////////////////

    public static function update_user_birthday($u_id,$birthday){
    	$sql="UPDATE `i_users` SET `u_st`='".$birthday."' WHERE `u_id`='".$u_id."'";
        self::$db->query($sql);
    }
    public static function update_user_phone($u_id,$phone){
    	$sql="UPDATE `i_users` SET `u_cell`='".$phone."' WHERE `u_id`='".$u_id."'";
        self::$db->query($sql);    	
    }
    public static function update_user_mocard($u_id,$mocard){
    	$sql="UPDATE `i_users` SET `mo_card`='".$mocard."' WHERE `u_id`='".$u_id."'";
        self::$db->query($sql);    	
    }

//=====================INIT===============================

	public static function init(){
	  self :: $db = dba :: get_instance(\cfg::$db_info);
	  self :: $nsp_db = dba :: get_instance(\cfg::$db_nic_small_projects);

	}
}

vkurse::init();

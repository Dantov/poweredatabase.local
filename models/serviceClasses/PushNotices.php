<?php
/**
 * Created by PhpStorm.
 * User: Dant
 * Date: 04.08.2019
 * Time: 0:04
 */

namespace app\models\serviceClasses;

class PushNotices
{
    /*
     * айпишник юзвера
     */
    public $IP_visiter = '';

    /*
     * массив статусов из $this->getStatLabArr('status')
     */
    public $statuses = [];

    public function __construct($IP_visiter, $statuses)
    {
        if ( !empty($IP_visiter) ) $this->IP_visiter = $IP_visiter;
        if ( !empty($statuses) ) $this->statuses = $statuses;
    }

    public function checkPushNotice()
    {
        $result = array();

        $quer = mysqli_query($this->connection, " SELECT * FROM pushnotice " );
        $c = 0;
        while( $pushRows = mysqli_fetch_assoc($quer) ) {

            $ips = explode(';', $pushRows['ip'] );
            // проверка на актуальность этого уведомления
            if ( in_array($this->IP_visiter, $ips) ) continue;

            $result[$c]['date'] = $pushRows['date'];

            $result[$c]['not_id'] = $pushRows['id'];
            $result[$c]['pos_id'] = $pushRows['pos_id'];
            $result[$c]['number_3d'] = $pushRows['number_3d'];
            $result[$c]['addEdit'] = $pushRows['addedit'];
            $result[$c]['fio'] = $pushRows['name'];
            $result[$c]['img_src'] = false;
            $result[$c]['status'] = false;

            if ( (int)$pushRows['addedit'] !== 3 ) {

                $id = $pushRows['pos_id'];

                $imgQuer = mysqli_query($this->connection, " SELECT img_name FROM images WHERE main='1' AND pos_id='$id' " );
                $pushImg = mysqli_fetch_assoc($imgQuer);
                $arr = explode(' / ',$pushRows['number_3d']);
                $n3d = $arr[0];

                $file = $n3d.'/'.$id.'/images/'.$pushImg['img_name'];
                $fileImg = _stockDIR_HTTP_.$n3d.'/'.$id.'/images/'.$pushImg['img_name'];
                if ( !file_exists(_stockDIR_.$file) ) $fileImg = _stockDIR_HTTP_."default.jpg";

                $result[$c]['img_src'] = $fileImg;

                $statQuer = mysqli_query($this->connection, " SELECT status FROM stock WHERE id='$id' " );
                $stat = mysqli_fetch_assoc($statQuer);

                $statuses = $this->getStatLabArr('status');

                for( $i = 0; $i < count($statuses); $i++ ) {
                    if ( $statuses[$i]['id'] == $stat['status'] ) $result[$c]['status'] = $statuses[$i];
                }
            }

            $c++;
        }
        if ( !count($result) ) return false;

        return $result;

    }
    public function addIPtoNotice($id) {

        // $querIP = mysqli_query($this->connection, " SELECT ip FROM pushnotice WHERE id='$id' " );
        // $iprow = mysqli_fetch_assoc($querIP);
        // $newIprow = $iprow['ip'].$this->IP_visiter.';';

        $newIprow = $this->IP_visiter.';';

        $addIPShowed = mysqli_query($this->connection, " UPDATE pushnotice SET ip=CONCAT(ip,'$newIprow') WHERE id='$id' ");
        if (!$addIPShowed) {
            printf( "Error_addIPtoNotice: %s\n", mysqli_error($this->connection) );
            return false;
        }
        return $this->IP_visiter;
    }

    public function addIPtoALLNotices($not_id) {


        $where = "WHERE ";
        for( $i = 0; $i < count($not_id); $i++ ) {
            $where .= "id='{$not_id[$i]}' OR ";
        }
        $where = trim($where);
        $where = substr($where, 0, -2);

        // $querIP = mysqli_query($this->connection, " SELECT id,ip FROM pushnotice $where" );
        // //$newIprow = $iprow['ip'].$this->IP_visiter.';';
        // while ( $iprow = mysqli_fetch_assoc($querIP) ) {
        // $newIprow = $iprow['ip'].$this->IP_visiter.';';
        // }
        $newIprow = $this->IP_visiter.';';
        //CONCAT соединяет строки
        //REPLACE - заменяет в строкке
        $query = " UPDATE pushnotice SET ip=CONCAT(ip,'$newIprow') $where ";

        $addIPS = mysqli_query($this->connection, $query);
        /*
        while ( $iprow = mysqli_fetch_assoc($querIP) ) {
            $newIprow = $iprow['ip'].$this->IP_visiter.';';
            $id = $iprow['id'];
            $addIPS = mysqli_query($this->connection, " UPDATE pushnotice SET ip='$newIprow' WHERE id='$id' ");
        }
        */
        if ( $addIPS ) return true;
        return false;
    }

    public function clearOldNotices() { // удаляем записи которым больше 2х дней
        $date = new DateTime('-2 days');
        $formdate = $date->format('Y-m-d');
        $Quer = mysqli_query($this->connection, " DELETE FROM pushnotice WHERE date<'$formdate' " );
    }

}
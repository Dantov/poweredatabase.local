<?php

namespace app\models\serviceTables;
use yii\db\ActiveRecord;

class Huf_images extends ActiveRecord
{

    const SCENARIO_ADD = 'add';
    const SCENARIO_EDIT = 'edit';


    public static function tableName()
    {
        return "huf_images";
    }

    public function getStock()
    {
        return $this->hasOne(Huf_stock::className(),['id'=>'pos_id']);
    }
    /*
    public function scenarios()
    {
        $columns = [
            'name',
            'status',
            'size',
            'pos_id',
        ];

        return [
            self::SCENARIO_ADD => $columns,
            self::SCENARIO_EDIT => $columns,
        ];
    }
    */

}
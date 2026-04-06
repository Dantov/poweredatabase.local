<?php

namespace app\models\serviceTables;
use yii\db\ActiveRecord;
use Yii;

class Huf_stock extends ActiveRecord
{

    const SCENARIO_ADD = 'add';
    const SCENARIO_EDIT = 'edit';
    const SCENARIO_CLONE = 'clone';

    public function getHuf_images()
    {
        return $this->hasMany(Huf_images::className(),['pos_id'=>'id']);
    }
    public function getHuf_materials()
    {
        return $this->hasMany(Huf_materials::className(),['pos_id'=>'id']);
    }
    public function getHuf_gems()
    {
        return $this->hasMany(Huf_gems::className(),['pos_id'=>'id']);
    }
    public function getHuf_stlfiles()
    {
        return $this->hasMany(Huf_stlfiles::className(),['pos_id'=>'id']);
    }
    public function getHuf_rhinofiles()
    {
        return $this->hasMany(Huf_rhinofiles::className(),['pos_id'=>'id']);
    }

    /*
    public function scenarios()
    {
        $columns = [
            'id',
            'number_3d',
            'client',
            'modeller3d',
            'model_type',
            'size_range',
            'print_cost',
            'model_cost',
            'model_weight',
            'description',
            'hashtags',
            'model_status',
            'date',
            'create_date',
            'creator_id',
        ];
        return [
            self::SCENARIO_ADD => $columns,
            self::SCENARIO_EDIT => $columns,
            self::SCENARIO_CLONE => $columns,
        ];
    }
    */

}
<?php


namespace app\models\serviceTables;


use yii\db\ActiveRecord;

class Users extends ActiveRecord
{

	public function rules()
    {
        return [
            [
                [
                    'login',
                    'pass',
                    'name',
                    'firstname',
                    'lastname',
                    'thirdname',
                    'fio',
                    'fullFio',
                    'role',
                    'clients',
                    'permissions',
                    'locations',
                    'email',
                    'about',
                    'access',
                ],
                'trim',
            ],
        ];
    }
}
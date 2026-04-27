<?php
namespace app\models;

use app\models\serviceTables\{Service_data,Stock};
use app\models\User;

use Yii;
use yii\helpers\Url;
use yii\db\ActiveQuery;

class Stats extends Common
{
    //protected array $gems;

    public function getModelTypes() : array
    {
         return Service_data::find()->where(['tab' => 'model_type'])->asArray()->all();
    }

    public function pricesTotal() : int
    {
        return Stock::find()->sum('model_cost');    
    }

    public function pricesByYear( int $year ) : int
    {
        if ( $year < 1970 ) $year = 1970;
        if ( $year > date("Y") ) $year = date("Y");

        $session = Yii::$app->session;
        $statPrYear = $session->set('stat_prices_year',$year);

        return (int)Stock::find()
            ->where(['>=','create_date',"$year-01-01"])
            ->andWhere(['<','create_date',"$year-12-31"])
            ->sum('model_cost');
    }

    public function pricesByMonth( string $month, string $year = "" ) : int
    {
        if ( empty($year) ) 
        {
            $year = date("Y");
            $session = Yii::$app->session;
            if ( $session->has('stat_prices_year') )
                $year = $session->get('stat_prices_year');
        }

        if ( $month <= 0 ) $month = 1;
        $nextMonth = (int)$month + 1;
        $lastDay = "01";

        if ( $month < 9 ) $month = "0" . $month;
        if ( $month > 12 ) $month = "12";

        if ( $nextMonth < 9 ) $nextMonth = "0" . $nextMonth;
        if ( $nextMonth >= 12 ) {
            $nextMonth = "12";
            $lastDay = "31";
        }

        return (int)Stock::find()
            ->where(['>=','create_date',"$year-$month-01"])
            ->andWhere(['<','create_date',"$year-$month-$lastDay"])
            ->sum('model_cost');
    }

    public function pricesByDate( string $dateFrom, string $dateTo ) : int
    {
        $v = new Validator();
        if ( $v->validateDate($dateFrom) && $v->validateDate($dateTo) )
        {
            //$this->stockQuery->andFilterWhere(['>=', 'create_date',$fromDate]);
            return (int)Stock::find()
                //->andFilterWhere(['between', 'date', $dateFrom, $dateTo])
                ->where(['>=','create_date',$dateFrom])
                ->andWhere(['<','create_date',$dateTo])
                ->sum('model_cost');    
        } 
        return 0;
    }

    public function getStockData() : array
    {
        $session = Yii::$app->session;

        $this->startStockQuery();
       
        $this->addByClient();
        if ( $session->has('searchFor') ) $this->addSearch();
        if ( $session->has('selectByHashtag') ) $this->addByHashtag();
        $this->addFromDate();
        $this->addToDate();
        $this->addOrderBy();

        $this->stockQuery->with(['images']);

        $this->stock = $this->pagination();

        $this->setMainImgforStock();

        return $this->stock;
    }


    protected function setMainImgforStock()
    {
        foreach ( $this->stock as &$model )
        {
            $randomimg = '';
            $found = false;
            foreach ( $model['images'] as $image )
            {
                if ( $image['status'] === 1 ) {
                    $model['mainimage'] = $image['name'];
                    $found = true;
                    break;
                }
            }

            if ( !$found )
            {
                $randomimg = $model['images'][ random_int( 0, (count( $model['images']))-1) ];
                $model['mainimage'] = $randomimg['name'];
            }
        }
    }

    public function pagination() : array
    {
        if ( !$this->stockQuery->exists() ) return [];

        $session = Yii::$app->session;
        $maxPos = $session->get('positionsCount');
        //$maxPos = 5;

        $this->countPos = $this->stockQuery->count();
        $pages = new Pagination(['totalCount' => $this->countPos,'pageSize' => $maxPos]);
        $models = $this->stockQuery->asArray()->offset($pages->offset)->limit($pages->limit)->all();
        $this->pages = $pages;

        return $models;
    }
}

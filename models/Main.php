<?php
namespace app\models;

use app\models\serviceTables\Stock;
use app\models\{User,Files};

use Yii;
use yii\helpers\Url;
use yii\db\ActiveQuery;
use yii\data\Pagination;

class Main extends Common
{ 
    public array $stock = []; 
    public int $countPos = 0;
    public Pagination $pages;

    protected ActiveQuery $stockQuery;

    protected function startStockQuery()
    {
        $this->stockQuery = Stock::find()->where(['model_status' => 1]);
    }

    protected function addByClient()
    {
        $session = Yii::$app->session;
        $clients =[];
        foreach ( self::$clients as $cl )
            $clients[] = $cl['name'];

        if ( User::hasPermission('clientonly') && !User::hasPermission('clientall') ) {
            return $this->stockQuery->andWhere(['in', 'client', $clients]);
        }

        if ( $session->get('SelectByClient') !== 'Все' )
            return $this->stockQuery->andWhere(['client' => $session->get('SelectByClient') ]);
    }
    
    protected function addSearch()
    {
        $session = Yii::$app->session;
        $searchFor = $session->get('searchFor');
        if ( empty($searchFor) ) return;

        $this->stockQuery
            ->andWhere('number_3d LIKE :search OR client LIKE :search OR modeller3d LIKE :search OR model_type LIKE :search OR description LIKE :search OR hashtags LIKE :search')
            ->addParams([':search' => "%$searchFor%"]);
    }

    protected function addModelType()
    {
        $session = Yii::$app->session;
        $selectByModelType = $session->get('selectByModelType');
        if ( empty($selectByModelType) ) return;

            $this->stockQuery
                ->andWhere('model_type LIKE :modeltype')
                ->addParams([':modeltype' => "%$selectByModelType%"]);
    }

    protected function addByHashtag()
    {
        $session = Yii::$app->session;
        $selectByHashtag = $session->get('selectByHashtag');
        if ( empty($selectByHashtag) ) return;

            $this->stockQuery
                ->andWhere('hashtags LIKE :hashtag')
                ->addParams([':hashtag' => "%$selectByHashtag%"]);
    }
    protected function addByHashtags()
    {
        $session = Yii::$app->session;
        $hashtags = $session->get('selectByHashtags');
        if ( empty($hashtags) ) return;
        $str = '';
        foreach ( $hashtags as $htag )
        {
            $str.= "hashtags LIKE '%$htag%' OR ";
        }
        $str = trim($str,' OR ');
        $this->stockQuery->andWhere($str);
                //->andWhere('hashtags LIKE :hashtag');
                //->addParams([':hashtag' => "%$htag%"]);
        //debug($str,'$$str',1);
    }

    protected function addFromDate()
    {
        //->andFilterWhere(['between', 'date', $this->start_date, $this->end_date]);
        $session = Yii::$app->session;
        $fromDate = $session->get('selectFromDate');
        if ( empty($fromDate) ) return;

        $this->stockQuery->andFilterWhere(['>=', 'create_date',$fromDate]);
    }
    protected function addToDate()
    {
        $session = Yii::$app->session;
        $toDate = $session->get('selectToDate');
        if ( empty($toDate) ) return;

        $this->stockQuery->andFilterWhere(['<=', 'create_date',$toDate]);
    }

    protected function addMaterials()
    {
        $session = Yii::$app->session;
        
        $mat[] = $matcolor = $session->get('selectByMatColor');
        $mat[] = $matName  = $session->get('selectByMatMetal');
        $mat[] = $matProbe = $session->get('selectByMatProbe');
        $go = false;
        foreach( $mat as $v ) {
            if ( !empty($v) ) {
                $this->stockQuery->joinWith('materials');
                $go = true;
                break;
            }
        }

        if ( !$go ) return;

        if ( !empty($matcolor) ) 
            $this->stockQuery->andFilterWhere(['=','materials.color',$matcolor]);
        if ( !empty($matName) ) 
            $this->stockQuery->andFilterWhere(['=','materials.metal',$matName]);
        if ( !empty($matProbe) ) 
            $this->stockQuery->andFilterWhere(['=','materials.probe',$matProbe]);
    }

    protected function addOrderBy()
    {
        $session = Yii::$app->session;
        $orderBy = $session->get('selectByOrder');
        if ( empty($orderBy) ) return;
        $ColName = 'date'; // for adding date by default 

        if ($session->get('selectFromDate') || $session->get('selectToDate') )
            $ColName = 'create_date';

        //SORT_DESC
        $this->stockQuery->orderBy([$ColName => $orderBy]);
    }

    public function getStockData() : array
    {
        $session = Yii::$app->session;

        $this->startStockQuery();
       
        $this->addByClient();
        if ( $session->has('searchFor') ) $this->addSearch();
        $this->addByHashtags();
        $this->addModelType();
        $this->addFromDate();
        $this->addToDate();
        $this->addMaterials();
        $this->addOrderBy();

        $this->stockQuery->with(['images']);

        $this->stock = $this->pagination();

        $this->setMainImgforStock();
        if ( User::hasPermission('hideclients') )
            $this->hideClientsName();

        foreach ($this->stock as &$model)
            $model['isEditBtn'] = $this->drawEditBtn( $model['creator_id'] );

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
            if ( $prevImgName = $this->addPreviewImages( $model['mainimage'], $model['id'] ) )
                $model['mainimgprev'] = $prevImgName;
        }
    }
    protected function addPreviewImages( $mainimgname, $id ) : string
    {
        $files = Files::instance();
        $prevSuff = '_prev';
        
        $imgname = $files->getFileName($mainimgname);
        $imgExt = $files->getExtension($mainimgname);
        $previmg = $imgname.$prevSuff.".".$imgExt;
        $path = _stockDIR_ . $id . "/images/".$previmg;
        if ( file_exists($path) ){
            return $previmg;
        }
        return "";
    }

    protected function hideClientsName()
    {
        $allClients = $this->getClients();
        foreach ( $this->stock as &$model )
        {
            foreach ( $allClients as $clientTmpl )
            {
                if ( $model['client'] == $clientTmpl['name'] ){
                    $model['client'] = $clientTmpl['secondname'];
                    break;
                }
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

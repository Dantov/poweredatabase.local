<?php
namespace app\controllers;

use Yii;

class SearchController extends GeneralController
{

    public function actionSet()
    {
        $request = Yii::$app->request;
        if ( $request->isAjax && $request->isPost )
        {
            $searchFor = mb_strtolower( trim(strip_tags($request->post('search_for'))) );
            $session = Yii::$app->session;
            $session->set('searchFor', $searchFor);
            exit(json_encode(true));
        }
        exit(json_encode(false));
    }
    public function actionPurge()
    {
        $request = Yii::$app->request;
        if ( $request->isAjax && $request->isPost )
        {
            if ( (int)$request->post('clean') !== 1 ) exit(json_encode(false));

            $session = Yii::$app->session;

            $session->set('SelectByClient','Все');
            $session->set('searchFor', '');
            $session->set('selectByHashtag', '');
            $session->set('selectByModelType', '');

            $session->set('selectByMatColor', '');
            $session->set('selectByMatMetal', '');
            $session->set('selectByMatProbe', '');

            $session->set('selectFromDate','');
            $session->set('selectToDate','');
            $session->set('selectByOrder', SORT_ASC);
            
            exit(json_encode(true));
        }
        exit(json_encode(false));
    }

    /**
     *
     * @return string
     */
    public function actionSelectBy()
    {
        $request = Yii::$app->request;

        $client = $request->get('client');
        $hashtag = $request->get('hashtag');
        $modeltype = $request->get('modeltype');

        $matpurge = $request->get('materials');

        $matcolor = $request->get('matcolor');
        $matprobe = $request->get('matprobe');
        $matname = $request->get('matname');

        $purgedate = $request->get('purgedate');
        $order = $request->get('order');

        if ( $matpurge ) $this->materialsPurge();
        if ( $client ) $this->SelectByClient( $client );
        //if ( $hashtag )  $this->SelectBy( $hashtag, 'selectByHashtag', $this->hashtags );
        if ( $hashtag )  $this->SelectByHashtags( $hashtag );
        if ( $modeltype )  $this->SelectBy( $modeltype,'selectByModelType', $this->modelTypes );
        if ( $matcolor )  $this->SelectBy( $matcolor, 'selectByMatColor', $this->modelMaterials['metal_color'] );
        if ( $matprobe )  $this->SelectBy( $matprobe, 'selectByMatProbe', $this->modelMaterials['metal_probe'] );
        if ( $matname )  $this->SelectBy( $matname, 'selectByMatMetal', $this->modelMaterials['model_material'] );

        if ( $purgedate )  $this->purgeDate();
        if ( $order )  $this->orderBy($order);
        
        Yii::$app->response->redirect(['/site'])->send();
    }
    protected function SelectBy( string $needle, string $sessName, array $heap )
    {
        $session = Yii::$app->session;
        if ( (int)$needle === 123 )
        {
            $session->set($sessName, '');
            return;
        }
        foreach ( $heap as $row )
        {
            if ( $row['name'] === $needle )
            {
                $session->set($sessName, $row['name']);
                break;
            }
        }
    }
    protected function materialsPurge()
    {
        $session = Yii::$app->session;
        $session->set('selectByMatColor', '');
        $session->set('selectByMatMetal', '');
        $session->set('selectByMatProbe', '');
    }
    protected function SelectByMaterialColor( string $mcolor )
    {
        $session = Yii::$app->session;
        if ( (int)$mcolor === 123 )
        {
            $session->set('selectByMatColor', '');
            return;
        }
        foreach ( $this->modelMaterials['metal_color'] as $color )
        {
            if ( $color['name'] === $mcolor )
            {
                $session->set('selectByMatColor', $color['name']);
                break;
            }
        }
    }

    protected function SelectByClient( string $client )
    {
        $session = Yii::$app->session;
        if ( (int)$client === 11 )
        {
            $session->set('SelectByClient', 'Все');
            return;
        }
        foreach ( $this->clients as $singleClient )
        {
            
            if ( $singleClient['name'] === $client )
            {
                $session->set('SelectByClient', $singleClient['name']);
                break;
            }
        }
    }
    protected function SelectByModelType( string $modeltype )
    {
        $session = Yii::$app->session;
        if ( (int)$modeltype === 123 )
        {
            $session->set('selectByModelType', '');
            return;
        }

        foreach ( $this->modelTypes as $singleMT )
        {
            if ( $singleMT['name'] === $modeltype )
            {
                $session->set('selectByModelType', $singleMT['name']);
                break;
            }
        }
    }
    protected function SelectByHashtags( string $hashtag )
    {
        $session = Yii::$app->session;
        if ( (int)$hashtag === 123 )
        {
            $session->set('selectByHashtags', []);
            return;
        }

        $hashtags = $session->get('selectByHashtags');
        foreach ( $this->hashtags as $singleHashtag )
        {
            if ( $singleHashtag['name'] === $hashtag )
            {
                if ( in_array($hashtag, $hashtags) ) {
                    //Delete Htag if already in query
                    foreach ( $hashtags as $key => $htag ) {
                        if ( $htag == $hashtag ) {
                            unset($hashtags[$key]);
                            break;
                        }
                    }
                    
                } else {
                    // Add Htag for query
                    $hashtags[] = $hashtag;
                }
                break;
            }
        }
        $session->set('selectByHashtags', $hashtags);
    }

    protected function orderBy( string $order )
    {
        $session = Yii::$app->session;
        switch ( $order )
        {
            case 'ASC':
                $session->set('selectByOrder', SORT_ASC);
            break;
            case 'DESC':
                $session->set('selectByOrder', SORT_DESC);
            break;
        }
    }

    public function actionFromDate()
    {
        $request = Yii::$app->request;
        if ( $request->isAjax && $request->isPost )
        {
            $session = Yii::$app->session;

            $date = $request->post('date');
            if (empty( $date )) exit(json_encode(false));

            $session->set('selectFromDate', $date);
            exit(json_encode(true));
        }
        exit(json_encode(false));
    }
    public function actionToDate()
    {
        $request = Yii::$app->request;
        if ( $request->isAjax && $request->isPost )
        {
            $session = Yii::$app->session;

            $date = $request->post('date');
            if (empty( $date )) exit(json_encode(false));

            $session->set('selectToDate', $date);
            exit(json_encode(true));
        }
        exit(json_encode(false));
    }
    protected function purgeDate()
    {
        $session = Yii::$app->session;
        if (!empty($session->get('selectToDate')))
                $session->set('selectToDate','');

        if (!empty($session->get('selectFromDate')))
                $session->set('selectFromDate','');
    }
    public function actionPositionsCount()
    {
        $request = Yii::$app->request;
        $get = (int)$request->get('v');
        if ( $get < 1 || $get > PHP_INT_MAX ) 
            return Yii::$app->response->redirect(['/site'])->send();

        $session = Yii::$app->session;
        switch ( $get )
        {
            case 27:
                $session->set('positionsCount', 27);
            break;
            case 54:
                $session->set('positionsCount', 54);
            break;
            case 108:
                $session->set('positionsCount', 108);
            break;
            case 216:
                $session->set('positionsCount', 216);
            break;
            default:
                $session->set('positionsCount', 27);
            break;
        }
        Yii::$app->response->redirect(['/site'])->send();
    }

    public function actionControlSize()
    {
        $request = Yii::$app->request;
        if ( $request->isAjax && $request->isPost )
        {
            $session = Yii::$app->session;
            $size = $request->post('size');
            if ( $size < 6 ) $size = 6;
            if ( $size > 24 ) $size = 24;
            $session->set('tilesControlSize', $size);
            exit(json_encode(['size'=>$size, 'done'=>true]));
        }
        exit(json_encode(false));
    }

}

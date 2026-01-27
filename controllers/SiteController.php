<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\{Main,User,Nom};
use app\models\serviceClasses\{SaveModel,AddEdit,ModelView,JewelStore};

class SiteController extends GeneralController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays Base Page.
     *
     * @return string
     */
    public function actionIndex()
    {
        $session = Yii::$app->session;
        $session->set('sitepage','main');
		
        $main = new Main();
        $stock = $main->getStockData();
        //debug($stock,'$stock',1);
        $pages = $main->pages??null;

        $compact = compact(['session','stock','main','pages']);
        return $this->render('index',$compact);
    }

    public function actionView()
    {
        $session = Yii::$app->session;
        $session->set('sitepage','view');

        $request = Yii::$app->request;
        $modelID = (int)$request->get('id');
        if ( $modelID < 0 || $modelID > PHP_INT_MAX )
            Yii::$app->response->redirect(['/site/']);

        $mv = new ModelView( $modelID );
        $model = $mv->getStockData();

        $comp = compact(['model','modelID','mv']);
        return $this->render('view',$comp);
    }

    /**
     * Displays View Page.
     *
     * @return string
     */
    public function actionAdd()
    {   
        $response = Yii::$app->response;
        $session = Yii::$app->session;
        $session->set('sitepage','add-edit');

        $modelID = Yii::$app->request->get('id');
        if ( !$modelID )
        {
             if ( !User::hasPermission('add_model'))
                return $response->redirect(['/site'])->send();

            $sm = new SaveModel();
            $sm->addNewModel();    
            $modelID = $sm->modelID;

            return $response->redirect(['/site/add/','id'=>$modelID])->send();
        }

        $addEdit = new AddEdit($modelID);
        $sevData = $addEdit->getDataTables();
        $stockData = $addEdit->getStockData();
        $datafileSizes = $addEdit->datafileSizes;

        if ( !$addEdit->accessControl('edit') )
            return $response->redirect(['/site'])->send();

        $addEdit->setHashtagsActiv($stockData['hashtags'], $sevData['hashtag']);

        $comp = compact(['modelID','sevData','stockData','datafileSizes']);
        return $this->render('add',$comp);
    }

    /**
     *
     * @return string
     */
    public function actionEdit()
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        if ( $request->isAjax && $request->isPost )
        {
            $session->set('sitepage','edit');

            $post = $request->post();
            if ( !$post['modelID'] ) die;

            $modelID = (int)$post['modelID'];
            $sm = new SaveModel( $modelID );

            //leave this place if no permission to edit
            if ( !$sm->accessControl() ) exit(json_encode('not enough rights'));

            //Leave this place if model is deleted
            if ( !$sm->isEditable() ) exit(json_encode(false));

            //debug($request->get(),'GET',1);
            //debug($request->get('imgfiles'),'imgfiles',1);
            //debug($post,1,1);
            //debug($request->get('dellrow'),1,1);

            //*** FILES ***//
            if ( $request->get('pushfiles') )
                exit(json_encode( $sm->addNewFile( $modelID )));
            if ( $request->get('dellFile') )
                exit(json_encode( $sm->dellFile( $post )));
            if ( $request->get('setMainImg') )
                exit(json_encode( $sm->setMainImg( $post['imgRowID'] )));

            if ( $request->get('linktable') )
                exit(json_encode( $sm->addNewLinkedRow( $request->get('linktable') )));

            if ( $request->get('dellrow') )
                exit(json_encode( $sm->dellRowLinked($post) ));

            if ( $request->get('duplicate') )
                exit(json_encode( $sm->duplicateRowLinked($post) ));

            if ( $request->get('publish') )
                exit(json_encode( $sm->publishModel() ));
            if ( $request->get('exclude') )
                exit(json_encode( $sm->excludeModel() ));
            if ( $request->get('deletemodel') )
                exit(json_encode( $sm->deleteModel() ));

            if ( isset($post['tableName'])  &&  (!empty($post['tableName'])) )
                exit(json_encode( $sm->editLinkedRow($post) ));

            if ( $post['name'] === 'hashtags' && isset($post['dell']) )
                exit(json_encode( $sm->deleteHashtags($modelID,$post) ));

            if ( $post['name'] === 'hashtags' && isset($post['hashtagByText']) )
                exit(json_encode( $sm->hashtagByText($modelID,$post) ));

            if ( $post['name'] === 'hashtags' )
                exit(json_encode( $sm->hashtagByClick($modelID,$post) ));
            
            $res = $sm->editInputs($post['modelID'], $post);
            exit(json_encode($res));
        }
        
        exit(json_encode(false));
    }

    public function actionJewel()
    {
        $request = Yii::$app->request;
        $response = Yii::$app->response;

        $proceed = ($request->isAjax && $request->isPost);
        $box = (string)$request->get('box');
        $jewelbox = new JewelStore( $request->post() );

        switch($box)
        {
            case "add":
                if ( !$jewelbox->accessControl() ) 
                    exit(json_encode("false 123"));

                if ( !$proceed ) exit(json_encode(false));
                exit(json_encode($jewelbox->add()));
            break;
            case "show":
                if ( !$jewelbox->accessControl() ) 
                    $response->redirect(['/site/error/','message'=>"forbidden"])->send();

                $storedModels = $jewelbox->getStoredModels();
                $comp = compact(['storedModels']);
                return $this->render('jewelbox',$comp);
            break;
            case "edit":
                if ( !$jewelbox->accessControl() ) exit(json_encode("false 123"));
                if ( !$proceed ) exit(json_encode(false));

                exit(json_encode( $jewelbox->edit() ));
            break;
            case "remove":
                if ( !$jewelbox->accessControl() ) 
                    $response->redirect(['/site/error/','message'=>"forbidden"])->send();

                $jewelbox->remove($request->get('id')); 
                $response->redirect(['/site/jewel/','box'=>'show'])->send();
            break;
        }

        $response->redirect(['site/'])->send();
    }

    /**
     * Displays Nomenclature page.
     *
     * @return Response|string
     */
    public function actionNomenclature()
    {
        if (!User::hasPermission(35)) 
            Yii::$app->response->redirect('/site')->send();

        $nom = new Nom();
        $modelTypes = $nom->getModelTypes();
        $gemsNames = $nom->getGems('names');
        $gemsColors = $nom->getGems('color');
        $gemsCuts = $nom->getGems('cut');
        $gemsSizes = $nom->getGems('sizes');

        $comp = compact(['modelTypes','gemsNames','gemsColors','gemsCuts','gemsSizes']);
        return $this->render('nomenclature',$comp);
    }
    /**
     * Displays user profile page.
     *
     * @return Response|string
     */
    public function actionProfile()
    {
        if (!User::hasPermission(70)) 
            Yii::$app->response->redirect('/site')->send();


        return $this->render('profile');
    }
    /**
     * Displays user options page.
     *
     * @return Response|string
     */
    public function actionOptions()
    {
        if (!User::hasPermission(69)) 
            Yii::$app->response->redirect('/site')->send();


        return $this->render('options');
    }
    /**
     * Displays user statistic page.
     *
     * @return Response|string
     */
    public function actionStatistic()
    {
        if (!User::hasPermission(36)) 
            Yii::$app->response->redirect('/site/error?id=frule')->send();


        return $this->render('statistic');
    }

    /**
     * Displays user statistic page.
     *
     * @return Response|string
     */
    public function actionError()
    {
        $get = Yii::$app->request->get();
        
        $comp = compact(['get']);
        return $this->render('error',$comp);
    }






    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }
    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}

<?php
namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use app\models\User;
use app\models\serviceClasses\{UsersAll,Crypt};

class UsersController extends GeneralController
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
    public function actionShowAll()
    {
        $users = new UsersAll();

        $all = $users->getAllUsers();
        $compact = compact(['all']);
        return $this->render('showall',$compact);
    }
    public function actionAdd()
    {
        $users = new UsersAll();

        debug('im here',1,1);

        $all = $users->getAllUsers();
        $compact = compact(['all']);
        return $this->render('add',$compact);
    }

    public function actionEdit( string $id )
    {
        $session = Yii::$app->session;
        $session->set('sitepage','edituser');
        //debug($id, 1,1);
        $id = (int)Crypt::strDecode($id);
        $users = new UsersAll($id);

        //if ( !$users->accessControl() ) 
            //throw new \Exception('you have no rights!',500);

        $all = $users->getAllUsers();
        $single = $users->user;
        $clients = $users->getClients();
        $permissions = $users->getAllPermissions();
        $uPermissions = User::permissions();

        $compact = compact(['all','single','clients','permissions','uPermissions']);
        return $this->render('edit',$compact);
    }

    public function actionEditUser()
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $response = Yii::$app->response;

        $post = $request->post();
        $id = Crypt::strDecode($post['uid']);

        $users = new UsersAll($id);
        //if (!$users->accessControl()) exit( json_encode("no permission to edit") ); 


        // for update user permittion and clients
        if ( $request->isAjax && $request->isPost )
        {
            if ( $request->get('applyright') )
                exit( json_encode($users->applyRight($post)) );
            if ( $request->get('removeright') )
                exit( json_encode($users->removeRight($post)) );
        }

        // for rest user data
        if ( $request->isPost )
        {
            //if (!$users->accessControl()) 
            //    throw new Exception("no permission to edit",500);

            if ( $users->saveUserData( $post ) )
            {
                $struid = Crypt::strEncode($id);
                return $response->redirect(['/users/edit', 'id' => $struid]); 
            }
            //debug($id,'id');
            //debug($post,'post',1);

        }
    }


}

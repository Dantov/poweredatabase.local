<?php

namespace app\controllers;

use app\models\serviceTables\Users;
use app\models\Main;
use app\models\Auth;
use app\models\User;

use Yii;
use yii\web\Controller;
use yii\helpers\Url;

class GeneralController extends Controller
{
    public $layout = 'modernize';

    public bool $isMobile = false;
    public bool $isDesktop = false;
    public string $jsCONSTANTS = "";

    /*
     * текущий ир юзвера
     */
    public $IP_visiter = '';

    /*
     * выборка юзверов из БД
     */
    protected $rootDir;
    protected $stockDir;

    public array $user;
    public array $clients = [];
    public array $hashtags = [];
    public array $nonPublished = [];
    public $status_arr = [];
    public $labels_arr = [];
    public $img_arr = [];

    public function beforeAction($action)
    {
        if ( !$this->accessControl() ) 
		{
			//debug('accessControl',1,1);
			return $this->redirect(['/auth'])->send();
		}

        //if ( empty( $this->user ) ) $this->user = Yii::$app->session['user'];
        $this->IP_visiter = $_SERVER['SERVER_ADDR'];

        $this->isMobile  = $this->isMobileCheck();
        $this->isDesktop = !$this->isMobile;
        
        $isMb = $this->isMobile ? 'true' : 'false';
        $this->jsCONSTANTS = <<<JS
            const _IS_MOBILE_  = {$isMb};
            const _IS_DESKTOP_ = !_IS_MOBILE_;
JS;

        $m = new Main();
        $this->clients = $m->getClients();
        $this->hashtags = $m->getAllHashtags();
        $this->nonPublished = $m->getNonPublished();

        return parent::beforeAction($action);
    }

    protected function accessControl() : bool
    {
        $auth = new Auth();
        return $auth->accessControl();
    }

    /*
     * Удаление не нужных сессий
     * и файлов
     */
    public function unsetData($path=false)
    {
        $session = Yii::$app->session;
        // удаляем автозаполнение при возврате на главную
        if ( $session->has('general_data') ) $session->remove('general_data');

        //удаляем инфу из ворд файла и сами файлы
        if ( $session->has('fromWord_data') )
        {
            //rrmdir( _rootDIR_ .$session['fromWord_data']['tempDirName']);
            $session->remove('fromWord_data');
        }
        //сессия id пдф прогресс бара
        if ( $session->has('id_progr') ) $session->remove('id_progr');
    }

    public function isMobileCheck()
    {
        $ua = '';
        if (filter_has_var(INPUT_SERVER,'HTTP_USER_AGENT'))
            $ua = filter_input(INPUT_SERVER,'HTTP_USER_AGENT');
        
        //$ua = $_SERVER['HTTP_USER_AGENT']??" ";
        return stripos($ua,'mobile') !== false ? true : false;
    }

}
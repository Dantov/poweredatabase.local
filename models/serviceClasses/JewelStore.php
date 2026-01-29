<?php
namespace app\models\serviceClasses;

use app\models\serviceTables\{Stock,Service_data,Jewelbox};
use app\models\{Common,Files,User,Validator};

use Yii;
use yii\helpers\Url;

class JewelStore extends Common
{ 
    protected int $modelID;
    protected string $modelComment;
    protected int $orderID;

	public function __construct( array $post )
    {
        $v = new Validator();
        if ( isset($post['modelID']) ) {
            $id = (int)$post['modelID'];

            if ( $id < 1 || $id > PHP_INT_MAX ) return false;
            $this->modelID = $id;
        }

        if ( isset($post['comment']) )
            $this->modelComment = trim( $v->sanitarizePost('comment') );

        if ( isset($post['orderid']) ) {
            $orderid = (int)$post['orderid'];
            if ( $orderid < 1 || $orderid > PHP_INT_MAX ) return false;
            $this->orderID = $orderid;
        }

        parent::__construct();
	}

    public static function getModelsCount() : int
    {
        $jb = Jewelbox::find()->where(['userid'=>User::getID()])->andWhere(['status'=>0]);
        if ($jb->exists()) {
            $jb =$jb->one();
            return count(json_decode($jb->storedmodels,true));
        } 
        return 0;
    }

    public function add()
    {
        $jbt = Jewelbox::find()->where(['userid'=>User::getID()])->andWhere(['status'=>0]);
        $jbModels = [];
        if ($jbt->exists())
        {
            $jbt = $jbt->one();
            $jbModels = json_decode($jbt->storedmodels,true);
        } else {
            $jbt = new Jewelbox();    
        }

        $jbModel = [
            'id' => $this->modelID,
            'comment' => $this->modelComment,
            'price' => '...',
        ];
        $jbModels[] = $jbModel;
        
        $jbt->storedmodels = json_encode($jbModels,true);
        $jbt->userid = User::getID();
        $jbt->lastdate = date('Y-m-d');

        return $jbt->save(false);
    }

    public function getOrderStatus( int $id ) : int
    {
        if ( $id < 1 || $id > PHP_INT_MAX ) return false;
        $jb = Jewelbox::find()->select(['id','status'])->where(['userid'=>User::getID()])->andWhere(['id'=>$id]);
        if (!$jb->exists()) return false;
        $jb = $jb->one();

        return $jb->status;
    }

    public function getStoredModels() : array
    {
        $jb = Jewelbox::find()->where(['userid'=>User::getID()]);
        $storedmodels = [];
        if (!$jb->exists()) return [];
        
        $jb = $jb->all();
        
        $resp = [
            'storedmodels' => [],
            'statuses' => [],
        ];
        foreach( $jb as $num => $orders )
        {
            $storedmodels = json_decode($orders->storedmodels,true);
            $resp['storedmodels'][$orders->id] = $this->proceedStoredModels($storedmodels);
            $resp['statuses'][$orders->id] = $orders->status;
        }
        return $resp;
    }

    protected function proceedStoredModels( array $storedmodels )
    {
        $ids = [];
        foreach( $storedmodels as $sm )
            $ids[] = $sm['id'];

        $stock = Stock::find()->where(['in','id',$ids]);
        if (!$stock->exists()) return [];
        $stock = $stock->with('images')->asArray()->all();

        foreach( $stock as &$model ) {
            foreach( $storedmodels as $sm ) {
                if ( $model['id'] === $sm['id'] ){
                    $model['comment'] = $sm['comment'];
                    $model['storeprice'] = $sm['price'];// round($model['model_cost'] / 2); //
                }
            }
            foreach( $model['images'] as $img ) {
                if ( (int)$img['status'] === 1 ){
                    $model['mainimage'] = "stock/".$model['id']."/images/".$img['name'];
                    break;
                }
            }
        }
        return $stock;
    }

    public function edit()
    {
        $jb = Jewelbox::find()->where(['userid'=>User::getID()])->andWhere(['id'=>$this->orderID]);//andWhere(['status'=>0]);
        if (!$jb->exists()) return false;
        $jb = $jb->one();
        $storedmodels = json_decode($jb->storedmodels,true);

        $flag = false;
        foreach( $storedmodels as $key => &$storedmodel ) {
            if ( (int)$storedmodel['id'] === $this->modelID ) {
                $storedmodel['comment'] = $this->modelComment;
                $flag = true;
                break;
            }
        }

        if ($flag) {
            $jb->storedmodels = json_encode($storedmodels,true);
            $jb->lastdate = date('Y-m-d');
            
            return $jb->save(false);    
        }
        return false;
    }

    public function remove( int $id, int $orderid ) : bool
    {
        if ( $id < 1 || $id > PHP_INT_MAX ) return false;
        if ( $orderid < 1 || $orderid > PHP_INT_MAX ) return false;

        $jb = Jewelbox::find()->where(['userid'=>User::getID()])->andWhere(['id'=>$orderid]);
        if (!$jb->exists()) return false;
        $jb = $jb->one();
        $storedmodels = json_decode($jb->storedmodels,true);

        $flag = false;
        foreach( $storedmodels as $key => $storedmodel ) {
            if ( (int)$storedmodel['id'] === $id ) {
                unset($storedmodels[$key]);
                $flag = true;
                break;
            }
        }

        if ($flag) {
            $jb->storedmodels = json_encode($storedmodels,true);
            $jb->lastdate = date('Y-m-d');
            
            return $jb->save(false);    
        }

        return false;
    }

    public function sendOrder( int $orderid )
    {
        if ( $orderid < 1 || $orderid > PHP_INT_MAX ) return false;
        $jb = Jewelbox::find()->where(['userid'=>User::getID()])->andWhere(['id'=>$orderid]);
        if (!$jb->exists()) return false;
        $jb = $jb->one();
        
        $count = count(json_decode($jb->storedmodels,true));//$this->getModelsCount();
        $sended = Yii::$app->mailer->compose()
            ->setFrom('from@domain.com')
            ->setTo('vady365@yahoo.com')
            ->setSubject('PJ3DB - Новый Заказ № ' . $jb->id . ' от ' . User::getFIO())
            ->setTextBody('Новый Заказ № ' . $jb->id . ' от ' . User::getFIO() . '! На общее кол-во ' . $count . 'шт.' )
            ->setHtmlBody('Новый Заказ № <i>' . $jb->id . '</i> от ' .'<b>'.User::getFIO().'</b>'.'! На общее кол-во ' . $count . 'шт.' )
            ->send();

        if ( $sended ) {
            $jb->status = 1;
            return $jb->save(false);
        }

        return false;
    }

    public function removeOrder( int $orderid )
    {
        if ( $orderid < 1 || $orderid > PHP_INT_MAX ) return false;
        $jb = Jewelbox::find()->where(['userid'=>User::getID()])->andWhere(['id'=>$orderid]);
        if (!$jb->exists()) return false;
        $jb = $jb->one();

        if ($jb->delete())
        {
            $sended = Yii::$app->mailer->compose()
            ->setFrom('from@domain.com')
            ->setTo('vady365@yahoo.com')
            ->setSubject('PJ3DB - Заказ УДАЛЕН!')
            ->setTextBody('Заказ № ' . $jb->id . ' от ' . User::getFIO() . ' УДАЛЕН!')
            ->setHtmlBody('Заказ № <i>' . $jb->id . '</i> от ' .'<b>'.User::getFIO().'</b> УДАЛЕН!')
            ->send();
        }
    }

    public function accessControl() : bool
    {
        if ( User::hasPermission('jewelbox')) return true;
        return false;
    }
}

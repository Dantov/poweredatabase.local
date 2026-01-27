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

	public function __construct( array $post )
    {
        $v = new Validator();
        if ( isset($post['modelID']) ) {
            $id = (int)$post['modelID'];
            if ( $id < 0 || $id > PHP_INT_MAX ) return false;
            $this->modelID = $id;
        }

        if ( isset($post['comment']) )
            $this->modelComment = trim( $v->sanitarizePost('comment') );

        parent::__construct();
	}

    public function add()
    {
        $jbt = Jewelbox::find()->where(['userid'=>User::getID()]);
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
        ];
        $jbModels[] = $jbModel;
        
        $jbt->storedmodels = json_encode($jbModels,true);
        $jbt->userid = User::getID();
        $jbt->lastdate = date('Y-m-d');

        return $jbt->save(false);
    }

    public function getStoredModels() : array
    {
        $jb = Jewelbox::find()->where(['userid'=>User::getID()]);
        $storedmodels = [];
        if ($jb->exists())
        {
            $jb = $jb->one();
            $storedmodels = json_decode($jb->storedmodels,true);
        }
        $ids = [];
        foreach( $storedmodels as $sm )
            $ids[] = $sm['id'];

        $stock = Stock::find()->where(['in','id',$ids]);
        if (!$stock->exists()) return [];
        $stock = $stock->with('images')->asArray()->all();

        foreach( $stock as &$model ) {
            foreach( $storedmodels as $sm ) {
                if ( $model['id'] === $sm['id'] )
                    $model['comment'] = $sm['comment'];
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
        $jb = Jewelbox::find()->where(['userid'=>User::getID()]);
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

    public function remove( int $id ) : bool
    {
        if ( $id < 0 || $id > PHP_INT_MAX ) return false;

        $jb = Jewelbox::find()->where(['userid'=>User::getID()]);
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

    public function accessControl() : bool
    {
        if ( User::hasPermission('jewelbox')) return true;
        return false;
    }
}

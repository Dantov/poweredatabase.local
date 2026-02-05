<?php
namespace app\models\serviceClasses;

use app\models\serviceTables\{Stock,Gems,Materials,Images,D3_files,Jewelbox,Users};
use app\models\{Files,User};

use Yii;

/**
 * Some methods for delete models
 */
class DeletePosition extends SaveModel
{
	
	function __construct( int $modelID = 0 )
	{
		parent::__construct($modelID);
		
	}

	/*
     * 
     */
    public function deleteModelFull() : array
    {
        $stock = Stock::find()->select(['id','model_status'])
        ->where(['id'=>$this->modelID])
        ->andWhere(['model_status'=>2])
        ->one();
        $result = ['gems'=>false,'materials'=>false,'images'=>false,'data'=>false,'files'=>false];
        //$result = ['gems'=>true,'materials'=>true,'images'=>true,'data'=>true,'files'=>true];
        //return $result;

        if ( $stock->delete() )
        {
            $result['gems'] = $this->deleteAllFromTable('gems');
            $result['materials'] = $this->deleteAllFromTable('materials');
            $result['images'] = $this->deleteAllFromTable('images');
            $result['data'] = $this->deleteAllFromTable('data');
            $result['filesAccess'] = $this->deleteAllFromTable('userFilesAccess');

            $path = _stockDIR_ . $this->modelID;
            if ( file_exists($path) )
            	$result['files'] = $this->rrmdir( $path );
        }

        return $result; 
    }

    protected function deleteAllFromTable( string $table ) : bool
    {
    	if ( empty($table) ) return false;

    	$count = 0;
    	switch ($table)
    	{
    		case "gems":
    			if ( Gems::find()->where(['pos_id'=>$this->modelID])->exists() )
    				$count = Gems::deleteAll(['pos_id'=>$this->modelID]);
    		break;
    		case "materials":
    			if ( Materials::find()->where(['pos_id'=>$this->modelID])->exists() )
    				$count = Materials::deleteAll(['pos_id'=>$this->modelID]);
    		break;
    		case "images":
    			if ( Images::find()->where(['pos_id'=>$this->modelID])->exists() )
    				$count = Images::deleteAll(['pos_id'=>$this->modelID]);
    		break;
    		case "data":
    			if ( D3_files::find()->where(['pos_id'=>$this->modelID])->exists() )
    				$count = D3_files::deleteAll(['pos_id'=>$this->modelID]);
    		break;
    		case "jb":
    		break;
    		case "userFilesAccess":
    			$user = Users::find()->where(['id'=>User::getID()]);
    			if ( $user->exists() )
    			{
    				$user = $user->select(['files_access'])->one();
    				$fa = json_decode($user->files_access,true);

    				$found = false;
    				foreach($fa??[] as $k => $mid)
    				{
    					if ( $mid == $this->modelID ) {
    						unset($fa[$k]);
    						$found = true;
    					}
    				}
    				if ( $found ) {
    					$user->files_access = json_encode($fa,true);
    					$count = $user->save(false);
    				}
    			}
    		break;
    	}

    	if ( $count ) return true;
    	return false;
    }

    /*
    protected function deleteDataFiles() : bool
    {
        $d3files = D3_files::find()->select(['id','zipname'])->where(['pos_id'=>$this->modelID]);
        $count = 0;
        if ($d3files->exists())
        {
        	$files = Files::instance();
        	$path = _stockDIR_ . $this->modelID . "/3dfiles/";
            foreach( $d3files->all() as $dfile )
            {
            	if (file_exists($path.$dfile['zipname']))
            		$files->delete($path.$dfile['zipname']);
            }
            $count = D3_files::deleteAll(['pos_id'=>$this->modelID]);
        }

        if ($count) return true;
        return false;
    }
    protected function deleteImages()
    {
    	$images = Images::find()->select(['id','name'])->where(['pos_id'=>$this->modelID]);
        $count = 0;
        if ($images->exists())
        {
        	$files = Files::instance();
        	$path = _stockDIR_ . $this->modelID ."/images/";
        	$posfix = "_prev";
            foreach( $images->all() as $imgfile )
            {
            	if (file_exists($path.$imgfile['name']))
            	{
            		$files->delete($path.$imgfile['name']);
            		if (file_exists($path.$imgfile['name']. ))
            	}
            }
            $count = D3_files::deleteAll(['pos_id'=>$this->modelID]);
        }

        if ($count) return true;
        return false;
    }
    */

    /*
	 * Удаляет папку(вместе с файлами)/файлы по указанному пути
	 */
	protected function rrmdir($src) : bool
	{
	    $dir = opendir($src);
	    while(false !== ( $file = readdir($dir)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            $full = $src . '/' . $file;
	            if ( is_dir($full) ) {
	                $this->rrmdir($full);
	            }
	            else {
	                unlink($full);
	            }
	        }
	    }
	    closedir($dir);
	    return rmdir($src);
	}
}
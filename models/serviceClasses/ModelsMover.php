<?php
namespace app\models\serviceClasses;

use app\models\serviceTables\{Stock,Service_data,Gems,Materials,Images,D3_files};
use app\models\{UploadImages,Common,Files,Validator,User};
use app\models\serviceClasses\ImageConverter;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Обрабатывает данные из формы сохранения модели
 */
class ModelsMover extends Common
{

    public int $modelID;

    public function __construct(  )
    {
        
        parent::__construct();
    }

    public function getStockData() : array
    {

        $stock = Stock::find()->select(['id','client']);//->where(['in','id', [6,8] ]);
        if ( !$stock->exists() )
            return [];

        $this->stock = $stock->asArray()->all();

        $clients = $this->getClients();

        foreach ($this->stock as $key => &$model) 
        {
            $model['modelid_hash'] = substr(sha1($model['id']), 16, 14);
            foreach ($clients as $client) 
            {
                if ( $model['client'] == $client['name'] )
                {
                    $model['client_id'] = $client['id'];
                    $model['clientid_hash'] = substr(sha1($client['id']), 9, 14);
                }
            }       
        }
        return $this->stock;
    }

    public function checkSHAID( int $id ) : array
    {
        $modelidhash = substr(sha1($id), 17, 15);
        foreach ($this->stock as $model) 
        {
            if ( $model['modelid_hash'] === $modelidhash ) return $model;
        }

        return [];
    }

    public function moveModel( array $modeltomove ) : bool
    {
        /*
        $modeltomove = [];
        foreach ($this->stock as $model) 
        {
            if ( $model['id'] === $modelID ) 
                $modeltomove = $model;
        }
        if ( empty($modeltomove) ) return false;
        */

        $oldPath = _stockDIR_ . $modeltomove['id']; 
        if ( file_exists($oldPath) )
        {
            $newClientPath = _stockDIR_ . $modeltomove['clientid_hash'];
            if ( !file_exists($newClientPath) )
                mkdir($newClientPath, 0777, true);

            //$newpath = _stockDIR_ . $modeltomove['modelid_hash'];
            $newpath =  $newClientPath ."/". $modeltomove['modelid_hash'];
            $this->xcopy($oldPath,$newpath);
            //rename($oldPath,$newpath);
        }

        return true;
    }

    protected function xcopy($src, $dest) 
    {
        foreach (scandir($src) as $file) {
            if (!is_readable($src . '/' . $file)) continue;
            if (is_dir($src .'/' . $file) && ($file != '.') && ($file != '..') ) {
                if (!is_dir($dest . '/' . $file))
                {
                    mkdir($dest . '/' . $file, 0777, true);
                }
                $this->xcopy($src . '/' . $file, $dest . '/' . $file);
            } else if (($file != '.') && ($file != '..')) {
                copy($src . '/' . $file, $dest . '/' . $file);
            }
        }
    }

    protected function uploadImageFile( Files $files ) : array
    {
        $uplImg = $files->get('UploadImage');
        $newImgName = '';
        $images = new Images();

        $images->name = $newImgName = $this->modelID."-".randomStringChars( 20, 'en', 'symbols').'.'.$files->getExtension($uplImg['name']);
        $images->status = 0;
        $images->size = $uplImg['size'];
        $images->pos_id = $this->modelID;

        $images->save(false);
        $imgID = $images->getPrimaryKey();

        $destPath = _stockDIR_ . $this->modelID .'/images/'; 

        if ( !file_exists($destPath) ) 
            mkdir($destPath, 0777, true);
        $res = false;
        $res = $files->upload($uplImg['tmp_name'], $destPath.$newImgName, ['png','gif','jpg','jpeg','webp']);
        if ($res)
        {
            /** оптимизация размера файла */
            ImageConverter::optimizeUpload($destPath.$newImgName);

            /** Сднлаем превью загруженного файла */
            //ImageConverter::makePrev($destPath.$newImgName);
            ImageConverter::makePrev($destPath,$newImgName);
        }

        return ['id'=>$imgID,'upload'=>$res,'type'=>'picture'];
    }
    protected function checkOveralFilesize( array $fileData ) : mixed
    {
        $sql  = "SELECT SUM(size) as s, SUM(zipsize) as z FROM d3_files WHERE pos_id={$this->modelID}";
        $data = D3_files::findBySql($sql)->asArray()->one();
        
        $haveSize = (int)$data['s'];
        $currentFileSize = (int)$fileData['size'];
        $total = $currentFileSize + $haveSize;
        $overallAllowedSize = 40100000; // 40.1 mb

        if ( $total > $overallAllowedSize )
            return ['id'=>0,'upload'=>false,'type'=>'data','txt'=>'Your files got too much size. Max allowed is 40mb total.'];
        
        return true;      
    }
    protected function uploadDataFile( Files $files ) : array
    {
        $uplFile = $files->get('Upload3DFile');
        $fileExtension = $files->getExtension($uplFile['name']);
        //$newFileName = $this->modelID."_".randomStringChars( 10, 'en', 'symbols').'.'.$fileExtension;
        $vl = new Validator();
        $newFileName = $vl->sanitizeFileName( $files->getFileName( $uplFile['name'] ) );
        $newFileName = $newFileName ."_".randomStringChars( 7, 'en', 'symbols').'.'.$fileExtension;
      
        $destPath = _stockDIR_ . $this->modelID .'/3dfiles/'; 
        if ( !file_exists($destPath) ) 
            mkdir($destPath, 0777, true);
        $uploadRes = false;
        //debug($uplFile['tmp_name']." - ". $destPath.$newFileName, 1,1 );
        $uploadRes = $files->upload($uplFile['tmp_name'], $destPath.$newFileName, ['3dm','stl','mgx','ai','dxf','obj','zip','rar']);

        //$rowID = 0;
        if ( $uploadRes )
        {
            if ( $fileExtension == 'zip' || $fileExtension == 'rar') {
               return $this->uploadArchive($newFileName, $uplFile, $fileExtension, $uploadRes);
            } else {
               return $this->uploadNonArchive($files, $destPath, $newFileName, $fileExtension, $uplFile, $uploadRes);
            }
        }
        return ['id'=>0,'upload'=>false,'type'=>'data','txt'=>'Some error occurred while saving file!'];
    }
    protected function uploadArchive( $newFileName, $uplFile, $fileExtension, $uploadRes)
    {
        // DB Record
        $d3_files = new D3_files();
        $d3_files->name    = $newFileName;
        $d3_files->zipname = $newFileName;
        $d3_files->type    = $fileExtension;
        $d3_files->size    = $uplFile['size'];
        $d3_files->zipsize = $uplFile['size'];
        $d3_files->pos_id  = $this->modelID;
        $d3_files->save(false);
        $rowID = 0;
        $rowID = $d3_files->getPrimaryKey();

        return ['id'=>$rowID,'upload'=>$uploadRes,'type'=>'data'];
    }
    protected function uploadNonArchive( Files $files, $destPath, $newFileName, $fileExtension, $uplFile, $uploadRes)
    {
        $newFileNameZip = $this->modelID."_zip_".randomStringChars( 10, 'en', 'symbols');
        $zipArch = $this->openZip( $destPath , $newFileNameZip );
        $zipArch['inst']->addFile( $destPath.$newFileName, $newFileName );
        $this->closeZip($zipArch['inst']);
        $files->delete($destPath.$newFileName);
        
        // DB Record
        $d3_files = new D3_files();
        $d3_files->name    = $newFileName;
        $d3_files->zipname = $zipArch['zipName'];
        $d3_files->type    = $fileExtension;
        $d3_files->size    = $uplFile['size'];
        $d3_files->zipsize = $files->getFileSize($destPath.$zipArch['zipName']);
        $d3_files->pos_id  = $this->modelID;
        $d3_files->save(false);
        $rowID = 0;
        $rowID = $d3_files->getPrimaryKey();

        return ['id'=>$rowID,'upload'=>$uploadRes,'type'=>'data'];
    }

    public function dellFile( array $post ) : array
    {
        $res = ['file'=>false,'row'=>false,'type'=>'']; 

        switch( $post['fileType'] )
        {
            case "picture":
                $res['type'] = "picture";
                $rowID = $post['rowID'];
                $images = Images::find()->where(['id'=>$rowID])->limit(1)->one();

                $destPath = _stockDIR_ . $this->modelID .'/images/' . $images->name; 

                $files = Files::instance();
                
                if ( $res['file'] = $files->delete($destPath) )
                $res['row'] = (bool)$images->delete();
            break;
            case "data":
                $res['type'] = "data";
                $rowID = $post['rowID'];
                $d3files = D3_files::find()->where(['id'=>$rowID])->limit(1)->one();

                $destPath = _stockDIR_ . $this->modelID .'/3dfiles/' . $d3files->zipname; 

                $files = Files::instance();
                
                if ( $res['file'] = $files->delete($destPath) )
                $res['row'] = (bool)$d3files->delete();
            break;
        }
        
        return $res;
    }

    public function openZip( string $zip_path, string $zip_name ) : array
    {
        $zip = new \ZipArchive();
        //$zip_name = $this->number_3d."-".$this->model_typeEn.".zip";
        $zip_name = $zip_name . ".zip";
        $zip->open($zip_path.$zip_name, \ZIPARCHIVE::CREATE);

        return ['inst'=>$zip, 'zipName' => $zip_name];
    }
    public function closeZip( \ZipArchive $zip ) : bool
    {
        if ( method_exists($zip,'close') )
        {
            $zip->close();
            return true;
        }
        return false;
    }

    public function accessControl() : bool
    {
        if ( User::hasPermission('edit_all_models') ) 
            return true;

        if ( User::hasPermission('edit_own_models') ) 
        {
            $stock = Stock::find()
                ->select(['id','creator_id','model_status'])
                ->where(['id'=>$this->modelID])
                ->andWhere([ 'creator_id' => User::getID() ]);

           if ( $stock->exists() ) return true;
        }
        return false;
    }

    public function isEditable() : bool
    {
        $stock = Stock::find()
            ->select(['id','model_status'])
            ->where(['id'=>$this->modelID])
            ->andWhere(['model_status'=>2]);

        if ( $stock->exists() ) return false;

        return true;
    }
}
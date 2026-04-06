<?php
namespace app\models\serviceClasses;

use app\models\serviceTables\{Stock,Service_data,Gems,Materials,Images,D3_files};
use app\models\serviceTables\{Huf_stock};

use app\models\{UploadImages,Common,Files,Validator,User};
use app\models\serviceClasses\ImageConverter;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class ModelsMover extends Common
{

    public int $modelID;
    public array $hufstock;

    protected Files $files;

    public function __construct(  )
    {
        $this->files = Files::instance();
        parent::__construct();
    }

    public function getHufStockCount() : int
    {
        return Huf_stock::find()->count();
    }

    public function getStockAffected() : int
    {
        return Stock::find()->select(['id','client'])->where(['client'=>'ХЮФ'])->count();
    }

    public function getHufStockData( int $offset = 0, int $limit = 1 ) : array
    {
        $this->hufstock = Huf_stock::find()
        ->with(['huf_images','huf_gems','huf_materials','huf_stlfiles','huf_rhinofiles'])
        ->offset($offset)
        ->limit($limit)
        ->orderBy('id')
        ->asArray()
        ->all();

        return $this->hufstock;
    }

    public function mergeStock() : array
    {
        $result = [];
        foreach ($this->hufstock as $model) {
            $newModel = new Stock();

            $newModel->number_3d = $model['number_3d'];
            $newModel->client = "ХЮФ";
            $newModel->modeller3d = $model['modeller3D'];
            $newModel->model_type = $model['model_type'];
            $newModel->size_range = $model['size_range'];
            $newModel->print_cost = $model['print_cost'];
            $newModel->model_cost = $model['model_cost'];
            $newModel->model_weight = $model['model_weight'];
            $newModel->description = $model['description'] . "\n Арт: " . $model['vendor_code'] . " || Коллекция: " . $model['collections'] . " || Метки:(" . $model['labels'] . ") || Автор: " . $model['author'];
            $newModel->hashtags = "";
            $newModel->model_status = 1;
            $newModel->date = date("Y-m-d");
            $newModel->create_date = $model['date'];
            $newModel->creator_id = User::getID();

            $newModel->save(false);
            $newModelID = $newModel->getPrimaryKey();

            $result[$model['id']] = [
                'newID' => $newModelID,
            ];

            if ( empty($model['huf_materials']) )
            {
                $result[$model['id']]['materialsID'] = $this->mergeMaterials( false, $model['model_material'], $model['model_type'], $newModelID );
            } else {
                $result[$model['id']]['materialsID'] = $this->mergeMaterials( true, $model['huf_materials'], '', $newModelID);
            }
            $result[$model['id']]['gems'] = $this->mergeGems( $model['huf_gems'], $newModelID );


            $oldPath = _webDIR_ . "stock_huf/".$model['number_3d']."/".$model['id'];
            $result[$model['id']]['images'] = $this->mergeImages( $model['huf_images'], $newModelID, $oldPath );
            $result[$model['id']]['files'] = $this->mergeDataFiles( $model['huf_stlfiles'],$model['huf_rhinofiles'], $newModelID,$oldPath );
        }

        return $result;
    }

    protected function mergeImages( array $hufimages, int $modelID, string $oldpath )
    {
        $result = [];
        foreach ($hufimages as $imgrow) 
        {
            $oldImgPath =  $oldpath."/images/".$imgrow['img_name'];
            if ( !file_exists($oldImgPath) ) continue;

            //DB PART
            $newImgRow = new Images();
            $newImgRow->name = $imgrow['img_name'];
            $newImgRow->status = ($imgrow['main']==null)?0:1;
            $newImgRow->size = '';
            $newImgRow->pos_id = $modelID;

            //MOVE FILE
            $modelPath =_stockDIR_ . Common::modelPath(467,$modelID) . "/images/";
            if ( !file_exists($modelPath) )
                mkdir($modelPath, 0777, true);

            if ( $this->files->copy($oldImgPath, $modelPath.$imgrow['img_name']) )
            {
                $newImgRow->save(false);
                $result[$imgrow['id']] = $newImgRow->getPrimaryKey();
            }
        }

        return $result;
    }
    protected function mergeMaterials( bool $isArray, $matData, string $type='', int $modelID ) : array
    {
        $result = [];
        if ( $isArray )
        {
            foreach ($matData??[] as $matrow) 
            {
                $newMatRow = new Materials();
                $newMatRow->part = $matrow['part']??$type;
                $newMatRow->metal = $matrow['type'];
                $newMatRow->probe = $matrow['probe'];
                $newMatRow->color = $matrow['metalColor'];
                $newMatRow->pos_id = $modelID;
                $newMatRow->save(false);

                $result[$matrow['id']] = $newMatRow->getPrimaryKey();
            }

        } else {
            //Золото;585;;Красное;
            $expl = explode(";", $matData);
            $hufmaterial = [];
            // sometimes we get an empty element becouse of double ;; 
            // clearing it
            foreach ($expl as $value) {
                if ( empty($value) ) continue;
                $hufmaterial[] = $value;
            }
            $newMatRow = new Materials();
            $newMatRow->part = $type;
            $newMatRow->metal = $hufmaterial[0]??'';
            $newMatRow->probe = $hufmaterial[1]??'';
            $newMatRow->color = $hufmaterial[2]??'';
            $newMatRow->pos_id = $modelID;
            $newMatRow->save(false);
            $result[] = $newMatRow->getPrimaryKey();
        }

        return $result;
    }
    protected function mergeGems( array $hufgems, int $modelID ) : array
    {
        $result = [];
        if ( empty($hufgems) ) return $result;

        foreach ($hufgems as $gemsrow) {
            $newGemsRow = new Gems();

            $newGemsRow->name = $gemsrow['gems_names'];
            $newGemsRow->cut = $gemsrow['gems_cut'];
            $newGemsRow->value = $gemsrow['value'];
            $newGemsRow->size = $gemsrow['gems_sizes'];
            $newGemsRow->color = $gemsrow['gems_color'];
            $newGemsRow->pos_id = $modelID;

            $newGemsRow->save(false);
            $result[$gemsrow['id']] = $newGemsRow->getPrimaryKey();
        }

        return $result;
    }
    protected function mergeDataFiles( array $hufStl, array $hufRhino, int $modelID, string $oldpath ) : array
    {
        $result = [
            'hufStl' => [],
            'hufRhino' => [],
        ];
        if ( !empty($hufStl) )
        {
            foreach ($hufStl as $rowStl) 
            {
                $oldStlPath =  $oldpath."/stl/".$rowStl['stl_name'];
                if ( !file_exists($oldStlPath) ) continue;

                $newStlRow = new D3_files();

                $newStlRow->name = $rowStl['stl_name'];
                $newStlRow->zipname = "stl_" . $rowStl['stl_name'];
                $newStlRow->type = 'stl';
                $newStlRow->size = $this->files->getFileSize($oldStlPath); 
                $newStlRow->zipsize = $newStlRow->size;
                $newStlRow->pos_id = $modelID;

                //MOVE FILE
                $modelPath =_stockDIR_ . Common::modelPath(467,$modelID) . "/3dfiles/";
                if ( !file_exists($modelPath) )
                    mkdir($modelPath, 0777, true);

                if ( $this->files->copy($oldStlPath, $modelPath."stl_".$rowStl['stl_name']) )
                {
                    $newStlRow->save(false);
                    $result['hufStl'][$rowStl['id']] = $newStlRow->getPrimaryKey();
                }

            }
        }

        if ( !empty($hufRhino) )
        {
            foreach ($hufRhino as $row3DM) 
            {
                $old3DMPath =  $oldpath."/3dm/".$row3DM['name'];
                if ( !file_exists($old3DMPath) ) continue;

                $new3DMRow = new D3_files();

                $new3DMRow->name = $row3DM['name'];
                $new3DMRow->zipname = "3dm_" . $row3DM['name'];
                $new3DMRow->type = '3dm';
                $new3DMRow->size = $row3DM['size'];
                $new3DMRow->zipsize = $row3DM['size'];
                $new3DMRow->pos_id = $modelID;

                //MOVE FILE
                $modelPath =_stockDIR_ . Common::modelPath(467,$modelID) . "/3dfiles/";
                if ( !file_exists($modelPath) )
                    mkdir($modelPath, 0777, true);

                if ( $this->files->copy($old3DMPath, $modelPath."3dm_" .$row3DM['name']) )
                {
                    $new3DMRow->save(false);
                    $result['hufRhino'][$row3DM['id']] = $new3DMRow->getPrimaryKey();
                }
            }
        }

        return $result;
    }

    /*
    public function getStockData() : array
    {

        $stock = Stock::find()->select(['id','client']);//->where(['>=','id', 159 ]);
        if ( !$stock->exists() )
            return [];

        $this->stock = $stock->asArray()->all();

        $clients = $this->getClients();

        foreach ($this->stock as $key => &$model) 
        {
            $model['modelid_hash'] = substr(sha1($model['id']), Common::SubstrID_FROM, Common::SubstrID_LEN);
            foreach ($clients as $client) 
            {
                if ( $model['client'] == $client['name'] )
                {
                    $model['client_id'] = $client['id'];
                    $model['clientid_hash'] = substr(sha1($client['id']), Common::SubstrClient_FROM, Common::SubstrClient_LEN);
                }
            }       
        }
        return $this->stock;
    }

    public function checkSHAID( int $id ) : array
    {
        $modelidhash = substr(sha1($id), Common::SubstrID_FROM, Common::SubstrID_LEN);
        foreach ($this->stock as $model) 
        {
            if ( $model['modelid_hash'] === $modelidhash ) return $model;
        }

        return [];
    }

    public function moveModelFiles( string $oldPath, string $newPath ) : bool
    {
        if ( file_exists($oldPath) )
        {
            if ( !file_exists($newPath) ) 
                mkdir($newPath, 0777, true);

            $files = Files::instance();
            $files->xcopy($oldPath,$newPath);
            $files->rrmdir($oldPath);

            return true;
        }

        return false;
    }

    public function moveModel( array $modeltomove, Files $files ) : bool
    {
        $oldPath = _stockDIR_ . $modeltomove['id']; 
        if ( file_exists($oldPath) )
        {
            $newClientPath = _stockDIR_ . $modeltomove['clientid_hash'];
            if ( !file_exists($newClientPath) )
                mkdir($newClientPath, 0777, true);

            //$newpath = _stockDIR_ . $modeltomove['modelid_hash'];
            $newpath =  $newClientPath ."/". $modeltomove['modelid_hash'];
            $this->xcopy($oldPath,$newpath);
            $files->rrmdir($oldPath);
        }

        return true;
    }
    */

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
}
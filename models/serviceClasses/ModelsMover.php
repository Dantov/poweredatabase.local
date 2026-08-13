<?php
namespace app\models\serviceClasses;

use app\models\serviceTables\{Stock,Service_data,Gems,Materials,Images,D3_files};
use app\models\serviceTables\{Huf_stock};

use app\models\{UploadImages,Common,Files,Validator,User};
use app\models\serviceClasses\{SaveModel, ImageConverter};

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class ModelsMover extends Common
{

    public int $modelID;
    public array $hufstock;

    protected const STOCKSKOLD = _webDIR_ . "stock_skold/";

    protected Files $files;

    protected $errors = [];
    protected $result = [];

    protected string $number3D = '';

    protected array $modelImages = [];
    protected array $modelFiles3d = [];
    protected array $filesforDescr = [];

    public function __construct(  )
    {
        $this->files = Files::instance();
        parent::__construct();
    }

    public function getErrors() : array
    {
        return $this->errors;
    }
    public function getResult() : array
    {
        return $this->result;
    }

    public function moveSKModel()
    {
        //debug(self::STOCKSKOLD,"self::STOCKSKOLD",1);

        $stockskold = opendir(self::STOCKSKOLD);
        //$file = readdir($stockskold);

        while(false !== ( $file = readdir($stockskold) ) ) 
        {
            if ( ( $file == '.' ) || ( $file == '..' ) ) continue;

            //debug($file,"file");
            $full = self::STOCKSKOLD . '/' . $file;

            if ( is_dir($full) )
            {
                $modelDir = opendir($full);
                $this->readModelDir($modelDir,$file);
                closedir($modelDir);
                //break;
            }
        }

        closedir($stockskold);
        //debug($this->result,"result");
        //debug($this->errors,"errors",1);
    }

    public function readModelDir( &$modelDir, string $modelDirName )
    {
        $hasJsonFile = false;
        $this->modelImages = [];
        $this->modelFiles3d = [];
        $this->filesforDescr = [];
        while(false !== ( $file = readdir($modelDir)) ) 
        {
            if ( ( $file == '.' ) || ( $file == '..' ) ) continue;

            /*
            if ($file == 'model_data.json') {
                //debug($modelDirName,'$modelDirName');
                $hasJsonFile = true;
                //$this->readModelData($modelDirName);
            }
            */

            $ext = $this->files->getExtension($file);
            if ( $ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "gif")
                $this->modelImages[] = $file;
            if ( $ext == "rar" || $ext == "zip" || $ext == "stl" || $ext == "mgx" || $ext == "3dm")
                $this->modelFiles3d[] = $file;
            if ( $ext == "txt")
                $this->filesforDescr[] = $file;
        }

        $this->readModelData($modelDirName);
        /*
        if ( $hasJsonFile ) {
            
        } else {
            $this->errors[$modelDirName] = 'No json file in this folder';
        }
        */
    }

    public function readModelData( string $modelDirName )
    {
        $path = self::STOCKSKOLD . $modelDirName;

        /* JSON BLOCK
        $filename = $path."/model_data.json";
        if ( !$fp = fopen($filename, 'r') ) 
             return $this->errors[$modelDirName] = 'cant open json file';

        $contents = fread($fp, filesize($filename));
        fclose($fp);

        //debug( $contents,"contents" );

        $dat = json_decode($contents,true);
        if ( empty($dat) )
            return $this->errors[$modelDirName] = 'cant read json file';
        */
        /*
        debug( $dat, $modelDirName );
        debug( $this->modelImages, "modelImages" );
        debug( $this->modelFiles3d, "modelFiles3d");
        exit('test');
        */

        $explDirName = explode('_',$modelDirName);
        $num3D = $explDirName[0];
        $mtypeRough = $explDirName[1];
        $mt = trim(explode(' ', $mtypeRough)[0]);

        $data = [
            'number_3d'=>$num3D,
            'model_type'=>$mt,
            'create_date'=>'',
            'mainimage'=>'',
            'description'=>''
        ];
        $found = false;

        foreach ( $this->modelImages as $mimage ) 
        {
            $imgdat = explode("_", $this->files->getFileName($mimage) );
            if ( $imgdat[0] == 'mainimage' ) {
                $data['mainimage'] = $mimage;
                $data['create_date'] = $imgdat[1];
            }
        }

        /*
        $currDate = date("2025-01-02");
        $date = '';
        foreach ( $this->filesfordate as $file ) 
        {
            $fdate = date("Y-m-d", filemtime($path.'/'.$file));
            if ( strtotime($fdate) < strtotime($currDate) )
                $date = $fdate;
        }
        if ( empty($date) )
        {
            foreach ( $this->modelImages as $ifile ) 
            {
                $ifdate = date("Y-m-d", filemtime($path.'/'.$ifile));
                if ( strtotime($ifdate) < strtotime($currDate) )
                    $date = $ifdate;
            }
        }
        */
        //$data['create_date'] = empty($date)?'2016-01-06':$date;

        foreach ( $this->filesforDescr as $dfile ) 
        {
            $data['description'] .= $this->readTextInfo($modelDirName, $dfile);
        }

        //debug( $data, "data",1);

        $this->addStockModel($data, $path);
    }

    public function readTextInfo( string $modelDirName, string $filename ) : string
    {
        $path = self::STOCKSKOLD . $modelDirName;

        $filename = $path.'/'.$filename;
        if ( !$fp = fopen($filename, 'r') ) 
             return $this->errors[$filename] = 'cant open text file';

        $contents = fread($fp, filesize($filename));
        $contents = iconv('CP1251', 'UTF-8', $contents); // Set file decoding for read cyrilic
        fclose($fp);

        //debug( $contents,"text contents",1);

        if ( empty($contents) )
        {
             $this->errors[$filename] = 'text file is empty';
             return '';
        }

        //debug( $dat, $modelDirName );
        //debug( $this->modelFiles3d, "modelFiles3d");
        //exit('test');

        return $contents;
    }

    public function addStockModel( array $jsondata, string $path )
    {
        
        $newModel = new Stock();
        $newModel->number_3d  = $jsondata['number_3d'];
        $newModel->client     = 'Кулинич Old';
        $newModel->modeller3d = 'Модельер';
        $newModel->model_type = $jsondata['model_type'];
        $newModel->size_range = '';
        $newModel->print_cost = '';
        $newModel->model_cost = '';
        $newModel->model_weight = 1;
        $newModel->hashtags = "";
        $newModel->description = basename($path) .' '. $jsondata['description'];
        $newModel->model_status = 1;
        $newModel->date = date("Y-m-d");
        $newModel->create_date = $jsondata['create_date'];
        $newModel->creator_id = User::getID();
        $newModel->save(false);

        $newModelID = $newModel->getPrimaryKey();
        if ( !$newModelID )
            return $this->errors[$path] = "Error happens while saving data in Stock";

        $this->result[$newModelID] = [
            'name' => basename($path),
            'gems' => [],
            'images' => [],
            '3dfiles' => [],
            'materials' => [],
        ];

        $newMatRow = new Materials();
        $newMatRow->part   = $jsondata['model_type'];
        $newMatRow->metal  = "Золото";
        $newMatRow->probe  = "585";
        $newMatRow->color  = "Белое";
        $newMatRow->pos_id = $newModelID;
        if ( $newMatRow->save(false) )
            $this->result[$newModelID]['materials'] = $newMatRow->getPrimaryKey();

        /*
        if ( !empty($jsondata['gems']) )
            $this->result[$newModelID]['gems'] = $this->addGems($jsondata['gems'],$newModelID);
        */

        if ( !empty($jsondata['mainimage']) && !empty($this->modelImages) )
            $this->result[$newModelID]['images'] = $this->addImages($jsondata['mainimage'],$newModelID,$path);

        //if ( !empty($jsondata['d3_files']) )
        if ( !empty($this->modelFiles3d) )
            $this->result[$newModelID]['3dfiles'] = $this->add3DFiles($this->modelFiles3d,$newModelID,$path);
    }
    public function addGems($gems, $newModelID)
    {
        $result = [];

        foreach ($gems as $gemsrow) 
        {
            $newGemsRow = new Gems();
            $newGemsRow->name = $gemsrow['name'];
            $newGemsRow->cut = $gemsrow['cut'];
            $newGemsRow->value = $gemsrow['value'];
            $newGemsRow->size = $gemsrow['size'];
            $newGemsRow->color = $gemsrow['color'];
            $newGemsRow->pos_id = $newModelID;
            $newGemsRow->save(false);

            $result[] = $newGemsRow->getPrimaryKey();
        }

        return $result;
    }
    public function addImages($mainImgName, $newModelID, $path)
    {
        $result = [];
        //foreach ($images as $imgrow) 
        foreach ($this->modelImages as $imgname) 
        {
            $oldImgPath =  $path."/".$imgname;
            if ( !file_exists($oldImgPath) ) continue;

            //MOVE FILE
            $modelPath =_stockDIR_ . Common::modelPath(512,$newModelID) . "/images/";
            if ( !file_exists($modelPath) )
                mkdir($modelPath, 0777, true);

            $newImgName = randomStringChars( 20, 'en', 'symbols').'.'.$this->files->getExtension($imgname);

            if ( $this->files->copy($oldImgPath, $modelPath.$newImgName) )
            {
                //DB PART
                $newImgRow = new Images();
                $newImgRow->name = $newImgName;
                $newImgRow->status = ($imgname==$mainImgName) ? 1 : 0;
                $newImgRow->size = $this->files->getFileSize($oldImgPath);
                $newImgRow->pos_id = $newModelID;
                if ( $newImgRow->save(false) ) {
                    $result[] = $newImgRow->getPrimaryKey();
                } else {
                    $this->errors[$newModelID][] = "Can't record DB image file ($newImgName) on id $newModelID";    
                }
            } else {
                $this->errors[$newModelID][] = "Can't copy image file from(".$oldImgPath.") \n to $modelPath$newImgName";
            }
        }

        return $result;
    }
    public function add3DFiles($d3files, $newModelID, $path)
    {
        $result = [];
        //foreach ($d3files as $d3fileRow) 
        foreach ($d3files as $d3fileName) 
        {
            $oldStlPath =  $path."/".$d3fileName;
            if ( !file_exists($oldStlPath) ) continue;

            //MOVE FILE
            $modelPath =_stockDIR_ . Common::modelPath(512,$newModelID) . "/3dfiles/";
            if ( !file_exists($modelPath) )
                mkdir($modelPath, 0777, true);

            $fileExtension = $this->files->getExtension($oldStlPath);
            $newFileNameZip = "";
            $fileType = "";
            if ( $this->files->copy($oldStlPath, $modelPath.$d3fileName) )
            {
                if ( $fileExtension == 'zip' || $fileExtension == 'rar' ) 
                {
                    $newFileNameZip = $d3fileName;
                    $fileType = explode("_", $d3fileName)[0];
                } else {
                    //rest 3d files need to archivate
                    $newFileNameZip = "zip_".randomStringChars( 10, 'en', 'symbols');
                    $zipArch = $this->openZip( $modelPath , $newFileNameZip );
                    $zipArch['inst']->addFile( $modelPath.$d3fileName, $d3fileName );

                    $newFileNameZip = $zipArch['zipName'];

                    $this->closeZip($zipArch['inst']);
                    $this->files->delete($modelPath.$d3fileName);

                    $fileType = $fileExtension;
                }

                $newStlRow = new D3_files();
                $newStlRow->name = $d3fileName;
                $newStlRow->zipname = $newFileNameZip;
                $newStlRow->type = $fileType;
                $newStlRow->size = $this->files->getFileSize($oldStlPath); 
                $newStlRow->zipsize = $this->files->getFileSize($modelPath.$newFileNameZip);
                $newStlRow->pos_id = $newModelID;
                if ( !$newStlRow->save(false) )
                    $this->errors[$oldStlPath]['add3DFiles'][] = "Error while recording DB 3d file $newFileNameZip";
                
                $result[] = $newStlRow->getPrimaryKey();
            } else {
                $this->errors[$oldStlPath]['add3DFiles'][] = "Error while coping 3d file: $newFileNameZip to new path";
            }
            
        }
        return $result;
    }

    public function openZip( string $zip_path, string $zip_name ) : array
    {
        $zip = new \ZipArchive();
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
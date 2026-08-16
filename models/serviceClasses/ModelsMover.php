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

    protected bool $allGood = false;
    protected bool $hasErrors = false;

    protected $errors = [];
    protected $result = [];

    protected string $number3D = '';

    protected array $allModelTypes = [];

    protected array $modelImages = [];
    protected array $modelFiles3d = [];
    protected array $filesforDescr = [];

    public function __construct(  )
    {
        $this->files = Files::instance();
        $allModelTypes = Service_data::find()->where(['tab'=>'model_type'])->asArray()->orderBy('name')->all();
        foreach ($allModelTypes as $value)
            $this->allModelTypes[] = mb_strtolower($value['name']);
        
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
    public function setAllGood() : bool
    {
        return $this->allGood = true;
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
        $this->modelImages = [];
        $this->modelFiles3d = [];
        $this->filesforDescr = [];
        while(false !== ( $file = readdir($modelDir)) ) 
        {
            if ( ( $file == '.' ) || ( $file == '..' ) ) continue;

            $ext = $this->files->getExtension($file);
            if ( $ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "gif")
                $this->modelImages[] = $file;
            if ( $ext == "rar" || $ext == "zip" || $ext == "stl" || $ext == "mgx" || $ext == "3dm" || $ext == "7z")
                $this->modelFiles3d[] = $file;
            if ( $ext == "txt")
                $this->filesforDescr[] = $file;
        }

        $this->readModelData($modelDirName);
    }

    public function readTextInfo( string $modelDirName, string $filename ) : string
    {
        $path = self::STOCKSKOLD . $modelDirName;

        $contents = '';

        $filename = $path.'/'.$filename;
        if ( !$fp = fopen($filename, 'r') ) 
             return $this->errors[$filename] = 'cant open text file';

        $contents = fread($fp, filesize($filename));
        $contents = iconv('CP1251', 'UTF-8', $contents); // Set file decoding for read cyrilic
        fclose($fp);

        /*
        if ( empty($contents) )
        {
             $this->errors[$filename] = 'text file is empty';
             return '';
        }
        */

        return $contents;
    }

    public function readModelData( string $modelDirName )
    {
        $path = self::STOCKSKOLD . $modelDirName;

        $explDirName = preg_replace(['/-/','/_/','/!/','/\+/','/\(/','/\)/'], ' ', $modelDirName);
        $explDirName = preg_replace('/\s\s+/', ' ', $explDirName);
        $explDirName = explode(' ',$explDirName);

        $num3D = $explDirName[0];

        $data = [
            'number_3d'=>$num3D,
            'model_type'=>'',
            'create_date'=>'',
            'mainimage'=>'',
            'description'=>'',
            'errors' => [],
        ];

        if ( isset($explDirName[1]) ) 
        {
            $mtypeRough = $explDirName[1];
            $mtypeRough = trim($mtypeRough,' ');

            $data['model_type'] = mb_strtolower(explode(' ', $mtypeRough)[0]);

            if ( !in_array($data['model_type'], $this->allModelTypes) )
                $data['errors'][] = '<<'.$data['model_type'].'>> Model type is incorrect in << ' . $modelDirName . ' >>';
            
        } else {
            $data['errors'][] = 'No Model type in << ' . $modelDirName . ' >>';
        }
        
        $found = false;
        foreach ( $this->modelImages as $ki => $mimage ) 
        {
            if ( !Validator::validateFileName($mimage) ){
                $this->modelImages[$ki] = $newIName = preg_replace(['/,/', '/!/', '/\+/', '/\(/','/\)/'], '', $mimage);
                rename($path.'/'.$mimage, $path.'/'.$newIName);
                //$data['errors'][] = 'File << '. $mimage .' >>' . ' has incorrect chars!';
            }

            $imgdat = explode("_", $this->files->getFileName($mimage) );
            if ( $imgdat[0] === 'mainimage' ) {
                $found = true;
                $data['mainimage'] = $mimage;

                if ( isset($imgdat[1]) ) {
                    if ( validateDate($imgdat[1]) ) $data['create_date'] = $imgdat[1];
                } else {
                    $data['errors'][] = 'date not found in << ' . $modelDirName .' >>';
                }
                break;
            } 
        }

        if ( !$found ) 
            $data['errors'][] = 'Main image does not found in << ' . $modelDirName .' >>';

        if ( empty($data['create_date']) ) 
            $data['errors'][] = 'Incorrect date in << ' . $modelDirName .' >>';

        foreach ( $this->filesforDescr as $dfile ) 
        {
            $data['description'] .= $this->readTextInfo($modelDirName, $dfile) . "\n";
        }

        foreach ( $this->modelFiles3d as $kd => $file3d ) 
        {
            if ( !Validator::validateFileName($file3d) ) {
                $this->modelFiles3d[$kd] = $newf3dName = preg_replace(['/,/', '/!/', '/\+/', '/\(/','/\)/'], '', $file3d);
                rename($path.'/'.$file3d, $path.'/'.$newf3dName);
                //$data['errors'][] = 'File << '. $file3d .' >>' . ' has incorrect chars!';
            }
        }


        if ( $this->allGood ) 
        {
            //debug( $data, "data All Good",1);
            //$this->result[] = $data;
            $this->addStockModel($data, $path);
        }

        if ( !empty($data['errors']) )
        {
            $this->errors[] = $data;
            $this->hasErrors = true;
        }    
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
            'data' => $jsondata,
        ];

        $newMatRow = new Materials();
        $newMatRow->part   = $jsondata['model_type'];
        $newMatRow->metal  = "Золото";
        $newMatRow->probe  = "585";
        $newMatRow->color  = "Белое";
        $newMatRow->pos_id = $newModelID;
        if ( $newMatRow->save(false) )
            $this->result[$newModelID]['materials'] = $newMatRow->getPrimaryKey();

        if ( !empty($jsondata['mainimage']) && !empty($this->modelImages) )
            $this->result[$newModelID]['images'] = $this->addImages($jsondata['mainimage'],$newModelID,$path);

        //if ( !empty($jsondata['d3_files']) )
        if ( !empty($this->modelFiles3d) )
            $this->result[$newModelID]['3dfiles'] = $this->add3DFiles($this->modelFiles3d,$newModelID,$path);
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

    /*
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
    */














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
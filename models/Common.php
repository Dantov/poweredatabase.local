<?php 
namespace app\models;
use app\models\serviceTables\{Service_data, Stock};
use app\models\User;
use Yii;

class Common
{
	public static array $clients;
	public static array $roles;

	public function __construct()
    {
        
    }

	public function dateConvert( string $date ) : string
	{
		if ( empty( $date ) ) return '';

		$ex = explode('-',$date);
		return $ex[2] . '.' . $ex[1] . '.' . $ex[0];
	}

	public function convertFileSize( string $sizeByte, string $measure = 'mb', int $precision = 2 ) : string
	{
		$measureTypes = [
            'b' => 1,
            'kb' => 1024,
            'mb' => 1e+6,
            'gb' => 1e+9,
            'tb' => 1e+12,
            'pb' => 1e+15,
        ];
        
        return round( $sizeByte / $measureTypes[$measure], $precision );    
	}

	public function getClients() : array
	{
		self::$clients = Service_data::find()->where(['tab'=>'client'])->asArray()->orderBy('name')->all();

		if ( User::hasPermission('clientall') )
			return self::$clients;

		if ( User::hasPermission('clientonly') )
		{
			$ids = User::getClientsID( self::$clients );
			return self::$clients = Service_data::find()->where(['tab'=>'client'])->andWhere(['in','id',$ids])->asArray()->orderBy('name')->all();
		}
		return [];
	}

	/*
	 * For hide client name in top bar near search row
	 */
	public function getClientName() : string
	{
		$session = Yii::$app->session;
		$unhidedName = $session->get('SelectByClient');

		if ( $unhidedName == 'Все' )
			return $unhidedName;

		$allClients = $this->getClients();

		foreach ( $allClients as $clientTmpl ) 
		{
			if ( $clientTmpl['name'] == $unhidedName ){
				return $clientTmpl['secondname'];
			}
		}

		return '';
	}

	public function getAllRoles()
	{
		if ( isset(self::$roles) ) return self::$roles;
		return self::$roles = Service_data::find()->where(['tab'=>'role'])->asArray()->orderBy('name')->all();
	}

	public function getAllHashtags() : array
	{
		return Service_data::find()->where(['tab'=>'hashtag'])->asArray()->orderBy('name')->all();
	}

	public function getAllModelTypes() : array
	{
		return Service_data::find()->where(['tab'=>'model_type'])->asArray()->orderBy('name')->all();
	}

	public function getAllMaterials() : array
	{
		$mats = Service_data::find()->where(['in','tab',['metal_color','model_material','metal_probe']])->asArray()->orderBy('name')->all();
		$res = [ 'metal_color'=>[], 'model_material'=>[],'metal_probe'=>[] ];
		foreach ($mats as $mat)
		{
			if ( $mat['tab'] == 'metal_color' ) $res['metal_color'][] = $mat;
			if ( $mat['tab'] == 'model_material' ) $res['model_material'][] = $mat;
			if ( $mat['tab'] == 'metal_probe' ) $res['metal_probe'][] = $mat;
		}
		return $res;
	}

	public function getNonPublished()
	{
		$stock = Stock::find()->where(['model_status' => 0]);

		if ( User::hasPermission('edit_own_models') )
			$stock->andWhere(['creator_id' => User::getID() ]);

		$stock = $stock->with(['images'])->asArray()->all();

		$files = Files::instance();
		$prevSuff = '_prev';
		foreach ( $stock as &$model )
        {
        	if ( empty($model['images']) )
        	{
        		$model['mainimage'] = 'web1.webp';
        		break;
        	}

        	$found = false;
            foreach ( $model['images'] as $image )
            {
                if ( $image['status'] === 1 ) {

                	//Image preview check
                	$imgname = $files->getFileName($image['name']);
		            $imgExt = $files->getExtension($image['name']);
		            $previmg = $imgname.$prevSuff.".".$imgExt;
		            $path = _stockDIR_ . $image['pos_id'] . "/images/";
		            $fullpath = _stockDIR_ . $image['pos_id'] . "/images/".$previmg;
		            $model['path'] = $path;
		            if ( file_exists($fullpath) ) {
		                $model['previmg'] = $previmg;
		            }

                    $model['mainimage'] = $image['name'];
                    $found = true;
                    break;
                }
            }

            if ( !$found )
	        {
	            $randomimg = $model['images'][ random_int( 0, (count( $model['images']))-1) ];
	            $model['mainimage'] = $randomimg['name'];
	        }
        }
        return $stock;
	}
	
	protected function setIdAsKeys( array &$array )
    {
        foreach ( $array as $key => $element )
        {
            if (!isset($element['id'])) continue;
            $array[$element['id']] = $element;
            unset($array[$key]);
        }
    }

   public function drawEditBtn( int $creatorID ) : bool
   {
   		if (  User::hasPermission('edit_all_models') ) return true;

   		if (  User::hasPermission('edit_own_models') )
   			if ( $creatorID === User::getID() ) return true; 

   		return false;
   }


}
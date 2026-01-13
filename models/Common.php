<?php 
namespace app\models;
use app\models\serviceTables\{Service_data, Stock};
use app\models\User;

class Common
{
	public static array $clients;

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

	public function getAllHashtags() : array
	{
		return Service_data::find()->where(['tab'=>'hashtag'])->asArray()->orderBy('name')->all();
	}

	public function getNonPublished()
	{
		$stock = Stock::find()->where(['model_status' => 0])->with(['images'])->asArray()->all();

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
}
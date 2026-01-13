<?php
namespace app\models\serviceClasses;

use app\models\serviceTables\{Stock,Service_data};
use app\models\{Files,User};

use Yii;
use yii\helpers\Url;

class AddEdit extends ModelView
{ 
    public array $stock = []; 

	function __construct( $general, $id=false )
    {
        parent::__construct($general, $id);
	}

    public function accessControl( string $subtype ) : bool
    {
        switch ($subtype)
        {
            case 'add':
                if ( !User::hasPermission('add_model')) return true;
            break;
            case 'edit':
                if ( User::hasPermission('edit_all_models') ) 
                    return true;
                if ( User::hasPermission('edit_own_models') ) {
                   if (User::getID() === (int)$this->stock['creator_id']) 
                        return true;
                }
            break;
            default:
                return false;
        }

        return false;
    }

    public function getStockData() : array
    {
        $this->stock = Stock::find()
            ->where(['id' => $this->id])
            ->with(['materials','gems','images','d3_files'])
            ->asArray()
            ->one();

        $this->dataFilesPrepare();
        return $this->stock;
    }

	public function getDataTables()
    {
		$tabs = [
		    'client',
            'modeller3d',
            'model_type',
            'model_material',
            'model_covering',
            'handling',
            'metal_color',
            'vc_names',
            'gems_color',
            'gems_cut',
            'gems_names',
            'gems_sizes',
            'hashtag',
        ];
		$tables = [];

        $service_data = Service_data::find()->asArray()->orderBy('name')->all();

		foreach ( $service_data as $row )
		{
            foreach ( $tabs as $tab )
            {
                if ( $row['tab'] === $tab ) $tables[$tab][] = $row;
            }
		}

        //debug($tables,1,1);
        foreach ( $tables['model_material'] as &$mat )
        {
            $expl = explode(";",$mat['name']);
            $mat['probe'] = '';
            if (isset($expl[1])) $mat['probe'] = $expl[1];
        }
        //debug($tables,1,1);
        //$tables['vc_names'] = $this->getNum3dVCList($tables['vc_names']);

		return $tables;
	}
    public function setHashtagsActiv( string $stockHashtags, array &$knownHashtags )
    {
        $stockHashtags = explode('#',$stockHashtags);
        foreach( $knownHashtags as &$knownHashtag )
            $knownHashtag['checked'] = ''; 
 
        foreach( $stockHashtags as $stockHashtag )
        {
            foreach( $knownHashtags as &$knownHashtag )
            {
                if ($stockHashtag === $knownHashtag['name']){
                    $knownHashtag['checked'] = 1; 
                    continue;
                }
            }    
        }
    }
	
	public function getImages($scetch=false)
    {
        $result = [];

//            debug($this->general->img_arr,'ggg');
//            debug($this->row,'row',1);

        $i = 0;
        foreach ( $this->row['images'] as $image )
        {
            $result[$i]['id'] = $image['id'];

            $img = $this->number_3d.'/'.$this->id.'/images/'.$image['img_name'];

            if ( !file_exists(_stockDIR_.$img) )
            {
                $result[$i]['src'] = _stockDIR_HTTP_."default.jpg";
            } else {
                $result[$i]['src'] = _stockDIR_HTTP_.$this->number_3d.'/'.$this->id.'/images/'.$image['img_name'];
            }

            $img_arr = $this->general->img_arr;

            // верхний ходит по картинкам цепляя main onbody итд в key
            foreach ( $image as $key => $value )
            {
                // нижний ходит по статусам из табл и сверяет имена с ключом из картинок
                $flagToResetNo = false;
                foreach ( $img_arr as &$option )
                {
                    if ( $key === $option['name_en'] && (int)$value === 1 )
                    {
                        $option['selected'] = $value;
                        $flagToResetNo = true;
                    }
                    // уберем флажек с "НЕТ" если был выставлен на чем-то другом
                    if (  (int)$option['id'] === 27 && $flagToResetNo === true ) $option['selected'] = 0;
                }
            }

            $result[$i]['statusImg'] = $img_arr;

            $i++;
        }

        //debug($result,'$result',1);

		return $result;
	}

	public function getStatus(&$row = [])
    {
		$statuses = $this->general->status_arr;
        $statusStock = isset($row['status']) ? trim($row['status']) : '';

		if ( empty($statusStock) )
        {
            $statuses[0]['check'] = "checked";
            return $statuses;
        }

        foreach ( $statuses as &$status )
        {
            if ( $statusStock === $status['name_ru'] ) $status['check'] = "checked";
        }
		return $statuses;
	}

	public function getLabels($str="")
    {
		$labels = $this->general->labels_arr;
		if ( isset($str) && !empty($str) )
		{
			$stock_labels = explode(";",$str);
			foreach ( $stock_labels as $stock_label )
			{
				foreach ( $labels as &$label )
				{
					if ( $stock_label == $label['name_ru'] ) $label['check'] = "checked";
				}
			}
		}
		return $labels;
	}

    public function dataFilesPrepare( string $measure = 'mb', int $precision = 2)
    {
        $measureTypes = [
            'b' => 1,
            'kb' => 1024,
            'mb' => 1e+6,
            'gb' => 1e+9,
            'tb' => 1e+12,
            'pb' => 1e+15,
        ];
        
        foreach ( $this->stock['d3_files'] as &$dfile )
        {
            $dfile['size'] = round($dfile['size'] / $measureTypes[$measure], $precision ) . $measure;    
        }

        //debug($this->stock['d3_files'], 1,1  );
    }

}

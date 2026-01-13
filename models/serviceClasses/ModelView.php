<?php
namespace app\models\serviceClasses;

use app\models\serviceTables\Stock;
use app\models\Common;

use Yii;
use yii\helpers\Url;

class ModelView extends Common
{
    public $id;
    public $number_3d;
	public $row;

	public $general; //Controller

    function __construct( &$general, $id = 0 )
    {
        $session = Yii::$app->session;

        //if ( !is_object($general) ) exit('omg error general is not an OBJECT');
        //if (empty($general->IP_visiter)) exit('omg error IP_visiter is Empty');

        //$this->general = $general;

        //$this->general->setAppPage('modelview');
        if ( isset($id) ) $this->id = $id;
    }

	public function getStockData()
    {
        $this->row = Stock::find()
            ->with(['images','materials','gems','d3_files'])
            ->where(['=','id',$this->id])
            ->asArray()
            ->limit(1)
            ->one();

		$this->number_3d = $this->row['number_3d'];
        $this->setMainImg();
        $this->setSizesRange();
        $this->setHashtags();
        $this->setDataFiles();

		return $this->row;
	}

    protected function setMainImg()
    {
        if ( empty($this->row['images']) )
            return;

        $found = false;
        foreach ( $this->row['images'] as $image )
        {
            if ( $image['status'] === 1 )
            {
                $this->row['mainimage'] = $image['name'];
                $this->row['mainimageID'] = $image['id'];
                $found = true;
                break;
            }
        }

        if ( !$found )
        {
            $randomimg = $this->row['images'][ random_int( 0, (count( $this->row['images']))-1) ];
            $this->row['mainimage'] = $randomimg['name'];
            $this->row['mainimageID'] = $randomimg['id'];
        }
    }
    protected function setSizesRange()
    {
        $this->row['size_range'] = explode('-',$this->row['size_range']);
    }
    protected function setHashtags()
    {
        $hashtagsC = ['success','info','warning','primary','secondary','danger','dark'];

        $hashtags = explode('#', $this->row['hashtags']);
        foreach ( $hashtags as $key => $hashtag )
        {
            if ( empty($hashtag) ) unset($hashtags[$key]);

        }
        
        $this->row['hashtags'] = $hashtags;
        $this->row['hashtags_colors'] = $hashtagsC;
    }

    protected function setDataFiles()
    {  
        $this->row['overal_size'] = 0;
        $this->row['overal_zipsize'] = 0;
        foreach ( $this->row['d3_files'] as &$dfile )
        {
            $this->row['overal_size'] += $dfile['size'];
            $this->row['overal_zipsize'] += $dfile['zipsize'];
            
            $dfile['size'] = $this->convertFileSize($dfile['size']);    
            $dfile['zipsize'] = $this->convertFileSize($dfile['zipsize']); 
        }

        $this->row['overal_size'] = $this->convertFileSize($this->row['overal_size']);   
        $this->row['overal_zipsize'] = $this->convertFileSize($this->row['overal_zipsize']);   
    }

    public function getImages()
    {
    
    }

    

}
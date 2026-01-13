<?php

namespace app\models\serviceClasses;

use app\models\serviceTables\Stock;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;

class Main
{
    /*
	 * ссылка на класс GeneralController
	 */
    protected $general;
    /*
     * массив переменных из текущей сессии
     */
    public $assist;

    /*
     *
     */
    public $selectionMode;

    /*
     * массив данных о юзвере из текущей сессии
     */
    protected $user;

    /*
     * массив выбранных моделей из базы, для отображения
     */
    protected $row;

    /*
     * массив меток из $this->getStatLabArr('labels')
     */
    protected $labels;

    /*
     * кол-во всех позиций для отображения
     */
    public $countPos;

    /*
     * Объект пагинации
     */
    public $pages;
	
	function __construct( &$general )
	{
        $session = Yii::$app->session;

        if ( !is_object($general) ) exit('omg error1');
		
        if ( empty($general->IP_visiter) ) exit('omg error2');

        $this->general = $general;

		//debug( $_COOKIE,'$_COOKIE' );
		
        if ( $session->has('assist') ) $this->assist = $session->get('assist');
        if ( $session->has('selectionMode') ) $this->selectionMode = $session->get('selectionMode');


       // debug($this->assist,'',1);

        /*
         * Смотрим откуда выбрать модели
         */
        if (   filter_has_var(INPUT_POST, 'searchFor')
            || filter_has_var(INPUT_GET, 'searchFor')
            || $session->get('re_search')
            //|| $session->get('searchFor')
        )
        {
            $this->row = $this->getModelsFromSearch();
        } elseif ( $this->selectionMode['getModels'] === true ) {
            $this->row = $this->getModelsFromSelectionMode();
        } else {
            $this->row = $this->getModelsFromStock();
        }
	}

    public function getVariables()
    {
        $result = array();

        if ( $this->assist['sortDirect'] == "ASC" )  { $result['chevron_']  = "triangle-top";    $result['chevTitle'] = "По возростанию";}  //1
        if ( $this->assist['sortDirect'] == "DESC" ) { $result['chevron_']  = "triangle-bottom"; $result['chevTitle'] = "По убыванию";} //2

        if ( $this->assist['reg'] == "number_3d" )	 $result['showsort'] = "№3D";
        if ( $this->assist['reg'] == "vendor_code" ) $result['showsort'] = "Арт.";
        if ( $this->assist['reg'] == "date" ) 		 $result['showsort'] = "Дате";
        if ( $this->assist['reg'] == "status" ) 	 $result['showsort'] = "Статусу";
        if ( $this->assist['reg'] == "model_type" )  $result['showsort'] = "Типу";

        $result['activeSquer'] = "";
        $result['activeList']  = "";
        $result['activeSelect'] = "";

        if ( $this->assist['drawBy_'] == 1 ) $result['activeSquer'] = "btnDefActive";
        if ( $this->assist['drawBy_'] == 2 ) $result['activeList']  = "btnDefActive";

        $result['collectionName'] = $this->assist['collectionName'];

        return $result;
    }

    private function setObjectQuery()
    {
        $objectQuery = Stock::find()
            ->select('id,number_3d,vendor_code,collections,author,model_type,status,labels,date');

        if ($this->assist['regStat'] != "Нет")
        {
            $objectQuery->andWhere(['=','status', $this->assist['regStat'] ]);
        }
        if ($this->assist['regLabels'] != "Нет")
        {
            $objectQuery->andWhere(['like','labels', $this->assist['regLabels'] ]);
        }
        $objectQuery->orderBy("{$this->assist['reg']} {$this->assist['sortDirect']}");

        return $objectQuery;
    }

	public function getModelsFromStock()
	{
        $session = Yii::$app->session;
        $session->remove('nothingFound');
        $session->remove('searchFor');

        $objectQuery = $this->setObjectQuery();

        if ( $this->assist['collectionName'] != 'Все Коллекции' )
        {
            $objectQuery->andWhere(['=','collections',$this->assist['collectionName'] ]);
        } else {
            $objectQuery->andWhere(['<>','collections','Детали']);
        }

        if ( !$objectQuery->exists() )
        {
            $session->set('nothingFound','По данному запросу пусто.');
        }

        return $this->pagination($objectQuery);
	}

	public function getModelsFromSearch()
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;

        if ( $session->get('re_search') === true )
        {
            $searchFor = $session->get('searchFor');
        }
        if ( filter_has_var(INPUT_POST, 'searchFor')
            || filter_has_var(INPUT_GET, 'searchFor') )
        {
            $searchFor = $request->post('searchFor') ? strip_tags( trim($request->post('searchFor')) ) : strip_tags( trim($request->get('searchFor')) );
        }

        // если в строке пусто то удаляем все переменные поиска и вернемся на коллекцию
        if ( empty($searchFor) )
        {
            $session->set('re_search',false);
            return $this->getModelsFromStock();
        }

        $objectQuery = $this->setObjectQuery();
        $objectQuery
            ->andWhere('number_3d LIKE :search OR vendor_code LIKE :search OR collections LIKE :search OR author LIKE :search OR modeller3D LIKE :search OR labels LIKE :search OR status LIKE :search OR description LIKE :search')
            ->addParams([':search' => "%$searchFor%"]);

        if ( $this->assist['searchIn'] === 2 && $this->assist['collectionName'] != "Все Коллекции" )
        {
            $objectQuery->andWhere(['=','collections',$this->assist['collectionName'] ]);
        }

        // если поиск что-то нашел - строку поиска запишем в имя коллекции для показа на главной
        // иначе создаем сессию-Флаг nothingFound
        $session->set('searchFor',$searchFor);

        //Если есть что искать, searchFor не пуст, то флаг репоиска должен быть в True
        //иначе не будет работать пагинация для поиска
        $session->set('re_search',true);

        if ( $objectQuery->exists() )
        {
            $session->remove('nothingFound');
        } else {
            $session->set('nothingFound', 'Ничего не найдено.');
        }

        // выкоючаем показ моделей
        $sm = $session->get('selectionMode');
        $sm['getModels'] = false;
        $this->selectionMode['getModels'] = false;
        $session->set('selectionMode',$sm);

        return $this->pagination($objectQuery);
    }

    public function getModelsFromSelectionMode()
    {
        $session = Yii::$app->session;
        $session->remove('nothingFound');
        $session->remove('searchFor');

        $modelIds = [];
        foreach ( $this->selectionMode['models'] as $key => &$model )
        {
            $modelIds[] = $key;
        }

        $objectQuery = $this->setObjectQuery();

        $objectQuery->andWhere(['in', 'id', $modelIds]);

        if ( !$objectQuery->exists() )
        {
            $session->set('nothingFound','По данному запросу пусто.');
        }

        return $this->pagination($objectQuery);
    }

    public function pagination($objectQuery)
    {
        if ( empty($objectQuery) ) return [];

        $this->countPos = $objectQuery->count();
        $pages = new Pagination(['totalCount' => $this->countPos,'pageSize' => $this->assist['maxPos']]);
        $models = $objectQuery->asArray()->with('images','stl_files')->offset($pages->offset)->limit($pages->limit)->all();
        $this->pages = $pages;

        return $models;
    }
	
	public function getModelsByRows()
	{
	    /*
		$result = array();
		$result['posIter'] = count($this->row); // кол-во всех моделей

		$complArray = $this->countComplects();

		$this->wholePos = $result['wholePos'] = count($complArray); // кол-во комплектов
		
		for ( $i = $this->assist['page']*$this->assist['maxPos']; $i < ($this->assist['page'] + 1)*$this->assist['maxPos']; $i++ ) {
			
			if ( !isset($complArray[$i]['id']) || empty($complArray[$i]['id']) ) continue;
			
			$complIterShow = $i+1;
			$thisVC = !empty($complArray[$i]['vendor_code']) ? "&#8212; Артикул: <b>{$complArray[$i]['vendor_code']}</b>" : "";
			$result['showByRows'] .= "<div class=\"col-xs-12\">";
			$result['showByRows'] .= "<div class=\"row complectRow\">";
			$result['showByRows'] .= "
				<center>
					<h4 class=\"margMinus\">
						<span class=\"pull-left\">$complIterShow. &nbsp;&nbsp;&nbsp;Коллекция: <b>&laquo;{$complArray[$i]['collection']}&raquo;</b></span>
						<span>№3D: <b>{$complArray[$i]['number_3d']}</b> $thisVC</span>
						<span class=\"pull-right\">{$complArray[$i]['modeller3D']}</span>
					</h4>
					<div class=\"clearfix\"></div>
				</center>
			";
			
			// вывод моделей в строке
			foreach( $complArray[$i]['id'] as &$value ){
				$result['showByRows'] .= $this->drawModel( $value, true );
				$result['iter']++; // счетчик отрисованных моделей в комплекте
			}
			$result['showByRows'] .= 	"</div>";
			$result['showByRows'] .= "</div>";
			$result['ComplShown']++; // счетчик отрисованных комплектов
		}
		return $result;
	    */
	}
	public function getModelsByTiles()
	{
		$result = [];

        ob_start();
		foreach ( $this->row as $model )
		{
            $this->drawModel( $model, false );
		}

        $result['showByTiles'] = ob_get_contents();
        ob_end_clean();

		return $result;
	}
	
	private function drawModel(&$row, $comlectIdent=false)
	{
        $session = Yii::$app->session;
		//по дефолту (плиткой)
		$vc_show = "";
		if ( !empty($row['vendor_code']) ) $vc_show = " | ".$row['vendor_code'];
		$col_md = 2;

		// если смотрим по комплектам
		if ( $comlectIdent === true )
		{
			$col_md = 3;
			$showN3DandVC = "";
		}

		// формируем путь к главной картинке
        $showimg = "";
		foreach ( $row['images'] as $image )
		{
			if ( !empty($image['main']) )
			{
                $showimg = $row['number_3d'].'/'.$row['id'].'/images/'.$image['img_name'];
                break;
			}
		}
		//debug($showimg,'',1);
        // file_exists работает только с настоящим путём!! не с HTTP
		
		if ( !file_exists(_stockDIR_.$showimg) )
		{
		    $showimg = "http://huf.db/Stock/" . "default.jpg";
		} else {
            $showimg = "http://huf.db/Stock/" . $showimg;
        }
		//debug($showimg,'showimg');
        // покажем что есть стлка
        $btn3D = "hidden";
		if ( count( $row['stl_files'] ) ) $btn3D = "";

        $drawEdit['editBtn'] = "hidden";
        $drawEdit['url'] = "/";
        $access = (int)$session['user']['access'];
        // смотрим отрисовывать ли нам кнопку едит
		if ( $access > 0 )
		{
            $url = Url::to(['/database/editmodel/','id'=>$row['id']]);
			// доступ на редактирование всех моделей, будет ограничен в контроллере Edit
			if ( $access > 0 && $access !== 2 )
            {
                $drawEdit['editBtn'] = "";
                $drawEdit['url'] = $url;
            }
			// доступ только где юзер 3д моделлер или автор
			if ( $access === 2 )
			{
				$userRowFIO = $this->user['fio'];
				$authorFIO = $row['author'];
				$modellerFIO = $row['modeller3D'];

                $drawEdit['editBtn'] = "hidden";
                $drawEdit['url'] = "/";
				
				if ( stristr($authorFIO, $userRowFIO) !== FALSE || stristr($modellerFIO, $userRowFIO) !== FALSE )
				{
                    $drawEdit['editBtn'] = "";
                    $drawEdit['url'] = $url;
				} 
			}
		}

		$status = $this->general->getStatus($row);
		$statusStr = 'hidden';
		if ( isset($status['stat_name']) ) $statusStr = "";
		$labels = $this->general->getLabels($row['labels']);
		
		$checkedSM = self::selectionMode($row['id']);
		
		// Укорочение длинны типа модели
		$modTypeCount = mb_strlen($row['model_type']);
		if ( $modTypeCount > 14 )
		{
			$modTypeStr = mb_substr($row['model_type'], 0, 11);
			$modTypeStr.= "...";
		} else {
			$modTypeStr = $row['model_type'];
		}

		include _webDIR_ . "includes/main/drawModel.php";
	}

	private static function selectionMode($id)
	{
        $session = Yii::$app->session;

		$defRes = ['inptAttr'=>'','class'=>'glyphicon-unchecked','active'=>'hidden'];
		if ( $session['selectionMode']['activeClass'] == "btnDefActive" )
		{
			$defRes['active'] = "";
			
			$selectedModels = $session['selectionMode']['models'];
			if ( !empty($selectedModels) ) {
				
				if ( array_key_exists($id, $selectedModels) ) {
					$defRes['inptAttr'] = "checked";
					$defRes['class'] = "glyphicon-check";
					
					return $defRes;
				}
			}
		}
		return $defRes;
	}
	
	public function countComplects()
	{

		$numRows = count($this->row);
		$savedrow = array();
		$complects = array();
		$cIt = 0;
		
		for ( $i = 0; $i < $numRows; $i++ ) {
			if ( empty($this->row[$i]['number_3d']) ) continue;
			$number_3d = $this->row[$i]['number_3d'];
			
			foreach ( $savedrow as &$value ) {
			// проверяем есть ли этот номер в массиве. если есть то пропускаем все такие номера, они уже посчитаны
				if ( $value == $number_3d ) continue(2);
			}

			for ( $j = 0; $j < $numRows; $j++ ) {
				
				$model_type = $this->row[$j]['model_type'];
				
				// если совпадают - значит это комплект
				if ( $number_3d == $this->row[$j]['number_3d'] ) {
					
					$id = $this->row[$j]['id'];
					$complects[$cIt]['number_3d'] = $this->row[$j]['number_3d'];
					$complects[$cIt]['vendor_code'] = $this->row[$j]['vendor_code'];
					$complects[$cIt]['modeller3D'] = $this->row[$j]['modeller3D'];
					$complects[$cIt]['collection'] = $this->row[$j]['collections'];
					
					$complects[$cIt]['id'][$id]['id'] = $this->row[$j]['id'];
					$complects[$cIt]['id'][$id]['number_3d'] = $this->row[$j]['number_3d'];
					$complects[$cIt]['id'][$id]['author'] = $this->row[$j]['author'];
					$complects[$cIt]['id'][$id]['modeller3D'] = $this->row[$j]['modeller3D'];
					$complects[$cIt]['id'][$id]['model_type'] = $this->row[$j]['model_type'];
					$complects[$cIt]['id'][$id]['labels'] = $this->row[$j]['labels'];
					$complects[$cIt]['id'][$id]['status'] = $this->row[$j]['status'];
					$complects[$cIt]['id'][$id]['date'] = $this->row[$j]['date'];
					
					if (  $this->toPdf === true ) {
						$complects[$cIt]['model_type'][$id]['id'] = $id;
						$complects[$cIt]['model_type'][$id]['model_type'] = $model_type;
						$complects[$cIt]['model_type'][$id]['images'] = $this->get_Images_FromPos($id);
						$complects[$cIt]['model_type'][$id]['dop_VC'] = $this->get_DopVC_FromPos($id);
						$complects[$cIt]['model_type'][$id]['model_weight'] = $this->row[$j]['model_weight'];
						$complects[$cIt]['model_type'][$id]['status'] = $this->row[$j]['status'];
					}
					
					$savedrow[] = $number_3d; // сохранем номер в массив, как посчитанный
					
				}
			}
			$cIt++;
		}
		return $complects;
	}
	
}
<?php
use yii\helpers\Url;
use app\models\User;

$this->title = 'Add New Model';

$tt=time();
$this->registerJsFile("@web/js/add-edit/Validator.js?v=$tt",['depends' => [\app\assets\AppAsset::class]]);
$this->registerJsFile("@web/js/add-edit/AddEdit.js?v=$tt",['depends' => [\app\assets\AppAsset::class]]);
$this->registerJsFile("@web/js/add-edit/HandlerFiles.js?v=$tt",['depends' => [\app\assets\AppAsset::class]]);
//$this->registerCssFile("@web/css/view/view.css?v=$tt");
//debug($datafileSizes,'datafileSizes',1);
//debug($sevData,'stockData',1);

$modelStatus = (int)$stockData['model_status']; 
?>
<h1 class="main-title-w3layouts mb-2 text-center">Добавить новую модель</h1>

<!-- TEXT DATA -->
<div class="outer-w3-agile mt-3">
    <h4 class="tittle-w3-agileits mb-4">Общие данные</h4>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="number_3d">№3Д</label>
                <div class="input-group mb-2">
                    <div class="input-group-prepend">
                        <div class="input-group-text text-light badge-light"><i class="fa-regular fa-square-full"></i></div>
                    </div>
                    <input type="text" editable class="form-control" name="number_3d" id="number_3d" value="<?=$stockData['number_3d']?>" placeholder="">
                </div>
            </div>
            <div class="form-group col-md-6">
                <label for="modeller3d">3Д модельер</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text badge-light ">
                            <i class="fa-regular fa-square-full"></i>
                        </div>
                    </div>
                    <input type="text" editable class="form-control" name="modeller3d" id="modeller3d" value="<?=$stockData['modeller3d']?>" placeholder="" >
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></button>
                        <div class="dropdown-menu">
                            <?php foreach ($sevData['modeller3d'] as $key => $m3d): ?>
                            <a class="dropdown-item" style="cursor: pointer;" elemToAdd><?php echo $m3d['name'] ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="model_type">Вид модели</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text badge-light"><i class="fa-regular fa-square-full"></i></div>
                    </div>
                    <input type="text" editable class="form-control" name="model_type" id="model_type" value="<?=$stockData['model_type']?>"placeholder="" >
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></button>
                        <div class="dropdown-menu">
                            <?php foreach ($sevData['model_type'] as $key => $mtype): ?>
                            <a class="dropdown-item" style="cursor: pointer;" elemToAdd><?php echo $mtype['name'] ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-3">
                <label for="size_range">Размеры</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text badge-light"><i class="fa-regular fa-square-full"></i></div>
                    </div>
                    <input type="text" editable class="form-control" name="size_range" id="size_range" value="<?=$stockData['size_range']?>" placeholder="">
                </div>
            </div>
            <div class="form-group col-md-3">
                <label for="model_weight">Общий вес 3д модели</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text badge-light"><i class="fa-regular fa-square-full"></i></div>
                    </div>
                    <input type="text" editable class="form-control" value="<?=$stockData['model_weight']?>" name="model_weight" id="model_weight" placeholder="">
                </div>
            </div>
            <div class="form-group col-md-3">
                <label for="model_price">Цена 3д</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text badge-light"><i class="fa-regular fa-square-full"></i></div>
                    </div>
                    <input type="text" editable class="form-control" value="<?=$stockData['model_cost']?>" name="model_cost" id="model_cost" placeholder="">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="customers">Заказчики</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text badge-light"><i class="fa-regular fa-square-full"></i></div>
                </div>
                <input type="text" editable class="form-control" value="<?=htmlspecialchars($stockData['client'])?>" name="client" id="client" aria-label="" >
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></button>
                    <div class="dropdown-menu">
                        <?php foreach ($sevData['client'] as $key => $value): ?>
                        <a class="dropdown-item" style="cursor: pointer;" elemToAdd><?php echo $value['name'] ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
</div>

<!--// MATERIALS -->
<div class="outer-w3-agile mt-3 tableMats" id="tableAllMats">
    <h4 class="tittle-w3-agileits mb-4">Материалы <button type="button" class="btn btn-info" id="addMats"><i class="fa-solid fa-plus"></i></button></h4>
    <?php foreach ($stockData['materials'] as $material): ?>
    <?php require _webDIR_ . 'includes/add-edit/mats_protoRow.php'; ?>
    <?php endforeach; ?>
</div>

<!--// GEMS -->
<div class="outer-w3-agile mt-3 tableGems">
    <h4 class="tittle-w3-agileits mb-4">Камни <button type="button" class="btn btn-info" id="addGems"><i class="fa-solid fa-plus"></i></button></h4>
    <?php foreach ($stockData['gems'] as $gem): ?>
    <?php require _webDIR_ . 'includes/add-edit/gems_protoRow.php'; ?>
    <?php endforeach; ?>
</div>

<div class="outer-w3-agile mt-3 pt-3 pb-3 <?=($modelStatus === 2)?"d-none":"" ?>">
    <div class="card-deck text-center row">
        <div class="card box-shadow col-xl-12 col-md-12">
            <div class="card-header p-5 border border-secondary rounded" id="drop-area"  title="Загрузить Файлы">
                <p>Загрузить файлы можно перетащив их в эту область.</p>
                <p> Форматы: .jpg .jpeg .png .gif .webp .stl .mgx .3dm .ai .dxf .obj</p></br>
                <button type="button" id="addImageFiles" class="btn btn-outline-secondary btn-block"><i class="far fa-images"></i> Выбрать файлы</button>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <!-- IMAGES -->
        <div class="outer-w3-agile col-xl mt-3 mr-xl-3 p-2">
            <h4 class="tittle-w3-agileits">Картинки</h4>
            <hr>
            <div class="row justify-content-center pl-2 pr-2" id="picts">
                <?php foreach ($stockData['images'] as $image): ?>
                <?php require _webDIR_ . 'includes/add-edit/img_protoRow.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <!--// IMAGES -->

        <!-- 3D Files -->
        <div class="outer-w3-agile col-xl mt-3 mr-xl-3 p-2">
            <h4 class="tittle-w3-agileits">
                3Д Файлы: 
                <small>(Origin: <?=$datafileSizes['origin']?>) (Zipped: <?=$datafileSizes['zip']?>)</small>
            </h4>
            <hr>
            <div class="card-body p-1 pt-0">
                <div class="list-group" id="d3-files-area">
                    <?php foreach ($stockData['d3_files'] as $datafile): ?>
                    <?php require _webDIR_ . 'includes/add-edit/datafile_protoRow.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!--// 3D Files -->
    </div>
</div>
<div class="outer-w3-agile mt-3">
    <div class="form-group">
        <label for="tags">Теги</label>
        <div class="btn-group-toggle" data-toggle="buttons" id="hashtags">
            <?php foreach ($sevData['hashtag'] as $key => $value): ?>
                <label class="btn btn-outline-info shadow-sm mb-1 <?php if ($value['checked'] == 1) echo "active"?>">
                    <input type="checkbox" name="hashtags" <?php if ($value['checked'] == 1) echo "checked"?> value="<?=$value['name']?>" /><span><?php echo $value['name'] ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <label for="tags"> </label>
        <textarea class="form-control" onchange="hashtagByText(this)" name="hashtags" id="hashtags" rows="1" required=""></textarea>
    </div>
    <div class="form-group">
        <label for="description">Примечания</label>
        <textarea class="form-control" editable name="description" id="description" rows="2"><?=$stockData['description'] ?></textarea>
    </div>
    <div class="form-group row">
        <div class="col-sm-5">
            <span>Дата создания 3д модели: </span>
            <input class="form-control" style="width: 13rem;" type="date" name="create_date" editable value="<?=$stockData['create_date'] ?>" />
        </div>
        <div class="col-sm-2">
            <br/>
            <?php if ( $modelStatus !== 2 ): ?>
                <a class="btn btn-outline-danger" href="<?=Url::to(["site/view", 'id'=>$stockData['id']])?>" role="button">Просмотр</a>
            <?php endif; ?>
        </div>
         <div class="col-sm-5 float-right">
            <div class="float-right"><span>Добавил: <?=User::getUsernameByID($stockData['creator_id'])?></span><span>Дата добавления 3д модели в базу: </span>
                <input class="form-control" readonly type="date" name="date" value="<?=$stockData['date'] ?>" />
            </div>
        </div>
    </div>
    <div class="form-group row" id="publishRow">
        <div class="col-sm-5 float-left">
            <?php if ( $modelStatus === 0 ): ?>
                <i class="text-danger">Модель не видна в поиске.</i></br>
                <i class="text-danger">Eсли все данные верны, опубликуйте её!</i></br>
                <button type="button" class="btn btn-success" data-publish="pub">Опубликовать</button>
            <?php endif; ?>
            <?php if ( $modelStatus === 1 ): ?>
                <i class="text-success">Модель опубликованв и доступна в поиске!</i></br>
            <?php endif; ?>
        </div>
        <div class="col-sm-2">
            <i class="text-danger"></i></br>
            <i class="text-danger"></i></br>
             <?php if ( $modelStatus === 1 ): ?>
                <button type="button" class="btn btn-outline-secondary text-center" data-publish="excl">Исключить</button>
            <?php endif; ?>
        </div>
        <div class="col-sm-5 float-right">
            <i class="text-danger"><?=($modelStatus === 2)?"Модель была удалена!":"" ?></i></br>
            <i class="text-danger"><?=($modelStatus === 2)?"Что бы восстановить её, обратитесь к администратору.":"" ?></i></br>
            <?php if ( $modelStatus !== 2 ): ?>
                <button type="button" class="btn btn-outline-danger float-right" data-publish="del">Удалить</button>
            <?php endif; ?>
        </div>
    </div>
</div>
<input type="hidden" id="modelID" value="<?=$modelID?>" />
<input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" />

<?php require _webDIR_.'includes/add-edit/protoRows.php'?>
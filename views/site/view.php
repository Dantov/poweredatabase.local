<?php
use yii\helpers\Url;
use app\models\User;

$this->title = $model['number_3d'] . '-' . $model['model_type'];

//debug($model,1,1);
$tt=time();
$this->registerCssFile("@web/css/view/view.css?v=$tt");
$this->registerJsFile("@web/js/view/imageViewer_v2.js?v=$tt");
$imgEncode = json_encode($model['images'], JSON_UNESCAPED_UNICODE);
$imgJs = <<<JS
    window.addEventListener('load',function() {
      new ImageViewer($imgEncode).init();
    }, false);
JS;
$this->registerJs($imgJs);

?>
<div class="row justify-content-center bg-light mb-2">
    <div class="col-sm">
        <div class="d-flex justify-content-between bg-dots">
            <div class="p-1 bg-light"><span>№3D:</span></div>
            <div onclick = "copyValueToClipBoard(this)" class="p-1 bg-light cursorPointer text-danger font-weight-bold" data-toggle="tooltip" data-placement="top" title="Копировать" id="num3d"><?=$model['number_3d']?></div>
        </div>
        <div class="d-flex justify-content-between bg-dots">
            <div class="p-1 bg-light"><span>Дата создания 3Д:</span></div>
            <div onclick = "copyValueToClipBoard(this)" class="p-1 bg-light cursorPointer text-danger font-weight-bold" data-toggle="tooltip" data-placement="top" title="Копировать" id="create_date"><?=$mv->dateConvert($model['create_date'])?></div>
        </div>
    </div>
    <div class="col-sm">
        <div class="d-flex justify-content-between bg-dots" id="complects">
            <div class="p-1 bg-light"><span>В Комплекте:</span></div>
            <div class="p-1 bg-light text-primary"><b><a imgtoshow="" class="text-primary" href="index.php?id=60"></a></b></div>
        </div>
    </div>
</div>

<div class="row justify-content-center mb-2">
    <div class="col-sm-12 col-md-6 bg-light pr-0" id="images_block">
        <div class="row">
            <div class="d-none d-sm-block col-sm-12 p-0 mb-2" id="mainImage">
                <div class="mainImage" data-posid="<?=$model['id']?>" data-id="<?=$model['mainimageID']?>" data-name="<?=$model['mainimage']?>" style="background-image: url(/web/stock/<?=$model['id']?>/images/<?=$model['mainimage']?>);"></div>
            </div>
            <div class="col-12 pl-0">
                <div class="row p-0 m-0 dopImages" id="bottomDopImages">
                <?php foreach( $model['images'] as $image ): ?>
                    <div class="col-12 col-sm-6 col-md-3 p-0">
                        <div class="ratio border border-<?=$image['status']?"primary":"light"?> cursorPointer">
                            <div class="ratio-inner ratio-4-3">
                                <div class="ratio-content">
                                    <img class="imageSmall<?=$image['status']?" activeImage":""?>" data-posid="<?=$image['pos_id']?>" data-id="<?=$image['id']?>" src="/web/stock/<?=$image['pos_id']?>/images/<?=$image['name']?>" width="100%">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
            <?php require "includes/view/datafiles.php"?>
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-md-6 bg-light position-relative" id="descriptions">
        <div class="pt-1 fontsView">
            <i class="fas fa-gem"></i>
            <span>Заказчик: </span>
            <strong>
                <i><a class="text-primary" href="" id="collection"><?=$model['client']?></a></i>
            </strong>
        </div>
        <div class="fontsView">
            <span class="float-left">
                <i class="fas fa-user-edit"></i>
                Автор:
                <strong>
                    <span></span>
                </strong>
            </span>
            <span class="float-right">
                <strong>
                    <span><?=$model['modeller3d']?></span>
                </strong>
                 :3D модельер
                <i class="fas fa-user-cog"></i>
            </span>
        </div>
        <div class="clearfix"></div>
        <hr>
        <div class=""><b>Хештеги:</b></div>
        <div class="d-flex justify-content-left ">
            <div class="">
            <?php foreach( $model['hashtags'] as $htag ): ?>
                <span class="badge badge-<?=$model['hashtags_colors'][random_int(0,6)]?> p-2 mb-1"><i class="fas fa-tag"></i> <?=$htag?></span>
            <?php endforeach; ?>
            </div>
        </div>
        <div class="d-flex justify-content-between bg-dots fontsView">
            <div class="p-1 bg-light">
                <i class="far fa-eye" data-toggle="tooltip" data-placement="top" title="Вид модели"></i>
                <span class="d-none d-lg-inline">Вид модели</span>
            </div>
            <div class="p-1 bg-light" id="modelType"><b><?=$model['model_type']?></b></div>
        </div>
        <div class="d-flex justify-content-between bg-dots fontsView">
            <div class="p-1 bg-light">
                <i class="fas fa-balance-scale-left"></i>
                <span class="d-none d-lg-inline">Вес в 3D</span>
            </div>
            <div class="p-1 bg-light">
                <b><?=$model['model_weight']?> гр.</b>
            </div>
        </div>
        <div class="d-flex justify-content-between bg-dots fontsView">
            <div class="p-1 bg-light">
                <i class="fa-solid fa-hand-holding-dollar"></i>
                <span class="d-none d-lg-inline">Стоимость 3Д + печать</span>
            </div>
            <div class="p-1 bg-light">
                <b><span><?=$model['model_cost']?></span></b>
            </div>
        </div>
        <div class="d-flex justify-content-between bg-dots fontsView">
            <div class="p-1 bg-light">
                <i class="fa-solid fa-ring"></i>
                <span class="d-none d-lg-inline">Размерный Ряд</span>
            </div>
            <div class="p-1 bg-light">
                <b>
                    <?php foreach( $model['size_range'] as $sr ): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary"><?=$sr?></button>
                    <?php endforeach; ?>
                </b>
            </div>
        </div>
        <hr>

        <div class="d-none d-lg-block">
            <?php require "includes/view/gems.php"?>
        </div>
        <div class="d-none d-lg-block">
            <?php require "includes/view/materials.php"?>
        </div>
        <div class="d-none d-lg-block" id="notes">
            <div class="alert alert-light" role="alert">
                <h5 class="alert-heading"><i class="fas fa-comment-alt"></i> Примечания:</h5>
                <p><?=$model['description']?></p>
            </div>
        </div>
    </div>
</div>

<div class="row bg-light mb-2 d-lg-none pt-1" id="tablesSM">
    <div class="col-12">
        <?php require "includes/view/gems.php"?>
    </div>
    <div class="col-12">
        <?php require "includes/view/materials.php"?>
    </div>
</div>

<div class="row d-lg-none" id="notesSM">
    <div class="col">
        <div class="alert alert-light" role="alert">
            <h5 class="alert-heading"><i class="fas fa-comment-alt"></i> Примечания:</h5>
            <p><?=$model['description']?></p>
        </div>
    </div>
</div>

<div class="row bg-light pb-2 pt-2" id="bottomRow">
    <div class="col">
        <div class="float-left">
            <div class="input-group">
                <div class="input-group-prepend">
                    <a href="<?=Url::previous()?>" role="button" class="btn btn-outline-secondary">
                        <i class="fas fa-caret-left"></i>
                        <span>Назад</span>
                    </a>
                </div>
                <div class="input-group-append">
                    <a href="<?=Url::to(["site/add", 'id'=>$model['id']])?>" role="button" class="btn btn-outline-info">
                        <i class="fas fa-pencil-alt"></i>
                        <span>Редактировать</span>
                    </a>
                </div>
            </div>
        </div>
        <small class="float-right p-2">
            <span title="Создатель">
                Добавил:&nbsp;<i><?=User::getUsernameByID($model['creator_id'])?> - </i>
            </span>
            <span title="Дата добавления в базу:">
                <i><?=$mv->dateConvert($model['date'])?></i>
                <i class="fas fa-calendar-alt"></i>
            </span>
        </small>
    </div>
    <div class="clearfix"></div>
</div>
<?php require 'includes/view/imageWrapper.php'; ?>

<div class="modal fade" id="imageViewer" style="height: 100%; width: 100%;" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered m-auto" style="height: 100%; max-width: 100%">
        <div class="modal-content p-0 m-0 imageViewer bg-transparent rounded-0">
            <div class="d-flex flex-row-reverse flex-row">
                <div class="p-2 pl-3 pr-3 bd-highlight rightPanel text-info closeImageViewer" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </div>
                <div class="p-2 pl-3 pr-3 bd-highlight text-info sizePlus rightPanel">
                    <i class="fas fa-search-plus"></i>
                </div>
                <div class="p-2 pl-3 pr-3 bd-highlight text-info sizeMinus rightPanel">
                    <i class="fas fa-search-minus"></i>
                </div>
            </div>
            <div class="d-flex flex-row bottomImgRow cursorPointer">
            </div>
        </div>
    </div>
</div>


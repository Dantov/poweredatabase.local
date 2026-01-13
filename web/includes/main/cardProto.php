<?php
use yii\helpers\Url;
?>
<div class="card bg-light mb-1 mainCard" style="width: <?=$session->get('tilesControlSize')?>rem;">
    <div class="card-header p-1 cursorPointer text-truncate bg-secondary text-white">
        <small onclick = "copyValueToClipBoard(this)" class="float-left text-truncate" data-toggle="tooltip" data-placement="top" title="№3D"><?=$model['number_3d']?></small>
        <small onclick = "copyValueToClipBoard(this)" class="float-right text-truncate" data-toggle="tooltip" data-placement="top" title="Дата создания"><?=$main->dateConvert($model['create_date'])?></small>
        <div class="clearfix"></div>
    </div>
    <a href="<?=Url::to(['site/view','id'=>$model['id']])?>">
        <div class="ratio">
            <div class="ratio-inner ratio-4-3">
                <div class="ratio-content">
                    <img src="web/stock/<?=$model['id']?>/images/<?=$model['mainimage']?>" class="card-img-top" alt="...">
                </div>
                <a class="btn btn-info btn-sm editBtnMain" href="<?=Url::to(['site/add','id'=>$model['id'] ])?>" role="button" data-toggle="tooltip" data-placement="bottom" title="Редактировать">
                    <i class="fas fa-pencil-alt"></i>
                </a>
            </div>
        </div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item p-1" style="font-size: small;">
                <small class="float-left">Тип:</small>
                <small class="float-right"><b><?=$model['model_type']?></b></small>
                <div class="clearfix"></div>
            </li>
        </ul>
    </a>
</div>
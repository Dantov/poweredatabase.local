<?php
use yii\helpers\Url;
use app\models\User;
?>
<div class="card bg-light mb-1 mainCard" style="width: <?=$session->get('tilesControlSize')?>rem;">
    <div class="card-header p-1 cursorPointer text-truncate bg-secondary text-white text-center">
        <small data-toggle="tooltip" title="<?=htmlentities($model['client'])?>" data-placement="top"><?=htmlentities($model['client'])?></small>
        <div class="clearfix"></div>
    </div>
    <a href="<?=Url::to(['site/view','id'=>$model['id']])?>">
        <div class="ratio">
            <div class="ratio-inner ratio-4-3">
                <?php $imgname = isset($model['mainimgprev'])?$model['mainimgprev']:$model['mainimage'] ?>
                <div class="ratio-content card-main-image" style="background: url('stock/<?=$model['id']?>/images/<?=$imgname?>');"></div>
                <?php if ( $model['isEditBtn'] ): ?>
                <a class="btn btn-outline-secondary btn-sm editBtnMain" href="<?=Url::to(['site/add','id'=>$model['id'] ])?>" role="button" data-toggle="tooltip" data-placement="bottom" title="Редактировать">
                    <i class="fas fa-pencil-alt"></i>
                </a>
                <?php endif; ?>
                <?php if ( User::hasPermission('jewelbox') ): ?>
                <button class="btn btn-dark btn-sm jewelboxBtnMain" role="button" data-id="<?=$model['id']?>" data-toggle="tooltip" data-placement="bottom" title="Добавить в Шкатулку">
                    <i class="fa-solid fa-basket-shopping"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item p-1" style="font-size: small;">
                <small onclick="copyValueToClipBoard(this)" class="float-left text-truncate" data-toggle="tooltip" data-placement="top" title="Дата создания"><?=$main->dateConvert($model['create_date'])?></small>
                <small class="float-right"><b><?=$model['model_type']?></b></small>
                <small onclick = "copyValueToClipBoard(this)" class="float-right text-truncate" data-toggle="tooltip" data-placement="top" title="№3D"><?=$model['number_3d']?> |&nbsp;</small>
                <div class="clearfix"></div>
            </li>
        </ul>
    </a>
</div>
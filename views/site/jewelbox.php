<?php
use yii\helpers\{Html,Url};
use app\models\User;

$this->title = 'Jewel Box';
?>
<div class="outer-w3-agile col-xl">
    <div class="work-progres">
        <h4 class="tittle-w3-agileits mb-4">Шкатулка для <?= User::getFIO()?></h4>
        <?php //debug($storedModels)?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr align="center">
                        <th>Изделие</th>
                        <th>Вид модели</th>
                        <th>Клиент</th>
                        <th>Ссылка</th>
                        <th>Комментарий</th>
                        <th></th>
                        <th>Стоимость</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach( $storedModels as $k => $storedModel ):?>
                    <tr align="center">
                        <td><img src="<?="/" . $storedModel['mainimage']?>" width="70 rem;"></td>
                        <td><?=$storedModel['model_type']?></td>
                        <td><?= htmlentities($storedModel['client'])?></td>
                        <td><a class="btn btn-primary btn-sm" href="<?=Url::to(['/site/view/','id'=>$storedModel['id']])?>" role="button">Перейти</a></td>
                        <td>
                            <h5><span class="badge badge-pill badge-secondary jbcomment"><?=$storedModel['comment']?></span></h5>
                        </td>
                        <td>
                            <button type="button" data-id="<?=$storedModel['id']?>" class="btn btn-sm btn-dark editbtnJewelBox" title="Редактировать">
                                <input class="editJBdata" type="hidden" data-img="<?="/" . $storedModel['mainimage']?>" data-link="<?=Url::to(['/site/view/','id'=>$storedModel['id']])?>" data-n3d="<?=$storedModel['number_3d']?>" data-mtype="<?=$storedModel['model_type']?>" data-client="<?=htmlentities($storedModel['client'])?>">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </td>
                        <td>
                            <h5><span class="badge badge-warning"><?= $storedModel['model_cost']?></span></h5>
                        </td>
                        <td>
                            <a type="button" href="<?=Url::to(['site/jewel','box'=>'remove','id'=>$storedModel['id']])?>" class="btn btn-sm btn-danger" title="Удалить"><i class="fa-solid fa-xmark"></i></a>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
</div>
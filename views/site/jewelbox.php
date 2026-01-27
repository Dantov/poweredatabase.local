<?php
use yii\helpers\{Html,Url};
use app\models\User;

$this->title = 'Jewel Box';
?>
<div class="outer-w3-agile col-xl">
    <div class="work-progres">
        <h4 class="tittle-w3-agileits mb-4">Шкатулка для <?= User::getFIO()?></h4>
        <hr>
        <?php //debug($storedModels)?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Изделие</th>
                        <th>Вид модели</th>
                        <th>Клиент</th>
                        <th>Ссылка</th>
                        <th>Комментарий</th>
                        <th>Стоимость</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach( $storedModels as $k => $storedModel ):?>
                    <tr>
                        <td><img src="<?="/" . $storedModel['mainimage']?>" width="50 rem;"></td>
                        <td><?=$storedModel['model_type']?></td>
                        <td><?= htmlentities($storedModel['client'])?></td>
                        <td><a class="btn btn-primary btn-sm" href="<?=Url::to(['/site/view/','id'=>$storedModel['id']])?>" role="button">Перейти</a></td>
                        <td>
                            <span class="badge badge-pill badge-primary"><?= $storedModel['comment']?></span>
                        </td>
                        <td>
                            <span class="badge badge-danger"><?= $storedModel['model_cost']?></span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-dark">Удалить</button>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
use yii\helpers\{Html,Url};
use app\models\User;

$this->title = 'Jewel Box';

$orderStatuses = $allOrders['statuses']??[];
$orderModels = $allOrders['storedmodels']??[];
?>

<div class="work-progres">
    <h4 class="tittle-w3-agileits mb-2">Шкатулка для <?= User::getFIO()?></h4>
    <?php //debug($allOrders,1,1)?>
</div>
<?php if( empty($orderModels) ): ?>
    <div class="outer-w3-agile col-xl pt-3 pb-2 mb-3">
        <h4 class="tittle-w3-agileits mb-2">Пусто</h4>
    </div>
<?php endif; ?>
<?php foreach( $orderModels as $orderID => $storedModels ): ?>
<div class="outer-w3-agile col-xl pt-3 pb-2 mb-3">
    <div class="work-progres">
        <h5 class="tittle-w3-agileits mb-2">Заказ № <?=$orderID?></h5>
        <div class="row mb-2">
            <div class="col-sm-6 col-xs-12">
                <?php $orderStatus = $orderStatuses[$orderID]?>
                <?php if ( $orderStatus == 0 ):?>
                <a type="button" href="<?=Url::to(['site/jewel/','box'=>'sendorder','orderid'=>$orderID])?>" class="btn btn-info btn-sm btn-block"><i class="fa-regular fa-paper-plane"></i> Сформировать заказ (всего моделей: <?=count($storedModels)?>)</a>
                <?php elseif($orderStatus == 1):?>
                <span class="text-danger">Заказ сформирован и отправлен! <br/>Свяжитесь с администратором любым известным вам способом.</span>
                <?php endif;?>
            </div>
            <div class="col-sm-6 col-xs-12">
                <a type="button" href="<?=Url::to(['site/jewel/','box'=>'removeorder','orderid'=>$orderID])?>" class="btn btn-outline-danger-danger btn btn-block"><i class="fa-regular fa-calendar-xmark"></i> Удалить заказ (всего моделей: <?=count($storedModels)?>)</a>
            </div>
        </div>
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
                            <?php //if ( $orderStatus == 0 ):?>
                            <button type="button" data-orderid="<?=$orderID?>" data-id="<?=$storedModel['id']?>" class="btn btn-sm btn-dark editbtnJewelBox" title="Редактировать">
                                <input class="editJBdata" type="hidden" data-img="<?="/" . $storedModel['mainimage']?>" data-link="<?=Url::to(['/site/view/','id'=>$storedModel['id']])?>" data-n3d="<?=$storedModel['number_3d']?>" data-mtype="<?=$storedModel['model_type']?>" data-client="<?=htmlentities($storedModel['client'])?>">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <?php //endif;?>
                        </td>
                        <td>
                            <h5><span class="badge badge-warning"><?=$storedModel['storeprice']?></span></h5>
                        </td>
                        <td>
                            <?php if ( $orderStatus == 0 ):?>
                            <a type="button" href="<?=Url::to(['site/jewel','box'=>'remove','id'=>$storedModel['id'],'orderid'=>$orderID])?>" class="btn btn-sm btn-danger" title="Удалить"><i class="fa-solid fa-xmark"></i></a>
                            <?php endif;?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endforeach;?>
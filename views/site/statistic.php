<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\User;
$name = User::getFIO();
$this->title = $name . ': Statistic';
?>
<div class="alert alert-light text-center" role="alert">
  <h3><?= Html::encode($this->title) ?></h3>
</div>

<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="pills-price-tab" data-toggle="pill" data-target="#pills-prices" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Прайсы</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-models-tab" data-toggle="pill" data-target="#pills-models" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Модели</button>
  </li>
    <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-clients-tab" data-toggle="pill" data-target="#pills-clients" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Клиенты</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-visits-tab" data-toggle="pill" data-target="#pills-visits" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Посещения</button>
  </li>
</ul>

<div class="tab-content" id="pills-tabContent">

    <!-- PRICES -->
    <div class="tab-pane fade show active" id="pills-prices" role="tabpanel" aria-labelledby="pills-price-tab">
        <div class="outer-w3-agile mt-3">
            <h4 class="tittle-w3-agileits mb-4">Прайсы</h4>
            <form class="form-inline mb-2" method="POST" >
                <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                   <div class="btn-group" role="group">
                    <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                      Год: <?=$byYear?>
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-year'=>2022])?>">2022</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-year'=>2023])?>">2023</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-year'=>2024])?>">2024</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-year'=>2025])?>">2025</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-year'=>2026])?>">2026</a>
                    </div>
                  </div>
                  <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                      Месяц: <?=getMonthRu($byMonth??0)?>
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-month'=>1])?>">Январь</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-month'=>2])?>">Февраль</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-month'=>3])?>">Март</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-month'=>4])?>">Апрель</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-month'=>5])?>">Май</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-month'=>6])?>">Июнь</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-month'=>7])?>">Июль</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-month'=>8])?>">Август</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-month'=>9])?>">Сентябрь</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-month'=>10])?>">Октябрь</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-month'=>11])?>">Ноябрь</a>
                      <a class="dropdown-item" href="<?=Url::to(['site/statistic','by-month'=>12])?>">Декабрь</a>
                    </div>
                  </div>

                </div>
                <div class="input-group ml-2">
                    <div class="input-group-prepend">
                        <div class="input-group-text badge-light">С: </div>
                    </div>
                    <input class="form-control" style="width: 13rem;" type="date" name="date_from" value="<?=$dateFrom?>"/>
                </div>

                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text badge-light">По: </div>
                    </div>
                    <input class="form-control" style="width: 13rem;" type="date" name="date_to" value="<?=$dateTo?>"/>
                </div>

                <button type="submit" class="btn btn-light ml-2">Show</button>
                <input type="hidden" name="byDates" value="fromto"/>
                <input type="hidden" name="<?= Yii::$app->request->csrfParam;?>" value="<?= Yii::$app->request->csrfToken;?>"/>
            </form>
            <?php if ( $sumByYear ): ?>
                <div class="alert alert-warning">
                    <p>За весь <?=$byYear?>: <?=$sumByYear?></p>
                </div>
            <?php endif;?>
            <?php if ( $sumByMonth ): ?>
                <div class="alert alert-success">
                    <p>За <?=getMonthRu($byMonth??0)?>: <?=$sumByMonth?></p>
                </div>
             <?php endif;?>
            <?php if ( $dateFrom && $dateTo ): ?>
                <div class="alert alert-info">
                    <p>C: <?=formatDate($dateFrom)?> По: <?=formatDate($dateTo)?></p>
                    <p>=<?=$byDates?></p>
                </div>    
            <?php endif;?>
        </div>
    </div>
    <!-- PRICES END -->

    <!-- MODELS -->
    <div class="tab-pane fade show" id="pills-models" role="tabpanel" aria-labelledby="pills-models-tab">
        <div class="outer-w3-agile mt-3">
            <h4 class="tittle-w3-agileits mb-4">Модели</h4>
            
        </div>
    </div>
    <!-- MODELS END -->

    <!-- CLIENTS -->
    <div class="tab-pane fade show" id="pills-clients" role="tabpanel" aria-labelledby="pills-clients-tab">
        <div class="outer-w3-agile mt-3">
            <h4 class="tittle-w3-agileits mb-4">Клиенты</h4>
            
        </div>
    </div>
    <!-- CLIENTS END -->

    <!-- VISITS -->
    <div class="tab-pane fade show" id="pills-visits" role="tabpanel" aria-labelledby="pills-visits-tab">
        <div class="outer-w3-agile mt-3">
            <h4 class="tittle-w3-agileits mb-4">Посещения</h4>
            
        </div>
    </div>
    <!-- VISITS END -->
</div>
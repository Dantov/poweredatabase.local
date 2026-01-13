<?php

use app\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

AppAsset::register($this);

$this->registerCsrfMetaTags();

$session = Yii::$app->session;
$controller = $this->context;
$jsCONSTANTS = $controller->jsCONSTANTS;
$clients = $controller->clients;
$nonPublished = $controller->nonPublished;
$allHashtags = $controller->hashtags;

$searchFor = $session->has('searchFor')?$session->get('searchFor') : '';

//debug($nonPublished,'$nonPublished',1);
$this->registerJs($jsCONSTANTS,View::POS_HEAD);
?>
<!doctype html>
<?php $this->beginPage() ?>
<html lang="<?= Yii::$app->language ?>">
<head>
    <!-- Required meta tags -->
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../images/favicon.ico?ver=<?=time()?>">
    <script src="../js/const.js?ver=<?=time()?>"></script>
    <script>
        addEventListener("load", function () {
            setTimeout(hideURLbar, 0);
        }, false);

        function hideURLbar() {
            window.scrollTo(0, 1);
        }
    </script>
    <!-- loading-gif Js -->
    <!--// loading-gif Js -->
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
<?php $this->beginBody() ?>

<div class="wrapper">
    <!-- Sidebar Holder START -->
    <nav id="sidebar">
        <div class="sidebar-header text-center">
            <h1>
                <a href="<?=Url::to(['/site'])?>"><img src="/images/Myicon3.png" height="70px" class=""></a>
            </h1>
        </div>
        <ul class="list-unstyled components">
            <li class="activeSB">
                <a href="#showSubmenu1" data-toggle="collapse" aria-expanded="false" class="sidebarMenuA">
                    <i class="fas fa-th-large"></i>
                    База Моделей
                    <i class="fas fa-angle-left fa-pull-right"></i>
                </a>
                <ul class="collapse list-unstyled" id="showSubmenu1">
                    <li><a href="<?=Url::to(['/site/add'])?>"><i class="far fa-file"></i> Создать модель</a></li>
                    <li><a href="<?=Url::to(['/site'])?>"><i class="fas fa-th-large"></i> Отобразить Плиткой</a></li>
                    <li><a href="<?=Url::to(['/site'])?>"><i class="far fa-edit"></i> Режим выделения</a></li>
                    <li><a href="<?=Url::to(['/site'])?>"><i class="far fa-file-alt"></i> Записать в PDF</a></li>
                </ul>
            </li>
            <li>
                <a href="#sortSubmenu1" data-toggle="collapse" aria-expanded="false" class="sidebarMenuA">
                    <i class="far fa-window-restore"></i>
                    Сортировка
                    <i class="fas fa-angle-left fa-pull-right"></i>
                </a>
                <ul class="collapse list-unstyled" id="sortSubmenu1">
                    <li>
                        <a href="#positionsSubmenu1" data-toggle="collapse" aria-expanded="false" class="sidebarMenuA">
                            <i class="fas fa-th"></i>
                            <span>Позиций: <?=$session->get('positionsCount')?></span>
                            <i class="fas fa-angle-left fa-pull-right"></i>
                        </a>
                        <ul class="collapse list-unstyled" id="positionsSubmenu1">
                            <li>
                                <a href="<?=Url::to(['/search/positions-count','v'=>27])?>">27</a>
                            </li>
                            <li>
                                <a href="<?=Url::to(['/search/positions-count','v'=>54])?>">54</a>
                            </li>
                            <li>
                                <a href="<?=Url::to(['/search/positions-count','v'=>108])?>">108</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#hashtagSubmenu1" data-toggle="collapse" aria-expanded="false" class="sidebarMenuA">
                            <i class="fa-solid fa-tags"></i>
                            По Хештегу: <?=$session->get('selectByHashtag')?$session->get('selectByHashtag'):"Нет" ?>
                            <i class="fas fa-angle-left fa-pull-right"></i>
                        </a>
                        <ul class="collapse list-unstyled" id="hashtagSubmenu1">
                            <li><a href="<?= Url::to(['/search/select-by','hashtag'=>123])?>">Нет</a></li>
                            <?php foreach( $allHashtags as $singlehashtag ): ?>
                                <li><a href="<?= Url::to(['/search/select-by','hashtag'=>$singlehashtag['name']])?>"><?=$singlehashtag['name']?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li>
                        <a href="#bySubmenu" data-toggle="collapse" aria-expanded="false" class="sidebarMenuA">
                            <i class="far fa-calendar-alt"></i>
                            По Дате: <?=$session->get('selectFromDate')?$session->get('selectFromDate'):"Нет" ?>
                            <i class="fas fa-angle-left fa-pull-right"></i>
                        </a>
                        <ul class="collapse list-unstyled" id="bySubmenu">
                            <li>
                                <a href="<?=Url::to(['/search/select-by','purgedate'=>1])?>">Нет</a>
                            </li>
                            <li>
                                <a class="cursorPointer">С &nbsp;&nbsp;<input class="bg-dark text-light" type="date" id="createdatefrom" value="<?=$session->get('selectFromDate')?>"/></a>
                            </li>
                            <li>
                                <a class="cursorPointer">По &nbsp;&nbsp;<input class="bg-dark text-light" type="date" id="createdateto" value="<?=$session->get('selectToDate')?>"/></a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#growingSubmenu" data-toggle="collapse" aria-expanded="false" class="sidebarMenuA">
                            <i class="fas fa-sort-amount-up-alt"></i>
                            По: <?=($session->get('selectByOrder')===SORT_ASC)?"Возрастанию":"Убыванию"?>
                            <i class="fas fa-angle-left fa-pull-right"></i>
                        </a>
                        <ul class="collapse list-unstyled" id="growingSubmenu">
                            <li>
                                <a href="<?=Url::to(['/search/select-by','order'=>'ASC'])?>">Возрастанию</a>
                            </li>
                            <li>
                                <a href="<?=Url::to(['/search/select-by','order'=>'DESC'])?>">Убыванию</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>
                <a href="<?=Url::to(['/site/add'])?>"><i class="far fa-file"></i>Создать модель</a>
            </li>
            <li>
                <a href="<?=Url::to(['/site/nomenclature'])?>">
                    <i class="far fa-list-alt"></i>
                    Номенклатура
                </a>
            </li>
            <li>
                <a href="#noticesSubmenu" data-toggle="collapse" aria-expanded="false" class="sidebarMenuA">
                    <i class="far fa-bell"></i>Оповещения
                    <?php if ( count($nonPublished) ): ?>
                        <span class="badge badge-secondary bg-danger"><?=count($nonPublished)?> Новых</span>
                        <i class="fas fa-angle-left fa-pull-right"></i>
                    <?php endif; ?>
                </a>
                <ul class="collapse list-unstyled" id="noticesSubmenu">
                    <?php foreach( $nonPublished as $npModel): ?>
                    <li>
                        <a href="<?=Url::to(['/site/add','id'=>$npModel['id']])?>" class="p-2 border-bottom border-secondary">
                            <span>Добавлена новая модель</span><br>
                            <span>Для <?=htmlentities($npModel['client'])?></span><br>
                            <span class="text-warning">Не опубликована!</span><br>
                            <?php if ( empty($npModel['images']) ): ?>
                                <img src="/pictAssets/web1.webp" width="50px" class="mr-2">
                            <?php else: ?>
                                <img src="/stock/<?=$npModel['id']?>/images/<?=$npModel['mainimage']?>" width="50px" class="mr-2">
                            <?php endif; ?>
                            <span><?=$npModel['number_3d']?></span><br>
                            <span>Добавил: <?=$npModel['creator_name'] . " - " . $npModel['date']?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        </ul>
    </nav>
    <!-- Sidebar END -->



    <!-- Page Content Holder -->
    <div id="content" class="pb-0">
        <!-- top-bar -->
        <nav class="navbar mb-2" style="margin: -10px -10px 0 -10px; display: block!important;">
            <div class="d-flex justify-content-between bd-highlight">
                <div class="p-1 bd-highlight">
                    <button type="button" id="sidebarCollapse" class="btn btn-info navbar-btn bg-dark">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div class="p-1 bd-highlight" id="search-form">
                    <div class="pt-1 mx-auto">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <button title="Очистить выборку" id="purge_button" class="btn btn-outline-secondary border-0"><i class="fa-solid fa-broom"></i></button>
                                <button title="Нажать для поиска" id="search_button" class="btn btn-outline-secondary border-0"><i class="fas fa-search"></i></button>
                            </div>
                            <input type="text" id="search_row" value="<?=$searchFor?>" type="search" placeholder="Поиск..." aria-label="Search" class="form-control border-top-0 border-left-0 border-right-0">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary border-0 dropdown-toggle" type="button" title="Где искать" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-gem"></i>
                                    <span><?=$session->get('SelectByClient')?></span>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" data-clientID="11" href='/search/select-by?client=11'>Все</a>
                                    <div class="dropdown-divider"></div>
                                    <?php foreach( $clients as $client ):?>
                                        <a class="dropdown-item" data-clientID="<?=$client['id']?>" href='/search/select-by?client=<?=htmlentities($client['name'])?>'><?=htmlentities($client['name']) ?></a>
                                    <?php endforeach;?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-1 bd-highlight">
                    <ul class="user-bar top-icons-agileits-w3layouts">
                        <li class="nav-item dropdown">
                            <a class="dropdown-toggle" style="" href="#" id="navbarDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true"
                               aria-expanded="false">
                                <div class="profile-l mr-0">
                                    <img src="/web/images/defaultUser2.png" class="img-fluid" alt="Responsive image">
                                </div>
                            </a>
                            <div class="dropdown-menu drop-3">
                                <div class="profile-r align-self-center">
                                    <h3 class="sub-title-w3-agileits"><?php echo $session->get('user')['fio'] ?></h3>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a href="<?=Url::to(['site/profile'])?>" class="dropdown-item mt-2">
                                    <h4><i class="far fa-user mr-3"></i>Профиль</h4>
                                </a>
                                <a href="<?=Url::to(['site/options'])?>" class="dropdown-item mt-2">
                                    <h4><i class="fas fa-tools mr-3"></i></i>Настройки</h4>
                                </a>
                                <a href="<?=Url::to(['site/statistic'])?>" class="dropdown-item mt-2">
                                    <h4><i class="fas fa-chart-pie mr-3"></i>Статистика</h4>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?=Url::to(['auth/logout'])?>">Выход</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!--// top-bar -->



        <!-- Collections Modal -->
        <div class="modal fade bd-example-modal-xl" id="collectionsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header p-2">
                        <h5 class="modal-title" id="exampleModalLabel">Список Коллекций</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-1">
                        <div class="container-fluid">
                            <div class="row justify-content-center">
                                <div class="card border-0 rounded-0" style="width: 14rem;">
                                    <div class="card-header bg-light">Золото</div>
                                    <div class="list-group border-right" style="font-size: small;">
                                        <a href="#" class="p-2 border-0 list-group-item list-group-item-action">Dapibus ac facilisis in</a>
                                        <a href="#" class="p-2 border-0 list-group-item list-group-item-action">Morbi leo risus</a>
                                        <a href="#" class="p-2 border-0 list-group-item list-group-item-action">Серебро с Золотыми накладками</a>
                                        <a href="#" class="p-2 border-0 list-group-item list-group-item-action disabled" tabindex="-1" aria-disabled="true">Vestibulum at eros</a>
                                    </div>
                                </div>
                                <div class="card border-0 rounded-0" style="width: 14rem;">
                                    <div class="card-header bg-light">Серебро</div>
                                    <div class="list-group border-right" style="font-size: small;">
                                        <a href="#" class="p-2 border-0 list-group-item list-group-item-action">Dapibus ac facilisis in</a>
                                        <a href="#" class="p-2 border-0 list-group-item list-group-item-action">Morbi leo risus</a>
                                        <a href="#" class="p-2 border-0 list-group-item list-group-item-action">Porta ac consectetur ac</a>
                                    </div>
                                </div>
                                <div class="card border-0 rounded-0" style="width: 14rem;">
                                    <div class="card-header bg-light">Бриллианты</div>
                                    <div class="list-group border-right" style="font-size: small;">
                                        <a href="#" class="p-2 border-0 list-group-item list-group-item-action">Dapibus ac facilisis in</a>
                                        <a href="#" class="p-2 border-0 list-group-item list-group-item-action">Morbi leo risus</a>
                                        <a href="#" class="p-2 border-0 list-group-item list-group-item-action">Porta ac consectetur ac</a>
                                    </div>
                                </div>
                                <div class="card border-0 rounded-0" style="width: 14rem;">
                                    <div class="card-header bg-light">Разное</div>
                                    <div class="list-group" style="font-size: small;">
                                        <a href="#" class="p-2 border-0 list-group-item list-group-item-action">Dapibus ac facilisis in</a>
                                        <a href="#" class="p-2 border-0 list-group-item list-group-item-action">Morbi leo risus</a>
                                        <a href="#" class="p-2 border-0 list-group-item list-group-item-action">Porta ac consectetur ac</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span class="text-xl-left">stats:</span>
                    </div>
                </div>
            </div>
        </div>
        <!--// Collections Modal -->



        <div class="container-fluid content" id="wrapp">
            <?= $content; ?>
        </div>

        <!-- Copyright -->
        <div class="copyright-w3layouts shadow pt-2 pb-2 mt-2 text-center" style="bottom: 0 !important;" id="footer">
            <p class="float-left ml-3"><small>Developed by Vadym Bykov</small></p>
            <p class="float-right mr-3"> ver2.0.0wip</p>
            <div class="clearfix"></div>
        </div>
        <!--// Copyright -->
    </div>
</div>
<div id="alertResponseModal" aria-hidden="true" aria-labelledby="alertResponseModal" role="dialog" class="iziModal">
    <div id="alertResponseContent" style="padding: 10px" class="hidden"></div>
</div>
<?php $this->endBody() ?>

<!-- Sidebar-nav Js -->
<script>
    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
        });
    });
</script>
<!--// Sidebar-nav Js -->

<!-- Tooltip -->
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });
</script>
<!-- //Tooltip -->

</body>
</html>
<?php $this->endPage() ?>
<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Url;
use app\assets\StanAdmAsset;
use yii\bootstrap4\Html;

StanAdmAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE HTML>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="keywords" content="" />
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
        <link rel="icon" href="/web/img/favicon.png">
        <link href='//fonts.googleapis.com/css?family=Roboto:700,500,300,100italic,100,400' rel='stylesheet' type='text/css'/>
        <link href='//fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
        <?php $this->head() ?>
    </head>

    <body>
        <?php $this->beginBody() ?>
        <div class="page-container">
        <!--/content-inner-->
        <div class="left-content">
        <div class="mother-grid-inner">
            <!--header start here-->
            <div class="header-main">

            <div class="logo-w3-agile">
                <h1><a>Stanimex Admin mode</a></h1>
            </div>

            <div class="profile_details w3l pull-right">
                <ul>
                    <li class="dropdown profile_details_drop">
                        <a href="<?= Url::to(['/']) ?>" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <div class="profile_img">
                                <span class="prfil-img"><img src="/web/img/favicon.png" alt=""></span>
                                <div class="user-name">
                                    <p>BAS</p>
                                    <span>Administrator</span>
                                </div>
                                <i class="fa fa-angle-down"></i>
                                <i class="fa fa-angle-up"></i>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                        <ul class="dropdown-menu drp-mnu">
                            <li> <a href="<?= Url::to(['/stan-admin/logoutt']) ?>"><i class="fa fa-sign-out"></i> Logout</a> </li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="clearfix"> </div>
        </div>
        <!--heder end here-->

        <?= $content ?>


            <div class="inner-block">
                <?php //debug($_SESSION) ?>
            </div>
            <div class="copyrights">
                <p>© <?=getdate()['year']?> Stanimex Ltd. | <a href="#" target="_blank">Powered by Yii framework</a></p>
            </div>
        </div>
        </div>
        <!--/sidebar-menu-->
        <div class="sidebar-menu">
            <header class="logo1">
                <a href="#" class="sidebar-icon"><span class="fa fa-bars"></span></a>
            </header>
            <div style="border-top:1px ridge rgba(255, 255, 255, 0.15)"></div>
            <div class="menu">
                <ul id="menu" >
                    <li><a href="<?= Url::to('/')?>"><i class="fa fa-home"></i> <span>Home Page</span><div class="clearfix"></div></a></li>
                    <li><a href="<?= Url::to('/stan-admin/main')?>"><i class="fa fa-tachometer"></i> <span>Statistic</span><div class="clearfix"></div></a></li>
                    <li id="menu-academico" ><a href="#"><i class="fa fa-file-text-o"></i>  <span>Web Pages</span> <span class="fa fa-angle-right" style="float: right"></span><div class="clearfix"></div></a>
                        <ul id="menu-academico-sub" >
                            <li id="menu-academico-avaliacoes" ><a href="<?=Url::to('/stan-admin/about')?>">About</a></li>
                            <li id="menu-academico-avaliacoes" ><a href="<?=Url::to('/stan-admin/webuy')?>">We Buy</a></li>
                            <li id="menu-academico-avaliacoes" ><a href="<?=Url::to('/stan-admin/stock')?>">Stock</a></li>
                        </ul>
                    </li>
                    <li id="menu-academico" ><a href="<?=Url::to('/stan-admin/addmachine')?>"><i class="fa fa-plus"></i><span>Add Machine</span><div class="clearfix"></div></a></li>
                    <li><a href="<?= Url::to('/stan-admin/shipments')?>"><i class="fa fa-picture-o" aria-hidden="true"></i><span>Shipments</span><div class="clearfix"></div></a></li>
                    <li id="menu-academico" ><a href="<?=Url::to('/stan-admin/orderbox')?>"><i class="fa fa-envelope nav_icon"></i><span>Order Box</span><div class="clearfix"></div></a></li>
                </ul>
            </div>
        </div>
        <div class="clearfix"></div>
        </div>
        <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
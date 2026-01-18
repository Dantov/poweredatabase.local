<?php
use yii\helpers\Url;
use app\models\User;
use app\models\serviceClasses\Crypt;

$this->title = 'Show all users';

$tt=time();
$this->registerJsFile("@web/js/add-edit/Validator.js?v=$tt",['depends' => [\app\assets\AppAsset::class]]);

//$this->registerCssFile("@web/css/view/view.css?v=$tt");
//debug($users->getRoles($single['role']),'getRoles',1);
//debug($all,'all');

?>

<div class="row justify-content-center">
    <?php foreach ($all as $single): ?>
    <!-- Profile -->
    <div class="col-md-auto">
        <div class="outer-w3-agile mt-3" style="width: 20rem !important;">
            <div class="profile-main-w3ls">
                <div class="profile-pic wthree">
                    <img src="/images/defaultUser2.png" class="img-fluid" alt="Responsive image">
                    <h3><?=$single['fio']?></h3>
                    <p><?=$users->getRoleNames($single['role'])?></p>
                </div>
                <div class="w3-message">
                    <h5>About Me</h5>
                    <p><?=$single['about']?></p>
                    <div class="w3ls-touch">
                       <ul class="nav nav-pills jus justify-content-center">
                          <li class="nav-item">
                            <a class="nav-link active" href="<?=Url::to(['users/edit','id'=>Crypt::strEncode($single['id'])])?>">Edit</a>
                          </li>
                          <li class="nav-item">
                            <a class="nav-link" href="<?=Url::to(['users/delete','id'=>Crypt::strEncode($single['id'])])?>">Delete</a>
                          </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--// Profiile -->
    <?php endforeach; ?>
    <!-- Profile -->
    <div class="col-md-auto">
        <div class="outer-w3-agile mt-3" style="width: 20rem !important;">
            <div class="profile-main-w3ls">
                <div class="profile-pic wthree">
                    <a href="<?=Url::to(['users/add'])?>">
                        <img src="/pictAssets/plususer.png" class="img-fluid" alt="">
                    <h3 class="text-danger">Add New One</h3>
                    <p>&nbsp;</p>
                    </a>
                </div>
                <div class="w3-message">
                    <h5>&nbsp;</h5>
                    <p><br/><br/></p>
                    <div class="w3ls-touch">
                       <ul class="nav nav-pills jus justify-content-center">
                          <li class="nav-item">
                            <a class="nav-link">&nbsp;</a>
                          </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--// Profiile -->
</div>


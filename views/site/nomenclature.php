<?php
/* @var $this yii\web\View */

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\User;

$this->title = 'Номенклатура';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>
    <code><?= __FILE__ ?></code>
</div>

<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="pills-home-tab" data-toggle="pill" data-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Home</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-profile-tab" data-toggle="pill" data-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Profile</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-contact-tab" data-toggle="pill" data-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Contact</button>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link" href="<?=Url::to(['users/show-all'])?>">Пользователи</a>
  </li>
</ul>
<div class="tab-content" id="pills-tabContent">
  <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
    <div class="outer-w3-agile mt-3">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="modeller3d">3Д модельеры:</label>
                <div class="input-group">
                    <input type="text" editable class="form-control" name="modeller3d" id="modeller3d" value="" placeholder="" >
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></button>
                        <div class="dropdown-menu">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="model_type">Типы моделей:</label>
                <div class="input-group">
                    <input type="text" editable class="form-control" name="model_type" id="model_type" value=""placeholder="" >
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></button>
                        <div class="dropdown-menu">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="customers">Заказчики</label>
            <div class="input-group">
                <input type="text" editable class="form-control" value="" name="client" id="client" aria-label="" >
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></button>
                    <div class="dropdown-menu">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
  <!-- HOME PILL END -->

  <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">...Profile Pill</div>
  <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">...Contact Pill</div>
  <div class="tab-pane fade" id="pills-users" role="tabpanel" aria-labelledby="pills-users-tab">
    <div class="outer-w3-agile mt-3">
        <div class="form-group">
            <label for="customers"></label>
            <div class="input-group">
                <input type="text" editable class="form-control" value="" name="users" id="users" aria-label="" >
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></button>
                    <div class="dropdown-menu">
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>


<?php
/* @var $this yii\web\View */

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\User;
use app\models\serviceClasses\Crypt;

$this->params['breadcrumbs'][] = $this->title;
$tt=time();
$this->registerJsFile("@web/js/users/Users.js?v=$tt");
$uClients = [];
?>
<script class="d-none dellscript">
let appliedRights = [];
<?php foreach ($uPermissions as $uPermission): ?>                                 
    appliedRights.push({
        permid: '<?=$uPermission['id']?>',
        name: '<?=$uPermission['name']?>',
        description: '<?=$uPermission['description']?>'
        });
<?php endforeach; ?>
</script>
<div class="site-about">
     <code><?php //echo debug($permissions) ?></code>
</div>
<div class="row validform">
    <div class="col-md-2 order-md-1 validform2"></div>

    <div class="col-md-8 order-md-1 validform2">
    <div class="outer-w3-agile mt-3"> 
        <h4 class="tittle-w3-agileits mb-4"><?= $this->title = 'Edit User:' . $single['fio'] ?></h4> 
            <div class="col-md-12 order-md-1 validform2">
                <form action="<?=Url::to(['users/edit-user/'])?>" method="post" class="needs-validation" novalidate="">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="firstName">Name</label>
                            <input type="text" class="form-control" id="firstName" placeholder="" value="<?= $single['name'] ?>" required="">
                            <div class="invalid-feedback">
                                Valid first name is required.
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="lastName">Last name</label>
                            <input type="text" class="form-control" id="lastName" placeholder="" value="<?= $single['lastname'] ?>" required="">
                            <div class="invalid-feedback">
                                Valid last name is required.
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="thirdName">Third name</label>
                            <div class="input-group">
                                <input type="text" value="<?= $single['thirdname'] ?>" class="form-control" id="thirdName" placeholder="">
                                <div class="invalid-feedback">
                                    Your username is required.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label for="email">Email
                                <span class="text-muted">(Optional)</span>
                            </label>
                            <input type="email" class="form-control" id="email" value="<?= $single['email'] ?>" placeholder="you@example.com">
                            <div class="invalid-feedback">
                                Please enter a valid email address for shipping updates.
                            </div>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="role">Role</label>
                            <select class="custom-select d-block w-100" name="role" id="role" required="">
                                <option value="2">3D Modeller</option>
                                <option value="3">Creative Designer</option>
                                <option value="4">Artist</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a valid Role.
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="address">Login</label>
                            <input type="text" class="form-control" id="login" placeholder="">
                            <div class="invalid-feedback">
                                Please enter your Login.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address">Password</label>
                            <input type="password" class="form-control" id="password" placeholder="">
                            <div class="invalid-feedback">
                                Please enter your password.
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="aboutControlTextarea1">About</label>
                            <textarea class="form-control" id="aboutControlTextarea1" rows="3"><?=$single['about']?></textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12 mb-3 form-group">
                            <label for="ClientsControlSelect">All Clients:</label>
                            <select multiple number="7" style="height: 8rem !important;" name="Clients[]" class="form-control" id="ClientsControlSelect">
                                <?php foreach( $clients as $client ): ?>
                                    <option value="<?=$client['id']?>"><?=$client['name']?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3 appliedClients">
                        <?php foreach ($uClients as $uClient): ?>
                            <div class="alert alert-success alert-dismissible fade show mb-1 appliedClient" role="alert">
                              <?=$uClient['name']?>
                              <button type="button" class="close ucid" data-ucid="<?=$uClient['id']?>" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="allrights">All Righs:</label>
                            <select multiple="10" name="perms[]" class="custom-select d-block w-100 cursorPointer"  style="height: 15rem !important;" id="allrights">
                                <?php foreach( $permissions as $permission ): ?>
                                    <option class="rightOpt" data-applied="<?=$permission['applied']?>" data-permid="<?=$permission['id']?>" value="<?=$permission['name']?>"><?=$permission['description']?><?=$permission['applied']?'<span class="right-in-use"> - &#10094;&#10094;applied&#10095;&#10095;':"" ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-0">
                            <label>Applied Righs:</label>
                        </div>
                        <div class="col-md-12 appliedPermAll">
                            <?php foreach ($uPermissions as $uPermission): ?>
                                <div class="alert alert-info alert-dismissible fade show mb-1 appliedPerm" role="alert">
                                  <strong><?=$uPermission['name']?></strong> <?=$uPermission['description']?>
                                  <button type="button" class="close upid" data-upid="<?=$uPermission['id']?>" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                  </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <hr>
                    <button class="btn btn-primary btn-lg btn-block error-w3l-btn" type="submit">Save</button>
                    <input type="hidden" value="<?=Crypt::strEncode($single['id'])?>" name="uid" id="uid" >
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-2 order-md-1 validform2"></div>
</div>
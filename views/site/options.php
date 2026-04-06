<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;
use app\models\User;

$name = User::getFIO();
$this->title = $name . ' OPTIONS';
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <h2>
        OPTIONS
    </h2>
    </div>
    <?= 1//debug( $count, 'Total: ') ?>
    <?= 1//debug( $countAffected, 'Total Affected:') ?>
    <?= 1//debug( ($count-$countAffected), 'Осталось: ') ?>
    <?= 1//debug( $merged, '(current affected: ' . count($merged). ') Merged data:' ) ?>
    <?= 1//debug( $hufdata ) ?>
</div>

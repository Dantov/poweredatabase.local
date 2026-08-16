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
<script>
    function procced()
    {
        let btn = document.querySelector('#procced');
        btn.setAttribute('disabled',"true");
        btn.innerHTML = 'Procced <div class="spinner-border text-primary" role="status"><span class="sr-only">Procced...</span></div>';

        obj = {
            uid : '123',
        };
        $.ajax({
            url: "/site/options/",
            type: 'POST',
            data: obj,
            dataType:"json",
            success:function(resp) {
                if (resp) 
                {
                    //AR.debug( resp );
                    //debug( resp );
                    document.querySelector('.result').innerHTML =  resp;
                    btn.classList.add('d-none');
                }
            }
        });
    }
    
</script>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <h2>
        OPTIONS
    </h2>
    </div>
    
    <?php if ( count($errors) ): ?>
        <?= debug( $errors, 'Errors:') ?>    
    <?php else: ?>
       No Errors. <button type="button" id="procced" onclick="procced();" class="btn btn-success">Procced</button>
    <?php endif; ?>

    <div class="result" ></div>
    <?php // debug( count($result), 'Affected: ') ?>
    <?php // debug( $result, 'Result: ') ?>
</div>

<div class="p-1 bg-secondary fontsView text-white Nit_gems">
    <i class="far fa-gem"></i>
    <span>Вставки 3D:</span>
</div>
<div class="row row-cols-2 justify-content-between pl-3 pr-3">
    <?php foreach( $model['gems'] as $gem ): ?>
    <div class="col-sm-6 p-0">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title mb-0">
                <?=$gem['name']?> <?=$gem['cut']?> <?=$gem['color']?>
                <p>Ø<?=$gem['size']?> - <?=$gem['value']?> шт.</p>    
            </h6>
          </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
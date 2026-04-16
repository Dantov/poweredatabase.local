<div id="modalImageViewer" data-modelid="<?=$model['id']?>">
    <div class="dopButtons">
        <a class="iziModal-button increaseSize" title="Увеличить картинку"><i class="fas fa-search-plus"></i></a>&nbsp;
        <a class="iziModal-button decreaseSize" title="Уменьшить картинку"><i class="fas fa-search-minus"></i></a>&nbsp;
        <a class="iziModal-button sizeFull" title="Полный размер картинки"><i class="fas fa-expand-arrows-alt"></i></a>&nbsp;
        <a class="iziModal-button sizeDefault" title="Вписать в экран"><i class="fas fa-compress-arrows-alt"></i></a>
    </div>
    <div id="modalImageViewerContent" class="d-none">

        <div id="carousel_ImageViewer" class="ImageViewerMainImage carousel slide" data-ride="carousel">
          <div class="carousel-inner" style="height: 100%;">
            <?php foreach( $model['images'] as $image ): ?>
            <?php $imgUrl = empty($image['name'])?"/pictAssets/default.png":'/stock/'.$modelPath.'/images/'.$image['name']?>
            <div class="carousel-item <?=$image['status']?"active":""?> " style="height: 100%;" >
                <div data-num="<?=$image['numOrigin']?>" class="IV-main-slide" style="background-image: url(<?=$imgUrl?>);"></div>
            </div>
            <?php endforeach; ?>
          </div>
          <button class="carousel-control-prev" type="button" data-target="#carousel_ImageViewer" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-target="#carousel_ImageViewer" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
          </button>
        </div>

        <!-- <div class="ImageViewerMainImage"></div>-->
        <div class="row m-0 p-0">
            <div class="col-sm-12 justify-content-md-center d-flex smallImgRow"></div>
        </div>
    </div>
</div>
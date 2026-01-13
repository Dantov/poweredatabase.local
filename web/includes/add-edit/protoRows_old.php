<?php
    $vcI = $i = $vcI = $c = null; // counters
    switch ( $switchTableRow )
    {
        case "gems": //прототип строки камней
    ?>
        <div class="form-row gemsRow">
            <div class="form-group col-md-3">
                <label for="gems_names">Сырье</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="gems[gems_names][]" id="gems_names" aria-label="">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></button>
                        <div class="dropdown-menu">
                            <?php foreach ($sevData['gems_names'] as $key => $value): ?>
                            <a class="dropdown-item" style="cursor: pointer;" elemToAdd><?php echo $value['name'] ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-2">
                <label for="gems_cut">Огранка</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="gems[gems_cut][]" id="gems_cut" aria-label="">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></button>
                        <div class="dropdown-menu">
                            <?php foreach ($sevData['gems_cut'] as $key => $value): ?>
                            <a class="dropdown-item" style="cursor: pointer;" elemToAdd><?php echo $value['name'] ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-2">
                <label for="gems_sizes">Ø Размеры мм</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="gems[gems_sizes][]" id="gems_sizes" aria-label="">
                </div>
            </div>
            <div class="form-group col-md-3">
                <label for="gems_color">Цвет</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="gems[gems_color][]" id="gems_color" aria-label="">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></button>
                        <div class="dropdown-menu">
                            <?php foreach ($sevData['gems_color'] as $key => $value): ?>
                            <a class="dropdown-item" style="cursor: pointer;" elemToAdd><?php echo $value['name'] ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-2">
                <label for="gems_value">Кол-во</label>
                <div class="input-group">
                    <input type="number" class="form-control" name="gems[gems_value][]" id="gems_value" aria-label="">
                </div>
            </div>
       </div>
           

            <tr <?= !isset($gem) ? 'class="hidden protoRow" id="protoGemRow"':'' ?> >
                <td><?= ++$c ?></td>
                <td>
                    <input type="hidden" class="rowID" name="gems[id][]" value="<?=$gem['id']??'' ?>">
                    <div class="input-group">
                        <input type="text" class="form-control" name="gems[gems_names][]" value="<?=$gem['gems_names']??'' ?>"/>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?=$formVars['gems_namesLi']??''?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group gems_cut_input">
                        <input type="text" class="form-control" name="gems[gems_cut][]" value="<?=$gem['gems_cut']??'' ?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?=$formVars['gems_cutLi']??'' ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group gems_diametr_input">
                        <input type="text" class="form-control" name="gems[gems_sizes][]" value="<?=$gem['gems_sizes']??'' ?>"/>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?=$formVars['gems_sizesLi']??'' ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <input type="number" min="1" class="form-control gems_value_input" name="gems[value][]" value="<?=$gem['value']??''?>">
                </td>
                <td>
                    <div class="input-group gems_color_input">
                        <input type="text" class="form-control" name="gems[gems_color][]" value="<?=$gem['gems_color']??''?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?=$formVars['gems_colorLi']??''?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td style="width:100px;">
                    <button class="btn btn-sm btn-default" type="button" onclick="duplicateRowNew(this);" title="дублировать строку">
                        <span class="glyphicon glyphicon-duplicate"></span>
                    </button>
                    <button class="btn btn-sm btn-default" type="button" onclick="deleteRowNew(this);" title="удалить строку">
                        <span class="glyphicon glyphicon-trash"></span>
                    </button>
                </td>
            </tr>
    <?php
        break;
        case 'materialsFull': //прототип строки материалов
    ?>
            <?php $materialsData = $formVars['materialsData']??[] ?>
            <tr <?= !isset($materialRow) ? 'class="hidden protoRow" id="protoMaterialsRow"':'' ?>>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="hidden" class="rowID" name="mats[id][]" value="<?=$materialRow['id']??'' ?>">
                        <input type="text" class="form-control" name="mats[part][]" value="<?=$materialRow['part']??'' ?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?=$formVars['modTypeLi']??'' ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[type][]" value="<?=$materialRow['type']??'' ?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php foreach ( $materialsData['names']??[] as $type ) : ?>
                                    <li style="position:relative;">
                                        <a elemToAdd><?=$type?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[probe][]" value="<?=$materialRow['probe']??'' ?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php foreach ( $materialsData['probes']?:[] as $probes ) : ?>
                                    <?php foreach ( $probes as $probe ) : ?>
                                        <li style="position:relative;">
                                            <a elemToAdd><?=$probe?></a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td class="brr-2-secondary">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[metalColor][]" value="<?=$materialRow['metalColor']??'' ?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php foreach ( $materialsData['colors']?:[] as $color ) : ?>
                                    <li style="position:relative;">
                                        <a elemToAdd><?=$color?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[covering][]" value="<?=$materialRow['covering']??'' ?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php $coveringsData = $formVars['coveringsData']??[] ?>
                                <?php foreach ( $coveringsData['names']??[] as $type ) : ?>
                                    <li style="position:relative;">
                                        <a elemToAdd><?=$type?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[area][]" value="<?=$materialRow['area']??'' ?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php foreach ( $coveringsData['areas']??[] as $area ) : ?>
                                    <li style="position:relative;">
                                        <a elemToAdd><?=$area?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[covColor][]" value="<?=$materialRow['covColor']??'' ?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php foreach ( $materialsData['colors']??[] as $color ) : ?>
                                    <li style="position:relative;">
                                        <a elemToAdd><?=$color?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="mats[handling][]" value="<?=$materialRow['handling']??'' ?>">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php $handlingsData = $formVars['handlingsData']??[] ?>
                                <?php foreach ( $handlingsData??[] as $type ) : ?>
                                    <li style="position:relative;">
                                        <a elemToAdd><?=$type['name']?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <input type="number" min="1" max="999" step="1" class="form-control input-sm" name="mats[count][]" value="<?=$materialRow['count'] ?? 1 ?>">
                </td>
                <td style="width:80px;">
                    <button class="btn btn-sm btn-default" type="button" onclick="duplicateRowNew(this);" title="дублировать строку">
                        <span class="glyphicon glyphicon-duplicate"></span>
                    </button>
                    <button class="btn btn-sm btn-default" type="button" onclick="deleteRowNew(this);" title="удалить строку">
                        <span class="glyphicon glyphicon-trash"></span>
                    </button>
                </td>
            </tr>
    <?php
        break;
        case 'dropImage':
    ?>
            <div class="col-xs-6 col-sm-3 col-md-2 <?=isset($protoImgRow)?'hidden':'image_row'?>" <?=isset($protoImgRow) ? 'id="proto_image_row"': ''?> >
                <div class="ratio img-thumbnail">
                    <div class="ratio-inner ratio-4-3">
                        <div class="ratio-content">
                            <img src="<?=$protoImgRow ? '': $setPrevImg($image) ?>" class="imgThumbs" />
                        </div>
                        <div class="img_dell">
                            <?php if ( !$protoImgRow && $component === 3 ): $onClk = "dellImgPrew(this)";  endif; ?>
                            <?php if ( !$protoImgRow && $component === 2 ): $onClk = "dell_fromServ({$formVars['id']}, '{$image['imgName']}', 'image', false, this)";  endif; ?>
                            <button class="btn btn-default" type="button" onclick="<?= $onClk??'' ?>" >
                                <span class="glyphicon glyphicon-remove"></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="img_inputs">
                    <input type="hidden" class="rowID" <?=$protoImgRow ? '': 'name="image[id][]"'?> value="<?= !$protoImgRow && $component === 3 ? '': $image['id']??'' ?>">
                    <select class="form-control input-sm" <?=$protoImgRow ? '': 'name="image[imgFor][]" onchange="handlerFiles.onSelect(this)"'?>>
                        <?php $statusImgArray = $protoImgRow ? $formVars['dataArrays'] : $image ?>
                        <?php foreach ( $statusImgArray['imgStat']??[] as $statusImg ): ?>
                            <option <?=(int)$statusImg['selected'] === 1 ?'selected':''?> data-imgFor="<?=$statusImg['id']??'' ?>" value="<?=$statusImg['id']??'' ?>" title="<?=$statusImg['title']??'' ?>"><?=$statusImg['name']??'' ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ( !$protoImgRow && $component === 3) : ?>
                        <input type="hidden" name="image[img_name][sketch]" value="<?=$row['number_3d'].'#'.$row['id'].'#'.$image['imgName']?>">
                    <?php endif;?>
                </div>
            </div>
<?php
            break;
    }
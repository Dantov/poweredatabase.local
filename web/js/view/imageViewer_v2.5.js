"use strict";
function ImageViewer(images, modelPath)
{
    if ( images ) this.images = images;
    if ( modelPath ) this.imagesPath = modelPath;

    /**
     *      В Открытом состоянии
     */
    this.smallImagesRowIsSet = false;
    this.activeImage = null; // активная маленькая картинка внизу слева


    this.nW = null; //natural Width (оригинальная ширина картинки)
    this.nH = null; //natural Height (оригинальная высота картинки)
    /*
    * реальная( такая на данный момент ) Ширина и высота в пикселях
    * после увеличения картинки (клик по +)
    * */
    this.realW = null;
    this.realH = null;
    this.percent = 0; // текущий размер картинки в %

    /**
    * переменные для передвижения картинки
    */
    this.mouseIsDown = false;
    this.mouseOnImage = false;
    this.plusActive = false; // когда true - картинка увеличена
    this.savedMouseX = null;
    this.savedMouseY = null;
    //Позиция главной фоновой картинки
    this.bgPosX = null;
    this.bgPosY = null;

    this.attachedEvents = false;

    this.insideDopImages = [];


    /**
     *  В закрытом состоянии
     */
    $('#carousel_ImageViewer').carousel('pause');
    // on view page
    this.carouseItems = document.querySelector("#carouselMainImg").querySelectorAll('.carousel-item');
    this.mainImage = document.querySelector(".mainImage");
    this.bottomDopImages = document.querySelector(".dopImages").querySelectorAll(".imageSmall");
    this.modelID = document.getElementById('modalImageViewer').getAttribute('data-modelid');

    this.carouseItemsIV = document.getElementById("carousel_ImageViewer").querySelectorAll('.carousel-item');
}

// This code run just one time
ImageViewer.prototype.init = function()
{
    //debug(this.images);
    let that = this;

    $('#carouselMainImg').carousel({
      interval: 6000
    });

    //carousel has completed its slide transition.
    $('#carouselMainImg').on('slide.bs.carousel', function (event) {
        //this.mainImage set
        that.mainImage = event.relatedTarget.children[0];
        that.mainImageLoupe();

        let prev = that.bottomDopImages[event.from];
        let next = that.bottomDopImages[event.to];

        prev.parentElement.parentElement.parentElement.classList.remove('activeImage','border-primary');
        prev.parentElement.parentElement.parentElement.classList.add("border-light");

        next.parentElement.parentElement.parentElement.classList.remove('border-light');
        next.parentElement.parentElement.parentElement.classList.add('activeImage','border-primary');
        
    });

    $('#modalImageViewer').iziModal({
        title: '',
        subtitle: '',
        headerColor: '#1a1c19ad',
        background: '#212320ad',
        icon: 'fas fa-image',
        width: '100%',
        radius: 0,
        openFullscreen: true,
        transitionIn: 'comingIn',
        transitionOut: 'comingOut',
        overlayClose: false,
        closeButton: true,
        afterRender: function () {
            //document.getElementById('modalImageViewerContent').classList.remove('d-none');
        }
    });

    $(document).on('opening', '#modalImageViewer', function () {

        let num3D = document.getElementById('num3d').innerHTML;
        let create_date = '';
        if ( document.getElementById('create_date') )
        {
            create_date = ' / ' + document.getElementById('create_date').innerHTML;
        }
        let modType = document.getElementById('modelType').innerHTML;
        let titleStr = num3D + ' - ' + modType + create_date;
        $('#modalImageViewer').iziModal('setTitle',titleStr);

        $('#carouselMainImg').carousel('pause');

        document.getElementById('modalImageViewerContent').classList.remove('d-none');

        that.start();
    });

    // открылось
    $(document).on('opened', '#modalImageViewer', function () {

        if ( that.attachedEvents === false )
        {
            let imageViewer = document.querySelector('#modalImageViewer');
            let viewerContent = imageViewer.querySelector('.ImageViewerMainImage');
            viewerContent.addEventListener('mousedown', that.mouseDownIMG.bind(event, that, viewerContent), false );
            viewerContent.addEventListener('mouseup', that.mouseUpIMG.bind(event, that, viewerContent), false );
            viewerContent.addEventListener('mousemove', that.mouseMoveIMG.bind(event, that, viewerContent), false );

            viewerContent.addEventListener('mouseout', function () {
                that.mouseOutIMG();
            }, false );
            viewerContent.addEventListener('mouseover', function () {
                that.mouseInIMG();
            }, false );

            imageViewer.addEventListener('mouseup', function (event) {
                if ( that.plusActive === true && that.mouseIsDown === true && that.mouseOnImage === false )
                {
                    that.mouseUpIMG(that, viewerContent, event);
                }
            }, false );

            that.attachedEvents = true;
        }

    });

    // Начало закрытия
    $(document).on('closing', '#modalImageViewer', function (event){
        event.preventDefault();
        that.stop();
        document.getElementById('modalImageViewerContent').classList.add('d-none');
        $('#carouselMainImg').carousel();
    });
    // исчезло
    $(document).on('closed', '#modalImageViewer', function () {
    });

    this.mainImageSetter();
    this.mainImageLoupe();

    //adding listeners for carusel items
    this.carouseItems.forEach(tslide => {
        let slide = tslide.children[0];

        slide.addEventListener('click', function(event)
        {
            event.preventDefault();
            if ( _IS_DESKTOP_ ) 
                $('#modalImageViewer').iziModal('open');
        });

    });

    let imageViewer = document.querySelector('#modalImageViewer');
    let dopButtons = imageViewer.querySelector('.dopButtons').querySelectorAll('a');
    dopButtons[0].addEventListener('click',function (event)
    {
        event.preventDefault();
        that.sizeIncrease();
    }, false);

    dopButtons[1].addEventListener('click', function (event)
    {
        event.preventDefault();
        that.sizeDecrease();
    }, false);
    dopButtons[2].addEventListener('click', function (event)
    {
        event.preventDefault();
        that.sizeFull();
    }, false);
    dopButtons[3].addEventListener('click', function (event)
    {
        event.preventDefault();
        that.sizeDefault();
    }, false);


    debug('Image Viewer 2.5 Init(ok)');
};

/**
 * запускаем просмотр картинок при клике на главную
 */
ImageViewer.prototype.start_OLD = function()
{
    let mainImageID = this.mainImage.getAttribute('data-id');
    //let imgSrc = this.images[imgID]['imgPath'];//['img_name'];
    let imgSrc = '';
    let self = this;
    $.each(this.images, function(id, imgObj) {
        if ( +imgObj.id === +mainImageID )
            return imgSrc = "/stock/" + self.imagesPath + "/images/" + imgObj.name;
    });

    //debug(imgSrc,'imgSrc');
    this.setBackgroundImage(imgSrc);
    this.smallImagesRow(mainImageID);

    document.body.style.overflow = 'hidden'; // убираем полосу прокрутки
};

ImageViewer.prototype.start = function()
{
    let that = this;
    let selectedImageID = this.mainImage.getAttribute('data-id');
    let slideNumber = this.images[selectedImageID]['numOrigin'];
    $('#carousel_ImageViewer').carousel(slideNumber);

    this.setBackgroundImage( this.images[selectedImageID] );
    this.setBgSize( this.carouseItemsIV[slideNumber].children[0] );

    this.smallImagesRow(selectedImageID);

    //carousel has completed its slide transition.
    $('#carousel_ImageViewer').on('slide.bs.carousel', function (event) {

        let prev = that.insideDopImages[event.from];
        let next = that.insideDopImages[event.to];

        //Bottom images
        prev.classList.remove('activeImage','border-primary');
        prev.classList.add('border-secondary');
        next.classList.remove('border-secondary');
        next.classList.add('activeImage','border-primary');

        let img;
        $.each(that.images, function(id, imgO) {
            if ( +imgO.numOrigin === +event.to )
                return img = imgO;
                //return imgSrc = "/stock/" + that.imagesPath + "/images/" + img.name;
        });

        that.setBackgroundImage(img);
        that.setBgSize( that.carouseItemsIV[event.to].children[0] );
    });
};

ImageViewer.prototype.stop = function() // закрываем просмотр картинок при клике на крестик
{
    $('#carousel_ImageViewer').carousel('pause');
    this.plusActive = false;
    this.percent = 0;
    this.clearMovingVars();

    document.body.style.overflow = 'visible'; // восстановим полосу прокрутки
};
ImageViewer.prototype.clearMovingVars = function()
{
    this.plusActive = false;
    this.savedMouseX = null;
    this.savedMouseY = null;
    this.bgPosX = null;
    this.bgPosY = null;
};


/**
 *    Устанавливаем картинку в просмотре
 */
ImageViewer.prototype.setBackgroundImage = function(img)
{
    debug(img,"imgXXX");
    let imgSrc = "/stock/" + this.imagesPath + "/images/" + img.name;
    let imageViewer = document.querySelector('#modalImageViewer');
    let viewerContent = imageViewer.querySelector('.ImageViewerMainImage');

    viewerContent.classList.remove('cursorGrab');
    viewerContent.classList.remove('cursorGrabbing');

    let that = this;

    let loadimg = new Image(); // создаем картинку
    loadimg.src = imgSrc;
    loadimg.onload = function() {

        that.nW = this.naturalWidth;
        that.nH = this.naturalHeight;

        // изначально реальные размеры равны натуральным
        that.realW = this.naturalWidth;
        that.realH = this.naturalHeight;

        //viewerContent.style.backgroundImage =  "url("+ imgSrc +")";
        //debug(viewerContent.style.backgroundImage,'backgroundImage');
        debug(this.width + 'x' + this.height,"loadimg w+h");
        debug(this.naturalWidth + 'x' + this.naturalHeight);

        //document.body.style.overflow = 'hidden'; // убираем полосу прокрутки

        //that.setBgSize();
    };

};
ImageViewer.prototype.setBgSize = function( current_slide ) // ставит backgroundSize на просмотре
{
    //let imageViewer = document.querySelector('#modalImageViewer');
    //let viewerContent = imageViewer.querySelector('.ImageViewerMainImage');

    let screenW = document.documentElement.clientWidth;
    let screenH = document.documentElement.clientHeight;

    if ( this.nH > screenH || this.nW > screenW ) {
        //if ( this.imageViewer.style.backgroundSize == "contain" ) return;
        //viewerContent.style.backgroundSize = "contain";
        current_slide.style.backgroundSize = "contain";

        debug('123setBgSize used = ' +  current_slide.style.backgroundSize);
    } else {
        //if ( this.imageViewer.style.backgroundSize == "auto" ) return;
        //viewerContent.style.backgroundSize = "auto";
        current_slide.style.backgroundSize = "cover";

        debug('321setBgSize used = ' + current_slide.style.backgroundSize);
    }
    debug(current_slide);
    //viewerContent.style.backgroundPositionX = "";
    current_slide.style.backgroundPositionX = "";
    //viewerContent.style.backgroundPositionY = "";
    current_slide.style.backgroundPositionY = "";

    this.setBgRealSizePxPercent();
};
ImageViewer.prototype.setBgRealSizePxPercent = function()
{
    let screenW = document.documentElement.clientWidth;
    let screenH = document.documentElement.clientHeight;

    // узнаем размер картинки в %
    if ( this.nW >= screenW )
    {
        this.realW = screenW;
        this.percent = this.realW * 100 / this.nW; // текущий размер картинки в %
        this.realH = this.nH * this.percent / 100;
    }
    if ( this.nH >= screenH )
    {
        this.realH = screenH;
        this.percent = this.realH * 100 / this.nH; // текущий размер картинки в %
        this.realW = this.nW * this.percent / 100;
    }
    if ( +this.nH === +screenH && +this.nW === +screenW )
    {
        this.realH = screenH;
        this.realW = screenW;
        this.percent = this.realH * 100 / this.nH;
    }
    if ( this.nH < screenH && this.nW < screenW )
    {
        this.realH = this.nH;
        this.realW = this.nW;
        this.percent = 100;
    }

    debug(this.realH ,'realH');
    debug(this.realW ,'realW');
    debug(this.percent ,'%');
};


/**
 * ставим доп картинки внизу слева В Просмотре
 */
ImageViewer.prototype.smallImagesRow = function()
{
    let that = this;
    let viewerContent = document.querySelector('#modalImageViewerContent');

    let smallImgRow = viewerContent.querySelector('.smallImgRow');

    let mainImgID = this.mainImage.getAttribute('data-id');

    if ( this.smallImagesRowIsSet === true )
    {
        $.each(smallImgRow.querySelectorAll('div'), function(i, image) {
            //debug(image,'smallImagesRowIsSet');
            if ( +mainImgID === +image.getAttribute('data-id') )
            {
                image.classList.remove('border-secondary');
                image.classList.add('border-primary','activeImage');
            } else {
                image.classList.remove('border-primary','activeImage');
                image.classList.add('border-secondary');
            }
        });
        return;
    }

    // формируем новые
    let flagActive = false;
    $.each(this.images, function(id, image) {

        let src = "/web/stock/" + that.imagesPath + "/images/";
        let fullsrc = src + (image.previmg??image.name);

        let div = document.createElement('div');
            div.classList.add('p-0','cursorPointer','imageSmall','imageViewerSmallImgDown','border');
            div.setAttribute('data-num-origin',image.numOrigin);
            div.setAttribute('data-id',image.id);
            div.style.backgroundImage = "url("+ fullsrc  +")";

        let mainImgID = that.mainImage.getAttribute('data-id');
        if ( +mainImgID === +image.id )
        {
            div.classList.add('border-primary','activeImage');
            flagActive = true;
        } else {
            div.classList.add('border-secondary');
        }

        let appended = smallImgRow.appendChild(div);
        if ( flagActive )
        {
            that.activeImage = appended;
            flagActive = false;
        }
        that.insideDopImages[image.numOrigin] = appended;

        appended.onclick = function()
        {
            that.stop();

            $('#carousel_ImageViewer').carousel( +this.getAttribute('data-num-origin') );
            //that.setBackgroundImage(this.style.backgroundImage.split("\"")[1]);
            //that.setBackgroundImage(image.name); // Оригин. размер
            //that.setBackgroundImage(src + image.name); // Оригин. размер

            that.activeImage.classList.remove('activeImage','border-primary');
            that.activeImage.classList.add('border-secondary');

            this.classList.remove('border-secondary');
            this.classList.add('activeImage','border-primary');

            that.activeImage = this;
        };
    });

    this.smallImagesRowIsSet = true;
};


/**
 *
 */
ImageViewer.prototype.setBgPosition = function(background)
{
    let screenW = document.documentElement.clientWidth;
    let screenH = document.documentElement.clientHeight;
    let offsetImgHeight,offsetImgWidth, screenCenterX, screenCenterY;
    offsetImgWidth = this.realW / 2;
    offsetImgHeight = this.realH / 2;
    screenCenterX = screenW / 2;
    screenCenterY = screenH / 2;

    // текущие коорд. картинки
    this.bgPosX = screenCenterX - offsetImgWidth;
    this.bgPosY = screenCenterY - offsetImgHeight - 1;

    background.style.backgroundPositionX = this.bgPosX + "px";
    background.style.backgroundPositionY = this.bgPosY + "px";
};
ImageViewer.prototype.sizeIncrease = function() // увеличим картинку на 1/4
{
    if ( this.percent >= 200 ) return; // макс увеличение в 2 раза

    if ( this.plusActive !== true )
    {
        this.plusActive = true;
    }
    this.percent += 25;
    if ( this.percent > 200 ) this.percent = 200;

    let imageViewer = document.querySelector('#modalImageViewer');
    let viewerContent = imageViewer.querySelector('.ImageViewerMainImage');

    this.realW = (this.nW * this.percent ) / 100;
    this.realH = (this.nH * this.percent ) / 100;

    viewerContent.style.backgroundSize = this.realW + "px" + "," + this.realH + "px";
    this.setBgPosition(viewerContent);

    viewerContent.classList.add('cursorGrab');

    debug(this.percent,'+%');
    debug(this.realH ,'realH');
    debug(this.realW ,'realW');
};
ImageViewer.prototype.sizeDecrease = function() // уменьшим картинку на 1/4
{
    if ( this.percent <= 25 ) return;
    if ( this.plusActive !== true )
    {
        this.plusActive = true;
    }

    this.percent -= 25;
    if ( this.percent <= 25 ) this.percent = 25;

    let imageViewer = document.querySelector('#modalImageViewer');
    let viewerContent = imageViewer.querySelector('.ImageViewerMainImage');

    this.realW = (this.nW * this.percent ) / 100;
    this.realH = (this.nH * this.percent ) / 100;

    viewerContent.style.backgroundSize = this.realW + "px" + "," + this.realH + "px";
    this.setBgPosition(viewerContent);

    viewerContent.classList.add('cursorGrab');

    debug(this.percent,'-%');
    debug(this.realH ,'realH');
    debug(this.realW ,'realW');
};
/**
 * увеличим картинку на полную
 */
ImageViewer.prototype.sizeFull = function()
{
    let imageViewer = document.querySelector('#modalImageViewer');
    let viewerContent = imageViewer.querySelector('.ImageViewerMainImage');

    this.plusActive = true;
    this.realH = this.nH;
    this.realW = this.nW;
    this.percent = 100;

    viewerContent.style.backgroundSize = 'auto auto';
    this.setBgPosition(viewerContent);

    viewerContent.classList.add('cursorGrab');

    debug("-------sizeFull bgPos-------");
    debug(this.realH ,'realH');
    debug(this.realW ,'realW');
    debug(this.percent ,'%');

    debug(viewerContent.style.backgroundSize,'backgroundSize');
    debug(viewerContent.style.backgroundPositionX,'bgPos X');
    debug(viewerContent.style.backgroundPositionY,'bgPos Y');
    debug("------------End-------------");
};
/**
 * Размер картинки по умолчанию
 */
ImageViewer.prototype.sizeDefault = function()
{
    if ( this.plusActive === false ) return;
    this.plusActive = false;
    this.percent = 0;

    let imageViewer = document.querySelector('#modalImageViewer');
    let viewerContent = imageViewer.querySelector('.ImageViewerMainImage');

    viewerContent.classList.remove('cursorGrab');
    viewerContent.classList.remove('cursorGrabbing');

    this.setBgSize();
    this.clearMovingVars();
};



/**
 * ПЕРЕТАСКИВАНИЕ КАРТИНКИ
 * @param that
 * @param imageContent
 * @param event
 */
ImageViewer.prototype.mouseUpIMG = function(that, imageContent, event)
{
    event.preventDefault();
    if ( that.plusActive === false ) return;
    that.mouseIsDown = false;

    imageContent.classList.add('cursorGrab');
    imageContent.classList.remove('cursorGrabbing');

    // сохраним координаты картинки
    that.bgPosX = +imageContent.style.backgroundPositionX.split('px')[0];
    that.bgPosY = +imageContent.style.backgroundPositionY.split('px')[0];

    // debug("----------Start MUp----------");
    // debug(that,'that');
    // debug(imageContent,'imageContent');
    // debug(that.bgPosX,'mouseUp bgPosX');
    // debug(that.bgPosY,'mouseUp bgPosY');
    // debug(imageContent.style.backgroundPositionX,'mouseUp backgroundPositionX');
    // debug(imageContent.style.backgroundPositionY,'mouseUp backgroundPositionY');
    // debug("-----------End MUp-----------");
};
ImageViewer.prototype.mouseDownIMG = function(that, imageContent, event)
{
    event.preventDefault();
    if ( that.plusActive === false ) return;
    that.mouseIsDown = true;

    imageContent.classList.remove('cursorGrab');
    imageContent.classList.add('cursorGrabbing');

    // коорд клика мыши
    that.savedMouseX = event.clientX;
    that.savedMouseY = event.clientY;

    // debug("----------Start MDn----------");
    // debug(that,'that');
    // debug(imageContent,'imageContent');
    // debug(that.savedMouseX,'MD savedMouseX');
    // debug(that.savedMouseY,'MD savedMouseY');
    // debug(imageContent.style.backgroundPositionX,'MD backgroundPositionX');
    // debug(imageContent.style.backgroundPositionY,'MD backgroundPositionY');
    // debug("------------End MD-----------");

};
ImageViewer.prototype.mouseMoveIMG = function(that, imageContent, event)
{
    event.preventDefault();
    if ( that.plusActive === false ) return;
    if ( that.mouseIsDown === false ) return;
    if ( that.mouseOnImage === false ) return;

    // координаты в текущий момент
    let currentMouseX = event.clientX;
    let currentMouseY = event.clientY;

    // расстояния на которые надо сдвинуть картинку по X и Y
    let diffX = currentMouseX - that.savedMouseX;
    let diffY = currentMouseY - that.savedMouseY;

    imageContent.style.backgroundPositionX = diffX + that.bgPosX + "px";
    imageContent.style.backgroundPositionY = diffY + that.bgPosY + "px";

    // debug("----------Start Move----------");
    // debug(currentMouseX,'MM currentMouseX');
    // debug(currentMouseY,'MM currentMouseY');
    // debug(diffX,'MM diffX');
    // debug(diffY,'MM diffY');
    // debug(imageContent.style.backgroundPositionX,'MouseMove backgroundPositionX');
    // debug(imageContent.style.backgroundPositionY,'MouseMove backgroundPositionY');
    // debug("------------End Move-----------");

};
ImageViewer.prototype.mouseOutIMG = function()
{
    this.mouseOnImage = false;
    //debug('MOUSE out IMG');
};
ImageViewer.prototype.mouseInIMG = function()
{
    this.mouseOnImage = true;
    //debug('MOUSE IN IMG');
};






/**
 * При клике на доп ставим главной
 * НЕ В ПРОСМОТРЕ
 */
ImageViewer.prototype.mainImageSetter = function()
{
    let that = this;
    let activeImage;

    this.bottomDopImages.forEach(dopImage => {
        if ( dopImage.classList.contains('activeImage') ) activeImage = dopImage;

        dopImage.addEventListener('click', function () {

            let dataID = this.getAttribute('data-id');
            let src = that.images[dataID]['name'];
            let slideNumber = that.images[dataID]['numOrigin'];

            $('#carouselMainImg').carousel(slideNumber);
            that.mainImage = that.carouseItems[slideNumber].children[0];
            that.mainImageLoupe();

            activeImage.parentElement.parentElement.parentElement.classList.remove('activeImage','border-primary');
            activeImage.parentElement.parentElement.parentElement.classList.add("border-light");

            this.parentElement.parentElement.parentElement.classList.remove('border-light');
            this.parentElement.parentElement.parentElement.classList.add('activeImage','border-primary');
            activeImage = this;
        });
    });
};



/**
 * Лупа
 */
ImageViewer.prototype.mainImageLoupe = function()
{
    let imageViewer = this;
    let overImage = false;
    let realClientX, realClientY, loupeDelayID, coordinates;
    let naturalWidth, naturalHeight, realW, realH;
    let self = this;

    //debug(this.mainImage,'this.mainImage');

    this.mainImage.addEventListener('mouseover',function () {
        let that = this;

        let img = new Image(); // создаем картинку
        //img.src = imageViewer.images[ this.getAttribute('data-id') ]['name'];//['img_name'];
        img.src = "/web/stock/" + self.imagesPath + "/images/" + this.getAttribute('data-name');
        img.onload = function() {

            naturalWidth = this.naturalWidth;
            naturalHeight = this.naturalHeight;

            // изначально реальные размеры равны натуральным
            realW = this.naturalWidth;
            realH = this.naturalHeight;

            //debug(naturalWidth + 'x' + naturalHeight);
        };

        loupeDelayID = setTimeout(function () {
            overImage = true;
            coordinates = that.getBoundingClientRect();
            //debug(coordinates);
            //that.style.backgroundSize = realW * 2 + 'px ' + realH * 2 + 'px';//200 + "%";
            that.style.backgroundSize = 200 + "%";
            //realW = realW * 2;
            //realH = realH * 2;
            //debug('realW: ' + realW + 'x realH: ' + realH);
            moveImage(that);
            that.classList.add('loupeCursor');

            // debug(coordinates.left,"left");
            // debug(coordinates.top,"top");
            // debug(coordinates.right,"right");
            // debug(coordinates.bottom,"bottom");
            // debug(coordinates.width,"width");
            // debug(coordinates.height,"height");

        }, 300);

        // ширина высота
        // debug(this.offsetHeight,"offsetHeight");
        // debug(this.offsetWidth,'offsetWidth');
    });
    this.mainImage.addEventListener('mouseout',function () {
        clearTimeout(loupeDelayID);
        overImage = false;
        coordinates = null;
        this.style.backgroundSize = "contain";
        this.style.backgroundPosition = "center center";
        this.classList.remove('loupeCursor');
    });
    this.mainImage.addEventListener('mousemove',function (event) {
        realClientX = event.clientX;
        realClientY = event.clientY;
        if ( coordinates === null || overImage === false ) return;
        //debug(event.clientX,"realClientX");
        //debug(event.clientY,"realClientY");
        moveImage(this);
    });

    function moveImage(mainImage)
    {
         let x = -( (realClientX - coordinates.left)-150 ) * 2;
         let y = -( (realClientY - coordinates.top)-75 ) * 2;

        //let per = realW / coordinates.width; //%
        //let x = - (realClientX-coordinates.left) / 3.9;
        //let y = -( (realClientY - coordinates.top) ) * ( realH / coordinates.height );

        // if ( x > coordinates.x ) x = coordinates.x;
        // if ( x < -coordinates.width ) x = -coordinates.width;

        // if ( y > coordinates.y ) y = coordinates.y;
        // if ( y < -coordinates.height ) y = -coordinates.height;

        //if ( x > coordinates.right ) x = coordinates.right;
        // debug(y,"bgY");
        // debug(x,"bgX");
        // debug(coordinates.bottom,"bottom");
        // debug(coordinates.right,"Right");
        //if ( (y + realH) < coordinates.bottom ) y = coordinates.bottom;
        // debug(event.clientX - coordinates.left,"X");
        // debug(event.clientY - coordinates.top,"Y");

        mainImage.style.backgroundPositionX = x + "px";
        mainImage.style.backgroundPositionY = y + "px";
    }
};


debug(JSON.parse(localStorage.getItem('listNames'), 'listNames: '));
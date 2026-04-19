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
    this.activeMainSlide = null; // активная 

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
    // on view page
    this.mainImage = document.querySelector(".mainImage");

    this.bottomDopImages = document.querySelector(".dopImages").querySelectorAll(".imageSmall");
    this.modelID = document.getElementById('modalImageViewer').getAttribute('data-modelid');
}

// This code run just one time
ImageViewer.prototype.init = function()
{
    let that = this;
    let imageViewer = document.querySelector('#modalImageViewer');

    $('#carouselMainImg').carousel({
      interval: 6000
    });

    this.mainImageSetter();
    this.mainImageLoupe();

    //carousel has completed its slide transition.
    $('#carouselMainImg').on('slide.bs.carousel', function (event) {
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
            
        }
    });

    // Image Viewer modal just start opening
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

    // Image Viewer modal has open
    $(document).on('opened', '#modalImageViewer', function () {

        // Adding move event for all slides only one time when modal is open
        if ( that.attachedEvents === false )
        {
            let allslides = document.getElementById("carousel_ImageViewer").querySelectorAll('.IV-main-slide');
            allslides.forEach(slide => {
                
                slide.addEventListener('mousedown', that.mouseDownIMG.bind(event, that, slide), false );
                slide.addEventListener('mouseup', that.mouseUpIMG.bind(event, that, slide), false );
                slide.addEventListener('mousemove', that.mouseMoveIMG.bind(event, that, slide), false );

                slide.addEventListener('mouseout', function () {
                    that.mouseOutIMG();
                }, false );
                
                slide.addEventListener('mouseover', function () {
                    that.mouseInIMG();
                }, false );

                slide.addEventListener('mouseup', function (event) {
                    if ( that.plusActive === true && that.mouseIsDown === true && that.mouseOnImage === false )
                    {
                        that.mouseUpIMG(that, slide, event);
                    }
                }, false );

            });

            that.attachedEvents = true;
        }

        document.body.style.overflow = 'hidden'; // убираем полосу прокрутки
    });

    // // Image Viewer modal just start closing
    $(document).on('closing', '#modalImageViewer', function (event){
        event.preventDefault();
        that.stop();
        document.getElementById('modalImageViewerContent').classList.add('d-none');
        $('#carouselMainImg').carousel();
    });
    // исчезло
    $(document).on('closed', '#modalImageViewer', function () {
        document.body.style.overflow = 'auto';
    });

    //adding listeners for carusel items on View
    this.carouseItems.forEach(tslide => {
        let slide = tslide.children[0];

        slide.addEventListener('click', function(event)
        {
            event.preventDefault();
            if ( _IS_DESKTOP_ ) 
                $('#modalImageViewer').iziModal('open');
        });

    });

    /**  Image Size  **/
    let top_buttons = imageViewer.querySelector('.dopButtons').querySelectorAll('a');

    top_buttons[0].addEventListener('click',function (event)
    {
        event.preventDefault();

        $('#carousel_ImageViewer').carousel('pause');
        that.sizeIncrease();
    }, false);

    top_buttons[1].addEventListener('click', function (event)
    {
        event.preventDefault();

        $('#carousel_ImageViewer').carousel('pause');
        that.sizeDecrease();
    }, false);

    top_buttons[2].addEventListener('click', function (event)
    {
        event.preventDefault();

        $('#carousel_ImageViewer').carousel('pause');
        that.sizeFull();
    }, false);

    top_buttons[3].addEventListener('click', function (event)
    {
        event.preventDefault();

        $('#carousel_ImageViewer').carousel();
        that.sizeDefault();
    }, false);

    debug('Image Viewer 2.5 Init');


};

/**
 * запускаем просмотр картинок при клике на главную
 */
ImageViewer.prototype.start = function()
{
    let that = this;
    let selectedImageID = this.mainImage.getAttribute('data-id');
    let slideNumber = this.images[selectedImageID]['numOrigin'];

    $('#carousel_ImageViewer').carousel(slideNumber);

    this.setBackgroundImage( this.images[selectedImageID].name, slideNumber );
    this.smallImagesRow(selectedImageID);

    //carousel has just start its slide transition.
    $('#carousel_ImageViewer').on('slide.bs.carousel', function (event) {

        let prev = that.insideDopImages[event.from];
        let next = that.insideDopImages[event.to];

        //Bottom images
        prev.classList.remove('activeImage','border-primary');
        prev.classList.add('border-secondary');
        next.classList.remove('border-secondary');
        next.classList.add('activeImage','border-primary');

        $.each(that.images, function(id, imgO) {
            if ( +imgO.numOrigin === +event.to )
                return that.setBackgroundImage(imgO.name, event.to);
        });

        $('#carousel_ImageViewer').carousel();
    });

    //carousel has completed its slide transition.
    $('#carousel_ImageViewer').on('slid.bs.carousel', function (event) {

    });

};


/**
 *    Устанавливаем картинку в просмотре
 */
ImageViewer.prototype.setBackgroundImage = function(name, slidenum)
{
    let imgSrc = "/stock/" + this.imagesPath + "/images/" + name;
    let viewerContent = document.querySelector('#modalImageViewer').querySelector('.ImageViewerMainImage');

    viewerContent.classList.remove('cursorGrab');
    viewerContent.classList.remove('cursorGrabbing');

    let allslides = document.getElementById("carousel_ImageViewer").querySelectorAll('.IV-main-slide');
    let activeMainSlide = allslides[slidenum];
    if ( activeMainSlide.getAttribute('data-type') == 'video' )
        return this.activeMainSlide = activeMainSlide;
    

    let that = this;
    let loadimg = new Image(); // создаем картинку
    loadimg.src = imgSrc;
    loadimg.onload = function() {

        //debug(this,'loadimg'); //just img tag
        that.nW = this.naturalWidth;
        that.nH = this.naturalHeight;

        // изначально реальные размеры равны натуральным
        that.realW = this.naturalWidth;
        that.realH = this.naturalHeight;

        debug(this.width + 'x' + this.height,"loadimg w+h");
        debug(this.naturalWidth + 'x' + this.naturalHeight,"loadimg nW+nH");

        that.setBgSize(slidenum);
    };
    document.body.style.overflow = 'hidden'; // убираем полосу прокрутки
};

ImageViewer.prototype.setBgSize = function( current_slide_num ) // ставит backgroundSize на просмотре
{
    let allslides = document.getElementById("carousel_ImageViewer").querySelectorAll('.IV-main-slide');
    this.activeMainSlide = allslides[current_slide_num];

    //debug(this.activeMainSlide);

    let screenW = document.documentElement.clientWidth;
    let screenH = document.documentElement.clientHeight;

    if ( this.nH > screenH || this.nW > screenW ) {
        this.activeMainSlide.style.backgroundSize = "contain";

        debug('contain_SetBgSize: ' +  this.activeMainSlide.style.backgroundSize);
    } else {
        this.activeMainSlide.style.backgroundSize = "auto";

        debug('auto_SetBgSize: ' + this.activeMainSlide.style.backgroundSize);
    }
    this.activeMainSlide.style.backgroundPositionX = "";
    this.activeMainSlide.style.backgroundPositionY = "";

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

        let div;
        if ( image.type == 'video' ) {
            div = document.createElement('video');
        } else {
            div = document.createElement('div');
        }

        div.classList.add('p-0','cursorPointer','imageSmall','imageViewerSmallImgDown','border');
        div.setAttribute('data-num-origin',image.numOrigin);
        div.setAttribute('data-id',image.id);

        if ( image.type == 'video' ) {
            let source = document.createElement('source');
            source.setAttribute('type','video/mp4');
            source.src = fullsrc;
            div.appendChild(source);
        } else {
            div.style.backgroundImage = "url("+ fullsrc  +")";
        }

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
    if ( this.activeMainSlide.getAttribute('data-type') == 'video' ) return;

    if ( this.percent >= 200 ) return; // макс увеличение в 2 раза

    if ( this.plusActive !== true ) 
        this.plusActive = true;
    
    this.percent += 25;
    if ( this.percent > 200 ) this.percent = 200;

    this.realW = (this.nW * this.percent ) / 100;
    this.realH = (this.nH * this.percent ) / 100;

    this.activeMainSlide.style.backgroundSize = this.realW + "px" + "," + this.realH + "px";
    this.setBgPosition(this.activeMainSlide);

    this.activeMainSlide.classList.add('cursorGrab');

    //debug(this.percent,'+%');
    //debug(this.realH ,'realH');
    //debug(this.realW ,'realW');
};

// уменьшим картинку на 1/4
ImageViewer.prototype.sizeDecrease = function() 
{
    if ( this.activeMainSlide.getAttribute('data-type') == 'video' ) return;

    if ( this.percent <= 25 ) return;
    if ( this.plusActive !== true )
        this.plusActive = true;

    this.percent -= 25;
    if ( this.percent <= 25 ) this.percent = 25;

    this.realW = (this.nW * this.percent ) / 100;
    this.realH = (this.nH * this.percent ) / 100;

    this.activeMainSlide.style.backgroundSize = this.realW + "px" + "," + this.realH + "px";
    this.setBgPosition(this.activeMainSlide);

    this.activeMainSlide.classList.add('cursorGrab');

    //debug(this.percent,'-%');
    //debug(this.realH ,'realH');
    //debug(this.realW ,'realW');
};
/**
 * увеличим картинку на полную
 */
ImageViewer.prototype.sizeFull = function()
{
    if ( this.activeMainSlide.getAttribute('data-type') == 'video' ) return;

    this.plusActive = true;
    this.realH = this.nH;
    this.realW = this.nW;
    this.percent = 100;

    this.activeMainSlide.style.backgroundSize = 'auto auto';
    this.setBgPosition(this.activeMainSlide);

    this.activeMainSlide.classList.add('cursorGrab');

    /*
    debug("-------sizeFull bgPos-------");
    debug(this.realH ,'realH');
    debug(this.realW ,'realW');
    debug(this.percent ,'%');

    debug(this.activeMainSlide.style.backgroundSize,'backgroundSize');
    debug(this.activeMainSlide.style.backgroundPositionX,'bgPos X');
    debug(this.activeMainSlide.style.backgroundPositionY,'bgPos Y');
    debug("------------End-------------");
    */
};
/**
 * Размер картинки по умолчанию
 */
ImageViewer.prototype.sizeDefault = function()
{
    if ( this.activeMainSlide.getAttribute('data-type') == 'video' ) return;

    if ( this.plusActive === false ) return;
    this.plusActive = false;
    this.percent = 0;

    this.activeMainSlide.classList.remove('cursorGrab');
    this.activeMainSlide.classList.remove('cursorGrabbing');

    this.setBgSize( this.activeMainSlide.getAttribute('data-num') );
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
 * RUNNING ONE TIME
 */
ImageViewer.prototype.mainImageSetter = function()
{
    let that = this;
    let activeImage;

    //Setting first main image tag for proper loupe
    $.each(this.images, function(id, img) {
        if ( +img.status === 1 ){
            return that.mainImage = that.carouseItems[img.numOrigin].children[0];
        }
    });

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
    let type = this.mainImage.getAttribute('data-type');
    if ( type == 'video' ) return;

    let self = this;

    let lupeData = {
        realClientX: null,
        realClientY: null,
        naturalWidth: null,
        naturalHeight: null,
        realW: null,
        realH: null,
        coordinates: null,
        loupeDelayID: null,
        bgsizeTotal: 120,
        overImage: false,
    };
    let prevSlidBeutton = document.getElementById('carouselMainImg').querySelector('.carousel-control-prev');
    let nextSlidBeutton = document.getElementById('carouselMainImg').querySelector('.carousel-control-next');

    /** MOUSE OVER **/
    if ( this.mainImage.getAttribute('listMOver') != 'true' ) {

        this.mainImage.addEventListener('mouseover',function () {
            if ( type == 'video' ) return;

            self.loupeOver(this,lupeData,prevSlidBeutton,nextSlidBeutton);
        });
        
        this.mainImage.setAttribute('listMOver',true);
    }

    /** MOUSE OUT **/
    if ( this.mainImage.getAttribute('listMOut') != 'true' ) {

        this.mainImage.addEventListener('mouseout',function () {
            if ( type == 'video' ) return;

            prevSlidBeutton.classList.remove('d-none');
            nextSlidBeutton.classList.remove('d-none');

            self.loupeOut(this, lupeData);
        });
        
        this.mainImage.setAttribute('listMOut',true);
    }

    /** MOUSE MOVE **/
    if ( this.mainImage.getAttribute('listMMove') != 'true' ) {

        this.mainImage.addEventListener('mousemove',function (event) {
            if ( type == 'video' ) return;
            self.loupeMove(this,event,lupeData);
        });

        this.mainImage.setAttribute('listMMove',true);
    }

    /** Wheel scroll **/
    if ( this.mainImage.getAttribute('listWheel') != 'true' ) {

        this.mainImage.addEventListener('wheel',function (event) {
            if ( type == 'video' ) return;
            
            self.loupeSize(this,event,lupeData);
        });

        this.mainImage.setAttribute('listWheel',true);
    }
};
ImageViewer.prototype.loupeSize = function(mainimg,event,lupeData)
{
    event.preventDefault();
    event.stopPropagation();

    let step = -(event.deltaY / 5);

    lupeData.bgsizeTotal += step;
    if ( lupeData.bgsizeTotal > 300 ) lupeData.bgsizeTotal = 300;
    if ( lupeData.bgsizeTotal < 50 ) lupeData.bgsizeTotal = 50;
        
    mainimg.style.backgroundSize = lupeData.bgsizeTotal + "%";
    
    //debug(step);
    //debug(lupeData.bgsizeTotal,'bgsizeTotal');
};
ImageViewer.prototype.loupeOver = function(mainimg,lupeData,prevSlidBeutton,nextSlidBeutton) {
    let that = this;
    let img = new Image(); // создаем картинку
    img.src = "/web/stock/" + this.imagesPath + "/images/" + mainimg.getAttribute('data-name');
    img.onload = function() {

        lupeData.naturalWidth = this.naturalWidth;
        lupeData.naturalHeight = this.naturalHeight;

        // изначально реальные размеры равны натуральным
        lupeData.realW = this.naturalWidth;
        lupeData.realH = this.naturalHeight;

        //debug(naturalWidth + 'x' + naturalHeight);
    };

    lupeData.loupeDelayID = setTimeout(function () {
        lupeData.overImage = true;
        lupeData.coordinates = mainimg.getBoundingClientRect();
        //debug(coordinates);
        mainimg.style.backgroundSize = lupeData.bgsizeTotal + "%";
        that.moveImage(mainimg, lupeData);
        mainimg.classList.add('loupeCursor');

        prevSlidBeutton.classList.add('d-none');
        nextSlidBeutton.classList.add('d-none');

        // debug(coordinates.left,"left");
        // debug(coordinates.top,"top");
        // debug(coordinates.right,"right");
        // debug(coordinates.bottom,"bottom");
        // debug(coordinates.width,"width");
        // debug(coordinates.height,"height");

    }, 350);

    // ширина высота
    // debug(this.offsetHeight,"offsetHeight");
    // debug(this.offsetWidth,'offsetWidth');
};
ImageViewer.prototype.loupeOut = function(img,lupeData)
{
    clearTimeout(lupeData.loupeDelayID);
    lupeData.overImage = false;
    lupeData.coordinates = null;
    img.style.backgroundSize = "contain";
    img.style.backgroundPosition = "center center";
    lupeData.bgsizeTotal = 120;
    img.classList.remove('loupeCursor');
};
ImageViewer.prototype.loupeMove = function(img,event,lupeData)
{
    lupeData.realClientX = event.clientX;
    lupeData.realClientY = event.clientY;
    if ( lupeData.coordinates === null || lupeData.overImage === false ) return;
    //debug(event.clientX,"realClientX");
    //debug(event.clientY,"realClientY");
    this.moveImage(img, lupeData);
};
ImageViewer.prototype.moveImage = function(mainImage,lupeData)
{
    //debug(mainImage);
    let x = -( (lupeData.realClientX - lupeData.coordinates.left)-150 ) * 2;
    let y = -( (lupeData.realClientY - lupeData.coordinates.top)-75 ) * 2;

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
};

//debug(JSON.parse(localStorage.getItem('listNames'), 'listNames: '));
"use strict";

function Search()
{
    this.sInput = document.getElementById('search_row'); 
    this.sButton = document.getElementById('search_button'); 
    this.pButton = document.getElementById('purge_button'); 
    this.fromButton = document.getElementById('createdatefrom'); 
    this.toButton = document.getElementById('createdateto'); 
    this.controlRange = document.getElementById('tilesControlRange'); 
	this.init();
}

/** INITIALIZATION **/
Search.prototype.init = function()
{
    let self = this;
    if ( this.sInput ) {
        this.sInput.onchange = function()
        {
            let obj = {
                search_for : self.sInput.value,
            };
            self.request("set", obj);
        }
    }
    if ( this.sButton ) {
        this.sButton.onclick = function()
        {
            let obj = {
                search_for : self.sInput.value,
            };
            self.request("set",obj);
        }
    }
    if ( this.pButton ) {
        this.pButton.onclick = function()
        {
            let obj = {
                clean : 1,
            };
            self.request("purge",obj);
        }
    }

    if ( this.fromButton ) {
        this.fromButton.onchange = function()
        {
            let obj = {
                date : this.value,
            };
            self.request("from-date",obj);
        }
    }
    if ( this.toButton ) {
        this.toButton.onchange = function()
        {
            let obj = {
                date : this.value,
            };
            self.request("to-date",obj);
        }
    }

    if ( this.controlRange ) {
        this.controlRange.onchange = function()
        {
            let obj = {
                size : this.value,
            };
            self.tilesControlSize(obj);
        }
    }

    //if ( hashTagsCheckApply )
    //{
        this.hashTagsCheckApply();
    //}
        this.clientsMenuImproove();

    debug('Search init fine');
};

Search.prototype.request = function( url, obj )
{    
    $.ajax({
        url: "/search/"+ url +"/",
        type: 'POST',
        data: obj,
        dataType:"json",
        success:function(resp) {
            //if (resp) reload(true);
            if (resp) redirect("/site");
        }
    });
};
Search.prototype.tilesControlSize = function( obj )
{    
    $.ajax({
        url: "/search/control-size/",
        type: 'POST',
        data: obj,
        dataType:"json",
        success:function(resp) {
            if (resp['done']) {
                
                let cardsDoc = document.getElementById('cards');
                let cards = cardsDoc.querySelectorAll('.mainCard');
                
                cards.forEach(card => {
                    card.style.width = resp.size + "rem";
                });
            }
        }
    });
};

Search.prototype.hashTagsCheckApply = function() 
{
    let hashtags = document.querySelector('#modal_hashtags').querySelectorAll('input');
    let self = this;

    $.each(hashtags, function(i, input) {
        self.singleHashtagCheck(input);
    });

//method hashTagCheck
};

Search.prototype.singleHashtagCheck = function(input)
{
    let self = this;

    input.addEventListener('click', function () {

        let selfInpt = this;
        let tagname = this.getAttribute('value');

        let obj = {
            value   : tagname,
        };
        $.ajax({
            url: "/search/hash?tag=" + tagname,
            type: 'POST',
            data: obj,
            dataType:"json",
            success:function(resp) {
                console.log(resp);

                if (resp == true) selfInpt.removeAttribute('checked')
                if (resp == true) selfInpt.setAttribute('checked','')
            }
        });
    });
};

Search.prototype.clientsMenuImproove = function(input)
{
    let cldpdmenu = document.getElementById('cl-dpd-menu');
    if (!cldpdmenu) return;
    
    let cldpdnames = cldpdmenu.querySelectorAll('.cl-dpd-name');
    let SHIFT_IS_PUSHED = false;

    $.each(cldpdnames, function(i, clname) 
    {
        clname.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            if ( SHIFT_IS_PUSHED )
            {
                let newhref = this.getAttribute('href');
                newhref += '&cladd=1';
                this.setAttribute('href',newhref);
            }

            redirect(this.href);
        });

        // Its need for proper work keydown event on element...
        clname.addEventListener("mouseover", function (event) {
            this.focus();
 
        });

        // SHIFT IS PUSHED
        clname.addEventListener("keydown", function (event) {
            event.preventDefault();
            event.stopPropagation();
            
            let ec = event.code;
            if (ec === "ShiftLeft" || ec === "ControlLeft" || ec === "ControlRight" || ec === "ShiftRight") {
                SHIFT_IS_PUSHED = true;
            }
            
        },false);

        // SHIFT IS UP
        clname.addEventListener("keyup", function (event) {
            event.preventDefault();
            event.stopPropagation();
            
            let ec = event.code;
            if (ec === "ShiftLeft" || ec === "ControlLeft" || ec === "ControlRight" || ec === "ShiftRight") {
                SHIFT_IS_PUSHED = false;
            }
            
        });
        
    });
    
};


let search_for = new Search();
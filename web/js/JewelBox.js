"use strict";
class JewelBox 
{
    constructor()
    {
        this.jbBadgeTag = document.querySelector('.jbBadge');
        this.jbBadgeCount = this.jbBadgeTag.innerHTML;

        this.jbBtns = document.querySelectorAll('.jewelboxBtnMain');
        if ( this.jbBtns ) this.btnsClickApply(this.jbBtns, "add");

        this.editBtns = document.querySelectorAll('.editbtnJewelBox');
        if ( this.editBtns ) this.btnsClickApply(this.editBtns, "edit");

        this.queryObj = {
            modelID: '',
            comment: '',
            price: '',
        };
    }

    btnsClickApply( btns, condition )
    {
        let self = this;
        $.each(btns, function(i, btn){
            btn.addEventListener('click', function(e) {
                self.jbModalShow(e, btn, condition);
            },false);    
        });
    }
    
    jbModalShow( e, btn, condition ) {
        e.preventDefault();
        e.stopPropagation();

        let modelData = btn.firstElementChild;

        let modelLink = modelData.getAttribute('data-link');
        let modelImg = modelData.getAttribute('data-img');
        let modelN3d = modelData.getAttribute('data-n3d');
        let modelType = modelData.getAttribute('data-mtype');
        let modelClient = modelData.getAttribute('data-client');

        let modal = document.getElementById('jewel-box-modal');
        let jbcomment,label = "";

        if ( condition == "add" ) label = "Добавить в шкатулку: ";
        if ( condition == "edit" ) {
            jbcomment = btn.parentElement.parentElement.querySelector('.jbcomment').innerHTML;
            label = "Редактировать: ";
        }

        modal.querySelector('#jbModalLabel').innerHTML = label;

        modal.querySelector('.mjb-img').src = modelImg;
        modal.querySelector('.mjb-mtype').innerHTML = modelN3d + " / " + modelType;
        modal.querySelector('.mjb-client').innerHTML = modelClient;
        modal.querySelector('#mjb-commenttext').innerHTML = jbcomment;
        modal.querySelector('.mjb-link').href = modelLink;

        let self = this;
        modal.querySelector('#mjb-submit').onclick = function()
        {
            self.queryObj.modelID = btn.getAttribute('data-id');
            self.queryObj.comment = modal.querySelector('#mjb-commenttext').value;
            //debug(self.queryObj);
            self.pushJBData(condition);
        };

        $('#jewel-box-modal').modal('show');
    }

    pushJBData(condition) {
        let self = this;
        $.ajax({
            url: "/site/jewel?box=" + condition,
            type: 'POST',
            data: self.queryObj,
            dataType:"json",
            success:function(resp) {
                debug(resp);
                if (resp) {
                    //AR.serverError(resp);
                    $('#jewel-box-modal').modal('hide');
                    if ( condition == "edit" ) reload(true);
                } 
            }
        });
    }

}
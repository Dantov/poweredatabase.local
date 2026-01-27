"use strict";

function sideBarTrianglesSwitch()
{
	debug('here');
	// Sidebar triangles toggle
    let sidemenu = document.querySelector('.components');
    let allA = sidemenu.querySelectorAll('a[data-toggle="collapse"]');
    sidemenu.addEventListener('click',function(e){
        let click = e.target;
        let b;
        b = (click.hasAttribute('data-toggle') || click.classList.contains('fa-angle-down') || click.classList.contains('fa-angle-left'));
        if ( !b ) return;
        if ( click.hasAttribute('data-toggle') ){
            click.lastElementChild.classList.toggle('fa-angle-down');
            click.lastElementChild.classList.toggle('fa-angle-left');    
        } else {
            click.classList.toggle('fa-angle-down');
            click.classList.toggle('fa-angle-left');
        }
    },false);
}

function jewelboxButtos()
{
	// jbox buttons
    let jbBtns = document.querySelectorAll('.jewelboxBtnMain');
    function jbBtnsClick(jbBtns)
    {
        $.each(jbBtns, function(i, btn){
            btn.addEventListener('click',function(e){
                e.preventDefault();
                e.stopPropagation();
                let mID = this.getAttribute('data-id');
                $.ajax({
                    url: "/site/jewel?box=add",
                    type: 'POST',
                    data: {
                        modelID: mID,
                    },
                    dataType:"json",
                    success:function(resp) {
                    	debug(resp);
                        //if (resp) reload(true);
                    }
                });
            },false);    
        });
    }
    if ( jbBtns ) jbBtnsClick(jbBtns);
}

$(document).ready(function () {

	//Sidebar-nav Js
	$('#sidebarCollapse').on('click', function () {
	    $('#sidebar').toggleClass('active');
	});
	//Tooltip
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	});
	//hideURLbar
	window.scrollTo(0, 1);

	sideBarTrianglesSwitch();
	jewelboxButtos();
 });


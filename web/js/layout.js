"use strict";
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

	let jewelbox = new JewelBox();
 });
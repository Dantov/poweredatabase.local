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

	let jewelbox = new JewelBox();
 });
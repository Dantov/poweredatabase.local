"use strict";
function AddEdit( content )
{
	if ( !content ) return;
	this.content = content;
	
	this.modelID = this.content.querySelector('#modelID').value;

	this.inputs = this.content.querySelectorAll('[editable]');

	this.validator = new Validator();

  this.init();
}

AddEdit.prototype.init = function()
{

  this.clickHandler();
  
  //applyEvents
  this.applyEventsChange(this.inputs);
  this.hashTagsCheckApply();
  this.pubExclDellModel();

  debug( 'AddEdit init ok' );
};

AddEdit.prototype.applyEventsChange = function(inputs)
{
	let self = this;
	for( let i = 0; i < inputs.length; i++ ) {
		inputs[i].addEventListener('change', function(event){ 
			self.changeInpt( this, self, event );
		}, false);
	}
};

AddEdit.prototype.changeInpt = function(input, self, event)
{
	if (event){
		event.stopPropagation();
  	event.preventDefault();	
	}

	let name = input.getAttribute('name');
	let value = input.value;//this.getAttribute('value');
	let rowID = input.getAttribute('data-rowID');
	let tableName = input.getAttribute('data-table');
	let modID = self.modelID;

	let obj = {
			name : name,
			id   : rowID,
			tableName : tableName,
			value  : value,
			modelID : modID,
	};
	
	$.ajax({
		url: "/site/edit/",
		type: 'POST',
		data: obj,
		dataType:"json",
		success:function(resp) {
			console.log(resp);

			let bg = input.previousElementSibling.children[0];
			let svg = input.previousElementSibling.children[0].children[0];

			function okToggle( back )
			{
				if ( back ) {
					bg.classList.replace('badge-success','badge-light');
					svg.remove();
					bg.innerHTML = '<i class="fa-regular fa-square-full"></i>';
		
				} else {
					bg.classList.replace('badge-light','badge-success');
					bg.innerHTML = '<i class="fa-regular fa-square-check"></i>';
				}
			}

			function errToggle( back )
			{
				if ( back ) {
					bg.classList.replace('badge-danger','badge-light');
					svg.remove();
					bg.innerHTML = '<i class="fa-regular fa-square-full"></i>';
				} else {
					bg.classList.replace('badge-light','badge-danger');
					bg.innerHTML = '<i class="fa-regular fa-circle-xmark"></i>';
				}
			}

			if ( resp ) 
			{
				okToggle();
				setTimeout(function() {
					okToggle( true );
				}, 1500);
			} else {
				errToggle();
				setTimeout(function() {
					errToggle( true );
				}, 2500);
			}
		
		}
		
	});
	
}

AddEdit.prototype.clickHandler = function()
{
	let self = this;

	this.content.addEventListener('click', function(event) {
		let click = event.target;
		if ( !click.hasAttribute('elemToAdd') ) return;

		let target = click.parentElement.parentElement.previousElementSibling;

		//target.setAttribute('value', click.innerHTML );
		target.value = click.innerHTML;
		self.changeInpt(target,self);
	});


	let addMats = document.getElementById('addMats');
	if ( addMats ) 
	{
		addMats.addEventListener('click', function(event){
		    event.preventDefault();
		    self.addRowNew('matsProtoRow','tableMats');
		}, false );
	}

	let addGem = document.getElementById('addGems');
	if ( addGem )
	{
	    addGem.addEventListener('click', function(event){
	        event.preventDefault();
	        self.addRowNew('gemsProtoRow','tableGems');
	    }, false );
	}	
};

AddEdit.prototype.addRowNew = function( protoName, targetTableName)
{
	let self = this;
	let tName = '';

	if (targetTableName == 'tableMats') tName = 'materials';
	if (targetTableName == 'tableGems') tName = 'gems';

	let obj = {
				tableName : tName,
				modelID : this.modelID,
			};
	$.ajax({
		url: "/site/edit/?linktable=" + tName,
		type: 'POST',
		data: obj,
		dataType:"json",
		success:function(newRowID) {
			debug(newRowID,'addRow on '+ targetTableName +': ');
			
			if (newRowID) {
				let table = self.content.querySelector('.' + targetTableName);
				let protoRow = '';
				let newRow = self.content.querySelector('.' + protoName).cloneNode(true);
					  newRow.classList.remove('d-none',protoName);

				let inputs = newRow.querySelectorAll('input');
						inputs.forEach(input => {
				    	input.setAttribute('data-table',targetTableName);
				    	input.setAttribute('data-rowID',newRowID);
						});
						
				let buttons = newRow.querySelectorAll('button');
						buttons.forEach(btn => {
				    	btn.setAttribute('data-table',targetTableName);
				    	btn.setAttribute('data-rowID',newRowID);
						});
						
				self.applyEventsChange(inputs);

				/*
				$.foreach(inputs, function(i,input){
				});
				*/

				table.appendChild(newRow);
			}
		}
	});
	  
};

AddEdit.prototype.duplicateRowNew = function( self ) 
{

	let tName = self.getAttribute('data-table');
	let rowID = self.getAttribute('data-rowID');

	let obj = {
				tableName : tName,
				rowID : rowID,
				modelID : this.modelID,
	};

	let that = this;
	
	$.ajax({
		url: "/site/edit/?duplicate=" + tName,
		type: 'POST',
		data: obj,
		dataType:"json",
		success:function( newRowID ) {
			debug(newRowID,'addRow on '+ tName +': ');
			
			if (newRowID) {
				let table = that.content.querySelector('.' + tName);
				let thisRow = self.parentElement.parentElement.parentElement.parentElement;
				let newRow = thisRow.cloneNode(true);

				let inputs = newRow.querySelectorAll('input');
						inputs.forEach(input => {
				    	input.setAttribute('data-rowID',newRowID);
						});
				that.applyEventsChange(inputs);

				table.insertBefore(newRow, thisRow);
			}
		}
	});
};

AddEdit.prototype.deleteRowNew = function( self ) 
{
	let tName = self.getAttribute('data-table');
	let rowID = self.getAttribute('data-rowID');

	let obj = {
				tableName : tName,
				rowID : rowID,
				modelID : this.modelID,
			};
		debug(obj);

	$.ajax({
		url: "/site/edit/?dellrow=" + tName,
		type: 'POST',
		data: obj,
		dataType:"json",
		success:function( dellwRowID ) {
			debug(dellwRowID,'dellRow on '+ tName +': ');
			let row = self.parentElement.parentElement.parentElement.parentElement;
			row.remove();
		}
	});
	
};

AddEdit.prototype.hashTagsCheckApply = function() 
{
	let hashtags = document.querySelector('#hashtags').querySelectorAll('input');
	let self = this;

	$.each(hashtags, function(i, input) {

		self.singleHashtagCheck(input);

	});

//method hashTagCheck
};

AddEdit.prototype.singleHashtagCheck = function(input)
{
	let self = this;

	input.addEventListener('click', function () {

	let selfInpt = this;
	if ( this.hasAttribute('checked') ) {
		let obj = {
					name    : this.getAttribute('name'),
					dell    : 1,
					value   : this.getAttribute('value'),
					modelID : self.modelID,
				};
				$.ajax({
					url: "/site/edit/",
					type: 'POST',
					data: obj,
					dataType:"json",
					success:function(resp) {
						console.log(resp);
						if (resp == true) selfInpt.removeAttribute('checked')
					}
				});
	} else {
		let obj = {
				name    : this.getAttribute('name'),
				value   : this.getAttribute('value'),
				modelID : self.modelID,
				};
		$.ajax({
				url: "/site/edit/",
				type: 'POST',
				data: obj,
				dataType:"json",
				success:function(resp) {
					console.log(resp);
					if (resp == true) selfInpt.setAttribute('checked','')
				}
			});
		}
	});
};

AddEdit.prototype.hashtagByText = function(textarea)
{
	let that = this;

	let obj = {
			hashtagByText : 1,
			name    : textarea.getAttribute('name'),
			value   : textarea.value,
			modelID : that.modelID,
	};
	$.ajax({
		url: "/site/edit/",
		type: 'POST',
		data: obj,
		dataType:"json",
		success:function(resp) {
			console.log(resp);
			if (resp == true) {
				let hashtags = that.content.querySelector('#hashtags');
				let newtag = hashtags.firstElementChild.cloneNode(true);
						newtag.classList.add('active');
						newtag.firstElementChild.setAttribute('value',textarea.value);
						newtag.firstElementChild.setAttribute('checked','');
						newtag.firstElementChild.nextElementSibling.innerHTML = textarea.value;
						that.singleHashtagCheck(newtag.firstElementChild);
						
				hashtags.appendChild(newtag);
			}
		}
	});
};

AddEdit.prototype.pubExclDellModel = function()
{
		let buttons = document.getElementById('publishRow').querySelectorAll('button');
		let reqest = {
					url : '',
					modelID : this.modelID,
				};
		let self = this;
		$.each(buttons, function(i, button) {
			button.addEventListener('click', function(event) {
				self.submitButtons(this, reqest);
			});
		});
};

AddEdit.prototype.submitButtons = function( button, reqest )
{
	switch ( button.getAttribute('data-publish') )
	{
		case "pub":
			if (!confirm('Все данные верны? Опубликлвать модель?'))
				return;
			reqest.url = "publish";
			if ( !this.validator.validate() ) return;
			break;
		case "excl":
			if (!confirm('Исключить модель из поиска?'))
				return;
			reqest.url = "exclude";
			break;
		case "del":
			if (!confirm('Удалить модель?'))
				return;
			reqest.url = "deletemodel";
			break;
	}
	$.ajax({
					url: "/site/edit/?" + reqest.url + "=1",
					type: 'POST',
					data: reqest,
					dataType:"json",
					success:function( resp ) {
						switch ( resp ) {
							case "publish":
								alert('Model is published successfully!');
								reload(true);
								break;
							case "exclude":
								alert('Model is exclude from search successfully!');
								reload(true);
								break;
							case "delete":
								alert('Model is deleted!');
								reload(true);
								break;
						}
					}
				});
}

let ae = new AddEdit( document.querySelector('.content') );

function duplicateRow(self) { ae.duplicateRowNew(self); }
function deleteRow(self) { ae.deleteRowNew(self); }
function hashtagByText(self) { ae.hashtagByText(self); }
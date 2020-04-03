/*
 * Copyright (C) 2013-2020 Combodo SARL
 *
 * This file is part of iTop.
 *
 * iTop is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * iTop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 */

/*
 * Copyright (C) 2013-2019 Combodo SARL
 *
 * This file is part of iTop.
 *
 * iTop is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * iTop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 */

;
$(function()
{
	// the widget definition, where 'itop' is the namespace,
	// 'lifecycle_sneakpeek_backoffice' the widget name
	$.widget( 'itop.lifecycle_sneakpeek_backoffice', $.itop.lifecycle_sneakpeek,
		{
			// default options
			options:
			{  // If no content on initialization, will be fetched from endpoint
				endpoint: null
			},

			// the constructor
			_create: function()
			{
				var me = this;

				this.element
					.addClass('lifecycle_sneakpeek_backoffice');

				this._super();
			},

			// called when created, and later when changing options
			_refresh: function()
			{

			},
			// events bound via _bind are removed automatically
			// revert other modifications here
			_destroy: function()
			{
				this.element
					.removeClass('lifecycle_sneakpeek_backoffice');

				this._super();
			},
			// _setOptions is called with a hash of all options that are changing
			// always refresh when changing options
			_setOptions: function()
			{
				this._superApply(arguments);
			},
			// _setOption is called for each individual option that is changing
			_setOption: function( key, value )
			{
				this._super( key, value );
			},

			_addShowButtonToDOM: function()
			{
				// Add to DOM
				this.show_button_elem.appendTo( this.element.find('.field_data') );
				// Add tooltip
				this.show_button_elem.qtip({
					show: { delay: 100 },
					position: { corner: { target: 'topMiddle', tooltip: 'bottomMiddle'}},
					style: {
						name: 'dark',
						tip: {
							corner: 'bottomMiddle',
							size: { x: 15, y: 10 }
						}
					}
				});
			},
			_prepareModal: function()
			{
				this.modal_elem.dialog({
					height: 'auto',
					maxHeight: $(window).height() - 40,
					width: $(window).width() * 0.90,
					maxWidth: $(window).width() - 40,
					modal: true,
					autoOpen: false,
					title: this.options.dict.modal_title,
					buttons: [
						{ text: this.options.dict.modal_close_button_label, click: function() { $(this).dialog( "close" ); } },
					]
				});
				this.modal_elem.html(this.options.content);
			},
			_openModal: function()
			{
				this.modal_elem.append('<div id="lifecycle-sneak-peak-cts" style="width:100%; height:400px;"></div>');
				this.modal_elem.dialog('open');
				this.modal_elem.dialog({
					position: { 'my': 'center', 'at': 'center' }
				});
			    var cy = cytoscape({
			    	container: document.getElementById('lifecycle-sneak-peak-cts'), // container to render in
			    	elements: this.options.graph,
			    	style: [ // the stylesheet for the graph
			    	    {
			    	        selector: 'node',
			    	        style: {
			    	          'background-color': '#666',
			    	          'label': 'data(label)'
			    	        }
			    	      },
			    	      {
			    	        selector: 'edge',
			                style: {
			                    'width': 2,
			                    'target-arrow-shape': 'triangle',
			                    'line-color': '#9dbaea',
			                    'target-arrow-color': '#9dbaea',
			                    'curve-style': 'bezier'
			                  }
			    	      }			    	      
			    	    ],
			    	layout: {
			            name: 'dagre',
			            rankDir: 'LR',
			            nodeDimensionsIncludeLabels: true
			        },
			    });
			    this.modal_elem.find('canvas').css('left', 0);
			}
		}
	);
});

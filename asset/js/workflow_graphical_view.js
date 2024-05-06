/*
 * Copyright (C) 2013-2024 Combodo SAS
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
	// 'workflow_graphical_view' the widget name
	$.widget( 'itop.workflow_graphical_view',
		{
			// default options
			options:
			{
				loaded: false,      // True when the lifecycle has been loaded in the DOM
				object_class: null,
				object_id: null,
				object_state: null,
				show_button_css_classes: [],
				content: null,      // If no content on initialization, will be fetched from endpoint
				legend: null,
				endpoint: null,
				dict: {
					show_button_tooltip: 'Show lifecycle',
					modal_title: 'Lifecycle',
					modal_close_label: 'Close'
				}
			},

			show_button_elem: null,
			modal_elem: null,

			// the constructor
			_create: function()
			{
				// Checking mandatory options
				var aMandatoryOptionsMissing = [];
				var aMandatoryOptions = ['object_class', 'object_id', 'object_state', 'endpoint'];
				for(var iIdx in aMandatoryOptions)
				{
					var sOption = aMandatoryOptions[iIdx];
					if(this.options[sOption] === null)
					{
						aMandatoryOptionsMissing.push(sOption);
					}
				}
				if(aMandatoryOptionsMissing.length > 0)
				{
					this._trace('Aborting initialization, widget must be instantiate with the following missing options: ' + aMandatoryOptionsMissing.join(', ') + '.');
					return;
				}

				this.element
					.addClass('workflow_graphical_view');

				this._initialize();
				this._bindEvents();
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
					.removeClass('workflow_graphical_view');
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

			// Initialization methods
			_initialize: function()
			{
				this._initializeMarkup();
			},
			_initializeMarkup: function()
			{
				this._makeShowButtonElem();
				this._addShowButtonToDOM();
				this._makeModalElem();
				this._addModalToDOM();
			},
			_bindEvents: function()
			{
				var me = this;

				// Show graph
				this.show_button_elem.on('click', function(oEvent){
					// Prevent scroll to top due to anchor
					oEvent.preventDefault();

					me._onShowGraph();
				});
			},
			// Make the jQuery object of the "show graph" element
			_makeShowButtonElem: function()
			{
				this.show_button_elem = $('<a></a>')
					.addClass('wgv-show-graph')
					.attr('href', '#');

				if (typeof(CombodoTooltip)!== 'undefined') { // iTop 3.0+
					this.show_button_elem
						.attr('aria-label', this.options.dict.show_button_tooltip)
						.attr('data-tooltip-content', this.options.dict.show_button_tooltip);
				} else { // iTop 2.7 and older
					this.show_button_elem
						.attr('title', this.options.dict.show_button_tooltip)
				}

				for(var iIdx in this.options.show_button_css_classes)
				{
					this.show_button_elem.addClass(this.options.show_button_css_classes[iIdx]);
				}
			},
			// Make the jQuery object of the "modal" element
			_makeModalElem: function()
			{
				this.modal_elem = $('<div></div>')
					.addClass('lcsn-graph-container')
					.attr('data-object-class', this.options.object_class)
					.attr('data-object-id', this.options.object_id);
			},
			// Insert the "show graph" element in the DOM
			_addShowButtonToDOM: function()
			{
				// Meant for overloading
			},
			// Insert the graph modal element in the DOM
			_addModalToDOM: function()
			{
				this.modal_elem.appendTo( $('body') );
			},

			// Events callbacks
			_onShowGraph: function()
			{
				if(this.options.content !== null)
				{
					this._showGraph();
				}
				else
				{
					this._loadContentAndShowGraph();
				}
			},
			_onLoadContentDone: function(oData)
			{
				this.options.content = '<img role="image" src="data:image/png;base64,'+oData+'" alt="Lifecycle" style="max-width: 96%;" />';
			},
			_onLoadContentFail: function(sErrorAsHTML)
			{
				this.options.content = sErrorAsHTML;
			},
			_onLoadContentAlways: function()
			{
				this._showGraph();
				this._hideLoader();
			},

			// Workflow methods
			_loadContentAndShowGraph: function()
			{
				var me = this;

				this._showLoader();
				$.get(
					this.options.endpoint,
					{
						object_class: this.options.object_class,
						object_id: this.options.object_id,
						object_state: this.options.object_state,     // Note: State could be retrieve by the backend, but we use it in the URL to cache 1 image per object/state in the browser's cache
						output_format: 'base64'
					}
					)
					.done(function(oData){ me._onLoadContentDone(oData); })
					.fail(function(oXHR){ me._onLoadContentFail(oXHR.responseText); })
					.always(function(){ me._onLoadContentAlways(); });
			},
			_showGraph: function()
			{
				// Prepare modal if not ready
				if(this.modal_elem.html().length === 0)
				{
					this._prepareModal()
				}

				this._openModal();
			},
			// Instantiate modal widget and prepare its content
			_prepareModal: function()
			{
				// Meant for overloading.
			},
			// Open the modal
			_openModal: function()
			{
				// Meant for overloading.
			},
			_showLoader: function()
			{
				for(var iIdx in this.options.show_button_css_classes)
				{
					this.show_button_elem.removeClass(this.options.show_button_css_classes[iIdx]);
				}

				this.show_button_elem
					.addClass('fas')
					.addClass('fa-sync-alt')
					.addClass('fa-spin');
			},
			_hideLoader: function()
			{
				this.show_button_elem
					.removeClass('fas')
					.removeClass('fa-sync-alt')
					.removeClass('fa-spin');

				for(var iIdx in this.options.show_button_css_classes)
				{
					this.show_button_elem.addClass(this.options.show_button_css_classes[iIdx]);
				}
			},

			// Helpers
			// Show a message in the JS console
			_trace: function(sMessage)
			{
				if(window.console && window.console.log)
				{
					console.log('Workflow graphical view: ' + sMessage);
				}
			}
		}
	);
});

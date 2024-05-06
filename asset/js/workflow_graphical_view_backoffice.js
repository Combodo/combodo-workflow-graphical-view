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

$(function()
{
	// the widget definition, where 'itop' is the namespace,
	// 'workflow_graphical_view_backoffice' the widget name
	$.widget( 'itop.workflow_graphical_view_backoffice', $.itop.workflow_graphical_view,
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
					.addClass('workflow_graphical_view_backoffice');

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
					.removeClass('workflow_graphical_view_backoffice');

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
				if (typeof(CombodoTooltip) !== 'undefined') { // iTop 3.0+
					// Add to DOM
					this.show_button_elem.appendTo( this.element.find('.ibo-field--value') );
					// Add tooltip
					CombodoTooltip.InitTooltipFromMarkup(this.show_button_elem, true);
				} else { // iTop 2.7 and older
					// Add to DOM
					this.show_button_elem.appendTo(this.element.find('.field_data'));
					// Add tooltip
					this.show_button_elem.qtip({
						show: {delay: 100},
						position: {corner: {target: 'topMiddle', tooltip: 'bottomMiddle'}},
						style: {
							name: 'dark',
							tip: {
								corner: 'bottomMiddle',
								size: {x: 15, y: 10}
							}
						}
					});
				}
			},
			_prepareModal: function()
			{
				this.modal_elem.dialog({
					height: 'auto',
					maxHeight: $(window).height() * 0.90,
					width: $(window).width() * 0.90,
					maxWidth: $(window).width() - 40,
					modal: true,
					autoOpen: false,
					title: this.options.dict.modal_title,
					buttons: [
						{ text: this.options.dict.modal_close_label, click: function() { $(this).dialog( "close" ); } },
					],
					position: { 'my': 'center top', 'at': 'center top+10%' }
				});
				this.modal_elem.html(this.options.content + this.options.legend);
			},
			_openModal: function()
			{
				var me = this;
				this.modal_elem.dialog('open');

				// Updating position / size because image is loaded after the modal is created...
				setTimeout(function(){
					var oRealModalElem = me.modal_elem.closest('.ui-dialog');
					// - Modal
					var iWindowHeight = $(window).innerHeight();
					var iModalHeight = oRealModalElem.outerHeight();
					var iNewModalTopOffset = Math.floor((iWindowHeight - iModalHeight) / 2);
					oRealModalElem.css('top', iNewModalTopOffset + 'px');
					// - Image
					var iGraphContainerHeight = oRealModalElem.find('.lcsn-graph-container').innerHeight();
					var iGraphLegendHeight = oRealModalElem.find('.lcsn-graph-container [role="legend"]').outerHeight();
					var iGraphImageMaxHeight = iGraphContainerHeight - iGraphLegendHeight - 80;
					oRealModalElem.find('.lcsn-graph-container [role="image"]').css('max-height', iGraphImageMaxHeight + "px");
				}, 500);
			}
		}
	);
});

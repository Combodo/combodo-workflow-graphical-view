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
	// 'workflow_graphical_view_portal' the widget name
	$.widget( 'itop.workflow_graphical_view_portal', $.itop.workflow_graphical_view,
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
					.addClass('workflow_graphical_view_portal');

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
					.removeClass('workflow_graphical_view_portal');

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
				this.show_button_elem.appendTo( this.element.find('.form_field_control') );
				if (typeof(CombodoTooltip) == 'undefined') {  // iTop 2.7 and older
					// Add tooltip
					this.show_button_elem.tooltip();
				}
			},
			_prepareModal: function()
			{
				// Note: Unfortunately we have to manually rebuild the modal markup as for now the CombodoPortalToolbox does not allow to specify all parts of the modal.
				// This has been tracked under N°2906.
				var sModalContent = '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><h4 class="modal-title">'+this.options.dict.modal_title+'</h4></div>'+
				'<div class="modal-body lcsn-graph-container">' + this.options.content + this.options.legend + '</div>' +
				'<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">'+this.options.dict.modal_close_label+'</button></div>';

				CombodoPortalToolbox.OpenModal({
					content: sModalContent
				});
			},
			_openModal: function()
			{

			}
		}
	);
});

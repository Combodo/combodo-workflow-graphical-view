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
	// 'lifecycle_sneakpeek' the widget name
	$.widget( 'itop.lifecycle_sneakpeek',
		{
			// default options
			options:
			{
				ui: 'backoffice',   // Either 'backoffice' or ContextTag::TAG_PORTAL, used to instantiate the correct tooltip library
				loaded: false,      // True when the lifecycle has been loaded in the DOM
				object_class: null,
				object_id: null,
				content: null,      // If no content on initialization, will be fetched from endpoint
				endpoint: null
			},

			// the constructor
			_create: function()
			{
				var me = this;

				// Checking mandatory options
				var aMandatoryOptionsMissing = [];
				var aMandatoryOptions = ['object_class', 'object_id', 'endpoint'];
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
					.addClass('lifecycle_sneakpeek');

				this._initializeTooltip();
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
					.removeClass('lifecycle_sneakpeek');
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

			//
			_initializeTooltip: function()
			{
				if(this.options.ui === 'backoffice')
				{
					var oContentParam = {};
					if(this.options.content !== null)
					{
						oContentParam['text'] = this.options.content;
					}
					else
					{
						oContentParam['url'] = this.options.endpoint;
						oContentParam['data'] = {
							object_class: this.options.object_class,
							object_id: this.options.object_id
						};
					}

					// TODO: Extract async load to handle errors
					this.element.qtip({
						content: oContentParam,
						position: {
							corner: {
								target: 'topMiddle',
								tooltip: 'bottomMiddle'
							},
						},
						style: {
							name: 'dark',
							tip: 'bottomMiddle'
						}
					});
				}
				else
				{

				}
			},

			// Helpers
			// Show a message in the JS console
			_trace: function(sMessage)
			{
				if(window.console && window.console.log)
				{
					console.log('Lifecycle sneak peek: ' + sMessage);
				}
			}
		}
	);
});

<?php
/**
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

namespace Combodo\iTop\Extension\LifecycleSneakPeek\Extension;

use AbstractPortalUIExtension;
use Combodo\iTop\Extension\LifecycleSneakPeek\Helper\ConfigHelper;
use Combodo\iTop\Extension\LifecycleSneakPeek\Service\LifecycleManager;
use Dict;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class PortalUIExtension
 *
 * @package Combodo\iTop\Extension\LifecycleSneakPeek\Extension
 * @author  Guillaume Lajarige <guillaume.lajarige@combodo.com>
 */
class PortalUIExtension extends AbstractPortalUIExtension
{
	/**
	 * @inheritDoc
	 * @throws \Exception
	 */
	public function GetCSSFiles(Container $oContainer)
	{
		// Check if extension allowed
		if (!ConfigHelper::IsAllowed($_ENV['PORTAL_ID']))
		{
			return array();
		}

		return LifecycleManager::GetCSSFilesUrls();
	}

	/**
	 * @inheritDoc
	 * @throws \Exception
	 */
	public function GetJSFiles(Container $oContainer)
	{
		// Check if extension allowed
		if (!ConfigHelper::IsAllowed($_ENV['PORTAL_ID']))
		{
			return array();
		}

		return LifecycleManager::GetJSFilesUrls();
	}

	/**
	 * @inheritDoc
	 * @throws \CoreException
	 * @throws \Exception
	 */
	public function GetJSInline(Container $oContainer)
	{
		$sJS = '';

		// Check if extension allowed
		if (!ConfigHelper::IsAllowed($_ENV['PORTAL_ID']))
		{
			return $sJS;
		}

		// Prepare JS variables
		// Note: Unlike in the backoffice, in the portal we don't have access to the current object, we need to list all eligible classes to then filter page objects.
		// This has been tracked under N°2905
		$aEligibleClasses = LifecycleManager::EnumEligibleClasses();
		$sEligibleClassesAsJSON = json_encode($aEligibleClasses);

		$sWidgetName = LifecycleManager::GetJSWidgetNameForUI();
		$sEndpoint = LifecycleManager::GetEndpoint();

		$sDictEntryShowButtonTooltip = Dict::S('lifecycle-sneakpeek:UI:Button:ShowLifecycle');
		$sDictEntryModalTitle = Dict::S('lifecycle-sneakpeek:UI:Modal:Title');
		$sDictEntryModalCloseButtonLabel = Dict::S('UI:Button:Close');
		
		$sJSGraph = 

		$sJS .= <<<JS
// Lifecycle sneakpeek
function InstantiateLifecycleSneakpeekOnObject(oFormElem)
{
    var oEligibleClasses = {$sEligibleClassesAsJSON};
    
    // Check if object is eligible
    var sObjClass = oFormElem.attr('data-object-class');
    if(oEligibleClasses[sObjClass] === 'undefined')
    {
        return false;
    }
    
    var sObjID = oFormElem.attr('data-object-id');
    var sObjStateAttCode = oEligibleClasses[sObjClass].state_att_code;
    var sObjState = oFormElem.attr('data-object-state');
    
    oFormElem.find('*[data-attribute-code="'+sObjStateAttCode+'"][data-attribute-flag-read-only="true"]').{$sWidgetName}({
		object_class: sObjClass,
		object_id: sObjID,
		object_state: sObjState,
		endpoint: '{$sEndpoint}',
		dict: {
			show_button_tooltip: '{$sDictEntryShowButtonTooltip}',
			modal_title: '{$sDictEntryModalTitle}',
			modal_close_button_label: '{$sDictEntryModalCloseButtonLabel}'
		},
        graph: null
	});
    
    return true;
}

// Instantiate on objects in modals
$('body').on('loaded.bs.modal', function (oEvent) {
    setTimeout(function(){
        var oForm = $(oEvent.target).find('.modal-content .object-details');
        if(oForm.length > 0)
        {
            InstantiateLifecycleSneakpeekOnObject(oForm);
        }
    }, 400);
});

// Instantiate on objects already in the page
$(document).ready(function(){
    $('.object-details').each(function(){
        InstantiateLifecycleSneakpeekOnObject($(this));
    });
});
JS;

		return $sJS;
	}
}
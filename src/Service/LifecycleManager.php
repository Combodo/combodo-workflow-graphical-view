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

namespace Combodo\iTop\Extension\LifecycleSneakPeek\Service;

use ContextTag;
use DBObject;
use MetaModel;
use Combodo\iTop\Extension\LifecycleSneakPeek\Helper\ConfigHelper;
use utils;

class LifecycleManager
{
	/**
	 * Return if $oObject is eligible to the service
	 *
	 * @param \DBObject $oObject
	 *
	 * @return bool
	 * @throws \CoreException
	 */
	public static function IsEligibleObject(DBObject $oObject)
	{
		$sClass = get_class($oObject);

		// Check if among disabled classes
		$aDisabledClasses = ConfigHelper::GetModuleSetting('disabled_classes');
		if(is_array($aDisabledClasses) && in_array($sClass, $aDisabledClasses))
		{
			return false;
		}

		// Check if has state attribute
		$sStateAttCode = MetaModel::GetStateAttributeCode($sClass);
		if(empty($sStateAttCode))
		{
			return false;
		}

		return true;
	}

	/**
	 * LifecycleManager constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Return an array of the required JS files
	 * @return array
	 * @throws \Exception
	 */
	public function GetJSFilesUrls()
	{
		$sBaseUrl = utils::GetAbsoluteUrlModulesRoot().ConfigHelper::GetModuleCode().'/asset/js/';

		return array(
			$sBaseUrl.'lifecycle_sneakpeek.js',
		);
	}

	/**
	 * Return the JS snippet to instantiate the lifecycle widget for $oObject
	 *
	 * @param \DBObject $oObject
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function GetJSWidgetSnippetForObjectDetails(DBObject $oObject)
	{
		$sUI = ContextTag::Check(ContextTag::TAG_PORTAL) ? 'portal' : 'backoffice';
		$sEndpoint = utils::GetAbsoluteUrlModulePage(ConfigHelper::GetModuleCode(), 'ajax-operations.php');
		$sObjClass = get_class($oObject);
		$sObjID = $oObject->GetKey();
		$sObjStateAttCode = MetaModel::GetStateAttributeCode($sObjClass);

		return <<<JS
\$('.object-details[data-object-class="{$sObjClass}"][data-object-id="{$sObjID}"] *[data-attribute-code="{$sObjStateAttCode}"]').lifecycle_sneakpeek({
	ui: '{$sUI}',
	object_class: '{$sObjClass}',
	object_id: '{$sObjID}',
	endpoint: '{$sEndpoint}'
});
JS;
	}
}
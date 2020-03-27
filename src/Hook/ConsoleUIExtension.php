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

use AbstractApplicationUIExtension;
use Combodo\iTop\Extension\LifecycleSneakPeek\Helper\ConfigHelper;
use Combodo\iTop\Extension\LifecycleSneakPeek\Service\LifecycleManager;
use WebPage;

/**
 * Class ConsoleUIExtension
 *
 * @package Combodo\iTop\Extension\LifecycleSneakPeek\Extension
 * @author Guillaume Lajarige <guillaume.lajarige@combodo.com>
 */
class ConsoleUIExtension extends AbstractApplicationUIExtension
{
	/**
	 * @inheritDoc
	 * @throws \CoreException
	 * @throws \Exception
	 */
	public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
	{
		// Check if extension allowed
		if(!ConfigHelper::IsAllowed('backoffice'))
		{
			return;
		}

		if(!LifecycleManager::IsEligibleObject($oObject))
		{
			return;
		}

		$oLM = new LifecycleManager();
		$aJSFiles = $oLM->GetJSFilesUrls();
		$sJSWidgetSnippet = $oLM->GetJSWidgetSnippetForObjectDetails($oObject);

		// Add resources
		foreach($aJSFiles as $sJSFile)
		{
			$oPage->add_linked_script($sJSFile);
		}

		// Add script
		$oPage->add_ready_script($sJSWidgetSnippet);
	}
}

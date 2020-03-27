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

namespace Combodo\iTop\Extension\LifecycleSneakPeek\Helper;

use MetaModel;
use UserRights;

/**
 * Class ConfigHelper
 *
 * @package Combodo\iTop\Extension\LifecycleSneakPeek\Helper
 * @author Guillaume Lajarige <guillaume.lajarige@combodo.com>
 */
class ConfigHelper
{
	const MODULE_CODE = 'combodo-lifecycle-sneakpeek';

	/**
	 * Return the module code so it can be used widely (module setting, URLs, ...)
	 *
	 * @return string
	 */
	public static function GetModuleCode()
	{
		return static::MODULE_CODE;
	}

	/**
	 * @param string $sProperty
	 *
	 * @return mixed
	 */
	public static function GetModuleSetting($sProperty)
	{
		return MetaModel::GetModuleSetting(static::GetModuleCode(), $sProperty);
	}

	/**
	 * Return if the module should be allowed based on:
	 * - The defined GUI
	 * - The current user profiles
	 *
	 * @param string $sGUI
	 *
	 * @return bool
	 */
	public static function IsAllowed($sGUI)
	{
		// Check if enabled in $sGUI
		$aEnabledGUIs = MetaModel::GetModuleSetting(static::GetModuleCode(), 'enabled_portals');
		if (is_array($aEnabledGUIs) && !in_array($sGUI, $aEnabledGUIs))
		{
			return false;
		}

		// Check if user has profile to access lifecycle
		$aUserProfiles = UserRights::ListProfiles();
		$aAllowedProfiles = MetaModel::GetModuleSetting(static::GetModuleCode(), 'allowed_profiles');
		// No allowed profile defined = Allowed for everyone
		if (!empty($aAllowedProfiles))
		{
			$bAllowed = false;
			foreach ($aAllowedProfiles as $sAllowedProfile)
			{
				if (in_array($sAllowedProfile, $aUserProfiles))
				{
					$bAllowed = true;
					break;
				}
			}

			if (!$bAllowed)
			{
				return false;
			}
		}

		return true;
	}
}
<?php

/**
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

namespace Combodo\iTop\Extension\WorkflowGraphicalView\Helper;

use MetaModel;
use UserRights;

/**
 * Class ConfigHelper
 *
 * @package Combodo\iTop\Extension\WorkflowGraphicalView\Helper
 * @author Guillaume Lajarige <guillaume.lajarige@combodo.com>
 */
class ConfigHelper
{
	const MODULE_CODE = 'combodo-workflow-graphical-view';

	/** @var array */
	const DEFAULT_SETTING_STIMULI_TO_HIDE = array();
	/** @var bool */
	const DEFAULT_SETTING_HIDE_INTERNAL_STIMULI = false;
	/** @var array */
	const DEFAULT_SETTING_DISABLED_CLASSES = array();
	/** @var array */
	const DEFAULT_SETTING_DISABLED_PORTALS = array();
	/** @var string */
	const DEFAULT_SETTING_SHOW_BUTTON_CSS_CLASSES = 'fas fa-map-marked-alt';

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
		$sDefaultValueConstName = static::class . '::DEFAULT_SETTING_' . strtoupper($sProperty);
		$defaultValue = (defined($sDefaultValueConstName)) ? constant($sDefaultValueConstName) : null;

		return MetaModel::GetModuleSetting(static::GetModuleCode(), $sProperty, $defaultValue);
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
		// Check if disabled in $sGUI
		$aDisabledGUIs = static::GetModuleSetting('disabled_portals');
		if (is_array($aDisabledGUIs) && in_array($sGUI, $aDisabledGUIs))
		{
			return false;
		}

		// Check if user has profile to access lifecycle
		$aUserProfiles = UserRights::ListProfiles();
		$aAllowedProfiles = static::GetModuleSetting('allowed_profiles');
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
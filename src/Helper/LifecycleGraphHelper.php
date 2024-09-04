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

namespace Combodo\iTop\Extension\WorkflowGraphicalView\Helper;

use Combodo\iTop\Extension\WorkflowGraphicalView\Service\GraphvizGenerator;
use Combodo\iTop\Extension\WorkflowGraphicalView\Service\LifecycleManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LifecycleGraphHelper
 *
 * @package Combodo\iTop\Extension\WorkflowGraphicalView\Helper
 */
class LifecycleGraphHelper
{
	public static function GetLifecycleGraph($oObject, $sOutputFormat)
	{
		$sObjClass = get_class($oObject);
		try {
			if (!LifecycleManager::IsEligibleObject($oObject)) {
				throw new Exception(Dict::Format('workflow-graphical-view:Error:ObjectNotEligible', $sObjClass, $oObject->GetKey()));
			}

			// Get module parameters
			// - stimuli to hide
			$aStimuliToHide = array();
			$aModuleParameter = ConfigHelper::GetModuleSetting('stimuli_to_hide');
			if (is_array($aModuleParameter) && isset($aModuleParameter[$sObjClass])) {
				foreach (explode(',', $aModuleParameter[$sObjClass]) as $sStimulusCode) {
					$aStimuliToHide[] = trim($sStimulusCode);
				}
			}
			// - internal stimuli to hide
			$bHideInternalStimuli = ConfigHelper::GetModuleSetting('hide_internal_stimuli');

			$oLM = new LifecycleManager($oObject);
			$sImageFilePath = $oLM->GetLifecycleImage($aStimuliToHide, $bHideInternalStimuli);

			$aHeaders = [];
			$sFileContent = null;
			// Send content
			switch ($sOutputFormat) {
				case 'base64':
					$aHeaders['Content-type'] = 'text/plain';
					$sFileContent = base64_encode(file_get_contents($sImageFilePath));
					break;

				case 'binary':
				default:
					$aHeaders['Content-type'] = 'image/png';
					$sFileContent = file_get_contents($sImageFilePath);
					break;
			}


			// If image in temp. dir., we delete it (means that it's not the default image)
			if (stripos($sImageFilePath, GraphvizGenerator::$sTmpFolderPath) !== false) {
				@unlink($sImageFilePath);
			}

			return [$sFileContent, Response::HTTP_OK, $aHeaders];
		}
		catch (Exception $oException) {
			$aHeaders['Content-type'] = 'text/html';

			return ["<h3>{$oException->getMessage()}</h3>", Response::HTTP_INTERNAL_SERVER_ERROR, $aHeaders];
		}
	}
}
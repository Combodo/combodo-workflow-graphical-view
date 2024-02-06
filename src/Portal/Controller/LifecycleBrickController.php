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
 *
 *
 */

namespace Combodo\iTop\Portal\Controller;

use ApprovalScheme;
use AttributeDate;
use AttributeDateTime;
use Combodo\iTop\Extension\WorkflowGraphicalView\Helper\ConfigHelper;
use Combodo\iTop\Extension\WorkflowGraphicalView\Service\GraphvizGenerator;
use Combodo\iTop\Extension\WorkflowGraphicalView\Service\LifecycleManager;
use Combodo\iTop\Portal\Brick\BrickCollection;
use Combodo\iTop\Portal\Helper\ObjectFormHandlerHelper;
use Combodo\iTop\Portal\Routing\UrlGenerator;
use Dict;
use IssueLog;
use MetaModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Service\Attribute\Required;
use UserRights;
use utils;

/**
 * Class ApprovalBrickController
 *
 * @package Combodo\iTop\Portal\Controller
 */
class LifecycleBrickController extends ObjectController
{
	public function GetObject($sObjectClass, $sObjectId){
        		$oObject = MetaModel::GetObject($sObjectClass, $sObjectId, false /* MustBeFound */,
			$this->oScopeValidatorHelper->IsAllDataAllowedForScope(UserRights::ListProfiles(), $sObjectClass));
                return $oObject;
}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $oRequest
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \ArchivedObjectException
	 * @throws \Combodo\iTop\Portal\Brick\BrickNotFoundException
	 * @throws \CoreException
	 * @throws \CoreUnexpectedValue
	 * @throws \DictExceptionMissingString
	 * @throws \MySQLException
	 * @throws \OQLException
	 */
	public function ViewObjectLifecycleAction(Request $oRequest)
	{

		$sObjClass = utils::ReadParam('object_class', '', false, 'class');
		$iObjID = (int) utils::ReadParam('object_id', 0, false, 'integer');
		$sOutputFormat = utils::ReadParam('output_format', 'image');
		try
		{
		$oObject = MetaModel::GetObject($sObjClass, $iObjID, true, 	$this->oScopeValidatorHelper->IsAllDataAllowedForScope(UserRights::ListProfiles(), $sObjClass));


		if(!LifecycleManager::IsEligibleObject($oObject))
		{
			throw new Exception('TOTR: Cannot show lifecycle for '.$sObjClass.'#'.$iObjID.', object is not eligible.');
		}

		// Get module parameters
		// - stimuli to hide
		$aStimuliToHide = array();
		$aModuleParameter = ConfigHelper::GetModuleSetting('stimuli_to_hide');
		if(is_array($aModuleParameter) && isset($aModuleParameter[$sObjClass]))
		{
			foreach(explode(',', $aModuleParameter[$sObjClass]) as $sStimulusCode)
			{
				$aStimuliToHide[] = trim($sStimulusCode);
			}
		}
		// - internal stimuli to hide
		$bHideInternalStimuli = ConfigHelper::GetModuleSetting('hide_internal_stimuli');

		$oLM = new LifecycleManager($oObject);
		$sImageFilePath = $oLM->GetLifecycleImage($aStimuliToHide, $bHideInternalStimuli);

		$aHeaders=[];
		$sFileContent = null;
		// Send content
		switch($sOutputFormat)
		{
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
		if(stripos($sImageFilePath, GraphvizGenerator::$sTmpFolderPath) !== false)
		{
			@unlink($sImageFilePath);
		}
		return  new Response( $sFileContent, Response::HTTP_OK, $aHeaders);
	}
	catch(Exception $oException)
	{
		$aHeaders['Content-type'] = 'text/html';
		return new Response( "<h3>{$oException->getMessage()}</h3>",Response::HTTP_INTERNAL_SERVER_ERROR, $aHeaders);
	}
	}


}

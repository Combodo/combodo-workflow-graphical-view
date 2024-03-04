<?php

/**
 * @copyright   Copyright (C) 2013-2024 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

namespace Combodo\iTop\Extension\WorkflowGraphicalView\Portal\Controller;

use ApprovalScheme;
use Combodo\iTop\Extension\WorkflowGraphicalView\Helper\LifecycleGraphHelper;
use Combodo\iTop\Portal\Controller\ObjectController;
use MetaModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
			[ $sContent, $sHttpResponseCode,$aHeaders] =  LifecycleGraphHelper::GetLifecycleGraph($sObjClass, $iObjID, $oObject, $sOutputFormat);
			return new Response($sContent, $sHttpResponseCode, $aHeaders);
		}
		catch(Exception $oException)
		{
			$aHeaders['Content-type'] = 'text/html';
			return new Response( "<h3>{$oException->getMessage()}</h3>",Response::HTTP_INTERNAL_SERVER_ERROR, $aHeaders);
		}
	}


}

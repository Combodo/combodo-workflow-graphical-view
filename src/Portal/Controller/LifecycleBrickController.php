<?php

/**
 * @copyright   Copyright (C) 2013-2024 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

namespace Combodo\iTop\Extension\WorkflowGraphicalView\Portal\Controller;

use ApprovalScheme;
use Combodo\iTop\Extension\WorkflowGraphicalView\Helper\LifecycleGraphHelper;
use Combodo\iTop\Portal\Controller\AbstractController;
use Combodo\iTop\Portal\Helper\RequestManipulatorHelper;
use Combodo\iTop\Portal\Helper\SecurityHelper;
use Dict;
use MetaModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ApprovalBrickController
 *
 * @package Combodo\iTop\Portal\Controller
 */
class LifecycleBrickController extends AbstractController
{
	public function __construct(
		protected SecurityHelper $oSecurityHelper,
		protected RequestManipulatorHelper $oRequestManipulatorHelper
	)
	{
	}

	public function ViewObjectLifecycleAction(Request $oRequest): Response
	{
		$sObjClass = $this->oRequestManipulatorHelper->ReadParam('object_class', '',  FILTER_SANITIZE_SPECIAL_CHARS);
		$iObjID = (int)  $this->oRequestManipulatorHelper->ReadParam('object_id', 0, FILTER_SANITIZE_NUMBER_INT);
		$sOutputFormat =  $this->oRequestManipulatorHelper->ReadParam('output_format', 'image', FILTER_SANITIZE_SPECIAL_CHARS);

		if ($this->oSecurityHelper->IsActionAllowed(UR_ACTION_READ, $sObjClass, $iObjID) === false) {
			throw new HttpException(Response::HTTP_NOT_FOUND, Dict::S('UI:ObjectDoesNotExist'));
		}

		$oObject = MetaModel::GetObject($sObjClass, $iObjID, true, true);
		[$sContent, $sHttpResponseCode, $aHeaders] = LifecycleGraphHelper::GetLifecycleGraph($oObject, $sOutputFormat);
		return new Response($sContent, $sHttpResponseCode, $aHeaders);
	}
}

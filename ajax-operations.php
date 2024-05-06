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

namespace Combodo\iTop\Extension\WorkflowGraphicalView;

use Combodo\iTop\Extension\WorkflowGraphicalView\Helper\ConfigHelper;
use Combodo\iTop\Extension\WorkflowGraphicalView\Service\GraphvizGenerator;
use Combodo\iTop\Extension\WorkflowGraphicalView\Service\LifecycleManager;
use Exception;
use LoginWebPage;
use MetaModel;
use utils;

// Note: approot.inc.php is relative to /pages/exc.php, so calls to this page must be done through it!
require_once '../approot.inc.php';
require_once APPROOT.'bootstrap.inc.php';
require_once(APPROOT.'/application/startup.inc.php');
require_once APPROOT.'/application/loginwebpage.class.inc.php';

// Check user is logged in
LoginWebPage::DoLoginEx(null, false);

// Retrieve parameters
$sObjClass = utils::ReadParam('object_class', '', false, 'class');
$iObjID = (int) utils::ReadParam('object_id', 0, false, 'integer');
$sOutputFormat = utils::ReadParam('output_format', 'image');

try
{
	// Retrieve object
	$oObject = MetaModel::GetObject($sObjClass, $iObjID);

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

	// Send content
	switch($sOutputFormat)
	{
		case 'base64':
			header('Content-type: text/plain');
			echo base64_encode(file_get_contents($sImageFilePath));
			break;

		case 'binary':
		default:
			header('Content-type: image/png');
			echo file_get_contents($sImageFilePath);
			break;
	}


	// If image in temp. dir., we delete it (means that it's not the default image)
	if(stripos($sImageFilePath, GraphvizGenerator::$sTmpFolderPath) !== false)
	{
		@unlink($sImageFilePath);
	}
}
catch(Exception $oException)
{
	http_response_code(500);
	header('Content-type: text/html');
	echo "<h3>{$oException->getMessage()}</h3>";
}

//$oController = new AjaxOperationsController(MODULESROOT.ConfigHelper::GetModuleCode().'/view', ConfigHelper::GetModuleCode());
//
//// Allow parallel execution
//session_write_close();
//
//$oController->HandleOperation();

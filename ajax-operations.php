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

use Combodo\iTop\Extension\WorkflowGraphicalView\Helper\LifecycleGraphHelper;
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
LoginWebPage::DoLoginEx('backoffice', false);

// Retrieve parameters
$sObjClass = utils::ReadParam('object_class', '', false, utils::ENUM_SANITIZATION_FILTER_CLASS);
$iObjID = (int) utils::ReadParam('object_id', 0, false, utils::ENUM_SANITIZATION_FILTER_INTEGER);
$sOutputFormat = utils::ReadParam('output_format', 'image', false, utils::ENUM_SANITIZATION_FILTER_PARAMETER);

try
{
	// Retrieve object
	$oObject = MetaModel::GetObject($sObjClass, $iObjID);
	[ $sContent, $sHttpResponseCode,$aHeaders]  = LifecycleGraphHelper::GetLifecycleGraph($oObject, $sOutputFormat);

	header('Content-type: '.$aHeaders['Content-type']);
	echo $sContent;

}
catch(Exception $oException)
{
	http_response_code(500);
	header('Content-type: text/html');
	echo "<h3>{$oException->getMessage()}</h3>";
}
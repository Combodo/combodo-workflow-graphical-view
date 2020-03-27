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

namespace Combodo\iTop\Extension\LifecycleSneakPeek\Controller;

use Combodo\iTop\Application\TwigBase\Controller\Controller;
use utils;

class AjaxOperationsController extends Controller
{
	public function OperationViewLog()
	{
		$sLogFileName = utils::ReadParam('logFileName', '', false, 'transaction_id');

		$sFullBasePath = self::GetLogBasePath();
		$sFileToViewFullPath = $sFullBasePath.$sLogFileName;

		$sFileToViewRealPath = utils::RealPath($sFileToViewFullPath, $sFullBasePath);
		if ($sFileToViewRealPath === false)
		{
			throw new CoreUnexpectedValue('File not allowed');
		}

		$sLogFileBrowserImpl = utils::ReadParam('logFileBrowserImpl', '', false, 'transaction_id');

		$oLogFileBrowser = new $sLogFileBrowserImpl;
		if (!($oLogFileBrowser instanceof AbstractLogFileBrowser))
		{
			throw new CoreUnexpectedValue('Invalid parameter : logFileBrowserImpl');
		}

		$aHeaders = array('Content-Type' => $oLogFileBrowser->GetHtmlContentType());
		$a_getLogFileName = $oLogFileBrowser->GetLogFileName($sLogFileName, $sFileToViewRealPath);

		$this->SendFileContent($a_getLogFileName[0], $a_getLogFileName[1], false, $oLogFileBrowser->RequireDeletion(), $aHeaders);
	}
}
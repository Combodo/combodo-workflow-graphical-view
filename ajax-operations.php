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

namespace Combodo\iTop\Extension\LifecycleSneakPeek;

use Combodo\iTop\Extension\LifecycleSneakPeek\Helper\ConfigHelper;
use Combodo\iTop\Extension\LifecycleSneakPeek\Controller\AjaxOperationsController;

// Note: approot.inc.php is relative to /pages/exc.php, so calls to this page must be done through it!
require_once '../approot.inc.php';
require_once APPROOT.'bootstrap.inc.php';

echo "<h1>TODO: Lifecycle HERE 👇</h1>";
die();

$oController = new AjaxOperationsController(MODULESROOT.ConfigHelper::GetModuleCode().'/view', ConfigHelper::GetModuleCode());

// Allow parallel execution
session_write_close();

$oController->HandleOperation();

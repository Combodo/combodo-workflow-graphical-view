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

use Combodo\iTop\Portal\Routing\ItopExtensionsExtraRoutes;

// protection if portal module is not installed
if (class_exists("Combodo\\iTop\\Portal\\Routing\\ItopExtensionsExtraRoutes")) {
	/** @noinspection PhpUnhandledExceptionInspection */
	ItopExtensionsExtraRoutes::AddControllersClasses(
		[
			'Combodo\\iTop\\Extension\\WorkflowGraphicalView\\Portal\\Controller\\LifecycleBrickController'
		]
	);
	/** @noinspection PhpUnhandledExceptionInspection */
	ItopExtensionsExtraRoutes::AddRoutes(
	    [
		    [
			    'pattern'  => '/lifecycle/view',
			    'callback' => 'Combodo\\iTop\\Extension\\WorkflowGraphicalView\\Portal\\Controller\\LifecycleBrickController::ViewObjectLifecycleAction',
			    'bind'     => 'p_lifecycle_view_object'
		    ],
	    ]
	);
}
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

/** @noinspection PhpUnhandledExceptionInspection */
ItopExtensionsExtraRoutes::AddRoutes(
    array(
        array('pattern' => '/lifecycle/view',
            'callback' => 'Combodo\\iTop\\Extension\\WorkflowGraphicalView\\Portal\\Controller\\LifecycleBrickController::ViewObjectLifecycleAction',
            'bind' => 'p_lifecycle_view_object'
        ),
    )
);

/**
 * @since 3.1.0
 */
//remove require itopdesignformat at the same time as version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') < 0
if (! defined("ITOP_DESIGN_LATEST_VERSION")) {
	require_once APPROOT.'setup/itopdesignformat.class.inc.php';
}
if (version_compare(ITOP_DESIGN_LATEST_VERSION, 3.1, '>=')) {
    /** @noinspection PhpUnhandledExceptionInspection */
    ItopExtensionsExtraRoutes::AddControllersClasses(
        array(
            'Combodo\\iTop\\Extension\\WorkflowGraphicalView\\Portal\\Controller\\LifecycleBrickController'
        )
    );
}
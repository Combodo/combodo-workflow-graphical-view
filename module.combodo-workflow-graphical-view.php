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

/** @noinspection PhpUnhandledExceptionInspection */
SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'combodo-workflow-graphical-view/1.1.3',
	array(
		// Identification
		//
		'label' => 'Workflow graphical view',
		'category' => 'ui',

		// Setup
		//
		'dependencies' => array(),
		'mandatory' => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			// Module's autoloader
			'vendor/autoload.php',
			// Explicitly load APIs classes
			'src/Hook/ConsoleUIExtension.php',
			'src/Hook/PortalUIExtension.php',
		),
		'webservice' => array(),
		'dictionary' => array(
		),
		'data.struct' => array(),
		'data.sample' => array(),

		// Documentation
		//
		'doc.manual_setup' => '',
		'doc.more_information' => '',

		// Default settings
		//
		'settings' => array(),
	)
);

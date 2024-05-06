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

namespace Combodo\iTop\Extension\WorkflowGraphicalView\Service;

use Combodo\iTop\Extension\WorkflowGraphicalView\Helper\ConfigHelper;
use ContextTag;
use DBObject;
use Dict;
use MetaModel;
use utils;

class LifecycleManager
{
	/** @var \DBObject */
	private $oObject;

	/**
	 * Return if $oObject is eligible to the service
	 *
	 * @param \DBObject $oObject
	 *
	 * @return bool
	 * @throws \CoreException
	 */
	public static function IsEligibleObject(DBObject $oObject)
	{
		$sClass = get_class($oObject);

		// Check if among disabled classes
		$aDisabledClasses = ConfigHelper::GetModuleSetting('disabled_classes');
		if (is_array($aDisabledClasses) && in_array($sClass, $aDisabledClasses))
		{
			return false;
		}

		// Check if has state attribute
		if (version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') < 0) {
			$sStateAttCode = MetaModel::GetStateAttributeCode($sClass);
			if (empty($sStateAttCode))
			{
				return false;
			}
		} else {
			return MetaModel::HasLifecycle($sClass);
		}

		return true;
	}

	/**
	 * Return an array of eligible classes and their state attribute code
	 *
	 * @return array
	 * @throws \CoreException
	 */
	public static function EnumEligibleClasses()
	{
		// Check if has state attribute
		$aEligibleClasses = array();
		if (version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') < 0) {
			foreach (MetaModel::EnumRootClasses() as $sRootClass) {
				$sStateAttCode = MetaModel::GetStateAttributeCode($sRootClass);
				if (!empty($sStateAttCode)) {
					$aEligibleClasses[$sRootClass] = array('state_att_code' => $sStateAttCode);
				}

				foreach (MetaModel::EnumChildClasses($sRootClass) as $sChildClass) {
					$sStateAttCode = MetaModel::GetStateAttributeCode($sChildClass);
					if (!empty($sStateAttCode)) {
						$aEligibleClasses[$sChildClass] = array('state_att_code' => $sStateAttCode);
					}
				}
			}
		} else {
			foreach (MetaModel::EnumRootClasses() as $sRootClass) {
				$sStateAttCode = MetaModel::GetStateAttributeCode($sRootClass);
				if (MetaModel::HasLifecycle($sRootClass)) {
					$aEligibleClasses[$sRootClass] = array('state_att_code' => $sStateAttCode);
				}

				foreach (MetaModel::EnumChildClasses($sRootClass) as $sChildClass) {
					$sStateAttCode = MetaModel::GetStateAttributeCode($sChildClass);
					if (MetaModel::HasLifecycle($sChildClass)) {
						$aEligibleClasses[$sChildClass] = array('state_att_code' => $sStateAttCode);
					}
				}
			}
		}
		return $aEligibleClasses;
	}

	/**
	 * Return an array of the required CSS files URLs
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function GetCSSFilesUrls()
	{
		$sDefaultCSSRelPath = utils::GetCSSFromSASS('env-'.utils::GetCurrentEnvironment().'/'.ConfigHelper::GetModuleCode().'/asset/css/default.scss');

		return array(
			utils::GetAbsoluteUrlAppRoot().$sDefaultCSSRelPath,
		);
	}

	/**
	 * Return an array of the required JS files URLs
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function GetJSFilesUrls()
	{
		$sBaseUrl = utils::GetAbsoluteUrlModulesRoot().ConfigHelper::GetModuleCode().'/asset/js/';

		return array(
			$sBaseUrl.'workflow_graphical_view.js',
			$sBaseUrl.static::GetJSWidgetNameForUI().'.js',
		);
	}

	/**
	 * Return the name of the JS widget for the UI
	 *
	 * @return string
	 */
	public static function GetJSWidgetNameForUI()
	{
			return ContextTag::Check(ContextTag::TAG_PORTAL) ? 'workflow_graphical_view_portal' : 'workflow_graphical_view_backoffice';
	}

	/**
	 * Return the endpoint absolute URL for AJAX calls
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function GetEndpoint()
	{
		return utils::GetAbsoluteUrlModulePage(ConfigHelper::GetModuleCode(), 'ajax-operations.php');
	}

	/**
	 * Return an array of the CSS classes for the "show button"
	 *
	 * @return array
	 */
	public static function GetShowButtonCSSClasses()
	{
		$aCSSClasses = array();

		$sModuleParameter = ConfigHelper::GetModuleSetting('show_button_css_classes');
		foreach(explode(' ', $sModuleParameter) as $sCSSClass)
		{
			$aCSSClasses[] = trim($sCSSClass);
		}

		return $aCSSClasses;
	}

	/**
	 * Return a string containing the HTML markup of the legend
	 *
	 * @return string
	 */
	public static function GetLegendHTMLMarkup()
	{
		$sDictEntryModalLegendItemCurrentState = utils::HtmlEntities(Dict::S('workflow-graphical-view:UI:Legend:Item:CurrentState'));
		$sDictEntryModalLegendItemUserStimuli = utils::HtmlEntities(Dict::S('workflow-graphical-view:UI:Legend:Item:UserStimuli'));
		$sDictEntryModalLegendItemInternalStimuli = utils::HtmlEntities(Dict::S('workflow-graphical-view:UI:Legend:Item:InternalStimuli'));

		return <<<HTML
<div class="lcsn-graph-legend" role="legend">
	<ul>
		<li role="current-state"><span role="decoration"></span><span role="text">{$sDictEntryModalLegendItemCurrentState}</span></li>
		<li role="user-stimuli"><span role="decoration"></span><span role="text">{$sDictEntryModalLegendItemUserStimuli}</span></li>
		<li role="internal-stimuli"><span role="decoration"></span><span role="text">{$sDictEntryModalLegendItemInternalStimuli}</span></li>
	</ul>
</div>
HTML;
	}

	/**
	 * LifecycleManager constructor.
	 *
	 * @param \DBObject $oObject
	 */
	public function __construct(DBObject $oObject)
	{
		$this->oObject = $oObject;
	}

	/**
	 * Return the JS snippet to instantiate the lifecycle widget for $oObject
	 *
	 * @return string
	 * @throws \CoreException
	 * @throws \Exception
	 */
	public function GetJSWidgetSnippetForObjectDetails()
	{
		$sObjClass = get_class($this->oObject);
		$sObjID = $this->oObject->GetKey();
		$sObjStateAttCode = MetaModel::GetStateAttributeCode($sObjClass);
		$sObjState = $this->oObject->GetState();

		$sWidgetName = $this->GetJSWidgetNameForUI();
		$sShowButtonCSSClassesAsJSON = json_encode(static::GetShowButtonCSSClasses());
		$sLegendHTMLAsJSON = json_encode(static::GetLegendHTMLMarkup());
		$sEndpoint = static::GetEndpoint();

		$sDictEntryShowButtonTooltipAsJSON = json_encode(Dict::S('workflow-graphical-view:UI:Button:ShowLifecycle'));
		$sDictEntryModalTitleAsJSON = json_encode(Dict::S('workflow-graphical-view:UI:Modal:Title'));
		$sDictEntryModalCloseLabelAsJSON = json_encode(Dict::S('UI:Button:Close'));

		$sAttributeSelectorPrefix = (version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0', '>=') && ContextTag::Check(ContextTag::TAG_CONSOLE)) ? '.ibo-object-details' : '.object-details';
		return <<<JS
			\$('{$sAttributeSelectorPrefix}[data-object-class="{$sObjClass}"][data-object-id="{$sObjID}"] *[data-attribute-code="{$sObjStateAttCode}"][data-attribute-flag-read-only="true"]').{$sWidgetName}({
				object_class: '{$sObjClass}',
				object_id: '{$sObjID}',
				object_state: '{$sObjState}',
				show_button_css_classes: {$sShowButtonCSSClassesAsJSON},
				legend: {$sLegendHTMLAsJSON},
				endpoint: '{$sEndpoint}',
				dict: {
					show_button_tooltip: {$sDictEntryShowButtonTooltipAsJSON},
					modal_title: {$sDictEntryModalTitleAsJSON},
					modal_close_label: {$sDictEntryModalCloseLabelAsJSON}
				}
			});
JS;
	}

	/**
	 * Return the path of the lifecycle image
	 *
	 * @param array $aStimuliToHide
	 * @param bool  $bHideInternalStimuli
	 *
	 * @return string
	 * @throws \CoreException
	 * @throws \ReflectionException
	 */
	public function GetLifecycleImage($aStimuliToHide = array(), $bHideInternalStimuli = ConfigHelper::DEFAULT_SETTING_HIDE_INTERNAL_STIMULI)
	{
		return GraphvizGenerator::GenerateObjectLifecycleAsImage($this->oObject, $aStimuliToHide, $bHideInternalStimuli);
	}
}
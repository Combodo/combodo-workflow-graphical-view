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

use DBObject;
use Dict;
use Exception;
use IssueLog;
use MetaModel;
use ReflectionClass;
use SetupUtils;
use StimulusInternal;

/**
 * Class GraphvizGenerator
 *
 * @package Combodo\iTop\Extension\WorkflowGraphicalView\Service
 * @author  Guillaume Lajarige <guillaume.lajarige@combodo.com>
 */
class GraphvizGenerator
{
	/** @var string */
	const ENUM_STIMULUS_TYPE_PRIMARY = 'primary';
	/** @var string */
	const ENUM_STIMULUS_TYPE_SECONDARY = 'secondary';

	/** @var string */
	public static $sTmpFolderPath = APPROOT.'data/lifecycle/';

	/**
	 * Generate an image for the $oObject lifecycle.
	 *
	 * @param \DBObject $oObject
	 * @param array     $aStimuliToHide
	 * @param bool      $bHideInternalStimuli
	 * @param bool      $bHideOrphanStates
	 *
	 * @return string
	 * @throws \CoreException
	 * @throws \ReflectionException
	 * @throws \Exception
	 */
	public static function GenerateObjectLifecycleAsImage(DBObject $oObject, $aStimuliToHide = array(), $bHideInternalStimuli = false, $bHideOrphanStates = true)
	{
		$sObjClass = get_class($oObject);
		$iObjID = $oObject->GetKey();

		$sStateAttCode = MetaModel::GetStateAttributeCode($sObjClass);
		if (empty($sStateAttCode))
		{
			throw new Exception(Dict::Format('workflow-graphical-view:Error:NoStateAttribute', $sObjClass));
		}

		// Prepare graph definition
		$aStatesConnections = static::GetObjectStatesConnections($oObject, $aStimuliToHide, $bHideInternalStimuli, $bHideOrphanStates);
		$sDotDefinition = static::GenerateDotDefinition($aStatesConnections);

		// Generate temp. DOT file
		SetupUtils::builddir(static::$sTmpFolderPath);
		$sDotFilePath = static::$sTmpFolderPath.$sObjClass."-".$iObjID."-".date("YmdHis").".dot";
		static::CreateDotFile($sDotFilePath, $sDotDefinition);

		// Generate temp. image file
		return static::GenerateImageFromDefinition($sObjClass, $iObjID, $sDotFilePath);
	}

	/**
	 * Return an array of states and their number of inbound / outbound connections
	 *
	 * @param \DBObject $oObject
	 * @param array     $aStimuliToIgnore
	 * @param bool      $bHideInternalStimuli
	 * @param bool      $bHideOrphanStates
	 *
	 * @return array
	 * @throws \CoreException
	 */
	protected static function GetObjectStatesConnections(DBObject $oObject, $aStimuliToIgnore = array(), $bHideInternalStimuli = false, $bHideOrphanStates = true)
	{
		$sObjClass = get_class($oObject);

		$sCurrentState = $oObject->GetState();
		$aStates = array_keys(MetaModel::EnumStates($sObjClass));
		$aStimuli = MetaModel::EnumStimuli($sObjClass);

		// Initialize states connections
		$aStatesConnections = array();
		foreach ($aStates as $sStateCode)
		{
			$sStateLabel = MetaModel::GetStateLabel($sObjClass, $sStateCode);
			$aStatesConnections[$sStateCode] = array(
				'label' => $sStateLabel,
				'current' => ($sStateCode === $sCurrentState),
				'in' => array(),
				'out' => array(),
			);
		}

		// Seek connections
		foreach ($aStates as $sStateCode)
		{
			$sStateLabel = MetaModel::GetStateLabel($sObjClass, $sStateCode);
			$aStateTransitions = MetaModel::EnumTransitions($sObjClass, $sStateCode);
			foreach ($aStateTransitions as $sStimulusCode => $aTransitionDef)
			{
				// Skip some stimuli when necessary
				// - Internal stimuli
				if($bHideInternalStimuli && ($aStimuli[$sStimulusCode] instanceof StimulusInternal))
				{
					continue;
				}
				// - Explicitly ask to be ignored
				if (in_array($sStimulusCode, $aStimuliToIgnore))
				{
					continue;
				}

				$sStimulusLabel = $aStimuli[$sStimulusCode]->GetLabel();
				$sStimulusType = ($aStimuli[$sStimulusCode] instanceof StimulusInternal) ? static::ENUM_STIMULUS_TYPE_SECONDARY : static::ENUM_STIMULUS_TYPE_PRIMARY;
				$sTargetStateCode = $aTransitionDef['target_state'];
				$sTargetStateLabel = MetaModel::GetStateLabel($sObjClass, $sTargetStateCode);

				$aStatesConnections[$sStateCode]['out'][] = array(
					'stimulus_code' => $sStimulusCode,
					'stimulus_label' => $sStimulusLabel,
					'stimulus_type' => $sStimulusType,
					'state_code' => $sTargetStateCode,
					'state_label' => $sTargetStateLabel,
				);
				$aStatesConnections[$sTargetStateCode]['in'][] = array(
					'stimulus_code' => $sStimulusCode,
					'stimulus_label' => $sStimulusLabel,
					'stimulus_type' => $sStimulusType,
					'state_code' => $sStateCode,
					'state_label' => $sStateLabel,
				);
			}
		}

		// Remove orphan states if necessary
		foreach ($aStates as $sStateCode)
		{
			if ($bHideOrphanStates && (count($aStatesConnections[$sStateCode]['out']) === 0) && (count($aStatesConnections[$sStateCode]['in']) === 0))
			{
				unset($aStatesConnections[$sStateCode]);
			}
		}

		return $aStatesConnections;
	}

	/**
	 * Return the Dot description to generate a graph using Graphviz based on the states connections in $aStatesConnections.
	 *
	 * @param array $aStatesConnections
	 *
	 * @return string
	 */
	protected static function GenerateDotDefinition($aStatesConnections)
	{
		$aStatesDefinitions = array();
		$aTransitionsDefinitions = array();

		// Prepare definitions
		foreach ($aStatesConnections as $sStateCode => $aStateConnections)
		{
			// State definition
			$sStateLabelVertical = str_replace(' ', '\n', $aStateConnections['label']);
			$sStateLabelEscaped = static::EscapeForDotFile($sStateLabelVertical);
			$sStateShape = static::IsEndNode($aStateConnections) ? 'doublecircle' : 'circle';
			$sStateFillColor = $aStateConnections['current'] ? '#2C5282' : '#FFFFFF';
			$sStateFontColor = $aStateConnections['current'] ? '#FFFFFF' : '#000000';
			$aStatesDefinitions[] = "$sStateCode [ shape={$sStateShape}, label=\"{$sStateLabelEscaped}\", fillcolor=\"{$sStateFillColor}\" fontcolor=\"{$sStateFontColor}\" fontweight=\"bold\"];";

			// Transitions definitions for this state
			foreach ($aStateConnections['out'] as $aStimulus)
			{
				$sStimulusLabelEscaped = static::EscapeForDotFile($aStimulus['stimulus_label']);
				$sStimulusStyle = ($aStimulus['stimulus_type'] == static::ENUM_STIMULUS_TYPE_PRIMARY) ? 'filled' : 'dashed';
				$aTransitionsDefinitions[] = "{$sStateCode} -> {$aStimulus['state_code']} [ label=\"{$sStimulusLabelEscaped}\" style=\"{$sStimulusStyle}\" ];";
			}
		}

		$sStatesDefinition = implode("\n\t", $aStatesDefinitions);
		$sTransitionsDefinition = implode("\n\t", $aTransitionsDefinitions);

		// Build file
		$sGlobalDefinition = <<<EOF
digraph finite_state_machine {
	graph [bgcolor = "#eeeeee00"];
	rankdir=LR;
	node [ fontname=Verdana style=filled fillcolor="#ffffff" ];
	edge [ fontname=Verdana ];
	{$sTransitionsDefinition}
	{$sStatesDefinition}
}\n
EOF;

		return $sGlobalDefinition;
	}

	/**
	 * Generate a PNG image from the DOT definition in $sDotDefinition and return its path.
	 * If DOT is not callable, path of a default image will be returned.
	 *
	 * @param string $sObjClass
	 * @param int    $iObjID
	 * @param string $sDotFilePath
	 * @param bool   $bDeleteDotFileAfterwards Should the definition file be deleted once the image file has been generated.
	 *
	 * @return string
	 * @throws \ReflectionException
	 */
	protected static function GenerateImageFromDefinition($sObjClass, $iObjID, $sDotFilePath, $bDeleteDotFileAfterwards = true)
	{
		$sDotExecutable = MetaModel::GetConfig()->Get('graphviz_path');

		// If the DOT executable does NOT exist, we use a default image
		if (!file_exists($sDotExecutable))
		{
			$oReflection = new ReflectionClass($sObjClass);
			$sDeclarationFile = $oReflection->getFileName();
			$sModuleDir = dirname($sDeclarationFile);

			return $sModuleDir."/lifecycle/".$sObjClass.".png";
		}

		$aOutput = array();
		$sImageFilePath = static::$sTmpFolderPath.$sObjClass."-".$iObjID."-".date("YmdHis").".png";
		$sCommandLine = "\"$sDotExecutable\" -v -Tpng < \"$sDotFilePath\" -o \"$sImageFilePath\" 2>&1";

		exec($sCommandLine, $aOutput, $iRetCode);
		if($iRetCode != 0)
		{
			// Log detailed error for admin.
			$sDotDefinition = @file_get_contents($sDotFilePath);
			$sOutputAsString = implode("\n", $aOutput);

			IssueLog::Error(<<<EOF
Error during lifecycle image generation.
The command {$sCommandLine} returned {$iRetCode}.
The output of the command is: {$sOutputAsString}.

Content of the $sDotFilePath file:
{$sDotDefinition}
EOF
);

			// Throw exception for GUI
			throw new Exception(Dict::S('flow-graphical-view:Error:GraphVizGeneration'));
		}

		// Delete definition file if necessary
		if($bDeleteDotFileAfterwards)
		{
			static::DeleteDotFile($sDotFilePath);
		}

		return $sImageFilePath;
	}

	/**
	 * Create the $sFilePath with $sFileContent.
	 * Potential errors are hidden.
	 *
	 * @param string $sFilePath
	 * @param string $sFileContent
	 */
	protected static function CreateDotFile($sFilePath, $sFileContent)
	{
		$rFile = @fopen($sFilePath, "w");
		@fwrite($rFile, $sFileContent);
		@fclose($rFile);
	}

	/**
	 * Delete the $sFilePath from the file system.
	 * Potential errors are hidden.
	 *
	 * @param string $sFilePath
	 */
	protected static function DeleteDotFile($sFilePath)
	{
		@unlink($sFilePath);
	}

	/**
	 * Escape $sInput to be used in the .dot file
	 *
	 * @param string $sInput
	 *
	 * @return string|string[]
	 */
	protected static function EscapeForDotFile($sInput)
	{
		return str_replace('"', '\\"', $sInput);
	}

	/**
	 * Return true if the given node has no inbound connection.
	 *
	 * @param array $aConnections
	 *
	 * @return bool
	 */
	protected static function IsStartNode($aConnections)
	{
		return (count($aConnections['in']) === 0);
	}

	/**
	 * Return true if the given node has no outbound connection.
	 *
	 * @param array $aConnections
	 *
	 * @return bool
	 */
	protected static function IsEndNode($aConnections)
	{
		return (count($aConnections['out']) === 0);
	}

	/**
	 * Return true if the given node has no inbound or outbound connection.
	 *
	 * @param array $aConnections
	 *
	 * @return bool
	 */
	protected static function IsStartOrEndNode($aConnections)
	{
		return static::IsStartNode($aConnections) || static::IsEndNode($aConnections);
	}
}
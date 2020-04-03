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

namespace Combodo\iTop\Extension\LifecycleSneakPeek\Service;

use DBObject;
use Exception;
use MetaModel;
use StimulusInternal;

/**
 * Class LifeCycleGraphGenerator
 *
 * @package Combodo\iTop\Extension\LifecycleSneakPeek\Service
 * @author  Denis Flaven <denis.flaven@combodo.com>
 */
class LifecycleGraphGenerator
{
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
                $sTargetStateCode = $aTransitionDef['target_state'];
                $sTargetStateLabel = MetaModel::GetStateLabel($sObjClass, $sTargetStateCode);
                
                $aStatesConnections[$sStateCode]['out'][] = array(
                    'stimulus_code' => $sStimulusCode,
                    'stimulus_label' => $sStimulusLabel,
                    'state_code' => $sTargetStateCode,
                    'state_label' => $sTargetStateLabel,
                );
                $aStatesConnections[$sTargetStateCode]['in'][] = array(
                    'stimulus_code' => $sStimulusCode,
                    'stimulus_label' => $sStimulusLabel,
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
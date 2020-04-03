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
use IssueLog;
use MetaModel;
use ReflectionClass;
use StimulusInternal;

/**
 * Class GraphvizGenerator
 *
 * @package Combodo\iTop\Extension\LifecycleSneakPeek\Service
 * @author  Guillaume Lajarige <guillaume.lajarige@combodo.com>
 */
class CytoscapeGenerator extends LifecycleGraphGenerator
{
    public static function GenerateJSGraph(DBObject $oObject, $aStimuliToHide, $bHideInternalStimuli, $bHideOrphanStates = true)
    {
        $sStateAttCode = MetaModel::GetStateAttributeCode(get_class($oObject));
        if (empty($sStateAttCode))
        {
            throw new Exception("TOTR: Cannot generate lifecycle graph for $sObjClass as it has no state attribute.");
        }
        
        // Prepare graph definition
        $aStatesConnections = static::GetObjectStatesConnections($oObject, $aStimuliToHide, $bHideInternalStimuli, $bHideOrphanStates);
        
        $aResult = array();
        
        foreach($aStatesConnections as $sStateCode => $aStateDef)
        {
            $aResult[] = array('data'=> array('id' => $sStateCode, 'label' => $aStateDef['label']));
            foreach($aStateDef['out'] as $aTransitionDef)
            {
                $aResult[] = array('data' => array('id' => $sStateCode.'_'.$aTransitionDef['stimulus_code'], 'label' => $aTransitionDef['stimulus_label'], 'source' => $sStateCode, 'target' => $aTransitionDef['state_code']));
            }
        }
        
        return json_encode($aResult);
    }
}
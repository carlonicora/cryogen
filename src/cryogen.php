<?php
/**
 * Copyright 2015 Carlo Nicora
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @license Apache
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @package CarloNicora\cryogen
 * @author Carlo Nicora
 */
namespace CarloNicora\cryogen;

abstract class cryogen{
    /** @var $connectionController connectionController */
	protected $connectionController;

    /** @var $dataController dataController */
    protected $dataController;

    /** @var  $structureController structureController */
	protected $structureController;

    private $errorStack;
	
	public function createDatabase($databaseName){
        if ($this->connectionController){
            $this->connectionController->createDatabase($databaseName);
        }
    }

    public function isConnected(){
        return($this->connectionController->isConnected());
    }
	
	public function log($message, $sqlStatement, $sqlParameters){
        $this->errorStack[] = $message;

        $backtrace = debug_backtrace();
        $pre = "[" . date("d-M-Y H:i:s") . "] ";
        $post = " in " . $backtrace[0]['file'] . " in line " . $backtrace[0]['line'] . ' WHILE PERFORMING '.$sqlStatement.' WITH PARAMETERS '.json_encode($sqlParameters)."\r\n";
        error_log($pre . $message . $post);
	}

    public function getLastLog(){
        $returnValue = NULL;

        $returnValue = end($this->errorStack);

        return($returnValue);
    }

    public function clearLastLogs(){
        $this->errorStack = [];

        return(true);
    }

    public function read($engine, $level=0, $metaTableCaller=NULL, $metaFieldCaller=NULL){
		$returnValue = $this->dataController->read($engine, $level, $metaTableCaller, $metaFieldCaller);

		if (sizeof($returnValue) > 0){
			$returnValue->meta = $engine->meta;
		} else {
			$returnValue = NULL;
		}
		
		return($returnValue);
	}

    public function readSingle($engine, $level=0, $metaTableCaller=NULL, $metaFieldCaller=NULL){
        $entityList = $this->read($engine, $level, $metaTableCaller, $metaFieldCaller);

        if (isset($entityList) && sizeof($entityList)==1){
            $returnValue = $entityList[0];
        } else {
            $returnValue = null;
        }

        return($returnValue);
    }
	
	public function count($engine){
		return($this->dataController->count($engine));
	}

    public function update($entity, $level = 0, $metaTableCaller=NULL, $metaFieldCaller=NULL){
		return($this->dataController->update($entity, $level, $metaTableCaller, $metaFieldCaller));
	}
	
	public function delete($entity, $engine = NULL){
		return($this->dataController->delete($entity, $engine));
	}

    public function createView($viewSql){
        return($this->structureController->createView($viewSql));
    }

    public function createTable($metaTable, $isFederated = FALSE, $federatedLink = NULL){
        return($this->structureController->createTable($metaTable, $isFederated, $federatedLink));
    }

	public function updateTable($metaTable){
		return($this->structureController->updateTable($metaTable));
	}
	
	public function readStructure(){
		return($this->structureController->readStructure());
	}

	public function readTableStructure($tableName){
		return($this->structureController->readTableStructure($tableName));
	}
	
	public function generatePersistencyFiles($metaTables){
		return($this->structureController->generatePersistencyFiles($metaTables));
	}

    public function setManualSql($sqlStatement, $sqlParameters){
        $this->dataController->setManualSql($sqlStatement, $sqlParameters);
    }
}
?>
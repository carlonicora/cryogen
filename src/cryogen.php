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

/**
 * Cryogen is a database persistency layer for php
 *
 * Cryogen is a generic database persistency layer designed to delegate the management of the database to a series of
 * objects. The layer automatically manages the connection, CRUD functionalities without having the need of writing
 * SQL code.
 * Cryogen is an abstract layer and may work with every type of database. The specific database type layer is
 * implemented on top of Cryogen
 *
 * @package cryogen
 * @author Carlo Nicora
 */
class cryogen{
	/**
	 * Identifies the specific type of database used by cryogen
	 *
	 * The naming convention for the implementation of a database
	 *
	 * @var string
	 */
	protected $plugin;
	protected $logFolder;
	protected $logSql;
	protected $logSqlResult;

    /** @var $connectionController connectionController */
	protected $connectionController;

    /** @var $dataController dataController */
    protected $dataController;

    /** @var  $structureController structureController */
	protected $structureController;

    private $errorStack;
	
	public function __construct($cryogenPlugin, $connectionString, $logFolder = NULL, $logSql = FALSE, $logSqlResult = FALSE){
		$returnValue = FALSE;
		
		$this->plugin = $cryogenPlugin;
		$this->logFolder = $logFolder;
		$this->logSql = $logSql;
		$this->logSqlResult = $logSqlResult;

        $this->errorStack = [];
		
		if (file_exists(substr(__FILE__, 0, strlen(__FILE__) - 11) . $this->plugin . "Cryogen.php")) {
			require_once($this->plugin . "Cryogen.php");
			$controller = '\\cryogen\\' . $this->plugin . 'ConnectionController';
			$this->connectionController = new $controller($this);
			if ($this->connectionController->initialize($connectionString)){
				$dataController = '\\cryogen\\' . $this->plugin . 'DataController';
				$structureController = '\\cryogen\\' . $this->plugin . 'StructureController';
				$this->dataController = new $dataController($this->connectionController, $this);
				$this->structureController = new $structureController($this->connectionController, $this);
				$returnValue = TRUE;
			}
		} else {
			$this->log("the plugin '" . $this->plugin . "' cannot be found", E_USER_ERROR, TRUE);
		}
		
		return($returnValue);
	}
	
	public function __destruct(){
		if (isset($this->connectionController) && $this->connectionController->isConnected){
			$this->connectionController->disconnect();
		}
	}

    public function createDatabase($databaseName){
        if ($this->connectionController){
            $this->connectionController->createDatabase($databaseName);
        }
    }

    public function isConnected(){
        return($this->connectionController->isConnected);
    }
	
	public function logScript($sql, $parameters){
		/*
		if ($this->logFolder && $this->logSql){
			$pre = "[" . date("d-M-Y H:i:s") . "] ";
			
			$textParameters = '';
			if (isset($parameters) && sizeof($parameters) > 1){
				for ($i=0; $i<sizeof($parameters[1]);$i++){
					$textParameters .= $parameters[1][$i] . '|';
				}
			}

			error_log($pre . $sql . $textParameters . "\r\n", 3, $this->logFolder . 'sql.log');
		}
		*/
	}
	
	public function logScriptResult($result){
		/*
		if ($this->logFolder && $this->logSqlResult){
			$pre = "[" . date("d-M-Y H:i:s") . "] ";
			error_log($pre . $result . "\r\n", 3, $this->logFolder . 'sql.log');
		}
		*/
	}
	
	public function log($message, $sqlStatement, $sqlParameters){
        $this->errorStack[] = $message;

		if ($this->logFolder){
			$backtrace = debug_backtrace();
			$pre = "[" . date("d-M-Y H:i:s") . "] ";
			$post = " in " . $backtrace[0]['file'] . " in line " . $backtrace[0]['line'] . ' WHILE PERFORMING '.$sqlStatement.' WITH PARAMETERS '.json_encode($sqlParameters)."\r\n";
			error_log($pre . $message . $post, 3, $this->logFolder . 'sqlErrors.log');
			error_log($pre . $message . $post);
		}
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
	
	public function generateQueryEngine($meta = NULL, $entity = NULL, $valueOfKeyField = NULL){
		$returnValue = NULL;

        $this->clearLastLogs();

		$queryEngine = '\\cryogen\\' . $this->plugin . 'QueryEngine';
		$returnValue = new $queryEngine($meta, $entity, $valueOfKeyField);

		return($returnValue);
	}

    /**
     * @param $engine queryEngine
     * @param int $level
     * @param null $metaTableCaller
     * @param null $metaFieldCaller
     * @return null
     */
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

    /**
     * @param $entity entity|entityList
     * @param $level int
     * @param $metaTableCaller metaTable
     * @param $metaFieldCaller metaField
     * @return mixed
     */
    public function update($entity, $level = 0, $metaTableCaller=NULL, $metaFieldCaller=NULL){
		$returnValue = $this->dataController->update($entity, $level, $metaTableCaller, $metaFieldCaller);
		/*
		if ($returnValue){
			if (isset($entity) && gettype($entity) != "array" &&  $entity->isEntityList) {
				$entityList = $entity;
			} else {
				if (gettype($entity) != "array"){
					$entityList = [];
					$entityList[] = $entity;
				} else {
					$entityList = $entity;
				}
			}
		}
		*/
		return($returnValue);
	}
	
	public function delete($entity, $engine = NULL){
		/*
		if ($engine){
			$engineCopy = $engine;
			//$entityCopy = $this->read($engineCopy);
			$engineCopy = NULL;
		}
		*/
		
		$returnValue = $this->dataController->delete($entity, $engine);

		/*
		if ($returnValue){
			if (isset($entityCopy)){
				$entityList = $entityCopy;
			} else {
				if (isset($entity) && gettype($entity) != "array" &&  $entity->isEntityList) {
					$entityList = $entity;
				} else {
					if (gettype($entity) != "array"){
						$entityList = [];
						$entityList[] = $entity;
					} else {
						$entityList = $entity;
					}
				}
			}
		}
		*/
		
		return($returnValue);
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
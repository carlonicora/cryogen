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
 * @package carlonicora\cryogen\mySqlCryogen
 * @author Carlo Nicora
 */
namespace carlonicora\cryogen\SQL;

use carlonicora\cryogen\cryogen;

use carlonicora\cryogen\cryogenException;
use carlonicora\cryogen\entity;
use carlonicora\cryogen\entityList;
use carlonicora\cryogen\queryEngine;
use carlonicora\cryogen\metaTable;
use carlonicora\cryogen\metaField;

/**
 * Class sqlCryogen
 *
 * @package carlonicora\cryogen\SQL
 */
abstract class sqlCryogen extends cryogen {
    /**
     * Initialises cryogen for MySql
     *
     * @param array $connectionString
     */
    public abstract function __construct($connectionString);

    /**
     * Clears the resources
     */
    public abstract function __destruct();

    /**
     * Updates an entity in the database.
     *
     * If the entity is not existing in the database, cryogen performs an INSERT, otherwise an UPDATE
     *
     * @param entity|entityList $entity
     * @return bool
     */
    public function update($entity){
        /**
         * @var entity $entity
         * @var queryEngine $engine
         * @var bool $returnValue
         * @var bool $noEntitiesModified
         * @var array $sqlParameters;
         */
        $returnValue = true;

        $noEntitiesModified = true;

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

        $this->connectionController->connect();

        foreach ($entityList as $entity){
            if ($entity->status() != entity::ENTITY_NOT_MODIFIED){
                $noEntitiesModified = false;

                $engine = $this->generateQueryEngine(NULL, $entity);

                $sqlStatement = $entity->status() == entity::ENTITY_NOT_RETRIEVED ? $engine->generateInsertStatement() : $engine->generateUpdateStatement();
                $sqlParameters = $entity->status() == entity::ENTITY_NOT_RETRIEVED ? $engine->generateInsertParameters() : $engine->generateUpdateParameters();

                if ($entity->status() == entity::ENTITY_NOT_RETRIEVED && $engine->hasAutoIncrementKey()){
                    $keyField = $engine->getAutoIncrementKeyName();
                    $entity->$keyField = true;
                    $returnValue = $this->setActionTransaction($sqlStatement, $sqlParameters, false, $entity->$keyField);
                } else {
                    $returnValue = $this->setActionTransaction($sqlStatement, $sqlParameters);
                }

                if (!$returnValue){
                    break;
                }
            }
        }

        if ($noEntitiesModified) {
            $returnValue = true;
        } else {
            if ($returnValue){
                $returnValue = $this->completeActionTransaction($returnValue);

                if ($returnValue){
                    foreach ($entityList as $entity){
                        $entity->setInitialValues();
                        $entity->setRetrieved();
                    }
                }

            } else {
                $this->completeActionTransaction($returnValue);
            }
        }

        return($returnValue);
    }

    /**
     * Deletes an entity in the database.
     *
     * @param entity|entityList $entity
     * @param queryEngine|null $engine
     * @return bool
     */
    public function delete($entity=null, queryEngine $engine=null){
        /**
         * @var array $sqlParameters;
         */
        $returnValue = false;

        if (!isset($entity) && !isset($engine)){
            $exception = new cryogenException(cryogenException::EMPTY_DELETE_PARAMETERS);
            $exception->log();
        } else {
            $entityList = null;

            if (isset($entity)) {
                if (isset($entity) && gettype($entity) != "array" && $entity->isEntityList) {
                    $entityList = $entity;
                } else {
                    if (isset($entity)) {
                        if (gettype($entity) != "array") {
                            $entityList = [];
                            $entityList[] = $entity;
                        } else {
                            $entityList = $entity;
                        }
                    }
                }
            }

            $this->connectionController->connect();

            if (isset($entityList)) {
                foreach ($entityList as $entity) {
                    if (isset($entity)) {
                        $engine = $this->generateQueryEngine(null, $entity);
                    }

                    $sqlStatement = $engine->generateDeleteStatement();
                    $sqlParameters = $engine->generateDeleteParameters();

                    $returnValue = $this->setActionTransaction($sqlStatement, $sqlParameters, true);

                    if (!$returnValue) {
                        break;
                    }
                }
            } else {
                $sqlStatement = $engine->generateDeleteStatement();
                $sqlParameters = $engine->generateDeleteParameters();

                $returnValue = $this->setActionTransaction($sqlStatement, $sqlParameters, true);
            }

            if ($returnValue) {
                $returnValue = $this->completeActionTransaction($returnValue);
            } else {
                $this->completeActionTransaction($returnValue);
            }
        }

        return($returnValue);
    }

    /**
     * Reads a list of records identified by the query engine.
     *
     * If the levels of relations to load is > 0, then cryogen will load records related to a single foreign key as
     * defined in the database objects
     *
     * @param queryEngine $engine
     * @param int $levelsOfRelationsToLoad
     * @param metaTable|null $metaTableCaller
     * @param metaField|null $metaFieldCaller
     * @param bool $isSingle
     * @return entity|entityList|null
     */
    public function read(queryEngine $engine, $levelsOfRelationsToLoad=0, metaTable $metaTableCaller=null, metaField $metaFieldCaller=null, $isSingle=false){
        /**
         * @var array $sqlParameters;
         */
        $this->connectionController->connect();
        $originalEngine = $engine;

        $sqlStatement = $engine->generateReadStatement();
        $sqlParameters = $engine->generateReadParameters();

        $returnValue = $this->setReadTransaction($engine, $sqlStatement, $sqlParameters);

        if ($levelsOfRelationsToLoad > 0 && ($returnValue && sizeof($returnValue) > 0)){
            if (isset($engine->meta->relations) && sizeof($engine->meta->relations) > 0){
                foreach ($engine->meta->relations as $relation){
                    $relationTarget = $relation->target;
                    if ((!isset($metaFieldCaller) && !isset($metaTableCaller)) || ($metaFieldCaller != $relation->linkedField || $metaTableCaller != $relation->linkedTable)){
                        $engine = NULL;
                        eval("\$engine = \$this->generateQueryEngine(" . $relation->linkedTable . "::\$table);");
                        foreach($returnValue as $parentEntity){
                            $fieldName = '';
                            eval("\$fieldName = " . $relation->table . "::\$" . $relation->field . "->name;");
                            $keyValue = $parentEntity->$fieldName;
                            eval("\$engine->setDiscriminant(" . $relation->linkedTable . "::\$" . $relation->linkedField . ", '" . $keyValue . "', \"=\", \" OR \");");
                        }
                        if ($levelsOfRelationsToLoad > 1){
                            $childrenEntities = $this->read($engine, $levelsOfRelationsToLoad-1, $relation->table, $relation->field);
                        } else {
                            $childrenEntities = $this->read($engine, $levelsOfRelationsToLoad-1);
                        }
                        $engine = null;

                        $parentFieldName = '';
                        $childFieldName = '';
                        eval("\$parentFieldName = " . $relation->table . "::\$" . $relation->field . "->name;");
                        eval("\$childFieldName = " . $relation->linkedTable . "::\$" . $relation->linkedField . "->name;");

                        foreach($returnValue as $parentEntity){
                            foreach (isset($childrenEntities) ? $childrenEntities : [] as $childEntity){
                                $isFine = $parentEntity->$parentFieldName == $childEntity->$childFieldName;
                                if ($isFine){
                                    if ($relation->relationType == 0){
                                        $parentEntity->$relationTarget = $childEntity;
                                        break;
                                    } else {
                                        $parentEntity->{$relationTarget}[] = $childEntity;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($isSingle){
            if (isset($returnValue) && sizeof($returnValue)==1){
                $returnValue = $returnValue[0];
            } else {
                $returnValue = null;
            }
        } else {
            if (sizeof($returnValue) > 0){
                $returnValue->meta = $originalEngine->meta;
            } else {
                $returnValue = NULL;
            }
        }

        return($returnValue);
    }

    /**
     * Reads one single record identified by the query engine.
     *
     * If the query returns more than one record, the system generates an error. This function is designed to return
     * a single-record query, not the first of many records.
     * If the levels of relations to load is > 0, then cryogen will load records related to a single foreign key as
     * defined in the database objects
     *
     * @param queryEngine $engine
     * @param int $levelsOfRelationsToLoad
     * @param metaTable|null $metaTableCaller
     * @param metaField|null $metaFieldCaller
     * @return entityList
     */
    public function readSingle(queryEngine $engine, $levelsOfRelationsToLoad=0, metaTable $metaTableCaller=null, metaField $metaFieldCaller=null){
        return($this->read($engine, $levelsOfRelationsToLoad, $metaTableCaller, $metaFieldCaller, true));
    }

    /**
     * Returns the number of records matching the query in the query engine
     *
     * @param queryEngine $engine
     * @return int
     */
    public function count(queryEngine $engine){
        /**
         * @var array $sqlParameters;
         */
        $this->connectionController->connect();

        $sqlStatement = $engine->generateReadCountStatement();
        $sqlParameters = $engine->generateReadParameters();

        $returnValue = $this->setCountTransaction($engine, $sqlStatement, $sqlParameters);

        return($returnValue);
    }
}
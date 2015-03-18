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

    /** @var  $structureController structureController */
	protected $structureController;

    /**
     * Create a new Database
     *
     * @param string $databaseName
     * @return bool
     */
    public function createDatabase($databaseName){
        if ($this->connectionController){
            $returnValue = $this->connectionController->createDatabase($databaseName);
        } else {
            $returnValue = false;
            $exception = new cryogenException(cryogenException::CONNECTION_CONTROLLER_NOT_INITIALISED);
            $exception->log();
        }

        return($returnValue);
    }

    /**
     * Confirms the connection to the database is active
     *
     * @return bool
     */
    public function isConnected(){
        if ($this->connectionController){
            $returnValue = $this->connectionController->isConnected();
        } else {
            $returnValue = false;
            $exception = new cryogenException(cryogenException::CONNECTION_CONTROLLER_NOT_INITIALISED);
            $exception->log();
        }

        return($returnValue);
    }

    /**
     * Generates the query engines used in cryogen
     *
     * @param metaTable|null $meta
     * @param entity|null $entity
     * @param null $valueOfKeyField
     * @return mixed
     */
    public abstract function generateQueryEngine(metaTable $meta=null, entity $entity=null, $valueOfKeyField=null);

    /**
     * Updates an entity in the database.
     *
     * If the entity is not existing in the database, cryogen performs an INSERT, otherwise an UPDATE
     *
     * @param entity $entity
     * @return bool
     */
    public abstract function update(entity $entity);

    /**
     * Deletes an entity in the database.
     *
     * @param entity|null $entity
     * @param queryEngine|null $engine
     * @return bool
     */
    public abstract function delete(entity $entity=null, queryEngine $engine=null);

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
    public abstract function read(queryEngine $engine, $levelsOfRelationsToLoad=0, metaTable $metaTableCaller=null, metaField $metaFieldCaller=null, $isSingle=false);

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
     * @return entity|null
     */
    public abstract function readSingle(queryEngine $engine, $levelsOfRelationsToLoad=0, metaTable $metaTableCaller=null, metaField $metaFieldCaller=null);

    /**
     * Returns the number of records matching the query in the query engine
     *
     * @param queryEngine $engine
     * @return int
     */
    public abstract function count(queryEngine $engine);

    /**
     * Runs the transactional INSERT, UPDATE or DELETE query on the database
     *
     * @param string $sqlStatement
     * @param array $sqlParameters
     * @param bool $isDelete
     * @param bool $generatedId
     * @return entityList
     */
    protected abstract function setActionTransaction($sqlStatement, $sqlParameters, $isDelete=false, &$generatedId=false);

    /**
     * Commit the INSERT, UPDATE or DELETE transaction on the database
     *
     * @param bool $commit
     * @return bool
     */
    protected abstract function completeActionTransaction($commit);

    /**
     * Runs the transactional SELECT query on the database
     *
     * @param queryEngine $engine
     * @param string $sqlStatement
     * @param array $sqlParameters
     * @return entityList
     */
    protected abstract function setReadTransaction(queryEngine $engine, $sqlStatement, $sqlParameters);

    /**
     * Specialised transaction that counts the records matching a specific query engine on the database
     *
     * @param queryEngine $engine
     * @param string $sqlStatement
     * @param array $sqlParameters
     * @return int
     */
    protected abstract function setCountTransaction(queryEngine $engine, $sqlStatement, $sqlParameters);
}
?>
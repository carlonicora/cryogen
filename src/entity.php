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
 * @package carlonicora\cryogen
 * @author Carlo Nicora
 */
namespace carlonicora\cryogen;

/**
 * The entity is the object view of a database record
 */
class entity{
    /**
     * @var int
     */
    public $entityStatus;

    /**
     * @var array
     */
    protected $_initialValues;

    /**
     * @var bool
     */
    public $entityRetrieved;

    /**
     * @var metaTable
     */
    public $metaTable;

    /**
     * @var bool
     */
    public $isEntityList=false;

    const ENTITY_NOT_RETRIEVED = 0;
    const ENTITY_NOT_MODIFIED = 1;
    const ENTITY_MODIFIED = 2;

    /**
     * @param $entity entity
     */
    public function __construct($entity = NULL){
        if (isset($entity) && gettype($entity) != "array" &&  $entity->isEntityList){
            $entity = $entity[0];
        }

        if (!$entity){
            $this->entityRetrieved = FALSE;
        } else {
            $this->metaTable = $entity->metaTable;
            $this->entityRetrieved = TRUE;
            $this->entityStatus = $entity->entityStatus;
            $this->_initialValues = $entity->_initialValues;
            $this->isEntityList = $entity->isEntityList;

            foreach($this->metaTable->fields as $field){
                $name = $field->name;
                $this->$name = $entity->$name;
            }

            if (isset($this->metaTable->relations) && sizeof($this->metaTable->relations) > 0){
                foreach($this->metaTable->relations as $relation){
                    $target = $relation->target;
                    $this->$target = $entity->$target;
                }
            }
        }
    }

    /**
     * Resets the entity
     */
    public function resetEntityStatus(){
        $this->entityStatus = false;
        $this->_initialValues = [];
    }

    /**
     * Set the flag that the entity has been retrieved from the databse instead of being a brand new record
     */
    public function setRetrieved(){
        $this->entityStatus = true;
    }

    /**
     * Duplicates the values read from the database in order to understand if and which field values have changed
     */
    public function setInitialValues(){
        foreach($this->metaTable->fields as $field){
            $name = $field->name;
            $this->_initialValues[$name] = $this->$name;
        }

        if (isset($this->metaTable->relations) && sizeof($this->metaTable->relations) > 0){
            foreach($this->metaTable->relations as $relation){
                $target = $relation->target;
                if($relation->relationType == 0){
                    /** @var $entity entity */
                    $entity = $this->$target;
                    if (isset($entity)){
                        $entity->setInitialValues();
                    }
                } else {
                    $entityList = $this->$target;
                    if (isset($entityList)){
                        /** @var entity $entity */
                        foreach($entityList as $entity){
                            $entity->setInitialValues();
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns if the record is new, has been read and not modified or has been read and modified
     *
     * @return int
     */
    public function status(){
        $returnValue = self::ENTITY_NOT_RETRIEVED;
        if ($this->entityStatus){
            foreach ($this->metaTable->fields as $field){
                $name = $field->name;

                if (!isset($this->_initialValues[$name]) && !isset($this->$name)){
                    $returnValue = self::ENTITY_NOT_MODIFIED;
                } else if (!isset($this->_initialValues[$name]) || !isset($this->$name)){
                    $returnValue = self::ENTITY_MODIFIED;
                } else {
                    if ($field->type == 'varchar' || $field->type == 'char') {
                        $returnValue = (strcmp($this->_initialValues[$name], $this->$name) == 0) ? self::ENTITY_NOT_MODIFIED : self::ENTITY_MODIFIED;
                    } elseif ($field->type == 'tinyint') {
                        $returnValue = ($this->_initialValues[$name] == $this->$name) ? self::ENTITY_NOT_MODIFIED : self::ENTITY_MODIFIED;
                    } elseif ($field->type == 'float') {
                        $returnValue = (abs($this->_initialValues[$name] - $this->$name) < 0.01) ? self::ENTITY_NOT_MODIFIED : self::ENTITY_MODIFIED;
                    } else {
                        $returnValue = ($this->_initialValues[$name] === $this->$name) ? self::ENTITY_NOT_MODIFIED : self::ENTITY_MODIFIED;
                    }
                }

                if ($returnValue == self::ENTITY_MODIFIED){
                    break;
                }
            }
        }

        return($returnValue);
    }

    /**
     * @param bool $useJson
     * @return array
     */
    public function getPublicObject($useJson=false){
        $response = array();

        foreach ($this->metaTable->fields as $field){
            $fieldName = $field->name;
            $fieldValue = $this->$fieldName;

            $response[$fieldName] = $fieldValue;
        }

        if ($useJson) $response = json_encode($response);

        return($response);
    }

    /**
     * @param bool $useJson
     * @return array
     */
    public function getPublicInitialObject($useJson=false){
        $response = array();

        foreach ($this->metaTable->fields as $field){
            $fieldName = $field->name;
            $fieldValue = $this->_initialValues[$fieldName];

            $response[$fieldName] = $fieldValue;
        }

        if ($useJson) $response = json_encode($response);

        return($response);
    }

    /**
     * Returns the list of modified fields with their original values
     *
     * @return array|null
     */
    public function getModifiedFieldsInitialValues(){
        /**
         * @var metaField $field
         */
        $returnValue = null;

        if ($this->status() == entity::ENTITY_MODIFIED){
            $returnValue = [];
            foreach ($this->metaTable->fields as $field){
                $name = $field->name;
                $newValue = null;
                $newValue = $this->$name;
                if (isset($this->_initialValues[$name])) {
                    $initialValue = $this->_initialValues[$name];
                } else {
                    $initialValue = null;
                }

                if (($field->type == 'varchar' || $field->type == 'char') && !strcmp($initialValue, $newValue) == 0) {
                    $returnValue[$name] = $initialValue;
                } elseif ($field->type == 'tinyint' && $initialValue != $newValue) {
                    $returnValue[$name] = $initialValue;
                } else {
                    if ($initialValue !== $newValue) {
                        $returnValue[$name] = $initialValue;
                    }
                }
            }
        }
        return($returnValue);
    }

    public function rebuildFromObject($object){
        $returnValue = true;

        /** @var metaField $metaField */
        foreach ($this->metaTable->fields as $metaField){
            $fieldName = $metaField->name;
            if (isset($object->$fieldName)) $this->$fieldName = $object->$fieldName;
        }

        if (isset($object->_initialValues)) {
            $this->_initialValues = $object->_initialValues;
        }
        $this->isEntityList = false;

        return($returnValue);
    }
}
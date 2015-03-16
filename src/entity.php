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

class entity{
    public $entityStatus;
    protected $_initialValues;
    public $entityRetrieved;

    public $metaTable;
    public $isEntityList = FALSE;

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

    public function setRetrieved(){
        $this->entityStatus = TRUE;
    }

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
                        foreach($entityList as $entity){
                            $entity->setInitialValues();
                        }
                    }
                }
            }
        }
    }

    public function status(){
        $returnValue = 0;
        if ($this->entityStatus){
            foreach ($this->metaTable->fields as $field){
                $name = $field->name;
                if ($field->type == 'varchar'){
                    $returnValue = (strcmp($this->_initialValues[$name], $this->$name) == 0) ? 1 : 2;
                } else {
                    $returnValue = ($this->_initialValues[$name] == $this->$name) ? 1 : 2;
                }

                if ($returnValue == 2){
                    break;
                }
            }
        }

        return($returnValue);
    }

    public function getModifiedFields(){
        $returnValue = NULL;

        if ($this->status() == 0){
            foreach ($this->metaTable->fields as $field){
                $name = $field->name;
                $returnValue[$name] = $this->$name;
            }
        } else {
            foreach ($this->metaTable->fields as $field){
                $name = $field->name;
                $initialValue = $this->_initialValues[$name];
                $newValue = $this->$name;
                if ($initialValue != $newValue){
                    $returnValue[$name] = $newValue;
                }
            }
        }
        return($returnValue);
    }

    public function getRecordIdentifier(){
        $returnValue = "";
        foreach ($this->metaTable->fields as $field){
            if ($field->isPrimaryKey){
                $name = $field->name;
                $newValue = $this->$name;
                $returnValue .= "~" . $newValue;
            }
        }

        $returnValue = substr($returnValue, 1);

        return($returnValue);
    }
}
?>
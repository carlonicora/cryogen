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

use ArrayObject;

/**
 * The entity list is the object view of a recordset. It extends the functionality of an array, adding features
 * used by a recordset
 */
class entityList extends ArrayObject{
    /**
     * @var bool
     */
    public $isEntityList = true;

    /**
     * @var metaTable
     */
    public $meta;

    /**
     * Initialises the entity list, setting the meta table if available
     *
     * @param metaTable|null $metaTable
     */
    public function __construct(metaTable $metaTable=null){
        if (isset($metaTable)){
            $this->meta = $metaTable;
        }
    }

    /**
     * Returns a list of entities matching the required field/values
     *
     * @param array $fields
     * @return array|null
     */
    public function getEntityByFields($fields){
        $returnValue = [];

        if (!$this->meta){
            if ($this[0]){
                $this->meta = $this[0]->metaTable;
            }
        }

        foreach ($this as $entity){
            $returnableEntity = $entity;

            foreach ($fields as $field){
                $fieldName = $field[0]->name;
                if ($entity->$fieldName != $field[1]){
                    $returnableEntity = null;
                    break;
                }
            }

            if (isset($returnableEntity)){
                $returnValue[] = $returnableEntity;
            }
        }

        if (sizeof($returnValue) == 0){
            $returnValue = null;
        }

        return($returnValue);
    }

    /**
     * Returns a list of entities matching the required field/values
     *
     * @param array $fields
     * @return entity
     */
    public function getFirstEntityByFields($fields){
        $returnValue = null;

        if (!$this->meta){
            if ($this[0]){
                $this->meta = $this[0]->metaTable;
            }
        }

        foreach ($this as $entity){
            $returnableEntity = $entity;

            foreach ($fields as $field){
                $fieldName = $field[0]->name;
                if ($entity->$fieldName != $field[1]){
                    $returnableEntity = null;
                    break;
                }
            }

            if (isset($returnableEntity)){
                $returnValue = $returnableEntity;
                break;
            }
        }

        return($returnValue);
    }

    /**
     * Replace the first entity matching the required field/values with the passed entity
     *
     * @param array $fields
     * @param entity $newEntity
     * @return bool
     */
    public function replaceFirstEntityByFields($fields, $newEntity){
        $returnValue = null;

        if (!$this->meta){
            if ($this[0]){
                $this->meta = $this[0]->metaTable;
            }
        }

        foreach ($this as &$entity){
            $returnableEntity = $entity;

            foreach ($fields as $field){
                $fieldName = $field[0]->name;
                if ($entity->$fieldName != $field[1]){
                    $returnableEntity = null;
                    break;
                }
            }

            if (isset($returnableEntity)){
                $entity = $newEntity;
                return(true);
            }
        }

        return(false);
    }
}
?>
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

class entityList extends \ArrayObject{
    public $isEntityList = TRUE;

    /** @var $meta metaTable */
    public $meta;

    public function __construct($metaTable=NULL){
        if (isset($metaTable)){
            $this->meta = $metaTable;
        }
    }

    public function getEntityByField($field, $value){
        $returnValue = NULL;

        if (!$this->meta){
            if ($this[0]){
                $this->meta = $this[0]->metaTable;
            }
        }

        $fieldName = $field->name;
        foreach ($this as $entity){
            if ($entity->$fieldName == $value){
                $returnValue = $entity;
                break;
            }
        }

        return($returnValue);
    }

    public function getEntityByFields($fields){
        $returnValue = NULL;

        if (!$this->meta){
            if ($this[0]){
                $this->meta = $this[0]->metaTable;
            }
        }

        foreach ($this as $entity){
            $returnValue = $entity;

            foreach ($fields as $field){
                //$fieldName = $field[0]->name;
                $fieldName = $field[0]->name;
                //if ($entity->$fieldName != $field[1]){
                if ($entity->$fieldName != $field[1]){
                    $returnValue = null;
                    break;
                }
            }

            if (isset($returnValue)) break;
        }

        return($returnValue);
    }
}
?>
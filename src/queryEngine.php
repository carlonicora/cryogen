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

class queryEngine{
    public $meta;

    protected $selectedFields;
    protected $keyFields;
    protected $normalFields;
    protected $discriminants;
    protected $ordering;
    protected $groupingFields;
    protected $limitStart;
    protected $limitLength;

    protected $dynamicFields;
    protected $dynamicDiscriminant;

    /**
     * @param $meta metaTable
     * @param $entity entity
     * @param $valueOfKeyField string
     */
    public function __construct($meta = NULL, $entity = NULL, $valueOfKeyField = NULL){
        $this->selectedFields = [];
        $this->keyFields = [];
        $this->normalFields = [];
        $this->discriminants = [];
        $this->dynamicFields = [];
        $this->dynamicDiscriminant = [];

        $this->limitStart = FALSE;
        $this->limitLength = FALSE;

        if (isset($meta)){
            $this->meta = $meta;
        }


        if (isset($entity)){
            if (!isset($this->meta)){
                $this->meta = $entity->metaTable;
            }
        }

        foreach($this->meta->fields as $field){
            $name = $field->name;
            $discriminant = new discriminant($field, NULL);
            if ($entity){
                $discriminant->value = $entity->$name;
            }
            if ($field->isPrimaryKey){
                $this->keyFields[] = $discriminant;
            } else {
                $this->normalFields[] = $discriminant;
            }
        }

        if ($valueOfKeyField){
            if (sizeof($this->keyFields) == 1){
                $this->discriminants[] = new discriminant($this->keyFields[0]->metaField, $valueOfKeyField);
            }
        }
    }

    public function md5($level){
        $returnValue = $this->meta->name . $this->limitLength . $this->limitStart;
        foreach ($this->discriminants as $discriminant){
            $returnValue .= $discriminant->metaField->name . $discriminant->value . $discriminant->value . $discriminant->clause . $discriminant->connector . $discriminant->separator;
        }
        if ($this->ordering && sizeof($this->ordering)){
            foreach ($this->ordering as $order){
                $returnValue .= $order[0]->name . $order[1];
            }
        }
        $returnValue .= $level;
        $returnValue = md5($returnValue);

        return($returnValue);
    }

    public function hasAutoIncrementKey(){
        $returnValue = ((sizeof($this->keyFields)==1) && $this->keyFields[0]->metaField->isAutoNumbering);

        return($returnValue);
    }

    public function getAutoIncrementKeyName(){
        $returnValue = $this->keyFields[0]->metaField->name;

        return($returnValue);
    }

    public function setDynamicField($field, $fieldName){
        $this->dynamicFields[$fieldName] = $field;
    }

    public function setLimitedFields(metaField $field, $sqlFunctionName=""){
        $this->selectedFields[$field->position]['field'] = $field;
        $this->selectedFields[$field->position]['sql'] = $sqlFunctionName;
    }

    public function getDynamicFieldsVariables(){
        $returnValue = [];

        if (isset($this->dynamicFields) && sizeof($this->dynamicFields) > 0){
            foreach($this->dynamicFields as $fieldName=>$field){
                $returnValue[] = $fieldName;
            }
        }

        return($returnValue);
    }

    public function getFieldsVariables(){
        $returnValue = [];

        if (isset($this->selectedFields) && sizeof($this->selectedFields) > 0){
            foreach($this->selectedFields as $field){
                $returnValue[] = $field['field']->name;
            }
        } else {
            foreach($this->meta->fields as $field){
                $returnValue[] = $field->name;
            }
        }

        return($returnValue);
    }

    public function setOrdering(metaField $field, $desc = FALSE){
        $this->ordering[] = [$field, $desc];
    }

    public function setGrouping(metaField $field){
        $this->groupingFields[] = $field;
    }

    public function setDiscriminant(metaField $field, $value, $clause="=", $connector=" AND ", $separator = ""){
        $this->discriminants[] = new discriminant($field, $value, $clause, $connector, $separator);
    }

    public function setDynamicDiscriminant($fieldName, $type, $value, $clause="=", $connector=" AND ", $separator = ""){
        $this->dynamicDiscriminant[] = new dynamicDiscriminant($fieldName, $type, $value, $clause, $connector, $separator);
    }

    public function setLimits($start, $length){
        $this->limitStart = $start ? $start : 0;
        $this->limitLength = $length;
    }

    public function generateReadCountStatement(){return(false);}
    public function generateReadStatement(){return(false);}
    public function generateReadParameters(){return(false);}

    public function generateUpdateStatement(){return(false);}
    public function generateUpdateParameters(){return(false);}

    public function generateInsertStatement(){return(false);}
    public function generateInsertParameters(){return(false);}

    public function generateDeleteStatement(){return(false);}
    public function generateDeleteParameters(){return(false);}
}
?>
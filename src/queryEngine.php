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
 * The query engine is the engine that manages all the information related to a table and its values to allow
 * cryogen to know how to exchange information with the database.
 * The query engine is database agnostic, but follows the standard SQL rules, so that can be extended for any
 * database
 */
abstract class queryEngine{
    /**
     * @var metaTable
     */
    public $meta;

    /**
     * @var metaFields
     */
    protected $selectedFields;

    /**
     * @var metaFields
     */
    protected $keyFields;

    /**
     * @var metaFields
     */
    protected $normalFields;

    /**
     * @var metaFields
     */
    protected $discriminants;

    /**
     * @var metaFields
     */
    protected $ordering;

    /**
     * @var metaFields
     */
    protected $groupingFields;

    /**
     * @var bool
     */
    protected $limitStart;

    /**
     * @var bool
     */
    protected $limitLength;

    /**
     * @var metaFields
     */
    protected $dynamicFields;

    /**
     * @var metaFields
     */
    protected $dynamicDiscriminant;

    /**
     * Initialises the query engine, identifying the metaTable or the entity that will define the table that will
     * be managed through it
     *
     * @param $meta metaTable
     * @param $entity entity
     * @param $valueOfKeyField string
     */
    public function __construct(metaTable $meta=null, $entity=null, $valueOfKeyField=null){
        $this->selectedFields = new metaFields();
        $this->keyFields = new metaFields();
        $this->normalFields = new metaFields();
        $this->discriminants = new metaFields();
        $this->dynamicFields = new metaFields();
        $this->dynamicDiscriminant = new metaFields();
        $this->ordering = new metaFields();
        $this->groupingFields = new metaFields();

        $this->limitStart = false;
        $this->limitLength = false;

        if (isset($meta)){
            $this->meta = $meta;
        } else if (isset($entity)){
            $this->meta = $entity->metaTable;

            $updatedFields = $entity->getModifiedFieldsInitialValues();
        }

        foreach($this->meta->fields as $field){
            $name = $field->name;
            $discriminant = new discriminant($field, NULL);
            if ($entity){
                $discriminant->value = $entity->$name;

                if (isset($updatedFields) && array_key_exists($field->name, $updatedFields)){
                    $discriminant->isChanged = true;
                    $discriminant->originalValue = $updatedFields[$field->name];
                }
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

    /**
     * Confirms if the table has an auto increment functionality on one field
     *
     * @return bool
     */
    public function hasAutoIncrementKey(){
        $returnValue = ((sizeof($this->keyFields)==1) && $this->keyFields[0]->metaField->isAutoNumbering);

        return($returnValue);
    }

    /**
     * Returns the meta fields which has an auto increment functionality
     *
     * @return metaField|null
     */
    public function getAutoIncrementKeyName(){
        $returnValue = null;

        if ($this->hasAutoIncrementKey()) {
            $returnValue = $this->keyFields[0]->metaField->name;
        }

        return($returnValue);
    }

    /**
     * Sets a new virtual field. A virtual field is a field that is generated by a query, but that is not a
     * real field in the table
     *
     * @param string $field
     * @param string $fieldName
     * @return bool
     */
    public function setDynamicField($field, $fieldName){
        $this->dynamicFields[$fieldName] = $field;

        return(true);
    }

    /**
     * Sets the query engine to read and return only a limited set of fields from the database, instead of all of them.
     * Using this functionality is discouraged if the object generated from it needs to be updated, as not all the
     * fields of the objects are set. the risk is to override the database values with nulls
     *
     * @param metaField $field
     * @param string $sqlFunctionName
     * @return bool
     */
    public function setLimitedFields(metaField $field, $sqlFunctionName=""){
        $this->selectedFields[$field->position]['field'] = $field;
        $this->selectedFields[$field->position]['sql'] = $sqlFunctionName;

        return(true);
    }

    /**
     * Returns an array of virtual fields name dynamically generated. A virtual field is a field that is generated
     * by a query, but that is not a real field in the table
     *
     * @return array
     */
    public function getDynamicFieldsVariables(){
        $returnValue = [];

        if (isset($this->dynamicFields) && sizeof($this->dynamicFields) > 0){
            foreach($this->dynamicFields as $fieldName=>$field){
                $returnValue[] = $fieldName;
            }
        }

        return($returnValue);
    }

    /**
     * returns an array of field names
     *
     * @return array
     */
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

    /**
     * Set a field to order the results of the query
     *
     * @param metaField $field
     * @param bool $desc
     * @return bool
     */
    public function setOrdering(metaField $field, $desc = FALSE){
        $this->ordering[] = [$field, $desc];

        return(true);
    }

    /***
     * Sets a field to group the results of a query
     *
     * @param metaField $field
     * @return bool
     */
    public function setGrouping(metaField $field){
        $this->groupingFields[] = $field;

        return(true);
    }

    /**
     * Sets a discriminant (field, value and various options) as the "WHERE" clause of an sql query
     *
     * @param metaField $field
     * @param mixed $value
     * @param string $clause
     * @param string $connector
     * @param string $separator
     * @return bool
     */
    public function setDiscriminant(metaField $field, $value, $clause="=", $connector=" AND ", $separator = ""){
        $this->discriminants[] = new discriminant($field, $value, $clause, $connector, $separator);

        return(true);
    }

    /**
     * Sets a discriminant (field, value and various options) as the "WHERE" clause of an sql query using a virtual
     * field. A virtual field is a field that is generated by a query, but that is not a real field in the table
     *
     * @param string $fieldName
     * @param string $type
     * @param mixed $value
     * @param string $clause
     * @param string $connector
     * @param string $separator
     * @return bool
     */
    public function setDynamicDiscriminant($fieldName, $type, $value, $clause="=", $connector=" AND ", $separator = ""){
        $this->dynamicDiscriminant[] = new dynamicDiscriminant($fieldName, $type, $value, $clause, $connector, $separator);

        return(true);
    }

    /**
     * Sets the limit to the number of records to be retrieved by a query, allowing to specify the starting record
     *
     * @param int $start
     * @param int $length
     * @return bool
     */
    public function setLimits($start, $length){
        $this->limitStart = $start ? $start : 0;
        $this->limitLength = $length;

        return(true);
    }

    /**
     * Generates the SQL statement needed for a count sql query
     *
     * @return string
     */
    public abstract function generateReadCountStatement();

    /**
     * Geneates the SQL statement needed for a read sql query
     *
     * @return string
     */
    public abstract function generateReadStatement();

    /**
     * Generates an array containing the parameters needed in WHERE or HAVING clauses
     *
     * @return array
     */
    public abstract function generateReadParameters();

    /**
     * Generates the SQL statement needed for an UPDATE sql query
     *
     * @return string
     */
    public abstract function generateUpdateStatement();

    /**
     * Generates an array containing the parameters needed in an UPDATE sql query
     *
     * @return array
     */
    public abstract function generateUpdateParameters();

    /**
     * Generates the SQL statement needed for an INSERT sql query
     *
     * @return string
     */
    public abstract function generateInsertStatement();

    /**
     * Generates an array containing the parameters needed in an INSERT sql query
     *
     * @return array
     */
    public abstract function generateInsertParameters();

    /**
     * Generates the SQL statement needed for a DELETE sql query
     *
     * @return string
     */
    public abstract function generateDeleteStatement();

    /**
     * Generates an array containing the parameters needed in aDELETE sql query
     *
     * @return array
     */
    public abstract function generateDeleteParameters();
}
?>
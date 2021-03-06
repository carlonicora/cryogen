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

use carlonicora\cryogen\queryEngine;
use carlonicora\cryogen\discriminant;
use carlonicora\cryogen\metaField;
use carlonicora\cryogen\dynamicDiscriminant;

/**
 * Class sqlQueryEngine
 *
 * @package carlonicora\cryogen\SQL
 */
abstract class sqlQueryEngine extends queryEngine {
    /**
     * Generates the SQL statement needed for a count sql query
     *
     * @return string
     */
    public function generateReadCountStatement(){
        $returnValue = 'SELECT '.$this->getCountField().' FROM '.$this->meta->name.$this->getWhereClause().';';
        return($returnValue);
    }

    /**
     * Geneates the SQL statement needed for a read sql query
     *
     * @return string
     */
    public function generateReadStatement(){
        $returnValue = 'SELECT '.$this->getSelectedFields().$this->getDynamicFields().' FROM '.$this->meta->name.$this->getWhereClause().$this->getHavingClause().$this->getGroupingClause().$this->getOrderClause().$this->getLimitClause().';';
        return($returnValue);
    }

    /**
     * Generates an array containing the parameters needed in WHERE or HAVING clauses
     *
     * @return array
     */
    public function generateReadParameters(){
        $returnValue = [];

        if (isset($this->discriminants) && sizeof($this->discriminants) > 0){
            $returnValue[0] = '';
            $returnValue[1] = [];
            foreach ($this->discriminants as $discriminant){
                if (isset($discriminant->value)){
                    $returnValue[0] .= $this->getDiscriminantTypeCast($discriminant->metaField);
                    $returnValue[1][] = $discriminant->value;
                }
            }
        }

        if (isset($this->dynamicDiscriminant) && sizeof($this->dynamicDiscriminant) > 0){
            if (!isset($this->discriminants) || sizeof($this->discriminants) == 0) {
                $returnValue[0] = '';
                $returnValue[1] = [];
            }
            foreach ($this->dynamicDiscriminant as $discriminant){
                if (isset($discriminant->value)){
                    if (!is_object($discriminant->value)) {
                        $returnValue[0] .= $this->getDiscriminantTypeCast($discriminant);
                        $returnValue[1][] = $discriminant->value;
                    }
                }
            }
        }

        return($returnValue);
    }

    /**
     * Generates the SQL statement needed for an UPDATE sql query
     *
     * @return string
     */
    public function generateUpdateStatement(){
        $returnValue = 'UPDATE '.$this->meta->name.' SET '.$this->getUpdatedFields().' WHERE '.$this->getKeyUpdateFields().';';

        return($returnValue);
    }

    /**
     * Generates an array containing the parameters needed in an UPDATE sql query
     *
     * @return array
     */
    public function generateUpdateParameters(){
        /**
         * @var discriminant $discriminant
         */
        $returnValue = [];

        $returnValue[0] = '';

        if (isset($this->normalFields) && sizeof($this->normalFields) > 0){
            if (!isset($returnValue[1])){
                $returnValue[1] = [];
            }
            foreach ($this->keyFields as $discriminant){
                if ($discriminant->isChanged) {
                    $returnValue[0] .= $this->getDiscriminantTypeCast($discriminant->metaField);
                    $returnValue[1][] = $discriminant->value;
                }
            }
            foreach ($this->normalFields as $discriminant){
                if ($discriminant->isChanged) {
                    $returnValue[0] .= $this->getDiscriminantTypeCast($discriminant->metaField);
                    $returnValue[1][] = $discriminant->value;
                }
            }
        }

        if (isset($this->keyFields) && sizeof($this->keyFields) > 0){
            foreach ($this->keyFields as $discriminant){
                $returnValue[0] .= $this->getDiscriminantTypeCast($discriminant->metaField);
                if ($discriminant->isChanged){
                    $returnValue[1][] = $discriminant->originalValue;
                } else {
                    $returnValue[1][] = $discriminant->value;
                }
            }
        }

        return($returnValue);
    }

    /**
     * Generates the SQL statement needed for an INSERT sql query
     *
     * @return string
     */
    public function generateInsertStatement(){
        $fieldParams = '';
        $returnValue = 'INSERT ';
        if ($this->meta->insertIgnore){
            $returnValue .='IGNORE ';
        }
        $returnValue .= 'INTO '.$this->meta->name.' ('.$this->getInsertFields($fieldParams).') VALUES ('.$fieldParams.');';

        return($returnValue);
    }

    /**
     * Generates an array containing the parameters needed in an INSERT sql query
     *
     * @return array
     */
    public function generateInsertParameters(){
        $returnValue = [];

        $returnValue[0] = '';

        if (isset($this->keyFields) && sizeof($this->keyFields) > 0){
            if (!isset($returnValue[1])){
                $returnValue[1] = [];
            }
            foreach ($this->keyFields as $discriminant){
                if (!$discriminant->metaField->isAutoNumbering){
                    $returnValue[0] .= $this->getDiscriminantTypeCast($discriminant->metaField);
                    $returnValue[1][] = $discriminant->value;
                }
            }
        }

        if (isset($this->normalFields) && sizeof($this->normalFields) > 0){
            if (!isset($returnValue[1])){
                $returnValue[1] = [];
            }
            foreach ($this->normalFields as $discriminant){
                $returnValue[0] .= $this->getDiscriminantTypeCast($discriminant->metaField);
                $returnValue[1][] = $discriminant->value;
            }
        }

        return($returnValue);
    }

    /**
     * Generates the SQL statement needed for a DELETE sql query
     *
     * @return string
     */
    public function generateDeleteStatement(){
        $returnValue = 'DELETE FROM ' . $this->meta->name . ' WHERE ';
        $sqlGenerated = FALSE;

        $lastDiscriminant = '';

        if (isset($this->keyFields) && sizeof($this->keyFields) > 0){
            foreach($this->keyFields as $discriminant){
                if (isset($discriminant->value)){
                    if (substr($discriminant->separator, 0, 1) == '(') {
                        $returnValue .= $discriminant->separator;
                    }
                    $returnValue .= $discriminant->metaField->quotedName() . $discriminant->clause . '?';
                    if (substr($discriminant->separator, 0, 1) == ')') {
                        $returnValue .= $discriminant->separator;
                    }
                    $returnValue .= $discriminant->connector;
                    $lastDiscriminant = $discriminant->connector;
                    $sqlGenerated = TRUE;
                }
            }
        }

        if (!$sqlGenerated) {
            if (isset($this->discriminants) && sizeof($this->discriminants) > 0){
                foreach($this->discriminants as $discriminant){
                    if (isset($discriminant->value)){
                        if (substr($discriminant->separator, 0, 1) == '(') {
                            $returnValue .= $discriminant->separator;
                        }
                        $returnValue .= $discriminant->metaField->quotedName() . $discriminant->clause . '?';
                        if (substr($discriminant->separator, 0, 1) == ')') {
                            $returnValue .= $discriminant->separator;
                        }
                        $returnValue .= $discriminant->connector;
                        $lastDiscriminant = $discriminant->connector;
                    }
                }
            } else {
                foreach ($this->normalFields as $discriminant){
                    if (isset($discriminant->value)){
                        if (substr($discriminant->separator, 0, 1) == '(') {
                            $returnValue .= $discriminant->separator;
                        }
                        $returnValue .= $discriminant->metaField->quotedName() . $discriminant->clause . '?';
                        if (substr($discriminant->separator, 0, 1) == ')') {
                            $returnValue .= $discriminant->separator;
                        }
                        $returnValue .= $discriminant->connector;
                        $lastDiscriminant = $discriminant->connector;
                    }
                }
            }
        }

        if (!$lastDiscriminant){
            $lastDiscriminant = '';
        }
        $returnValue = substr($returnValue, 0, strlen($returnValue)-strlen($lastDiscriminant));

        if (substr($returnValue, -7) == ' WHERE '){
            $returnValue = substr($returnValue, 0, -7);
        }

        return($returnValue);
    }

    /**
     * Generates an array containing the parameters needed in aDELETE sql query
     *
     * @return array
     */
    public function generateDeleteParameters(){
        $returnValue = [];

        $returnValue[0] = '';

        $sqlGenerated = FALSE;

        $keys = null;

        if (isset($this->keyFields) && sizeof($this->keyFields) > 0){
            foreach($this->keyFields as $discriminant){
                if (isset($discriminant->value)){
                    $keys = $this->keyFields;
                    $sqlGenerated = TRUE;
                }
            }

        }
        if (!$sqlGenerated) {
            if (isset($this->discriminants) && sizeof($this->discriminants) > 0){
                $keys = $this->discriminants;
            } else {
                $keys = $this->normalFields;
            }
        }

        foreach($keys as $discriminant){
            if (isset($discriminant->value)){
                $returnValue[0] .= $this->getDiscriminantTypeCast($discriminant->metaField);
                $returnValue[1][] = $discriminant->value;
            }
        }
        return($returnValue);
    }

    /**
     * Generates the part of the sql query specifying the LIMIT sql function
     *
     * @return string
     */
    protected function getLimitClause(){
        $returnValue = '';

        if ($this->limitStart || $this->limitLength){
            $returnValue = ' LIMIT ' . $this->limitStart . ',' . $this->limitLength;
        }

        return($returnValue);
    }

    /**
     * Generates the part of the sql query specifying COUNT sql function
     *
     * @return string
     */
    protected function getCountField(){
        $returnValue = '';

        foreach ($this->meta->fields as $metaField){
            $returnValue .= 'COUNT(' . $metaField->quotedName() . ') as Count ';
            break;
        }

        return($returnValue);
    }

    /**
     * Generates the part of the sql query specifying the fields to be returned
     *
     * @return string
     */
    protected function getSelectedFields(){
        $returnValue = '';

        if (!$this->removeDefaultFields) {
            $returnValue = '*';
            if (isset($this->selectedFields) && sizeof($this->selectedFields) > 0) {
                $returnValue = '';
                foreach ($this->selectedFields as $metaField) {
                    if ($metaField['sql'] && $metaField['sql'] != '') {
                        $returnValue .= $metaField['sql'] . '(' . $metaField['field']->name . ') as ' . $metaField['field']->name . ', ';
                    } else {
                        $returnValue .= $metaField['field']->name . ', ';
                    }
                }
                $returnValue = substr($returnValue, 0, strlen($returnValue) - 2);
            }
        }
        return($returnValue);
    }

    /**
     * Generates the part of the sql query specifying the virtual fields. A virtual field is a field that is generated
     * by a query, but that is not a real field in the table
     *
     * @return string
     */
    protected function getDynamicFields(){
        $returnValue = '';

        if (isset($this->dynamicFields) && sizeof($this->dynamicFields) > 0){
            foreach($this->dynamicFields as $fieldName=>$field){
                $returnValue .= ','.$field.' AS '.$fieldName;
            }
        }

        if ($this->removeDefaultFields){
            $returnValue = substr($returnValue, 1);
        }

        return($returnValue);
    }

    /**
     * Generates the part of the sql query specifying the HAVING clause
     *
     * @return string
     */
    protected function getHavingClause(){
        $returnValue = '';
        if (isset($this->dynamicDiscriminant) && sizeof($this->dynamicDiscriminant) > 0){
            $returnValue .= ' HAVING ';
            $returnValue .= $this->getWhereAndHavingClause(false);
        }

        return($returnValue);
    }

    /**
     * Generates the part of the sql query specifying the WHERE clause
     *
     * @return string
     */
    protected function getWhereClause(){
        $returnValue = '';

        if (isset($this->discriminants) && sizeof($this->discriminants) > 0){
            $returnValue .= ' WHERE ';
            $returnValue .= $this->getWhereAndHavingClause(true);
        }

        return($returnValue);
    }

    /**
     * Generates the part of the sql query specifying the ORDER BY clause
     *
     * @return string
     */
    protected function getOrderClause(){
        $returnValue = '';

        if (isset($this->ordering) && sizeof($this->ordering) > 0){
            $returnValue = ' ORDER BY ';
            foreach ($this->ordering as $order){
                $field = $order[0];

                $returnValue .= $field->name;
                if ($order[1]){
                    $returnValue .= ' DESC';
                }
                $returnValue .= ', ';
            }
            $returnValue = substr($returnValue, 0, strlen($returnValue)-2);
        }

        return($returnValue);
    }

    /**
     * Generates the part of the sql query specifying the GROUP BY clause
     *
     * @return string
     */
    protected function getGroupingClause(){
        $returnValue = '';

        if (isset($this->groupingFields) && sizeof($this->groupingFields) > 0){
            $returnValue = ' GROUP BY ';
            foreach ($this->groupingFields as $field){
                $returnValue .= $field->name . ', ';
            }
            $returnValue = substr($returnValue, 0, strlen($returnValue)-2);
        }

        return($returnValue);
    }

    /**
     * Returns the list of fields defining the record-identifying group during a UPDATE sql statement
     *
     * @return string
     */
    protected function getKeyUpdateFields(){
        $returnValue = '';

        foreach($this->keyFields as $discriminant){
            $returnValue .= $discriminant->metaField->quotedName() . $discriminant->clause . '? AND ';
        }

        $returnValue = substr($returnValue, 0, strlen($returnValue)-5);

        return($returnValue);
    }

    /**
     * Returns the normal fields used during an UPDATE sql statement
     *
     * @return string
     */
    protected function getUpdatedFields(){
        /**
         * @var discriminant $discriminant
         */
        $returnValue = '';

        foreach ($this->keyFields as $discriminant){
            if ($discriminant->isChanged){
                $returnValue .= $discriminant->metaField->quotedName() . $discriminant->clause . '?, ';
            }
        }
        foreach($this->normalFields as $discriminant){
            if ($discriminant->isChanged){
                $returnValue .= $discriminant->metaField->quotedName() . $discriminant->clause . '?, ';
            }
        }

        $returnValue = substr($returnValue, 0, strlen($returnValue)-2);

        return($returnValue);
    }

    /**
     * Returns the fields used during an INSERT ssql statement
     *
     * @param string $fieldParams
     * @return string
     */
    protected function getInsertFields(&$fieldParams) {
        $returnValue = '';

        foreach ($this->keyFields as $discriminant) {
            if (!$discriminant->metaField->isAutoNumbering) {
                $returnValue .= $discriminant->metaField->quotedName() . ', ';
                $fieldParams .= '?, ';
            }
        }

        foreach ($this->normalFields as $discriminant) {
            $returnValue .= $discriminant->metaField->quotedName() . ', ';
            $fieldParams .= '?, ';
        }

        if (strlen($returnValue) > 2) {
            $returnValue = substr($returnValue, 0, strlen($returnValue) - 2);
            $fieldParams = substr($fieldParams, 0, strlen($fieldParams) - 2);
        }

        return ($returnValue);
    }

    /**
     * Returns the type of field to be passed as type of parameters to mySql for the sql query preparation
     *
     * @param mixed $field
     * @return string
     */
    protected abstract function getDiscriminantTypeCast($field);

    /**
     * Returns the WHERE and HAVING common part of the clause
     *
     * @var bool $isWhereClause
     * @return string
     */
    protected function getWhereAndHavingClause($isWhereClause){
        /**
         * @var discriminant|dynamicDiscriminant $discriminant
         */
        $returnValue = '';

        $discriminantCount = ($isWhereClause) ? sizeof($this->discriminants) : sizeof($this->dynamicDiscriminant);

        for ($discriminantKey=0; $discriminantKey<$discriminantCount; $discriminantKey++){
            $discriminant = ($isWhereClause) ? $this->discriminants[$discriminantKey] : $this->dynamicDiscriminant[$discriminantKey];
            if (($isWhereClause && isset($discriminant->metaField)) || (!$isWhereClause && !isset($discriminant->metaField))) {
                if (substr($discriminant->separator, 0, 1) == '(') {
                    $returnValue .= $discriminant->separator;
                }
                if ($isWhereClause){
                    $returnValue .= $discriminant->metaField->quotedName();
                } else {
                    $returnValue .= $discriminant->fieldName;
                }


                if ($discriminant->clause == '=' && !isset($discriminant->value)) {
                    $returnValue .= ' IS NULL';
                } else {
                    if (($discriminant->clause == '!=' || $discriminant->clause == '<>') && !isset($discriminant->value)) {
                        $returnValue .= ' IS NOT NULL';
                    } else {
                        if ($discriminant->clause == ' *LIKE ') {
                            $returnValue .= ' LIKE ';
                        } else if ($discriminant->clause == ' NOT LIKE* ') {
                            $returnValue .= ' NOT LIKE ';
                        } else if ($discriminant->clause == ' *NOT LIKE ') {
                            $returnValue .= ' NOT LIKE ';
                        } else if ($discriminant->clause == ' LIKE* ') {
                            $returnValue .= ' LIKE ';
                        } else {
                            $returnValue .= $discriminant->clause;
                        }
                    }
                }

                $unset = true;
                if ($discriminant->clause == ' LIKE ') {
                    $returnValue .= "'%" . $discriminant->value . "%'";
                } else if ($discriminant->clause == ' *LIKE ') {
                    $returnValue .= "'%" . $discriminant->value . "'";
                } else if ($discriminant->clause == ' LIKE* ') {
                    $returnValue .= "'" . $discriminant->value . "%'";
                } else if ($discriminant->clause == ' NOT LIKE ') {
                    $returnValue .= "'%" . $discriminant->value . "*'";
                } else if ($discriminant->clause == ' *NOT LIKE ') {
                    $returnValue .= "'%" . $discriminant->value . "'";
                } else if ($discriminant->clause == ' NOT LIKE* ') {
                    $returnValue .= "'" . $discriminant->value . "%'";
                } else if ($discriminant->clause == ' IN ') {
                    $returnValue .= '(' . $discriminant->value . ')';
                } else if ($discriminant->clause == ' NOT IN ') {
                    $returnValue .= '(' . $discriminant->value . ')';
                } else {
                    $unset = false;
                    if (isset($discriminant->value)) {
                        if (is_object($discriminant->value) && $discriminant->value instanceof metaField) {
                            $returnValue .= $discriminant->value->name;
                        } else {
                            $returnValue .= '?';
                        }
                    }
                }

                if ($unset){
                    if ($isWhereClause){
                        unset($this->discriminants[$discriminantKey]);
                    } else {
                        unset($this->dynamicDiscriminant[$discriminantKey]);
                    }
                }

                if (substr($discriminant->separator, 0, 1) == ')') {
                    $returnValue .= $discriminant->separator;
                }
                $returnValue .= $discriminant->connector;
            }
        }
        if (substr($returnValue, strlen($returnValue) - 5) == ' AND '){
            $returnValue = substr($returnValue, 0, strlen($returnValue) - 5);
        } else if (substr($returnValue, strlen($returnValue) - 4) == ' OR '){
            $returnValue = substr($returnValue, 0, strlen($returnValue) - 4);
        }

        return($returnValue);
    }
}
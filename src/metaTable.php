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
 * The meta table is the object view of a db table
 */
class metaTable{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $object;

    /**
     * @var metaFields
     */
    public $fields;

    /**
     * @var array
     */
    public $relations;

    /**
     * @var bool
     */
    public $insertIgnore;

    /**
     * Initialises the meta table with the name of the table in the database and the fully qualified name of the object
     *
     * @param string $name
     * @param $object
     */
    public function __construct($name, $object) {
        $this->name = $name;
        $this->object = $object;
        $this->insertIgnore = false;
        $this->fields = new metaFields();
        $this->relations = [];
    }

    /**
     * Returns the fields that compose the primary key of a table
     *
     * @return metaFields
     */
    public function getKeyFields() {
        /**
         * @var $field metaField
         */
        $returnValue = new metaFields();

        foreach ($this->fields as $field) {
            if ($field->isPrimaryKey) {
                $returnValue[] = $field;
            }
        }

        return ($returnValue);
    }
}
?>
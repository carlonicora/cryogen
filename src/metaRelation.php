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
 * The metaRelation class identifies a relation between two tables managing primary key and foreign key in two tables
 * in order to link them together
 */
class metaRelation{
    /**
     * @var string
     */
    public $target;

    /**
     * @var string
     */
    public $field;

    /**
     * @var string
     */
    public $table;

    /**
     * @var string
     */
    public $linkedField;

    /**
     * @var string
     */
    public $linkedTable;

    /**
     * @var string
     */
    public $relationType;

    /**
     * Initialises the relation
     *
     * @param string $target
     * @param string $field
     * @param string $table
     * @param string $linkedField
     * @param string $linkedTable
     * @param string $relationType
     */
    public function __construct($target, $field, $table, $linkedField, $linkedTable, $relationType){
        $this->target = $target;
        $this->field = $field;
        $this->table = $table;
        $this->linkedField = $linkedField;
        $this->linkedTable = $linkedTable;
        $this->relationType = $relationType;
    }
}
?>
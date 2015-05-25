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
 * The discriminant class identifies a meta field used in the WHERE clause
 */
class discriminant{
    /**
     * @var metaField
     */
    public $metaField;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var string
     */
    public $clause;

    /**
     * @var string
     */
    public $connector;

    /**
     * @var string
     */
    public $separator;

    /**
     * @var bool
     */
    public $isChanged;

    /**
     * @var mixed
     */
    public $originalValue;

    /**
     * Initialises the discriminant, setting the meta field and the value
     *
     * @param metaField|null $metaField
     * @param mixed $value
     * @param string $clause
     * @param string $connector
     * @param string $separator
     */
    public function __construct($metaField=null, $value, $clause="=", $connector=" AND ", $separator = ""){
        $this->metaField = $metaField;
        $this->value = $value;
        $this->clause = $clause;
        $this->connector = $connector;
        $this->separator = $separator;
        $this->isChanged = false;
    }
}
?>
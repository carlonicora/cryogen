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
 * The Dynamic discriminant is a discriminant based on a virtual field. A virtual field is a field that is generated
 * by a query, but that is not a real field in the table
 */
class dynamicDiscriminant extends discriminant{
    /**
     * @var string
     */
    public $fieldName;

    /**
     * @var string
     */
    public $type;

    /**
     * Initialises the discriminant for the virtual field with its value
     *
     * @param string $fieldName
     * @param string $type
     * @param mixed $value
     * @param string $clause
     * @param string $connector
     * @param string $separator
     */
    public function __construct($fieldName, $type, $value, $clause="=", $connector=" AND ", $separator=""){
        $this->fieldName = $fieldName;
        $this->type = $type;
        parent::__construct(null, $value, $clause, $connector, $separator);
    }
}

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

class metaField{
    public $position;
    public $name;
    public $type;
    public $size;
    public $isPrimaryKey;
    public $isAutoNumbering;

    public function __construct($position, $fieldName, $fieldType, $fieldSize, $isPrimaryKey, $isAutoNumbering){
        $this->position = $position;
        $this->name = $fieldName;
        $this->type = $fieldType;
        $this->size = $fieldSize;
        $this->isPrimaryKey = $isPrimaryKey;
        $this->isAutoNumbering = $isAutoNumbering;
    }
}
?>
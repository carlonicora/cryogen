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

class metaFields extends \ArrayObject{
    /**
     * @param string $fieldName
     * @param string|null $tableName
     * @return metaField
     * @throws cryogenException
     */
    public function getFieldByName($fieldName, $tableName = null){
        /**
         * @var $returnValue metaField
         * @var cryogenException $exception
         * @var metaField $metaField
         */
        $returnValue = null;

        if (!isset($fieldName)){
            $exception = new cryogenException(cryogenException::FIELD_NOT_SPECIFIED);
            throw $exception;
        }

        foreach ($this as $metaField){
            if ($metaField->name == $fieldName){
                $returnValue = $metaField;
                break;
            }
        }

        if (!isset($returnValue)){
            $exception = new cryogenException(cryogenException::FIELD_NOT_FOUND, 'field name: '.$fieldName.(isset($tableName)?'-table name: '.$tableName:''));
            throw $exception;
        }

        return($returnValue);
    }
}
?>
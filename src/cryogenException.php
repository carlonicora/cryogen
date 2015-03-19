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

use Exception;

/**
 * Class cryogenException
 *
 * @package cryogen
 */
class cryogenException extends Exception {
    /** @const int GENERIC_ERROR Indicates a generic cryogen error */
    const GENERIC_ERROR = 1;
    const FIELD_NOT_FOUND = 2;
    const FIELD_NOT_SPECIFIED = 3;
    const FAILURE_CREATING_DATABASE_CONNECTION = 4;
    const EMPTY_DELETE_PARAMETERS = 5;
    const CONNECTION_CONTROLLER_NOT_INITIALISED=6;
    const ERROR_PREPARING_SQL_STATEMENT=7;
    const ERROR_BINDING_OBJECT_TO_TABLE_PARAMETERS=8;
    const ERROR_COMMITTING_QUERY = 9;
    const ERROR_RUNNING_UPDATE_QUERY = 10;

    /**
     *
     * @param string $cryogenExceptionId the exception id defined by one of the cryogenException constants
     * @param string|null $specification any additional specifications to be added to the error message
     */
    public function __construct($cryogenExceptionId, $specification = NULL) {
        parent::__construct($this->generateTextualDescription($cryogenExceptionId) . (isset($specification) ? ' ('.$specification.')' : ''), $cryogenExceptionId);
    }

    public function log(){
        error_log('Cryogen Exception: '. $this->getCode() . '-' . $this->getMessage());
    }

    /**
     * Returns a textual description for a specific cryogen error
     *
     * @param int $cryogenExceptionId
     * @return string
     */
    private function generateTextualDescription($cryogenExceptionId) {
        /** @var string $returnValue */

        switch ($cryogenExceptionId){
            case self::GENERIC_ERROR: $returnValue = ''; break;
            case self::FIELD_NOT_FOUND: $returnValue = 'field not found in meta table'; break;
            case self::FIELD_NOT_SPECIFIED : $returnValue = 'field name not specified while searching a field in a list of fields'; break;
            case self::FAILURE_CREATING_DATABASE_CONNECTION : $returnValue = 'failure in creating database connection'; break;
            case self::EMPTY_DELETE_PARAMETERS : $returnValue = 'at least one of the parameters for a deletion should be set'; break;
            case self::CONNECTION_CONTROLLER_NOT_INITIALISED : $returnValue = 'connection controller not initialised'; break;
            case self::ERROR_PREPARING_SQL_STATEMENT : $returnValue = 'error preparing the sql statment'; break;
            case self::ERROR_BINDING_OBJECT_TO_TABLE_PARAMETERS : $returnValue = 'error binding the database object parameters to the database table (is your DB table aligned to your DB object?)'; break;
            case self::ERROR_COMMITTING_QUERY : $returnValue = 'error committing the SQL to the database'; break;
            case self::ERROR_RUNNING_UPDATE_QUERY : $returnValue = 'error running the INSERT-UPDATE-DELETE query on the database'; break;

            default: $returnValue = 'Generic Error'; break;
        }

        return($returnValue);
    }
}
?>
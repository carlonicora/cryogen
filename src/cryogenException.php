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
            default: $returnValue = 'Generic Error'; break;
        }

        return($returnValue);
    }
}
?>
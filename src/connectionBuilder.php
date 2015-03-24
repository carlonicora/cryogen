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
 * Class cryogenConnectionBuilder
 *
 * @package CarloNicora\cryogen
 */
abstract class connectionBuilder {
    /**
     * @var string
     */
    public $databaseType;

    /**
     * @param array $connectionValues
     * @return connectionBuilder
     */
    public static function bootstrap($connectionValues){
        /**
         * @var connectionBuilder $returnValue
         */
        $databaseType = $connectionValues['type'];

        $cryogenConnectionBuilder = '\\CarloNicora\\cryogen\\'.$databaseType.'Cryogen\\'.$databaseType.'ConnectionBuilder';
        $returnValue = new $cryogenConnectionBuilder();
        $returnValue->initialise($connectionValues);

        return($returnValue);
    }

    /**
     * Initialises the connection parameters in the database-type-specific connection builder
     *
     * @param array $connectionValues
     */
    public abstract function initialise($connectionValues);

    /**
     * Extends the database name of the connection builder
     *
     * @param string $databaseName
     */
    public abstract function extendDatabaseName($databaseName);
}
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
 * Class cryogenBuilder
 *
 * @package CarloNicora\cryogen
 */
class cryogenBuilder {
    /**
     * Initialises a database-type specific cryogen
     *
     * @param connectionBuilder $connection
     * @return cryogen
     */
    public static function bootstrap(connectionBuilder $connection){
        $cryogen = '\\CarloNicora\\cryogen\\'.$connection->databaseType.'Cryogen\\'.$connection->databaseType.'Cryogen';

        $returnValue = new $cryogen($connection);

        return($returnValue);
    }
}
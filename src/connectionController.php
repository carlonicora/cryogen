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
 * connectionController
 *
 *
 */
abstract class connectionController{
    /** @var array */
    protected $connectionString;

    /**
     * The object that extends connectionController and provides connectivity to the specific
     * database type of a specific plugin
     *
     * @var object
     */
    public $connection;

    /**
     * The Cryogen object used from the connection controller
     *
     * @var cryogen
     */
    protected $cryogen;

    /**
     * Identifies if there is an active connection to the specific database
     *
     * @var bool
     */
    public $isConnected;

    /**
     * Constructs the connection controller
     *
     * @param cryogen $cryogen
     */
    public function __construct($cryogen){
        $this->cryogen = $cryogen;
    }

    /**
     * Stores the connection details in the connection controller and opens the connection to the database
     *
     * @param array $connectionString
     * @return bool
     */
    public abstract function initialize($connectionString);
    public abstract function connect();
    public abstract function disconnect();
    public abstract function createDatabase($databaseName);
}
?>
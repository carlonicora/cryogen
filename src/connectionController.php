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
 * Abstract class that provides the common methods for the connection
 *
 */
abstract class connectionController{
    /**
     * An array containing all the connection values needed by every single database plugin.
     * The composition of the array is defined at a plugin level
     *
     * @var array
     */
    protected $connectionString;

    /**
     * The object that extends connectionController and provides connectivity to the specific
     * database type of a specific plugin
     *
     * @var object
     */
    public $connection;

    /**
     * Stores the connection details in the connection controller and opens the connection to the database
     *
     * @param array $connectionString
     * @return bool
     */
    public abstract function initialize($connectionString);

    /**
     * Opens a connection to the database
     *
     * @return bool
     */
    public abstract function connect();

    /**
     * Closes a connection to the database
     *
     * @return bool
     */
    public abstract function disconnect();

    /**
     * Create a new Database
     *
     * @param string $databaseName
     * @return bool
     */
    public abstract function createDatabase($databaseName);

    /**
     * Returns the name of the database specified in the connection
     *
     * @return string
     */
    public abstract function getDatabaseName();

    /**
     * Identifies if there is an active connection to the database
     *
     * @return bool
     */
    public abstract function isConnected();
}
?>
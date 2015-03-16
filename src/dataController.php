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

abstract class dataController{
    public abstract function update($entity, $level = 0, $metaTableCaller=NULL, $metaFieldCaller=NULL);
    public abstract function delete($entity, $engine = NULL);
    public abstract function read(queryEngine $engine, $level=0, $metaTableCaller=NULL, $metaFieldCaller=NULL);
    protected abstract function createTransaction();
    protected abstract function setActionTransaction($sqlStatement, $sqlParameters, &$generatedId = FALSE);
    protected abstract function completeActionTransaction($commit);
    protected abstract function setReadTransaction(queryEngine $engine, $sqlStatement, $parameters);
    protected abstract function setCountTransaction(queryEngine $engine, $sqlStatement, $sqlParameters);
    public abstract function count(queryEngine $engine);
    public abstract function setManualSql($sqlStatement, $sqlParameters);
}
?>
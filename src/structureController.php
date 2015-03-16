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

class structureController{
    public function readStructure(){
        return(false);
    }

    public function readTableStructure($tableName){
        unset($tableName);
        return(false);
    }

    public function createView($viewSql){
        unset($viewSql);
        return(false);
    }

    public function createTable($metaTable, $isFederated = FALSE, $federatedLink = NULL){
        unset($metaTable);
        unset($isFederated);
        unset($federatedLink);
        return(false);
    }

    public function updateTable($metaTable){
        unset($metaTable);
        return(false);
    }

    public function generatePersistencyFiles($metaTables){
        $returnValue = "&lt;?php
";
        foreach ($metaTables as $metaTable){
            $returnValue .= "class " . $metaTable->name . " extends entity{\r\n";
            $returnValue .= "	protected \$_initialValues;\r\n\r\n";
            if (sizeof($metaTable->fields) > 0){
                foreach ($metaTable->fields as $field){
                    $returnValue .= "	public \$" . $field->name . ";\r\n";
                }
                $returnValue .= "\r\n";
            }
            if (sizeof($metaTable->relations) > 0){
                foreach ($metaTable->relations as $relation){
                    if (substr($relation->target, strlen($relation->target)-5) == "List_"){
                        $returnValue .= "	public \$" . $relation->target . $relation->linkedField . "_" . ";\r\n";
                    } else {
                        $returnValue .= "	public \$" . $relation->target . $relation->field . "_" . ";\r\n";
                    }
                }
                $returnValue .= "\r\n";
            }
            $returnValue .= "	public static \$table;\r\n";
            if (sizeof($metaTable->fields) > 0){
                foreach ($metaTable->fields as $field){
                    $returnValue .= "	public static \$field_" . $field->name . ";\r\n";
                }
                $returnValue .= "\r\n";
            }
            if (sizeof($metaTable->relations) > 0){
                foreach ($metaTable->relations as $relation){
                    if (substr($relation->target, strlen($relation->target)-5) == "List_"){
                        $returnValue .= "	public static \$relation_" . $relation->target . $relation->linkedField . ";\r\n";
                    } else {
                        $returnValue .= "	public static \$relation_" . $relation->target . $relation->field . ";\r\n";
                    }
                }
                $returnValue .= "\r\n";
            }
            $additionalParams = '';
            $additionalConstruct = '';

            if (sizeof($metaTable->fields) > 0){
                $additionalConstruct .= "\r\n";
                foreach ($metaTable->fields as $field){
                    $additionalParams .= ", \$" . $field->name . " = NULL";
                    $additionalConstruct .= "		if (isset(\$" . $field->name . ")) \$this->" . $field->name . " = \$" . $field->name . ";\r\n";
                }
            }

            $returnValue .= "	public function __construct(\$entity = NULL" . $additionalParams . "){\r\n";
            $returnValue .= "		\$this->metaTable = self::\$table;\r\n";
            $returnValue .= "		\$this->_initialValues = [];\r\n";
            $returnValue .= "		\r\n";
            $returnValue .= "		parent::__construct(\$entity);\r\n";
            $returnValue .= $additionalConstruct;
            $returnValue .= "	}\r\n";
            $returnValue .= "}\r\n";

            $returnValue .= $metaTable->name . "::\$table = new metaTable(\"" . $metaTable->name . "\", \"" . $metaTable->name . "\");\r\n";
            if (sizeof($metaTable->fields) > 0){
                foreach ($metaTable->fields as $field){
                    if (!isset($field->size) || $field->size == ""){
                        $field->size = 0;
                    }
                    $returnValue .= $metaTable->name . "::\$field_" . $field->name . " = new metaField(" . $field->position . ", \"" . $field->name . "\", \"" . $field->type . "\", " . $field->size . ", " . ($field->isPrimaryKey ? "TRUE" : "FALSE") . ", " . ($field->isAutoNumbering ? "TRUE" : "FALSE") . ");\r\n";
                }
                foreach ($metaTable->fields as $field){
                    $returnValue .= $metaTable->name . "::\$table->fields[] = " . $metaTable->name . "::\$field_" . $field->name . ";\r\n";
                }
                $returnValue .= "\r\n";
            }

            if (sizeof($metaTable->relations) > 0){
                foreach ($metaTable->relations as $relation){
                    if (substr($relation->target, strlen($relation->target)-5) == "List_"){
                        $returnValue .= $metaTable->name . "::\$relation_" . $relation->target . $relation->linkedField . " = new metaRelation(\"" . $relation->target . $relation->linkedField . "_" . "\", \"field_" . $relation->field . "\", \"" . $relation->table . "\", \"field_" . $relation->linkedField . "\", \"" . $relation->linkedTable . "\", " . $relation->relationType . ");\r\n";
                    } else {
                        $returnValue .= $metaTable->name . "::\$relation_" . $relation->target . $relation->field . " = new metaRelation(\"" . $relation->target . $relation->field . "_" . "\", \"field_" . $relation->field . "\", \"" . $relation->table . "\", \"field_" . $relation->linkedField . "\", \"" . $relation->linkedTable . "\", " . $relation->relationType . ");\r\n";
                    }
                }
                foreach ($metaTable->relations as $relation){
                    if (substr($relation->target, strlen($relation->target)-5) == "List_"){
                        $returnValue .= $metaTable->name . "::\$table->relations[] = " . $metaTable->name . "::\$relation_" . $relation->target . $relation->linkedField . ";\r\n";
                    } else {
                        $returnValue .= $metaTable->name . "::\$table->relations[] = " . $metaTable->name . "::\$relation_" . $relation->target . $relation->field . ";\r\n";
                    }
                }
                $returnValue .= "\r\n";
            }
        }
        $returnValue .= "?&gt;";
        return($returnValue);
    }
}
?>
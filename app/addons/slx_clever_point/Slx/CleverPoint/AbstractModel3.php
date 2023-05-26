<?php
/**
 * @copyright 2020 ArenaSoftwares.com.
 * @author Panos <panos@kartpay.com>
 * Date: 3/10/2020
 * Time: 5:39 μ.μ.
 */

namespace Slx\CleverPoint;

abstract class AbstractModel3 {

    protected $tableName;
    protected $descriptionsTableName = false;
    protected $idColumn;
    protected $labelColumn;
    protected $parentColumn;
    protected $row;
    protected $isNew;
    protected $hasStatusColumn = true;
    protected $scrollToEnd = false;
    protected $jsonFields = [];
    protected $tableFields;
    protected $descriptionsTableFields;


    public final function __construct($scrollToEnd = false) {
        $this->scrollToEnd = $scrollToEnd;
        $this->configure();
        if (empty($this->tableName)) {
            throw new \Exception("You must in configure method setup your model");
        }
        $this->descriptionsTableFields = [];
        $this->tableFields = \Tygh::$app['db']->getTableFields(str_replace("?:", '', $this->tableName));
        if ($this->descriptionsTableName) {
            $this->descriptionsTableFields = \Tygh::$app['db']->getTableFields(str_replace("?:", '', $this->descriptionsTableName));
        }
    }

    abstract public function configure();

    /*
     * Get single row by primary key
     */
    public function get($id, $langCode = CART_LANGUAGE) {
        if ($this->descriptionsTableName) {
            $this->row = db_get_row(
                "select {$this->descriptionsTableName}.*, {$this->tableName}.*  
                 from {$this->tableName} 
                 left join {$this->descriptionsTableName} on {$this->tableName}.{$this->idColumn}={$this->descriptionsTableName}.{$this->idColumn} 
                      and {$this->descriptionsTableName}.lang_code=?s 
                 where {$this->tableName}.{$this->idColumn}=?i",
                $langCode,
                $id
            );
        }
        else {
            $this->row = db_get_row("select * from {$this->tableName} where {$this->idColumn}=?i", $id);
        }
        if($this->row) {
            $this->decodeJsonFields($this->row);
        }
        $this->getRowRelatedEntities();
        return $this->row;
    }

    /*
     * Get record related records. Called from get()
     */
    protected function getRowRelatedEntities() {
        // override this if there are related records to main entity
    }


    public function update($id, $data, $langCode = CART_LANGUAGE) {
        $this->updatePre($id, $data);
        $data = $this->updatePreFixJsonFields($data);
        if (empty($id)) {
            $id = db_query("INSERT INTO {$this->tableName} ?e", $data);
            $this->isNew = true;
        }
        else {
            db_query("UPDATE " . $this->tableName . " set ?u where {$this->idColumn}=?i", $data, $id);
            $this->isNew = false;
        }
        if ($this->descriptionsTableName) {
            if ($this->hasDataTableFields($data, $this->descriptionsTableFields)) {
                $data[$this->idColumn] = $id;
                $data['lang_code'] = $langCode;
                db_query("REPLACE INTO {$this->descriptionsTableName} ?e", $data);
            }
        }
        $this->updateRelatedRecords($id, $data);
        return $id;
    }

    private function updatePreFixJsonFields($data) {
        if($this->jsonFields) {
            foreach($this->jsonFields as $fld) {
                if(!empty($data[$fld])) {
                    $data[$fld] = json_encode($data[$fld]);
                }
            }
        }
        return $data;
    }

    private function hasDataTableFields($data, $tblFields) {
        $common = array_intersect(array_keys($data), $tblFields);
        return count($common) > 0;
    }

    protected function updatePre(&$id, &$data) {
        // override this if any pre update manipulation required
    }

    protected function updateRelatedRecords($id, $data) {
        // override this if there are related records to main entity
    }

    public function delete($id) {
        $row = $this->get($id);
        if ($row) {
            $this->deleteRelatedRecords($id);
            db_query("delete from " . $this->tableName . " where {$this->idColumn}=?i", $id);
            if ($this->descriptionsTableName) {
                db_query("delete from " . $this->descriptionsTableName . " where {$this->idColumn}=?i", $id);
            }
        }
    }

    protected function deleteRelatedRecords($id) {
        // override this if there are related records to main entity
    }

    protected $sql;
    protected $joins;
    protected $conditions;
    protected $sorting;
    protected $fields;
    protected $params;
    protected $sortings;

    abstract protected function setupGetItemsComponents();

    protected function prepareSorting() {
        if (empty($this->sortings)) {
            $this->sortings = [
                $this->idColumn => $this->tableName . '.' . $this->idColumn,
            ];
        }
        $tmp_sort_keys = array_keys($this->sortings);
        $default_by = reset($tmp_sort_keys);
        $default_ord = 'asc';
        $params = $this->params;
        $this->sorting = db_sort($params, $this->sortings, $default_by, $default_ord);
        if ($this->sorting == ' ORDER BY ') {
            $this->sorting = '';
        }
        $this->params = $params;
    }

    /*
     * if any parameter name matches to table field adds a condition for that field
     */
    private function getItemsParamsToFilters() {
        foreach ($this->params as $key => $param) {
            if(empty($param) && $param!==0) {
                continue;
            }
            $table = '';
            if (in_array($key, $this->tableFields)) {
                $table = $this->tableName;
            }
            elseif (in_array($key, $this->descriptionsTableFields)) {
                $table = $this->descriptionsTableName;
            }
            if (!empty($table)) {
                $placeholder = $this->guessFilterParamPlaceholder($param);
                if (is_array($param)) {
                    $this->conditions[$key] = db_quote(sprintf("%s.%s in (%s)", $table, $key, $placeholder), $param);
                }
                else {
                    $this->conditions[$key] = db_quote(sprintf("%s.%s=%s", $table, $key, $placeholder), $param);
                }
            }
        }
    }

    private function guessFilterParamPlaceholder($param) {
        $out = '?s';
        if (gettype($param) == 'integer') {
            $out = '?i';
        }
        elseif (is_array($param)) {
            $out = '?a';
        }
        return $out;
    }

    protected function getItemsSql($params, $langCode = CART_LANGUAGE) {
        $sql = "select ?p from {$this->tableName}";
        $this->joins = [];
        $this->fields = [];
        $this->conditions = [];
        $this->sorting = '';
        if ($this->descriptionsTableName) {
            $this->fields[] = $this->descriptionsTableName . '.*';
            $this->joins['descriptions'] = db_quote(
                "left join {$this->descriptionsTableName} 
                     on {$this->tableName}.{$this->idColumn}={$this->descriptionsTableName}.{$this->idColumn}
                     and {$this->descriptionsTableName}.lang_code=?s
                ",
                $langCode
            );
        }
        $this->fields[] = $this->tableName . '.*';

        $this->params = $params;
        $this->prepareSorting();
        $this->getItemsParamsToFilters();
        $this->setupGetItemsComponents();

        $joins = implode(' ', $this->joins);
        $condition = implode(' AND ', $this->conditions);
        $sql = $sql . ' ' . $joins;
        if (!empty($condition)) {
            $sql = $sql . ' where ' . $condition;
        }
        return $sql;
    }

    public function getItems($params, $langCode = CART_LANGUAGE) {
        $this->configure();
        $this->sql = $this->getItemsSql($params, $langCode);
        $count = db_get_field($this->sql, "count(*) cnt");

        $limit = '';
        if (!empty($this->params['items_per_page'])) {
            $this->params['total_items'] = $count;
            $limit = $this->dbPaginate($this->params['page'], $this->params['items_per_page'], $this->params['total_items']);
        }
        $fields = implode(',', $this->fields);
        $rows = db_get_array(
            $this->sql . $this->sorting . $limit,
            $fields
        );
/*
        printf("%s <Br />", db_quote(
            $this->sql . $this->sorting . $limit,
            $fields
        ));
        */
        $rows = $this->getItemsFixJsonFields($rows);
        $rows = $this->getItemsRelatedEntities($rows);
        return [$rows, $this->params];
    }

    private function getItemsFixJsonFields($rows) {
        foreach($rows as &$row) {
            $this->decodeJsonFields($row);
        }
        return $rows;
    }

    private function decodeJsonFields(&$row) {
        foreach($this->jsonFields as $field) {
            if (!empty($row[$field])) {
                $row[$field] = @json_decode($row[$field], true);
            }
            if (!is_array($row[$field])) {
                $row[$field] = [];
            }
        }
    }

    public function getOneBy($params, $langCode = CART_LANGUAGE) {
        $out = false;
        $params['items_per_page'] = 1;
        list($items, $params) = $this->getItems($params, $langCode);
        if ($items) {
            $out = reset($items);
        }

        return $out;
    }

    protected function getItemsRelatedEntities($rows) {
        return $rows;
    }

    /*
     * Utility get items useful for select boxes population
     * returns id - labelColumn hash array of all records
     * if is set status column then applies status=A filter
     *
     */
    public function getListItems($parentId = 0, $langCode = CART_LANGUAGE) {
        $joins = [];
        if ($this->descriptionsTableName) {
            $join = db_quote(
                "left join {$this->descriptionsTableName} 
                     on {$this->tableName}.{$this->idColumn}={$this->descriptionsTableName}.{$this->idColumn}
                     and {$this->descriptionsTableName}.lang_code=?s
                ",
                $langCode
            );
            $sql = "select {$this->tableName}.{$this->idColumn}, {$this->descriptionsTableName}.{$this->labelColumn} from {$this->tableName} " . $join;
        }
        else {
            $sql = "select {$this->idColumn}, {$this->labelColumn} from {$this->tableName} ";
        }
        $conditions = [];
        if (!empty($this->parentColumn) && empty($parentId)) {
            return [];
        }
        if (!empty($this->parentColumn) && !empty($parentId)) {
            $conditions[] = db_quote("{$this->parentColumn}=?s", $parentId);
        }
        if (!empty($this->hasStatusColumn)) {
            $conditions[] = db_quote("status=?s", 'A');
        }
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= " ORDER BY " . $this->labelColumn;
        $items = db_get_hash_single_array($sql, [$this->idColumn, $this->labelColumn]);
        return $items;
    }

    private function dbPaginate(&$page, &$items_per_page, $total_items = 0) {
        $page = (int)$page;
        $items_per_page = (int)$items_per_page;

        if ($page <= 0) {
            $page = 1;
        }

        if ($items_per_page <= 0) {
            $items_per_page = 10;
        }

        // Check if page in valid limits
        if ($total_items > 0 && !$this->scrollToEnd) {
            $page = db_get_valid_page($page, $items_per_page, $total_items);
        }

        return ' LIMIT ' . (($page - 1) * $items_per_page) . ', ' . $items_per_page;
    }

    private function dbGetValidPage($page, $items_per_page, $total_items) {
        if (($page - 1) * $items_per_page >= $total_items) {
            $page = ceil($total_items / $items_per_page);
        }
        return empty($page) ? 1 : $page;
    }

    public function __call($method, $arguments) {
        $funcType = '';
        if (strpos($method, 'findBy') === 0) {
            $funcType = 'findBy';
        }
        if (strpos($method, 'findOneBy') === 0) {
            $funcType = 'findOneBy';
        }

        if ($funcType) {
            $findFields = $this->extractFieldNamesFromFuncName($method);
            if (count($findFields)) {
                if (count($findFields) == count($arguments)) {
                    $params = array_combine($findFields, $arguments);
                    if ($funcType=='findBy') {
                        return $this->getItems($params);
                    }
                    elseif ($funcType=='findOneBy') {
                        return $this->getOneBy($params);
                    }
                }
                else {
                    throw new \InvalidArgumentException(
                        sprintf("findBy %s expects %s arguments", $method, count($findFields))
                    );
                }
            }
        }
    }

    public function extractFieldNamesFromFuncName($funcName) {
        $out = [];
        if (strpos($funcName, 'findBy') === 0 || strpos($funcName, 'findOneBy') === 0) {
            if (strpos($funcName, 'findBy') === 0) {
                $nameStr = str_replace('findBy', '', $funcName);
            }
            elseif (strpos($funcName, 'findOneBy') === 0) {
                $nameStr = str_replace('findOneBy', '', $funcName);
            }
            if (strpos($nameStr, 'And') !== 0) {
                $out = explode('And', $nameStr);
            }
            else {
                $out[] = $nameStr;
            }
        }
        if (count($out)) {
            $out = array_map([$this, 'snakeToCamel'], $out);
        }
        return $out;
    }


    private function snakeToCamel(string $string): string {
        $delimiter = '_';
        $string = preg_replace("/(?!^)[[:upper:]]+/", $delimiter . '$0', $string);
        return strtolower($string);
    }

}
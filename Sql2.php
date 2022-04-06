<?php
class Sql2
{
    /* fields datatypes */
    const T_TEXT = 1;
    const T_NUMERIC = 2;
    const T_FLOAT = 3;
    const T_INTEGER = 4;
    const T_DATE = 5;
    const T_TIME = 6;
    const T_TIMESTAMP = 7;
    const T_RANGE = 8;
    const T_BOOLEAN = 9;
    const T_JSON = 10;
    const T_TEXT_ARRAY = 11;
    const T_CHAR = 12;
    const T_INTEGER_ARRAY = 13;
    const T_NUMERIC_ARRAY = 14;
    const T_FLOAT_ARRAY = 15;

    /* flags */
    const F_DISCARD_EMPTY_STRING = 0x01;
    const F_DISCARD_NULL = 0x02;

    private $FV; //Field-Value pairs
    
    public function __construct()
    {
        $this->FV = [];
    }

    /**
     * @param array $data 
     * associative array of key=>value pairs to be filtered by the $ruleset
     * @param array $ruleset
     * [key_name => [$fld_type, 'fld_prefix.fld_name', $flags, $value_conversion_function],
     * 		...
     * ]
     * 
     * - key_name .. corresponding $data[key]
     * - $fieldname_prefix .. Table name when needed. If not needed it can be null or ''
     * - $fld_name .. Table field name if different from input data parameter name. If field name is same as input parameter name, this can be null or ''
     */
    public function accept(array $data, array $ruleset)
    {
        foreach ($ruleset as $key => $rule) {

            if (array_key_exists($key, $data)){
                
                $fld_name_prefix = null;
                $fld_name = null;
                if (is_string($rule[1])) {
                    $fx = explode('.', $rule[1]);
                    if (count($fx) == 1) $fld_name = $fx[0];
                    else list($fld_name_prefix, $fld_name) = $fx;
                }
                $fld_name_prefix = (is_string($fld_name_prefix) && !empty($fld_name_prefix)) ? ($fld_name_prefix.'.') : '';
                if (!is_string($fld_name) || empty($fld_name)) $fld_name = $key;
                $final_fld_name = $fld_name_prefix.$fld_name;

                $par = ['type' => $rule[0],
                        'flags' => ((isset($rule[2]) && is_numeric($rule[2])) ? intval($rule[2]) : 0),
                        'value' => (is_callable($rule[3]) ? $rule[3]($data[$key]) : $data[$key])];

                if ($par['flags'] & self::F_DISCARD_NULL && is_null($par['value'])) continue;
                if ($par['flags'] & self::F_DISCARD_EMPTY_STRING) {
                    if ($par['type'] == self::T_TEXT && $par['value'] == '') continue;
                }

                $this->FV[$final_fld_name] = $par;
            }
        }
    }

    /** deprecated */
    public function addParam($type, $name, $value)
    {
        $this->FV[$name] = ['type'=>$type, 'value'=>$value];
    }

    public function setParam($name, $type, $value)
    {
        $this->FV[$name] = ['type'=>$type, 'value'=>$value];
    }

    public function getParam($name)
    {
        return $this->FV[$name];
    }

    public function removeParam($name)
    {
        unset($this->FV[$name]);
    }

    public function clear()
    {
        $this->FV = [];
    }

    private function prepareValue($prop)
    {
        extract($prop); //$type, $value, $flags(not used)
        switch ($type) {
        
        case self::T_TEXT:
            if (!is_string(($value))) return 'NULL';
            $value = str_replace("'", "''", $value);  //escape single quote
            return "'".$value."'";
            break;

        case self::T_TEXT_ARRAY:
            foreach ($value as &$item) {
                if (empty($item)) {
                    unset($item);
                    continue;
                }
                if (!is_string(($item))) $item = 'NULL';
                else {
                    $item = str_replace("'", "''", $item);
                    $item = "'".$item."'";
                }
            }
            return $value;
            break;

        case self::T_CHAR:
            if (empty($value)) return 'NULL';
            $value = str_replace('\'', '\\\'', $value);
            return "'".$value."'";
            break;

        case self::T_JSON:
            if (is_string($value)) {
                json_decode($value); //is valid JSON encoded?
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $value; //yes, it is JSON encoded
                }
            }
            return "'".json_encode($value)."'";
            break;

        case self::T_NUMERIC:
        case self::T_FLOAT:
            return (is_numeric($value) ? (float)($value) : 'NULL');
            break;

        case self::T_INTEGER:
            return (is_numeric($value) ? (int)($value) : 'NULL');
            break;

        case self::T_INTEGER_ARRAY:
            foreach ($value as &$item) {
                if (!is_numeric($item)) {
                    unset($item);
                    continue;
                }
                $item = (int)($item);
            }
            return $value;
            break;

        case self::T_NUMERIC_ARRAY:
        case self::T_FLOAT_ARRAY:
            foreach ($value as &$item) {
                if (!is_numeric($item)) {
                    unset($item);
                    continue;
                }
                $item = (float)($item);
            }
            return $value;
            break;

    
        case self::T_DATE:
        case self::T_TIME:
        case self::T_TIMESTAMP:
            $value = str_replace('\'', '', $value);
            return "'".$value."'";
            break;
        
        case self::T_RANGE:
            $value = str_replace('\'', '', $value);
            return "'[".$value[0].",".$value[1]."]'";
            break;
        
        case self::T_BOOLEAN:
            return ($value === true) ? 'TRUE' : 'FALSE';
            break;
        }
    }

    public function join_fields()
    {
        return implode(',', array_keys($this->FV));
    }

    public function join_values()
    {
        $VALS = [];
        foreach ($this->FV as $key=>$prop){
            //if (is_array($prop['value'])) continue;
            $VALS[] = $this->prepareValue($prop);
        }
        return implode(',', $VALS);
    }

    public function join_set() //Alias join_pairs
    {
        return $this->join_pairs();
    }

    public function join_pairs()
    {
        $PAIRS = [];
        foreach ($this->FV as $key=>$prop){
            //if (is_array($prop['value'])) continue;
            $PAIRS[] = $key.'='.$this->prepareValue($prop);
        }
        return implode(',', $PAIRS);
    }

    /**
     * @param string $join_operator ['AND'|'OR'] (default 'AND')
     * @param array $opt
     * [  field_name=>[cmp_operator, value_mutator], ... ]
     * 
     * - cmp_operator: (string) comaprison operator (default '=' for Numeric,Date,String_without_wildcard, 'ILIKE' for String_with_wildcard, '@>' for Range)
     * - value_mutator: function(current_value) : new_value
     */
    public function join_where($join_operator = 'AND', $opt = [])
    {
        $WHERE = [];

        foreach ($this->FV as $fld_name => $prop) {

            /* cmp_operator: ? */
            $cmp_operator = (isset($opt[$fld_name][0]) && !empty($opt[$fld_name][0])) ? ' '.$opt[$fld_name][0].' ' : null;
            /* value_mutator: ? */
            if (is_callable($opt[$fld_name][1])) $prop['value'] = $opt[$fld_name][1]($prop['value']);


            switch ($prop['type']) {

                case self::T_TEXT:
                    $pVal = $this->prepareValue($prop);
                    $cmp_operator = is_null($cmp_operator) ? '=' : $cmp_operator;
                    $WHERE[] = $fld_name.$cmp_operator.$pVal;
                    break;


                case self::T_NUMERIC:
                case self::T_FLOAT:
                    $cmp_operator = is_null($cmp_operator) ? '=' : $cmp_operator;
                    $WHERE[] = $fld_name.$cmp_operator.(float)($this->prepareValue($prop));
                    break;

                case self::T_INTEGER:
                    $cmp_operator = is_null($cmp_operator) ? '=' : $cmp_operator;
                    $WHERE[] = $fld_name.$cmp_operator.(int)($this->prepareValue($prop));
                    break;

                case self::T_TEXT_ARRAY:
                case self::T_INTEGER_ARRAY:
                case self::T_NUMERIC_ARRAY:
                case self::T_FLOAT_ARRAY:
                    $WHERE[] = $fld_name.' IN ('.implode(',',$this->prepareValue($prop)).')';
                    break;

                case self::T_DATE:
                case self::T_TIME:
                case self::T_TIMESTAMP:
                    $cmp_operator = is_null($cmp_operator) ? '=' : $cmp_operator;
                    $WHERE[] = $fld_name.$cmp_operator.$this->prepareValue($prop);
                    break;

                case self::T_RANGE:
                    $cmp_operator = is_null($cmp_operator) ? '@>' : $cmp_operator;
                    $WHERE[] = $fld_name.$cmp_operator.$this->prepareValue($prop);
                    break;
            }
        }

        return implode(" $join_operator ", $WHERE);
    }

    public function join_where_prepended($join_operator = 'AND', $opt = [])
    {
        $where = $this->join_where($join_operator, $opt);
        if (!empty($where)) $where = "WHERE $where";
        return $where;
    }

    public function dump()
    {
        return $this->FV;
    }


    /* Check input $params for:
     * @params = array(col_name=>direction, ...)   direction = ['ASC'|'DESC']
     */
    public static function order_by($params)
    {
        $ord = []; //col_name => direction

        foreach ($params as $col_name => $direction) {
            $ord[] = $col_name.' '.$direction;
        }

        if (empty($ord)) {
            return '';
        }

        return 'ORDER BY '.implode(',', $ord);
    }

    /* Check input $params for:
     * - 'length' or 'limit' -> generate SQL sentence part 'LIMIT x'
     * - 'start' or 'offset' -> generate SQL sentence part 'OFFSET x'
     */
    public static function limit_offset($params)
    {
        $return = '';

        if (isset($params['limit'])) {
            $return = 'LIMIT '.(int) ($params['limit']);
        } elseif (isset($params['length'])) { //js:DataTables
            $return = 'LIMIT '.(int) ($params['length']);
        }

        if (isset($params['offset'])) {
            $return .= ' OFFSET '.(int) ($params['offset']);
        } elseif (isset($params['start'])) { //js:DataTables
            $return .= ' OFFSET '.(int) ($params['start']);
        }

        return $return;
    }
    
    /* Convert timestamp range string returned from postgresql to array of two strings:
     * "\"yyyy-mm-dd hh:mm:ss\",\"yyyy-mm-dd hh:mm:ss\""  --> ["yyyy-mm-dd hh:mm:ss","yyyy-mm-dd hh:mm:ss"]
     */
    public static function ts_range_from_dbstring_to_array($tsrange_string)
    {
        return self::getArray_from_rangeStr($tsrange_string);
    }

    public static function getArray_from_rangeStr($range_string)
    {
        preg_match('/[\[\(](.*),(.*)[\]\)]/', $range_string, $matches);
        $matches[1] = str_replace('"','',$matches[1]);
        $matches[2] = str_replace('"','',$matches[2]);
        return [(empty($matches[1])? null : $matches[1]),
                (empty($matches[2])? null : $matches[2])];
    }
}
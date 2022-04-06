<?php
class Sql
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
    const F_DISCARD_EMPTY = 1;


    /* $data = [
     * 		key=>value,
     * 		...
     * ]
     * 
     * $conversion = [
     * 		key_name => [$fieldname_prefix, $fld_name, $fld_type], //format_1
     * 		key_name => [$fld_type, 'fld_prefix.fld_name', flags], //format_2
     * 		...
     * ]
     * $fieldname_prefix  (Table name when needed. If not needed it can be null or ''.)
     * $fld_name          (Table field name if different from input data parameter name. If fileld name is same as input parameter name, this can be null or ''.)
     */
    public static function collect(array $data, array $conversion)
    {
        $OUT = [];

        foreach ($conversion as $key => $conv) {

            if (isset($data[$key])) {

                $flags = 0;

                /* Detect data conversion format: ['prefix','name',type] or [type,'prefix.name'] */

                if (count($conv) == 3 && is_string($conv[0]) && is_integer($conv[2])) {//format_1: ['prefix','name',type]
                    
                    list($fld_name_prefix, $fld_name, $fld_type) = $conv;
                }
                else if (count($conv) > 0 && is_integer($conv[0])) {//format_2: [type,'prefix.name',flags]
                    
                    $fld_type = $conv[0];
                    $fld_name_prefix = null;
                    $fld_name = null;
                    if (is_string($conv[1])) {
                        $idntf = explode('.', $conv[1]);
                        if (count($idntf) == 1) $fld_name = $idntf[0];
                        else list($fld_name_prefix, $fld_name) = $idntf;
                    }
                    if (isset($conv[2]) && is_integer($conv[2])) $flags = $conv[2];
                    $flags = intval($conv[2]);
                }
                
                $fld_name_prefix = (is_string($fld_name_prefix) && !empty($fld_name_prefix)) ? ($fld_name_prefix.'.') : '';
                if (!is_string($fld_name) || empty($fld_name)) $fld_name = $key;
                $final_fld_name = $fld_name_prefix.$fld_name;

                switch ($fld_type) {
                
                case self::T_TEXT:
                    if (($flags & Sql::F_DISCARD_EMPTY) && empty($data[$key])) break;
                    $data[$key] = str_replace('\'', '"', $data[$key]);
                    $OUT[$final_fld_name] = ['value' => "'".$data[$key]."'"];
                    break;

                case self::T_TEXT_ARRAY:
                    foreach ($data[$key] as &$item) {
                        $item = str_replace('\'', '"', $item);
                        if (empty($item)) {
                            unset($data[$key]);
                            continue;
                        }
                        $item = "'".$item."'";
                    }
                    $OUT[$final_fld_name] = ['value' => $data[$key]];
                    break;

                case self::T_CHAR:
                    $data[$key] = str_replace('\'', '\\\'', $data[$key]);
                    $OUT[$final_fld_name] = ['value' => (empty($data[$key]) ? 'NULL' : "'".$data[$key]."'")];
                    break;

                case self::T_JSON:
                    $OUT[$final_fld_name] = ['value' => "'".json_encode($data[$key])."'"];
                    break;

                case self::T_NUMERIC:
                case self::T_FLOAT:
                    $OUT[$final_fld_name] = ['value' => (is_numeric($data[$key]) ? (float)($data[$key]) : 'NULL')];
                    break;

                case self::T_INTEGER:
                    $OUT[$final_fld_name] = ['value' => (is_numeric($data[$key]) ? (int)($data[$key]) : 'NULL')];
                    break;

                case self::T_INTEGER_ARRAY:
                    foreach ($data[$key] as &$item) {
                        if (!is_numeric($item)) {
                            unset($data[$key]);
                            continue;
                        }
                        $item = (int)($item);
                    }
                    $OUT[$final_fld_name] = ['value' => $data[$key]];
                    break;
    
                case self::T_NUMERIC_ARRAY:
                case self::T_FLOAT_ARRAY:
                    foreach ($data[$key] as &$item) {
                        if (!is_numeric($item)) {
                            unset($data[$key]);
                            continue;
                        }
                        $item = (float)($item);
                    }
                    $OUT[$final_fld_name] = ['value' => $data[$key]];
                    break;
    
            
                case self::T_DATE:
                case self::T_TIME:
                case self::T_TIMESTAMP:
                    $data[$key] = str_replace('\'', '', $data[$key]);
                    $OUT[$final_fld_name] = ['value' => "'".$data[$key]."'"];
                    break;
                
                case self::T_RANGE:
                    $data[$key] = str_replace('\'', '', $data[$key]);
                    $OUT[$final_fld_name] = ['value' => "'[".$data[$key][0].",".$data[$key][1]."]'"];
                    break;
                
                case self::T_BOOLEAN:
                    $OUT[$final_fld_name] = ['value' => (($data[$key]===true) ? 'TRUE' : 'FALSE')];
                    break;
                }
                
                if (isset($OUT[$final_fld_name])) $OUT[$final_fld_name]['type'] = $fld_type;
            }
        }

        return $OUT;
    }

    public static function join_fields($collection)
    {
        return implode(',', array_keys($collection));
    }

    public static function join_values($collection)
    {
        $VALS = [];
        foreach ($collection as $key=>$prop){
            if (is_array($prop['value'])) continue;
            $VALS[] = $prop['value'];
        }
        return implode(',', $VALS);
    }

    public static function join_set($collection) //Alias join_pairs
    {
        return self::join_pairs($collection);
    }

    public static function join_pairs($collection)
    {
        $PAIRS = [];
        foreach ($collection as $key=>$prop){
            if (is_array($prop['value'])) continue;
            $PAIRS[] = $key.'='.$prop['value'];
        }
        return implode(',', $PAIRS);
    }

    /**
     * $opt = [
     *     'join_operator'=>(string)           join operator ['AND'|'OR'] (default 'AND')
     *     'fopt'=>[
     *         field_name=>[
     *             'cmp_operator'=>(string)    comaprison operator (default '=' for Numeric,Date,String_without_wildcard, 'ILIKE' for String_with_wildcard, '@>' for Range)
     *         ]
     *     ]
     * ]
     */
    public static function join_where($collection, $opt = [])
    {
        $WHERE = [];

        if (is_string($opt)) $opt = ['join_operator'=>$opt]; //backward compatibility
        if (!isset($opt['join_operator'])) $opt['join_operator'] = 'AND';  //default
        if (!isset($opt['fopt']) || !is_array($opt['fopt'])) $opt['fopt'] = [];

        foreach ($collection as $fld_name => $prop) {

                $cmp_operator = isset($opt['fopt'][$fld_name]['cmp_operator']) ? ' '.$opt['fopt'][$fld_name]['cmp_operator'].' ' : null;

                switch ($prop['type']) {

                    case self::T_TEXT:
                        if (is_null($cmp_operator)) {
                            if (strpos($prop['value'], '%') !== false) $cmp_operator = ' ILIKE ';  //wildcard present
                            else $cmp_operator = '=';
                        }
                        $WHERE[] = $fld_name.$cmp_operator.$prop['value'];
                        break;


                    case self::T_NUMERIC:
                    case self::T_FLOAT:
                        $cmp_operator = is_null($cmp_operator) ? '=' : $cmp_operator;
                        $WHERE[] = $fld_name.$cmp_operator.(float)($prop['value']);
                        break;

                    case self::T_INTEGER:
                        $cmp_operator = is_null($cmp_operator) ? '=' : $cmp_operator;
                        $WHERE[] = $fld_name.$cmp_operator.(int)($prop['value']);
                        break;

                    case self::T_TEXT_ARRAY:
                    case self::T_INTEGER_ARRAY:
                    case self::T_NUMERIC_ARRAY:
                    case self::T_FLOAT_ARRAY:
                        $WHERE[] = $fld_name.' IN ('.implode(',',$prop['value']).')';
                        break;

                    case self::T_DATE:
                    case self::T_TIME:
                    case self::T_TIMESTAMP:
                        $cmp_operator = is_null($cmp_operator) ? '=' : $cmp_operator;
                        $WHERE[] = $fld_name.$cmp_operator.$prop['value'];
                        break;

                    case self::T_RANGE:
                        $cmp_operator = is_null($cmp_operator) ? '@>' : $cmp_operator;
                        $WHERE[] = $fld_name.$cmp_operator.$prop['value'];
                        break;
                }

        }

        return implode(" {$opt['join_operator']} ", $WHERE);
    }

    public static function format(array $data, array $fields, $fieldname_prefix = '') ////DEPRECATED
    {
        $PAR = [];

        foreach ($fields as $fld_name => $fld_type) {
            if (isset($data[$fld_name])) {
                switch ($fld_type) {
                    case self::T_TEXT:
                        $data[$fld_name] = str_replace("'", '"', $data[$fld_name]);
                        $PAR[$fieldname_prefix.$fld_name] = (empty($data[$fld_name]) ? 'null' : "'".$data[$fld_name]."'");
                        break;

                    case self::T_NUMERIC:
                    case self::T_FLOAT:
                        $PAR[$fieldname_prefix.$fld_name] = (float) ($data[$fld_name]);
                        break;

                    case self::T_INTEGER:
                        $PAR[$fieldname_prefix.$fld_name] = (int) ($data[$fld_name]);
                        break;
                
                    case self::T_DATE:
                    case self::T_TIME:
                    case self::T_TIMESTAMP:
                        $data[$fld_name] = str_replace("'", "", $data[$fld_name]);
                        $PAR[$fieldname_prefix.$fld_name] = "'".$data[$fld_name]."'";
                        break;
                    
                    case self::T_RANGE:
                        $data[$fld_name] = str_replace("'", "", $data[$fld_name]);
                        $PAR[$fieldname_prefix.$fld_name] = "'[".$data[$fld_name][0].",".$data[$fld_name][1]."]'";
                        break;
                    
                    case self::T_BOOLEAN:
                        $PAR[$fieldname_prefix.$fld_name] = (($data[$fld_name]===true) ? 'TRUE' : 'FALSE');
                        break;
                }
            }
        }

        return $PAR;
    }

    /* return ['fields' => 'F1,F2,F3,...,Fn',
     *         'values' => 'V1,V2,V3,...,Vn',
     *         'set'    => 'F1=V1,F2=V2,...,Fn=Vn']
     */
    public static function fields_values($fields_values)
    {
        $SET = [];
        foreach ($fields_values as $key=>$val) {
            $SET[] = $key.'='.$val;
        }
        return ['fields'=>implode(',', array_keys($fields_values)),
                'values'=>implode(',', array_values($fields_values)),
                'set'=>implode(',', $SET)];
    }
    
    public static function where($fields_values, $field_types, $op = 'AND')
    {
        $CHUNKS = [];

        foreach ($fields_values as $fld_name => $fld_value) {
            if (isset($field_types[$fld_name])) {
                switch ($field_types[$fld_name]) {
                    case self::T_TEXT:
                        $CHUNKS[] = $fld_name." ILIKE '".$fld_value."'";
                        break;

                    case self::T_NUMERIC:
                    case self::T_FLOAT:
                        $CHUNKS[] = $fld_name.'='.(float) ($fld_value);
                        break;

                    case self::T_INTEGER:
                        $CHUNKS[] = $fld_name.'='.(int) ($fld_value);
                        break;

                    case self::T_DATE:
                    case self::T_TIME:
                    case self::T_TIMESTAMP:
                        $CHUNKS[] = $fld_name."='".$fld_value."'";
                        break;

                    case self::T_RANGE:
                        $CHUNKS[] = $fld_name."@>'".$fld_value."'";
                        break;
                }
            }
        }

        return implode(" $op ", $CHUNKS);
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

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
    

    public static function format(array $data, array $fields, $fieldname_prefix = '')
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
        foreach ($fields_values as $key=>$val){
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
    private static function limit_offset($params)
    {
        $return = '';

        if (isset($params['limit'])) {
            $return = 'LIMIT '.(int) ($params['limit']);
        } elseif (isset($params['length'])) {
            $return = 'LIMIT '.(int) ($params['length']);
        } //js:DataTables

        if (isset($params['offset'])) {
            $return .= ' OFFSET '.(int) ($params['offset']);
        } elseif (isset($params['start'])) {
            $return .= ' OFFSET '.(int) ($params['start']);
        } //js:DataTables

        return $return;
    }
    
    /* Convert timestamp range string returned from postgresql to array of two strings:
     * "\"yyyy-mm-dd hh:mm:ss\",\"yyyy-mm-dd hh:mm:ss\""  --> ["yyyy-mm-dd hh:mm:ss","yyyy-mm-dd hh:mm:ss"]
     */
    public static function ts_range_from_dbstring_to_array($tsrange_string)
    {
        preg_match('/[\[\(](.*),(.*)[\]\)]/', $tsrange_string, $matches);
        $matches[1] = str_replace('"','',$matches[1]);
        $matches[2] = str_replace('"','',$matches[2]);
        return [(empty($matches[1])? null : $matches[1]),
                (empty($matches[2])? null : $matches[2])];
    }

}

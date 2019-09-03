<?php

class Validator
{
    public static function apply($obj, $rules)
    {
        $error = [];
        foreach ($rules as $prop_name=>$rule_set){

            $property_is_set = isset($obj[$prop_name]);

            foreach ($rule_set as $rule){

                if (!$property_is_set){
                    if (in_array('required', $rule_set)){
                        $error[] = "Missing '$prop_name'";
                    }
                    break; //goto next property
                }

                $args = explode(':', $rule);
                $cmd = array_shift($args);

                switch ($cmd){
                    case 'is_num':
                        if (!self::is_num($obj[$prop_name])){
                            $error[] = "Not numeric '$prop_name' (".$obj[$prop_name].")";
                        }
                        break;

                    case 'is_id_num':
                        if (!self::is_id_num($obj[$prop_name])){
                            $error[] = "Invalid ID '$prop_name' (".$obj[$prop_name].")";
                        }
                        break;

                    case 'is_id_num_array':
                        if (!self::is_id_num_array($obj[$prop_name])){
                            $error[] = "Invalid array of IDs '$prop_name'";
                        }
                        break;

                    case 'is_time':
                        if (!self::is_time($obj[$prop_name])){
                            $error[] = "Invalid time '$prop_name' (".$obj[$prop_name].")";
                        }
                        break;

                    case 'is_date':
                        if (!self::is_date($obj[$prop_name])){
                            $error[] = "Invalid date '$prop_name' (".$obj[$prop_name].")";
                        }
                        break;

                    case 'is_timestamp':
                        if (!self::is_timestamp($obj[$prop_name])){
                            $error[] = "Invalid timestamp '$prop_name' (".$obj[$prop_name].")";
                        }
                        break;

                    case 'is_timestamp_range':
                        if (!self::is_timestamp_range($obj[$prop_name])){
                            $error[] = "Invalid timestamp range '$prop_name'";
                        }
                        break;

                    case 'is_not_empty_string':
                        if (!self::is_not_empty_string($obj[$prop_name])){
                            $error[] = "Empty string '$prop_name'";
                        }
                        break;

                    case 'is_string':
                        if (!self::is_string($obj[$prop_name])){
                            $error[] = "Not a string '$prop_name'";
                        }
                        break;

                    case 'is_bool':
                        if (!self::is_bool($obj[$prop_name])){
                            $error[] = "Not boolean '$prop_name'";
                        }
                        break;

                    case 'is_string_alphanumeric':
                        if (!self::is_string_alphanumeric($obj[$prop_name])){
                            $error[] = "Not alpha-numeric string '$prop_name'";
                        }
                        break;

                    case 'is_string_numeric':
                        if (!self::is_string_numeric($obj[$prop_name])){
                            $error[] = "Not a numeric string '$prop_name'";
                        }
                        break;

                    case 'is_string_alpha':
                        if (!self::is_string_alpha($obj[$prop_name])){
                            $error[] = "Not a alpha string '$prop_name'";
                        }
                        break;

                    case 'is_ip_address':
                        if (!self::is_ip_address($obj[$prop_name])){
                            $error[] = "Not valid IP address '$prop_name'";
                        }
                        break;
                }
            }
        }
        return $error;
    }

    public static function is_bool($var)
    {
        return is_bool($var);
    }

    public static function is_num($id)
    {
        return (is_numeric($id)) ? true : false;
    }

    public static function is_id_num($id)
    {
        return (is_integer($id) && $id > 0) ? true : false;
    }

    public static function is_id_num_array($arrid)
    {
        if (!is_array($arrid))
            return false;

        foreach ($arrid as $id){
            if (!self::is_id_num($id)){
                return false;
            }
        }

        return true;
    }

    public static function is_timestamp($ts)
    {
        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}[T ]\d{1,2}:\d{1,2}:[\d\.]+$/', $ts) !== 1)
            return false;

        return true;
    }

    /*Valid inputs:
     * ['yyyy-mm-dd hh:mm:ss.ssss', 'yyyy-mm-dd hh:mm:ss.ssss']
     * ['yyyy-mm-dd hh:mm:ss.ssss', null]
     * ['yyyy-mm-dd hh:mm:ss.ssss', '']
     * [null, 'yyyy-mm-dd hh:mm:ss.ssss']
     * ['', '']
     * [null, null]
     */
    public static function is_timestamp_range($range)
    {
        if (!is_array($range)) return false;
        if (count($range) != 2) return false;
        if (!(self::is_timestamp($range[0]) || self::is_empty_string($range[0]) || is_null($range[0]))) return false;
        if (!(self::is_timestamp($range[1]) || self::is_empty_string($range[1]) || is_null($range[1]))) return false;
        return true;
    }

    public static function is_time($ts)
    {
        if (preg_match('/^\d{1,2}:\d{1,2}(:[\d\.]+)*$/', $ts) !== 1)
            return false;

        return true;
    }

    public static function is_date($ts)
    {
        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $ts) !== 1)
            return false;

        return true;
    }

    public static function is_empty_string($str)
    {
        if (is_string($str) && empty($str)) return true;
        return false;
    }

    public static function is_string($str)
    {
        return is_string($str);
    }

    public static function is_not_empty_string($str)
    {
        if (is_string($str) && !empty($str)) return true;
        return false;
    }

    public static function is_ip_address($str)
    {
        if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $str) == 1) return true;
        return false;
    }

    public static function is_string_alphanumeric($str)
    {
		if (!is_string($str)) return false;
        //if (preg_match('/[^a-zA-Z0-9čšžČŠŽ]+/', $str) == 1) return false;
        if (preg_match('/[^a-zA-Z0-9]+/', $str) == 1) return false;
        return true;
    }

    public static function is_string_alpha($str)
    {
		if (!is_string($str)) return false;
        if (preg_match('/[^a-zA-ZčšžČŠŽ]+/', $str) == 1) return false;
        return true;
    }

    public static function is_string_numeric($str)
    {
		if (!is_string($str)) return false;
        if (preg_match('/[^0-9]+/', $str) == 1) return false;
        return true;
    }

    public static function filter(array $input, array $keys)
    {
        $out = [];
        foreach ($input as $k=>$v){
            if (in_array($k, $keys)) $out[$k] = $v;
        }
        return $out;
    }
}

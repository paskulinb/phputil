<?php

class Validator
{
    public static function apply($obj, $rules)
    {
        $VALID = [];
        foreach ($rules as $prop_name=>$rule_set){
			
			if (!isset($obj[$prop_name])){ //Is property missing?

				if (in_array('required', $rule_set)){

					$VALID[$prop_name] = 'missing required';
				}

				continue; //goto next property
			}
			else {
				$VALID[$prop_name] = false;
			}

            foreach ($rule_set as $test_functions){

                $test_functions = explode('|', $test_functions);
                foreach ($test_functions as $test_fn){

					if ($test_fn == 'required') continue; //ignore 'required' rule
					
					if (self::$test_fn($obj[$prop_name])){
						$VALID[$prop_name] = true;
					}
					else {
						if ($VALID[$prop_name] === true) continue; //already met some condition
						//$VALID[$prop_name] = false;
						$VALID[$prop_name] = 'invalid '.print_r($obj[$prop_name], true);
					}
				}
            }
        }
        
        foreach ($VALID as $prop_name=>&$stat){
			if ($stat === false) {
				$VALID[$prop_name] = 'invalid';
			} else if ($stat === true) {
				unset($VALID[$prop_name]);
			}
		}
        return $VALID;
    }

    public static function is_bool($var)
    {
        return is_bool($var);
    }

    public static function is_integer($id)
    {
        return (is_integer($id)) ? true : false;
    }

    public static function is_num($id)
    {
        return (is_numeric($id)) ? true : false;
    }

    public static function is_id_num($id)
    {
		if (self::is_string_integer($id)) $id = intval($id);
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
		if (!is_string($ts)) return false;
        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}[T ]\d{1,2}:\d{1,2}:[\d\.+]+$/', $ts) !== 1)
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
		if (!is_string($ts)) return false;
        if (preg_match('/^\d{1,2}:\d{1,2}(:[\d\.]+)*$/', $ts) !== 1)
            return false;

        return true;
    }

    public static function is_date($ts)
    {
		if (!is_string($ts)) return false;
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
		if (!is_string($str)) return false;
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

    public static function is_string_integer($str)
    {
		if (!is_string($str)) return false;
        return ctype_digit($str);
    }

    public static function is_string_numeric($str)
    {
		if (!is_string($str)) return false;
        if (preg_match('/[^0-9\.]+/', $str) == 1) return false;
        return true;
    }

    public static function is_object($param)
    {
        return is_object($param);
    }

    public static function is_string_array($param)
    {
        if (!is_array($param)) return false;
        foreach ($param as $item) {
			if (!is_string($item)) return false;
		}
		return true;
    }

    public static function is_array($param)
    {
        return is_array($param);
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

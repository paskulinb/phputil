# Validator
* required
* is_id_num
* is_array_id_num
* is_timestamp
* is_timestamp_range
* is_not_empty_string
* is_string
* is_boolean
* is_string_alphanumeric
* is_string_numeric
* is_string_alpha
* is_ip_address
## Example
        $validator = new Validator();
        $error = $validator->apply($param,
                                   ['id'           => ['is_id_num'],
                                    'ime'          => ['required', 'is_string_alphanumeric'],
                                    'servis_id'    => ['required', 'is_id_num'],
                                    'uecp_site'    => ['is_num'],
                                    'uecp_encoder' => ['is_num'],
                                    'ip_naslov'    => ['is_ip_address'],
                                    'protocol'     => ['is_string']]);

        if (!empty($error)) {
            return ['success'=>false, 'error'=>$error];
		}

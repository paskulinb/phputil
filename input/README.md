# Validator
This class is intended to use for input data validation from HTTP request (forms, ajax).
```php
Validator::apply(array $param, array $ruleset): array
```
* **$param** Input data to be validated. It is array of *param_name => param_value* pairs. 
* **$ruleset** Ruels for *$param* test. Array of *param_name => param_type* pairs (see example).
## Parameter type options
* required
* is_string
* is_string_alphanumeric
* is_string_numeric
* is_string_alpha
* is_not_empty_string
* is_empty_string
* is_bool
* is_integer
* is_num
* is_id_num
* is_id_num_array
* is_time
* is_date
* is_timestamp
* is_timestamp_range
* is_ip_address
## Example
```php
$validator = new Validator();
$messages = $validator->apply(
    $input_params_to_test,
    [
        'id'         => ['is_id_num'],
        'name'       => ['required', 'is_string_alphanumeric', 'is_not_empty_string'],
        'service_id' => ['required', 'is_id_num'],
        'count'      => ['is_num'],
        'host'       => ['is_ip_address'],
        'protocol'   => ['is_string']
    ]
);

if (!empty($messages)) {
    print_r($messages);
}
```

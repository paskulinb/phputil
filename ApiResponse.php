<?php
class ApiResponse
{
    private $response;
    
    public function __construct()
    {
        $this->response['success'] = true;
    }
    
    public function Success(bool $success)
    {
        $this->response['success'] = $success;
        return $this;
    }
    
    public function Error($message)
    {
        $this->response['message'] = $message;
        $this->response['type'] = 'error';
        $this->response['success'] = false;
        return $this;
    }
    
    public function Warning($message)
    {
        $this->response['message'] = $message;
        $this->response['type'] = 'warning';
        return $this;
    }
    
    public function Info($message)
    {
        $this->response['message'] = $message;
        $this->response['type'] = 'info';
        return $this;
    }
    
    public function Debug($message)
    {
        $this->response['debug'] = $message;
        $this->response['type'] = 'debug';
        $this->response['success'] = false;
        return $this;
    }
    public function Set($custom_key, $custom_value)
    {
        $this->response[$custom_key] = $custom_value;
        return $this;
    }
    
    public function SetList($value)
    {
        $this->response['list'] = (array) $value;
        return $this;
    }
    
    public function SetItem($value, $extract_first_if_array = true)
    {
        $this->response['item'] =
            ($extract_first_if_array && is_array($value) && isset($value[0]))
            ? $value[0]
            : $value;
        return $this;
    }
    
    public function SetSuggestion($field, $value)
    {
        $this->response['found'][$field] = $value;
        return $this;
    }
    
    public function Output()
    {
        return $this->response;
    }
}
 

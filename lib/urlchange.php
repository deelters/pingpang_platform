<?
    class app
    {
        var $url_args;
        var $controller;
        var $method;
        var $argument;

        function __construct()
        {
            $this->url_args=explode("/",$_SERVER["REQUEST_URI"]);
            $this->controller=$this->url_args[1];
            $this->method=$this->url_args[2];
        }

        function get_controller()
        {
            return $this->controller;
        }

        function get_method()
        {
            return $this->method;
        }

        function get_argument($num)
        {
            if(count($this->url_args)>3)
                return $this->url_args[2+$num];
            else
                return false;
        }
    };
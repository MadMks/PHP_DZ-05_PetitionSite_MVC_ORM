<?php 
    function __autoload($class){
        require 'app/classes/' . $class . '.php';
    }
?>
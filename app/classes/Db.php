<?php

class Db
{
    public static function getConnection()
    {
        $paramsPath = 'app/config/db_params.php';
        $params = include($paramsPath);

        try{
            $dsn = "mysql:host={$params['host']};"
                . "dbname={$params['dbname']}";

            $dbh = new PDO(
                $dsn,
                $params['user'],
                $params['password']);
        }
        catch(PDOException $e){
            echo($e->getMessage());
            exit();
        }

        return $dbh;
    }
}
<?php

define("db_username", "root");
define("db_password", "@Ktn2b");
define("db_database", "pingpang");
define("db_host", "127.0.0.1");

class DB
{
    public function getConn()
    {
        return new mysqli(db_host, db_username, db_password, db_database);
    }
}

<?php

//Class for database connection
class dbconnections
{
    //Initialise Constant
    private $connection;
        //Function to Connect to database
    function connect()
    {
        include_once dirname(__FILE__)  . '/dbconstants.php';
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        //Connection error message
        if (mysqli_connect_error()) {
            echo "Failed to Connect to Database" . mysqli_connect_error();
            return null;
        }
        return $this->connection;
    }
}

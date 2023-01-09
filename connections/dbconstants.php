<?php
//Constants used for database connection
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASSWORD','');
define('DB_NAME','my_app');

//Constants used for User creation with status response codes
define('USER_CREATED',101);
define('USER_EXISTS',102);
define('USER_FAILURE',103);

//Constants used for User Reading(Login)
define('USER_ACCEPTED',201);
define('USER_NOT_FOUND',202);
define('USER_INVALID',203);

//Constants used for Password Update
define('PASSWORD_UPDATED',301);
define('PASSWORD_UNCHANGED',302);
define('PASSWORD_INVALID',303);

//Constants used for Password Update
define('USER_DELETED',401);
define('USER_UNCHANGED',402);
?>
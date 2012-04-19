<?php

spl_autoload_register(
    function($className)
    {
        $fileName = str_replace("\\", DIRECTORY_SEPARATOR, $className);
        /* path to the same dir as the main Assetic folder */
        require __DIR__ . "/../../$fileName.php";
    }
);

?>

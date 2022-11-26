<?php

function checkKey($table, $primaryID, $keyName, $keyValue, &$db)
{   
    
    $stmt = $db->prepare("SELECT * FROM `$table` WHERE `id` = :primaryid AND `$keyName` = :keyvalue");
    $stmt->bindParam('primaryid', $primaryID);
    $stmt->bindParam('keyvalue', $keyValue);
    $stmt->execute();
    $checkKeyQuery = $stmt->fetch();

    if($checkKeyQuery){
        return $checkKeyQuery;
    }
    else {
        return NULL;
    }
}

?>
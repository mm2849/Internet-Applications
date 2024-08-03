<?php

//internal API endpoint to receive data and do something with it.
//Temperory stopm not a user page
require(__DIR__ . "/../../../lib/functions.php");
session_start();

echo "<pre>"; 
var_dump($_GET);
echo "</pre>";

if(isset($_GET["user_id"]) && is_logged_in()){
    //Implementing some purchasing logic
    $db = getDB();
    $query = "INSERT INTO UserCountries (user_id, country_id) VALUES (:user_id, :country_id)";
    try{
    $stmt = $db->prepare($query);
    error_log("Inserting: user_id = " . get_user_id() . ", country_id = " . $_GET["country_id"]);
    error_log("GET data: " . var_export($_GET, true));
    $stmt->execute([":user_id"=>get_user_id(), ":country_id"=>$_GET["country_id"]]);
    flash("CONGRADUALTIONS ON PURCHASING A COUNTRY!!!", "success");
    redirect("my_countries.php");
    }
    catch(PDOException $e){
        if($e->errorInfo[1] === 1062){
            flash("This country is not available", "danger");
        }
        else{
            flash("Unhandled error occured", "danger");
        }
        error_log("Error purchasing country: " . var_export($e, true));
    }

}

//die(header("Location: " . get_url("countries.php")));
redirect("my_countries.php");

<?php
//mm2849
//07/27/2024
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    //die(header("Location: $BASE_PATH" . "/home.php"));
    redirect("home.php");
}
?>


<?php
$id = se($_GET, "id", -1, false);

// TODO Handle Country Fetch
if (isset($_POST["code"])) {
    foreach ($_POST as $k => $v) {
        if (!in_array($k, ["code", "code2", "name", "localname", "continent", "region", "indepyear", "surfacearea", "governmentform"])) {
            unset($_POST[$k]);
        }
        $quote = [$_POST];
        error_log("Cleaned up POST: " . var_export($quote, true));
    }

    //Inserting Database & API Data
    $db = getDB();
    $query = "UPDATE `Countries` SET ";
    $params = [];
    //per record
    $quote = $quote[0];
    foreach ($quote as $k => $v) {
        //array_push($columns, "`$k`");
        if($params){
            $query .=",";
        }
        $query .= "$k=:$k";
        $params[":$k"] = $v;
    }
    $query .= " WHERE id = :id";
    $params[":id"] = $id;
    error_log("Query: " . $query);
    error_log("Params: " . var_export($params, true));
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        //mm2849
        //7/27/2024
        flash("Updated record", "success");
    } catch (PDOException $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred", "danger");
    }
}
$country = [];

if ($id > -1) {
    $db = getDB();
    $query = "SELECT name , code, code2, name, localname, continent, region, indepyear, surfacearea, governmentform FROM `Countries` WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $country = $r;
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }
} else {
    flash("Invalid id passed", "danger");
    //die(header("Location:" . get_url("admin/list_country.php")));
    redirect("admin/list_country.php");
}


if ($country) {
    $form = [
        ["type" => "text", "name" => "code", "placeholder" => "Country Code", "label" => "Country Code", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "code2", "placeholder" => "Country Code #2", "label" => "Country Code #2", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "name", "placeholder" => "Country Name", "label" => "Country Name", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "localname", "placeholder" => "Country Local Name", "label" => "Country Local Name", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "continent", "placeholder" => "Country Continent", "label" => "Country Continent", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "region", "placeholder" => "Country Region", "label" => "Country Region", "rules" => ["required" => "required"]],
        ["type" => "number", "name" => "indepyear", "placeholder" => "Country Independence Year", "label" => "Country Independence Year", "rules" => ["required" => "required"]],
        ["type" => "number", "name" => "surfacearea", "placeholder" => "Country Surface Area", "label" => "Country Surface Area", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "governmentform", "placeholder" => "Country Government Form", "label" => "Country Government Form", "rules" => ["required" => "required"]],

    ];
    $keys = array_keys($country);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $country[$v["name"]];
        }
        //mm2849
        //7/27/2024
    }
}
// TODO Handle Manual Create Trip
?>
<div class="container-fluid">
    <h3>Edit Country</h3>
    <form method="POST">
        <?php foreach ($form as $k => $v) {

            render_input($v);
        } ?>
        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Update"]); ?>
    </form>
</div>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>
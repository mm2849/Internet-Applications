<?php
//mm2849
//07/27/2024
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>


<?php

// TODO Handle Country Fetch
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $name = strtoupper(se($_POST, "name", "", false));
    $quote = [];

//mm2849
//07/27/2024
    if ($name) {
        if ($action === "search") {
            $result = search_name($name);
            error_log("Data from API" . var_export($result, true));
            if ($result) {
                $quote = $result;
                $quote = $quote[0];
                $quote["is_api"] = 1;
                error_log("Data from API" . var_export($result, true));
            }
        } else if ($action === "create") {
            //$_POST = [$_POST][0];         
            foreach ($_POST as $k => $v) {
                if (!in_array($k, ["code", "code2", "name", "localname", "continent", "region", "indepyear", "surfacearea", "governmentform"])) {
                    $_POST["is_api"] = 0;
                    unset($_POST[$k]);
                }
                $quote = [$_POST];
                $quote = $quote[0];
                error_log("Cleaned up POST: " . var_export($quote, true));
            }
        }
    } else {
        flash("You must provide a name", "warning");
    }


    //Inserting Database & API Data
    $db = getDB();
    $query = "INSERT INTO `Countries` ";
    $columns = [];
    $params = [];
    //per record
    //$quote = $quote[0];
    //$quote["is_api"] = 1;
    //$_POST["is_api"] = 0;
    foreach ($quote as $k => $v) {
        array_push($columns, "`$k`");
        $params[":$k"] = $v;
    }
    $query .= "(" . join(",", $columns) . ")";
    $query .= "VALUES (" . join(",", array_keys($params)) . ")";
    error_log("Query: " . $query);
    error_log("Params: " . var_export($params, true));
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Inserted record" . $db->lastInsertId(), "success");
    } catch (PDOException $e) {
        //mm2849
        //07/27/2024
        if ($e->errorInfo[1] == 1062) {
            flash("A country with this name already exist, please try another or edit it", "warning");
        } else {
            error_log("Something broke with the query" . var_export($e, true));
            flash("An error occurred", "danger");
        }
    }
}

// TODO Handle Manual Create Trip
?>
<div class="container-fluid">
    <h3>Plan a Trip or Search for a Country</h3>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link bg-secondary" href="#" onclick="switchTab('create')">Search</a>
        </li>
        <li class="nav-item">
            <a class="nav-link bg-secondary" href="#" onclick="switchTab('search')">Create</a>
        </li>
    </ul>
    <div id="search" class="tab-target">
        <form method="POST">
            <?php render_input(["type" => "search", "name" => "name", "placeholder" => "Country Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "hidden", "name" => "action", "value" => "search"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit",]); ?>
        </form>
    </div>
    <!-- mm2849
        //07/27/2024 -->
    <div id="create" style="display: none;" class="tab-target">
        <form method="POST">
            <?php render_input(["type" => "text", "name" => "code", "placeholder" => "Country Code", "label" => "Country Code", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "code2", "placeholder" => "Country Code #2", "label" => "Country Code #2", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "name", "placeholder" => "Country Name", "label" => "Country Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "localname", "placeholder" => "Country Local Name", "label" => "Country Local Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "continent", "placeholder" => "Country Continent", "label" => "Country Continent", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "region", "placeholder" => "Country Region", "label" => "Country Region", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "number", "name" => "indepyear", "placeholder" => "Country Independence Year", "label" => "Country Independence Year", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "number", "name" => "surfacearea", "placeholder" => "Country Surface Area", "label" => "Country Surface Area", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "governmentform", "placeholder" => "Country Government Form", "label" => "Country Government Form", "rules" => ["required" => "required"]]); ?>

            <?php render_input(["type" => "hidden", "name" => "action", "value" => "create"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit", "text" => "Create"]); ?>
        </form>
    </div>
</div>

<script>
    function switchTab(tab) {
        let target = document.getElementById(tab);
        if (target) {
            let eles = document.getElementsByClassName("tab-target");
            for (let ele of eles) {
                ele.style.display = (ele.id === tab) ? "none" : "block";
            }
        }
    }
</script>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>
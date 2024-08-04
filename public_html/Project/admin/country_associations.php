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


//Building Search Form
$form = [
    ["type" => "text", "name" => "username", "placeholder" => "User Name", "label" => "User Name"],

    ["type" => "text", "name" => "name", "placeholder" => "Country Name", "label" => "Country Name"],

    ["type" => "text", "name" => "localname", "placeholder" => "Country Local Name", "label" => "Country Local Name"],

    ["type" => "text", "name" => "continent", "placeholder" => "Country Continent", "label" => "Country Continent"],

    ["type" => "number", "name" => "limit", "label" => "Limit", "Value" => "10", "include_margin" => false],



];

$total_records = get_total_count("`Countries` b
JOIN `UserCountries` ub ON b.id = ub.country_id");

//mm2849
//07/27/2024
$query = "SELECT u.username, b.id, name , code, code2, name, localname, continent, region, indepyear, surfacearea, governmentform, is_api, user_id FROM `Countries` b
JOIN `UserCountries` ub ON b.id = ub.country_id JOIN Users u on u.id = ub.user_id";
$params = [];
if (count($_GET) > 0) {
    $keys = array_keys($_GET);
    //redirect($session_key);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $_GET[$v["name"]];
        }
    }

    //username
    $username = se($_GET, "username", "", false);
    if (!empty($username)) {
        $query .= " AND u.username like :username";
        $params[":username"] = "%$username%";
    }

    $name = se($_GET, "name", "", false);
    if (!empty($name)) {
        $query .= " AND name like :name";
        $params[":name"] = "%$name%";
    }
    $localname = se($_GET, "localname", "", false);
    if (!empty($localname)) {
        $query .= " AND localname like :localname";
        $params[":localname"] = "%$localname%";
    }
    $continent = se($_GET, "continent", "", false);
    if (!empty($continent)) {
        $query .= " AND continent like :continent";
        $params[":continent"] = "%$continent%";
    }
    //mm2849
    //07/27/2024

    $sort = se($_GET, "sort", "indepyear", false);
    if (!in_array($sort, ["name", "localname", "continent"])) {
        $sort = "indepyear";
    }

    if ($sort === "created" || $sort === "modified") {
        $sort = "b." . $sort;
    }

    $order = se($_GET, "order", "desc", false);
    if (!in_array($order, ["asc", "desc"])) {
        $order = "desc";
    }

    $query .= " ORDER BY $sort $order";
    try {
        $limit = (int)se($_GET, "limit", "10", false);
    } catch (Exception $e) {
        $limit = 10;
    }
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
    }
    $query .= " LIMIT $limit";
}

//mm2849
//7/27/2024
$db = getDB();
$stmt = $db->prepare($query);
$results = [];
try {
    $stmt->execute($params);
    $r = $stmt->fetchAll();
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    error_log("Error searching for country " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

$table = ["data" => $results,"delete_url" => get_url("admin/delete_country.php?id=")];


?>



<div class="container-fluid">
    <h3>Associated Countries</h3>
    <form method="GET">
        <div class="row mb-3" style="align-items: flex-end;">

            <?php foreach ($form as $k => $v) : ?>
                <div class="col">
                    <?php render_input($v); ?>
                </div>
            <?php endforeach; ?>

        </div>
        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Filter"]); ?>
        <a href="?clear" class="btn btn-secondary">Clear</a>
    </form>
    <?php render_table($table); ?>
    <?php render_result_counts(count($results), $total_records); ?>
    <div class="row w-100 row-cols-auto row-cols-sm-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5 g-4">
        <?php foreach ($results as $country) : ?>
            <div class="col">
                <?php render_country_card($country); ?>
            </div>
        <?php endforeach; ?>
        <?php if (count($results) === 0) : ?>
            <div class="cols">
                No Results Found
            </div>
        <?php endif; ?>
    </div>
</div>


<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>
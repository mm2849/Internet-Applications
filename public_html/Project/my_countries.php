<?php
//mm2849
//07/27/2024
//note we need to go up 1 more directory
require(__DIR__ . "/../../partials/nav.php");

$db = getDB();
//Removing All Associations

if (isset($_GET["remove"])){
    $query = "DELETE FROM `UserCountries` WHERE user_id = :user_id";
    try{
        $stmt = $db->prepare($query);
        $stmt->execute([":user_id" => get_user_id()]);
        flash("Successfully removed all users countries", "success");
    } catch (PDOException $e){
        error_log("Error removing country association: " . var_export($e, true));
        flash("Error removing country associations", "danger");
    }
    redirect("my_countries.php");
}

//Building Search Form
$form = [

    ["type" => "text", "name" => "name", "placeholder" => "Country Name", "label" => "Country Name"],

    ["type" => "text", "name" => "localname", "placeholder" => "Country Local Name", "label" => "Country Local Name"],

    ["type" => "text", "name" => "continent", "placeholder" => "Country Continent", "label" => "Country Continent"],

    ["type" => "number", "name" => "limit", "label" => "Limit", "Value" => "10", "include_margin" => false],



];

$total_records = get_total_count("`Countries` b
JOIN `UserCountries` ub ON b.id = ub.country_id
WHERE user_id = :user_id", [":user_id" => get_user_id()]);

//mm2849
//07/27/2024
$query = "SELECT b.id, name , code, code2, name, localname, continent, region, indepyear, surfacearea, governmentform, is_api, user_id FROM `Countries` b
JOIN `UserCountries` ub ON b.id = ub.country_id
WHERE user_id = :user_id";
$params = [":user_id" => get_user_id()];
if (count($_GET) > 0) {
    $keys = array_keys($_GET);
    //redirect($session_key);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $_GET[$v["name"]];
        }
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

$table = ["data" => $results, "title" => "Countries", "ignored_columns" => ["id"], "edit_url" => get_url("admin/edit_country.php?id="), "delete_url" => get_url("admin/delete_country.php?id="), "view_url" => get_url("admin/view_country.php")];


?>



<div class="container-fluid">
    <h3>My Countries</h3>
    <div>
        <a href="?remove" onclick="!confirm('Are you sure you want to get rid of this country. Once removed you will have to buy it again')?event.preventDefault(): ''" class="btn btn-danger"> Remove all Purchased Countries</a>
    </div>
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
require_once(__DIR__ . "/../../partials/flash.php");
?>
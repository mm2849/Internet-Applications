<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    
}
?>

<?php
$id = se($_GET, "id", -1, false);


$country = [];
if ($id > -1) {
    //fetch
    $db = getDB();
    $query = "SELECT name , localname, continent, modified FROM `Countries` WHERE id = :id";
    //$query = "SELECT id, name , code, code2, name, localname, continent, region, indepyear, surfacearea, governmentform, is_api FROM `Countries` WHERE 1=1";
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
    
}
foreach ($country as $key => $value) {
    if (is_null($value)) {
        $country[$key] = "N/A";
    }
}
//TODO handle manual create stock
?>
<div class="container-fluid">
    <h3>Country: <?php se($country, "name", "Unknown"); ?></h3>
    <div>
        <a href="<?php echo get_url("admin/list_country.php"); ?>" class="btn btn-secondary">Back</a>
    </div>
    
    <?php render_country_card($country); ?>
</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>
<?php
//note we need to go up 1 more directory
//mm2849
//08/04/2024
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    //die(header("Location: $BASE_PATH" . "/home.php"));
    redirect("home.php");
}

//attempt to apply
if (isset($_POST["users"]) && isset($_POST["countries"])) {
    $user_ids = $_POST["users"]; //se() doesn't like arrays so we'll just do this
    $country_ids = $_POST["countries"]; //se() doesn't like arrays so we'll just do this
    if (empty($user_ids) || empty($country_ids)) {
        flash("Both users and countries need to be selected", "warning");
    } else {
        //for sake of simplicity, this will be a tad inefficient
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO UserCountries (user_id, country_id, is_active) VALUES (:uid, :cid, 1) 
        ON DUPLICATE KEY UPDATE is_active = !is_active");
        foreach ($user_ids as $uid) {
            foreach ($country_ids as $cid) {
                try {
                    $stmt->execute([":uid" => $uid, ":cid" => $cid]);
                    flash("Updated Country", "success");
                } catch (PDOException $e) {
                    flash(var_export($e->errorInfo, true), "danger");
                }
            }
        }
    }
}


//get active countries
$active_countries = [];
$db = getDB();
$stmt = $db->prepare("SELECT id, name, localname FROM Countries WHERE 1 LIMIT 25");
try {
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        $active_countries = $results;
    }
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
}


//search for user by username
$users = [];
$username = "";
if (isset($_POST["username"])) {
    $username = se($_POST, "username", "", false);
    if (!empty($username)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT Users.id, username, 
        (SELECT GROUP_CONCAT(name, ' (' , IF(ur.is_active = 1,'active','inactive') , ')') from 
        UserCountries ur JOIN Countries on ur.country_id = Countries.id WHERE ur.user_id = Users.id) as countries
        from Users WHERE username like :username");
        try {
            $stmt->execute([":username" => "%$username%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $users = $results;
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    } else {
        flash("Username must not be empty", "warning");
    }
}


?>
<div class="container-fluid">
    <h1>Assign Countries</h1>
    <form method="POST">
        <?php render_input(["type" => "search", "name" => "username", "placeholder" => "Username Search", "value" => $username]);/*lazy value to check if form submitted, not ideal*/ ?>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <form method="POST">
        <?php if (isset($username) && !empty($username)) : ?>
            <input type="hidden" name="username" value="<?php se($username, false); ?>" />
        <?php endif; ?>
        <table class="table">
            <thead>
                <th>Users</th>
                <th>Country to Assign</th>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <table class="table">
                            <?php foreach ($users as $user) : ?>
                                <tr>
                                    <td>
                                        <?php render_input(["type" => "checkbox", "id"=> "user_".se($user,'id', "", false), "name" => "users[]", "label" => se($user, "username", "", false), "value" => se($user, 'id', "", false)]); /*lazy value to check if form submitted, not ideal*/ ?>
                                    </td>
                                    <td><?php se($user, "countries", "No Countries"); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <td>
                        <?php foreach ($active_countries as $country) : ?>
                            <div>
                                <?php render_input(["type" => "checkbox", "id"=> "country_".se($country,'id', "", false), "name" => "countries[]", "label" => se($country, "name", "", false), "value" => se($country, 'id', "", false)]); /*lazy value to check if form submitted, not ideal*/ ?>
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php render_button(["text" => "Toggle Countries", "type" => "submit", "color" => "secondary"]); ?>
    </form>
</div>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>

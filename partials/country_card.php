<?php
if (!isset($country)) {
    error_log("Using country partial without data");
    flash("Dev Alert: country called without data", "danger");
}
?>
<?php if (isset($country)) : ?>
    <div class="card mx-auto" style="width: 18rem; margin-bottom: 1rem;">
        <?php if (isset($country["username"])) : ?>
            <div class="card-header">
                Owned By: <?php se($country, "username", "N/A"); ?>
            </div>
        <?php endif; ?>
        <div class="card-body">
            <h5 class="card-title"><?php se($country, "name", "Unknown"); ?> (<?php se($country, "id", "Unknown"); ?>)</h5>
            <div class="card-text">
                <ul class="list-group">
                    <li class="list-group-item">Name: <?php se($country, "name", "Unknown"); ?></li>
                    <li class="list-group-item">Local Name: <?php se($country, "localname", "Unknown"); ?></li>
                    <li class="list-group-item">Continent: <?php se($country, "continent", "Unknown"); ?></li>
                </ul>
            </div>

            <?php if (!isset($country["user_id"]) && isset($country["id"])) : ?>
                <div class="card-body">
                    <a href="<?php echo get_url('api/purchase_country.php?user_id=&country_id=' . $country["id"]); ?>" class="btn btn-primary btn-sm">Purchase Country</a>
                    <a href="<?php echo get_url('admin/view_country.php?id=' . $country["id"]); ?>" class="btn btn-secondary btn-sm">View</a>

                    
                </div>
                <?php else : ?>
                    <a href="<?php echo get_url("profile.php?id=" . $country["user_id"]); ?>"><?php se($country, "username", "N/A"); ?>'s Profile</a> 

            <?php endif; ?>

        </div>
    </div>
<?php endif; ?>
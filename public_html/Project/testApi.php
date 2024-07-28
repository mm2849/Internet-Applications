<?php
require(__DIR__ . "/../../partials/nav.php");

$result = [];
if (isset($_GET["name"])) {
    //function=GLOBAL_QUOTE&symbol=MSFT&datatype=json
    $data = ["mode" => "country", "name" => $_GET["name"]];
    $endpoint = "https://basic-country-city-information.p.rapidapi.com/?mode=country&code=DEU";
    $isRapidAPI = true;
    $rapidAPIHost = "basic-country-city-information.p.rapidapi.com";
    $result = get($endpoint, "COUNTRY_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    //example of cached data to save the quotas
    /*$result = ["status" => 200, "response" => '{
    "Global Quote": {
        ":code": "IND",
        ":code2": "IN",
        ":name": "India",
        ":localname": "Bharat/India",
        ":continent": "Asia",
        ":region": "Southern and Central Asia",
        ":indepyear": "1947",
        ":surfacearea": "3,287,263 km2",
        ":governmentform": "Federal Republic",
    }
}'];*/
    error_log("Response: " . var_export($result, true));
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }
    if (isset($result["country"])) {
        $quote = $result["country"];
        $quote = array_reduce(
            array_keys($quote),
            function ($temp, $key) use ($quote) {
                $k = explode(" ", $key)[0];
                
                $temp[$k] = str_replace('km2', '', $quote[$key]);
                return $temp;
            }
        );
        $result = [$quote];
        $db = getDB();
        $query = "INSERT INTO `Countries` ";
        $columns = [];
        $params = [];
        //per record
        foreach ($quote as $k => $v) {
            array_push($columns, "`$k`");
            $params[":$k"] = $v;
        }
        $query .= "(" . join(",", $columns) . ")";
        $query .= "VALUES (" . join(",", array_keys($params)) . ")";
        var_export($query);
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            flash("Inserted record", "success");
        } catch (PDOException $e) {
            error_log("Something broke with the query" . var_export($e, true));
            flash("An error occurred", "danger");
        }
    }
}
?>
<div class="container-fluid">
    <h1>Stock Info</h1>
    <p>Remember, we typically won't be frequently calling live data from our API, this is merely a quick sample. We'll want to cache data in our DB to save on API quota.</p>
    <form>
        <div>
            <label>Symbol</label>
            <input name="name" />
            <input type="submit" value="Fetch Stock" />
        </div>
    </form>
    <div class="row ">
        <?php if (isset($result)) : ?>
            <?php foreach ($result as $stock) : ?>
                <pre>
            <?php var_export($stock);
            ?>
            </pre>
                <table style="display: none">
                    <thead>
                        <?php foreach ($stock as $k => $v) : ?>
                            <td><?php se($k); ?></td>
                        <?php endforeach; ?>
                    </thead>
                    <tbody>
                        <tr>
                            <?php foreach ($stock as $k => $v) : ?>
                                <td><?php se($v); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php
require(__DIR__ . "/../../partials/flash.php");
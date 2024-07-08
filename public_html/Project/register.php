<?php
require_once(__DIR__ . "/../../partials/nav.php");
?>

<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required />
    </div>
    <div>
        <label for="username">Username</label>
        <input id="username" type="username" name="username" required maxlength="30" />
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" required minlength="8" />
    </div>
    <div>
        <label for="confirm">Confirm</label>
        <input type="password" name="confirm" required minlength="8" />
    </div>
    <input type="submit" value="Register" />
</form>
<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success

        /*function validationForm(){
            let x = document.forms["register.php"]["email"].value;
            if (x == ""){
                alert ("Wrong username of password entered");
                return false
            }
            else return true;
        }*/

        return true;
    }
</script>
<?php
 //TODO 2: add PHP Code

 if(isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["confirm"]) && isset($_POST["username"])){
    $email = se($_POST, "email","", false);   //$_POST["email"];
    $password = se($_POST, "password", "", false);   //$_POST["password"];
    $confirm = se($_POST, "confirm", "", false);   //$_POST["confirm"];
    $username = se($_POST, "username", "", false);   

    //TODO 3: validate/use

    $hasError = false;
    if (empty($email)){
        flash("Email must not be empty <br>");
        $hasError = true;
    }

    //sanitize
    //$email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $email = sanitize_email($email);

    //validate
    /*if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        flash("Invalid email address. Please Enter a valid Email");
        $hasError = true;
    }*/
    if(!is_valid_email($email)){
        flash("Invalid email address. Please Enter a valid Email <br>");
        $hasError = true;
    }
    if(!preg_match('/^[a-z0-9_-]{3,30}$/', $username)){
        flash("Username must be lowercase, alphanumerical, and can only contain _ or - and be between 3-30 characters", "danger");
        $hasError = true;
    }

    if (empty($password)){
        flash("password must not be empty <br>");
        $hasError = true;
    }
    if (empty($confirm)){
        flash("Confirm password must not be empty <br>");
        $hasError = true;
    }
    if (strlen($password) < 8){
        flash("Password must be atleast 8 characters long <br>");
        $hasError = true;
    }
    if (strlen($password) > 0 && $password !== $confirm) {
        flash("Passwords must match <br>");
        $hasError = true;
    }
    if (!$hasError){
        //flash("Welcome, $email");
        //TODO 4
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Users (email, password, username) VALUES(:email, :password, :username)");
        try{
            $stmt->execute([":email" => $email, ":password" => $hash, ":username" => $username]);
            flash("Successfully registered!");
        } catch (Exception $e) {
            flash("There was a problem registering");
            flash("<pre>" . var_export($e, true) . "</pre>");
        }
    }
 }
?>
<?php require(__DIR__ . "/../../partials/flash.php");
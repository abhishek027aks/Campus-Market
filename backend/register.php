<?php

echo "REGISTER.PHP WORKING";

echo "<pre>";
print_r($_POST);
echo "</pre>";

include "config.php";

if(isset($_POST['register'])){

    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "INSERT INTO users(fullname,email,password)
            VALUES('$fullname','$email','$password')";

    if(mysqli_query($conn,$sql)){
        echo " Registration Successful";
    }else{
        echo mysqli_error($conn);
    }
}

?>
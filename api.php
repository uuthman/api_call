<?php
/**
 * Created by PhpStorm.
 * User: AYINDE
 * Date: 08/12/2018
 * Time: 10:45
 */

require_once "database.php";

$response = array();

if (isset($_GET['access'])){

    switch ($_GET['access']){

        case 'register':
            if (isParameterAvailable(array('first_name','last_name','email','phone_number',
                'password'))){

                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $email = $_POST['email'];
                $phone_number = $_POST['phone_number'];
                $password = $_POST['password'];
                $user_type = "normal";
                $user_status = "1";

                $stmt = $pdo ->prepare("select id from user_entity where email = :email");
                $stmt->bindParam(":email",$email);
                $stmt->execute();
                $count = $stmt->rowCount();

                if (!filter_var($email)){

                    $response['error'] = true;
                    $response['message'] = "invalid email format";
                }else{
                    if ($count > 0){
                        $response['error'] = true;
                        $response['message'] = "email already exists";
                    }else{

                        $first_name = checkInput($first_name);
                        $last_name = checkInput($last_name);
                        $email = checkInput($email);
                        $phone_number = checkInput($phone_number);
                        $password = checkInput($password);

                        $encrypt_password = password_hash($password, PASSWORD_BCRYPT);

                        $stmt = $pdo -> prepare("insert into user_entity(firstName, lastName, email, phoneNumber, password, registrationDate, userStatus, userType) values (
                                                          :first_name,:last_name,:email,:phone_number,:password,now(),:user_type,:user_status
                                                         )");

                        $stmt -> bindParam(":first_name",$first_name);
                        $stmt -> bindParam(":last_name",$last_name);
                        $stmt -> bindParam(":email",$email);
                        $stmt -> bindParam(":phone_number",$phone_number);
                        $stmt -> bindParam(":password", $encrypt_password);
                        $stmt -> bindParam(":user_type",$user_type);
                        $stmt -> bindParam(":user_status",$user_status);

                        if ($stmt -> execute()){

                            $stmt = $pdo -> prepare("select * from user_entity where email = :email");
                            $stmt -> bindParam(":email",$email);
                            $stmt -> execute();
                            $result = $stmt ->fetch(PDO::FETCH_ASSOC);

                            $user = array(
                                'id' => $result['id'],
                                'first_name' => $first_name,
                                'last_name' => $last_name,
                                'email' => $email,
                                'password' => $result['password'],
                                'registration_date' => $result['registrationDate'],
                                'user_type' => $user_type,
                                'user_status' => $user_status

                            );

                            $response['error'] = false;
                            $response['message'] = 'User registered successfully';
                            $response['user'] = $user;
                        }else{
                            $response['error'] = false;
                            $response['message'] = 'Error occurred while registering user';
                        }
                    }
                }
            }else{
                $response['error'] = true;
                $response['message'] = 'required parameters are not available';
            }
            break;
        case 'login':
            if (isParameterAvailable(array('email','password'))){
                $email = $_POST['email'];
                $password = $_POST['password'];

                $stmt = $pdo -> prepare("select * from user_entity where email = :email");
                $stmt-> bindParam(':email',$email);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $count = $stmt->rowCount();
                $verify_password = $row['password'];

                if ($count > 0 && password_verify($password,$verify_password)){

                    $user = array(
                        'id' => $row['id'],
                        'first_name' => $row['firstName'],
                        'last_name' => $row['lastName'],
                        'email' => $email,
                        'phone_number' => $row['phoneNumber'],
                        'password' => $verify_password
                    );

                    $response['error'] = false;
                    $response['message'] = 'Login successful';
                    $response['user'] = $user;
                }else{
                    $response['error'] = false;
                    $response['message'] = 'Invalid username or password';
                }
            }else{
                $response['error'] = true;
                $response['message'] = 'required parameters are not available';
            }
    }
}else{
    $response['error'] = true;
    $response['message'] = "Invalid Api Call";
}

echo json_encode($response);

function isParameterAvailable($params){

    foreach ($params as $param) {
        if (!isset($_POST[$param])) {
            return false;
        }
    }
    return true;
}

function checkInput($var){
    $var = htmlspecialchars($var);
    $var = trim($var);
    $var = stripcslashes($var);
    return $var;
}


<?php
include("myfunctions.php");
if(isset($_POST['loginEmail']) && isset($_POST['loginPass']) && isset($_POST['action'])){
    $user = strip_tags($_POST['loginEmail']);
    $password = $_POST['loginPass'];
    $action = strip_tags($_POST['action']);

    if(checkSpelling($user, $password)){
        if($action == 'login')
            login($user, $password);
        else if($action == 'signup')
            signup($user, $password);
        exit;
    }
    else{
        if($action == 'login'){
            header('HTTP/1.1 401 Unauthorized');
            header('Location: login.php?err=unauth');
        }
        else if($action == 'signup'){
            header('Location: signup.php?err');
        }
        exit;
    }
}

header('HTTP/1.1 401 Unauthorized');
header('Location: login.php?err=unauth');
exit;


<?php
include("myfunctions.php");
if(isset($_POST['user'])){
    $user = strip_tags($_POST['user']);
    deleteReservation($user);
    exit;
}

header('Location: reservedarea.php?err');
exit;


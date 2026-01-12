<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

if (!empty($_SESSION["user_id"]) && !empty($_SESSION["username"])) {
  echo json_encode(["loggedIn" => true, "username" => $_SESSION["username"]]);
} else {
  echo json_encode(["loggedIn" => false]);
}

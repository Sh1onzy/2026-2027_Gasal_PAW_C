<?php 
$servername = "localhost";
$username = "root";
$password = "root123";//kalau error kosongi 
$db="KampusDB";

try {
	$conn = mysqli_connect($servername,$username,$password,$db);
} catch (Exception $e) {
	die("Koneksi gagal ". $e->getMessage());
}
 ?>

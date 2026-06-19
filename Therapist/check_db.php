<?php
require 'connection.php';
$res = $conn->query('DESCRIBE messages');
while($row = $res->fetch_assoc()) {
    print_r($row);
}

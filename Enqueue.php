<?php
require 'Queue.php';
require 'SendEmailJob.php';

$pdo = new PDO("mysql:host=localhost;dbname=test", "root", "852456"); // change credentials
$queue = new Queue($pdo);

$job = new SendEmailJob("user@example.com", "Welcome!");
$queue->push($job , 'test1',1);

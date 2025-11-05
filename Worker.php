<?php
require 'Queue.php';
require 'SendEmailJob.php';
require 'Args.php';
require 'DB.php';


$pdo = new PDO("mysql:host=localhost;dbname=test", "root", "852456");
$queue = new Queue($pdo);
$db = new DB($pdo);

$countExisted = $db->count(); 

$args = parseCommandArgs($argv);
$retry     = $args['retry']     ?? 0;
$id        = $args['id']     ?? null;
$runEvery  = $args['run-every'] ?? 1;
$queueName = $args['queue-name'] ?? 'default';
$stopWhenEmpty = $args['stop-when-empty'] ?? false;
 
// run job with id
if ($retry && $id) {
    $jobData = $db->get($id);
    if ($jobData) {
        $job = unserialize(base64_decode($jobData['payload']));
        try {
            $job->handle();
            $queue->delete($jobData['id']);
            echo "Processed job #{$jobData['id']}\n";
        } catch (Exception $e) {
            $queue->markJobAsFailed($jobData['id']);
        }
    } else {
        print("Job not found!");
    } 
    return;
}


/**
 * This is one of main part of application it run a infinit loop
 * to get jobs from storage(db,redis,...) and run it 
 */
while (true) {
    if ($stopWhenEmpty && $countExisted == 0) {
        break;
    }

    if (isset($queueName)) {
        $jobData = $queue->pop($queueName);
    } else {
        $jobData = $queue->pop('default');
    }
    
    if ($jobData) {
        $job = unserialize(base64_decode($jobData['payload']));
        try {
            $job->handle();
            $queue->delete($jobData['id']);
            echo "Processed job #{$jobData['id']}\n";
        } catch (Exception $e) {
            $queue->markJobAsFailed($jobData['id']);
        }
    } 
    sleep($runEvery ?? 1);
}
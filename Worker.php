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
$tries     = $args['tries']     ?? 1;
$id        = $args['id']     ?? null;
$runEvery  = $args['run-every'] ?? 1;
$queueName = $args['queue-name'] ?? 'default';
$stopWhenEmpty = $args['stop-when-empty'] ?? false;
 
// run job with id
if ($tries && $id) {
    $jobData = $db->get($id);

    if (!$jobData) {
        echo "Job not found!\n";
        return;
    }

    $job = unserialize(base64_decode($jobData['payload']));

    $attempt = 0;
    $success = false;

    while ($attempt < $tries && !$success) {
        try {
            run($job, $queue, $jobData);
            $success = true;  
        } catch (Exception $e) {
            $attempt++;
            echo "Attempt {$attempt} failed: {$e->getMessage()}\n";
 
            if ($attempt >= $tries) {
                $queue->markJobAsFailed($jobData['id']);
                echo "Job #{$jobData['id']} permanently failed after {$tries} tries.\n";
            } else { 
                sleep(1);
            }
        }
    } 
    return;
}

function run($job, $queue, $jobData) {
    $job->handle();
    $queue->delete($jobData['id']);
    echo "Processed job #{$jobData['id']}\n";
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
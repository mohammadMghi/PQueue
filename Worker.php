<?php
require 'Queue.php';
require 'SendEmailJob.php';
require 'Args.php';


$pdo = new PDO("mysql:host=localhost;dbname=test", "root", "852456");
$queue = new Queue($pdo);
 

$args = parseCommandArgs($argv);
$retry     = $args['retry']     ?? 0;
$runEvery  = $args['run-every'] ?? 1;
$queueName = $args['queue-name'] ?? 'default';
 
 

while (true) {
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
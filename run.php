<?php
// ini_set("display_errors", "ON");
// ini_set("error_reporting", E_ALL);

require 'ImportTwitter.php';
require 'ImportLinkedin.php';
require 'Reader.php';
require 'Model.php';

$dbConfig = include 'config.php';

// db model
$model = new Model($dbConfig['host'], $dbConfig['dbname'], $dbConfig['user'], $dbConfig['password'], $dbConfig['port']);

// import to db from json
$json = new ImportTwitter();
$readerTwitter = new Reader($json);
$readerTwitter->setTable("twitter")
    ->setFile("./twitter.sample.json")
    ->setModel($model)
    ->Run();
unset($json, $readerTwitter);

// import to db from csv
$csv = new ImportLinkedin();
$readerLinkedin = new Reader($csv);
$readerLinkedin->setTable("linkedin")
    ->setFile("./Linkedin.sample.Leads.csv")
    ->setModel($model)
    ->Run();
unset($csv, $readerLinkedin);


// set relationship
echo "[info]set relationship start".PHP_EOL;

$maxID = $model->getInstance()->max("linkedin", "id");
$id = 0;

while ($id < $maxID) {
    $pageNum = 100;
    $rows = $model->getInstance()->select("linkedin", ['first_name', 'last_name', 'id', 'email'], [
        "id[>]"=> $id, 
        "LIMIT" => [0, $pageNum],
        "ORDER"=>["id" => "ASC"]
    ]);
    
    if (count($rows) < 1) {
        break;
    }

    foreach ($rows as $row) {
        $id = $row['id'];
        $data = handleRow($row, $model);
        // insert relationship
        if (count($data) > 0) {
            $model->batchInsert("relationship", $data);
            echo '[info]'.\json_encode($data).PHP_EOL;
        }
    }
}

// handle row
function handleRow($row, $model) {
    $data = [];

    // search by email
    if ($row['email']) {
        $list = $model->getInstance()->select("twitter", ['id', 'email'], [
            'email'=> $row['email'], 
        ]);

        if (count($list) > 0) {
            foreach ($list as $value) {
                $data[] = [
                    'twitter_id' => $value['id'],
                    'linkedin_id' => $row['id'],
                    'first_name'=> $row['first_name'], 
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                ];

                echo '[info]'.\json_encode($data).PHP_EOL;
            }
        }
    }

    // search by name
    if ($row['first_name'] && $row['last_name']) {
        $list = $model->getInstance()->select("twitter", ['id', 'email'], [
            'name_md5'=> md5($row['first_name']."-".$row['last_name']), 
        ]);
        
        if (count($list) > 0) {
            foreach ($list as $value) {
                $data[] = [
                    'twitter_id' => $value['id'],
                    'linkedin_id' => $row['id'],
                    'first_name'=> $row['first_name'], 
                    'last_name' => $row['last_name'],
                    'email' => $row['email']?? $value['email'],
                ];
            }
        }
    }

    return $data;
}


echo "[info]set relationship end".PHP_EOL;
echo "[info]run successfully".PHP_EOL;
<?php

require_once 'ImportInterface.php';

class ImportTwitter implements ImportInterface 
{
    public $tableName;

    public $file;

    public $model;

    public function serialize($row)
    {
        $rowWithoutUnicode = preg_replace("/\\\u[a-z0-9]{4}/i", "", $row);
        $dataWithoutUnicode = \json_decode($rowWithoutUnicode, true);
        // json_decode will transform unicode strings
        $data = \json_decode($row, true);

        $boolfields = ["protected", "verified", "is_translator", "default_profile_image"];
        $intfields = ["id", "followers_count", "friends_count", "listed_count", "favourites_count", "statuses_count"];
        $charfields = ["name", "screen_name", "location", "url", "description", "profile_image_url_https", "profile_banner_url", "translator_type", "phone", "email"];
        $timeFields = ["created_at"];
        $fields = array_merge($boolfields, $intfields, $charfields, $timeFields);

        foreach ($fields as $key) {
            if (!isset($data[$key])) {
                $data[$key] = "";
            }
            if (in_array($key, $timeFields)) {
                $data[$key] = date("Y-m-d H:i:s", strtotime($data[$key]));
            } elseif (in_array($key, $boolfields)) {
                $data[$key] = $data[$key] ? 1 : 0;
            } elseif (in_array($key, $intfields)) {
                $data[$key] = intval($data[$key]);
            } else {
                $data[$key] = trim($data[$key]);
            }
        }

        // list($data['first_name'], $data['last_name']) = $this->serializeName($dataWithoutUnicode['name']??'');
        $data['name_md5'] = $this->md5Name($dataWithoutUnicode['name']??'');
        return $data;
    }

    // it is convenient for subsequent query if parse first_name and last_name, and md5
    // 考虑后续关联关系，提前解析出 first_name,last_name，做 md5 加密方便查询，加索引
    private function md5Name($name)
    {
        $name = trim($name);
        if (!$name) {
            return "";
        }
        $nameString = str_replace(["(", ")", "[", "]"], "", $name);
        $nameArr = explode(" ", $nameString);
        if (count($nameArr) < 2) {
            return "";
        }
        return md5($nameArr[0]."-".$nameArr[1]);
    }

    // it is convenient for subsequent query if parse first_name and last_name
    // 考虑后续关联关系，提前解析出 first_name,last_name，方便查询
    // to md5Name
    /**
    private function serializeName($name)
    {
        $name = trim($name);
        if (!$name) {
            return ["", ""];
        }
        $nameString = str_replace(["(", ")", "[", "]"], "", $name);
        $nameArr = explode(" ", $nameString);
        if (count($nameArr) < 1) {
            return ["", ""];
        }
        $firstName = $nameArr[0];
        $lastName = "";
        if (count($nameArr) > 1) {
            $lastName = $nameArr[count($nameArr)-1];
        }
        return [$firstName, $lastName];
    }
    */

    public function ReadAndSave() 
    {
        echo "[info]ImportTwitter start".PHP_EOL;
        $handle = fopen($this->file,"r");
        $i = 0;
        $successNum = $unlessNum = $repeatedNum = 0;
        $idStr = '|';

        // batch insert to the number of sql queries (num could modify)
        // 批量插入，减少 sql 次数， num 根据业务情况设置
        $num = 100;
        $chunk = [];

        while(!feof($handle))
        {
            $i++;
            $row = trim(fgets($handle));
            if (strlen($row) < 2) {
                $unlessNum ++;
                echo \json_encode([
                    "level" => "[error]",
                    "function" => "ImportTwitter ReadAndSave",
                    "message" => "data error lines ".$i.", too short",
                ]).PHP_EOL;
                continue;
            }
            if (substr($row, -1) !== "}") {
                $unlessNum ++;
                echo \json_encode([
                    "level" => "[error]",
                    "function" => "ImportTwitter ReadAndSave",
                    "message" => "data error lines ".$i.", is not a valid json string",
                ]).PHP_EOL;
                continue;
            }

            $successNum ++;
            try {
                $data = $this->serialize($row);
            } catch (\Throwable $t) {
                echo \json_encode([
                    "level" => "[error]",
                    "function" => "Model batchInsert",
                    "message" => $t->getMessage(),
                    "lines" => $t->getLine(),
                ]).PHP_EOL;
                continue;
            }
            
            if (strpos($idStr, '|'.$data['id'].'|') !== false) {
                $repeatedNum ++;
                continue;
            }
            $idStr .= $data['id'].'|';
            array_push($chunk, $data);
            if ($i%$num == 0) {
                $this->model->batchInsert("twitter", $chunk);
                $chunk = [];
            }
        }

        unset($chunk);
        fclose($handle);
        echo "[info]ImportTwitter ReadAndSave end, read lines:$i, success lines:$successNum, unless lines:$unlessNum".PHP_EOL;
    }
}


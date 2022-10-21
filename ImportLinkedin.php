<?php

require_once 'ImportInterface.php';

class ImportLinkedin implements ImportInterface 
{
    public $tableName;

    public $file;

    public $model;

    public function serialize($data)
    {
        $companySizeArr = explode("-", trim($data[11]));
        $companySize = 0;
        switch ($companySizeArr[0]) {
            case '1':
                $companySize = 1;
                break;
            case '11':
                $companySize = 2;
                break;
            case '51':
                $companySize = 3;
                break;
            // maybe more ...
        }
        return [
            'company' => trim($data[0]),
            'website' => trim($data[1]),
            'company_linkedin' => trim($data[2]),
            'address' => trim($data[3]),
            'zip' => intval(trim($data[4])),
            'first_name' => trim($data[5]),
            'last_name' => trim($data[6]),
            'title' => trim($data[7]),
            'email' => trim($data[8]),
            'linkedin_profile' => trim($data[9]),
            'industry_type' => trim($data[10]),
            'company_size' => $companySize,
        ];
    }

    public function ReadAndSave()
    {
        if (($handle = fopen($this->file, "r")) !== FALSE) {
            $i = 0;
            // batch insert to the number of sql queries (num could modify)
            // 批量插入，减少 sql 次数， num 根据业务情况设置
            $num = 100;
            $chunk = [];

            echo "[info]start ImportLinkedin".PHP_EOL;

            while (($data = fgetcsv($handle, 10240, ",")) !== FALSE) {
                $i++;
                if ($i == 1) {
                    continue;
                }
                $data = $this->serialize($data);
    
                array_push($chunk, $data);
                if ($i%$num == 0) {
                    $this->model->batchInsert("linkedin", $chunk);
                    echo "[info]ImportLinkedin handled lines ".$i.PHP_EOL;
                    $chunk = [];
                }
            }
            unset($chunk);
            fclose($handle);
            echo "[info]ImportLinkedin import successfully".PHP_EOL;
        } else {
            echo \json_encode([
                "level" => "[error]",
                "function" => "ImportLinkedin ReadAndSave",
                "message" => "ImportLinkedin import, open file $this->file error".PHP_EOL,
            ]).PHP_EOL;
            die();
        }
    }
}
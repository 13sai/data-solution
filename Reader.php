<?php
class Reader 
{
    private $strategy;

    public function __construct(ImportInterface $strategy)
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function setTable($tableName)
    {
        $this->strategy->tableName = $tableName;
        return $this;
    }

    public function setFile($file)
    {
        $this->strategy->file = $file;
        return $this;
    }

    public function setModel($model) 
    {
        $this->strategy->model = $model;
        return $this;
    }

    public function Run()
    {
        $this->strategy->ReadAndSave();
    }
}

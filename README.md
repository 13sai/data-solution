## README

## Your challenge/task

Import the data set and associate as much data as possible

There are two small datasets in this directory, please design the tables and import them to the database separately.

We assume that the two datasets are twitter and linkedin users, and we want to find the connection between them.

## Task Specifications

*  Statements for creating tables
*  Scripts for importing datasets
*  Script to find the connection
   -  Let's assume that people with the same name are the same person, and people with the same email are also the same person.
   -  When we associate the two datasets, we can find the company name of a person by his twitter account.
   -  You can create a new table to store this relationship, twitter account -> linkedin name
   -  Note that some people may have a middle name, we assume that if the first and last names match, it is the same person.

## Notes
* Please consider indexes when designing tables, as this is a small dataset, and in the real world, we may be running on files with millions of rows
* Please use `php` or `python` to write the script
* Please use`mysql` as database
* Don't worry if you can't find an associated person, since this is a small test dataset and we are only focusing on the code side.

----------------------------------------------------------------

### how to run

1. 新建数据库 `testdata`，导入data.sql。
2. 修改 config.php 中 db 配置参数
```php
return [
   "dbname"   => "testdata",
   "host"     => "127.0.0.1",
   "user"     => "root",
   "password" => "123456",
   "port"     => 3306,
];
```
3. 执行 
   
> php run.php > run.log


### SQL

1. 字段依据数据源字段定义，字段含emoji等，建议使用 utf8mb4
2. 增加字段: 
   imported_at 导入数据的时间
3. 调整字段：
   company_size 因为比较固定，定义位了tinyint，转化为1: 1-10 employees , 2: 11-50 employees, 3: 51...，以此类推。
4. 字段长度：
   可以根据数据源 twitter、linkedin 字段支持的最长长度去设置，first_name、last_name 等字段视情况应该能更小，大量数据情况能大大节省。我这里按数据与以前经验定义，对没有截断字段做了一定保证，使用了 varchar。
5. 主键：
   - 三张表都设置了 id 主键，主要考虑 InnoDB 使用的是聚簇索引，聚簇索引默认是主键，没有设置主键也会寻找一个唯一非空字段作为聚簇索引，如果都没有，自己隐式定义主键，所以习惯生成。
   - id 自增，顺序写性能会稍微好一点，另外以防有多对多关系（当然了，目前数据集没有）
6. 普通索引：
   长度根据导入后的数据做了调整。 `SELECT count(distinct left(email, 13)) / count(*) FROM twitter;`
   因为后续查询查的是 twitter，所以 linkedin 就没加索引
   因为测试 twitter 的first_name 和 last_name 区分度都不高，优化拼接加密name_md5，在拼接字段加索引
7. 关联表主要记录了两者id 还有重要的三个字段 email、first_name 和 last_name，使用 twitter account 可以直接获取 linkedin name
8. 扩容：
   没有太大的字段，百万级别暂不需要考虑分表，再大可以视情况考虑分表。
   简单的可以考虑把热点字段（email、first_name 和 last_name等）与非热点字段（比如location，description等）进行分表，具体热点字段视情况。
   根据 first_name 首字母划分也是个不错分表方案，relationship 也进行分表。
    
### document

因为是个数据集的导入，没有使用到 composer 或框架，仅引入了medoo，方便操作sql

- data.sql: statements for creating tables 
- ImportInterface.php: import interface
- ImportLinkedin.php: import data from csv 
- ImportTwitter.php: import data from json
- Medoo.php: lightweight db extend
- Model.php: db instance
- Reader.php: reading strategy
- run.php: run task, set relationship
- run.log: important log, format json, format text 


### code 

1. 因为后续要寻找关联，所以直接在写入时就解析 twitter 的 name，冗余字段 first_name、last_name
2. 需要注意把 unicode 要剔除，而 json_decode 会将 unicode 解析，不便于匹配，事先替换下 unicode，写数据库其他字段使用原数据，保证原数据不变， first_name、last_name 解析使用替换的数据，主要代码：
```php
$rowWithoutUnicode = preg_replace("/\\\u[a-z0-9]{4}/i", "", $row);
$dataWithoutUnicode = \json_decode($rowWithoutUnicode, true);
$data = \json_decode($row, true);
```
3. 解析 name 需要注意 middle name，所以解析只保留字符串头和尾（切割为数组取第一个和最后一个）
4. 解答过程中发现有 unicode 编码成的形似英文字母的 unicode，如 \u1d05\u1d00\u0274\u026a\u1d07\u029f \u1d0d\u026a\u1d07ss\u029f\u1d07\u0280 ，这个暂时没有想到比较好的方法，后续可优化




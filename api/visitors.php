<?php

// mysql parameters
try{
  include '../vendor/autoload.php';
  $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
  $my_env = dirname(__DIR__);
  $dotenv->load();
}
catch (exception $e){
  // can't load .env file if its in production
}


$host = $_SERVER['RDS_HOSTNAME'];
$user = $_SERVER['RDS_USERNAME'];
$password = $_SERVER['RDS_PASSWORD'];
$dbname = $_SERVER['RDS_DB_NAME'];
$table_name = $_SERVER['DATABASE_VISITORS_TABLE'];
$con = new mysqli($host, $user, $password,$dbname);

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$limit_sql = 100;
$sql = '';
if(!$con){
    die("Connection failed: " . mysqli_connect_error());
}

switch ($method){
    case 'GET':
        $sql = <<<SQL
        


        (select 'Browser' as ip_info, 0 as total
        from $table_name
        limit 1)
        union
        (select coalesce(browser,'No Browser') as ip_info, count(*) as total
        from $table_name
        where timestamp >= Date(now()) - interval 7 day group by browser limit $limit_sql)
        union
        (select 'Language' as ip_info, 0 as total
        from $table_name 
        limit 1)
        union
        (select coalesce(brow_language, 'No Language') as ip_info, count(*) as total 
        from $table_name
        where timestamp >= Date(now()) - interval 7 day group by brow_language limit $limit_sql)
        union 
        (select 'Country' as ip_info, 0 as total
        from $table_name
        limit 1)
        union
        (select coalesce(country, 'No Country') as ip_info, count(*) as total
        from $table_name
        where timestamp >= Date(now()) - interval 7 day group by country limit $limit_sql);
SQL;
        break;
    case 'POST':
        die("I only work with GET method. :p");  
        break;  
    
}
$result = $con->query($sql);
$my_array = array();
while ($obj = $result->fetch_object() ){
  $newRow = [
    'name' => $obj->ip_info,
    'total' => $obj->total

  ];
  array_push($my_array, $newRow);

}

// create json object
// https://stackoverflow.com/questions/3281354/how-to-create-a-json-object
$post_data = json_encode($my_array, JSON_FORCE_OBJECT);
$con->close();

echo $post_data
?>
<!-- <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" href="../public/favicon.ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vis itors</title>
  </head>
  <body>
    <div id="app"></div>
    <script src="https://cdn.jsdelivr.net/npm/vue@"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
  </body>
</html> -->
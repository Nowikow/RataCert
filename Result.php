<?php

 $IsTest = ($_REQUEST['IsTest'] == 1) ? true : false;

 $OutSum = $_REQUEST['OutSum'];
 $InvId = intval($_REQUEST['InvId']);
 $Shp_email = $_REQUEST['Shp_email'];
 $Shp_name = $_REQUEST['Shp_name'];
 $SignatureValue = $_REQUEST['SignatureValue'];
 $Culture = $_REQUEST['Culture'];

 $sert_code = get_new_code();
 $connect= mysqli_connect(
     "localhost",
     "root",
     '',
     "my_db"
 );

//Проверка кода на соответствие по БД

$stm = mysqli_query($connect,
    "SELECT code FROM orders WHERE code = '$sert_code'"
);

$check = mysqli_fetch_assoc($stm);

//Цикл перезаписи, если код существует

while ($check) {
    $sert_code=get_new_code();

    $stm = mysqli_query($connect,
        "SELECT code FROM orders WHERE code = '$sert_code'"
    );
    $check = mysqli_fetch_assoc($stm);

}

 $date = date("r");


if ($IsTest) {
     $pwd2 = "###";
 } else {
     $pwd2 = "###";
 }

// final Проверка подписи
 if (strtolower($SignatureValue) != strtolower(md5($OutSum . ":" . $InvId . ":" . $pwd2 . ":Shp_email=" . $Shp_email . ":Shp_name=" . $Shp_name))) {
     echo "ERR: invalid signature";
     exit();
 }

 // Подключение к БД для бинда
 $dbh = new \PDO(
     'mysql:host=localhost;dbname=my_db;',
     'root',
     ''
 );

//Проверка отправки лишних запросов от кассы
$checkId = mysqli_query($connect,
    "SELECT count(*) FROM orders WHERE id = '$InvId'"
);
$count = mysqli_fetch_row($checkId);
foreach ($count as $key) {
    if ($key>0) {
        exit();
    }
};

 $dbh->exec('SET NAMES UTF8');
 $stm = $dbh->prepare(
     'INSERT INTO orders (id, email, name, code, outsumm, date) 
                VALUES (:invid, :email, :name, :code, :outsumm, :date)');
 $stm->bindValue('invid', $InvId);
 $stm->bindValue('email', $Shp_email);
 $stm->bindValue('name', $Shp_name);
 $stm->bindValue('code', $sert_code);
 $stm->bindValue('outsumm', $OutSum);
$stm->bindValue('date', $date);
 $stm->execute();

 // final Отправка письма
 $headers = "MIME-Version: 1.0\r\n";
 $headers .= "Content-type: text/html; charset=UTF-8\r\n";
 mail(
     $Shp_email,
     "Ваш сертификат",
     '
                 <html lang="en-ru">
 <head>
 <meta charset="UTF-8">
     <style>
         div {
             width: 100%;
             text-align: center;
             display: flex;
             flex-direction: column;
             align-items: center;
         }
         header {
             width: 100%;
             background-color: black;
             display: flex;
             justify-content: center;
         }
         h1 {
             margin-top: 30px;
             width: 55%;
             color: white;
         }
 
         .sign {
             text-transform: uppercase;
             width: 35%;
             margin-top: 30px;
         }
 
         img {
             width: 100%;
         }
 
         footer {
             width: 100%;
             background-color: black;
             color: white;
             display: flex;
             flex-direction: column;
             align-items: center;
         }
 
         footer :nth-child(2) {
             padding: 10px 5px 10px 5px;
             background-color: white;
             width: 300px;
             color: black;
             font-size: 30pt;
 
         }
     </style>
 </head>
 <div>
     <header>
         <h1>Вы приобрели сертификат на рыцарские продукты 2020-2021 гг.</h1>
     </header>
 
 <p class="sign">Спасибо за доверие и поддержку нашего сообщества!</p>
     <img src="./Group%201.png">
 
 <footer>
     <p class="sign">Ваш индивидуальный номер сертификата</p>
     <span>' . $sert_code . '</span>
 </footer>
 
 </div>',
     $headers
 );

 // Генерация кода для сертификата
 function get_new_code()
 {
     $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
     $size = StrLen($chars) - 1;
     $code = null;
     $max = 16;
     while ($max--) {
         $code .= $chars[rand(0, $size)];
     }
     return $code;
 }

 exit();

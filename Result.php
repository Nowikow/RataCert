<?php

 $IsTest = ($_REQUEST['IsTest'] == 1) ? true : false;

 $OutSum = $_REQUEST['OutSum'];
 $InvId = intval($_REQUEST['InvId']);
 $Shp_email = $_REQUEST['Shp_email'];
 $Shp_name = $_REQUEST['Shp_name'];
 $SignatureValue = $_REQUEST['SignatureValue'];
 $Culture = $_REQUEST['Culture'];
 $date = date("r");
 $sert_code = get_new_code();
 
 //Конфигурация обычных запросов к БД
 $connect= mysqli_connect(
     "localhost",
     "root",
     '',
     "my_db"
 );

 // Подключение к БД PDO
 $dbh = new \PDO(
     'mysql:host=localhost;dbname=my_db;',
     'root',
     ''
 );

//Проверка наличия таблицы PDO
$dbh->exec(
    'CREATE TABLE IF NOT EXISTS orders (
        id INT NOT NULL AUTO_INCREMENT ,
        email VARCHAR(255) NOT NULL ,
        name VARCHAR(255) NOT NULL ,
        code VARCHAR(255) NOT NULL ,
        outsumm VARCHAR(255) NOT NULL ,
        date VARCHAR(255) NOT NULL ,
        PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
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

//Биндинг значений в БД PDO
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
        table {
            margin: auto;
            width: 600px;
            text-align: center;
        }
        
        .blackback {
            background-color: black;
            color: white;
        }
        
        img {
            width: 100%;
        }
        
        .headerMail {
            padding: 20px 0 20px 0;
            font-size: 14pt;
        }
        
        .thanksMail {
            padding: 10px 0 10px 0;
            font-size: 10pt;
        }
        
        .footerPad {
            padding-bottom: 20px;
        }
        
        p {
            padding-top: 10px;
        }
        
        span {
            font-size: 14pt;
            padding: 10px 10px 10px 10px;
            color: black;
            background-color: white;
        }
    </style>
</head>
<table>
    <tr class='blackback'>
        <td class='headerMail'>Вы приобрели сертификат на рыцарские продукты 2020-2021 гг.</td>
    </tr>
    <tr>
        <td class='thanksMail'>СПАСИБО ЗА ДОВЕРИЕ И ПОДДЕРЖКУ НАШЕГО СООБЩЕСТВА!</td>
    </tr>
    <tr>
        <td><img src="https://turnir.moscow/wp-content/uploads/slack-imgs.png" alt="PIC"></td>
    </tr>
    <tr class='blackback'>
        <td class="footerPad">
            <p>ВАШ ИНДИВИДУАЛЬНЫЙ НОМЕР СЕРТИФИКАТА</p>
            <span>' . $sert_code . '</span>
        </td>
    </tr>
</table>',
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

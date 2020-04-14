<?php

$pwd2='I5baCa9jtQ541WrpitLO';

// Генерация кода для сертификата

$chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";

$max=16;

$size=StrLen($chars)-1;

$code=null;

while($max--)
    $code.=$chars[rand(0,$size)];

//Проверка подписи
if ( strtolower($_POST['SignatureValue']) != strtolower(md5($_POST['OutSum'] . ":" . $pwd2)) ) {
    // не совпадает подпись
    echo "ERR: invalid signature";
    exit();
}

echo "OK";

//Работа с БД
$dbh = new \PDO(
    'mysql:host=localhost;dbname=my_db;',
    'root',
    ''
);

$dbh->exec('SET NAMES UTF8');

$stm = $dbh->prepare('INSERT INTO users (`email`, `name`, `code`) VALUES (:email, :name, :code)');
$stm->bindValue('email', $_POST['Shp_mail']);
$stm->bindValue('name', $_POST['Shp_name']);
$stm->bindValue('code', $code);
$stm->execute();


//Отправка письма
mail(
    $_POST['Shp_mail'],
    "Ваш сертификат",
    '    
                <html lang="en-ru">
                <head>
                <meta charset="UTF-8">
                </head>
                <div lang="en-ru">
                <h1>Вы приобрели сертификат на рыцарские продукты 2020-2021 гг.</h1>

                <p>Спасибо за доверие и поддержку нашего сообщества!</p>

                <p>Ваш индивидуальный номер сертификата</p>
    
                <p>Заглушка</p>
    
                </div>
              '
);

exit();
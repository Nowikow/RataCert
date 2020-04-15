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

        a {
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

<a href="https://ibb.co/brNDLbd"><img src="https://i.ibb.co/BgTFjns/Group-1.png" alt="Group-1" border="0"></a>

<footer>
    <p class="sign">Ваш индивидуальный номер сертификата</p>
    <span>' . $code  . '</span>
</footer>

</div>
'
);

exit();

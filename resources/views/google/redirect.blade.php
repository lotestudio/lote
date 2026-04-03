<!DOCTYPE html>
<html>
<head>
    <title>Drive API Quickstart</title>
    <meta charset="utf-8" />
</head>
<body>
<p>Drive API Quickstart</p>
<p>Redirected!</p>
{{var_dump($token)}}


{{--Как се ползва--}}
{{--За Google Drive API го пращаш като Authorization header:``` php--}}
{{--<?php--}}

{{--$client = new \Google\Client();--}}
{{--$client->setAccessToken($token);--}}

{{--$service = new \Google\Service\Drive($client);--}}

{{--$files = $service->files->listFiles([--}}
{{--    'pageSize' => 10,--}}
{{--    'fields' => 'files(id, name)',--}}
{{--]);--}}

{{--foreach ($files->getFiles() as $file) {--}}
{{--    echo $file->getName() . PHP_EOL;--}}
{{--}--}}
{{--```--}}

{{--Ако правиш заявка ръчно с HTTP, header-ът е:``` http--}}
{{--Authorization: Bearer YOUR_ACCESS_TOKEN--}}
{{--```--}}

{{-- --}}
{{--Трябва ли да го рефрешваш?--}}
{{--Да, ако искаш дълготрайна работа.--}}
{{--Важно:--}}
{{--access token обикновено е валиден около 1 час--}}
{{--след това изтича--}}
{{--ако си получил и refresh token, можеш да си вземеш нов access token автоматично--}}
{{--ако нямаш refresh token, трябва пак да минеш OAuth login flow--}}
{{-- --}}
{{--Какво трябва да запазиш--}}
{{--При fetchAccessTokenWithAuthCode($code) Google може да върне нещо като:``` php--}}
{{--[--}}
{{--'access_token' => '...',--}}
{{--'expires_in' => 3599,--}}
{{--'refresh_token' => '...',--}}
{{--'scope' => '...',--}}
{{--'token_type' => 'Bearer',--}}
{{--]--}}
{{--```--}}

{{--Препоръка:--}}
{{--Запази:--}}
{{--access_token--}}
{{--refresh_token ако има--}}
{{--expires_in / време на изтичане--}}
{{-- --}}
{{--Как да провериш дали е изтекъл--}}
{{--С Google client:``` php--}}
{{--if ($client->isAccessTokenExpired()) {--}}
{{--    $client->fetchAccessTokenWithRefreshToken($refreshToken);--}}
{{--}--}}
{{--```--}}
{{--## Типичен flow--}}
{{--### Първи login--}}
{{--1. User дава consent--}}
{{--2. Получаваш `code`--}}
{{--3. Разменяш го за token--}}
{{--4. Запазваш `refresh_token`--}}

{{--### После при следващи заявки--}}
{{--1. Зареждаш token-а от база/кеш--}}
{{--2. Проверяваш дали е изтекъл--}}
{{--3. Ако е изтекъл и имаш refresh token → обновяваш го--}}
{{--4. Ползваш новия access token--}}

{{--## Много важно за `refresh_token`--}}
{{--Google **не връща refresh token всеки път**.--}}
{{--Обикновено го дава само при първо съгласие или при специфични условия.--}}
{{--За да увеличиш шанса да го получиш:--}}
{{--- използвай `access_type=offline`--}}
{{--- `prompt=consent` помага--}}
{{--- но пак не е 100% гаранция при всяко влизане--}}

{{--## Практическа препоръка за твоя проект--}}
{{--Вместо да кешираш token за 1 ден, по-добре:--}}
{{--- записвай го в **database**--}}
{{--- пази `access_token`, `refresh_token`, `expires_at`--}}
{{--- при всяка заявка:--}}
{{--    - ако е валиден → използвай го--}}
{{--    - ако е изтекъл → refresh--}}
{{--    - ако няма refresh token → нов login--}}

{{--## Опростено правило--}}
{{--- **Access token** = краткотраен, за реалните API заявки--}}
{{--- **Refresh token** = дълготраен, за взимане на нов access token--}}
{{--- **Не разчитай access token да "си стои"**--}}

{{--Ако искаш, мога да ти покажа **как да го направиш в Laravel**:--}}
{{--- да запазваш токена в таблица--}}
{{--- да го refresh-ваш автоматично--}}
{{--- и да правиш `DriveService` клас за заявки към Google Drive.--}}

</body>
</html>

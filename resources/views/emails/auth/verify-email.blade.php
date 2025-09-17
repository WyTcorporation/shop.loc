<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Підтвердіть електронну адресу</title>
</head>
<body>
    <h1>Привіт, {{ $user->name }}!</h1>
    <p>Щоб активувати свій обліковий запис у Shop, підтвердіть електронну адресу протягом наступної години.</p>
    <p>
        <a href="{{ $verificationUrl }}" style="display:inline-block;padding:12px 24px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:6px;">
            Підтвердити електронну адресу
        </a>
    </p>
    <p>Якщо кнопка не працює, скопіюйте та вставте це посилання у свій браузер:</p>
    <p><a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a></p>
    <p>Якщо ви не створювали обліковий запис, проігноруйте цей лист.</p>
</body>
</html>

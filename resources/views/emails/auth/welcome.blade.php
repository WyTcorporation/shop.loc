<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Ласкаво просимо до Shop</title>
</head>
<body>
    <h1>Привіт, {{ $user->name }}!</h1>
    <p>Дякуємо за реєстрацію у Shop. Щоб завершити створення облікового запису, підтвердіть свою електронну адресу.</p>

    @if ($verificationUrl)
        <p>
            <a href="{{ $verificationUrl }}" style="display:inline-block;padding:12px 24px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:6px;">
                Підтвердити електронну адресу
            </a>
        </p>
        <p>Кнопка не працює? Скопіюйте та вставте це посилання у свій браузер: <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a></p>
    @endif

    <p>Якщо ви не створювали обліковий запис, просто проігноруйте цей лист.</p>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Affiliate Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">
            Welcome, {{ auth('affiliate')->user()->name }}
        </h1>
        <p class="text-gray-700">You are logged in as an affiliate.</p>
        <div class="mt-6">
            <a href="/logout/affiliate" class="text-red-500 hover:underline">Logout</a>
        </div>
    </div>
</body>
</html>
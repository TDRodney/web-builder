<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$tenant->name}}</title> 
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 text-slate-900">

    <main class="max-w-4xl mx-auto my-12 p-6 bg-white rounded-xl shadow">
        @foreach($blocks as $block)
            @include('partials.block', ['block' => $block])
        @endforeach
    </main>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$tenant->name}}'s Website</title> <!-- $tenant is resolved by the global middleware TODO: change this to the actual tenant SITE TITLE-->
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 text-slate-900">

    <main class="max-w-4xl mx-auto my-12 p-6 bg-white rounded-xl shadow">
        @foreach($blocks as $block)
            
            @if($block['type'] === 'HeroBlock')
                <div class="text-center" style="padding: {{ $block['styles']['padding'] }}px; background-color: {{ $block['styles']['backgroundColor'] }};">
                    <h1 class="text-4xl font-black">
                        {{ $block['content']['headline'] ?? 'Default Headline' }}
                    </h1>
                </div>
            @endif

        @endforeach
    </main>

</body>
</html>
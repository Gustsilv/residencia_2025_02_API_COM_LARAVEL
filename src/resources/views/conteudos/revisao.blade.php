<!DOCTYPE html>
<html>
<head>
    <title>Revisão de Conteúdos</title>
</head>
<body>
    @if(session('sucesso'))
    <div style="background:#d1ffd1;border:1px solid #92cc92;padding:10px;margin-bottom:10px;">
        {{ session('sucesso') }}
    </div>
@endif

@if(session('erro'))
    <div style="background:#ffd1d1;border:1px solid #cc9292;padding:10px;margin-bottom:10px;">
        {{ session('erro') }}
    </div>
@endif

    <h1>Conteúdos para Revisão</h1>

    @foreach($conteudos as $conteudo)
        <div style="border:1px solid #ccc; padding:10px;margin:10px;">
            <p><strong>ID:</strong> {{ $conteudo->id }}</p>
            <p><strong>Papel:</strong> {{ $conteudo->papel }}</p>
            <p><strong>Ticker:</strong> {{ $conteudo->ticker }}</p>
            <p><strong>Texto:</strong> {{ $conteudo->conteudo }}</p>

            {{-- Form Aprovar --}}
            <form action="/conteudos/{{ $conteudo->id }}/aprovar" method="POST" style="display:inline-block;">
                @csrf
                <button type="submit">Aprovar</button>
            </form>

            {{-- Form Reprovar --}}
            <form action="/conteudos/{{ $conteudo->id }}/reprovar" method="POST" style="display:inline-block; margin-left:10px;">
                @csrf
                <input type="text" name="motivo" placeholder="Motivo da reprovação" required>
                <button type="submit">Reprovar</button>
            </form>
        </div>
    @endforeach

</body>
</html>

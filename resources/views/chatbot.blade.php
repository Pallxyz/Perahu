<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Asisten AI Perahu IoT</title>
<link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
</head>
<body>

<div class="stage">
    <div class="stage-status" id="statusText">Siap membantu</div>
    <div class="character" id="character">
        <div class="ring"></div>
        <div class="char-body">
            <div class="eyes">
                <div class="eye"></div>
                <div class="eye"></div>
            </div>
            <div class="mouth"></div>
        </div>
    </div>
    <div class="char-name">Asisten Perahu IoT</div>
    <div class="char-hint">Tanya apa aja soal komponen, cara kontrol, atau perawatan produk ini</div>
</div>

<div class="chat-panel">
    <div class="chat-header">Chat</div>
    <div class="messages" id="messages"></div>
    <div class="input-bar">
        <input type="text" id="messageInput" placeholder="Tulis pesan..." autocomplete="off">
        <button id="sendBtn">Kirim</button>
    </div>
</div>

<script src="{{ asset('js/chatbot.js') }}"></script>
</body>
</html>
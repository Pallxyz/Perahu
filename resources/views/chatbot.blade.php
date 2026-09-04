<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Asisten AI Perahu IoT</title>
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        height: 100vh;
        background-image: url('/storage/pelabuhan 1.0.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        color: #eaf2f8;
        font-family: 'Segoe UI', sans-serif;
        display: flex;
        overflow: hidden;
    }

    .stage {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        background: transparent;
    }

    .stage-status {
        position: absolute;
        top: 24px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 13px;
        color: #93a8bd;
        background: #12283d;
        padding: 6px 16px;
        border-radius: 20px;
        border: 1px solid #1e3a52;
        z-index: 10;
    }

    #vrm-canvas-wrap {
        width: 100%;
        height: 100%;
        position: relative;
    }

    #vrm-canvas {
        width: 100%;
        height: 100%;
        display: block;
    }

    #vrm-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #93a8bd;
        font-size: 13px;
        text-align: center;
    }

    .char-name {
        position: absolute;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 16px;
        font-weight: 600;
        color: #7fd4ff;
        text-align: center;
    }

    .char-hint {
        position: absolute;
        bottom: 4px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 12px;
        color: #93a8bd;
        text-align: center;
        max-width: 280px;
    }

    .chat-panel {
        width: 400px;
        min-width: 340px;
        display: flex;
        flex-direction: column;
        background: transparent;
        border-left: 1px solid rgba(255, 255, 255, 0.25);
    }

    .chat-header {
        padding: 18px 20px;
        border-bottom: 1px solid #1e3a52;
        font-size: 15px;
        font-weight: 600;
        color: #eaf2f8;
    }

    .messages {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .bubble {
        max-width: 85%;
        padding: 10px 14px;
        border-radius: 14px;
        font-size: 14px;
        line-height: 1.4;
        overflow-wrap: anywhere;
        white-space: pre-wrap;
    }

    .bubble.user {
        align-self: flex-end;
        background: #3b82f6;
        color: #06121f;
        border-bottom-right-radius: 4px;
    }

    .bubble.bot {
        align-self: flex-start;
        background: #1e3a52;
        color: #eaf2f8;
        border-bottom-left-radius: 4px;
    }

    .bubble.typing {
        color: #93a8bd;
        font-style: italic;
    }

    .input-bar {
        display: flex;
        gap: 8px;
        padding: 14px;
        border-top: 1px solid #1e3a52;
    }

    .input-bar input {
        flex: 1;
        min-width: 0;
        padding: 12px 14px;
        border-radius: 10px;
        border: none;
        background: #12283d;
        color: #eaf2f8;
        font-size: 14px;
        outline: none;
    }

    .input-bar input:focus {
        box-shadow: 0 0 0 2px #3b82f6;
    }

    .input-bar button {
        padding: 0 18px;
        border: none;
        border-radius: 10px;
        background: #22c55e;
        color: #062910;
        font-weight: bold;
        cursor: pointer;
    }

    .input-bar button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    @media (max-width: 760px) {
        body {
            flex-direction: column;
        }

        .chat-panel {
            width: 100%;
            min-width: 0;
            height: 45vh;
        }

        .stage {
            height: 55vh;
            flex: none;
        }

        .char-name {
            bottom: 20px;
        }
    }
</style>

<script type="importmap">
{
    "imports": {
        "three": "https://cdn.jsdelivr.net/npm/three@0.180.0/build/three.module.js",
        "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.180.0/examples/jsm/",
        "@pixiv/three-vrm": "https://cdn.jsdelivr.net/npm/@pixiv/three-vrm@3/lib/three-vrm.module.min.js"
    }
}
</script>
</head>
<body>

<div class="stage">
    <div class="stage-status" id="statusText">Siap membantu</div>
    <div id="vrm-canvas-wrap">
        <canvas id="vrm-canvas"></canvas>
        <div id="vrm-loading">Memuat karakter 3D...</div>
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

<!-- ============ MODUL 3D: load & animasiin karakter VRM ============ -->
<script type="module">
import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { VRMLoaderPlugin, VRMUtils } from '@pixiv/three-vrm';

const canvas = document.getElementById('vrm-canvas');
const wrap = document.getElementById('vrm-canvas-wrap');
const loadingEl = document.getElementById('vrm-loading');

const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
renderer.setPixelRatio(window.devicePixelRatio);
renderer.setSize(wrap.clientWidth, wrap.clientHeight);

const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(30, wrap.clientWidth / wrap.clientHeight, 0.1, 20);
camera.position.set(0, 1.3, 2.2);
camera.lookAt(0, 1.25, 0);

scene.add(new THREE.DirectionalLight(0xffffff, 2).translateY(1).translateZ(1));
scene.add(new THREE.AmbientLight(0xffffff, 1.2));

let currentVrm = null;
let mode = 'idle'; // 'idle' | 'think' | 'talk' -- dipakai buat mulut aja, tangan gak ikut berubah
const clock = new THREE.Clock();

// Nilai mulut saat ini per vokal, dipakai buat transisi halus (lerp), bukan loncat mendadak
const visemes = ['aa', 'ih', 'ou', 'ee', 'oh'];
const currentMouthValues = { aa: 0, ih: 0, ou: 0, ee: 0, oh: 0 };

// Referensi tulang lengan (masih diambil, dipakai cuma buat pose diam yang natural)
let bones = { rightUpper: null, rightLower: null, leftUpper: null, leftLower: null };

const REST_RIGHT_Z = 1.2;
const REST_LEFT_Z = -1.2;

const loader = new GLTFLoader();
loader.register((parser) => new VRMLoaderPlugin(parser));

// ====== GANTI PATH INI SESUAI LOKASI FILE .vrm KAMU ======
const VRM_PATH = '/vrm/anastasia.vrm';

loader.load(
    VRM_PATH,
    (gltf) => {
        const vrm = gltf.userData.vrm;
        VRMUtils.removeUnnecessaryVertices(gltf.scene);
        VRMUtils.removeUnnecessaryJoints(gltf.scene);

        // Kalau karakternya masih menghadap belakang, ganti 0 jadi Math.PI (atau sebaliknya)
        vrm.scene.rotation.y = 0;

        bones.rightUpper = vrm.humanoid?.getNormalizedBoneNode('rightUpperArm');
        bones.leftUpper  = vrm.humanoid?.getNormalizedBoneNode('leftUpperArm');

        // Turunin tangan dari T-pose default SEKALI SAJA, diem di situ terus (gak dianimasiin lagi)
        if (bones.rightUpper) bones.rightUpper.rotation.z = REST_RIGHT_Z;
        if (bones.leftUpper)  bones.leftUpper.rotation.z  = REST_LEFT_Z;

        scene.add(vrm.scene);
        currentVrm = vrm;
        loadingEl.style.display = 'none';
    },
    (progress) => {
        if (progress.total) {
            const pct = Math.round((progress.loaded / progress.total) * 100);
            loadingEl.textContent = `Memuat karakter 3D... ${pct}%`;
        }
    },
    (error) => {
        loadingEl.textContent = 'Gagal load karakter 3D. Cek console (F12).';
        console.error('Gagal load VRM:', error);
    }
);

function animate() {
    requestAnimationFrame(animate);
    const t = clock.getElapsedTime();
    const dt = clock.getDelta();

    if (currentVrm) {
        const expr = currentVrm.expressionManager;
        if (expr) {
            // MULUT: gonta-ganti bentuk vokal (A I U E O) ngikutin volume suara asli, pas mode 'talk'
            // Transisinya di-lerp (halus), gak loncat mendadak antar bentuk - ini yang bikin keliatan natural
            let targetIntensity = 0;
            let activeIndex = -1;

            if (mode === 'talk') {
                targetIntensity = (typeof window.getMouthIntensity === 'function')
                    ? window.getMouthIntensity()
                    : (Math.sin(t * 14) + 1) / 2; // fallback kalau analyser belum siap
                // ganti vokal yang aktif tiap ~250ms (lebih pelan dari sebelumnya, biar gak buru-buru)
                activeIndex = Math.floor(t * 4) % visemes.length;
            }

            const mouthSmooth = 0.35; // makin besar = makin cepat ngikutin, makin kecil = makin halus/lembut
            visemes.forEach((v, i) => {
                const target = (i === activeIndex) ? targetIntensity : 0;
                currentMouthValues[v] = THREE.MathUtils.lerp(currentMouthValues[v], target, mouthSmooth);
                expr.setValue(v, currentMouthValues[v]);
            });

            // MATA: kedipan otomatis berkala, jalan terus di semua mode
            const blinkCycle = (t % 4);
            expr.setValue('blink', blinkCycle > 3.85 ? 1 : 0);
        }
        currentVrm.update(dt);
    }

    renderer.render(scene, camera);
}
animate();

window.addEventListener('resize', () => {
    renderer.setSize(wrap.clientWidth, wrap.clientHeight);
    camera.aspect = wrap.clientWidth / wrap.clientHeight;
    camera.updateProjectionMatrix();
});

// Fungsi ini dipanggil dari kode chat di bawah buat ganti mode animasi
let modeLocked = false;
window.setVrmMode = function (newMode) {
    if (modeLocked) {
        console.log('Mode LOCKED, abaikan perubahan ke:', newMode);
        return;
    }
    mode = newMode;
    console.log('Mode diganti jadi:', mode);
};
window.getVrmMode = function () {
    return mode;
};
window.lockVrmMode = function (locked) {
    modeLocked = locked;
    console.log(locked ? 'Mode DIKUNCI di: ' + mode : 'Mode dibuka lagi (auto)');
};

// ===== ALAT DEBUG: tuning posisi tangan langsung dari Console (F12) =====
// Cara pakai di Console: window.tuneArm(upperZ, upperX, lowerX)
// Contoh: window.tuneArm(0.3, -0.5, -1.2)
// Ketik window.stopTune() buat balikin ke animasi normal lagi
let debugOverride = null;
window.tuneArm = function (upperZ, upperX, lowerX) {
    debugOverride = { upperZ, upperX, lowerX };
    console.log('Tuning aktif. Coba angka lain kalau belum pas, atau window.stopTune() buat berhenti.');
};
window.stopTune = function () {
    debugOverride = null;
    console.log('Tuning dimatikan, balik ke animasi normal.');
};
</script>

<!-- ============ LOGIC CHAT (sama seperti sebelumnya, tinggal panggil setVrmMode) ============ -->
<script>
    const API_URL = "/api/chat";

    const sessionId = localStorage.getItem("perahu_session_id") ||
        (() => {
            const id = "sesi-" + Math.random().toString(36).slice(2, 10);
            localStorage.setItem("perahu_session_id", id);
            return id;
        })();

    const messagesEl = document.getElementById("messages");
    const inputEl = document.getElementById("messageInput");
    const sendBtn = document.getElementById("sendBtn");
    const statusEl = document.getElementById("statusText");

    function addBubble(text, role) {
        const div = document.createElement("div");
        div.className = "bubble " + role;
        div.textContent = text;
        messagesEl.appendChild(div);
        messagesEl.scrollTop = messagesEl.scrollHeight;
        return div;
    }

    // Mengetik teks selama durasi audio agar teks dan suara berjalan bersamaan
    function typeBubble(text, role, durationMs) {
        const div = document.createElement("div");
        div.className = "bubble " + role;
        messagesEl.appendChild(div);

        return new Promise((resolve) => {
            const totalChars = text.length;
            if (!totalChars) {
                div.textContent = text;
                resolve(div);
                return;
            }

            let i = 0;
            const intervalMs = Math.max(10, durationMs / totalChars);
            const interval = setInterval(() => {
                i++;
                div.textContent = text.slice(0, i);
                messagesEl.scrollTop = messagesEl.scrollHeight;

                if (i >= totalChars) {
                    clearInterval(interval);
                    resolve(div);
                }
            }, intervalMs);
        });
    }

    let currentAudio = null;
    let audioContext = null;
    let analyser = null;
    let analyserData = null;

    function setupAnalyser(audioEl) {
        if (!audioContext) {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
        }
        const source = audioContext.createMediaElementSource(audioEl);
        analyser = audioContext.createAnalyser();
        analyser.fftSize = 256;
        analyserData = new Uint8Array(analyser.frequencyBinCount);
        source.connect(analyser);
        analyser.connect(audioContext.destination);
    }

    // Dipanggil terus-terusan dari animation loop VRM (window.getMouthIntensity)
    window.getMouthIntensity = function () {
        if (!analyser || !analyserData) return 0;
        analyser.getByteFrequencyData(analyserData);
        let sum = 0;
        for (let i = 0; i < analyserData.length; i++) sum += analyserData[i];
        const avg = sum / analyserData.length; // 0-255
        return Math.min(avg / 60, 1); // normalisasi kasar ke 0-1
    };

    // Siapkan audio terlebih dahulu, lalu mulai mengetik tepat saat suara mulai diputar
    async function speakAndType(text) {
        if (currentAudio) {
            currentAudio.pause();
            currentAudio = null;
        }

        let audioUrl = null;

        try {
            const res = await fetch('/api/tts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text })
            });

            if (!res.ok) {
                throw new Error('TTS gagal: HTTP ' + res.status);
            }

            const audioBlob = await res.blob();
            audioUrl = URL.createObjectURL(audioBlob);
            const audio = new Audio(audioUrl);
            currentAudio = audio;

            // Tunggu metadata supaya durasi audio bisa dipakai untuk menyamakan animasi teks
            await new Promise((resolve, reject) => {
                audio.addEventListener('loadedmetadata', resolve, { once: true });
                audio.addEventListener('error', () => reject(new Error('Audio tidak bisa dimuat')), { once: true });
                audio.load();
            });

            if (!audioContext) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioContext.state === 'suspended') {
                await audioContext.resume();
            }
            setupAnalyser(audio);

            const durationMs = Number.isFinite(audio.duration) && audio.duration > 0
                ? audio.duration * 1000
                : Math.max(2000, text.length * 55);

            audio.onplay = () => {
                window.setVrmMode('talk');
                statusEl.textContent = 'Berbicara...';
            };

            audio.onended = () => {
                window.setVrmMode('idle');
                statusEl.textContent = 'Siap membantu';
                if (audioUrl) URL.revokeObjectURL(audioUrl);
            };

            // Mulai suara dan efek mengetik hampir bersamaan
            const typingPromise = typeBubble(text, 'bot', durationMs);
            await audio.play();
            await Promise.all([
                typingPromise,
                new Promise((resolve) => {
                    if (audio.ended) return resolve();
                    audio.addEventListener('ended', resolve, { once: true });
                    audio.addEventListener('error', resolve, { once: true });
                })
            ]);
        } catch (err) {
            console.warn('Gagal memutar suara:', err);
            window.setVrmMode('idle');
            statusEl.textContent = 'Suara gagal diputar';

            // Kalau TTS gagal, jawaban tetap muncul dan tidak membuat chat macet
            if (audioUrl) URL.revokeObjectURL(audioUrl);
            await typeBubble(text, 'bot', Math.max(1000, text.length * 25));
        }
    }

    let isSending = false;

    async function sendMessage() {
        if (isSending) return; // cegah spam kirim sebelum respons sebelumnya kelar
        const text = inputEl.value.trim();
        if (!text) return;
        isSending = true;

        addBubble(text, "user");
        inputEl.value = "";
        sendBtn.disabled = true;

        const typingBubble = addBubble("Mengetik...", "bot typing");
        statusEl.textContent = "Berpikir...";
        window.setVrmMode('think');

        try {
            const res = await fetch(API_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ message: text, session_id: sessionId })
            });
            const data = await res.json();
            typingBubble.remove();

            if (!res.ok) {
                addBubble(data.error || "Maaf, terjadi kesalahan.", "bot");
                statusEl.textContent = "Siap membantu";
                window.setVrmMode('idle');
            } else {
                await speakAndType(data.reply);
            }
        } catch (err) {
            typingBubble.remove();
            addBubble("Gagal terhubung ke server. Pastikan backend sedang berjalan.", "bot");
            statusEl.textContent = "Koneksi gagal";
            window.setVrmMode('idle');
        } finally {
            sendBtn.disabled = false;
            isSending = false;
            inputEl.focus();
        }
    }

    sendBtn.addEventListener("click", sendMessage);
    inputEl.addEventListener("keydown", (e) => { if (e.key === "Enter") sendMessage(); });

    addBubble("Halo! Aku asisten produk perahu kendali jarak jauh ini. Mau tanya soal apa?", "bot");
</script>
</body>
</html>
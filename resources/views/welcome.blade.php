<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Asisten AI Perahu IoT</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        height: 100vh;
        background: #0b1b2b;
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
        background: radial-gradient(circle at 50% 35%, #12283d 0%, #0b1b2b 70%);
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
        top: 50%; left: 50%;
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
        background: #0e2033;
        border-left: 1px solid #1e3a52;
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
    .bubble.typing { color: #93a8bd; font-style: italic; }
    .input-bar {
        display: flex;
        gap: 8px;
        padding: 14px;
        border-top: 1px solid #1e3a52;
    }
    .input-bar input {
        flex: 1;
        padding: 12px 14px;
        border-radius: 10px;
        border: none;
        background: #12283d;
        color: #eaf2f8;
        font-size: 14px;
        outline: none;
    }
    .input-bar input:focus { box-shadow: 0 0 0 2px #3b82f6; }
    .input-bar button {
        padding: 0 18px;
        border: none;
        border-radius: 10px;
        background: #22c55e;
        color: #062910;
        font-weight: bold;
        cursor: pointer;
    }
    .input-bar button:disabled { opacity: 0.5; cursor: not-allowed; }

    @media (max-width: 760px) {
        body { flex-direction: column; }
        .chat-panel { width: 100%; min-width: 0; height: 45vh; }
        .stage { height: 55vh; }
    }
</style>

<!-- ============ IMPORT MAP: ambil library 3D dari CDN, gak perlu install apapun ============ -->
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
let mode = 'idle'; // 'idle' | 'think' | 'talk'
const clock = new THREE.Clock();

// Referensi tulang lengan kiri & kanan
let bones = { rightUpper: null, rightLower: null, leftUpper: null, leftLower: null };

// ===== NILAI POSE — BISA DIUBAH LIVE LEWAT CONSOLE (F12) SAAT TESTING =====
// Contoh di console: window.poseValues.think.rightUpper.x = -0.5  (Enter, langsung keliatan)
// Setelah pas, kabarin angka finalnya biar aku kunci permanen di kode.
window.poseValues = {
    restRightZ: 1.2,
    restLeftZ: -1.2,
    think: {
        rightUpper: { x: -0.9, z: 0.4 },
        rightLower: { x: -2.0 },
    },
    talk: {
        rightUpper: { x: -0.3, y: -0.9, z: 0.6 },
        rightLower: { x: -1.3 },
        leftUpper:  { x: -0.3, y: 0.9,  z: -0.6 },
        leftLower:  { x: -1.3 },
    },
    idle2: {
        rightUpper: { x: -0.15 },
        rightLower: { x: -0.3 },
    },
};

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
        bones.rightLower = vrm.humanoid?.getNormalizedBoneNode('rightLowerArm');
        bones.leftUpper  = vrm.humanoid?.getNormalizedBoneNode('leftUpperArm');
        bones.leftLower  = vrm.humanoid?.getNormalizedBoneNode('leftLowerArm');

        // Turunin tangan dari T-pose default biar natural
        if (bones.rightUpper) bones.rightUpper.rotation.z = window.poseValues.restRightZ;
        if (bones.leftUpper)  bones.leftUpper.rotation.z  = window.poseValues.restLeftZ;

        scene.add(vrm.scene);
        currentVrm = vrm;
        loadingEl.style.display = 'none';

        // Buat debugging: cek di Console ketik "window.bones" buat liat tulang mana yang null
        window.bones = bones;
        console.log('Bone check:', {
            rightUpper: !!bones.rightUpper,
            rightLower: !!bones.rightLower,
            leftUpper: !!bones.leftUpper,
            leftLower: !!bones.leftLower,
        });
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

function lerpBone(bone, axis, target, smooth) {
    if (!bone || target === undefined) return;
    bone.rotation[axis] = THREE.MathUtils.lerp(bone.rotation[axis], target, smooth);
}

function animate() {
    requestAnimationFrame(animate);
    const t = clock.getElapsedTime();
    const dt = clock.getDelta();

    if (currentVrm) {
        // idle: gerakan nafas halus, selalu jalan
        currentVrm.scene.position.y = Math.sin(t * 1.5) * 0.015;

        const expr = currentVrm.expressionManager;
        if (expr) {
            // TALKING: mulut ngikutin volume suara asli, gonta-ganti bentuk vokal
            const visemes = ['aa', 'ih', 'ou', 'ee', 'oh'];
            if (mode === 'talk') {
                const intensity = (typeof window.getMouthIntensity === 'function')
                    ? window.getMouthIntensity()
                    : (Math.sin(t * 14) + 1) / 2;
                const visemeIndex = Math.floor(t * 8) % visemes.length;
                visemes.forEach((v, i) => {
                    expr.setValue(v, i === visemeIndex ? intensity : 0);
                });
            } else {
                visemes.forEach(v => expr.setValue(v, 0));
            }

            // THINKING: mata agak merem (ekspresi mikir) + auto-blink pas mode lain
            if (mode === 'think') {
                expr.setValue('blink', 0.5);
            } else {
                const blinkCycle = (t % 4);
                expr.setValue('blink', blinkCycle > 3.85 ? 1 : 0);
            }
        }

        currentVrm.update(dt);

        // ===== ANIMASI LENGAN =====
        const P = window.poseValues;
        const smooth = 0.1;

        let targetRightUpperZ = P.restRightZ, targetRightUpperX = 0, targetRightUpperY = 0;
        let targetRightLowerX = 0;
        let targetLeftUpperZ = P.restLeftZ, targetLeftUpperX = 0, targetLeftUpperY = 0;
        let targetLeftLowerX = 0;

        if (mode === 'think') {
            // Tangan KANAN ke dagu, tangan kiri tetap istirahat
            targetRightUpperZ = P.think.rightUpper.z;
            targetRightUpperX = P.think.rightUpper.x;
            targetRightLowerX = P.think.rightLower.x;
        } else if (mode === 'talk') {
            // KEDUA tangan nyilang di dada
            targetRightUpperZ = P.talk.rightUpper.z;
            targetRightUpperX = P.talk.rightUpper.x;
            targetRightUpperY = P.talk.rightUpper.y;
            targetRightLowerX = P.talk.rightLower.x;

            targetLeftUpperZ = P.talk.leftUpper.z;
            targetLeftUpperX = P.talk.leftUpper.x;
            targetLeftUpperY = P.talk.leftUpper.y;
            targetLeftLowerX = P.talk.leftLower.x;
        } else {
            // IDLE: gantian standby 1 & standby 2 tiap ~8 detik
            const idleCycle = Math.floor(t / 8) % 2;
            if (idleCycle === 1) {
                targetRightUpperX = P.idle2.rightUpper.x;
                targetRightLowerX = P.idle2.rightLower.x;
            }
        }

        lerpBone(bones.rightUpper, 'z', targetRightUpperZ, smooth);
        lerpBone(bones.rightUpper, 'x', targetRightUpperX, smooth);
        lerpBone(bones.rightUpper, 'y', targetRightUpperY, smooth);
        lerpBone(bones.rightLower, 'x', targetRightLowerX, smooth);

        lerpBone(bones.leftUpper, 'z', targetLeftUpperZ, smooth);
        lerpBone(bones.leftUpper, 'x', targetLeftUpperX, smooth);
        lerpBone(bones.leftUpper, 'y', targetLeftUpperY, smooth);
        lerpBone(bones.leftLower, 'x', targetLeftLowerX, smooth);
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
window.setVrmMode = function (newMode) {
    mode = newMode;
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

    async function speak(text) {
        if (currentAudio) {
            currentAudio.pause();
            currentAudio = null;
        }
        try {
            const res = await fetch('/api/tts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text })
            });
            if (!res.ok) {
                window.setVrmMode('idle');
                return;
            }
            const audioBlob = await res.blob();
            const audioUrl = URL.createObjectURL(audioBlob);
            currentAudio = new Audio(audioUrl);

            if (audioContext && audioContext.state === 'suspended') {
                audioContext.resume();
            }
            setupAnalyser(currentAudio);

            currentAudio.onplay = () => {
                window.setVrmMode('talk');
                statusEl.textContent = "Berbicara...";
            };
            currentAudio.onended = () => {
                window.setVrmMode('idle');
                statusEl.textContent = "Siap membantu";
                URL.revokeObjectURL(audioUrl);
            };
            currentAudio.play();
        } catch (err) {
            console.warn('Gagal memutar suara:', err);
            window.setVrmMode('idle');
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
                addBubble(data.reply, "bot");
                speak(data.reply);
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
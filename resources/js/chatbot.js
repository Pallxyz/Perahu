// ================== KONFIGURASI DEVELOPER ==================
// Karena file ini di-serve dari Laravel sendiri, pakai path relatif "/api/chat"
// TIDAK PERLU nulis "http://127.0.0.1:8000" lagi -- otomatis nyambung ke domain manapun nanti pas deploy
const API_URL = "/api/chat";

const VOICE_CONFIG = {
  lang: "id-ID",
  rate: 1.0,
  pitch: 1.0,
  volume: 1.0,
  preferredVoiceName: "Google Bahasa Indonesia"
};
// =============================================================

const sessionId = localStorage.getItem("perahu_session_id") ||
  (() => {
    const id = "sesi-" + Math.random().toString(36).slice(2, 10);
    localStorage.setItem("perahu_session_id", id);
    return id;
  })();

const messagesEl = document.getElementById("messages");
const inputEl = document.getElementById("messageInput");
const sendBtn = document.getElementById("sendBtn");
const characterEl = document.getElementById("character");
const statusEl = document.getElementById("statusText");

function addBubble(text, role) {
  const div = document.createElement("div");
  div.className = "bubble " + role;
  div.textContent = text;
  messagesEl.appendChild(div);
  messagesEl.scrollTop = messagesEl.scrollHeight;
  return div;
}

function speak(text) {
  if (!("speechSynthesis" in window)) return;
  window.speechSynthesis.cancel();

  const utter = new SpeechSynthesisUtterance(text);
  utter.lang = VOICE_CONFIG.lang;
  utter.rate = VOICE_CONFIG.rate;
  utter.pitch = VOICE_CONFIG.pitch;
  utter.volume = VOICE_CONFIG.volume;

  const voices = window.speechSynthesis.getVoices();
  const preferred = voices.find(v => v.name === VOICE_CONFIG.preferredVoiceName)
                 || voices.find(v => v.lang === VOICE_CONFIG.lang);
  if (preferred) utter.voice = preferred;

  utter.onstart = () => {
    characterEl.classList.add("talking");
    statusEl.textContent = "Berbicara...";
  };
  utter.onend = () => {
    characterEl.classList.remove("talking");
    statusEl.textContent = "Siap membantu";
  };

  window.speechSynthesis.speak(utter);
}

async function sendMessage() {
  const text = inputEl.value.trim();
  if (!text) return;

  addBubble(text, "user");
  inputEl.value = "";
  sendBtn.disabled = true;

  const typingBubble = addBubble("Mengetik...", "bot typing");
  statusEl.textContent = "Berpikir...";

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
    } else {
      addBubble(data.reply, "bot");
      speak(data.reply);
    }
  } catch (err) {
    typingBubble.remove();
    addBubble("Gagal terhubung ke server. Pastikan backend sedang berjalan.", "bot");
    statusEl.textContent = "Koneksi gagal";
  } finally {
    sendBtn.disabled = false;
    inputEl.focus();
  }
}

sendBtn.addEventListener("click", sendMessage);
inputEl.addEventListener("keydown", (e) => { if (e.key === "Enter") sendMessage(); });

addBubble("Halo! Aku asisten produk perahu kendali jarak jauh ini. Mau tanya soal apa?", "bot");

if ("speechSynthesis" in window) {
  window.speechSynthesis.onvoiceschanged = () => window.speechSynthesis.getVoices();
}
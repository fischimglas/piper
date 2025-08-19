# Adapter

## Planned adapter
- Elasticsearch 
- Ollama
- Wikipedia Adapter
- Anthropic Claude
- Mistral / Mixtral

🔤 LLMs / Textgenerierung
•	OpenAI (GPT-4/5, ChatGPT API) ✅ → API
•	Anthropic Claude ✅ → Claude API
•	Google Gemini (früher PaLM/Bard) ✅ → AI Studio / Vertex AI
•	Mistral / Mixtral ✅ → API & Open Source
•	Meta LLaMA ❌ (nur Modelle, aber per Hugging Face API verfügbar)

⸻

✍️ Textverbesserung / Grammatik
•	Grammarly ✅ → Grammarly Text API
•	LanguageTool ✅ → LT API (gratis & Self-host)
•	ProWritingAid ❌ (kein offizielles API)
•	QuillBot ❌ (kein offizielles API, nur Webapp → viele nutzen inoffizielle Scraper)
•	Hemingway Editor ❌ (nur App, kein API)

⸻

🪄 Paraphrasieren & Umformulieren
•	Wordtune ❌ (nur App, Browser)
•	QuillBot ❌ (kein offizielles API)
•	Copy.ai ✅ → API (Business Accounts)
•	Jasper AI ✅ → API (Beta/Business)

⸻

📑 Zusammenfassung & Struktur
•	OpenAI, Claude, Gemini ✅ (sehr stark für Summarization per Prompt)
•	Scholarcy ✅ → Scholarcy API (wissenschaftliche Zusammenfassungen)
•	Notion AI ❌ (nur innerhalb Notion nutzbar, kein API)

⸻

🎨 Creative Writing
•	Sudowrite ❌ (kein API, nur Webapp für Autoren)
•	NovelAI ✅ (hat ein API, eher für Story-gen / Anime-Text)
•	AI Dungeon ✅ (API für Premium-User)

⸻

📰 Marketing & SEO Texte
•	Jasper AI ✅
•	Copy.ai ✅
•	Writesonic ✅ → Writesonic API
•	INK AI ❌ (kein API)
•	Anyword ✅ → Anyword API

⸻

🛠 Developer / Open Source
•	spaCy (kein API, aber Python Library)
•	NLTK (kein API, Python Library)
•	Haystack ✅ (REST API möglich, Self-host)
•	LangChain ✅ (eigenes API + Framework)
•	TextBlob (kein API, nur Library)

⸻

🚦 Zusammenfassung (beste Kandidaten für deine Adapter):

✅ Hat offizielles API
•	OpenAI
•	Claude
•	Google Gemini
•	Mistral
•	Grammarly
•	LanguageTool
•	Copy.ai
•	Jasper
•	Writesonic
•	Anyword
•	Scholarcy
•	NovelAI

❌ Kein API / nur Web
•	QuillBot
•	ProWritingAid
•	Hemingway
•	Wordtune
•	Sudowrite
•	Notion AI
•	INK


🗣 Text-to-Speech (TTS) & Voice Cloning
•	OpenAI TTS ✅ → OpenAI Audio API
•	ElevenLabs ✅ → ElevenLabs API (sehr beliebt für Voice Cloning & TTS)
•	Google Cloud Text-to-Speech ✅ → Google Cloud TTS API
•	Amazon Polly (AWS) ✅ → Amazon Polly API
•	Microsoft Azure Speech ✅ → Azure Cognitive Speech API
•	Play.ht ✅ → Play.ht API
•	Coqui TTS ✅ (Open Source, self-host, REST-API möglich)
•	Resemble AI ✅ → Resemble API

🎵 AI Music Generation
•	Suno AI ❌ (nur Webapp, kein offizielles API, aber sehr stark)
•	Udio ❌ (neue Player im Musikbereich, kein offizielles API bisher)
•	Boomy ❌ (Webapp, kein offizielles API)
•	Soundraw.io ❌ (kein API, nur SaaS)
•	Aiva ✅ → Aiva API (kompositorisches AI-Tool)
•	Amper Music (von Shutterstock) ❌ (API eingestellt)
•	Loudly AI Music Generator ❌ (kein offizielles API)

🔊 Sound Design & SFX
•	AudioLDM ✅ (Open Source, text-to-audio model, REST-API möglich)
•	Riffusion ✅ (Open Source, Musik aus Stable Diffusion, API möglich)
•	Meta AudioCraft (MusicGen, AudioGen, EnCodec) ✅ (Open Source Modelle, API selbst baubar)
•	Stability AI – Stable Audio ✅ → Stable Audio API (kommerziell, API verfügbar)

📚 Speech-to-Speech / Dubbing
•	OpenAI Whisper ✅ (Speech-to-Text, Open Source + API)
•	Deepgram ✅ → Deepgram API
•	AssemblyAI ✅ → AssemblyAI API
•	Speechmatics ✅ → Speechmatics API


👉 D.h. du kannst in deiner Library direkt Adapter bauen für:
•	Text → Voice (TTS) (OpenAI, ElevenLabs, Google, Polly, Azure)
•	Text → Music (Stable Audio, Aiva)
•	Text → SoundFX (AudioLDM, Meta AudioCraft)

Pinecone API (Vektor-Datenbanken, Embeddings)
•	Weaviate API (Open Source Vektordatenbank, API-first)
•	Milvus API (Open Source Vektordatenbank)
•	ElasticSearch API (Semantische Suche mit Embeddings)

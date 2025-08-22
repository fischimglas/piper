#!/bin/sh
set -e

# Pull the smallest model at container start
ollama pull llama2-7b

# Start Ollama server in background
ollama serve --host 0.0.0.0 --port 11434 &

# Wait until the server is ready
echo "Waiting for Ollama server to start..."
until curl -s http://localhost:11434/healthz >/dev/null 2>&1; do
    sleep 1
done

echo "Ollama server is ready."
# Keep the container alive
wait

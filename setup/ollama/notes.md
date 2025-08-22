## Models:
- smollm:latest

## Install model
docker exec -it piper-ollama-1 ollama pull smollm:latest

## List Models
docker exec -it piper-ollama-1 ollama list

## Run interactive shell
docker exec -it piper-ollama-1 ollama run smollm:latest


## health check
curl http://localhost:11434/health

## Test the model
curl -X POST http://localhost:11434/v1/completions \
-H "Content-Type: application/json" \
-d '{
"model": "smollm:latest",
"prompt": "Hello Ollama! Can you introduce yourself?",
"max_tokens": 50
}'

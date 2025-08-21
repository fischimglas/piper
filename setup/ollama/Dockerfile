# Use the official Ollama image
FROM ollama/ollama:latest

# Set working directory and Ollama home
ENV OLLAMA_HOME=/root/.ollama
WORKDIR /root

# Install the smallest model (adjust model name as needed)
RUN ollama pull llama2-7b

# Expose API port
EXPOSE 11434

# Default command to start Ollama REST API
CMD ["ollama", "api", "--host", "0.0.0.0", "--port", "11434"]

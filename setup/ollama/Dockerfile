# Use the official Ollama image
FROM ollama/ollama:latest

ENV OLLAMA_HOME=/root/.ollama
WORKDIR /root

# Copy startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Expose API port
EXPOSE 11434

# Run the startup script at container start
ENTRYPOINT ["/start.sh"]

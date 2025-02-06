# Gerando a imagem
docker build -t gabrielmanzano/cvdw-cli .  

# Subindo pro Registry
docker push gabrielmanzano/cvdw-cli:latest

# Subindo o compose
docker compose up -d
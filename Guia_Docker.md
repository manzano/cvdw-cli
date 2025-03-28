# Docker cvdw-cli

## Criar imagem

```bash
docker build -t gabrielmanzano/cvdw-cli:latest .
```

## Ajustes no docker-compose

Modifique o docker-compose.yaml com os seus dados.

```yaml
    environment:
      CV_URL: SEU AMBIENTE
      CV_EMAIL: SEU EMAIL
      CV_TOKEN: SEU TOKEN
```

## Executar Container

```bash
docker run --rm -ti gabriel/cvdw-cli:0.42.0 ash
```

## Rodando via compose

```bash
docker compose up -d
```

## Miscs

### Subindo pro Registry

```bash
docker push gabrielmanzano/cvdw-cli:latest
```

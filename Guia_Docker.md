# Docker cvdw-cli

## Ajustes no docker-compose

Modifique o docker-compose.yaml com os seus dados.

```yaml
    environment:
      CV_URL: _SEU_AMBIENTE_
      CV_EMAIL: _SEU_EMAIL_
      CV_TOKEN: _SEU_TOKEN_
      DB_PASSWORD: _SUA_SENHA_PARA_O_MYSQL_
      MYSQL_ROOT_PASSWORD: _SUA_SENHA_PARA_O_MYSQL_
```

## Criar imagem e rodar container via compose

```bash
docker compose up -d --build
```

## Misc

### Executar Container

```bash
docker run --rm -ti gabrielmanzano/cvdw-cli:latest ash
```

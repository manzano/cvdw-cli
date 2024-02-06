# Configuração do Ambiente e Instalação do CVDW

Este guia fornece uma visão geral detalhada dos passos necessários para configurar seu ambiente Linux / Mac / Windows e instalar o CVDW, incluindo a instalação do PHP 8.2 e suas extensões necessárias.

## Atualizar o Sistema

Antes de iniciar, é recomendável atualizar os pacotes do sistema para as versões mais recentes disponíveis:

```bash
sudo yum update -y
sudo yum install -y amazon-linux-extras
sudo yum clean metadata
sudo yum update
sudo yum upgrade
```

curl -sSL https://raw.githubusercontent.com/manzano/cvdw-cli/main/install.sh
chmod +x install.sh
./install.sh

## Instalação de Ferramentas e Extensões PHP

A instalação inclui ferramentas essenciais como `tmux` e várias extensões PHP para garantir compatibilidade e funcionalidade:

```bash
sudo yum install -y tmux
sudo yum install -y php8.2 php8.2-cli php8.2-pdo php8.2-mysqlnd php8.2-pgsql
```

## Verificação da Extensão Phar

Para verificar se a extensão Phar está instalada e habilitada:

```bash
php -m | grep Phar
```

## Preparação para o CVDW

Os seguintes comandos preparam o ambiente para o CVDW:

```bash
mkdir cvdw-cli
cd cvdw-cli
wget -O cvdw-cli.zip https://github.com/manzano/cvdw-cli/archive/refs/tags/v0.8.0-alpha.zip
unzip cvdw-cli.zip
mv cvdw-cli-0.8.0-alpha/build/cvdw.phar ./cvdw.phar
rm -rf cvdw-cli-0.8.0-alpha/
rm -rf cvdw-cli.zip
chmod +x cvdw.phar
```

## Configuração do Alias

Para facilitar o uso do CVDW, configure um alias no `.bashrc`:

```bash
vim ~/.bashrc
# Adicione a seguinte linha ao arquivo
alias cvdw='php /home/ec2-user/cvdw-cli/cvdw.phar'
# Salve e saia do editor, então aplique as mudanças
source ~/.bashrc
```

## Conclusão

Após seguir estes passos, seu ambiente estará configurado e o CVDW pronto para ser usado. Para iniciar o CVDW, simplesmente digite `cvdw` no terminal.

---

Este documento destina-se a ajudar na configuração inicial e instalação. Para mais informações ou suporte, consulte a documentação oficial ou entre em contato com o suporte.

# Adora README

## Estrutura de Diretórios
- /adminer: (Mysql adminer)
- /config: (configurações do WordPress)
	- wp-config-dev.php
- /docker: (Arquivos do Docker compose para os ambientes)
	- docker-compose-dev.yml
- /mysql: (Database inicial)
- /nginx: (Arquvios de configuração do Nginx)
- /wordpress: (Diretório da aplicação)
- docker-compose.yml (Arquivos do Docker compose para os ambientes)
- README.md

## Para rodar o Docker
- ####Desenvolvimento
	**Neste Ambiente é ncessário fazer o build do docker-compose**
	- build

		docker-compose -f docker-compose.yml -f docker/docker-compose-dev.yml build

	- Iniciar

		docker-compose -f docker-compose.yml -f docker/docker-compose-dev.yml up -d

	- Parar

		docker-compose -f docker-compose.yml -f docker/docker-compose-dev.yml stop

	**Acessos**

	URL: 	http://adora.dev.perrout.com.br

	Adminer:	http://adora.dev.perrout.com.br

	**Usuário Wordpress**
	user: padmin
	pass: 123mudar

	* Ajustar o /etc/hosts para
		127.0.0.1       adora.dev.perrout.com.br


- ####Homologação

	- Iniciar

		docker-compose -f docker-compose.yml -f docker/docker-compose-hml.yml up -d

	- Parar

		docker-compose -f docker-compose.yml -f docker/docker-compose-hml.yml stop

	**Acessos**

	URL: 	http://adora.dev.perrout.com.br

	Adminer:	http://adora.dev.perrout.com.br/adminer

## Aplicação

A aplicação está no diretório /wordpress. Lá deve conter os arquivos do core do Wordpress, temas, plugins e arquivos de idiomas.

O arquivo wp-config.php é um volume do docker que fica no diretório /config e não há necessiade de allterações para rodar a aplicação# admnw

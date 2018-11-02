# Star Citizen Fleet Manager #

## Installation ##

**Clone repository**

```
git clone https://github.com/Ioni14/starcitizen-fleet-manager.git
cd starcitizen-fleet-manager
```

**Create local .env file**

```
cp .env.dist .env
```

**(Optional but recommended) configure .env**

* `APP_SECRET` a long random value
* `DB_ROOT_PASSWORD` a long random value
* `TRUSTED_PROXIES` to the ip / range ip of your potential proxies

**(Optional but recommended) override docker-compose.yml**

* Configure for example the port mapping.

**Launch the stack**

```
docker-compose up -d --build
```

**Install dependencies**
```
docker-compose exec -u $(id -u):$(id -g) php composer install -o
```

**Apply DB migrations**
```
docker-compose exec -u $(id -u):$(id -g) php bin/console d:m:m -n
```

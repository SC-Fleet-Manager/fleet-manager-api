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
* `TRUSTED_PROXIES` the ip / range ip of your potential proxies

**(Optional but recommended) override docker-compose.yml**

* Configure for example the port mapping.

### For development purposes ###

**Override the docker-compose stack**
* `cp docker-compose.override.yml.dist docker-compose.override.yml`
* Configure your `docker-compose.override.yml`

**Update .env**

* `APP_ENV=dev`

**Launch the stack**

```
docker-compose up -d --build
```

**Apply DB migrations**
```
docker-compose exec -u $(id -u):$(id -g) php bin/console d:m:m -n
```

**Install dependencies**
```
make yarn c=install
```

**Compile & Watch assets**
```
make yarn c=watch
```

**Launch tests**
```
make do_tests
```

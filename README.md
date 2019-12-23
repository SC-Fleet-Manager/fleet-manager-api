# Star Citizen Fleet Manager #

## Installation for development ##

**Requirements**

- git
- docker
- docker-compose

**Clone repository**

```
git clone https://github.com/Ioni14/starcitizen-fleet-manager.git
cd starcitizen-fleet-manager
```

**Customize environment variables**

```
echo "APP_ENV=dev" > .env.local
```

You can add your Discord OAuth2 config via `DISCORD_ID` and `DISCORD_SECRET`:

1. Create an app on https://discordapp.com/developers/applications/
2. Generate a client secret
3. Add your domain in OAuth2 redirects uri

**Customize docker-compose.override.yml**

    cp docker-compose.override.yml.dist docker-compose.override.yml

Customize the ports according to your needs, configure your dev reverse-proxy, etc.

**Launch the stack (build & up containers)**

```
make up
```

**Prepare the database (create db & apply migrations)**
```
make db-reset
```

**Install dependencies (yarn install)**
```
make yi
```

**Compile & Watch assets**
```
make watch
```

**Launch all tests**
```
make tests
```

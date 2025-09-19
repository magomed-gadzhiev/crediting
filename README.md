## Crediting API (Loan requests processing)

Prod-ready demo service with DDD (Decision, Loan), CQRS, RabbitMQ, Docker Compose, Yii2, PostgreSQL.

### Requirements
- PHP 7.4 (containerized)
- PostgreSQL
- Docker Compose

### Environment
Copy `.env.example` to `.env` and adjust if needed.

Required DB params exactly as per task (tests rely on them):
- host: localhost
- port: 5432
- database: loans
- user: user
- password: password

Note: in Docker network app connects to `postgres:5432`, but from host tests use `localhost:5432`.

### Run
1) Install vendors
```
docker-compose run --rm php composer install --no-interaction --prefer-dist
```

2) Start stack
```
docker-compose up -d --build
```

3) Initialize AMQP topology (idempotent; also runs on container start)
```
docker-compose run --rm php php yii amqp/init-topology
```

4) Run DB migrations
```
docker-compose run --rm php php yii migrate --interactive=0
```

5) Verify app is available
```
curl -i http://localhost/
```

### API

POST `/requests`
Request:
```
{
  "user_id": 1,
  "amount": 3000,
  "term": 30
}
```
Responses:
- 201 `{ "result": true, "id": 42 }`
- 400 `{ "result": false }`

GET `/processor?delay=5`
- 200 `{ "result": true }`

Rules:
- Approval probability 10% (configurable via env `APPROVAL_PROBABILITY`)
- Only one approved request per user
- Processing uses RabbitMQ workers, can be triggered concurrently, requests for the same user may be processed in parallel, decision application is transactional and guarded by a mutex

### Testing (optional)
```
docker-compose run --rm php vendor/bin/codecept run
```

### Time spent
- Development and docs: 6h

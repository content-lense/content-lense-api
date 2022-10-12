# API

- based on API platform / symfony


## Microservices

- are configured in `AnalysisMicroservice` entity
    - `name`: choose whatever you want
    - `endpoint`: the route (e.g. `http://localhost:3111/sentiment`)
    - `headers`: any additional headers you'd like us to send to the endpoint, e.g. (`x-auth-token`: `123-123-123-123`)
    - `autoRunForNewArticles`: if true, this endpoint is called whenever a new articles has been added to the database
    - `organisation`: related organisation
    - `method`: method to be used (defaults to post)
- the endpoint needs to accept `POST` requests including a single article's JSON payload such as the following:
```json
{
    "id": 0, 
    "heading":"The Headline of the Article",
    "summary":"A short summary / abstract of the article",
    "authors": ["First Author", "Second Author"],
    "body": "The entire fulltext"
}
```

- whenever a new article is pushed,
- need to be able to receive a standardized article object
## Welcome to Content Lense 👋

<p align="center">
  <img src="https://user-images.githubusercontent.com/15559708/195378979-701254fa-ada7-41d4-abc7-494a40207a6d.png" />
</p>

_This is the API of Content Lense, a project that aims at enabling publishers to easily gain insights into their content._

-> See the full [ContentLense on Github](https://github.com/content-lense)


## How to get started

- We will soon provide a very simple docker compose stack that includes latest stable builds of all parts of content lense. You will then just need to do a `docker compose up` to get up and running.
- If you want to try the api right now, please do the following:

```bash
# Checkout and start the API docker stack
git clone git@github.com:content-lense/content-lense-api.git
cd content-lense-api
docker compose up -d

# Create the database, load doctrine fixtures and create jwt key pairs
./dev_flush_db.sh
```

## Entities


### Organisation

- One _Organisation_ has one `owner` _User_.
- One _Organisation_ can have many `members[]` _Users_.
- One _Organisation_ can have many _Articles_ in 'articles[]'.
- One _Organisation_ can have many _AnalysisMicroservices_ in 'analysisMicroservices[]'.
- Every member has access to all _Articles_ of the _Organisation_
- Every member has access to the organisations API token (note: currently, using the API token means to have the same permissions as the owning user of the organisation)

### User

- Users log in via POST request to `/auth/login` with a JSON payload that includes `email` and `password`. The route issues a JWT token using the split-cookie approach (header and signed payload).
- Only users that have `isActive` and `isConfirmed` set to `true` are allowed to login

### Article

- Article belong the an _Organisation_ and can be created via a simple POST request to /articles using the Accept header set to `application/json+ld` 
- One _Article_ can have many _ArticleAnalysisResults_
- One _Article_ can have many _ArticleMentions_ (currently only people via `mentionedPeople` field)

### ArticleMention
 
- this is a mapping table between _Article_ and _Person_ including a `mentionCount` field

### Person

- One _Person_ can be the author of many _Articles_ (`Person::articles`)
- One _Person_ can be mentioned in many _Articles_ ('Person::articleMentions')
- More fields are `firstName`, `lastName`, `age`, `gender` and `rawFullName` (we currently have this field to recognize unique mentions from our [Mentioned entites API](https://github.com/content-lense/content-lense-mention-api) which is part of this project)

### AnalysisMicroservice

Analysis microservices are APIs (either an existing ContentLense service or any service of your choice) used to analyse incoming texts. For example, it counts the number of mentions of people, analyses text complexity, sentiment, etc.

- Once a AnalysisMicroservices is registered, new articles are automatically sent to it if `autoRunForNewArticles` and `isActive` are both set to `true`. 
- The resulting payload of these services is stored in _ArticleAnalysisResult_.
- After the result has been received from the microservice, a `PostAnalysisProcessorMessage` is created for all configured `PostProcessors`. The `PostProcessorService` needs to handle the configured post processing steps (e.g. `STORE_MENTIONED_PEOPLE` post processor which handles the result of the `metion api` payload)
- One _AnalysisMicroservice_ has one _Organisation_. 
- More fields are:
    - `name`: choose whatever you want
    - `endpoint`: the route (e.g. `http://localhost:3111/sentiment`)
    - `headers`: any additional headers you'd like us to send to the endpoint, e.g. (`x-auth-token`: `123-123-123-123`)
    - `method`: method to be used (defaults to post)

The endpoint of every configured microservice should accept the following JSON payload:

```json
{
    "id": 0, 
    "heading":"The Headline of the Article",
    "summary":"A short summary / abstract of the article",
    "authors": ["First Author", "Second Author"],
    "body": "The entire fulltext"
}
```

### ArticleAnalysisResult

- this is a mapping table between _Article_ and _AnalysisMicroservice_ including a `rawResult` field that contains the payload of the microservice

### Webhook (work in progress)

Webhook help you to forward results of _ContentLense_ to other services, e.g. Content Management Systems. They run either _after a _AnalysisMicroservice_ is done OR _after a new article has been posted_ if `runOnNewArticle` is set to true. It simply forwards the result of the analysis to the CRM including the article information to the configured `endpoint`

- One _Webhook_ can have many _AnalysisMicroservice_ ('runAfterAnalyses')

### RefreshToken

This is an empty entity extending `Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken` needed for the `LexikJWTAuthenticationBundle`, we did not change anything on it.


## Supported by

Media Tech Lab [`media-tech-lab`](https://github.com/media-tech-lab)

<a href="https://www.media-lab.de/en/programs/media-tech-lab">
    <img src="https://raw.githubusercontent.com/media-tech-lab/.github/main/assets/mtl-powered-by.png" width="240" title="Media Tech Lab powered by logo">
</a>

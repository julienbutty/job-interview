<div class="align-center">
<h3 align="center">Synolia</h3>
  <p align="center">
   Julien Butty technical tests 
  </p>
</div>
<hr>

## About The Project

Technical test with 2 main exercices:

* Consume SugarCrm API using PHP 
* Consume Sirene INSEE API using a javascript framework 


### Built With

* Symfony 6.1 
* php 8.17
* include Boostrap 5.2 cdn
* include JQuery 3.6 cdn

### Time spent
* exercice 1 :<br>
It took me about 8 hours for the first exercise to read the documentation, manipulate the backend, do reverse engineering, read Symfony documentation and code 
* exercic 2:<br>
It took me about 4 hours.<br>
I hadn't done ajax for a while so I had to go back to the doc and sometimes stackOverflow. The requests are quite simple so I didn't spend too much time on the doc except at the beginning for the token creation

## Getting Started

### Prerequisites

* sugarCRM credentials
* generate a token from [Insee API](https://api.insee.fr/catalogue/site/themes/wso2/subthemes/insee/pages/help.jag#generer)
 
### Installation

1. Clone the repo
   ```sh
   git clone https://github.com/julienbutty/job-interview.git
   ```
3. Launch composer install
   ```sh
   composer install
   ```
4. Enter your API SugarCrm credentials in `.env` or `.env.local`
   ```yaml
    ###> SugarCRM API ###
    SUGAR_USERNAME=yourUsername
    SUGAR_PASSWORD=yourPassword
    ###< SugarCRM API ###
   ```
5. Enter your Insee Siren token in `templates/base.html.twig`
   ```html
   <!-- line 41 -->
    const bearerToken = '';
   ```
   
6. Launch the Symfony built-in server
     ```sh
        symfony serve -d
    ``` 
7. Now you can test each of this routes
    * /contact-list
    * /specific-contact
    * /specific-contact-cases
    * /create-case
    * /api-siren

## Explanations

### Exercice 1 (SugarCRM)
#### Structure
For this exercise I created the ApiController class which will allow us to manage actions to communicate with the API.<br>
But I only want this one to get the request, call the API and display the answer. I want to have as little logic as possible. So I created the SugarApiService class whose role will be to talk to the api.<br>

I inject the HttpClient service of Symfony which allows to manage requests like Guzzle.
   ```php
   class SugarApiService
   {
       public function __construct(
           public HttpClientInterface $sugarClient,
            //...
   ```
I added some parameters of HttpClient in the framework.yaml file in order not to repeat them in the service like the base url and the Content-type

   ```yaml
   http_client:
      scoped_clients:
         sugar.client:
            base_uri: "https://sg-candidat.demo.sugarcrm.eu"
            headers:
               Content-Type: 'application/json'
   ```
I also injected the API credentials in the service
   ```php 
   class SugarApiService
   {
       public function __construct(
            //...
           private readonly string    $username,
           private readonly string    $password,
           //...
   ```
For more security I used environment variables that will be configured directly on the server in case of production
   ```yaml

   ###> SugarCRM API ###
      SUGAR_USERNAME=
      SUGAR_PASSWORD=
   ###< SugarCRM API ###

  
   App\Service\SugarApiService:
    arguments:
       $username: '%env(SUGAR_USERNAME)%'
       $password: '%env(SUGAR_PASSWORD)%'
   ```
#### Actions
 1. API Connection<br><br>
The ApiSugarService takes a 'token' parameter in its constructor.<br>
This token is directly built in the constructor method by calling a connect method which will generate an authentication token from the API.
Then we could use this token in each api calls.
      ```php
      class SugarApiService
      {
          public function __construct(
              //...
              private ?string            $token = null
         )
         {
            $this->token = $this->connect();
         }

         public function connect(): string
         {
            $response = $this->sugarClient->request(
               'POST',
               '/rest/v11_17/oauth2/token',
               //...
               ]
           );

           return $response->toArray()['access_token'];
         }
      ```
2. Process<br><br>
   Then for each action the process is quite classical:<br>
   The controller sends a request to the API via the SugarApiService.<br>
   Each request is surrounded by a try catch to raise an exception.<br>
   I try to stop the script as soon as possible in case of error which is always better in terms of performance.<br>
   For example for the listing of tickets related to a contact there are 2 API calls and I throw an exception directly after the first call if the result is empty
   ```php
   public function showSpecificContactCases(): Response
   {
      $contact = $this->getSpecificContact();

      if (empty($contact)) {
         throw new NotFoundHttpException('Specific user doesn\'t exist');
      }
      //...
   }
   ```
<br> 

#### Improvments

Many query made to the API are hard-coded for this exercise.<br>
Ideally I would have liked to make it more dynamic with forms.<br>
If I take the request to retrieve a specific contact I would like to generate a form where you can choose the filter (contains, equals, starts...) and the field(s) to search on.<br>
I would have retrieved these fields in the controller and could have sent them to the SugarApiService.<br>
I could then have a function that would have built the filters probably in a new dedicated class<br><br>
I would also create models when I get data from the API.<br>
For example when I get a contact, I could build a DTO so that I have an object that I can control and validate.<br><br>
#### Difficulties / Interrogations <br><br>
It took me a while to build the right syntax for filters despite the documentation<br>
   ```php
      'filter[0][$or][0][first_name][$contains]' => "a",
      'filter[0][$or][0][last_name][$contains]' => "b",

   ```
   <br>
I had to reverse engineer to create a ticket for a specific user.<br>
Indeed with the route provided by the documentation I was not sure to create in the right module <br>

```
#documentation route
/<module>/:record/link/:link_name POST
```
Still for this same action I was not able to find the identifier of the user connected with the route `/me`.
So I hard-coded it in the query
   ```php
    public function createCase(string $contactId): string
    {
        $url = sprintf("/rest/v11_17/Contacts/%s/link/cases", $contactId);

        $response = $this->sugarClient->request(
            'POST',
            $url,
            [
                'auth_bearer' => $this->token,
                'body' => [
                    //...
                    "account_id" => "e78d0e60-3359-11ed-bc7e-067f2945c900",
   ```
<br>

### Exercice 2 (Siren API)
#### Structure
As requested in the statement I just used an html page which can be accessed via the folder `/templates/base.html.twig`.<br>
If needed, I also created a controller with the route /api-siren which allows to have the profiler 

#### Actions
I built a form with 2 fields.<br>
One field to request a SIRET number and one for the SIREN number.<br>

If you type a SIREN number you will get a message telling you that you need 9 characters to make the request.<br>
Once the 9 characters are reached the request is sent and the answer is displayed in JSON.
A message is displayed if the SIREN number does not exist 

You have a button that allows you to delete the result and the value of the input.

In the same way if you make a search in the other field the result and the input are deleted.

#### Improvments

- I should have validated the characters in the input so as not to send unnecessary requests to the API.
- I could have installed Webpack Encore and installed JQuery to have a specific javascript file and not write the code in the html file

#librest

This library handle rest http requests and the url is interpret as a resource request. 
By resource is meant a domain object model, where those classes one wants to expose must implement RestEnable.

The url schema is like this:
```
/rest/ClassName[/uid|a_static_method|/uid/a_method][?property=value&...]
```

**Examples:**
 
GET /rest/Person will return all person objects

GET /rest/Person/1 will return a person object with id 1

GET /rest/Person/pets will call the static method 'pets' on the class Person and return the output

GET /rest/Person/1/cats will call the method on the person object with uid == 1 and return the output

GET /rest/Person?name=foo will return all persons object which have a the name 'foo'

GET /rest/Person/pets?name=xx will call the static method 'pets' on class Person with arguments 'xx' 
and return the output

DELETE /rest/Person will delete all person objects

DELETE /rest/Person/1 will delete

POST /rest/Person/pets?property=name,.. will set all the properties and call static function on Person.

POST /rest/Person/1?property=name,.. will set all the properties and call save on the person object with id 1 

**Configuration:**

If DiContainer object is configured with an object named restAuthenticator, so the rest call will authenticated before
executionen.

Typical you can check $_SERVER['REQUEST_URI'] in your index.php, 
and if it starts with /rest/ then call: echo Rest::run($_SERVER, $_REQUEST); 
<?php
///   Query String
/*

users.php?username=AAAAAAAA&password=99999999&macaddress=


Time format complies with  ISO 8601
"2013-04-23T18:25:43.511Z"

*/
?>

HTTP/1.1 200 OK
Content-Type: application/json

{
  "users":[
    {
      "user_id":1,
      "username":"Chris",
      "email":"chris@ap1.io",
      "img":"http://www.image.com/Chris.png",
      "inout":1,
      "datetime": "2013-04-23T18:25:43.511Z"
    },
    {
      "user_id":5,
      "username":"Joe",
      "email":"joe@ap1.io",
      "img":"http://www.image.com/noimg.png",
      "inout":1,
      "datetime": "2013-04-23T18:25:43.511Z"
    },
      {
      "user_id":11,
      "username":"Rich",
      "email":"rich@ap1.io",
      "img":"http://www.image.com/noimg.png",
      "inout":0,
      "datetime": "2013-04-23T18:25:43.511Z"
    }
    
  ]
}
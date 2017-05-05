Simple test MVC base for users and goods
========================================

### Completed

#### System
- DB - simple DB MySQLi connector
- ORM - simple ORM for Models
- Router - simple class for parse url and recall controllers
- Render - view renderer class  
- Controller_Base - basic class with before recall filter

#### Model
- User - basic ORM role for users (with auth)

#### View
- empty template file
- user login form and index welcome file

#### Controller
- Controller_Base_Auth - class for filter only authorized users
- Controller_User - for User model basic controller
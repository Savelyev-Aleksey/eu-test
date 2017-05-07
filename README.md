Simple test MVC base for users and goods
========================================

### Completed

#### System
- DB - simple DB MySQLi connector
- ORM - simple ORM for Models
- Router - simple class for parse url and recall controllers
- Render - view renderer class
- Controller_Base - basic class with before recall filter
- Form - helper class

#### Model
- User - basic ORM role for users (with auth)
- Good - ORM goods table model
- Good_Review - user reviews on goods table model

#### View
- empty template file
- user login form and index welcome file
- user index page with link on goods page (temp)
- Good page with list of goods.

#### Controller
- Controller_Base_Auth - class for filter only authorized users
- Controller_User - for User model basic controller
- Controller_Good - for Good and Good_Review model controller
# phpliteauth
Small authentication library for PHP, using SQLite as a database backend

## Installation

	composer require tlucas/liteauth

In the project file (e.g. project.php) you wish to use it in make sure you have

	require_once('vendor/autoload.php');

Then initialise the authentication object with:

	$auth = new liteAuth\liteAuth('path/to/my/auth_database.db');

(Of course, you can call the object anything, but for the rest of this readme, we will assume you called it `$auth`)

## Basic usage

### User creation
The very first thing you will have to do, before logging in to liteAuth, is, of course, create a user.

This is done using:
	
	$auth->newUser($user, $pass, $email, $fname, $sname, $admin);

Only the `$user` and `$pass` parameters are required, the rest are optional.

So, to give an example:

	$auth->newUser('John', 'superstrongpassword');

We now have a user called `John` in the database, with the password `superstrongpassword`.

(If newUser() is successful it returns the new user's `id` , otherwise, it returns `False`)

(The other fields should be self-explanatory, with the posssible expception of the `$admin`. This is simply a `True`/`False` field, it has no special meaning within liteAuth, so you are free to use it however you will!)

#### From a form

There is also included a helper method `registerFromPost()`, to allow for easy registration form creation.

Place it at the target of a form, and it will look for the relevant post variables, to register a new user.

For example, you might have a file `register.php` containing

	$auth->registerFromPost();

And another file `signup.html`:
	
	<form action="register.php" method="post">
	<input type="text" name="user" placeholder="Username"><br>
	<input type="text" name="fname" placeholder="First name"><br>
	<input type="text" name="sname" placeholder="Surname"><br>
	<input type="email" name="email" placeholder="Email Address"><br>
	<input type="password" name="pass" placeholder="Password"><br>
	<input type="password" name="pass2" placeholder="Password"><br>
	<input type="checkbox" name="admin"> Admin?<br>
	<input type="submit" value="Register">
	</form>

The `register.php` will take the data from `signup.html` and create a new user corresponding to the input.

***BE CAREFUL***: Anyone with access to the script calling `registerFromPost()` will be able to create a new user. Make sure this is only accessible by people who should have this authority!

One example would be to require an admin user to be logged in:

`register.php`:

	if($auth->user->admin)
		$auth->registerFromPost();

### Logging in

Logging in is very similar to creating a user, you haev the `login()` method you can call:

	$auth->login($user, $pass);

After which, if the password correctly matches the user, it will return `True` and populate `$auth->user`

The following properties are available on the user object, once logged in:

	$auth->user->user 		// User's username
	$auth->user->first_name 	// User's first name, if set
	$auth->user->surname 		// User's surname, if set
	$auth->user->email		// User's email address, if set
	$auth->user->admin		// If the user is set as an admin

There is also the special method

	$auth->name()

Which returns either the user's human name (ie. 'Firstname surname' or 'Firstname'), or falls back to username.

### From a form

Just like with registration, there is a helper method for logging in from a form:

	$auth->loginFromPost();

If you put that in, for example, `login.php`, the form at `loginform.html`:

	<form action="register.php" method="post">
	<input type="text" name="user" placeholder="Username"><br>
	<input type="password" name="pass" placeholder="Password"><br>
	<input type="submit" value="Login">
	</form>

Will pass the appropriate values to sign in.

## Modifying a user

To modify an existing user, you can edit any of the accessible properties, listed above, for example
	
	$auth->user->first_name = 'Stephen';

And then call

	$auth->user->save();

And it will update the current user's first name to `Stephen` in the database.

## Other methods

	$auth->countUsers();
Returns a count of users that exist in the database

	$auth->existUsers();

Returns False if no users exist (This is useful to allow for 'first run' setup procedures)
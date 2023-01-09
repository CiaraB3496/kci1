<?php
//SLIM FRAMEWORK API

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../connections/dbconnections.php';
require __DIR__ . '/../connections/dboperations.php';
$app = AppFactory::create();
$app->addBodyParsingMiddleware(); 
$app->setBasePath("/MyAPI/public");
$app->addErrorMiddleware(true, true, true);


/*
 * Endpoint:CreateUser
 * Parameters:Email,Password,Name,School
 * Method:Post
 */
$app->post('/createuser', function(Request $request, Response $response) { //Request and Response for Post Method
	if (!emptyParameters(array('email', 'password', 'name', 'school'), $response)) { //If there are no empty parameters
		$request_data = $request->getParsedBody(); //Get parsed values 
		//Define Parameters
		$email = $request_data['email'];
		$password = $request_data['password'];
		$name = $request_data['name'];
		$school = $request_data['school'];
		
		//Encrypt password 
		$hash_password = password_hash($password, PASSWORD_DEFAULT);
		
		//Defined DBoperations 
		$dbO = new DbOperations;

		$result = $dbO->createUser($email, $hash_password, $name, $school); //define result variable

		if ($result == USER_CREATED) { //If created, no error and User creation successful
			$message = array(); //Message array 
			$message['error'] = false; //No error
			$message['message'] = 'User Created Successfully.';

			$response->getBody()->write(json_encode($message)); //Write Response in Json Format

			return $response
								->withHeader('Content-type', 'application/json')
								->withStatus(201); //http status code 201 Created

		} else if ($result == USER_FAILURE) { //If user failure, error message and user creation not successful
			$message = array();
			$message['error'] = true; //Error 
			$message['message'] = 'An Error Occurred.'; 

			$response->getBody()->write(json_encode($message)); //Write response in Json Format

			return $response
								->withHeader('Content-type', 'application/json')
								->withStatus(422); //http status code 422 Unprocessable Entity

		} else if ($result == USER_EXISTS) { //if user already exists, error message and no user creation.
			$message = array();
			$message['error'] = true;
			$message['message'] = 'User Already Exists.';

			$response->getBody()->write(json_encode($message)); 

			return $response
								->withHeader('Content-type', 'application/json')
								->withStatus(422); //http status code 422 Unprocessable Entity
		}
	}

	return $response //If empty parameters return error from emptyParameters method
						->withHeader('Content-type', 'application/json')
						->withStatus(422); //http status code 422 Unprocessable Entity
});

$app->post('/userread', function(Request $request, Response $response){
	if (!emptyParameters(array('email', 'password'), $response)){
		$request_data = $request->getParsedBody(); //Get parsed values 
		//Define Parameters
		$email = $request_data['email'];
		$password = $request_data['password'];
		//Defined DBoperations 
		$dbO = new DbOperations;
		$result = $dbO->userRead($email,$password); //define result variable

		if($result == USER_ACCEPTED){
			$user = $dbO->getUserByEmail($email);
			$message = array(); //Message array 
			$message['error'] = false; //No error
			$message['message'] = 'Login Successful';
			$message['user']=$user;

			$response->getBody()->write(json_encode($message)); //Write response in Json Format
			return $response
								->withHeader('Content-type', 'application/json')
								->withStatus(201); //http status code 201 Successful

		}else if ($result == USER_NOT_FOUND){
			$message = array(); //Message array 
			$message['error'] = true; //No error
			$message['message'] = 'User Does Not Exist';

			$response->getBody()->write(json_encode($message)); //Write response in Json Format
			return $response
								->withHeader('Content-type', 'application/json')
								->withStatus(422); //http 422 Unprocessable Entity


		}else if ($result == USER_INVALID){
			$message = array(); //Message array 
			$message['error'] = true; //No error
			$message['message'] = 'Password Incorrect';

			$response->getBody()->write(json_encode($message)); //Write response in Json Format
			return $response
								->withHeader('Content-type', 'application/json')
								->withStatus(422); //http 422 Unprocessable Entity
		}
	}
	return $response //If empty parameters return error from emptyParameters method
	->withHeader('Content-type', 'application/json')
	->withStatus(422); //http status code 422 Unprocessable Entity

});

$app->get('/allusers', function(Request $request, Response $response){
	$dbO= new DbOperations;
	$users = $dbO->getAllUsers();
	$response_data=array();

	$response_data['error']=false;
	$response_data['users']=$users;

	$response->getBody()->write(json_encode($response_data)); //Write response in Json Format
			return $response
								->withHeader('Content-type', 'application/json')
								->withStatus(201);
});
//Should be put method, but paramaters pass as empty through postman, try put with android
$app->post('/updateuser/{id}', function(Request $request, Response $response, array $args ){
	$id = $args['id'];
	if(!emptyParameters(array('email','name','school','id'), $response)){

		$request_data=$request->getParsedBody();
		$email = $request_data['email'];
		$name = $request_data['name'];
		$school = $request_data['school'];
		$id= $request_data['id'];

		$dbO= new DbOperations;

		if($dbO->updateUser($email,$name,$school,$id)){
			$response_data=array();
			$response_data['error'] = false; //No error
			$response_data['message'] = 'Update Successful';
			$user= $dbO->getUserByEmail($email);
			$response_data['user']=$user;
				
			$response->getBody()->write(json_encode($response_data)); //Write response in Json Format
				
			return $response
									->withHeader('Content-type', 'application/json')
									->withStatus(422); //http 422 Unprocessable Entity
		}else{
		$response_data=array();
		$response_data['error'] = true; //No error
		$response_data['message'] = 'Update UnSuccessful';
		$user= $dbO->getUserByEmail($email);
		$response_data['user']=$user;
			
		$response->getBody()->write(json_encode($response_data)); //Write response in Json Format
			
		return $response
								->withHeader('Content-type', 'application/json')
								->withStatus(422); //http 422 Unprocessable Entity
		}
	}
	return $response //If empty parameters return error from emptyParameters method
	->withHeader('Content-type', 'application/json')
	->withStatus(422); //http status code 422 Unprocessable Entity

});
	//Should be put method, but paramaters pass as empty through postman, try put with android
	$app->post('/updatepassword', function(Request $request, Response $response){
		if(!emptyParameters(array('currentPassword','newPassword','email'), $response)){
			
			$request_data = $request->getParsedBody(); 

			$currentPassword = $request_data['currentPassword'];
			$newPassword = $request_data['newPassword'];
			$email = $request_data['email']; 

			$dbO = new DbOperations; 

			$result = $dbO->updatePassword($currentPassword, $newPassword, $email);
			if($result == PASSWORD_UPDATED){
				$response_data = array(); 
				$response_data['error'] = false;
				$response_data['message'] = 'Password Changed';
				$response->getBody()->write(json_encode($response_data)); //Write response in Json Format
				return $response->withHeader('Content-type', 'application/json')
								->withStatus(200);
	
			}else if($result == PASSWORD_INVALID){
				$response_data = array(); 
				$response_data['error'] = true;
				$response_data['message'] = 'Current Password Invalid';
				$response->getBody()->write(json_encode($response_data)); //Write response in Json Format
				return $response->withHeader('Content-type', 'application/json')
								->withStatus(200);
			}else if($result == PASSWORD_UNCHANGED){
				$response_data = array(); 
				$response_data['error'] = true;
				$response_data['message'] = 'An Error Occurred';
				$response->getBody()->write(json_encode($response_data)); //Write response in Json Format
				return $response->withHeader('Content-type', 'application/json')
								->withStatus(200);
			}
		}
		return $response //If empty parameters return error from emptyParameters method
		->withHeader('Content-type', 'application/json')
		->withStatus(422); //http status code 422 Unprocessable Entity
		
	});
	//delete method also not working through postman? 
	$app->post('/deleteuser', function(Request $request, Response $response){
		if(!emptyParameters(array('id'), $response)){
			$request_data = $request->getParsedBody(); 
			$id= $request_data['id'];
			$dbO = new DbOperations; 
		
			$result=$dbO->deleteUser($id);
		
			if($result==USER_DELETED){
				$response_data = array(); 
				$response_data['error'] = false;
				$response_data['message'] = 'User Deleted';
				$response->getBody()->write(json_encode($response_data)); //Write response in Json Format
				return $response->withHeader('Content-type', 'application/json')
								->withStatus(200);    
			}else if($result == USER_UNCHANGED){
				$response_data = array(); 
				$response_data['error'] = true;
				$response_data['message'] = 'An Error Occurred';
				$response->getBody()->write(json_encode($response_data)); //Write response in Json Format
				return $response->withHeader('Content-type', 'application/json')
								->withStatus(422);
			}
		} return $response //If empty parameters return error from emptyParameters method
			->withHeader('Content-type', 'application/json')
			->withStatus(422); //http status code 422 Unprocessable Entity
			
	});


 //Method for parameter validation
function emptyParameters($required_params, $response) {
	$error = false; //Assume error is false to begin
	$error_params = ''; //Define empty parameter
	$request_params = $_REQUEST; //Define request parameters

	foreach ($required_params as $param) { //loop through parameters
		if(!isset($request_params[$param]) || strlen($request_params[$param]) <= 0) { //if the parameters are not set or the string length <0
			$error = true; //set error to true
			$error_params .= $param . ', '; //Define error parameters
		  }
		}

	if($error) { //if there is an error show detail message
		$error_detail = array(); //Create error detail array
  		$error_detail['error'] = true; 
  		$error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty'; //remove last ', ' for error message

		$response->getBody()->write(json_encode($error_detail)); //json encode to return error message
	}

	return $error;
}


$app->run();
?>
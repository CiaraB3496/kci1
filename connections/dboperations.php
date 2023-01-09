<?php
//Class for database operations
class dboperations{
    private $connection;

    //Constructor for connection
    function __construct(){
        require_once dirname(__FILE__) . '/dbconnections.php';
        $db = new dbconnections;
        $this->connection = $db->connect();
        
    }
    //Function for creating new user in database
    public function createUser($email,$password,$name,$school){
        if(!$this->checkEmailExists($email)){ //if email not found in database create user
            $stmt = $this->connection->prepare("INSERT INTO users ( email ,password, name, school) VALUES(?, ?, ?, ?)" );
            $stmt->bind_param("ssss", $email, $password, $name, $school); //bind parameters to string 's'

            if($stmt->execute()){
                return USER_CREATED; //Return Created response
            } else{
                return USER_FAILURE; //Return Failure response
            }
        } 
        return USER_EXISTS; //Return already exists response
    }

    //Function for checking Email Parameter in database
    private function checkEmailExists($email){
        $stmt =$this->connection->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email); //Bind parameter to string
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows >0; 
    }

    public function userRead($email,$password){
        if($this->checkEmailExists($email)){
            $hashed_password= $this->getUserPasswordByEmail($email);
            if(password_verify($password, $hashed_password)){
            return USER_ACCEPTED;
            }else{
            return USER_INVALID;
            }
        }else{
            return USER_NOT_FOUND;
        }
    }
    //function for getting password by email
    private function getUserPasswordByEmail($email){
        $stmt =$this->connection->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($password);
        $stmt->fetch();
        return $password;
       
    }
   //function for getting user info by email
    public function getUserByEmail($email){
        $stmt =$this->connection->prepare("SELECT id, email, name, school FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($id, $email, $name, $school);
        $stmt->fetch();
        $user = array();
        $user['id']=$id;
        $user['email']=$email;
        $user['name']=$name;
        $user['school']=$school;
        return $user;
    }   
       //function for getting user info by email
       public function getallUsers(){
        $stmt =$this->connection->prepare("SELECT id, email, name, school FROM users");
        $stmt->execute();
        $stmt->bind_result($id, $email, $name, $school);
        $users = array();
        while($stmt->fetch()){
        $user = array();
        $user['id']=$id;
        $user['email']=$email;
        $user['name']=$name;
        $user['school']=$school;
        array_push($users,$user);
        }
        return $users;
    }   
    //Function for updating user information
    public function updateUser( $email, $name, $school,$id){
        $stmt =$this->connection->prepare("UPDATE users SET email=?, name=?, school=? WHERE id=?");
        $stmt->bind_param("sssi", $email, $name, $school,$id);
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function deleteUser($id){
        $stmt = $this->connection->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()){
            return USER_DELETED; 
        }else{
            return USER_UNCHANGED;
        }
    }

    



    //Funtion for updating user password
    public function updatePassword($currentPassword, $newPassword, $email){
        $hashed_password=$this->getUserPasswordByEmail($email);

        if(password_verify($currentPassword,$hashed_password)){
            $hash_password=password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt=$this->connection->prepare("UPDATE users SET password=? WHERE email=?");
            $stmt->bind_param('ss', $hash_password, $email);

            if($stmt->execute()){
                return PASSWORD_UPDATED;
            } else{ 
                return PASSWORD_UNCHANGED;
            }

        } else{
            return PASSWORD_INVALID;
        }
    }
}


?>
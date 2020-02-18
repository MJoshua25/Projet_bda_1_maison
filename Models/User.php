<?php
require_once 'Database.php';
/**
 * Created by PhpStorm.
 * User: jyao2
 * Date: 02/03/2019
 * Time: 02:30
 */

class User
{
    public $nom;
    public $prenom;
    public $datenaiss;
    public $lieunaiss;
    public $sexe;
    public $nationalite;
    public $niveau;
    public $email;
    public $mdpass;
    public $tel;
    public $correspondant;
    public $solde;
    public $lienphoto;
    public $transactions =[];


    public function m_gain(){
        $db = Database::connect();
        $statement = $db->prepare('select sum(montant) from transactions where receveur =? and (type="depot" or type="transfert")  and month(Date)=month(NOW())');
        $statement->execute(array($this->num_compte));
        $mg = $statement->fetch();
        if ($mg[0]==null){
            $mg=0;
        }
        else
            $mg=(int)$mg[0];
        Database::disconnect();
        return $mg;
    }

    public function m_trans(){
        $db = Database::connect();
        $statement = $db->prepare('select sum(montant) from transactions where compte =? and type="transfert" and month(Date)=month(NOW())');
        $statement->execute(array($this->num_compte));
        $mt = $statement->fetch();
        if ($mt[0]==null){
            $mt=0;
        }
        else
            $mt=(int)$mt[0];
        Database::disconnect();
        return $mt;
    }

    public function __construct($email){
        $db = Database::connect();
        $statement = $db->prepare('select * from user where email=?');
        $statement->execute(array($email));
        $var = $statement->fetch();
        $this->num_compte = $var['num_compte'];
        $this->nom = $var['nom'];
        $this->prenom = $var['prenom'];
        $this->sexe = $var['sexe'];
        $this->telephone = $var['telephone'];
        $this->naiss = $var['naiss'];
        $this->pays = $var['pays'];
        $this->ville = $var['ville'];
        $this->email = $var['email'];
        $this->pass = $var['pass'];
        $this->image = $var['image'];
        Database::disconnect();
        $this->actual_solde();
    }

    public static function insert($list){
        $db = Database::connect();
        $statement = $db->prepare('insert into user(num_compte, nom, prenom, sexe, telephone, naiss, pays, ville, email, pass) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
        $env[] = random_int(1000000,9999999);
        $env[] = $list['nom'];
        $env[] = $list['prenom'];
        $env[] = $list['sexe'];
        $env[] = $list['telephone'];
        $env[] = $list['naiss'];
        $env[] = $list['pays'];
        $env[] = $list['ville'];
        $env[] = $list['email'];
        $env[] = $list['pass'];
        $statement->execute($env);
        Database::disconnect();
        return new User($list['email']);
    }
    public static function login($email,$pass){
        $db = Database::connect();
        $statement = $db->prepare('select * from user where email=? and pass=?');
        $statement->execute(array($email,$pass));
        $test=$statement->fetch();
        Database::disconnect();
        if($test){
            return  new User($email);
        }
        else{
            return false;
        }
    }
    public function actual_solde(){

        $db = Database::connect();
        $statement = $db->prepare('select sum(montant) from transactions where receveur =? and type<>"retrait"');
        $statement->execute(array($this->num_compte));
        $pos = $statement->fetch();
        if ($pos[0]==null){
            $pos=0;
        }
        else
            $pos=(int)$pos[0];
        $statement = $db->prepare('select sum(montant) from transactions where compte=? and type<>"depot"');
        $statement->execute(array($this->num_compte));
        $neg = $statement->fetch();
        if ($neg[0]==null){
            $neg=0;
        }
        else
            $neg=(int)$neg[0];
        $this->solde = $pos-$neg;
        $statement = $db->prepare('update user set solde=? where num_compte=?');
        $statement->execute(array($this->solde,$this->num_compte));
        Database::disconnect();
    }
}
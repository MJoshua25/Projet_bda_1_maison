<?php
require_once 'Database.php';

class Transaction
{
    public $id;
    public $type;
    public $montant;
    public $date;
    public $receveur;
    public function __construct($id)
    {
        $db = Database::connect();
        $statement = $db->prepare('select * from transactions where id=?');
        $statement->execute(array($id));
        $var = $statement->fetch();
        $this->id = $var['id'];
        $this->compte = $var['compte'];
        $this->montant = $var['montant'];
        $this->date = $var['date'];
        $this->receveur = $var['receveur'];
        Database::disconnect();
    }

    public static function insert($list){
        $db = Database::connect();
        $statement = $db->prepare('insert into transactions(type, compte, montant, Date, receveur) VALUES (?,?,?,NOW(),?)');
        $env[] = $list['type'];
        $env[] = $list['compte'];
        $env[] = $list['montant'];
        $env[] = $list['receveur'];
        $statement->execute($env);
        $id =$db->lastInsertId();
        Database::disconnect();
    }
}
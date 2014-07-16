<?php
/**
 * Created by PhpStorm.
 * User: MajdiH
 * Date: 27/06/2014
 * Time: 13:07
 */



class User {

    private $id;
    private $name;
    private $email;
    private $password_hash;
    private $api_key;
    private $status;
    private $created_at;

    function __construct($name, $email, $password_hash, $api_key)
    {
        $this->api_key = $api_key;
        $this->email = $email;
        $this->name = $name;
        $this->password_hash = $password_hash;
    }

    /**
     * @param mixed $api_key
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $password_hash
     */
    public function setPasswordHash($password_hash)
    {
        $this->password_hash = $password_hash;
    }

    /**
     * @return mixed
     */
    public function getPasswordHash()
    {
        return $this->password_hash;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }/**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }/**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }


} 
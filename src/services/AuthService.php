<?php

class AuthService
{
  private $userModel;

  public function __construct(User $userModel)
  {
    $this->userModel = $userModel;
  }

  public function register($fullName, $email, $password, $role, $contactNumber, $gender)
  {
    $existingEmail = $this->userModel->findByEmail($email);

    if ($existingEmail) {
      return [
        'success' => false,
        'message' => 'Email is already registered'
      ];
    }

    $existingContact = $this->userModel->findByContact($contactNumber);

    if ($existingContact) {
      return [
        'success' => false,
        'message' => 'Contact Number is already registered.'
      ];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $created = $this->userModel->create($fullName, $email, $passwordHash, $role, $contactNumber, $gender);

    if (!$created) {
      return [
        'success' => false,
        'message' => 'Unable to create account. Please try again later.'
      ];
    }

    return [
      'success' => true,
      'message' => 'Registered successfully!'
    ];
  }

  public function login($email, $password)
  {
    $user = $this->userModel->findByEmail($email);

    if (!$user || !password_verify($password, $user['password_hash'])) {
      return [
        'success' => false,
        'message' => "Invalid email or password"
      ];
    }

    return [
      'success' => true,
      'user' => $user
    ];
  }
}

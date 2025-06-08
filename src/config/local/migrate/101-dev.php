<?php



// Update user passwords for testing
foreach (\Bs\Auth::findAll() as $auth) {
    $auth->password = \Bs\Auth::hashPassword('password');
    $auth->save();
}


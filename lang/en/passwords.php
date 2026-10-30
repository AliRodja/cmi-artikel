<?php

// Hanya menimpa 'sent' dari file bawaan Laravel (digabung, kunci lain tetap bawaan). Sengaja "if ... registered":
// jawaban sama untuk email terdaftar maupun tidak (ForgotPasswordController), agar tidak membocorkan akun.
return [
    'sent' => 'If that email is registered, a password reset link has been sent.',
];

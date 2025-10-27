<?php
// config/mail.php
return [
  // SMTP server
  'host'       => 'smtp.gmail.com',     // or your SMTP
  'port'       => 587,                  // 587 (TLS) or 465 (SSL)
  'encryption' => 'tls',                // 'tls' or 'ssl'

  // SMTP user (use an app password if Gmail)
  'username'   => 'ksd.perez13@gmail.com',
  'password'   => 'ylcjlzqknvktnert',

  // FROM identity
  'from_email' => 'ksd.perez13@gmail.com',
  'from_name'  => 'Data Agreements & Contracts Portal',

  // Optional: BCC every ticket to an archive mailbox
  'bcc'        => '' // e.g. 'archive@yourdomain.com'
];

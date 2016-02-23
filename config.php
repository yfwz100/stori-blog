<?php return [
  'settings' => [
    'displayErrorDetails' => true,
  ],
  'view' => function ($c) {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/templates/2016');
  }
];
